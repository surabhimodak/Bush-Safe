<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Data_Dictionary
 */

namespace WPDataAccess\Data_Dictionary {

	use WPDataAccess\Connection\WPDADB;

	/**
	 * Class WPDA_Dictionary_Lists
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_Dictionary_Lists {

		/**
		 * List of tables for setting pages
		 *
		 * Returns an array including all tables and views in the WordPress database.
		 *
		 * Do NOT use table access control here! This list is used in the settings forms and must show ALL tables in
		 * the WordPress database.
		 *
		 * @param string  $schema_name Database schema name
		 * @param boolean $show_views TRUE = show views, FALSE = hide views.
		 *
		 * @return array List of database tables (and views).
		 * @since   1.0.0
		 *
		 */
		public static function get_tables( $show_views = true, $schema_name = '' ) {
			$wpdadb = WPDADB::get_db_connection( $schema_name );
			if ( $wpdadb === null ) {
				return [];
			}

			if ( false === $show_views ) {
				$and = " and table_type != 'VIEW' ";
			} else {
				$and = '';
			}

			$query = $wpdadb->prepare( "
				select table_name AS table_name,
					   create_time AS create_time,
					   table_rows AS table_rows
				  from information_schema.tables
				 where table_schema = %s
				 $and
				 order by table_name
				",
				[
					$wpdadb->dbname,
				]
			);

			return $wpdadb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
		}

		/**
		 * List all table in a specific schema
		 *
		 * jQuery usage: action=wpda_get_tables
		 *
		 * @return array
		 */
		public static function get_tables_ajax() {
			if ( isset( $_REQUEST['wpdaschema_name'] ) ) {
				$wpdadb = WPDADB::get_db_connection( sanitize_text_field( wp_unslash( $_REQUEST['wpdaschema_name'] ) ) );

				if ( $wpdadb === null ) {
					echo json_encode( [] );
				} else {
					$query = $wpdadb->prepare( "
					select table_name AS table_name
					  from information_schema.tables
					 where table_schema = %s
					 order by table_name
					",
						[
							$wpdadb->dbname,
						]
					);

					echo json_encode( $wpdadb->get_results( $query, 'ARRAY_A' ) ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
				}
			} else {
				echo json_encode( [] );
			}
		}

		/**
		 * List of columns for a specific table
		 *
		 * jQuery usage: action=wpda_get_columns
		 *
		 * @return array List of column for a specific table.
		 * @since   1.6.10
		 *
		 */
		public static function get_columns() {
			if ( isset( $_REQUEST['wpdaschema_name'] ) && isset( $_REQUEST['table_name'] ) ) {
				$schema_name = isset( $_REQUEST['wpdaschema_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpdaschema_name'] ) ) : ''; // input var okay.
				$table_name  = sanitize_text_field( wp_unslash( $_REQUEST['table_name'] ) ); // input var okay.

				$wpdadb      = WPDADB::get_db_connection( $schema_name );
				if ( $wpdadb === null ) {
					echo json_encode( [] );
				} else {
					$query = $wpdadb->prepare(
						'
				  SELECT column_name AS column_name
					FROM information_schema.columns 
				   WHERE table_schema = %s
					 AND table_name   = %s
				   ORDER BY ordinal_position
				',
						[
							$wpdadb->dbname,
							$table_name,
						]
					);

					echo json_encode( $wpdadb->get_results( $query, 'ARRAY_A' ) ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
				}
			} else {
				echo json_encode( [] );
			}
		}

		/**
		 * List of columns for a specific table
		 *
		 * @param string $table_name Database table name
		 * @param string $schema_name Database schema name
		 *
		 * @return Column in $table_name
		 * @since   2.0.10
		 *
		 */
		public static function get_table_columns( $table_name, $schema_name ) {
			$wpdadb = WPDADB::get_db_connection( $schema_name );
			if ( $wpdadb === null ) {
				return [];
			} else {
				$query = $wpdadb->prepare(
					'
				  SELECT column_name AS column_name
					FROM information_schema.columns 
				   WHERE table_schema = %s
					 AND table_name   = %s
				   ORDER BY ordinal_position
				',
					[
						$wpdadb->dbname,
						$table_name,
					]
				);

				return $wpdadb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
			}
		}

		/**
		 * List of database schemas available to user
		 *
		 * @param bool $incl_remote_dbs Include remote database connections? (for backward compatibility)
		 *
		 * @return array
		 *
		 * @since   1.6.0
		 */
		public static function get_db_schemas( $incl_remote_dbs = true ) {
			global $wpdb;
			$schemas = $wpdb->get_results(
				'SELECT schema_name AS schema_name
				   FROM information_schema.schemata 
				  ORDER BY schema_name'
				, 'ARRAY_A'
			);

			if ( $incl_remote_dbs ) {
				$rdb = WPDADB::get_remote_databases();
				foreach ( $rdb as $key => $val ) {
					$schemas[] = [
						'schema_name' => $key
					];
				}
			}

			return $schemas;
		}

		/**
		 * List of available engines
		 *
		 * @param string $schema_name Database schema name (default = WordPress schema)
		 *
		 * @return array
		 *
		 * @since   1.6.0
		 */
		public static function get_engines( $schema_name = '' ) {
			$wpdadb = WPDADB::get_db_connection( $schema_name );
			if ( $wpdadb === null ) {
				return [];
			} else {
				return $wpdadb->get_results(
					'SELECT engine AS engine,
							support AS support
					   FROM information_schema.engines
					 ORDER BY engine'
					, 'ARRAY_A'
				);
			}
		}

		/**
		 * List of available collations
		 *
		 * @param string $schema_name Database schema name (default = WordPress schema)
		 *
		 * @return array
		 *
		 * @since   1.6.0
		 */
		public static function get_collations( $schema_name = '' ) {
			$wpdadb = WPDADB::get_db_connection( $schema_name );
			if ( $wpdadb === null ) {
				return [];
			} else {
				return $wpdadb->get_results(
					'SELECT character_set_name AS character_set_name, 
							collation_name AS collation_name
					 FROM   information_schema.collations
					 ORDER BY character_set_name, collation_name'
					, 'ARRAY_A'
				);
			}
		}

		/**
		 * Returns default collation
		 *
		 * @param string $schema_name Database schema name (default = WordPress schema)
		 *
		 * @return array
		 *
		 * @since   1.6.0
		 */
		public static function get_default_collation( $schema_name = '' ) {
			$wpdadb = WPDADB::get_db_connection( $schema_name );
			if ( $wpdadb === null ) {
				return [];
			} else {
				return $wpdadb->get_results(
					$wpdadb->prepare(
						"SELECT default_character_set_name AS default_character_set_name,
								default_collation_name AS default_collation_name
						 FROM   information_schema.schemata
						 WHERE  schema_name = %s
						 GROUP BY schema_name",
						[
							$wpdadb->dbname
						]
					)
					, 'ARRAY_A'
				);
			}
		}

		public static function get_engine( $schema_name, $table_name ) {
			$wpdadb = WPDADB::get_db_connection( $schema_name );
			if ( $wpdadb === null ) {
				return null;
			} else {
				$row = $wpdadb->get_results(
					$wpdadb->prepare(
						"SELECT engine AS engine
						   FROM information_schema.tables
						  WHERE table_schema = %s
							AND table_name = %s
					    ",
						[
							$wpdadb->dbname,
							$table_name
						]
					)
					, 'ARRAY_A'
				);
				if ( 1 === sizeof( $row ) ) {
					return $row[0]['engine'];
				} else {
					return null;
				}
			}
		}

	}

}
