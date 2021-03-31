<?php
/**
 * Registration handler.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */

/**
 * A class to handle everything related to product registration
 *
 * @since 1.0.0
 */
class Fusion_Product_Registration {

	/**
	 * The option name.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var string
	 */
	private $option_name = 'fusion_registration_data';

	/**
	 * Holding the available registration data.
	 *
	 * @access private
	 * @since 1.9.2
	 * @var array
	 */
	private $registration_data = [];

	/**
	 * The arguments that are used in the constructor.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var array
	 */
	private $args = [];

	/**
	 * The product-name converted to ID.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var string
	 */
	private $product_id = '';

	/**
	 * An array of bundled products.
	 *
	 * @static
	 * @access private
	 * @since 1.0.0
	 * @var array
	 */
	private static $bundled = [];

	/**
	 * Updater
	 *
	 * @access private
	 * @since 1.0.0
	 * @var null|object Fusion_Updater.
	 */
	private $updater = null;

	/**
	 * An instance of the Fusion_Envato_API class.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var null|object Fusion_Envato_API.
	 */
	private $envato_api = null;

	/**
	 * Envato API response as WP_Error object.
	 *
	 * @access private
	 * @since 1.7
	 * @var null|object WP_Error.
	 */
	private $envato_api_error = null;

	/**
	 * The class constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $args An array of our arguments [string "type", string "name", array "bundled"].
	 */
	public function __construct( $args = [] ) {

		$this->args       = $args;
		$this->product_id = sanitize_key( $args['name'] );

		if ( isset( $args['bundled'] ) ) {
			$this->add_bundled_product( $args['bundled'] );
		}

		$this->set_registration_data();

		// Instantiate the updater.
		if ( null === $this->updater ) {
			$this->updater = new Fusion_Updater( $this );
		}

		add_action( 'wp_ajax_avada_product_registration', [ $this, 'ajax_check_registration' ] );
	}

	/**
	 * Adds a product to the array of bundled products.
	 *
	 * @access private
	 * @since 1.0.0
	 * @param array $bundled An array o bundled products.
	 */
	private function add_bundled_product( $bundled ) {

		$bundled = (array) $bundled;
		foreach ( $bundled as $product_slug => $product_name ) {
			$product = sanitize_key( $product_name );

			if ( ! isset( self::$bundled[ $product ] ) ) {
				self::$bundled[ $product ] = $this->args['name'];
			}
		}
	}

	/**
	 * Gets bundled products array.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return array
	 */
	public function get_bundled() {

		return self::$bundled;
	}

	/**
	 * Gets the arguments.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return array
	 */
	public function get_args() {

		return $this->args;
	}

	/**
	 * Envato API class.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return Fusion_Envato_API
	 */
	public function envato_api() {

		if ( null === $this->envato_api ) {
			$this->envato_api = new Fusion_Envato_API( $this );
		}
		return $this->envato_api;
	}

