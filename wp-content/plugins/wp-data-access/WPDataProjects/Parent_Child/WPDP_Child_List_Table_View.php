<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Parent_Child
 */

namespace WPDataProjects\Parent_Child {

	/**
	 * Class WPDP_Child_List_Table_View extends WPDP_Child_List_Table
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Child_List_Table_View extends WPDP_Child_List_Table {

		/**
		 * WPDP_Child_List_Table_View constructor
		 *
		 * @param array $args
		 *
		 * @see WPDP_Child_List_Table
		 *
		 */
		public function __construct( $args = [] ) {
			$args['allow_update'] = 'off';

			parent::__construct( $args );

			$this->mode = 'view';
		}

		/**
		 * Overwrites method column_default_add_action
		 *
		 * @param array  $item
		 * @param string $column_name
		 * @param array  $actions
		 *
		 * @see WPDP_Child_List_Table::column_default_add_action()
		 *
		 */
		protected function column_default_add_action( $item, $column_name, &$actions ) { }

	}

}
