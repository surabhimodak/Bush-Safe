<?php

class APPMAKER_WP_REST_BACKEND_MEDIA_Controller extends APPMAKER_WP_REST_Controller {

	protected $type;

	protected $isRoot = true;


	public function __construct() {
		parent::__construct();

		$this->rest_base = 'backend-media';
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
					'callback'            => array( $this, 'search_media' ),
					'permission_callback' => array( $this, 'api_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'api_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

	}



	/**
	 * Search media
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function search_media( $request ) {
		$args = array(
			'post_type'   => 'attachment',
			'post_status' => array( 'publish', 'inherit' ),
		);

		if ( isset( $request['search'] ) ) {
			$args['s'] = $request['search'];
		}

		$q           = new WP_Query( $args );
		$attachments = array();
		/** @var WP_Post $attachment */
		foreach ( $q->get_posts() as $attachment ) {
			$response      = $this->prepare_item_for_response( $attachment, $request );
			$attachments[] = array(
				'id'    => $response,
				'label' => $attachment->post_title,
			);
		}
		return rest_ensure_response( $attachments );
	}

	/**
	 * Create a single meta
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {
		if ( ! empty( $request['post'] ) && in_array(
			get_post_type( $request['post'] ),
			array(
				'revision',
				'attachment',
			)
		)
		) {
			return new WP_Error( 'rest_invalid_param', __( 'Invalid parent type.' ), array( 'status' => 400 ) );
		}

		// Get the file via $_FILES or raw data
		$files   = $request->get_file_params();
		$headers = $request->get_headers();

		if ( ! empty( $files ) ) {
			$file = $this->upload_from_file( $files, $headers );
		} else {
			$file = $this->upload_from_data( $request->get_body(), $headers );
		}
		if ( is_wp_error( $file ) ) {
			return $file;
		}
		$name       = basename( $file['file'] );
		$name_parts = pathinfo( $name );
		$name       = trim( substr( $name, 0, - ( 1 + strlen( $name_parts['extension'] ) ) ) );
		$url        = $file['url'];
		$type       = $file['type'];
		$file       = $file['file'];
		// use image exif/iptc data for title and caption defaults if possible
        // @codingStandardsIgnoreStart
        // $image_meta = @wp_read_image_metadata($file);
        // @codingStandardsIgnoreEnd
		// if (!empty($image_meta)) {
		//    if (empty($request['title']) && trim($image_meta['title']) && !is_numeric(sanitize_title($image_meta['title']))) {
		//        $request['title'] = $image_meta['title'];
		//    }
		//    if (empty($request['caption']) && trim($image_meta['caption'])) {
		//        $request['caption'] = $image_meta['caption'];
		//    }
		// }
		$attachment       = $this->prepare_item_for_database( $request );
		$attachment->file = $file;

		$attachment->post_mime_type = $type;
		$attachment->guid           = $url;
		if ( empty( $attachment->post_title ) ) {
			$attachment->post_title = preg_replace( '/\.[^.]+$/', '', basename( $file ) );
		}

		// Insert the attachment.
		$id = wp_insert_attachment( $attachment, $attachment->post_title );

		if ( is_wp_error( $id ) ) {
			if ( in_array( $id->get_error_code(), array( 'db_update_error' ) ) ) {
				$id->add_data( array( 'status' => 500 ) );
			} else {
				$id->add_data( array( 'status' => 400 ) );
			}

			return $id;
		}
		$attachment = get_post( $id );
		$response   = $this->prepare_item_for_response( $attachment, $request );
		$response   = rest_ensure_response( $response );
		return $response;
	}

	protected function upload_from_file( $files, $headers ) {

		if ( empty( $files ) ) {
			return new WP_Error( 'rest_upload_no_data', __( 'No data supplied' ), array( 'status' => 400 ) );
		}
		// Verify hash, if given
		if ( ! empty( $headers['CONTENT_MD5'] ) ) {
			$expected = trim( $headers['CONTENT_MD5'] );
			$actual   = md5_file( $files['file']['tmp_name'] );
			if ( $expected !== $actual ) {
				return new WP_Error( 'rest_upload_hash_mismatch', __( 'Content hash did not match expected' ), array( 'status' => 412 ) );
			}
		}
		// Pass off to WP to handle the actual upload
		$overrides = array(
			'test_form' => false,
		);
		// Bypasses is_uploaded_file() when running unit tests
		if ( defined( 'DIR_TESTDATA' ) && DIR_TESTDATA ) {
			$overrides['action'] = 'wp_handle_mock_upload';
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		$file = wp_handle_upload( $files['file'], $overrides );
		if ( isset( $file['error'] ) ) {
			return new WP_Error( 'rest_upload_unknown_error', $file['error'], array( 'status' => 500 ) );
		}

		return $file;
	}

	/**
	 * Handle an upload via raw POST data
	 *
	 * @param array $data Supplied file data
	 * @param array $headers HTTP headers from the request
	 *
	 * @return array|WP_Error Data from {@see wp_handle_sideload()}
	 */
	protected function upload_from_data( $data, $headers ) {

		if ( empty( $data ) ) {
			return new WP_Error( 'rest_upload_no_data', __( 'No data supplied' ), array( 'status' => 400 ) );
		}
		if ( empty( $headers['content_type'] ) ) {
			return new WP_Error( 'rest_upload_no_content_type', __( 'No Content-Type supplied' ), array( 'status' => 400 ) );
		}
		if ( empty( $headers['content_disposition'] ) ) {
			return new WP_Error( 'rest_upload_no_content_disposition', __( 'No Content-Disposition supplied' ), array( 'status' => 400 ) );
		}
		$filename = $this->get_filename_from_disposition( $headers['content_disposition'] );
		if ( empty( $filename ) ) {
			return new WP_Error( 'rest_upload_invalid_disposition', __( 'Invalid Content-Disposition supplied. Content-Disposition needs to be formatted as `attachment; filename="image.png"` or similar.' ), array( 'status' => 400 ) );
		}
		if ( ! empty( $headers['content_md5'] ) ) {
			$content_md5 = array_shift( $headers['content_md5'] );
			$expected    = trim( $content_md5 );
			$actual      = md5( $data );
			if ( $expected !== $actual ) {
				return new WP_Error( 'rest_upload_hash_mismatch', __( 'Content hash did not match expected' ), array( 'status' => 412 ) );
			}
		}
		// Get the content-type
		$type = array_shift( $headers['content_type'] );
		/** Include admin functions to get access to wp_tempnam() and wp_handle_sideload() */
		require_once ABSPATH . 'wp-admin/includes/admin.php';
		// Save the file
		$tmpfname = wp_tempnam( $filename );
		$fp       = fopen( $tmpfname, 'w+' );
		if ( ! $fp ) {
			return new WP_Error( 'rest_upload_file_error', __( 'Could not open file handle' ), array( 'status' => 500 ) );
		}
		fwrite( $fp, $data );
		fclose( $fp );
		// Now, sideload it in
		$file_data  = array(
			'error'    => null,
			'tmp_name' => $tmpfname,
			'name'     => $filename,
			'type'     => $type,
		);
		$overrides  = array(
			'test_form' => false,
		);
		$sideloaded = wp_handle_sideload( $file_data, $overrides );
		if ( isset( $sideloaded['error'] ) ) {
            // @codingStandardsIgnoreStart
            @unlink( $tmpfname );

            // @codingStandardsIgnoreEnd
			return new WP_Error( 'rest_upload_sideload_error', $sideloaded['error'], array( 'status' => 500 ) );
		}

		return $sideloaded;
	}

