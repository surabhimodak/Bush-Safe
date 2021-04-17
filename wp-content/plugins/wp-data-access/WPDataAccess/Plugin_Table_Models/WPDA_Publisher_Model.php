<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Plugin_Table_Models
 */

namespace WPDataAccess\Plugin_Table_Models {

	/**
	 * Class WPDA_Publisher_Model
	 *
	 * Model for plugin table 'publisher'
	 *
	 * @author  Peter Schulz
	 * @since   2.6.0
	 */
	class WPDA_Publisher_Model extends WPDA_Plugin_Table_Base_Model {

		const BASE_TABLE_NAME = 'wpda_publisher';

		/**
		 * Return the publication for a specific publication id
		 *
		 * @param int $pub_id Publication id
		 *
		 * @return bool|array
		 */
		public static function get_publication( $pub_id ) {
			global $wpdb;
			$query =
				$wpdb->prepare( '
							SELECT *
							  FROM ' . self::get_base_table_name() . '
							 WHERE pub_id = %d
						',
					[
						$pub_id,
					]
				); // db call ok; no-cache ok.

			$dataset = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
			return 1 === $wpdb->num_rows ? $dataset : false;
		}

		/**
		 * Return the publication for a specific publication name
		 *
		 * @param int $pub_name Publication name
		 *
		 * @return bool|array
		 */
		public static function get_publication_by_name( $pub_name ) {
			global $wpdb;
			$query =
				$wpdb->prepare( '
							SELECT *
							  FROM ' . self::get_base_table_name() . '
							 WHERE pub_name = %s
						',
					[
						$pub_name,
					]
				); // db call ok; no-cache ok.

			$dataset = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
			return 1 === $wpdb->num_rows ? $dataset : false;
		}

	}

}