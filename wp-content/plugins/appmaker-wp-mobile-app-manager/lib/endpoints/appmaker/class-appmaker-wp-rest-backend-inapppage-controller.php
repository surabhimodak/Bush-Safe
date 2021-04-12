<?php

//$this->options = get_option( 'appmaker_option' );
class APPMAKER_WP_REST_BACKEND_INAPPPAGE_Controller extends APPMAKER_WP_REST_Controller {

	protected $type;

	protected $isRoot        = true;
	protected $inAppPagesKey = '_inAppPages';

	public function __construct() {
		parent::__construct();
		$this->type      = 'inAppPages';
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
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'api_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<key>[a-zA-Z0-9\-\_]+)/delete',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'api_permissions_check' ),
					'args'                => array(
						'force'    => array(
							'default'     => false,
							'description' => __( 'Required to be true, as resource does not support trashing.' ),
						),
						'reassign' => array(),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<key>[a-zA-Z0-9\-\_]+)/set-as-home',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'set_as_home' ),
					'permission_callback' => array( $this, 'api_permissions_check' ),
					'args'                => array(
						'force'    => array(
							'default'     => false,
							'description' => __( 'Required to be true, as resource does not support trashing.' ),
						),
						'reassign' => array(),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

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
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'api_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'api_permissions_check' ),
					'args'                => array(
						'force'    => array(
							'default'     => false,
							'description' => __( 'Required to be true, as resource does not support trashing.' ),
						),
						'reassign' => array(),
					),
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
		$response = get_option( $this->getSafeKey( $this->inAppPagesKey ) );
		if ( ! $response || empty( $response ) ) {
			$response = array(
				'home' => array(
					'id'    => 'home',
					'label' => 'Home',
					'key'   => 'home',
				),
				'home_tab' => array(
					'id'    => 'home_tab',
					'label' => 'Home latest tab',
					'key'   => 'home_tab',
				),
			);
		}

		if ( isset( $response['menu'] ) ) {
			unset( $response['menu'] );
		}

		return $response;
	}