	/**
	 * Parse filename from a Content-Disposition header value.
	 *
	 * As per RFC6266:
	 *
	 *     content-disposition = "Content-Disposition" ":"
	 *                            disposition-type *( ";" disposition-parm )
	 *
	 *     disposition-type    = "inline" | "attachment" | disp-ext-type
	 *                         ; case-insensitive
	 *     disp-ext-type       = token
	 *
	 *     disposition-parm    = filename-parm | disp-ext-parm
	 *
	 *     filename-parm       = "filename" "=" value
	 *                         | "filename*" "=" ext-value
	 *
	 *     disp-ext-parm       = token "=" value
	 *                         | ext-token "=" ext-value
	 *     ext-token           = <the characters in token, followed by "*">
	 *
	 * @see http://tools.ietf.org/html/rfc2388
	 * @see http://tools.ietf.org/html/rfc6266
	 *
	 * @param string[] $disposition_header List of Content-Disposition header values.
	 *
	 * @return string|null Filename if available, or null if not found.
	 */
	public static function get_filename_from_disposition( $disposition_header ) {
		// Get the filename
		$filename = null;
		foreach ( $disposition_header as $value ) {
			$value = trim( $value );
			if ( strpos( $value, ';' ) === false ) {
				continue;
			}
			list( $type, $attr_parts ) = explode( ';', $value, 2 );
			$attr_parts                = explode( ';', $attr_parts );
			$attributes                = array();
			foreach ( $attr_parts as $part ) {
				if ( strpos( $part, '=' ) === false ) {
					continue;
				}
				list( $key, $value )        = explode( '=', $part, 2 );
				$attributes[ trim( $key ) ] = trim( $value );
			}
			if ( empty( $attributes['filename'] ) ) {
				continue;
			}
			$filename = trim( $attributes['filename'] );
			// Unquote quoted filename, but after trimming.
			if ( substr( $filename, 0, 1 ) === '"' && substr( $filename, - 1, 1 ) === '"' ) {
				$filename = substr( $filename, 1, - 1 );
			}
		}

		return $filename;
	}

	/**
	 * Prepare the item for the REST response.
	 *
	 * @param WP_Post $item WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return array $response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$meta = wp_get_attachment_metadata( $item->ID );
		if ( empty( $meta ) ) {
			$attachment_path = get_attached_file( $item->ID );
			if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
				require_once ABSPATH . 'wp-admin/includes/image.php';
			}
			$attach_data = wp_generate_attachment_metadata( $item->ID, $attachment_path );
			wp_update_attachment_metadata( $item->ID, $attach_data );
			// Wrap the data in a response object
			$meta = wp_get_attachment_metadata( $item->ID );
		}
		$response = array(
			'title' => $item->post_title,
			'id'    => $item->ID,
			'url'   => $item->guid,
			'meta'  => array(
				'width'  => $meta['width'],
				'height' => $meta['height'],
			),
		);
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
					'context'     => array( 'view' ),

				),
				'key'  => array(
					'description' => __( 'Key for that data .' ),
					'type'        => 'string',
					'context'     => array( 'view' ),

				),

			),
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
