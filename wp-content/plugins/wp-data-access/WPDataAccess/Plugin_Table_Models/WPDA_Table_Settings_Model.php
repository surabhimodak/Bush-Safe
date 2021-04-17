<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Plugin_Table_Models
 */

namespace WPDataAccess\Plugin_Table_Models {

	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Table_Settings_Model
	 *
	 * Model for plugin table 'table_settings'
	 *
	 * @author  Peter Schulz
	 * @since   2.6.0
	 */
	class WPDA_Table_Settings_Model extends WPDA_Plugin_Table_Base_Model {

		const BASE_TABLE_NAME = 'wpda_table_settings';

		public static function query( $table_name, $schema_name ) {
			global $wpdb;
			if ( '' === $schema_name ) {
				$schema_name = $wpdb->dbname;
			}

			return $wpdb->get_results(
				$wpdb->prepare(
					'
		              SELECT wpda_table_settings
		                FROM ' . static::get_base_table_name() . ' 
		               WHERE wpda_schema_name = %s
		                 AND wpda_table_name = %s
		            ',
					[
						$schema_name,
						$table_name,
					]
				)
				, 'ARRAY_A'
			);
		}

		/**
		 * Insert a record in the base table
		 *
		 * @param string $table_name Table name
		 * @param string $table_settings Table settings
		 * @param string $schema_name Schema name
		 *
		 * @return bool TRUE = Insert was successful
		 */
		public static function insert( $table_name, $table_settings, $schema_name ) {
			global $wpdb;

			return ( 1 === $wpdb->insert(
					static::get_base_table_name(),
					[
						'wpda_schema_name'    => $schema_name,
						'wpda_table_name'     => $table_name,
						'wpda_table_settings' => $table_settings,
					]
				)
			);
		}

		/**
		 * Update a record in the base table
		 *
		 * @param string $table_name Table name
		 * @param string $table_settings Table settings
		 * @param string $schema_name Schema name
		 *
		 * @return mixed Transaction status
		 */
		public static function update( $table_name, $table_settings, $schema_name ) {
			global $wpdb;

			return $wpdb->update(
				static::get_base_table_name(),
				[
					'wpda_table_settings' => $table_settings,
				],
				[
					'wpda_schema_name' => $schema_name,
					'wpda_table_name'  => $table_name,
				]
			);
		}

	}

}