	/**
	 * Get a single meta
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {

		$key = $this->getSafeKey( $request['key'] );

		$option = get_option( $key );
		if ( ! $option && 'home' === $request['key'] ) {
			$option = $this->get_default_home();
		} elseif ( ! $option && 'home_tab' === $request['key'] ) {
			$option = $this->get_default_home_tab();
		} elseif ( ! $option && 'menu' === $request['key'] ) {
			$option = $this->get_default_menu();
		}

		if ( ! $option ) {
			return new WP_Error( 'rest_invalid_key', __( 'Key is not invalid.' ), array( 'status' => 404 ) );
		}
		$item     = array(
			'key'  => $request['key'],
			'data' => $option,
		);
		$data     = $this->prepare_item_for_response( $item, $request );
		$response = rest_ensure_response( $data );

		return $response;
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
		if ( ! empty( $request['key'] ) ) {
			$key = $request['key'];
		} else {
			$key = $this->get_random_key();
		}

		$data = json_decode( $request['data'] );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$data = stripslashes( $request['data'] ); // To Fix issue for some users having slashes added.
			$data = json_decode( $data );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				return new WP_Error( 'invalid_json', __( 'Json is invalid.' ), array( 'status' => 400 ) );
			}
		}

		if ( empty( $data ) || ! isset( $data->title ) || ! isset( $data->widgets ) || empty( $data->title ) ) {
			return new WP_Error( 'rest_invalid_request', __( 'Please include all data.' ), array( 'status' => 400 ) );
		}

		$data->id = $key;
		if ( isset( $data->is_new ) ) {
			unset( $data->is_new );
		}

		$appData = APPMAKER_WP_Converter::convert_inAppPage_data( $data, $key );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'invalid_json', __( 'Json is invalid.' ), array( 'status' => 400 ) );
		}

		if ( add_option( $this->getSafeKey( $key ), $data, '', 'no' ) ) {
			add_option( $this->getSafeKey( $key . '_app' ), $appData, '', 'no' );

			$savedKeys = get_option( $this->getSafeKey( $this->inAppPagesKey ), array() );
			$savedKeys = array_merge(
				$savedKeys,
				array(
					$key => array(
						'id'    => $appData['id'],
						'label' => $appData['title'],
						'key'   => $key,
					),
				)
			);
			update_option( $this->getSafeKey( $this->inAppPagesKey ), $savedKeys );

			$request->set_param( 'context', 'edit' );
			$item     = array(
				'key'  => $key,
				'data' => $data,
			);
			$response = $this->prepare_item_for_response( $item, $request );
			$response = rest_ensure_response( $response );
			return $response;
		}

		return new WP_Error( 'rest_item_exists', __( 'Cannot create existing resource.' ), array( 'status' => 400 ) );

	}

	/**
	 * Update a single meta
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$key    = $request['key'];
		$option = get_option( $this->getSafeKey( $key ) );

		if ( ! $option && 'home' === $request['key'] ) {
			$option = array(
				'id'      => 'home',
				'title'   => 'Home',
				'widgets' => array(),
			);
		} elseif ( ! $option && 'home_tab' === $request['key'] ) {
			$option = array(
				'id'      => 'home_tab',
				'title'   => 'Home latest tab',
				'widgets' => array(),
			);		
		} elseif ( ! $option && 'menu' === $request['key'] ) {
			$option = array(
				'id'      => 'menu',
				'title'   => 'Menu',
				'widgets' => array(),
			);
		}

		$data = json_decode( $request['data'] );
		if ( json_last_error() != JSON_ERROR_NONE ) {
			$data = stripslashes( $request['data'] ); // To Fix issue for some users having slashes added.
			$data = json_decode( $data );
			if ( json_last_error() != JSON_ERROR_NONE ) {
				return new WP_Error( 'invalid_json', __( 'Json is invalid.' ), array( 'status' => 400 ) );
			}
		}

		if ( ! isset( $data->title ) || ! isset( $data->widgets ) || empty( $data->title ) ) {
			return new WP_Error( 'rest_invalid_request', __( 'Please include all data.' ), array( 'status' => 400 ) );
		}

		if ( ! $option && ! ( isset( $data->is_new ) && true === $data->is_new ) ) {
			return new WP_Error( 'rest_invalid_key', __( 'Key is not valid.' ), array( 'status' => 404 ) );
		}
		$data->id = $key;
		$saveData = true;
		if ( ( isset( $data->is_new ) && true === $data->is_new ) ) {
			unset( $data->is_new );
			if ( $option != false ) {
				$saveData = false;
			}
		}

		if ( $saveData ) {
			$appData = APPMAKER_WP_Converter::convert_inAppPage_data( $data, $key );
			update_option( $this->getSafeKey( $key ), $data );
			update_option( $this->getSafeKey( $key . '_app' ), $appData );
			$request->set_param( 'context', 'edit' );
		}

		if ( ! ( isset( $data->language ) && 'default' !== $data->language ) && $data->id !== 'menu' ) {
			$savedKeys         = get_option( $this->getSafeKey( $this->inAppPagesKey ), array() );
			$savedKeys[ $key ] = array(
				'id'    => $data->id,
				'label' => $data->title,
				'key'   => $key,
			);
			update_option( $this->getSafeKey( $this->inAppPagesKey ), $savedKeys );
		}

		$cache_key = 'appmaker_wc_' . $this->type;
		if ( ! empty( $key ) ) {
			$cache_key .= '-' . $key;
		}

		delete_transient( $cache_key );

		$item     = array(
			'key'  => $request['key'],
			'data' => $data,
		);
		$response = $this->prepare_item_for_response( $item, $request );
		$response = rest_ensure_response( $response );

		return $response;
	}

	/**
	 * Delete a single meta
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {
		$key    = $this->getSafeKey( $request['key'] );
		$option = get_option( $key );
		if ( ! $option ) {
			return new WP_Error( 'rest_invalid_key', __( 'Key is not invalid.' ), array( 'status' => 404 ) );
		}
		delete_option( $key );

		$savedKeys = get_option( $this->getSafeKey( $this->inAppPagesKey ), array() );
		unset( $savedKeys[ $request['key'] ] );
		update_option( $this->getSafeKey( $this->inAppPagesKey ), $savedKeys );

		$request->set_param( 'context', 'delete' );
		$item     = array(
			'key'  => $request['key'],
			'data' => $option,
		);
		$response = $this->prepare_item_for_response( $item, $request );
		$response = rest_ensure_response( $response );

		return $response;
	}

	/**
	 * Delete a single meta
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function set_as_home( $request ) {
		$page_key = $this->getSafeKey( $request['key'] );
		$page     = get_option( $page_key );

		if ( ! $page ) {
			return new WP_Error( 'rest_invalid_key', __( 'Key is not invalid.' ), array( 'status' => 404 ) );
		}

		$home_key = $this->getSafeKey( 'home' );
		$home     = get_option( $home_key );

		$home->id    = $page_key;
		$home->title = 'Home Backup';

		$page->id    = 'home';
		$page->title = 'Home';

		update_option( $page_key, $home );

		update_option( $home_key, $page );

		$saved_keys                    = get_option( $this->getSafeKey( $this->inAppPagesKey ), array() );
		$saved_keys[ $request['key'] ] = array(
			'id'    => $request['key'],
			'label' => 'Home Backup',
			'key'   => $request['key'],
		);
		update_option( $this->getSafeKey( $this->inAppPagesKey ), $saved_keys );

		$item     = array(
			'key'  => 'home',
			'data' => $page,
		);
		$response = $this->prepare_item_for_response( $item, $request );
		$response = rest_ensure_response( $response );

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

	/**
	 * Return default home page
	 *
	 * @return object
	 */
	public function get_default_home() {

		$args = array(
			'orderby' => 'name',
			'order' => 'ASC',
			'parent' => 0
		);
		$categories = get_categories($args);
		$tabs = array();
		$tabs[0] = <<<JSON
		{
                        "data": {
                            "title": {
                                "value": "Latest",
                                "display_value": "Latest",
                                "label": "Title",
                                "display": true
                            },
                            "image": "",
                            "icon": "",
                            "action": {
                                "value": "OPEN_IN_APP_PAGE",
                                "display_value": "Open In-App Page",
                                "label": "Action",
                                "display": true
                            },
                            "action_value": {
                                "value": "home_tab",
                                "display_value": "Latest tab",
                                "label": "Select In-App Page",
                                "display": true
                            }
                        },
                        "image": ""
		}   
JSON;
$count = 1;
		foreach ($categories as $category) {
			if($count++ > 10) break;
			$category_name = html_entity_decode($category->name);
			$tabs[] = <<<JSON
		{
                        "data": {
                            "title": {
                                "value": "$category_name",
                                "display_value":  "$category_name",
                                "label": "Title",
                                "display": true
                            },
                            "image": "",
                            "icon": "",
							"action": {
                                "value": "LIST_POST",
                                "display_value": "Open Category",
                                "label": "Action",
                                "display": true
                            },
                            "action_value": {
                                "value": $category->term_id,
                                "display_value": "$category_name",
                                "label": "Choose Category",
                                "display": true
                            }
                        },
                        "image": ""
                    } 	
JSON;
		}
		
	   
       	$default_home_json =  '{ "parentID": false, "title": "Home", "language": "default", "type": "TABBED", "widgets": [{ "data": [ ';
		$default_home_json .=  implode(",", $tabs );                                                    
		$default_home_json .= '], "expanded": true, "title": "Tabs","type": "tab", "data_main": { "type": "material-top" }, "key": "widget_key_tab" } ],"id": "home" }';
        return json_decode( $default_home_json );
	}
	
