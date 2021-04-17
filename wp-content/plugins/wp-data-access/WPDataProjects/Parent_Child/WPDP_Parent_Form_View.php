<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Parent_Child
 */

namespace WPDataProjects\Parent_Child {

	/**
	 * Class WPDP_Parent_Form_View extends WPDP_Parent_Form
	 *
	 * @see WPDP_Parent_Form
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Parent_Form_View extends WPDP_Parent_Form {

		/**
		 * Overwrites WPDP_Parent_Form_View constructor
		 *
		 * Show the parent form in view mode
		 *
		 * @param       $schema_name
		 * @param       $table_name
		 * @param       $wpda_list_columns
		 * @param array $args
		 * @param array $relationship
		 */
		public function __construct( $schema_name, $table_name, &$wpda_list_columns, $args = [], $relationship = [] ) {
			$this->edit_form_class  = 'WPDataProjects\\Parent_Child\\WPDP_Child_Form_View';
			$this->list_table_class = 'WPDataProjects\\Parent_Child\\WPDP_Child_List_Table_View';

			parent::__construct( $schema_name, $table_name, $wpda_list_columns, $args, $relationship );

			$this->mode = 'view';
		}

		/**
		 * Set child action to view
		 */
		protected function set_child_action() {
			$this->child_action = 'view';
		}

	}

}
