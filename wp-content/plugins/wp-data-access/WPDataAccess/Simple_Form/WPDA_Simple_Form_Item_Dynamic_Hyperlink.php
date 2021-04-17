<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Simple_Form
 */

namespace WPDataAccess\Simple_Form {

	/**
	 * Class WPDA_Simple_Form_Item_Dynamic_Hyperlink
	 *
	 * Add dynamic (read-only) hyperlink to form (taken from table settings: dynamic hyperlinks).
	 * Allows substitution of column values.
	 *
	 * @author  Peter Schulz
	 * @since   3.0.3
	 */
	class WPDA_Simple_Form_Item_Dynamic_Hyperlink extends WPDA_Simple_Form_Item {

		protected $hyperlink_label  = '';
		protected $hyperlink_target = '';
		protected $hyperlink_html   = '';

		/**
		 * WPDA_Simple_Form_Item_Dynamic_Hyperlink constructor.
		 *
		 * @param array $args
		 */
		public function __construct( $args = [] ) {
			parent::__construct( $args );

			$this->hyperlink_label  = isset( $args['hyperlink_label'] ) ? $args['hyperlink_label'] : '';
			$this->hyperlink_target = isset( $args['hyperlink_target'] ) ? $args['hyperlink_target'] : false;
			$this->hyperlink_html   = isset( $args['hyperlink_html'] ) ? $args['hyperlink_html'] : '';

			$this->set_label( $this->hyperlink_label );

			$this->item_hide_icon = true;
		}

		/**
		 * Overwrite method
		 */
		protected function show_item() {
			if ( false !== strpos( ltrim( $this->hyperlink_html ), '&lt;') ) {
				echo html_entity_decode( $this->hyperlink_html );
			} else {
				$target = true === $this->hyperlink_target ? "target='_blank'" : '';
				echo "<a href='{$this->hyperlink_html}' {$target}>{$this->hyperlink_label}</a>";
			}

		}

	}

}