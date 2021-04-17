<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Simple_Form
 */

namespace WPDataAccess\Simple_Form {

	class WPDA_Simple_Form_Item_Boolean extends WPDA_Simple_Form_Item {

		protected $checkbox_on    = '1';
		protected $checkbox_label = '';

		/**
		 * WPDA_Simple_Form_Item_Boolean constructor.
		 *
		 * @param array $args
		 */
		public function __construct( $args = [] ) {
			parent::__construct( $args );

			$this->checkbox_label = $this->get_item_label();
			$this->set_label( '' );
			$this->set_item_hide_icon( true );

			if ( isset( $args->checkbox_value_on ) ) {
				$this->checkbox_on = $args->checkbox_value_on;
			}
		}

		/**
		 * Overwrite method
		 */
		protected function show_item() {
			if ( 'new' === $this->show_context_action ) {
				$checked = $this->checkbox_on===$this->item_default_value ? 'checked' : '';
			} else {
				$checked = $this->checkbox_on===$this->item_value ? 'checked' : '';
			}
			?>
			<label>
				<input type="checkbox"
					   id="<?php echo esc_attr( $this->item_name ); ?>_chk"
					   value="<?php echo esc_attr( $this->checkbox_on ); ?>"
					<?php echo $checked; ?>
				/>

				<input type="hidden"
					   id="<?php echo esc_attr( $this->item_name ); ?>"
					   name="<?php echo esc_attr( $this->item_name ); ?>"
				       value="<?php echo esc_attr( $this->item_value ); ?>"
				/>
				<?php echo esc_attr( $this->checkbox_label ); ?>
			</label>
			<script type="text/javascript">
				jQuery('#<?php echo esc_attr( $this->item_name ); ?>_chk').on('click', function() {
					if (jQuery('#<?php echo esc_attr( $this->item_name ); ?>_chk').is(':checked')) {
						jQuery('#<?php echo esc_attr( $this->item_name ); ?>').val('<?php echo esc_attr( $this->checkbox_on ); ?>');
					} else {
						jQuery('#<?php echo esc_attr( $this->item_name ); ?>').val('0');
					}
				});
			</script>
			<?php
		}

	}

}