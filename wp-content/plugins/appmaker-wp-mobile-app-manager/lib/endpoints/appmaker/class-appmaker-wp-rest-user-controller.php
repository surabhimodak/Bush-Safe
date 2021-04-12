<?php
/**
 * REST API User controller
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API User controller class.
 *
 */
class APPMAKER_WP_REST_User_Controller  extends APPMAKER_WP_REST_Controller {

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
	protected $rest_base = 'user';


	/**
	 * Register the routes for products.
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/register',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'register' ),
					'permission_callback' => array( $this, 'api_permissions_check' ),
					'args'                => $this->get_register_args(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);		

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/login',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'login' ),
					'permission_callback' => array( $this, 'api_permissions_check' ),
					'args'                => $this->get_login_args(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);	

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/reset_password',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'reset_password' ),
					'permission_callback' => array( $this, 'api_permissions_check' ),
					'args'                => $this->get_reset_password_args(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);		

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/logout',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'logout' ),
					'permission_callback' => array( $this, 'api_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
    }

    /**
	 * Validate email.
	 *
	 * @param string $username Username.
	 *
	 * @return bool
	 */
	public function validate_username( $username ) {
		$username = trim( $username );

		return validate_username( $username );
	}
	/**
	 * Trim text.
	 *
	 * @param string $text Text.
	 *
	 * @return string
	 */
	public function trim( $text ) {
		return trim( $text );
    }
    
    public function appmaker_is_email( $email ) {
		return is_email( $email );
    }
    

	/**
	 * Get the query params for collections of attachments.
	 *
	 * @return array
	 */
	public function get_register_args() {
		$params             = array();
		$params['username'] = array(
			'description'       => __( 'Username.', 'appmaker-woocommerce-mobile-app-manager' ),
			'type'              => 'string',
			'validate_callback' => array( $this, 'validate_username' ),
			'required'          => apply_filters( 'appmaker_wp_register_username_required', true ),
		);

		$params['email'] = array(
			'description'       => __( 'Email.', 'appmaker-woocommerce-mobile-app-manager' ),
			'type'              => 'string',
			'validate_callback' => array( $this, 'appmaker_is_email' ),
			'required'          => apply_filters( 'appmaker_wp_register_email_required', false ),
		);

		$params['phone'] = array(
			'description'       => __( 'Mobile Number.', 'appmaker-woocommerce-mobile-app-manager' ),
			'type'              => 'string',
			'sanitize_callback' => array( $this, 'trim' ),
			//'validate_callback' => array( 'WC_Validation', 'is_phone' ),
			'required'          => apply_filters( 'appmaker_wp_register_phone_required', false ),
		);

		$params['password'] = array(
			'description'       => __( 'Password.', 'appmaker-woocommerce-mobile-app-manager' ),
			'type'              => 'string',
			'sanitize_callback' => array( $this, 'trim' ),
			'validate_callback' => 'rest_validate_request_arg',
			'required'          => apply_filters( 'appmaker_wp_register_password_required', true ),
		);

		$params['otp'] = array(
			'description'       => __( 'One Time Password.', 'appmaker-woocommerce-mobile-app-manager' ),
			'type'              => 'string',
			'sanitize_callback' => array( $this, 'trim' ),
			//'validate_callback' => array( 'WC_Validation', 'is_phone' ),
			//'required'          =>true,
		);

		return $params;
    }
    
    /**
	 * Get the query params for collections of attachments.
	 *
	 * @return array
	 */
	public function get_login_args() {
		$params             = array();
		$params['username'] = array(
			'description'       => __( 'Username.', 'appmaker-woocommerce-mobile-app-manager' ),
			'type'              => 'string',
			'validate_callback' => array( $this, 'validate_username' ),
			'required'          => apply_filters( 'appmaker_wp_login_username_required', true ),
		);

		$params['email'] = array(
			'description'       => __( 'Email.', 'appmaker-woocommerce-mobile-app-manager' ),
			'type'              => 'string',
			'validate_callback' => array( $this, 'appmaker_is_email' ),
			'required'          => apply_filters( 'appmaker_wp_login_email_required', false ),
		);

		$params['password'] = array(
			'description'       => __( 'Password.', 'appmaker-woocommerce-mobile-app-manager' ),
			'type'              => 'string',
			'sanitize_callback' => array( $this, 'trim' ),
			'validate_callback' => 'rest_validate_request_arg',
			'required'          => true,
		);

		return $params;
    }
    
