<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Simple_Form
 */

namespace WPDataAccess\Simple_Form {

	/**
	 * Class WPDA_Simple_Form_Type_Icon
	 *
	 * Displays an icon presenting the data type of a {@see WPDA_Simple_Form_Item}.
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_Simple_Form_Type_Icon {

		/**
		 * Item name
		 *
		 * @var string
		 */
		protected $item_name;

		/**
		 * Item data type
		 *
		 * @var string
		 */
		protected $data_type;

		/**
		 * WPDA_Simple_Form_Type_Icon constructor
		 *
		 * Sets the item data type.
		 *
		 * @param string $item_name Item name.
		 * @param string $data_type Item data type.
		 *
		 * @since   1.0.0
		 *
		 */
		public function __construct( $item_name, $data_type ) {

			$this->item_name = $item_name;
			$this->data_type = $data_type;

		}

		/**
		 * Show data type icon
		 *
		 * For our SIMPLE form we have four data types:
		 * + Number (all numeric items)
		 * + Date
		 * + Time
		 * + String (all other items...)
		 *
		 * @since   1.0.0
		 */
		public function show() {

			switch ( $this->data_type ) {

				case 'number':
					echo '<span class="wpda_data_type">123</span>';
					break;

				case 'hyperlink':
					echo '<span id="' . esc_attr( $this->item_name ) . '_icon" class="dashicons dashicons-admin-links wpda_data_type_icon" style="cursor:pointer;"></span>';
					break;

				case 'date':
					echo '<span class="dashicons dashicons-calendar-alt wpda_data_type_icon"></span>';
					break;

				case 'time':
					echo '<span class="dashicons dashicons-clock wpda_data_type_icon"></span>';
					break;

				default:
					echo '<span class="wpda_data_type">abc</span>';

			}

		}

	}

}
