<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Plugin_Table_Models
 */

namespace WPDataAccess\Plugin_Table_Models {

	/**
	 * Class WPDP_Page_Model
	 *
	 * Model for plugin table 'wpda_project_page'
	 *
	 * @author  Peter Schulz
	 * @since   2.6.0
	 */
	class WPDP_Page_Model extends WPDA_Plugin_Table_Base_Model {

		const BASE_TABLE_NAME = 'wpda_project_page';

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

		/**
		 * Get project page
		 *
		 * @param $project_id
		 * @param $page_id
		 *
		 * @return mixed
		 */
		public static function get_page( $project_id, $page_id ) {
			static::check_base_table_name();

			global $wpdb;
			$query =
				$wpdb->prepare(
					'select * from ' . static::get_base_table_name() .
					' where project_id = %d and page_id = %d',
					[
						$project_id,
						$page_id,
					]
				);

			return $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
		}

		/**
		 * Get project page
		 *
		 * @param $page_id
		 *
		 * @return mixed
		 */
		public static function get_page_from_page_id( $page_id ) {
			static::check_base_table_name();

			global $wpdb;
			$query =
				$wpdb->prepare(
					'select * from ' . static::get_base_table_name() .
					' where page_id = %d',
					[
						$page_id,
					]
				);

			return $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
		}

	}

}