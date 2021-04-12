<?php

class APPMAKER_WP_REST_Posts_Controller extends APPMAKER_WP_REST_Controller {
	protected $isRoot = false;

	public $post_type;
	public $head   = false;
	public $footer = false;
    private $options;
	public function __construct( $post_type, $rest_base = '' ) {
		parent::__construct();
		$this->post_type = $post_type;
		$obj             = get_post_type_object( $post_type );
		$this->rest_base = ! empty( $rest_base ) ? $rest_base : ( ! empty( $obj->rest_base ) ? $obj->rest_base : $obj->name );

	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'inAppPages/wp/' . $this->rest_base . '/',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'api_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),

				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'inAppPages/wp/' . $this->rest_base . '/(?P<id>[\d]+)',
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

		register_rest_route(
			$this->namespace,
			'inAppPages/wp/' . $this->rest_base . '/(?P<id>[\d]+)/comments',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'add_comment' ),
					'permission_callback' => array( $this, 'api_permissions_check' ),
					'args'                => $this->add_comment_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route( $this->namespace, 'inAppPages/wp/' . $this->rest_base . '/(?P<id>[\d]+)/content', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_content' ),
                'permission_callback' => array( $this, 'api_permissions_check' ),
                'args'                => array(
                    'context' => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            ),

            'schema' => array( $this, 'get_public_item_schema' ),
        ) );

	}

	public function add_comment_params() {
		$params = array();

		$params['author_name']  = array(
			'description'       => __( 'Comment Author name.', 'woocommerce' ),
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
			'required'          => true,
		);
		$params['author_email'] = array(
			'description'       => __( 'Comment Author name.', 'woocommerce' ),
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
			'required'          => true,
		);
		$params['author_url']   = array(
			'description'       => __( 'Comment Author name.', 'woocommerce' ),
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['content']      = array(
			'description'       => __( 'Comment Author name.', 'woocommerce' ),
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
			'required'          => true,
		);
		return $params;
	}

	/**
	 * Get the query params for collections of attachments.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['context']['default'] = 'view';

		$params['after'] = array(
			'description'       => __( 'Limit response to resources published after a given ISO8601 compliant date.' ),
			'type'              => 'string',
			'format'            => 'date-time',
			'validate_callback' => 'rest_validate_request_arg',
		);
		if ( post_type_supports( $this->post_type, 'author' ) ) {
			$params['author']         = array(
				'description'       => __( 'Limit result set to posts assigned to specific authors.' ),
				'type'              => 'array',
				'default'           => array(),
				'items'             => array( 'type' => 'integer' ),
				'sanitize_callback' => 'wp_parse_id_list',
				'validate_callback' => 'rest_validate_request_arg',
			);
			$params['author_exclude'] = array(
				'description'       => __( 'Ensure result set excludes posts assigned to specific authors.' ),
				'type'              => 'array',
				'default'           => array(),
				'items'             => array( 'type' => 'integer' ),
				'sanitize_callback' => 'wp_parse_id_list',
				'validate_callback' => 'rest_validate_request_arg',
			);
		}
		$params['before']   = array(
			'description'       => __( 'Limit response to resources published before a given ISO8601 compliant date.' ),
			'type'              => 'string',
			'format'            => 'date-time',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['per_page'] = array(
			'description'       => __( 'Per page.' ),
			'type'              => 'integer',
			'default'           => 10,
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['exclude']  = array(
			'description'       => __( 'Ensure result set excludes specific ids.' ),
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);
		$params['include']  = array(
			'description'       => __( 'Limit result set to specific ids.' ),
			'type'              => 'array',
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);
		if ( 'page' === $this->post_type || post_type_supports( $this->post_type, 'page-attributes' ) ) {
			$params['menu_order'] = array(
				'description'       => __( 'Limit result set to resources with a specific menu_order value.' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			);
		}
		$params['offset']  = array(
			'description'       => __( 'Offset the result set by a specific number of items.' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['order']   = array(
			'description'       => __( 'Order sort attribute ascending or descending.' ),
			'type'              => 'string',
			'default'           => 'desc',
			'enum'              => array( 'asc', 'desc' ),
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['orderby'] = array(
			'description'       => __( 'Sort collection by object attribute.' ),
			'type'              => 'string',
			'default'           => 'date',
			'enum'              => array(
				'date',
				'id',
				'include',
				'title',
				'slug',
			),
			'validate_callback' => 'rest_validate_request_arg',
		);
		if ( 'page' === $this->post_type || post_type_supports( $this->post_type, 'page-attributes' ) ) {
			$params['orderby']['enum'][] = 'menu_order';
		}

		$post_type_obj = get_post_type_object( $this->post_type );
		if ( $post_type_obj->hierarchical || 'attachment' === $this->post_type ) {
			$params['parent']         = array(
				'description'       => __( 'Limit result set to those of particular parent ids.' ),
				'type'              => 'array',
				'sanitize_callback' => 'wp_parse_id_list',
				'default'           => array(),
			);
			$params['parent_exclude'] = array(
				'description'       => __( 'Limit result set to all items except those of a particular parent id.' ),
				'type'              => 'array',
				'sanitize_callback' => 'wp_parse_id_list',
				'default'           => array(),
			);
		}

		$params['slug']   = array(
			'description'       => __( 'Limit result set to posts with a specific slug.' ),
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['status'] = array(
			'default'           => 'publish',
			'description'       => __( 'Limit result set to posts assigned a specific status.' ),
			'sanitize_callback' => 'sanitize_key',
			'type'              => 'string',
			'validate_callback' => array( $this, 'validate_user_can_query_private_statuses' ),
		);
		$params['filter'] = array(
			'description' => __( 'Use WP Query arguments to modify the response; private query vars require appropriate authorization.' ),
		);

		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );
		foreach ( $taxonomies as $taxonomy ) {
			$base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;

			$params[ $base ] = array(
				'description'       => sprintf( __( 'Limit result set to all items that have the specified term assigned in the %s taxonomy.' ), $base ),
				'type'              => 'array',
				'sanitize_callback' => 'wp_parse_id_list',
				'default'           => array(),
			);
		}

		return $params;
	}

	/**
	 * Get a collection of posts.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$args                        = array();
		$args['author__in']          = $request['author'];
		$args['author__not_in']      = $request['author_exclude'];
		$args['menu_order']          = $request['menu_order'];
		$args['offset']              = $request['offset'];
		$args['order']               = $request['order'];
		$args['orderby']             = $request['orderby'];
		$args['paged']               = $request['page'];
		$args['post__in']            = $request['include'];
		$args['post__not_in']        = $request['exclude'];
		$args['posts_per_page']      = $request['per_page'];
		$args['name']                = $request['slug'];
		$args['post_parent__in']     = $request['parent'];
		$args['post_parent__not_in'] = $request['parent_exclude'];
		$args['post_status']         = 'any';
		$args['s']                   = $request['search'];

		if ( isset( $request['categories'] ) && ( empty( $request['categories'] ) || ( count( $request['categories'] ) === 1 && empty( $request['categories'][0] ) ) ) ) {
			unset( $request['categories'] );
		}

		if ( isset( $request['tags'] ) && ( empty( $request['tags'] ) || ( count( $request['tags'] ) === 1 && empty( $request['tags'][0] ) ) ) ) {
			unset( $request['tags'] );
		}

		$args['date_query'] = array();
		// Set before into date query. Date query must be specified as an array of an array.
		if ( isset( $request['before'] ) ) {
			$args['date_query'][0]['before'] = $request['before'];
		}

		// Set after into date query. Date query must be specified as an array of an array.
		if ( isset( $request['after'] ) ) {
			$args['date_query'][0]['after'] = $request['after'];
		}

		if ( is_array( $request['filter'] ) ) {
			$args = array_merge( $args, $request['filter'] );
			unset( $args['filter'] );
		}

		// Force the post_type argument, since it's not a user input variable.
		$args['post_type'] = $this->post_type;

		/**
		 * Filter the query arguments for a request.
		 *
		 * Enables adding extra arguments or setting defaults for a post
		 * collection request.
		 *
		 * @see https://developer.wordpress.org/reference/classes/wp_user_query/
		 *
		 * @param array $args Key value array of query var to query value.
		 * @param WP_REST_Request $request The request used.
		 */
		$args       = apply_filters( "rest_{$this->post_type}_query", $args, $request );
		$query_args = $this->prepare_items_query( $args, $request );

		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );
		foreach ( $taxonomies as $taxonomy ) {
			$base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;

			if ( ! empty( $request[ $base ] ) ) {
				$query_args['tax_query'][] = array(
					'taxonomy'         => $taxonomy->name,
					'field'            => 'term_id',
					'terms'            => $request[ $base ],
					'include_children' => false,
				);
			}
		}

		$posts_query  = new WP_Query();
		$query_result = $posts_query->query( $query_args );

		$posts = array();
		if ( ! empty( $request['author'] ) &&  ! empty( $request['author'][0] ) ) {
			$author = $request['author'][0];
			$posts[] = array(
				"type" => "author",
				"data" => array(array(
					"backgroundColor" => "black",
					"textColor" => "white",
					"title"=> "PUBLISHED BY",
					"name"=>  get_the_author_meta( 'display_name', $author ),
					"image"=> get_avatar_url( $author ),
					"description"=> get_the_author_meta( 'description', $author )
					))
				);
		}
		foreach ( $query_result as $post ) {
			if ( ! $this->check_read_permission( $post ) ) {
				continue;
			}

			$data    = $this->prepare_item_for_response( $post, $request, false );
			$posts[] = $this->prepare_response_for_collection( $data );
		}

		$page        = (int) $query_args['paged'];
		$total_posts = $posts_query->found_posts;

		if ( $total_posts < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count
			unset( $query_args['paged'] );
			$count_query = new WP_Query();
			$count_query->query( $query_args );
			$total_posts = $count_query->found_posts;
		}

		$max_pages = ceil( $total_posts / (int) $query_args['posts_per_page'] );

		$response = array(
			'id'      => 'posts',
			'title'   => 'Posts',
			'widgets' => $posts,
			'pages'   => $max_pages,
		);

		$response = rest_ensure_response( $response );
		$response->header( 'X-WP-Total', (int) $total_posts );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$request_params = $request->get_query_params();
		if ( ! empty( $request_params['filter'] ) ) {
			// Normalize the pagination params.
			unset( $request_params['filter']['posts_per_page'] );
			unset( $request_params['filter']['paged'] );
		}
		$base = add_query_arg( $request_params, rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ) );

		if ( $page > 1 ) {
			$prev_page = $page - 1;
			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}
			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );
			$response->link_header( 'next', $next_link );
		}

		return $response;
	}

	/**
	 * Get content function.
	 *
	 * @param string  $content post content.
	 * @param WP_Post $post post object.
	 *
	 * @return string
	 */
	public function get_content( $request ) {	

		$post_id = $request['id'];
		$post = get_post($post_id);
		$content = $post->post_content;

		$content            = apply_filters( 'the_content', $content );
		$custom_head        = APPMAKER_WP::$api->get_settings( 'custom_post_head', '' );
		if ( empty( $this->head ) ) {
			ob_start();
			wp_head();
			$this->head = ob_get_clean();
			// $this->head = '';
		}
		if ( empty( $this->footer ) ) {
			ob_start();
			wp_footer();
			$this->footer = ob_get_clean();
			// $this->footer = '';
		}

		$output =  '<!DOCTYPE html>
			' . $this->head . '
			<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=1.0, minimum-scale=1.0, maximum-scale=1.0">
			 <!-- link rel="stylesheet" type="text/css" href="' . get_stylesheet_uri() . '" -->
             <style>   				
				img {
					max-width:100% !important;
				}
				.wp-caption {
					max-width: 100% !important;
				}
			</style>
			' . $custom_head . '
            <body>
			<div style="padding: 15px">
            ' . $content . '
			</div>
            </body>
            ' . $this->footer . '
            </html>
			';
			header('Content-Type:text/html');
			echo $output;exit;
	}

	public function add_comment( $request ) {
		$commentdata = array(
			'comment_post_ID'      => $request['id'],
			'comment_author'       => $request['author_name'],
			'comment_author_email' => $request['author_email'],
			'comment_author_url'   => $request['author_url'],
			'comment_content'      => $request['content'],
			'comment_type'         => '',
			'comment_parent'       => 0,
			'user_id'              => get_current_user_id(),
			'comment_approved'     => 0,
		);

		// Insert new comment and get the comment ID
		$comment_id = wp_insert_comment( $commentdata );
		return array(
			'id'      => $comment_id,
			'status'  => true,
			'message' => 'Your comment is awaiting moderation.',
		);
	}

	/**
	 * Determine the allowed query_vars for a get_items() response and
	 * prepare for WP_Query.
	 *
	 * @param array           $prepared_args
	 * @param WP_REST_Request $request
	 *
	 * @return array          $query_args
	 */
	protected function prepare_items_query( $prepared_args = array(), $request = null ) {

		$valid_vars = array_flip( $this->get_allowed_query_vars() );
		$query_args = array();
		foreach ( $valid_vars as $var => $index ) {
			if ( isset( $prepared_args[ $var ] ) ) {
				/**
				 * Filter the query_vars used in `get_items` for the constructed query.
				 *
				 * The dynamic portion of the hook name, $var, refers to the query_var key.
				 *
				 * @param mixed $prepared_args [ $var ] The query_var value.
				 */
				$query_args[ $var ] = apply_filters( "rest_query_var-{$var}", $prepared_args[ $var ] );
			}
		}

		if ( 'post' !== $this->post_type || ! isset( $query_args['ignore_sticky_posts'] ) ) {
			$query_args['ignore_sticky_posts'] = true;
		}

		if ( 'include' === $query_args['orderby'] ) {
			$query_args['orderby'] = 'post__in';
		}

		return $query_args;
	}

	/**
	 * Get all the WP Query vars that are allowed for the API request.
	 *
	 * @return array
	 */
	protected function get_allowed_query_vars() {
		global $wp;

		/**
		 * Filter the publicly allowed query vars.
		 *
		 * Allows adjusting of the default query vars that are made public.
		 *
		 * @param array  Array of allowed WP_Query query vars.
		 */
		$valid_vars = apply_filters( 'query_vars', $wp->public_query_vars );

		$post_type_obj = get_post_type_object( $this->post_type );
		if ( current_user_can( $post_type_obj->cap->edit_posts ) ) {
			/**
			 * Filter the allowed 'private' query vars for authorized users.
			 *
			 * If the user has the `edit_posts` capability, we also allow use of
			 * private query parameters, which are only undesirable on the
			 * frontend, but are safe for use in query strings.
			 *
			 * To disable anyway, use
			 * `add_filter( 'rest_private_query_vars', '__return_empty_array' );`
			 *
			 * @param array $private_query_vars Array of allowed query vars for authorized users.
			 * }
			 */
			$private    = apply_filters( 'rest_private_query_vars', $wp->private_query_vars );
			$valid_vars = array_merge( $valid_vars, $private );
		}
		// Define our own in addition to WP's normal vars.
		$rest_valid = array(
			'author__in',
			'author__not_in',
			'ignore_sticky_posts',
			'menu_order',
			'offset',
			'post__in',
			'post__not_in',
			'post_parent',
			'post_parent__in',
			'post_parent__not_in',
			'posts_per_page',
			'date_query',
		);
		$valid_vars = array_merge( $valid_vars, $rest_valid );

		/**
		 * Filter allowed query vars for the REST API.
		 *
		 * This filter allows you to add or remove query vars from the final allowed
		 * list for all requests, including unauthenticated ones. To alter the
		 * vars for editors only, {@see rest_private_query_vars}.
		 *
		 * @param array {
		 *    Array of allowed WP_Query query vars.
		 *
		 * @param string $allowed_query_var The query var to allow.
		 * }
		 */
		$valid_vars = apply_filters( 'rest_query_vars', $valid_vars );

		return $valid_vars;
	}

	/**
	 * Check if we can read a post.
	 *
	 * Correctly handles posts with the inherit status.
	 *
	 * @param object $post Post object.
	 *
	 * @return boolean Can we read it?
	 */
	public function check_read_permission( $post ) {
		if ( ! empty( $post->post_password ) && ! $this->check_update_permission( $post ) ) {
			return false;
		}

		$post_type = get_post_type_object( $post->post_type );
		if ( ! $this->check_is_post_type_allowed( $post_type ) ) {
			return false;
		}

		// Can we read the post?
		if ( 'publish' === $post->post_status || current_user_can( $post_type->cap->read_post, $post->ID ) ) {
			return true;
		}

		$post_status_obj = get_post_status_object( $post->post_status );
		if ( $post_status_obj && $post_status_obj->public ) {
			return true;
		}

		// Can we read the parent if we're inheriting?
		if ( 'inherit' === $post->post_status && $post->post_parent > 0 ) {
			$parent = get_post( $post->post_parent );

			return $this->check_read_permission( $parent );
		}

		// If we don't have a parent, but the status is set to inherit, assume
		// it's published (as per get_post_status()).
		if ( 'inherit' === $post->post_status ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a given post type should be viewed or managed.
	 *
	 * @param object|string $post_type
	 *
	 * @return boolean Is post type allowed?
	 */
	protected function check_is_post_type_allowed( $post_type ) {
		if ( ! is_object( $post_type ) ) {
			$post_type = get_post_type_object( $post_type );
		}

		if ( ! empty( $post_type ) && ! empty( $post_type->show_in_rest ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Prepare a single post output for response.
	 *
	 * @param WP_Post         $post Post object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @param bool            $single
	 *
	 * @return WP_REST_Response $data
	 */
	public function prepare_item_for_response( $post, $request, $single = false ) {
		$GLOBALS['post'] = $post;
		setup_postdata( $post );
		if ( ! empty( $post->post_password ) ) {
			$this->prepare_password_response( $post->post_password );
		}

		if ( method_exists( 'WPBMap', 'addAllMappedShortcodes' ) ) {
			WPBMap::addAllMappedShortcodes();
		}

		$featured_media_url = get_the_post_thumbnail_url( $post );

		if ( ! $single ) {
			$data = array(
				'type'     => 'post',
				'view'     => 'simple',
				'template' => 'post-list-1',
				'title'    => $this->decode_html( get_the_title( $post->ID ) ),
				'data'     => array(
					array(
						'view'     => 'simple',
						'template' => 'template-5',
						'title'    => $this->decode_html( get_the_title( $post->ID ) ),
						'content'  => $this->prepare_excerpt_response( $post->post_excerpt ),
						'author'   => get_the_author_meta( 'display_name', $post->post_author ),
						'style'    => array(
							'title'   => array(
								//'fontFamily' => 'AppFont',
							),
							'content' => array(
								//'fontFamily' => 'AppFont',
							),
						),
						'action'   => array(
							'type'   => 'OPEN_IN_APP_PAGE',
							'params' => array(
								'id' => 'wp/posts/' . $post->ID,
							),
						),
					),
				),
			);
			if ( $featured_media_url ) {
				$data['data'][0]['image'] = $featured_media_url;
			}
		} else {
			$data     = array();
			$comments = $this->get_comments( $post );
			$featured_media_url = get_the_post_thumbnail_url( $post, 'full' );
			$category = get_the_category( $post->ID );
			$category_text = !empty($category) ? strtoupper($category[0]->name) . " | " : "";
			$category_text = html_entity_decode( $category_text );
			$this->options = get_option( 'appmaker_wp_settings' );
			$api_key = $this->options['api_key'];
			$base_url =  site_url();   
            $url = $base_url. '/?rest_route=/' . $this->namespace .
				'/inAppPages/wp/' . $this->rest_base . '/' . $post->ID . '/content'.'&api_key='.$api_key ;

			$data[]   =
				array(
					'type' => 'post_detail',
					'data' => array(
						"title" => $this->decode_html( get_the_title( $post->ID ) ),
						"image" => $featured_media_url,
						"author_image" => get_avatar_url( $post->post_author ),
						"author" => strtoupper(get_the_author_meta( 'display_name', $post->post_author )),
						"author_action" => array(
							"type" =>  "OPEN_IN_APP_PAGE",
							"params" => array( 'id' => 'wp/posts?author=' . $post->post_author )
						),
						"category" => $category_text . date("j M Y",strtotime($post->post_date)),												
						'content'          => array(
							'source' => array(
								'title'       => $this->decode_html( get_the_title( $post->ID ) ),
								'source_type' => 'url',
								//'html'        => $this->get_content( $post->post_content, $post ),
								'url'         => $url,
							),
						),					
						'show_comment'     => ! empty( $comments ),
						'show_add_comment' => comments_open( $post->ID ),
						'add_comment'      => array(
							'callback_url' => get_site_url() . '/wp-json/' . $this->namespace .
							'/inAppPages/wp/' . $this->rest_base . '/' . $post->ID . '/comments',
						),
						'comments'         => ! empty( $comments ) ? $comments : array(),
				),
				);
		}

		$data = rest_ensure_response($data);
		/**
		 * Filter the post data for a response.
		 *
		 * The dynamic portion of the hook name, $this->post_type, refers to post_type of the post being
		 * prepared for the response.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WP_Post $post Post object.
		 * @param WP_REST_Request $request Request object.
		 */
		return apply_filters( "rest_prepare_{$this->post_type}", $data, $post, $request );
	}

	protected function get_comments( $post_id, $limit = 10 ) {
		if ( is_a( $post_id, 'WP_REST_Request' ) ) {
			$post = get_post( $post_id['post_id'] );
		} else {
			$post = get_post( $post_id );
		}

		if ( ! is_a( $post, 'WP_Post' ) ) {
			return new WP_Error( 'invalid_post', 'Invalid Post' );
		}

		$args = array(
			'post_id' => $post->ID,
			'number'  => $limit,
			'orderby' => 'comment_date_gmt ',
			'order'   => 'DESC',
			'status'  => 'approve',
		);

		$reviews = get_comments( $args );
		if ( 0 !== $limit ) {
			$reviews = array_slice( $reviews, 0, $limit );
		}
		$data = array();
		foreach ( $reviews as $review ) {
			$data[] = array(
				'type' => 'comment_item',
				'data' => array(
					'id'     => (int) $review->comment_ID,
					'text'   => $review->comment_content,
					'author' => $review->comment_author,
					'image'  => get_avatar_url( $review->comment_author_email ),
				),
			);
		}
		return $data;
	}

	/**
	 * Check the post_date_gmt or modified_gmt and prepare any post or
	 * modified date for single post output.
	 *
	 * @param string      $date_gmt
	 * @param string|null $date
	 *
	 * @return string|null ISO8601/RFC3339 formatted datetime.
	 */
	protected function prepare_date_response( $date_gmt, $date = null ) {
		// Use the date if passed.
		if ( isset( $date ) ) {
			return mysql_to_rfc3339( $date );
		}

		// Return null if $date_gmt is empty/zeros.
		if ( '0000-00-00 00:00:00' === $date_gmt ) {
			return null;
		}

		// Return the formatted datetime.
		return mysql_to_rfc3339( $date_gmt );
	}

	/**
	 * Get the Post's schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->post_type,
			'type'       => 'object',
			/*
			 * Base properties for every Post.
			 */
			'properties' => array(
				'date'         => array(
					'description' => __( "The date the object was published, in the site's timezone." ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit', 'embed' ),
				),
				'date_gmt'     => array(
					'description' => __( 'The date the object was published, as GMT.' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'guid'         => array(
					'description' => __( 'The globally unique identifier for the object.' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'GUID for the object, as it exists in the database.' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'GUID for the object, transformed for display.' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
					),
				),
				'id'           => array(
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'link'         => array(
					'description' => __( 'URL to the object.' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'modified'     => array(
					'description' => __( "The date the object was last modified, in the site's timezone." ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'modified_gmt' => array(
					'description' => __( 'The date the object was last modified, as GMT.' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'password'     => array(
					'description' => __( 'A password to protect access to the post.' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
				),
				'slug'         => array(
					'description' => __( 'An alphanumeric identifier for the object unique to its type.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_title',
					),
				),
				'status'       => array(
					'description' => __( 'A named status for the object.' ),
					'type'        => 'string',
					'enum'        => array_keys( get_post_stati( array( 'internal' => false ) ) ),
					'context'     => array( 'edit' ),
				),
				'type'         => array(
					'description' => __( 'Type of Post for the object.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
			),
		);

		$post_type_obj = get_post_type_object( $this->post_type );
		if ( $post_type_obj->hierarchical ) {
			$schema['properties']['parent'] = array(
				'description' => __( 'The id for the parent of the object.' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
			);
		}

		$post_type_attributes = array(
			'title',
			'editor',
			'author',
			'excerpt',
			'thumbnail',
			'comments',
			'revisions',
			'page-attributes',
			'post-formats',
		);
		$fixed_schemas        = array(
			'post'       => array(
				'title',
				'editor',
				'author',
				'excerpt',
				'thumbnail',
				'comments',
				'revisions',
				'post-formats',
			),
			'page'       => array(
				'title',
				'editor',
				'author',
				'excerpt',
				'thumbnail',
				'comments',
				'revisions',
				'page-attributes',
			),
			'attachment' => array(
				'title',
				'author',
				'comments',
				'revisions',
			),
		);
		foreach ( $post_type_attributes as $attribute ) {
			if ( isset( $fixed_schemas[ $this->post_type ] ) && ! in_array( $attribute, $fixed_schemas[ $this->post_type ] ) ) {
				continue;
			} elseif ( ! in_array( $this->post_type, array_keys( $fixed_schemas ) ) && ! post_type_supports( $this->post_type, $attribute ) ) {
				continue;
			}

			switch ( $attribute ) {

				case 'title':
					$schema['properties']['title'] = array(
						'description' => __( 'The title for the object.' ),
						'type'        => 'object',
						'context'     => array( 'view', 'edit', 'embed' ),
						'properties'  => array(
							'raw'      => array(
								'description' => __( 'Title for the object, as it exists in the database.' ),
								'type'        => 'string',
								'context'     => array( 'edit' ),
							),
							'rendered' => array(
								'description' => __( 'HTML title for the object, transformed for display.' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
						),
					);
					break;

				case 'editor':
					$schema['properties']['content'] = array(
						'description' => __( 'The content for the object.' ),
						'type'        => 'object',
						'context'     => array( 'view', 'edit' ),
						'properties'  => array(
							'raw'      => array(
								'description' => __( 'Content for the object, as it exists in the database.' ),
								'type'        => 'string',
								'context'     => array( 'edit' ),
							),
							'rendered' => array(
								'description' => __( 'HTML content for the object, transformed for display.' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
							),
						),
					);
					break;

				case 'author':
					$schema['properties']['author'] = array(
						'description' => __( 'The id for the author of the object.' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit', 'embed' ),
					);
					break;

				case 'excerpt':
					$schema['properties']['excerpt'] = array(
						'description' => __( 'The excerpt for the object.' ),
						'type'        => 'object',
						'context'     => array( 'view', 'edit', 'embed' ),
						'properties'  => array(
							'raw'      => array(
								'description' => __( 'Excerpt for the object, as it exists in the database.' ),
								'type'        => 'string',
								'context'     => array( 'edit' ),
							),
							'rendered' => array(
								'description' => __( 'HTML excerpt for the object, transformed for display.' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),
							),
						),
					);
					break;

				case 'thumbnail':
					$schema['properties']['featured_media'] = array(
						'description' => __( 'The id of the featured media for the object.' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
					);
					break;

				case 'comments':
					$schema['properties']['comment_status'] = array(
						'description' => __( 'Whether or not comments are open on the object.' ),
						'type'        => 'string',
						'enum'        => array( 'open', 'closed' ),
						'context'     => array( 'view', 'edit' ),
					);
					$schema['properties']['ping_status']    = array(
						'description' => __( 'Whether or not the object can be pinged.' ),
						'type'        => 'string',
						'enum'        => array( 'open', 'closed' ),
						'context'     => array( 'view', 'edit' ),
					);
					break;

				case 'page-attributes':
					$schema['properties']['menu_order'] = array(
						'description' => __( 'The order of the object in relation to other object of its type.' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
					);
					break;

				case 'post-formats':
					$schema['properties']['format'] = array(
						'description' => __( 'The format for the object.' ),
						'type'        => 'string',
						'enum'        => array_values( get_post_format_slugs() ),
						'context'     => array( 'view', 'edit' ),
					);
					break;

			}
		}

		if ( 'post' === $this->post_type ) {
			$schema['properties']['sticky'] = array(
				'description' => __( 'Whether or not the object should be treated as sticky.' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
			);
		}

		if ( 'page' === $this->post_type ) {
			$schema['properties']['template'] = array(
				'description' => __( 'The theme file to use to display the object.' ),
				'type'        => 'string',
				'enum'        => array_keys( wp_get_theme()->get_page_templates() ),
				'context'     => array( 'view', 'edit' ),
			);
		}

		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );
		foreach ( $taxonomies as $taxonomy ) {
			$base                          = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;
			$schema['properties'][ $base ] = array(
				'description' => sprintf( __( 'The terms assigned to the object in the %s taxonomy.' ), $taxonomy->name ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
			);
		}

		return $this->add_additional_fields_schema( $schema );
	}

	protected function prepare_password_response( $password ) {
		if ( ! empty( $password ) ) {
			/**
			 * Fake the correct cookie to fool post_password_required().
			 * Without this, get_the_content() will give a password form.
			 */
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$hasher                                 = new PasswordHash( 8, true );
			$value                                  = $hasher->HashPassword( $password );
			$_COOKIE[ 'wp-postpass_' . COOKIEHASH ] = wp_slash( $value );
		}

		return $password;
	}

	/**
	 * Check the post excerpt and prepare it for single post output.
	 *
	 * @param string $excerpt
	 *
	 * @return string|null $excerpt
	 */
	protected function prepare_excerpt_response( $excerpt ) {
		if ( post_password_required() ) {
			return __( 'There is no excerpt because this is a protected post.' );
		}

		/** This filter is documented in wp-includes/post-template.php */
		$excerpt = apply_filters( 'the_excerpt', apply_filters( 'get_the_excerpt', $excerpt ) );

		if ( empty( $excerpt ) ) {
			return '';
		}

		return $this->decode_html( $excerpt );
	}

	/**
	 * Get a single post.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$id   = (int) $request['id'];
		$post = get_post( $id );

		if ( empty( $id ) || empty( $post->ID ) || ( 'page' !== $post->post_type && $this->post_type !== $post->post_type ) ) {
			return new WP_Error( 'rest_post_invalid_id', __( 'Invalid post id.' ), array( 'status' => 404 ) );
		}

		if ( ! $this->check_read_permission( $post ) ) {
			return new WP_Error( 'rest_forbidden_status', __( 'Status is forbidden' ), array( 'status' => rest_authorization_required_code() ) );
		}

		$data     = $this->prepare_item_for_response( $post, $request, true );
		$posts  = $this->prepare_response_for_collection( $data );
		$response = array(
			'id'      => 'posts-' . $post->ID,
			'title'   => $post->post_title,
			'widgets' => $posts,
			'pages'   => 1,
		);

		$response = rest_ensure_response( $response );

		if ( is_post_type_viewable( get_post_type_object( $post->post_type ) ) ) {
			$response->link_header( 'alternate', get_permalink( $id ), array( 'type' => 'text/html' ) );
		}

		return $response;
	}

	/**
	 * Validate whether the user can query private statuses
	 *
	 * @param  mixed           $value
	 * @param  WP_REST_Request $request
	 * @param  string          $parameter
	 *
	 * @return WP_Error|boolean
	 */
	public function validate_user_can_query_private_statuses( $value, $request, $parameter ) {
		if ( 'publish' === $value ) {
			return true;
		}
		$post_type_obj = get_post_type_object( $this->post_type );
		if ( current_user_can( $post_type_obj->cap->edit_posts ) ) {
			return true;
		}

		return new WP_Error( 'rest_forbidden_status', __( 'Status is forbidden' ), array( 'status' => rest_authorization_required_code() ) );
	}

	/**
	 * Determine validity and normalize provided status param.
	 *
	 * @param string $post_status
	 * @param object $post_type
	 *
	 * @return WP_Error|string $post_status
	 */
	protected function handle_status_param( $post_status, $post_type ) {

		switch ( $post_status ) {
			case 'draft':
			case 'pending':
				break;
			case 'private':
				if ( ! current_user_can( $post_type->cap->publish_posts ) ) {
					return new WP_Error( 'rest_cannot_publish', __( 'Sorry, you are not allowed to create private posts in this post type' ), array( 'status' => rest_authorization_required_code() ) );
				}
				break;
			case 'publish':
			case 'future':
				if ( ! current_user_can( $post_type->cap->publish_posts ) ) {
					return new WP_Error( 'rest_cannot_publish', __( 'Sorry, you are not allowed to publish posts in this post type' ), array( 'status' => rest_authorization_required_code() ) );
				}
				break;
			default:
				if ( ! get_post_status_object( $post_status ) ) {
					$post_status = 'draft';
				}
				break;
		}

		return $post_status;
	}
}
