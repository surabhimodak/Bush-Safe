<?php

/**
 * Access terms associated with a taxonomy
 */
class APPMAKER_WP_REST_Settings_Controller extends APPMAKER_WP_REST_Controller {

	public function __construct() {
		parent::__construct();
		$this->rest_base = 'backend/settings';
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'api_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),

				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'save_settings' ),
					'permission_callback' => array( $this, 'api_permissions_check' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
					),
				),
			)
		);
	}

	public function get_settings() {
		$options = get_option( 'appmaker_wp_custom_settings', array() );

		$return = array(
			'general' => array(
				'id' => 'general',
				'title'  => __( 'Single Post', 'appmaker-wp-mobile-app-manager' ),
				'fields' => array(
					self::get_field(
						array(
							'type'	=> 'textarea',
							'id'      => 'custom_post_head',
							'label'   => 'Custom post head html',
							'default' => '',
						)
					),
				),
			),
			'cache'   => array(
				'id'     => 'cache',
				'title'  => __( 'Caching', 'appmaker-wp-mobile-app-manager' ),
				'fields' => array(
					self::get_field(
						array(
							'type'        => 'select',
							'id'          => 'cache_enabled',
							'label'       => 'Caching',
							'default'     => 0,
							'data_source' => array(
								'data' => array(
									array(
										'id'    => 1,
										'label' => 'Enabled',
									),
									array(
										'id'    => 0,
										'label' => 'Disabled',
									),
								),
							),
						)
					),
					self::get_field(
						array(
							'id'      => 'cache_time',
							'label'   => 'Caching time (in Seconds)',
							'default' => 10800,
						)
					),
				),
			),
		);
		foreach ( $return as $section ) {
			foreach ( $section['fields'] as $field ) {
				if ( ! isset( $options[ $field['id'] ] ) && $field['type'] != 'title' ) {
					$options[ $field['id'] ] = $field['default'];
				}
			}
		}

		return array(
			'data' => array(
				'fields' => array_values( apply_filters( 'appmaker_wp_settings_fields', $return ) ),
				'values' => apply_filters( 'appmaker_wp_settings_values', $options ),
			),
		);
	}

	public function save_settings( $request ) {
		$data = json_decode( $request['data'], true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$data = stripslashes( $request['data'] ); // To Fix issue for some users having slashes added.
			$data = json_decode( $data, true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				return new WP_Error( 'invalid_json', __( 'Json is invalid.', 'appmaker-wp-mobile-app-manager' ), array( 'status' => 400 ) );
			}
		}
		if ( is_array( $data ) ) {
			$options = get_option( 'appmaker_wp_custom_settings', array() );
			$options = array_merge( $options, $data );
			update_option( 'appmaker_wp_custom_settings', $options );
		}

		return $this->get_settings();
	}

	public static function get_field( $config ) {
		$defaults = array(
			'type'           => 'text',
			'id'             => 'action_value',
			'default'        => '',
			'display'        => true,
			'validation'     => array( 'required' ),
			'label'          => '',
			'desc'           => '',
			'placeholder'    => isset( $config['label'] ) ? $config['label'] : '',
			'depended'       => false,
			'depended_value' => false,
			'data_source'    => array(),
		);

		return wp_parse_args( $config, $defaults );
	}
}