		/**
		 * Checks if the product is part of the themes or plugins
		 * purchased by the user belonging to the token.
		 *
		 * @access public
		 * @since 1.0.0
		 */
	public function ajax_check_registration() {
		if ( ! isset( $_POST['avada_product_reg'] ) || ! wp_verify_nonce( $_POST['avada_product_reg'], 'avada_product_reg_nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			exit( 'Invalid request.' );
		}

		$this->check_registration();

		ob_start();
		$this->the_form();
		$response = ob_get_clean();

		exit( $response ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Checks if the product is part of the themes or plugins
	 * purchased by the user belonging to the token.
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function check_registration() {

		// Sanity check. No need to do anything if we're not saving the form.
		if ( ( isset( $_POST[ $this->option_name ] ) && isset( $_POST[ $this->option_name ][ $this->product_id ] ) || isset( $_POST['avada_unregister_product'] ) ) && isset( $_POST['_wpnonce'] ) ) {

			// Security check.
			check_admin_referer( $this->option_name . '_' . $this->product_id );

			// The new token.
			$token = '';
			if ( ! isset( $_POST['avada_unregister_product'] ) || '1' !== $_POST['avada_unregister_product'] ) {
				if ( isset( $_POST[ $this->option_name ][ $this->product_id ]['token'] ) ) {
					$token = sanitize_text_field( wp_unslash( $_POST[ $this->option_name ][ $this->product_id ]['token'] ) );
				}
				$token = wp_strip_all_tags( trim( $token ) );
			}

			// Update saved product data.
			$this->registration_data[ $this->product_id ]['token']    = $token;
			$this->registration_data[ $this->product_id ]['is_valid'] = $this->product_exists( $token );
			$this->registration_data[ $this->product_id ]['scopes']   = $this->envato_api()->get_token_scopes( $token );

			update_option( $this->option_name, $this->registration_data );
		}
	}

	/**
	 * Checks if the product is part of the themes or plugins
	 * purchased by the user belonging to the token.
	 *
	 * @access private
	 * @since 1.0.0
	 * @param string $token A token to check.
	 * @param int    $page  The page number if one is necessary.
	 * @return bool
	 */
	private function product_exists( $token = '', $page = '' ) {
		if ( ! empty( $token ) && 32 !== strlen( $token ) ) {
			return false;
		}

		// Set the new token for the API call.
		if ( '' !== $token ) {
			$this->envato_api()->set_token( $token );
		}
		if ( 'theme' === $this->args['type'] ) {
			$products = $this->envato_api()->themes( [], $page );
		} elseif ( 'plugin' === $this->args['type'] ) {
			$products = $this->envato_api()->plugins( [], $page );
		}

		if ( is_wp_error( $products ) ) {
			$this->envato_api_error = $products;
			return false;
		}

		// Check iv product is part of the purchased themes/plugins.
		foreach ( $products as $product ) {
			if ( isset( $product['name'] ) ) {
				if ( $this->args['name'] === $product['name'] ) {
					return true;
				}
			}
		}

		if ( 100 === count( $products ) ) {
			$page = ( ! $page ) ? 2 : $page + 1;
			return $this->product_exists( '', $page );
		}
		return false;
	}

	/**
	 * Set available registration data.
	 *
	 * @access public
	 * @since 1.9.2
	 * @return void
	 */
	public function set_registration_data() {
		$registration_data        = [];
		$registration_data_stored = get_option( $this->option_name, [] );

		$registration_data_dummy = [
			'token'         => '',
			'purchase_code' => '',
			'is_valid'      => 'false',
			'scopes'        => [],
		];

		foreach ( $registration_data_stored as $product => $data ) {
			$registration_data[ $product ] = wp_parse_args( $data, $registration_data_dummy );
		}

		$this->registration_data = $registration_data;
	}



	/**
	 * Get all available registration data.
	 *
	 * @access public
	 * @since 1.9.2
	 * @return array The registration data.
	 */
	public function get_registration_data() {

		return $this->registration_data;
	}

	/**
	 * Check if product is part of registration data and is also valid.
	 *
	 * @access public
	 * @since 1.9.2
	 * @param string $product_id The plugin/theme ID.
	 * @return bool
	 */
	public function is_registered( $product_id = '' ) {
		if ( ! $product_id ) {
			$product_id = $this->product_id;
		}

		// Is the product registered?
		if ( isset( $this->registration_data[ $product_id ] ) && true === $this->registration_data[ $product_id ]['is_valid'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the stored token for the product.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param string $product_id The product-ID.
	 * @return string The current token.
	 */
	public function get_token( $product_id = '' ) {
		if ( '' === $product_id ) {
			$product_id = $this->product_id;
		}

		if ( isset( $this->registration_data[ $product_id ] ) ) {
			return $this->registration_data[ $product_id ]['token'];
		}

		return '';
	}

	/**
	 * Returns the stored purchase key for the product.
	 *
	 * @access public
	 * @since 1.9.2
	 * @param string $product_id The product-ID.
	 * @return string The current token.
	 */
	public function get_purchase_key( $product_id = '' ) {
		if ( '' === $product_id ) {
			$product_id = $this->product_id;
		}

		if ( isset( $this->registration_data[ $product_id ] ) ) {
			return $this->registration_data[ $product_id ]['purchase_key'];
		}

		return '';
	}

	/**
	 * Prints the registration form.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function the_form() {

		/**
		 * Check registration. Now donw in the admin class.
		 *
		$this->check_registration();
		 */

		// Get the stored token.
		$token = $this->get_token();

		// Is the product registered?
		$is_registered = $this->is_registered();
		?>
		<h2 class="avada-db-reg-heading">
			<?php if ( $token ) : ?>
				<?php if ( $is_registered ) : ?>
					<i class="fusiona-verified avada-db-reg-icon"></i>
				<?php else : ?>
					<i class="fusiona-cross avada-db-reg-icon"></i>
				<?php endif; ?>
			<?php else : ?>
				<i class="fusiona-unlock avada-db-reg-icon"></i>
			<?php endif; ?>
			<span class="avada-db-reg-heading-text"><?php esc_html_e( 'Register Your Product', 'Avada' ); ?></span>
			<span class="avada-db-card-heading-badge avada-db-card-heading-badge-howto">
				<i class="fusiona-help-outlined"></i>
				<span class="avada-db-card-heading-badge-text"><?php esc_html_e( 'How To?', 'Avada' ); ?></span>
			</span>
		</h2>
		<div class="avada-db-reg-form-container">
			<?php if ( $is_registered ) : ?>
				<p class="avada-db-reg-text"><?php esc_html_e( 'Congratulations! Thank you for registering your product.', 'Avada' ); ?></p>
			<?php else : ?>
				<p class="avada-db-reg-text"><?php esc_html_e( 'Please enter your Envato token and get access to our prebuilt websites, auto updates and premium plugins.', 'Avada' ); ?></p>
			<?php endif; ?>

			<form class="avada-db-reg-form" method="post">
				<div class="avada-db-reg-input-wrapper">
					<div class="avada-db-reg-loader"><span class="avada-db-loader"></span></div>
					<i class="fusiona-key avada-db-reg-input-icon"></i>
					<?php
						$disabled = '';

					if ( $is_registered ) {
						$token_length = strlen( $token ) / 2;
						$token        = substr( $token, 0, $token_length ) . str_repeat( '*', $token_length );
						$disabled     = ' ';
					}
					?>
					<input type="text" class="avada-db-registration-input" name="<?php echo esc_attr( "{$this->option_name}[{$this->product_id}][token]" ); ?>" value="<?php echo esc_attr( $token ); ?>"<?php echo esc_attr( $disabled ); ?> />
				</div>
				<?php $reg_button_text = __( 'Register Now', 'Avada' ); ?>
				<?php if ( $is_registered ) : ?>
					<?php $reg_button_text = __( 'Unregister', 'Avada' ); ?>
					<input type="hidden" name="avada_unregister_product" value="1">
				<?php endif; ?>
				<?php if ( isset( $_GET['no_ajax_reg'] ) && '1' === $_GET['no_ajax_reg'] ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
					<input type="hidden" name="no_ajax_reg" value="1">
				<?php endif; ?>
				<input type="hidden" name="action" value="avada_product_registration">
				<?php wp_nonce_field( $this->option_name . '_' . $this->product_id ); ?>
				<?php wp_nonce_field( 'avada_product_reg_nonce', 'avada_product_reg' ); ?>
				<?php submit_button( esc_html( $reg_button_text ), 'primary avada-db-reg-button', 'submit', false ); ?>
			</form>

			<?php if ( $token && ! $is_registered ) : ?>
				<div class="avada-db-card-error">
					<?php if ( 36 === strlen( $token ) && 4 === substr_count( $token, '-' ) ) : ?>
						<?php esc_html_e( 'Registration could not be completed because the value entered above is a purchase code. A token key is needed to register. Please read the directions below to find out how to create a token key to complete registration.', 'Avada' ); ?>
					<?php elseif ( $this->envato_api_error ) : ?>
						<?php $error_code = $this->envato_api_error->get_error_code(); ?>
						<?php $error_message = str_replace( [ 'Unauthorized', 'Forbidden' ], '', $this->envato_api_error->get_error_message() ); ?>
						<?php /* translators: The server error code and the error message. */ ?>
						<?php printf( esc_html__( 'Invalid token, the server responded with code %1$s.%2$s', 'Avada' ), esc_html( $error_code ), esc_html( $error_message ) ); ?>
					<?php else : ?>
						<?php /* translators: The product name for the license. */ ?>
						<?php printf( esc_html__( 'Invalid token, or corresponding Envato account does not have %s purchased.', 'Avada' ), esc_html( $this->args['name'] ) ); ?>
					<?php endif; ?>
				</div>
			<?php elseif ( $token ) : ?>
				<?php $scopes_ok = $this->envato_api()->check_token_scopes( $this->registration_data[ $this->product_id ]['scopes'] ); ?>
				<?php if ( ! $scopes_ok ) : ?>
					<div class="avada-db-card-error">
						<?php _e( 'Token does not have the necessary permissions. Please create a new token and make sure the following permissions are enabled for it: <strong>View Your Envato Account Username</strong>, <strong>Download Your Purchased Items</strong>, <strong>List Purchases You\'ve Made</strong>, <strong>Verify Purchases You\'ve Made</strong>.', 'Avada' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
			<div class="avada-db-reg-howto">
				<h3 class="avada-db-reg-howto-heading"><?php esc_html_e( 'How To Generate A Token', 'Avada' ); ?></h3>
				<ol class="avada-db-reg-howto-list avada-db-card-text-small">
					<li>
						<?php
						printf(
							/* translators: "Generate A Personal Token" link. */
							__( 'Click on this %1$s link. <strong>IMPORTANT:</strong> You must be logged into the same Themeforest account that purchased %2$s. If you are logged in already, look in the top menu bar to ensure it is the right account. If you are not logged in, you will be directed to login then directed back to the Create A Token Page.', 'Avada' ), // phpcs:ignore WordPress.Security.EscapeOutput
							'<a href="https://build.envato.com/create-token/?user:username=t&purchase:download=t&purchase:verify=t&purchase:list=t" target="_blank">' . esc_html__( 'Generate A Personal Token', 'Avada' ) . '</a>',
							esc_html( $this->args['name'] )
						);
						?>
					</li>
					<li>
						<?php
						_e( 'Enter a name for your token, then check the boxes for <strong>View Your Envato Account Username, Download Your Purchased Items, List Purchases You\'ve Made</strong> and <strong>Verify Purchases You\'ve Made</strong> from the permissions needed section. Check the box to agree to the terms and conditions, then click the <strong>Create Token button</strong>.', 'Avada' ); // phpcs:ignore WordPress.Security.EscapeOutput
						?>
					</li>
					<li>
						<?php
						_e( 'A new page will load with a token number in a box. Copy the token number then come back to this registration page and paste it into the field below and click the <strong>Submit</strong> button.', 'Avada' ); // phpcs:ignore WordPress.Security.EscapeOutput
						?>
					</li>
					<li>
						<?php
						printf(
							/* translators: "documentation post" link. */
							esc_html__( 'You will see a green check mark for success, or a failure message if something went wrong. If it failed, please make sure you followed the steps above correctly. You can also view our %s for various fallback methods.', 'Avada' ),
							'<a href="https://theme-fusion.com/documentation/avada/getting-started/how-to-register-your-purchase/" target="_blank">' . esc_html__( 'documentation post', 'Avada' ) . '</a>'
						);
						?>
					</li>
				</ol>
			</div>
		</div>
		<?php

	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
