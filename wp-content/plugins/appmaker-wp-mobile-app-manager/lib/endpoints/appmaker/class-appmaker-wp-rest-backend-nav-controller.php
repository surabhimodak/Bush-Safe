<?php

//$this->options = get_option( 'appmaker_option' );
class APPMAKER_WP_REST_BACKEND_NAV_Controller extends APPMAKER_WP_REST_Controller {

	protected $type;

	protected $isRoot = true;


	public function __construct() {
		parent::__construct();
		$this->type      = 'navigationMenu';
		$this->rest_base = "backend/$this->type";
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
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'api_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'api_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

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
	 * Get all metas
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$option = get_option( $this->getSafeKey( 'mainmenu' ), array() );

		if ( $option == false || empty( $option ) || ! is_array( $option ) ) {
			$option = $this->get_default_menu();
		}

		$item     = array( 'data' => $option );
		$data     = $this->prepare_item_for_response( $item, $request );
		$response = rest_ensure_response( $data );

		return $response;
	}

	public function get_default_menu() {
		$args = array(
			'taxonomy'     => 'category',
			'orderby'      => 'id',
			'show_count'   => true,
			'pad_counts'   => true,
			'hierarchical' => true,
			'title_li'     => '',
			'hide_empty'   => true,
			);
	
			$cat_terms = get_categories($args);
			$return    = array();
			$menu_type = new stdClass();
			$menu_type->id            = 'menu_item';
			$menu_type->label         = 'Menu Item';
	
			$menu_action_in_app_page = new stdClass();
			$menu_action_in_app_page->id            = 'OPEN_IN_APP_PAGE';
			$menu_action_in_app_page->label         = 'Open In-App Page';
	
			$home_action_value = new stdClass();
			$home_action_value->id            = 'home';
			$home_action_value->label         = 'Home';
			$home_action_value->key         = 'home';
	
			$return[0] = new stdClass();
			$return[0]->id           = 0;
			$return[0]->title        = __('Home');
			$return[0]->icon         = '';
			$return[0]->type         = $menu_type;
			$return[0]->action       = $menu_action_in_app_page;
			$return[0]->action_value = $home_action_value;
			$return[0]->children = array();
	
			foreach ( $cat_terms as $item ) {
				$menu                      = new stdClass();
				$menu->id                  = $item->term_id;
				$menu->title               = html_entity_decode($item->name);
				$menu->icon                = '';
				$menu->type                = $menu_type;
				$menu->action              = new stdClass();
				$menu->action->id          = 'LIST_POST';
				$menu->action->label       = 'Open Category';
				$menu->action_value        = new stdClass();
				$menu->action_value->id    = $item->term_id;
				$menu->action_value->label = html_entity_decode($item->name);
				$menu->children               = array();
	
				if (0 !== $item->parent ) {
					if (isset($return[ $item->parent ]) ) {
						$return[ $item->parent ]->children[] = $menu;
					}
				} else {
					$return[ $item->term_id ] = $menu;
				}
			}
	
			return array_values($return);	
	}

	public function get_default_menu_from_site_menu() {
		$locations = get_nav_menu_locations();
		$menuItems = array();
		foreach ( $locations as $location ) {
			$menu      = wp_get_nav_menu_object( $location );
			$menuItems = wp_get_nav_menu_items( $menu->term_id );
			if ( ! empty( $menuItems ) ) {
				break;
			}
		}
		$return = array();
		foreach ( $menuItems as $item ) {
			$skip              = false;
			$menu              = new stdClass();
			$menu->id          = $item->ID;
			$menu->title       = $item->title;
			$menu->icon        = '';
			$menu->type        = new stdClass();
			$menu->type->id    = 'menu_item';
			$menu->type->label = 'Menu Item';
			$menu->action      = new stdClass();
			if ( $item->type == 'post_type' && $item->object == 'post' ) {
				$menu->action->id          = 'OPEN_POST';
				$menu->action->label       = 'Open Post / Page';
				$menu->action_value        = new stdClass();
				$menu->action_value->id    = $item->object_id;
				$menu->action_value->label = $item->title;
			}
			if ( $item->type == 'post_type' && $item->object == 'page' ) {
				$menu->action->id          = 'OPEN_POST';
				$menu->action->label       = 'Open Post / Page';
				$menu->action_value        = new stdClass();
				$menu->action_value->id    = $item->object_id;
				$menu->action_value->label = $item->title;
			} elseif ( $item->type == 'taxonomy' && $item->object == 'category' ) {
				$menu->action->id          = 'LIST_POST';
				$menu->action->label       = 'Open Category';
				$menu->action_value        = new stdClass();
				$menu->action_value->id    = $item->object_id;
				$menu->action_value->label = $item->title;
			} elseif ( $item->type == 'custom' ) {
				$menu->action->id    = 'OPEN_URL';
				$menu->action->label = 'Open URL';
				$menu->action_value  = $item->url;
			} else {
				$skip = true;
			}
			$menu->nodes = array();
			if ( ! $skip && $item->menu_item_parent != 0 ) {
				if ( isset( $return[ $item->menu_item_parent ] ) ) {
					$return[ $item->menu_item_parent ]->nodes[] = $menu;
				}
			} elseif ( ! $skip ) {
				$return[ $item->ID ] = $menu;
			}
		}

		return array_values( $return );
	}

