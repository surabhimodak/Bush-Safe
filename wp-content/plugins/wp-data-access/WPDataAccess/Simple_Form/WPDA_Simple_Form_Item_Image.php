<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Simple_Form
 */

namespace WPDataAccess\Simple_Form {

	/**
	 * Class WPDA_Simple_Form_Item_Image
	 *
	 * Handles a database column of type image.
	 *
	 * @author  Peter Schulz
	 * @since   2.5.0
	 */
	class WPDA_Simple_Form_Item_Image extends WPDA_Simple_Form_Item_Media {

		/**
		 * WPDA_Simple_Form_Item_Image constructor.
		 *
		 * @param array $args
		 */
		public function __construct( $args = [] ) {
			parent::__construct( $args );

			$this->media_frame_title  = __( 'Upload or select image(s) from your WordPress media library', 'wp-data-access' );
			$this->media_frame_remove = __( 'Remove image(s)', 'wp-data-access' );
			$this->media_types        = 'image';
		}

		public function show_item_media() {
			if ( 'number' === esc_attr( $this->data_type ) ) {
				// Column supports only one media file
				$url = wp_get_attachment_url( esc_attr( $this->item_value ) );
				if ( false !== $url ) {
					?>
					<img src="<?php echo wp_get_attachment_url( esc_attr( $this->item_value ) ); ?>">
					<?php
				}
			} else {
				// Column supports multiple media files
				if ( null !== $this->item_value && '' !== $this->item_value ) {
					$media_ids = explode( ',', $this->item_value );
					foreach ( $media_ids as $media_id ) {
						$url = wp_get_attachment_url( esc_attr( $media_id ) );
						if ( false !== $url ) {
							?>
							<img src="<?php echo $url; ?>">
							<?php
						}
					}
				}
			}
		}

		public function add_media_library_selection() {
			?>
			<script type='text/javascript'>
				function get_selection_<?php echo esc_attr( $this->item_name ); ?>(attachment) {
					var media_container = jQuery('#media_container_<?php echo esc_attr( $this->item_name ); ?>');
					media_container.empty();
					if ('number'==='<?php echo esc_attr( $this->data_type ); ?>') {
						jQuery('#<?php echo esc_attr( $this->item_name ); ?>').val(attachment.id);
						media_container.append('<img src="' + attachment.url + '">');
					} else {
						jQuery('#<?php echo esc_attr( $this->item_name ); ?>').val('');
						for (var i = 0; i < attachment.length; ++i) {
							if (attachment[i].id !== '') {
								if (jQuery('#<?php echo esc_attr( $this->item_name ); ?>').val() === '') {
									jQuery('#<?php echo esc_attr( $this->item_name ); ?>').val(attachment[i].id);
								} else {
									jQuery('#<?php echo esc_attr( $this->item_name ); ?>').val(
										jQuery('#<?php echo esc_attr( $this->item_name ); ?>').val() + ',' + attachment[i].id
									);
								}
								media_container.append('<img src="' + attachment[i].url + '">');
							}
						}
					}
				}
			</script>
			<?php
		}

	}

}