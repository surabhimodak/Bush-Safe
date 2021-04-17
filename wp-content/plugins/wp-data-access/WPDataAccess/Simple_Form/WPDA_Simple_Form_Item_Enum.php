<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Simple_Form
 */

namespace WPDataAccess\Simple_Form {

	use WPDataAccess\Utilities\WPDA_Message_Box;

	/**
	 * Class WPDA_Simple_Form_Item_Enum
	 *
	 * Handles a database column of type enum and shows them as a listbox.
	 *
	 * @author  Peter Schulz
	 * @since   2.5.0
	 */
	class WPDA_Simple_Form_Item_Enum extends WPDA_Simple_Form_Item {

		/**
		 * WPDA_Simple_Form_Item_Enum constructor.
		 *
		 * @param array $args
		 */
		public function __construct( $args = [] ) {
			parent::__construct( $args );

			$this->data_type      = 'enum';
			$this->item_hide_icon = true;
		}

		/**
		 * Overwrite method show_item: create listbox from enum
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
			<select name="<?php echo esc_attr( $this->item_name ); ?>"
					id="<?php echo esc_attr( $this->item_name ); ?>"
					class="<?php echo esc_attr( $this->show_context_class_primary_key ); ?> <?php echo esc_attr( $this->item_class ); ?>"
				<?php echo esc_attr( $this->show_context_item_events ); ?>
			>
				<?php
				$enum_options                     = $this->item_enum_options;
				$i                                = 0;
				$list_values                      = [];
				$list_values[ $this->item_value ] = true;
				if ( 'new' === $this->show_context_action ) {
					// Check if there is a default value.
					if ( $this->item_default_value !== null &&
					     strtolower( $this->item_default_value ) !== 'null' ) {
						$list_values[ $this->item_default_value ] = true;
					}
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

			if ( 'enum' === substr( $this->column_type, 0, 4 ) ) {
				// Get enum from MySQL table.
				$allowed_values = explode(
					',',
					str_replace(
						'\'',
						'',
						substr( substr( $this->column_type, 5 ), 0, - 1 )
					)
				);
				$value_found    = false;
				// Check if value is in enum.
				foreach ( $allowed_values as $allowed_value ) {
					if ( $allowed_value === $this->item_value ) {
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
			} else {
				// TODO: how can I test the values of a manually created enum?
			}

			return true;
		}

	}

}
