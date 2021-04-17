<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Plugin_Table_Models
 */

namespace WPDataAccess\Plugin_Table_Models {

	/**
	 * Class WPDP_Project_Model
	 *
	 * Model for plugin table 'wpda_project'
	 *
	 * @author  Peter Schulz
	 * @since   2.6.0
	 */
	class WPDP_Project_Model extends WPDA_Plugin_Table_Base_Model {

		const BASE_TABLE_NAME = 'wpda_project';

		/**
		 * Method overwritten for different table name handling
		 *
		 * @return string Table name
		 */
		public static function get_base_table_name() {
			static::check_base_table_name();

			global $wpdb;
			return $wpdb->prefix . static::BASE_TABLE_NAME;
		}

	}

}