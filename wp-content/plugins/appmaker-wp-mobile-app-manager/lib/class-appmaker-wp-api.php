<?php

/**
 * Class APPMAKER_WP_API
 *
 * @property APPMAKER_WP_REST_Posts_Controller $APPMAKER_WP_REST_Posts_Controller
 * @property APPMAKER_WP_REST_Posts_Controller $APPMAKER_WP_REST_Pages_Controller
 * @property APPMAKER_WP_REST_BACKEND_INAPPPAGE_Controller $APPMAKER_WP_REST_BACKEND_INAPPPAGE_Controller
 * @property APPMAKER_WP_REST_Settings_Controller $APPMAKER_WP_REST_Settings_Controller
 * @property APPMAKER_WP_REST_BACKEND_NAV_Controller $APPMAKER_WP_REST_BACKEND_NAV_Controller"
 * @property APPMAKER_WP_REST_BACKEND_MEDIA_Controller $APPMAKER_WP_REST_BACKEND_MEDIA_Controller
 * @property APPMAKER_WP_REST_BACKEND_Posts_Controller $APPMAKER_WP_REST_BACKEND_Posts_Controller
 * @property APPMAKER_WP_REST_BACKEND_Terms_Controller $APPMAKER_WP_REST_BACKEND_Terms_Controller
 * @property APPMAKER_WP_REST_FRONTEND_Controller $APPMAKER_WP_REST_FRONTEND_INAPPPAGE_Controller
 * @property APPMAKER_WP_REST_FRONTEND_Controller $APPMAKER_WP_REST_FRONTEND_NAV_Controller
 * @property APPMAKER_WP_REST_User_Controller $APPMAKER_WP_REST_User_Controller
 */
class APPMAKER_WP_API {
	public static $_instance;
	public $settings_key;
	public $settings = false;

	public function __construct() {

		// WP REST API.
		$this->rest_api_init();
	}

	private function rest_api_init() {
		global $wp_version;

		// REST API was included starting WordPress 4.4.
		if ( version_compare( $wp_version, 4.4, '<' ) ) {
			return;
		}

		// $this->rest_api_includes();

		do_action( 'appmaker_wp_rest_api_init' );
		add_action( 'rest_api_init', array( $this, 'rest_api_includes' ) );
	}

	public function rest_api_includes() {
		defined( 'APPMAKER_WP_REQUEST' ) || define( 'APPMAKER_WP_REQUEST', true );

		include_once 'third-party-support/class-appmaker-wp-third-party-support.php';

		//	// Authentication.
		include_once( 'class-appmaker-wp-rest-authentication.php' );


		// WP-API classes and functions.
		if ( ! class_exists( 'WP_REST_Controller' ) ) {
			include_once 'vendor/wp-rest-functions.php';
			include_once 'vendor/class-wp-rest-controller.php';
		}
		include_once 'class-appmaker-wp-helper.php';
		if ( ! class_exists( 'APPMAKER_WP_REST_Controller' ) ) {
			// Abstract Classes.
			include_once 'abstracts/abstract-appmaker-wp-rest-controller.php';

			// Endpoints Classes.
			include_once 'endpoints/class-appmaker-wp-rest-posts-controller.php';
			include_once 'endpoints/class-appmaker-wp-rest-legacy-posts-controller.php';
			include_once 'endpoints/appmaker/class-appmaker-wp-rest-backend-media-controller.php';
			include_once 'endpoints/appmaker/class-appmaker-wp-rest-backend-inapppage-controller.php';
			include_once 'endpoints/appmaker/class-appmaker-wc-rest-backend-settings-controller.php';
			include_once 'endpoints/appmaker/class-appmaker-wp-rest-frontend-controller.php';
			include_once 'endpoints/appmaker/class-appmaker-wp-rest-backend-posts-controller.php';
			include_once 'endpoints/appmaker/class-appmaker-wp-rest-backend-terms-controller.php';
			include_once 'endpoints/appmaker/class-appmaker-wp-rest-backend-nav-controller.php';
			include_once 'endpoints/appmaker/class-appmaker-wp-rest-user-controller.php';

			// Format Converter.
			include_once 'class-appmaker-wp-converter.php';
		}
		include_once 'endpoints/class-appmaker-wp-rest-posts-controller.php';
		$this->register_rest_routes();
	}

