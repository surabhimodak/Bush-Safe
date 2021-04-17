<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Simple_Form
 */

namespace WPDataAccess\Simple_Form {

	/**
	 * Class WPDA_Simple_Form_Item_Hyperlink
	 *
	 * Database column is handled as a hyperlink: allow to enter a valid hyperlink in data entry form (shown as
	 * hyperlink in list table)
	 *
	 * @author  Peter Schulz
	 * @since   2.5.0
	 */
	class WPDA_Simple_Form_Item_Hyperlink extends WPDA_Simple_Form_Item {

		protected $hyperlink_label  = '';
		protected $hyperlink_url    = '';
		protected $hyperlink_target = '';

		/**
		 * WPDA_Simple_Form_Item_Hyperlink constructor.
		 *
		 * @param array $args
		 */
		public function __construct( $args = [] ) {
			parent::__construct( $args );

			if ( '' !== $this->item_value ) {
				$hyperlink = json_decode( $this->item_value, true );
				if ( isset( $hyperlink['label'] ) ) {
					$this->hyperlink_label = $hyperlink['label'];
				}
				if ( isset( $hyperlink['url'] ) ) {
					$this->hyperlink_url = $hyperlink['url'];
				}
				if ( isset( $hyperlink['target'] ) ) {
					$this->hyperlink_target = $hyperlink['target'];
				}
			}

			$this->item_icon_type = 'hyperlink';
		}

		/**
		 * Overwrite method
		 *
		 * @param string $action
		 * @param string $update_keys_allowed
		 */
		public function show( $action, $update_keys_allowed ) {
			parent::show( $action, $update_keys_allowed );

			?>
			<tr id="<?php echo esc_attr( $this->item_name ); ?>_hyperlink" style="display:none;">
				<td></td>
				<td>
					<table cellpadding="0" cellspacing="0"
						   style="width:100%;border-spacing:0;border-collapse: collapse;">
						<tr>
							<td style="width:1%;white-space:nowrap;">
								<label for="<?php echo esc_attr( $this->item_name ); ?>_label">Link Text&nbsp;</label>
							</td>
							<td style="width:100%;">
								<input type="text"
								       id="<?php echo esc_attr( $this->item_name ); ?>_label"
								       name="<?php echo esc_attr( $this->item_name ); ?>_label"
									   value="<?php echo $this->hyperlink_label; ?>"
									   style="width:100%;"
								/>
							</td>
						</tr>
						<tr>
							<td>
								<label for="<?php echo esc_attr( $this->item_name ); ?>_url">URL</label>
							</td>
							<td>
								<input type="text"
								       id="<?php echo esc_attr( $this->item_name ); ?>_url"
								       name="<?php echo esc_attr( $this->item_name ); ?>_url"
								       value="<?php echo $this->hyperlink_url; ?>"
									   style="width:100%;"
								/>
							</td>
						</tr>
						<tr>
							<td></td>
							<td>
								<label>
									<input type="checkbox"
										   id="<?php echo esc_attr( $this->item_name ); ?>_target"
										   name="<?php echo esc_attr( $this->item_name ); ?>_target"
										   <?php echo '_blank' === $this->hyperlink_target ? 'checked' : ''; ?>
									/>
									Open link in a new tab
								</label>
							</td>
						</tr>
					</table>
				</td>
				<td></td>
			</tr>
			<?php
		}

		/**
		 * Overwrite method
		 */
		protected function show_item() {
			?>
			<span id="<?php echo esc_attr( $this->item_name ); ?>_textlink"
			      style="vertical-align:middle;font-weight:bold;cursor:pointer;">
				<?php echo esc_attr( $this->hyperlink_label ); ?>
			</span>

			<script type='text/javascript'>
				jQuery(function () {
					jQuery('#<?php echo esc_attr( $this->item_name ); ?>_textlink').on('click', function () {
						jQuery('#<?php echo esc_attr( $this->item_name ); ?>_hyperlink').toggle();
					});
					jQuery('#<?php echo esc_attr( $this->item_name ); ?>_icon').on('click', function () {
						jQuery('#<?php echo esc_attr( $this->item_name ); ?>_hyperlink').toggle();
					});
				});
			</script>

			<input type="hidden"
			       name="<?php echo esc_attr( $this->item_name ); ?>"
			       value="<?php echo $this->show_context_column_value; ?>"
			       class="wpda_hyperlink"
			/>
			<?php
		}

	}

}