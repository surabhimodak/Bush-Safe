<?php

// $this->options = get_option( 'appmaker_option' );
class APPMAKER_WP_REST_FRONTEND_Controller extends APPMAKER_WP_REST_Controller {

	protected $type;

	protected $isRoot = false;


	public function __construct( $type ) {
		parent::__construct();
		$this->type      = $type;
		$this->rest_base = "$this->type";
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<key>[a-zA-Z0-9\-\_]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'api_permissions_check' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
					),
				),

				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

	}


	/**
	 * Get a single meta
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		if ( $request['key'] == 'menu' ) {
			$this->type = 'navigationMenu';
			$key        = $this->getSafeKey( 'mainmenu_app' );
		} else {
			$key = $this->getSafeKey( $request['key'] . '_app' );
		}

		$option = get_option( $key );

		if ( ! $option && 'navigationMenu' === $this->type ) {
			$option = APPMAKER_WP_Converter::convert_navMenu_data( APPMAKER_WP::$api->APPMAKER_WP_REST_BACKEND_NAV_Controller->get_default_menu() );
		} elseif ( ! $option && 'inAppPages' === $this->type && 'home' === $request['key'] ) {
			$option = APPMAKER_WP_Converter::convert_inAppPage_data( APPMAKER_WP::$api->APPMAKER_WP_REST_BACKEND_INAPPPAGE_Controller->get_default_home(), 'home' );
		} elseif ( ! $option && 'inAppPages' === $this->type && 'home_tab' === $request['key'] ) {
			$option = APPMAKER_WP_Converter::convert_inAppPage_data( APPMAKER_WP::$api->APPMAKER_WP_REST_BACKEND_INAPPPAGE_Controller->get_default_home_tab(), 'home_tab' );
		} elseif ( ! $option && 'inAppPages' === $this->type && 'menu' === $request['key'] ) {
			$option = APPMAKER_WP_Converter::convert_navMenu_data( APPMAKER_WP::$api->APPMAKER_WP_REST_BACKEND_NAV_Controller->get_default_menu() );
			$option = APPMAKER_WP_Converter::convert_inAppPage_data( APPMAKER_WP::$api->APPMAKER_WP_REST_BACKEND_INAPPPAGE_Controller->get_default_menu(), 'menu' );
		} elseif ( ! $option ) {
			return new WP_Error( 'rest_invalid_key', __( 'Key is not invalid..' ), array( 'status' => 404 ) );
		}

		if ( isset( $option['dynamic'] ) && $option['dynamic'] ) {
			foreach ( $option['widgets'] as $key => $widget ) {
				if ( isset( $widget['dynamic'] ) && $widget['dynamic'] ) {
					$widget = APPMAKER_WP_Converter::convert_dynamic_widget( $widget );
					if ( isset( $widget['skip'] ) && $widget['skip'] ) {
						unset( $option['widgets'][ $key ] );
					} else {
						$option['widgets'][ $key ] = $widget;
					}
				}
			}
		}
		if ( isset( $option['widgets'] ) ) {
			$option['widgets'] = array_values( $option['widgets'] );
		}
		$item = array(
			'key'  => $request['key'],
			'data' => $option,
		);

		$data     = $this->prepare_item_for_response( $item, $request );
		$response = rest_ensure_response( $data );

		return apply_filters('appmaker_wp_item_response',$response);
	}

	/**
	 * Prepare the item for the REST response.
	 *
	 * @param mixed           $item WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response $response
	 */
	public function prepare_item_for_response( $item, $request ) {

		// Wrap the data in a response object.
		$response = rest_ensure_response( $item['data'] );

		/**
		 * Filter meta data returned from the REST API.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param object $meta User object used to create response.
		 * @param WP_REST_Request $request Request object.
		 */
		return $response;
	}

	/**
	 * Get the User's schema, conforming to JSON Schema
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'meta',
			'type'       => 'object',
			'properties' => array(),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$query_params = array(
			'context' => $this->get_context_param(),

		);

		$query_params['context']['default'] = 'view';

		return $query_params;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param WP_Post $meta User object.
	 *
	 * @return array Links for the given meta.
	 */
	protected function prepare_links( $meta ) {
		$links = array(
			'self'       => array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $meta->ID ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);

		return $links;
	}
}
