<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Plugin_Table_Models
 */

namespace WPDataAccess\Plugin_Table_Models {

	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Media_Model
	 *
	 * Model for plugin table 'table_settings'
	 *
	 * @author  Peter Schulz
	 * @since   2.6.0
	 */
	class WPDA_Media_Model extends WPDA_Plugin_Table_Base_Model {

		const BASE_TABLE_NAME = 'wpda_media';

		/**
		 * Holds all defined media columns
		 *
		 * @var null|array
		 */
		protected static $pooled_media_columns = null;

		/**
		 * Indicates if pool was already setup
		 *
		 * @var bool
		 */
		protected static $pool_setup = false;

		/**
		 * Set up media column pool
		 *
		 * Gets all defined media columns and stores them in a two dimensional named array
		 */
		public static function setup_pool() {
			if ( ! self::$pool_setup ) {
				global $wpdb;

				$query = 'select * from ' . self::get_base_table_name() . ' ' .
				         'where media_activated = \'Yes\'';
				$media = $wpdb->get_results( $query, 'ARRAY_A' );

				foreach ( $media as $m ) {
					if ( '' === $m['media_schema_name'] ) {
						$schema_name = $wpdb->dbname;
					} else {
						$schema_name = $m['media_schema_name'];
					}
					self::$pooled_media_columns[ $schema_name ][ $m['media_table_name'] ][ $m['media_column_name'] ] = $m['media_type'];
				}

				self::$pool_setup = true;
			}
		}

		/**
		 * Returns pool (all media columns)
		 *
		 * @return array|null
		 */
		public static function get_pool() {
			if ( ! self::$pool_setup ) {
				self::setup_pool();
			}

			return self::$pooled_media_columns;
		}

		/**
		 * Returns the media type for a specific database schema/table/column
		 *
		 * Returns false if no media type is assigned to the database schema/table/column
		 *
		 * @param $table_name
		 * @param $column_name
		 * @param $schema_name
		 *
		 * @return bool|string
		 */
		public static function get_column_media( $table_name, $column_name, $schema_name = '' ) {
			if ( ! self::$pool_setup ) {
				self::setup_pool();
			}

			global $wpdb;
			if ( '' === $schema_name ) {
				$schema_name = $wpdb->dbname;
			}

			if ( isset( self::$pooled_media_columns[ $schema_name ][ $table_name ] [ $column_name ] ) ) {
				return self::$pooled_media_columns[ $schema_name ][ $table_name ] [ $column_name ];
			} else {
				return false;
			}
		}

		/**
		 * Insert a record in the base table
		 *
		 * @param string $table_name Media table name
		 * @param string $column_name Media column name
		 * @param string $media_type Media type
		 * @param string $media_activated Media activated?
		 * @param string $schema_name Schema name
		 *
		 * @return bool TRUE = Insert was successful
		 */
		public static function insert( $table_name, $column_name, $media_type, $media_activated = 'Yes', $schema_name = '' ) {
			global $wpdb;
			if ( '' === $schema_name ) {
				$schema_name = $wpdb->dbname;
			}

			return ( 1 === $wpdb->insert(
					static::get_base_table_name(),
					[
						'media_schema_name' => $schema_name,
						'media_table_name'  => $table_name,
						'media_column_name' => $column_name,
						'media_type'        => $media_type,
						'media_activated'   => $media_activated,
					]
				)
			);
		}

		/**
		 * Update a record in the base table
		 *
		 * @param string $table_name Media table name
		 * @param string $column_name Media column name
		 * @param string $media_type Media type
		 * @param string $schema_name Schema name
		 *
		 * @return mixed Transaction status
		 */
		public static function update( $table_name, $column_name, $media_type, $schema_name = '' ) {
			global $wpdb;
			if ( '' === $schema_name ) {
				$schema_name = $wpdb->dbname;
			}

			return $wpdb->update(
				static::get_base_table_name(),
				[
					'media_type' => $media_type,
				],
				[
					'media_schema_name' => $schema_name,
					'media_table_name'  => $table_name,
					'media_column_name' => $column_name,
				]
			);
		}

		/**
		 * Delete a record fro the base table
		 *
		 * @param string $table_name Media table name
		 * @param string $column_name Media column name
		 * @param string $schema_name Schema name
		 *
		 * @return mixed Transaction status
		 */
		public static function delete( $table_name, $column_name, $schema_name = '' ) {
			global $wpdb;
			if ( '' === $schema_name ) {
				$schema_name = $wpdb->dbname;
			}

			return $wpdb->delete(
				static::get_base_table_name(),
				[
					'media_schema_name' => $schema_name,
					'media_table_name'  => $table_name,
					'media_column_name' => $column_name,
				]
			);
		}

	}

}