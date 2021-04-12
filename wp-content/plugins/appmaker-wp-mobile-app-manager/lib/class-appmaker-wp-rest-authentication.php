<?php
/**
 * REST API Authentication
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class APPMAKER_WP_REST_Authentication {

	public $current_user_id;
	
	/**
	 * Initialize authentication actions.
	 */
	public function __construct() {
		global $appmaker_wp_rest_authentication_error;
		$appmaker_wp_rest_authentication_error = true;
		add_filter( 'determine_current_user', array( $this, 'authenticate' ), 100 );
		add_filter( 'rest_authentication_errors', array( $this, 'check_authentication_error' ) );
		add_filter( 'rest_post_dispatch', array( $this, 'send_unauthorized_headers' ), 50 );

		add_filter( 'appmaker_wp_set_user_access_token', array( $this, 'set_user_access_token' ), 0 );

		// Reset Access Token on password change.
		add_action( 'password_reset', array( $this, 'on_password_reset' ), 10, 2 );
		add_action( 'profile_update', array( $this, 'on_profile_update' ), 10, 2 );
		$this->current_user_id = get_current_user_id();
		if ( false === strpos( $_SERVER['REQUEST_URI'], 'manage-login' ) && false === strpos( $_SERVER['REQUEST_URI'], 'login-manage' ) ) {
			$this->authenticate( false ); // Override all cookie authenticated user with token.
		}
	}

	/**
	 * @param $user
	 * @param $pass
	 */
	public function on_password_reset( $user, $pass ) {
		$this->set_user_access_token( $user->ID, true );
	}

	/**
	 * @param $user_id
	 * @param $old_user_data
	 */
	public function on_profile_update( $user_id, $old_user_data ) {
		if ( ! isset( $_POST['pass1'] ) || '' == $_POST['pass1'] ) {
			return;
		}
		$this->set_user_access_token( $user_id, true );
	}

	/**
	 * @param int $user
	 * @param bool $force_new
	 *
	 * @return bool
	 */
	public function set_user_access_token( $user, $force_new = false ) {
		$access_token = get_user_meta( $user, 'appmaker_wp_access_token', true );
		if ( empty( $access_token ) || $force_new ) {
			$access_token = 'token_' . $this->generate_random_hash();
			update_user_meta( $user, 'appmaker_wp_access_token', $access_token );
		}

		return $access_token;
    }
    
    public function generate_random_hash() {
        
        if ( function_exists( 'openssl_random_pseudo_bytes' ) ) { 
            return bin2hex( openssl_random_pseudo_bytes( 20 ) ); 
        } else { 
            return sha1( wp_rand() ); 
        } 
    }   
	/**
	 * Authenticate user.
	 *
	 * @param int|false $user_id User ID if one has been determined, false otherwise.
	 *
	 * @return int|false
	 */
	public function authenticate( $user_id ) {

		// Do not authenticate twice and check if is a request to our endpoint in the WP REST API.
		if ( ! empty( $user_id ) || ! $this->is_request_to_rest_api() ) {
			return $user_id;
		}

		$authenticate = $this->check_access_token();
		if ( ! $authenticate && wp_validate_logged_in_cookie( false ) !== false ) {
			wp_set_auth_cookie( 0 );
		}
		if ( $this->current_user_id !== $authenticate ) {
			wp_set_current_user( $authenticate );
		}


		return $authenticate;
	}

	/**
	 * Check if is request to our REST API.
	 *
	 * @return bool
	 */
	protected function is_request_to_rest_api() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		// Check if our endpoint.
		$appmaker_wp = false !== strpos( $_SERVER['REQUEST_URI'], 'appmaker-wp' );

		return apply_filters( 'appmaker_wp_rest_is_request_to_rest_api', $appmaker_wp );
	}

	/**
	 * Basic Authentication.
	 *
	 * SSL-encrypted requests are not subject to sniffing or man-in-the-middle
	 * attacks, so the request can be authenticated by simply looking up the user
	 * associated with the given consumer key and confirming the consumer secret
	 * provided is valid.
	 *
	 * @return int|bool
	 */
	private function check_access_token() {
		global $appmaker_wp_rest_authentication_error;

		$user_id      = false;
		$access_token = '';

		// If the $_GET parameters are present, use those first.
		if ( ! empty( $_GET['user_id'] ) && ! empty( $_GET['access_token'] ) ) {
			$user_id      = $_GET['user_id'];
			$access_token = $_GET['access_token'];
		}

		// If the above is not present, we will do full basic auth.
		if ( ! $user_id && ! empty( $_SERVER['PHP_AUTH_USER'] ) && ! empty( $_SERVER['PHP_AUTH_PW'] ) ) {
			$user_id      = $_SERVER['PHP_AUTH_USER'];
			$access_token = $_SERVER['PHP_AUTH_PW'];
		}

		// Stop if don't have any key.
		if ( ! $user_id || ! $access_token ) {
			return false;
		}

		// Get user data.
		$user = get_user_by( 'id', $user_id );
		if ( empty( $user ) ) {
			return false;
		}

		$user_access_token = get_user_meta( $user->ID, 'appmaker_wp_access_token', true );
		// Validate user secret.
		if ( empty( $user_access_token ) || ! hash_equals( $user_access_token, $access_token ) ) {
			$appmaker_wp_rest_authentication_error = new WP_Error( 'appmaker_wp_rest_authentication_error', __( 'Auth Token is invalid.', 'appmaker-woocommerce-mobile-app-manager' ), array( 'status' => 401 ) );

			return false;
		}

		if ( wp_validate_logged_in_cookie( false ) != $user->ID ) {
			wp_set_auth_cookie( $user->ID ); // In Order to work login in WebView.
		}

		return $user->ID;
	}

	/**
	 * Check for authentication error.
	 *
	 * @param WP_Error|null|bool $error
	 *
	 * @return WP_Error|null|bool
	 */
	public function check_authentication_error( $error ) {
		global $appmaker_wp_rest_authentication_error;

		// Passthrough other errors.
		if ( ! empty( $error ) ) {
			return $error;
		}

		return $appmaker_wp_rest_authentication_error;
	}

	/**
	 * If the consumer_key and consumer_secret $_GET parameters are NOT provided
	 * and the Basic auth headers are either not present or the consumer secret does not match the consumer
	 * key provided, then return the correct Basic headers and an error message.
	 *
	 * @param WP_REST_Response $response Current response being served.
	 *
	 * @return WP_REST_Response
	 */
	public function send_unauthorized_headers( $response ) {
		global $appmaker_wp_rest_authentication_error;

		if ( is_wp_error( $appmaker_wp_rest_authentication_error ) && is_ssl() ) {
			$auth_message = __( 'Appmaker WordPress API - Use a consumer key in the username field and a consumer secret in the password field.', 'appmaker-woocommerce-mobile-app-manager' );
			$response->header( 'WWW-Authenticate', 'Basic realm="' . $auth_message . '"', true );
		}

		return $response;
	}

}

if ( false !== strpos( $_SERVER['REQUEST_URI'], 'appmaker-wp/' ) || false !== strpos( $_SERVER['REQUEST_URI'], 'appmaker-wp%2F' ) ) {
    new APPMAKER_WP_REST_Authentication();
}
