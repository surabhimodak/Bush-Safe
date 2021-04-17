<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Data_Dictionary
 */

namespace WPDataAccess\Data_Dictionary {

	use WPDataAccess\Connection\WPDADB;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Dictionary_Exist
	 *
	 * WP Data Access provides interactive database access. Users might follow the interaction provided by the
	 * plugin, but might as well influence it's behaviour through plugin arguments (hack query strings and post
	 * values). The dynamic nature of WP Data Access allows users to change table and column names that are
	 * communicated from one page to another, which clearly leads to a SQL injection risk. This class deals with
	 * this issue through checks of existence of database object, like tables, columns, ect.
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_Dictionary_Exist {

		/**
		 * Cached schema names
		 *
		 * @var array
		 */
		static protected $schema_name_cache = [];

		/**
		 * Cached table names
		 *
		 * @var array
		 */
		static protected $table_name_cache = [];

		/**
		 * Indicator to identify if standard database schema and plugin tables are loaded
		 *
		 * @var bool
		 */
		static protected $plugin_schema_and_tables_loaded = false;

		/**
		 * Database schema name
		 *
		 * @var string
		 */
		protected $schema_name = '';

		/**
		 * Database table name
		 *
		 * @var string
		 */
		protected $table_name = '';

		/**
		 * WPDA_Dictionary_Checks constructor
		 *
		 * For table and column checks this class should be used by instantiating it. The table name must be
		 * provided as an argument. The schema name might be part of the table name provided in which case the
		 * schema name and table name should be seperated by a '.' (dot) as in MySQL notations. If no schema name
		 * is provided the WordPress schema is used as the default.
		 *
		 * @param string $schema_name Database schema name.
		 * @param string $table_name Database table name.
		 *
		 * @since   1.0.0
		 *
		 */
		public function __construct( $schema_name, $table_name ) {
			// DO NOT ADD PLUGIN TABLES TO CACHE (causes problems if not found)
			// self::load_plugin_tables();
			global $wpdb;

			if ( '' !== $schema_name ) {

				if ( ! isset( WPDA_Dictionary_Exist::$schema_name_cache[ $schema_name ] ) ) {
					// Check if schema name is valid!
					// Remote check...
					$rdb = WPDADB::get_remote_database( $schema_name );
					if ( false !== $rdb ) {
						WPDA_Dictionary_Exist::$schema_name_cache[ $schema_name ] = true;
					} else {
						// Local check...
						// Since a table name (and therefor schema name as well) can be provided on the url we need to check for
						// sql injection. We'll do this by checking of the schema name exists in our database.
						$wpdb->query(
							$wpdb->prepare( '
							SELECT TRUE
							  FROM information_schema.schemata
							 WHERE schema_name = %s
						',
								[
									$schema_name,
								]
							)
						); // db call ok; no-cache ok.
						$wpdb->get_results(); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
						if ( 1 !== $wpdb->num_rows ) {
							// Schema name doesn't exist! It makes no sense to continue.
							wp_die( __( 'ERROR: Wrong arguments [schema name not found]', 'wp-data-access' ) );
						} else {
							WPDA_Dictionary_Exist::$schema_name_cache[ $schema_name ] = true;
						}
					}
				}

				$this->schema_name = $schema_name;
				$this->table_name  = $table_name;

			} else {

				// Table or view is located in the WordPress database schema.
				$this->schema_name = $wpdb->dbname; // Taken from wpdb: no check needed.
				$this->table_name  = $table_name;

			}
		}

		/**
		 * Load plugin table name into cache
		 *
		 * Loads the plugin tables into a named array to be cached for fast table named access.
		 *
		 * @since 2.0.11
		 */
		protected static function load_plugin_tables() {
			if ( ! self::$plugin_schema_and_tables_loaded ) {
				global $wpdb;
				WPDA_Dictionary_Exist::$schema_name_cache[ $wpdb->dbname ] = true;

				$plugin_tables = WPDA::get_wpda_tables();
				foreach ( $plugin_tables as $plugin_table ) {
					WPDA_Dictionary_Exist::$table_name_cache["{$wpdb->dbname}.$plugin_table"] = true;
				}

				self::$plugin_schema_and_tables_loaded = true;
			}
		}

		/**
		 * Check if function exists
		 *
		 * @param string $schema_name Database schema name.
		 * @param string $function_name Database function name.
		 *
		 * @return bool TRUE = function exists, FALSE = function does not exist.
		 * @since   1.0.0
		 *
		 */
		public static function function_exists( $schema_name, $function_name ) {
			return self::routine_exists( $schema_name, $function_name, 'FUNCTION' );
		}

		/**
		 * Check is routine exists
		 *
		 * @param string $schema_name Database schema name.
		 * @param string $routine_name Database routine name.
		 * @param string $routine_type Database routine type.
		 *
		 * @return bool TRUE = routine exists, FALSE = routine does not exist.
		 * @since   1.0.0
		 *
		 */
		protected static function routine_exists( $schema_name, $routine_name, $routine_type ) {
			$wpdadb = WPDADB::get_db_connection( $schema_name );
			if ( $wpdadb === null ) {
				return false;
			}

			$wpdadb->query(
				$wpdadb->prepare( '
					SELECT TRUE
					  FROM information_schema.routines
					 WHERE routine_schema = %s
					   AND routine_name   = %s
					   AND routine_type   = %s
				',
					[
						$wpdadb->dbname,
						$routine_name,
						$routine_type,
					]
				)
			); // db call ok; no-cache ok.
			$wpdadb->get_results(); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			return ( 1 === $wpdadb->num_rows );
		}

		/**
		 * Check if procedure exists
		 *
		 * @param string $schema_name Database schema name.
		 * @param string $procedure_name Database procedure name.
		 *
		 * @return bool TRUE = procedure exists, FALSE = procedure does not exist.
		 * @since   1.0.0
		 *
		 */
		public static function procedure_exists( $schema_name, $procedure_name ) {
			return self::routine_exists( $schema_name, $procedure_name, 'PROCEDURE' );
		}

		/**
		 * Check if trigger exists
		 *
		 * @param string $schema_name Database schema name.
		 * @param string $trigger_name Database trigger name.
		 *
		 * @return bool TRUE = trigger exists, FALSE = trigger does not exist.
		 * @since   1.0.0
		 *
		 */
		public static function trigger_exists( $schema_name, $trigger_name ) {
			$wpdadb = WPDADB::get_db_connection( $schema_name );
			if ( $wpdadb === null ) {
				return false;
			}

			$wpdadb->query(
				$wpdadb->prepare( '
					SELECT TRUE
					  FROM information_schema.triggers
					 WHERE trigger_schema = %s
					   AND trigger_name   = %s
				',
					[
						$wpdadb->dbname,
						$trigger_name,
					]
				)
			); // db call ok; no-cache ok.
			$wpdadb->get_results(); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			return ( 1 === $wpdadb->num_rows );
		}

		/**
		 * Checks if a table exists
		 *
		 * A dynamic SQL statement might use a table or view name which can be supplied as argument on the url. This
		 * is a source of a possible SQL injection attack. In cases where users have the possibility to change a table
		 * or view name argument, we must check if the table or view name exists in the WordPress database to protect
		 * ourselves against SQL injection attacks.
		 *
		 * Before the existence of a table is checked against the MySQL data dictionary, an access check is performed.
		 * If the access check returns FALSE, a query for existence is no longer needed. The access check is performed
		 * prior to the existence check as it quicker and might save us a more expensive action.
		 *
		 * Works with tables as well as views.
		 *
		 * @param bool $use_table_access_settings Indicator whether settings should be checked (default = true).
		 * @param bool $check_back_end TRUE = back-end check, FALSE = font-end check.
		 *
		 * @return bool TRUE means table name is valid.
		 * @since   1.0.0
		 *
		 */
		public function table_exists( $use_table_access_settings = true, $check_back_end = true ) {
			if ( isset( WPDA_Dictionary_Exist::$table_name_cache["$this->schema_name.$this->table_name"] ) ) {
				return true;
			}

			if ( $use_table_access_settings ) {
				// First check if access is granted.
				if ( $check_back_end ) {
					// Check back-end access.
					$access = WPDA_Dictionary_Access::check_table_access_backend(
						$this->schema_name,
						$this->table_name,
						$done
					);
					if ( $done ) {
						return $access;
					}
					if ( ! $access ) {
						return false;
					}
				} else {
					// Check front-end access.
					$access = WPDA_Dictionary_Access::check_table_access_frontend(
						$this->schema_name,
						$this->table_name,
						$done
					);
					if ( $done ) {
						return $access;
					}
					if ( ! $access ) {
						return false;
					}
				}
			}

			$wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( $wpdadb === null ) {
				return false;
			}

			// In all other cases check if table or view exists.
			$wpdadb->query(
				$wpdadb->prepare( '
					SELECT TRUE
					  FROM information_schema.tables
					 WHERE table_schema = %s
					   AND table_name   = %s
				',
					[
						$wpdadb->dbname,
						$this->table_name,
					]
				)
			); // db call ok; no-cache ok.

			$wpdadb->get_results(); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			if ( 1 === $wpdadb->num_rows ) {
				WPDA_Dictionary_Exist::$table_name_cache["$this->schema_name.$this->table_name"] = true;

				return true;
			} else {
				return false;
			}
		}

		/**
		 * Plain check if table exists
		 *
		 * No access control, back-end or front-end checks are taken into account. This method only checks the
		 * existence of the table.
		 *
		 * @return bool
		 */
		public function plain_table_exists() {
			$wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( $wpdadb === null ) {
				return false;
			}

			$wpdadb->query(
				$wpdadb->prepare( '
					SELECT TRUE
					  FROM information_schema.tables
					 WHERE table_schema = %s
					   AND table_name   = %s
				',
					[
						$wpdadb->dbname,
						$this->table_name,
					]
				)
			); // db call ok; no-cache ok.
			$wpdadb->get_results(); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			return ( 1 === $wpdadb->num_rows );
		}

		/**
		 * Check is a column exists
		 *
		 * Checks whether a column name exists in the WordPress database. Used to prevent SQL injection. Also read
		 * {@see WPDA_Dictionary_Exist::table_exists()}.
		 *
		 * Works with tables as well as views.
		 *
		 * @param string $column_name Database columns name.
		 *
		 * @return bool TRUE = column exists.
		 * @since   1.0.0
		 *
		 */
		public function column_exists( $column_name ) {
			$wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( $wpdadb === null ) {
				return false;
			}

			$wpdadb->query(
				$wpdadb->prepare( '
					SELECT TRUE
					  FROM information_schema.columns
					 WHERE table_schema = %s
					   AND table_name   = %s
					   AND column_name  = %s
				',
					[
						$wpdadb->dbname,
						$this->table_name,
						$column_name,
					]
				)
			); // db call ok; no-cache ok.
			$wpdadb->get_results(); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			return ( 1 === $wpdadb->num_rows );
		}

		/**
		 * Is provided table name a view?
		 *
		 * @return bool TRUE = $this->table_name is a view, FALSE = $this->table_name is a table
		 */
		public function is_view() {
			$wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( $wpdadb === null ) {
				return false;
			}

			$wpdadb->query(
				$wpdadb->prepare( "
					SELECT TRUE
					  FROM information_schema.tables
					 WHERE table_schema = %s
					   AND table_name   = %s
					   AND table_type   LIKE '%VIEW'
				",
					[
						$wpdadb->dbname,
						$this->table_name,
					]
				)
			); // db call ok; no-cache ok.
			$wpdadb->get_results(); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			return ( 1 === $wpdadb->num_rows );
		}

		public static function schema_exists( $schema_name ) {
			global $wpdb;
			$wpdb->query(
				$wpdb->prepare( '
							SELECT TRUE
							  FROM information_schema.schemata
							 WHERE schema_name = %s
						',
					[
						$schema_name,
					]
				)
			); // db call ok; no-cache ok.
			$wpdb->get_results(); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
			return ( 1 === $wpdb->num_rows );
		}

	}

}
