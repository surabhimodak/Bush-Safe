<?php

class APPMAKER_WP_Helper {
	public static function get_id( $object ) {
		if ( method_exists( $object, 'get_id' ) ) {

			return $object->get_id();
		} else {
			return $object->id;
		}
	}

	public static function get_property( $object, $property ) {
		if ( method_exists( $object, 'get_' . $property ) ) {
			return call_user_func( array( $object, 'get_' . $property ) );
		} else {
			return $object->{$property};
		}
	}

	/**
	 * @param WP_Post|string $item
	 * @return array|false
	 */
	public static function get_image_dimensions( $item ) {
		if ( is_string( $item ) ) {
			$id   = attachment_url_to_postid( $item );
			$item = get_post( $id );
		}
		if ( ! is_a( $item, 'WP_Post' ) ) {
			return false;
		}
		$meta = wp_get_attachment_metadata( $item->ID );
		if ( empty( $meta ) ) {
			$attachment_path = get_attached_file( $item->ID );
			$attach_data     = wp_generate_attachment_metadata( $item->ID, $attachment_path );
			wp_update_attachment_metadata( $item->ID, $attach_data );
			// Wrap the data in a response object
			$meta = wp_get_attachment_metadata( $item->ID );
		}
		if ( isset( $meta['width'], $meta['height'] ) ) {

			return array(
				'width'  => $meta['width'],
				'height' => $meta['height'],
			);
		} else {
			return false;
		}
	}

	/**
	 * @param WP_Post $post
	 * @param string  $template
	 *
	 * @return array
	 */
	public static function get_post_widget( $post, $template = 'template-2' ) {
		$GLOBALS['post'] = $post;
		setup_postdata( $post );
		$featured_media_url = get_the_post_thumbnail_url( $post );
		$category = get_the_category( $post->ID );
		$category_text = !empty($category) ? strtoupper($category[0]->name) : "";
		$category_text = html_entity_decode( $category_text );
		$response           =
				array(
					'view'     => 'simple',
					'template' => $template,
					'content'  => self::prepare_excerpt_response( $post->post_excerpt ),
					'title'    => self::decode_html( $post->post_title ),
					"author" => get_the_author_meta( 'display_name', $post->post_author ) . " | " . date("j M Y",strtotime($post->post_date)),
					"category" => $category_text,												
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
				);
		if ( $featured_media_url ) {
			$response['image'] = $featured_media_url;
		}
		return $response;
	}

	public static function decode_html( $string ) {
		return wp_strip_all_tags( html_entity_decode( $string ) );
	}

	public static function prepare_excerpt_response( $excerpt ) {
		if ( post_password_required() ) {
			return __( 'There is no excerpt because this is a protected post.' );
		}

		/** This filter is documented in wp-includes/post-template.php */
		$excerpt = apply_filters( 'the_excerpt', apply_filters( 'get_the_excerpt', $excerpt ) );

		if ( empty( $excerpt ) ) {
			return '';
		}

		return self::decode_html( $excerpt );
	}

	public static function get_recent_posts( $limit = 10, $offset = 0 ) {
		$atts = array(
			'per_page' => $limit,
			'orderby'  => 'date',
			'order'    => 'desc',
		);
		extract( $atts );
		$args = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => $per_page,
			'orderby'             => $orderby,
			'order'               => $order,
			'fields'              => 'id=>parent',
			'offset'              => $offset,
			'tax_query'           => array(),
			'suppress_filters'    => 0,
		);

		$posts            = get_posts( $args );
		$posts_ids        = array_keys( $posts );
		$parent_ids       = array_values( array_filter( $posts ) );
		$return_posts_ids = array_unique( array_merge( $posts_ids, $parent_ids ) );
		return $return_posts_ids;
	}



	public static function get_posts_by_tax( $taxonomy, $tax_id, $limit = 10, $offset = 0 ) {
		$atts = array(
			'orderby' => 'date',
			'order'   => 'desc',
		);

		extract( $atts );

		$args = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'orderby'             => $orderby,
			'order'               => $order,
			'posts_per_page'      => $limit,
			'fields'              => 'id=>parent',
			'offset'              => $offset,
			'tax_query'           => array(
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $tax_id,
					'operator' => 'IN',
				),
			),
			'suppress_filters'    => 0,
		);

		$posts            = get_posts( $args );
		$posts_ids        = array_keys( $posts );
		$parent_ids       = array_values( array_filter( $posts ) );
		$return_posts_ids = array_unique( array_merge( $posts_ids, $parent_ids ) );
		return $return_posts_ids;
	}
}
