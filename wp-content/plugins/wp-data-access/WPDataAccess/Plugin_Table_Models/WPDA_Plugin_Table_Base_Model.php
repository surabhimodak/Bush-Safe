<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Plugin_Table_Models
 */

namespace WPDataAccess\Plugin_Table_Models {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Plugin_Table_Base_Model
	 *
	 * Base class to handle standard plugin table features
	 *
	 * @author  Peter Schulz
	 * @since   2.6.0
	 */
	class WPDA_Plugin_Table_Base_Model {

		/**
		 * Base table name (without prefixes): MUST BE DEFINED IN SUBCLASS!!!
		 */
		const BASE_TABLE_NAME = null;

		/**
		 * Check if const BASE_TABLE_NAME is defined (cannot proceed without)
		 */
		public static function check_base_table_name() {
			if ( null === static::BASE_TABLE_NAME ) {
				wp_die( __( 'Wrong usage of class WPDA_Plugin_Table_Base_Model [missing BASE_TABLE_NAME]', 'wp-data-access' ) );
			}
		}

		/**
		 * Check if base table exists
		 *
		 * @return bool TRUE = table found
		 */
		public static function table_exists() {
			static::check_base_table_name();

			$wpda_dictionary_exist = new WPDA_Dictionary_Exist( '', static::get_base_table_name() );
			return $wpda_dictionary_exist->table_exists( false );
		}

		/**
		 * Get base table name
		 *
		 * @return string Base table name
		 */
		public static function get_base_table_name() {
			static::check_base_table_name();

			global $wpdb;
			return $wpdb->prefix . static::BASE_TABLE_NAME;
		}

		/**
		 * Return number of records in base table
		 *
		 * @return int
		 */
		public static function count() {
			static::check_base_table_name();

			global $wpdb;
			$query = 'SELECT count(*) AS noitems FROM ' . static::get_base_table_name();
			$result = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			if ( 1 === $wpdb->num_rows ) {
				return $result[0]['noitems'];
			} else {
				return 0;
			}
		}

	}

}