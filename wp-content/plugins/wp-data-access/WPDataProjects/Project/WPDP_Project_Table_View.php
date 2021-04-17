<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Project
 */

namespace WPDataProjects\Project {

	use \WPDataAccess\List_Table\WPDA_List_View;

	/**
	 * Class WPDP_Project_Table_View extends WPDA_List_View
	 *
	 * @see WPDA_List_View
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Project_Table_View extends WPDA_List_View {

		/**
		 * Overwrites method show to add extra actions
		 */
		public function show() {
			// Add datetimepicker
			wp_enqueue_style( 'datetimepicker' );
			wp_enqueue_script( 'datetimepicker' );

			if ( 'reconcile' === $this->action || 'reverse_engineering' === $this->action ) {
				$this->display_edit_form();
			} else {
				parent::show();
			}
		}

	}

}