<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\List_Table
 */

namespace WPDataProjects\List_Table {

	/**
	 * Class WPDP_List_Table
	 *
	 * Overwrites WPDP_List_Table_Lookup. Disables insert, update, delete and import depending on given arguments.
	 *
	 * @see WPDP_List_Table_Lookup
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_List_Table extends WPDP_List_Table_Lookup {

		protected $image_columns      = [];
		protected $attachment_columns = [];

		/**
		 * WPDP_List_Table constructor
		 *
		 * @param array $args See WPDA_List_Table for argument list
		 */
		public function __construct( array $args = [] ) {
			if ( isset( $args['where_clause'] ) && '' !== $args['where_clause'] ) {
				$this->where = $args['where_clause'];
			}

			if ( isset( $args['orderby_clause'] ) && '' !== $args['orderby_clause'] ) {
				$this->orderby = $args['orderby_clause'];
			}

			parent::__construct( $args );

			// Save image and attachment items in named array for quick access
			if ( null != $this->column_options_listtable ) {
				foreach ( $this->column_options_listtable as $column_option ) {
					if ( isset( $column_option->item_type ) ) {
						if ( 'image' === $column_option->item_type ) {
							$this->image_columns[ $column_option->column_name ] = true;
						}
						if ( 'attachment' === $column_option->item_type ) {
							$this->attachment_columns[ $column_option->column_name ] = true;
						}
					}
				}
			}
		}

		/**
		 * Overwrite method to process images and attachments
		 *
		 * @param array  $item
		 * @param string $column_name
		 *
		 * @return mixed|string
		 */
		public function column_default( $item, $column_name ) {
			// Is item an image?
			if ( isset( $this->image_columns[ $column_name ] ) ) {
				if ( null === $item[ $column_name ] || '' === $item[ $column_name ] ) {
					return '';
				}

				$image_ids = explode( ',', $item[ $column_name ] );
				$image_src = '';

				foreach ( $image_ids as $image_id ) {
					$url = wp_get_attachment_url( esc_attr( $image_id ) );
					if ( false !== $url ) {
						$image_src .= '' !== $image_src ? '<br/>' : '';
						$image_src .= sprintf( '<img src="%s" width="100%%">', $url );
					}
				}

				return $image_src;
			}

			// Is item an attachment attachment?
			if ( isset( $this->attachment_columns[ $column_name ] ) ) {
				if ( null === $item[ $column_name ] || '' === $item[ $column_name ] ) {
					return '';
				}

				$media_ids   = explode( ',', $item[ $column_name ] );
				$media_links = '';

				foreach ( $media_ids as $media_id ) {
					$url = wp_get_attachment_url( esc_attr( $media_id ) );
					if ( false !== $url ) {
						$mime_type = get_post_mime_type( $media_id );
						if ( false !== $mime_type ) {
							$mime_ext = strpos( $mime_type, '/' );
							if ( false !== $mime_ext ) {
								$mime_type = substr( $mime_type, $mime_ext + 1 );
							}
							$title       = get_the_title( esc_attr( $media_id ) );
							$media_links .= '' !== $media_links ? '<br/>' : '';
							$media_links .= sprintf( '<span class="dashicons dashicons-external"></span><a href="%s" title="%s" class="wpda_tooltip" target="_blank">%s</a>', $url, $title, $mime_type );
						}
					}
				}

				return $media_links;
			}

			return parent::column_default( $item, $column_name );
		}

	}

}