	/**
	 * Prepare the item for the REST response.
	 *
	 * @param mixed $item WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response $response
	 */
	public function prepare_item_for_response( $item, $request ) {

		// Wrap the data in a response object
		$response = rest_ensure_response( $item );

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
	 * Create a single meta
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {

		$key  = $request['key'];
		$data = json_decode( $request['data'] );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$data = stripslashes( $request['data'] ); // To Fix issue for some users having slashes added.
			$data = json_decode( $data );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				return new WP_Error( 'invalid_json', __( 'Json is invalid.' ), array( 'status' => 400 ) );
			}
		}

		$appData = APPMAKER_WP_Converter::convert_navMenu_data( $data );

		update_option( $this->getSafeKey( 'mainmenu' ), $data, 'no' );
		update_option( $this->getSafeKey( 'mainmenu_app' ), $appData, 'no' );

		$cache_key = 'appmaker_wc_' . $this->type;
		if ( ! empty( $key ) ) {
			$cache_key .= '-' . $key;
		}
		delete_transient( $cache_key );

		$request->set_param( 'context', 'edit' );
		$item     = array( 'data' => $data );
		$response = $this->prepare_item_for_response( $item, $request );
		$response = rest_ensure_response( $response );
		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '/%s/%s/%s', $this->namespace, $this->rest_base, '' ) ) );

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
			'properties' => array(

				'data' => array(
					'description' => __( 'JSON data .' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),

				),
				'key'  => array(
					'description' => __( 'Key for that data .' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),

				),

			),
		);

		return $this->add_additional_fields_schema( $schema );
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

	/**
	 * Determine if the current meta is allowed to make the desired roles change.
	 *
	 * @param integer $meta_id
	 * @param array $roles
	 *
	 * @return WP_Error|boolean
	 */
	protected function check_role_update( $meta_id, $roles ) {
		global $wp_roles;

		foreach ( $roles as $role ) {

			if ( ! isset( $wp_roles->role_objects[ $role ] ) ) {
				return new WP_Error( 'rest_meta_invalid_role', sprintf( __( 'The role %s does not exist.' ), $role ), array( 'status' => 400 ) );
			}

			$potential_role = $wp_roles->role_objects[ $role ];
			// Don't let anyone with 'edit_metas' (admins) edit their own role to something without it.
			// Multisite super admins can freely edit their blog roles -- they possess all caps.
			if ( ! ( is_multisite() && current_meta_can( 'manage_sites' ) ) && get_current_meta_id() === $meta_id && ! $potential_role->has_cap( 'edit_metas' ) ) {
				return new WP_Error( 'rest_meta_invalid_role', __( 'You cannot give resource that role.' ), array( 'status' => rest_authorization_required_code() ) );
			}

			// The new role must be editable by the logged-in meta.

			/** Include admin functions to get access to get_editable_roles() */
			require_once ABSPATH . 'wp-admin/includes/admin.php';

			$editable_roles = get_editable_roles();
			if ( empty( $editable_roles[ $role ] ) ) {
				return new WP_Error( 'rest_meta_invalid_role', __( 'You cannot give resource that role.' ), array( 'status' => 403 ) );
			}
		}
		return true;
	}
}