	/**
	 * Return default home page
	 *
	 * @return object
	 */
	public function get_default_home_tab() {
    $default_home_tab = <<<JSON
	{
        "parentID": false,
        "title": "Latest tab",
        "language": "default",
        "type": "NORMAL",
        "widgets": [
            {
                "data": [
                    {
                        "data": {
                            "list_type": {
                                "value": "RECENT",
                                "display_value": "Recent Posts",
                                "label": "Select Post List",
                                "display": true
                            },
                            "id": ""
                        }
                    }
                ],
                "expanded": true,
                "title": "",
                "type": "pre_build_post_list",
                "data_main": {
                    "template": {
                        "value": "post-slider-1",
                        "display_value": "Banner Slider",
                        "label": "Template",
                        "display": true
                    },
                    "item_template": {
                        "value": "template-6",
                        "display_value": "Template 6",
                        "label": "Item template",
                        "display": true
                    },
                    "limit": {
                        "value": "3",
                        "display_value": "3",
                        "label": "Limit",
                        "display": true
                    },
                    "offset": "0",
                    "show_view_more_button": "false",
                    "view_more_button_title": "View More",
                    "action": "",
                    "action_value": ""
                },
                "key": "widget_key_17912"
            },
            {
                "data": [
                    {
                        "data": {
                            "list_type": {
                                "value": "RECENT",
                                "display_value": "Recent Posts",
                                "label": "Select Post List",
                                "display": true
                            },
                            "id": ""
                        }
                    }
                ],
                "expanded": true,
                "title": "Your Daily Read",
                "type": "pre_build_post_list",
                "data_main": {
                    "template": {
                        "value": "post-list-1",
                        "display_value": "Post List",
                        "label": "Template",
                        "display": true
                    },
                    "item_template": {
                        "value": "template-4",
                        "display_value": "Template 4",
                        "label": "Item template",
                        "display": true
                    },
                    "limit": {
                        "value": "6",
                        "display_value": "6",
                        "label": "Limit",
                        "display": true
                    },
                    "offset": {
                        "value": "3",
                        "display_value": "3",
                        "label": "Offset",
                        "display": true
                    },
                    "show_view_more_button": "false",
                    "view_more_button_title": "View More",
                    "action": "",
                    "action_value": ""
                },
                "key": "widget_key_56682"
            },
            {
                "data": [
                    {
                        "data": {
                            "list_type": {
                                "value": "RECENT",
                                "display_value": "Recent Posts",
                                "label": "Select Post List",
                                "display": true
                            },
                            "id": ""
                        }
                    }
                ],
                "expanded": true,
                "title": "Recent updates",
                "type": "pre_build_post_list",
                "data_main": {
                    "template": {
                        "value": "post-scroller-1",
                        "display_value": "Post Scroller",
                        "label": "Template",
                        "display": true
                    },
                    "item_template": {
                        "value": "template-7",
                        "display_value": "Template 7",
                        "label": "Item template",
                        "display": true
                    },
                    "limit": {
                        "value": "5",
                        "display_value": "5",
                        "label": "Limit",
                        "display": true
                    },
                    "offset": {
                        "value": "9",
                        "display_value": "9",
                        "label": "Offset",
                        "display": true
                    },
                    "show_view_more_button": "false",
                    "view_more_button_title": "View More",
                    "action": "",
                    "action_value": ""
                },
                "key": "widget_key_68142"
            },
            {
                "data": [
                    {
                        "data": {
                            "list_type": {
                                "value": "RECENT",
                                "display_value": "Recent Posts",
                                "label": "Select Post List",
                                "display": true
                            },
                            "id": ""
                        }
                    }
                ],
                "expanded": true,
                "title": "Trending today",
                "type": "pre_build_post_list",
                "data_main": {
                    "template": {
                        "value": "post-slider-1",
                        "display_value": "Banner Slider",
                        "label": "Template",
                        "display": true
                    },
                    "item_template": {
                        "value": "template-5",
                        "display_value": "Template 5",
                        "label": "Item template",
                        "display": true
                    },
                    "limit": {
                        "value": "1",
                        "display_value": "1",
                        "label": "Limit",
                        "display": true
                    },
                    "offset": {
                        "value": "14",
                        "display_value": "14",
                        "label": "Offset",
                        "display": true
                    },
                    "show_view_more_button": "false",
                    "view_more_button_title": "View More",
                    "action": "",
                    "action_value": ""
                },
                "key": "widget_key_11872"
            },
            {
                "data": [
                    {
                        "data": {
                            "list_type": {
                                "value": "RECENT",
                                "display_value": "Recent Posts",
                                "label": "Select Post List",
                                "display": true
                            },
                            "id": ""
                        }
                    }
                ],
                "expanded": true,
                "title": "Recent Posts #75181",
                "type": "pre_build_post_list",
                "data_main": {
                    "template": {
                        "value": "post-list-1",
                        "display_value": "Post List",
                        "label": "Template",
                        "display": true
                    },
                    "item_template": {
                        "value": "template-4",
                        "display_value": "Template 4",
                        "label": "Item template",
                        "display": true
                    },
                    "limit": "10",
                    "offset": {
                        "value": "15",
                        "display_value": "15",
                        "label": "Offset",
                        "display": true
                    },
                    "show_view_more_button": "false",
                    "view_more_button_title": "View More",
                    "action": "",
                    "action_value": ""
                },
                "key": "widget_key_94622"
            }
        ],
        "id": "latest_tab"
}
JSON;
        return json_decode( $default_home_tab );
    }