	/**
	 * @return APPMAKER_WP_API
	 */
	public static function get_instance() {
		if ( ! is_a( self::$_instance, 'APPMAKER_WP_API' ) ) {
			self::$_instance = new APPMAKER_WP_API();
		}

		return self::$_instance;
	}

	public function register_rest_routes() {
		global $wp_post_types;
		global $wp_taxonomies;

		if ( isset( $wp_post_types['post'] ) ) {
			$wp_post_types['post']->show_in_rest = true;
			$wp_post_types['post']->rest_base    = 'posts';
		}

		if ( isset( $wp_post_types['page'] ) ) {
			$wp_post_types['page']->show_in_rest = true;
			$wp_post_types['page']->rest_base    = 'pages';
		}

		if ( isset( $wp_taxonomies['category'] ) ) {
			$wp_taxonomies['category']->show_in_rest = true;
			$wp_taxonomies['category']->rest_base    = 'categories';
		}

		if ( isset( $wp_taxonomies['post_tag'] ) ) {
			$wp_taxonomies['post_tag']->show_in_rest = true;
			$wp_taxonomies['post_tag']->rest_base    = 'tags';
		}

		APPMAKER_WP_Third_Party_Support::init();

		$controllers = array(
			'APPMAKER_WP_REST_Posts_Controller'        => array(
				'APPMAKER_WP_REST_Posts_Controller',
				'post',
			),
			'APPMAKER_WP_REST_Legacy_Posts_Controller' => array(
				'APPMAKER_WP_REST_Legacy_Posts_Controller',
				'post',
			),
			'APPMAKER_WP_REST_BACKEND_INAPPPAGE_Controller',
			'APPMAKER_WP_REST_Settings_Controller',
			'APPMAKER_WP_REST_BACKEND_NAV_Controller',
			'APPMAKER_WP_REST_BACKEND_MEDIA_Controller',
			'APPMAKER_WP_REST_BACKEND_Posts_Controller',
			'APPMAKER_WP_REST_BACKEND_Terms_Controller',
			'APPMAKER_WP_REST_FRONTEND_INAPPPAGE_Controller' => array(
				'APPMAKER_WP_REST_FRONTEND_Controller',
				'inAppPages',
			),
			'APPMAKER_WP_REST_FRONTEND_NAV_Controller' => array(
				'APPMAKER_WP_REST_FRONTEND_Controller',
				'navigationMenu',
			),
			'APPMAKER_WP_REST_User_Controller',
		);

		foreach ( $controllers as $key => $controller ) {
			if ( is_array( $controller ) ) {
				$this->{$key} = new $controller[0]( $controller[1] );
				$this->$key->register_routes();
			} else {
				$this->$controller = new $controller();
				$this->$controller->register_routes();
			}
		}

		register_rest_route(
			'appmaker-wp/v1',
			'/meta',
			array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'callback' => array( $this, 'get_meta' ),
					'permission_callback' => '__return_true',
				),
			)
		);

	}

	/**
	 * Return plugin meta (Publicly accessible).
	 *
	 * @return array
	 */
	public function get_meta( $request ) {
		global $wp_version;
		$option = get_option( 'appmaker_wp_settings', false );
		$return = array(
			'version'           => APPMAKER_WP::$version,
			'wordpress_version' => $wp_version,
			'plugin_configured' => ( false !== $option ) && isset( $option['api_key'] ) && ! empty( $option['api_key'] ),
		);

		return $return;
	}

	public function load_settings() {
		$this->settings = get_option( 'appmaker_wp_custom_settings', array() );
	}

	public function get_settings( $key, $default_value = '' ) {
		if ( $this->settings === false ) {
			$this->load_settings();
		}
		if ( isset( $this->settings[ $key ] ) ) {
			if ( ! is_numeric( $this->settings[ $key ] ) && is_string( $this->settings[ $key ] ) ) {
				return trim( $this->settings[ $key ] );
			} else {
				return $this->settings[ $key ];
			}
		} else {
			return $default_value;
		}
	}
}
