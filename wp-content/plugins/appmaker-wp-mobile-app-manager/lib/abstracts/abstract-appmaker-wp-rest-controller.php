<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Rest Controller Class
 */
abstract class APPMAKER_WP_REST_Controller extends WP_REST_Controller {
	/**
	 * Plugin name (appmaker_wc or appmaker) Get settings according to plugin
	 *
	 * @var string
	 */
	public $plugin = 'appmaker_wp';
	/**
	 * Controller type
	 *
	 * @var string
	 */
	protected $type = '';
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'appmaker-wp/v1';
	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '';
	protected $isRoot = false;
	/**
	 * Variable to store api key details
	 *
	 * @var mixed|void
	 */
	private $options;

	/**
	 * APPMAKER_WP_REST_Controller constructor.
	 */
	public function __construct() {
		$this->options = get_option( $this->plugin . '_settings' );
	}

	/**
	 * Permissions check for common (In Appmaker API case)
	 *
	 * @param string $request
	 * @param string $method
	 *
	 * @return bool|WP_Error
	 * @internal param string $type
	 */
	public function api_permissions_check( $request, $method = '' ) {
		if ( ! $this->options ) {
			return new WP_Error( 'rest_forbidden_context', __( 'Plugin not configured' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( $this->isRoot ) {
			if ( $this->options['api_secret'] === $request['api_secret'] ) {
				return true;
			}
		} elseif ( ( $this->options['api_secret'] === $request['api_secret'] ) || ( $this->options['api_key'] === $request['api_key'] ) ) {
			return true;
		}

		return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to do this' ), array( 'status' => rest_authorization_required_code() ) );
	}


	/**
	 * Permissions check for common (In Appmaker API case)
	 *
	 * @param string $request
	 * @param string $method
	 *
	 * @return bool|WP_Error
	 * @internal param string $type
	 */
	public function user_logged_in_check( $request, $method = '' ) {
		$api_check = $this->api_permissions_check( $request, $method );
		if ( ! is_wp_error( $api_check ) ) {
			if ( is_user_logged_in() ) {
				return true;
			} else {
				return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to do this' ), array( 'status' => rest_authorization_required_code() ) );
			}
		} else {
			return $api_check;
		}
	}

	/**
	 * Return a random string
	 *
	 * @param int $length Length of random string.
	 *
	 * @return string
	 */
	public function get_random_key( $length = 6 ) {
		return substr( str_shuffle( '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_' ), 0, $length );
	}

	/**
	 * Return a safe key
	 *
	 * @param string $key Key to convert into safe.
	 *
	 * @return string
	 */
	public function getSafeKey( $key ) {
		return $this->plugin . '_' . $this->type . '_' . $key;
	}

	/**
	 * Add the schema from additional fields to an schema array.
	 *
	 * The type of object is inferred from the passed schema.
	 *
	 * @param array $schema Schema array.
	 *
	 * @return array
	 */
	protected function add_additional_fields_schema( $schema ) {
		if ( empty( $schema['title'] ) ) {
			return $schema;
		}

		/**
		 * Can't use $this->get_object_type otherwise we cause an inf loop.
		 */
		$object_type = $schema['title'];

		$additional_fields = $this->get_additional_fields( $object_type );

		foreach ( $additional_fields as $field_name => $field_options ) {
			if ( ! $field_options['schema'] ) {
				continue;
			}

			$schema['properties'][ $field_name ] = $field_options['schema'];
		}

		$schema['properties'] = apply_filters( 'woocommerce_rest_' . $object_type . '_schema', $schema['properties'] );

		return $schema;
	}

	/**
	 * Get normalized rest base.
	 *
	 * @return string
	 */
	protected function get_normalized_rest_base() {
		return preg_replace( '/\(.*\)\//i', '', $this->rest_base );
	}

	/**
	 * Decode html entries.
	 *
	 * @param string $string String.
	 *
	 * @return string
	 */
	public function decode_html( $string ) {
		return strip_tags( html_entity_decode( $string ) );
	}
}
