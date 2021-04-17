<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Parent_Child
 */

namespace WPDataProjects\Parent_Child {

	/**
	 * Class WPDP_Child_Form_View
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Child_Form_View extends WPDP_Child_Form {

		/**
		 * WPDP_Child_Form_View constructor.
		 *
		 * @param       $schema_name
		 * @param       $table_name
		 * @param       $wpda_list_columns
		 * @param array $args
		 */
		public function __construct( $schema_name, $table_name, $wpda_list_columns, $args = [] ) {
			parent::__construct( $schema_name, $table_name, $wpda_list_columns, $args );

			$this->action = 'view';
		}

	}

}
