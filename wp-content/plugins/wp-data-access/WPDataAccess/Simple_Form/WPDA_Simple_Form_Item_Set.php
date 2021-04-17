<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Simple_Form
 */

namespace WPDataAccess\Simple_Form {

	use WPDataAccess\Utilities\WPDA_Message_Box;

	/**
	 * Class WPDA_Simple_Form_Item_Set
	 *
	 * Handles a database column of type set and shows them as a listbox which allows multiple selections.
	 *
	 * @author  Peter Schulz
	 * @since   2.5.0
	 */
	class WPDA_Simple_Form_Item_Set extends WPDA_Simple_Form_Item {

		/**
		 * WPDA_Simple_Form_Item_Set constructor.
		 *
		 * @param array $args
		 */
		public function __construct( $args = [] ) {
			parent::__construct( $args );

			$this->data_type      = 'set';
			$this->item_hide_icon = true;
		}

		/**
		 * Overwrite method show_item: create listbox from set
		 */
		protected function show_item() {
			if ( $this->is_key_column && ! $this->show_context_update_keys_allowed ) {
				// PROBLEM
				// Key columns are set to readonly. This will not work for listboxes.
				// Therefor listboxes need to be set to disabled. Disabled values however
				// are not available in a post ($_POST/$_REQUEST).
				// SOLUTION
				// Disable listbox (see JS when document is loaded) and add a hidden field
				// holding the key value (HERE).
				?>
				<input type="hidden"
					   name="<?php echo esc_attr( $this->item_name ); ?>"
					   value="<?php echo esc_attr( $this->item_value ); ?>"
				/>
				<?php
			}

			// Enum column: show values in listbox.
			?>
			<select name="<?php echo esc_attr( $this->item_name ); ?>[]"
			        id="<?php echo esc_attr( $this->item_name ); ?>"
					class="<?php echo esc_attr( $this->show_context_class_primary_key ); ?><?php echo esc_attr( $this->item_class ); ?>"
			multiple size=5
			<?php echo esc_attr( $this->show_context_item_events ); ?>
			>
			<?php
			$enum_options    = $this->item_enum_options;
			$i               = 0;
			$list_values     = [];
			if ( 'new' === $this->show_context_action ) {
				$get_list_values = [ $this->item_default_value ];
			} else {
				$get_list_values = explode( ',', $this->item_value );
			}
			foreach ( $get_list_values as $get_list_value ) {
				$list_values[ $get_list_value ] = true;
			}

			if ( is_array( $this->item_enum ) ) {
				foreach ( $this->item_enum as $value ) {
					$selected = isset( $list_values[ '' !== $enum_options ? $enum_options[ $i ] : $value ] ) ? ' selected' : '';
					?>
					<option value="<?php echo esc_attr( '' !== $enum_options ? $enum_options[ $i ] : $value ); ?>"<?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $value ); ?></option>
					<?php
					$i ++;
				}
			}
			?>
			</select>
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

			if ( 'YES' === $this->is_nullable && ( '' === $this->item_value || null === $this->item_value ) ){
				return true;
			}

			if ( 'set' === substr( $this->column_type, 0, 3 ) ) {
				// Get set from MySQL table.
				$allowed_values = explode(
					',',
					str_replace(
						'\'',
						'',
						substr( substr( $this->column_type, 4 ), 0, - 1 )
					)
				);
				$entered_values = explode( ',', $this->item_value );

				// Check if all values are in set
				foreach ( $entered_values as $entered_value ) {
					$value_found = false;
					foreach ( $allowed_values as $allowed_value ) {
						if ( $allowed_value === $entered_value ) {
							$value_found = true; // Value allowed.
						}
					}
					if ( ! $value_found ) {
						// Value not in enum: inform user and set validation to failed.
						$msg = new WPDA_Message_Box(
							[
								'message_text'           => 'Value for ' .
								                            str_replace( '_', ' ', $this->item_name ) .
								                            ' ' . __( 'not allowed', 'wp-data-access' ),
								'message_type'           => 'error',
								'message_is_dismissible' => false,
							]
						);
						$msg->box();

						return false;
					}
				}
			} else {
				// TODO: how can I test the values of a manually created set?
			}

			return true;
		}

	}

}
