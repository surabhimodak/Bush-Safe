<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Simple_Form
 */

namespace WPDataAccess\Simple_Form {

	/**
	 * Class WPDA_Simple_Form_Item_Media
	 *
	 * Handles a database column of type media.
	 *
	 * @author  Peter Schulz
	 * @since   2.5.0
	 */
	class WPDA_Simple_Form_Item_Media extends WPDA_Simple_Form_Item {

		protected $media_frame_title;
		protected $media_frame_button;
		protected $media_frame_remove;

		protected $media_types = ''; // Use comma separated list (empty = all)

		/**
		 * WPDA_Simple_Form_Item_Media constructor.
		 *
		 * @param array $args
		 */
		public function __construct( $args = [] ) {
			parent::__construct( $args );

			$this->item_hide_icon = true;

			$this->media_frame_title  = __( 'Upload or select media from your WordPress media library', 'wp-data-access' );
			$this->media_frame_remove = __( 'Remove media', 'wp-data-access' );
			$this->media_types        = '';
			$this->media_frame_button = __( 'Select', 'wp-data-access' );
		}

		/**
		 * Overwrite method show_item: create media item to interact with the WordPress media library
		 *
		 * When adding a new media item type, modify the following methods:
		 * (1) show_item_media()
		 * (2) add_media_library_selection()
		 *
		 * If you change this method all media items will be affected!
		 */
		public function show_item() {
			?>
			<div id="media_container_<?php echo esc_attr( $this->item_name ); ?>">
				<?php
				$this->show_item_media();
				?>
			</div>
			<?php
			if ( 'view' !== $this->show_context_action ) {
				$this->add_media_library_interaction();
				$this->add_media_library_selection();
			}
		}

		/**
		 * Uses the media id to the media
		 *
		 * Overwrite this method for every new media item.
		 */
		protected function show_item_media() {
			if ( 'number' === esc_attr( $this->data_type ) ) {
				// Column supports only one media file
				$url = wp_get_attachment_url( esc_attr( $this->item_value ) );
				if ( false !== $url ) {
					$icon  = wp_mime_type_icon( esc_attr( $this->item_value ) );
					$title = get_the_title( esc_attr( $this->item_value ) );
					?>
					<span style="float:left;padding:10px;text-align:center;">
						<a href="<?php echo $url; ?>" target="_blank">
							<img src="<?php echo $icon; ?>">
							<br/>
							<?php echo $title; ?>
						</a>
					</span>
					<?php
				}
			} else {
				// Column supports multiple media files
				if ( null !== $this->item_value && '' !== $this->item_value ) {
					$media_ids = explode( ',', $this->item_value );
					foreach ( $media_ids as $media_id ) {
						$url   = wp_get_attachment_url( esc_attr( $media_id ) );
						if ( false !== $url ) {
							$icon  = wp_mime_type_icon( esc_attr( $media_id ) );
							$title = get_the_title( esc_attr( $media_id ) );
							?>
							<span style="float:left;padding:10px;text-align:center;">
								<a href="<?php echo $url; ?>" target="_blank">
									<img src="<?php echo $icon; ?>">
									<br/>
									<?php echo $title; ?>
								</a>
							</span>
							<?php
						}
					}
				}
			}
		}