    /**
	 * Perform user registration
	 *
	 * @param WP_REST_Request $request request object.
	 *
	 * @return array|int|mixed|WP_Error
	 */
	public function register( $request ) {
		$return = array( 'status' => true );
		$return = apply_filters( 'appmaker_wp_registration_validate', $return, $request );

		if ( ! empty( $request['phone'] ) && WC()->countries->get_base_country() === 'IN' ) {
			if ( ! preg_match( '/^\d{10}$/', $request['phone'] ) ) {
				return new WP_Error( 'invalid_phone', 'Invalid phone number' );
			}
		}
		if ( ! is_wp_error( $return ) ) {
			if ( isset( $request['phone'] ) && $request['otp'] ) {
				$user_id = wp_create_user( $request['phone'] ,  $request['password'] , sanitize_email( $request['email']  ) );
			} else {
				$user_id = wp_create_user(  $request['username'] , $request['password'] , sanitize_email( $request['email']  ) );
			}
			if ( is_wp_error( $user_id ) ) {

				return $user_id;
			}
			add_user_meta( $user_id, '_registered_from_app', 1 );

			// if ( isset( $request['phone'] ) ) {
			// 	update_user_meta( $user_id, 'billing_phone', trim( $request['phone'] ) );
			// 	update_user_meta( $user_id, 'shipping_phone', trim( $request['phone'] ) );
			// }
			update_user_meta( $user_id, 'appmaker_wp_user_login_from_app', true );
			do_action( 'appmaker_wp_user_registered', $user_id, $request );

			if ( apply_filters( 'appmaker_wp_login_after_register_required', true ) ) {
				 $return = $this->login( $request );
			}
			$register_datetime = current_time( 'mysql' );
			update_user_meta( $user_id, 'user_register_date_time', $register_datetime );
			return  apply_filters( 'appmaker_wp_registration_response', $return );

		}

		return $return;
    }
    
    /**
	 * Set Current User token and return user object
	 *
	 * @param WP_User $user User object.
	 *
	 * @return array|int|mixed|void|WP_Error
	 */
	public function set_current_user( $user ) {
		$access_token = apply_filters( 'appmaker_wp_set_user_access_token', $user->ID );
		update_user_meta( $user->ID, 'appmaker_wp_user_login_from_app', true );
		$return = array(
			'status'       => 1,
			'access_token' => $access_token,
			'user_id'      => $user->data->ID,
			'user'         => array(
				'id'           => $user->data->ID,
				'nicename'     => $user->data->user_nicename,
				'email'        => $user->data->user_email,
				'status'       => $user->data->user_status,
				'display_name' => $user->data->display_name,
				'avatar'       => $this->get_avatar( $user ),
			),
		);

		return $return;
	}

    	/**
	 * @param WP_User $user User object.
	 *
	 * @return string
	 */
	public function get_avatar( $user ) {
		$avatar = get_user_meta( $user->data->ID, '_user_avatar', true );
		if ( ! empty( $avatar ) ) {
			return $avatar;
		} else {
			$avatar = get_avatar_url( $user->data->ID );
			/*if ( empty( $avatar ) ) {
			   $avatar = get_avatar_url( $user->data->ID, array( 'default' => 'retro', 'size' => 96 ) );
			}*/

			return $avatar;
		}
    }
    