	/**
	 * Return default home page
	 *
	 * @return object
	 */
	public function get_default_menu() {
		$default_menu_json = <<<JSON
    {
        "id": "menu",
        "title": "Menu",
        "widgets": [
            {
                "data": [
                    {
                        "data": {
                            "image": {
                                "value": {
                                    "title": "app-11",
                                    "id": 107,
                                    "url": "https://storage.googleapis.com/appilder_cdn/app_default/navbar_top.png",
                                    "meta": {
                                        "width": 512,
                                        "height": 300
                                    }
                                },
                                "display_value": "https://storage.googleapis.com/appilder_cdn/app_default/navbar_top.png",
                                "label": "Image",
                                "display": true
                            },
                            "action": {
                                "value": "OPEN_URL",
                                "display_value": "Open URL",
                                "label": "Action",
                                "display": true
                            },
                            "action_value": {
                                "value": "#",
                                "display_value": "#",
                                "label": "URL",
                                "display": true
                            }
                        },
                        "image": {
                            "value": {
                                "title": "app-11",
                                "id": 107,
                                "url": "https://storage.googleapis.com/appilder_cdn/app_default/navbar_top.png",
                                "meta": {
                                    "width": 512,
                                    "height": 200
                                }
                            },
                            "display_value": "https://storage.googleapis.com/appilder_cdn/app_default/navbar_top.png",
                            "label": "Image",
                            "display": true
                        }
                    }
                ],
                "expanded": true,
                "title": "Banner title #53581",
                "type": "banner",
                "data_main": {},
                "key": "widget_key_97802"
            },
            {
                "data": [
                    {
                        "data": {
                            "title": {
                                "value": "Home",
                                "display_value": "Home",
                                "label": "Title",
                                "display": true
                            },
                            "image": {
                                "value": {
                                    "title": "app-11",
                                    "id": 107,
                                    "url": "https://img.icons8.com/material/24/000000/home-page.png",
                                    "meta": {
                                        "width": 512,
                                        "height": 512
                                    }
                                },
                                "display_value": "https://img.icons8.com/material/24/000000/home-page.png",
                                "label": "Image",
                                "display": true
                            },
                            "action": {
                                "value": "OPEN_IN_APP_PAGE",
                                "display_value": "Open In-App Page",
                                "label": "Action",
                                "display": true
                            },
                            "action_value": {
                                "value": "home",
                                "display_value": "Home",
                                "label": "Choose In-App Page",
                                "display": true
                            }
                        },
                        "image": {
                            "value": {
                                "title": "app-11",
                                "id": 107,
                                "url": "https://img.icons8.com/material/24/000000/home-page.png",
                                "meta": {
                                    "width": 512,
                                    "height": 512
                                }
                            },
                            "display_value": "https://img.icons8.com/material/24/000000/home-page.png",
                            "label": "Image",
                            "display": true
                        }
                    }
                ],
                "expanded": true,
                "title": "Menu title #33331",
                "type": "menu",
                "data_main": {},
                "key": "widget_key_28342"
            },
            {
                "data": [
                    {
                        "data": {
                            "type": "MENU",
                            "limit": 10,
                            "parent": {
                                "value": "",
                                "display_value": "All",
                                "label": "Parent Category",
                                "display": true
                            }
                        }
                    }
                ],
                "expanded": true,
                "title": "Category List",
                "type": "category_list",
                "data_main": {},
                "key": "widget_key_1852"
            }
        ]
    }
JSON;
		return json_decode( $default_menu_json );
	}
}