		/**
		 * Uses the selected media id(s) to update the table media column
		 *
		 * Overwrite this method for every new media item.
		 */
		protected function add_media_library_selection() {
			?>
			<script type='text/javascript'>
				function get_selection_<?php echo esc_attr( $this->item_name ); ?>(attachment) {
					var media_container = jQuery('#media_container_<?php echo esc_attr( $this->item_name ); ?>');
					media_container.empty();
					if ('number' === '<?php echo esc_attr( $this->data_type ); ?>') {
						jQuery('#<?php echo esc_attr( $this->item_name ); ?>').val(attachment.id);
						media_container.append('<span style="float:left;padding:10px;text-align:center;"><a href="' + attachment.url + '" target="_blank"><img src="' + attachment.icon + '"><br/>' + attachment.title + '</a></span>');
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
								media_container.append('<span style="float:left;padding:10px;text-align:center;"><a href="' + attachment[i].url + '" target="_blank"><img src="' + attachment[i].icon + '"><br/>' + attachment[i].title + '</a></span>');
							}
						}
					}
				}
			</script>
			<?php
		}

		/**
		 * Adds media library interaction to media item
		 *
		 * This method:
		 * (1) Adds a hidden item holding the media id(s).
		 * (2) Adds an upload button to start the interaction with the media library.
		 * (3) Adds a remove button to remove the media.
		 * (4) Adds a JS function which implements the selection of media in the media library.
		 *     Uses the hidden media item to select media.
		 * (5) Adds a JS function which removes the media.
		 *     Uses the hidden media item to remove the media.
		 * (6) Adds an on click event to the button previously created.
		 *     Opens the WordPress media library and selects the media id(s) taken from the hidden media item.
		 *
		 * All created items have uniques names to prevent issues between them.
		 *
		 * If you change this method all media items will be affected!
		 */
		protected function add_media_library_interaction() {
			?>
			<input type="hidden"
				   name="<?php echo esc_attr( $this->item_name ); ?>"
				   value="<?php echo esc_attr( $this->item_value ); ?>"
				   id="<?php echo esc_attr( $this->item_name ); ?>"
			/>
			<div style="clear:both;">
				<a id="<?php echo esc_attr( $this->item_name ); ?>_upload_button"
				   href="javascript:void(0)"
				   class="button">
					<?php echo $this->media_frame_title; ?>
				</a>
				<a id="<?php echo esc_attr( $this->item_name ); ?>_remove_button"
				   href="javascript:void(0)"
				   class="button">
					<?php echo $this->media_frame_remove; ?>
				</a>
			</div>
			<script type='text/javascript'>
				var frame_<?php echo esc_attr( $this->item_name ); ?>;

				function set_selection_<?php echo esc_attr( $this->item_name ); ?>() {
					selection = frame_<?php echo esc_attr( $this->item_name ); ?>.state().get('selection');
					media_ids = jQuery("#<?php echo esc_attr( $this->item_name ); ?>").val();
					media_ids = media_ids.split(',');
					media_ids.forEach(
						function (id) {
							attachment = wp.media.attachment(id);
							attachment.fetch();
							selection.add(attachment ? [attachment] : []);
						}
					);
				}

				jQuery(function () {
					jQuery('#<?php echo esc_attr( $this->item_name ); ?>_upload_button').on('click', function () {
						if (frame_<?php echo esc_attr( $this->item_name ); ?>) {
							set_selection_<?php echo esc_attr( $this->item_name ); ?>();
							frame_<?php echo esc_attr( $this->item_name ); ?>.open();
						} else {
							frame_<?php echo esc_attr( $this->item_name ); ?> = wp.media.frames.frame_<?php echo esc_attr( $this->item_name ); ?> = wp.media({
								title: '<?php echo $this->media_frame_title; ?>',
								button: {
									text: '<?php echo $this->media_frame_button; ?>'
								},
								library: {
									type: ['<?php echo implode( '\',\'', explode( ',', $this->media_types ) ); ?>']
								},
								multiple: <?php echo 'number' === $this->data_type ? 'false' : 'true'; ?>
							});
							frame_<?php echo esc_attr( $this->item_name ); ?>.on('select', function () {
								if ('number' === '<?php echo esc_attr( $this->data_type ); ?>') {
									attachment = frame_<?php echo esc_attr( $this->item_name ); ?>.state().get('selection').first().toJSON();
								} else {
									attachment = frame_<?php echo esc_attr( $this->item_name ); ?>.state().get('selection').toJSON();
								}
								get_selection_<?php echo esc_attr( $this->item_name ); ?>(attachment);
							});
							frame_<?php echo esc_attr( $this->item_name ); ?>.open();
							set_selection_<?php echo esc_attr( $this->item_name ); ?>();
						}
					});

					jQuery('#<?php echo esc_attr( $this->item_name ); ?>_remove_button').on('click', function () {
						jQuery('#media_container_<?php echo esc_attr( $this->item_name ); ?>').empty();
						jQuery('#<?php echo esc_attr( $this->item_name ); ?>').val('');
					});
				});
			</script>
			<?php
		}

		/**
		 * Overwrite method
		 *
		 * @param $pre_insert
		 *
		 * @return bool
		 */
		public function is_valid( $pre_insert = false ) {
			if ( ! parent::is_valid( $pre_insert ) ) {
				return false;
			}

			// TODO: check if the media id exists

			return true;
		}

	}

}