    /**
	 * Perform user login
	 *
	 * @param WP_REST_Request $request request object.
	 *
	 * @return array|int|mixed|void|WP_Error
	 */
	public function login( $request ) {
		if ( function_exists( 'w3tc_dbcache_flush' ) ) {
			w3tc_dbcache_flush();
		}
		if ( isset( $request['phone'] ) && $request['otp'] ) {
			$user = get_user_by( 'login', $request['phone'] );
		} else {
			$user = get_user_by( 'login', $request['username'] );
		}
		if ( ! $user ) {
			$user = get_user_by( 'email', $request['username'] );
		}
		if ( $user && wp_check_password( $request['password'], $user->data->user_pass, $user->ID ) ) {
			//Saving date and time after user login
			$login_datetime = current_time( 'mysql' );
			update_user_meta( $user->ID, 'user_login_date_time', $login_datetime );
			$return =  $this->set_current_user( $user );
		} else {
			$return = new WP_Error( 'invalid_login_details', __( 'Invalid username/password', 'appmaker-woocommerce-mobile-app-manager' ) );
		}

		return apply_filters( 'appmaker_wp_login_response', $return );
	}
    /**
	 * @param int $user
	 * @param bool $force_new
	 *
	 * @return bool
	 */
	// public function set_user_access_token( $user, $force_new = false ) {
	// 	$access_token = get_user_meta( $user, 'appmaker_wp_access_token', true );
	// 	if ( empty( $access_token ) || $force_new ) {
	// 		$access_token = 'token_' . $this->generate_random_hash();
	// 		update_user_meta( $user, 'appmaker_wp_access_token', $access_token );
	// 	}

	// 	return $access_token;
    // }
    
    // public function generate_random_hash() {
        
    //     if ( function_exists( 'openssl_random_pseudo_bytes' ) ) { 
    //         return bin2hex( openssl_random_pseudo_bytes( 20 ) ); 
    //     } else { 
    //         return sha1( wp_rand() ); 
    //     } 
    // }   

    public function logout() {
		wp_set_current_user( 0 );
		wp_clear_auth_cookie();		

		return array( 'status' => true );
    }
    
    /**
	 * Perform rest password
	 *
	 * @param WP_REST_Request $request request object.
	 *
	 * @return array|int|mixed|void|WP_Error
	 */
	public function reset_password( $request ) {
        $_POST['user_login'] = $request['email'];
        ob_start();
        require_once ABSPATH . 'wp-includes/registration.php';
        require_once ABSPATH . 'wp-login.php';
        ob_end_clean();
        $status = retrieve_password();

		if ( $status === true ) {	
            return array(
				'status'  => true,
				'message' => __( 'Link for password reset has been emailed to you. Please check your email.', 'appmaker-woocommerce-mobile-app-manager' ),
			);		
		
		} else {
            //$erro_msgs = implode('<br>', $status->get_error_messages());
            $error = $this->get_wp_notice_errors($status);
            if ( false == $error ) {
				return new WP_Error( 'rest_error', __( 'The e-mail could not be sent', 'appmaker-woocommerce-mobile-app-manager' ) );
			} else {
				return $error;
			}
            
        }
        return $return;
    }

    public function get_wp_notice_errors($status){

        if (! empty($status->errors) ) { 
            $return = false;              
            foreach ( $status->errors as $key => $errors ) {
                foreach ($errors as $error){

                    if ('EMPTY_ERROR' !== $error ) {
                        if(isset($error['notice']) ){                        
                            $error_message = $error['notice'];
                        }else
                            $error_message = $error;
                        
                        $return = $this->add_error($return, html_entity_decode($error_message), 'appmaker_wp_error', array( 'status' => 405 ));
                    }
                    
                }               
            }
            return $return;
        }
        return false;
    }

      /**
     * @param mixed|WP_Error $return
     *
     * @param string         $message
     * @param string         $code
     * @param string         $data
     *
     * @return WP_Error
     */
    public function add_error( $return, $message = '', $code = 'appmaker_wp_error', $data = '' )
    {
        $message = strip_tags($message);
        $message = html_entity_decode($message);
        if (is_wp_error($return) ) {
            $return->add($code, $message, $data);
        } else {
            $return = new WP_Error($code, $message, $data);
        }
        return $return;
    }


    	/**
	 * Get the query params for collections of attachments.
	 *
	 * @return array
	 */
	public function get_reset_password_args() {
		$params = array();

		$params['email'] = array(
			'description' => __( 'Email.', 'appmaker-woocommerce-mobile-app-manager' ),
			'type'        => 'string',
			'required'    => apply_filters( 'appmaker_wp_login_email_required', false ),
		);

		return $params;
	}
}
