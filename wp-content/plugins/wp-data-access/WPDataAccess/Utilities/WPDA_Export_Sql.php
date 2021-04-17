<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities {

	use http\Encoding\Stream;
	use WPDataAccess\Connection\WPDADB;
	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Export_Sql
	 *
	 * Exports tables or rows (depending on arguments)
	 *
	 * + Table export
	 *   + One or more tables (batch)
	 *   + Contains a create table statements for every tables exported
	 *   + All records of a tables are exported as one insert statement
	 *   + Contains comments (no reimport without editing possible)
	 * + Row export
	 *   + All records are exported as one insert statement
	 *   + Contains no comments (can be reimported without editing)
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_Export_Sql {

		/**
		 * Database schema name
		 *
		 * @var string
		 */
		protected $schema_name = '';

		/**
		 * Database schema name prefix used in strings
		 *
		 * @var string
		 */
		protected $schema_name_prefix = '';

		/**
		 * Table name(s) of table(s) to be exported
		 *
		 * @var string|array
		 */
		protected $table_list = [];

		/**
		 * Indicates whether to set MySQL environment
		 *
		 * @var string 'on' or 'off'
		 */
		protected $mysql_set;

		/**
		 * Indicates whether comments should be added
		 *
		 * @var string 'on' or 'off'
		 */
		protected $show_comments;

		/**
		 * Indicates whether to add a create table
		 *
		 * @var string 'on' or 'off'
		 */
		protected $show_create;

		/**
		 * Indicates whether table settings should be exported
		 *
		 * @var string 'on' or 'off'
		 */
		protected $include_table_settings;

		/**
		 * Use variable to write output
		 *
		 * @var string
		 */
		protected $output_string;

		/**
		 * Write output to stream if available
		 *
		 * @var null
		 */
		protected $output_stream = null;

		protected $export_with_prefix = false;

		/**
		 * WPDA_Export constructor.
		 *
		 * Make sure the export procedure has sufficient memory.
		 * @since    2.0.11
		 */
		public function __construct() {
			if ( defined( 'WP_MAX_MEMORY_LIMIT' ) ) {
				$wp_memory_limit      = WP_MAX_MEMORY_LIMIT;
				$current_memory_limit = @ini_get( 'memory_limit' );
				if ( false === $current_memory_limit ||
				     WPDA::convert_memory_to_decimal( $current_memory_limit ) < WPDA::convert_memory_to_decimal( $wp_memory_limit )
				) {
					@ini_set( 'memory_limit', $wp_memory_limit );
				}
			}

			$this->export_with_prefix = 'on' === WPDA::get_option( WPDA::OPTION_BE_EXPORT_VARIABLE_PREFIX );
		}

		/**
		 * Use this method to startan export using method arguments
		 *
		 * @param string       $mysql_set on|off
		 * @param string       $show_comments on|off
		 * @param string       $show_create on|off
		 * @param string       $schema_name Database schema name
		 * @param string|array $table_names Single table name (string) | Table name list (array)
		 * @param string       $export_type table|row
		 * @param string       $include_table_settings on|off
		 *
		 * @since    2.0.7
		 *
		 */
		public function export_with_arguments(
			$mysql_set,
			$show_comments,
			$show_create,
			$schema_name,
			$table_names,
			$export_type,
			$include_table_settings = 'on'
		) {
			$this->mysql_set              = 'on' === $mysql_set ? 'on' : 'off';
			$this->show_comments          = 'on' === $show_comments ? 'on' : 'off';
			$this->show_create            = 'on' === $show_create ? 'on' : 'off';
			$this->include_table_settings = 'on' === $include_table_settings ? 'on' : 'off';

			if ( '' !== $schema_name ) {
				$wpdadb = WPDADB::get_db_connection( $schema_name );
				if ( null === $wpdadb ) {
					die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				}

				$this->schema_name        = $schema_name;
				$this->schema_name_prefix = "`{$wpdadb->dbname}`.";
			}

			$this->table_list = $table_names;

			if ( 'table' === $export_type ) {
				// Table export.
				$this->export_tables();
			} else {
				// Row export.
				if ( is_array( $this->table_list ) ) {
					// For row exports a single table name must be supplied as a string.
					$this->wrong_arguments();
				} else {
					$this->export_rows();
				}
			}
		}

		/**
		 * Main method to start export
		 *
		 * This method checks arguments and starts the export according to the arguments provided.
		 *
		 * @since   1.0.0
		 */
		public function export() {
			// Check if export is allowed.
			$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '?'; // input var okay.
			if ( ! wp_verify_nonce( $wp_nonce, 'wpda-export-*' ) ) {
				wp_die();
			}

			// Get arguments.
			$this->mysql_set              = isset( $_REQUEST['mysql_set'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mysql_set'] ) ) : 'on'; // input var okay.
			$this->show_comments          = isset( $_REQUEST['show_comments'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['show_comments'] ) ) : 'on'; // input var okay.
			$this->show_create            = isset( $_REQUEST['show_create'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['show_create'] ) ) : 'on'; // input var okay.
			$this->include_table_settings = isset( $_REQUEST['include_table_settings'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['include_table_settings'] ) ) : 'off'; // input var okay.

			if ( isset( $_REQUEST['wpdaschema_name'] ) ) {
				$this->schema_name = sanitize_text_field( wp_unslash( $_REQUEST['wpdaschema_name'] ) ); // input var okay.
				if ( '' !== $this->schema_name ) {
					$wpdadb = WPDADB::get_db_connection( $this->schema_name );
					if ( null === $wpdadb ) {
						wp_die();
					}
					$this->schema_name_prefix = "`{$wpdadb->dbname}`.";
				}
			}

			if ( isset( $_REQUEST['type'] ) && isset( $_REQUEST['table_names'] ) ) { // input var okay.
				// Get table_name(s) from URL. Type is string for single table export and array for multi table export.
				// Row export implies single table export.
				$this->table_list = $_REQUEST['table_names'];

				if ( 'table' === sanitize_text_field( wp_unslash( $_REQUEST['type'] ) ) ) { // input var okay.
					// Table export.
					$this->export_tables();
				} else {
					// Row export.
					if ( is_array( $this->table_list ) ) {
						// For row exports a single table name must be supplied as a string.
						$this->wrong_arguments();
					} else {
						$this->export_rows();
					}
				}
			} else {
				$this->wrong_arguments();
			}
		}

		/**
		 * A table export was requested
		 *
		 * @since   1.0.0
		 */
		protected function export_tables() {
			if ( is_array( $this->table_list ) ) {
				// Multiple table export.
				$this->header( 'export' );
				$this->db_begin();

				foreach ( $this->table_list as $table_name ) {
					// Check if table exists to prevent SQL injection.
					$wpda_dictionary_exists = new WPDA_Dictionary_Exist( $this->schema_name, $table_name );
					if ( ! $wpda_dictionary_exists->table_exists() ) {
						wp_die();
					}

					$this->create_table( $table_name );
					$this->insert_rows( $table_name );
				}

				$this->db_end();
			} else {
				// Single table export.
				$table_name = $this->table_list;

				// Check if table exists to prevent SQL injection.
				$wpda_dictionary_exists = new WPDA_Dictionary_Exist( $this->schema_name, $table_name );
				if ( ! $wpda_dictionary_exists->table_exists() ) {
					wp_die();
				}

				$this->header( $table_name );
				$this->db_begin();

				$this->create_table( $table_name );
				$this->insert_rows( $table_name );

				$this->db_end();
			}
		}

		/**
		 * Set export header (filename)
		 *
		 * @param string $file_name Export filename.
		 *
		 * @since   1.0.0
		 *
		 */
		protected function header( $file_name ) {
			if ( null === $this->output_stream ) {
				header('Content-type: text/plain; charset=utf-8');
				header("Content-Disposition: attachment; filename=wpda_$file_name.sql");
				header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
				header('Cache-Control: post-check=0, pre-check=0', false);
				header('Pragma: no-cache');
				header('Expires: 0');
			}
		}

		/**
		 * Set MySQL environment
		 *
		 * @since   1.0.0
		 */
		protected function db_begin() {
			if ( 'off' === $this->mysql_set ) {
				return;
			}

			$wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( null === $wpdadb ) {
				wp_die();
			}

			$this->output_string = '';

			$this->output_string .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
			$this->output_string .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
			$this->output_string .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
			$this->output_string .= '/*!40101 SET NAMES ' . esc_attr( $wpdadb->charset ) . " */;\n\n";

			$this->write_output();
		}

		/**
		 * Write create table statement
		 *
		 * @param string $table_name Database table name.
		 *
		 * @return boolean Indicates whether table exists for further processing.
		 * @since   1.0.0
		 *
		 */
		protected function create_table( $table_name ) {
			global $wpdb;
			$wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( null === $wpdadb ) {
				wp_die();
			}

			$save_suppress_errors  = $wpdadb->suppress_errors;
			$wpdadb->suppress_errors = true;

			if ( 'off' !== $this->show_create ) {
				$query = "show create table {$this->schema_name_prefix}`$table_name`";
				$ctcmd = $wpdadb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
			}

			$this->output_string = '';
			if ( $wpdadb->num_rows > 0 ) {
				if ( 'off' !== $this->show_comments ) {
					$this->output_string .= "--\n";
					if (
						$this->export_with_prefix &&
						$wpdb->dbname === $this->schema_name &&
						( WPDA::is_wp_table( $table_name ) || WPDA::is_wpda_table( $table_name ) )
					) {
						$this->output_string .= '-- Create table `{wp_prefix}' . esc_attr( substr( $table_name, strlen( $wpdb->prefix ) ) ) . "`\n";
					} else {
						$this->output_string .= '-- Create table `' . esc_attr( $table_name ) . "`\n";
					}
					$this->output_string .= "--\n";
				}

				if ( 'off' !== $this->show_create ) {
					$create_table_statement = $ctcmd[0]['Create Table'];
					if (
						$this->export_with_prefix &&
						$wpdb->dbname === $this->schema_name &&
						( WPDA::is_wp_table( $table_name ) || WPDA::is_wpda_table( $table_name ) )
					) {
						$table_name_position = strpos( $create_table_statement, $table_name );
						if ( $table_name_position > 0 ) {
							$create_table_statement_saved = $create_table_statement;
							$create_table_statement       = substr( $create_table_statement, 0, $table_name_position );
							$create_table_statement       .= '{wp_prefix}' . esc_attr( substr( $table_name, strlen( $wpdb->prefix ) ) );
							$create_table_statement       .= substr( $create_table_statement_saved, $table_name_position + strlen( $table_name ) );
						}
					}
					$this->output_string .= wp_kses_data( $create_table_statement ) . ";\n\n";
				}

				$table_exists = true;
			} else {
				if ( 'off' !== $this->show_comments ) {
					$this->output_string .= "--\n";
					$this->output_string .= '-- Table `' . esc_attr( $table_name ) . "` not found\n";
					$this->output_string .= "--\n\n";
				}

				$table_exists = false;
			}

			$this->write_output();

			$wpdadb->suppress_errors = $save_suppress_errors;

			return $table_exists;
		}

		/**
		 * Write insert into statement
		 *
		 * @param string  $table_name Database table name.
		 * @param string  $where Where clause.
		 * @param boolean $show_comments Argument is needed for direct call from WPDA_Repository! (do not remove).
		 *
		 * @since   1.0.0
		 *
		 */
		public function insert_rows( $table_name, $where = '', $show_comments = true ) {
			global $wpdb;
			$wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( null === $wpdadb ) {
				wp_die();
			}

			$save_suppress_errors  = $wpdadb->suppress_errors;
			$wpdadb->suppress_errors = true;

			$query = "select * from {$this->schema_name_prefix}`$table_name` $where";
			$rows  = $wpdadb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			$this->output_string = '';
			if ( $wpdadb->num_rows > 0 ) {
				// Prepare row export: get column names and data types.
				$wpda_list_columns = WPDA_List_Columns_Cache::get_list_columns( $this->schema_name, $table_name );
				$table_columns     = $wpda_list_columns->get_table_columns();

				// Create array for fast column_name based access.
				$column_data_types = $this->column_data_types( $table_columns );

				// Exports rows.
				if ( $show_comments && 'off' !== $this->show_comments ) {
					$this->output_string .= "--\n";
					if (
						$this->export_with_prefix &&
						$wpdb->dbname === $this->schema_name &&
						( WPDA::is_wp_table( $table_name ) || WPDA::is_wpda_table( $table_name ) )
					) {
						$this->output_string .= '-- Export table `{wp_prefix}' . esc_attr( substr( $table_name, strlen( $wpdb->prefix ) ) ) . "`\n";
					} else {
						$this->output_string .= '-- Export table `' . esc_attr( $table_name ) . "`\n";
					}
					$this->output_string .= "--\n";
				}

				if ( ( $show_comments && '' !== $where ) && 'off' !== $this->show_comments ) {
					$this->output_string .= '-- Condition: `' . esc_attr( $where ) . "`\n";
					$this->output_string .= "--\n";
				}

				$this->write_output();

				if (
					$this->export_with_prefix &&
					( $wpdb->dbname === $this->schema_name || '' === $this->schema_name ) &&
					( WPDA::is_wp_table( $table_name ) || WPDA::is_wpda_table( $table_name ) )
				) {
					$insert_statement = 'INSERT INTO `{wp_prefix}' . esc_attr( substr( $table_name, strlen( $wpdb->prefix ) ) ) . "` ";
				} else {
					$insert_statement = 'INSERT INTO `' . esc_attr( $table_name ) . "` ";
				}

				$process_first_row = true;
				foreach ( $rows[0] as $column_name => $column_value ) {
					if (
						! (
							WPDA::is_wpda_table( $table_name ) &&
							(
								'pub_id' === $column_name ||
								'csv_id' === $column_name ||
								'menu_id' === $column_name ||
								'report_id' === $column_name
							)
						)
					) {
						$insert_statement .= $process_first_row ? '(' : ', ';
						$insert_statement .= '`' . esc_attr( $column_name ) . '`';

						$process_first_row = false;
					}
				}

				$insert_statement .= ') VALUES ';

				foreach ( $rows as $row ) {
					$this->output_string .= $insert_statement . "(";

					$keys        = array_keys( $row );
					$last_column = end( $keys );
					foreach ( $row as $column_name => $column_value ) {
						if (
							! (
								WPDA::is_wpda_table( $table_name ) &&
								(
									'pub_id' === $column_name ||
									'csv_id' === $column_name ||
									'menu_id' === $column_name ||
									'report_id' === $column_name
								)
							)
						) {
							if ( $this->is_numeric( $column_data_types[ $column_name ] ) ) {
								if ( is_null( $column_value ) ) {
									$this->output_string .= 'null';
								} else {
									$this->output_string .= esc_attr( $column_value );
								}
							} else {
								if (
									WPDA::column_is_schema_name( $table_name, $column_name ) &&
									( '' === $column_value || $wpdb->dbname === $column_value )
								) {
									$this->output_string .= "'{wp_schema}'";
								} else {
									if ( is_null( $column_value ) ) {
										$this->output_string .= 'null';
									} else {
										$this->output_string .= "'" . esc_sql( $column_value ) . "'";
									}
								}
							}
							if ( $column_name !== $last_column ) {
								$this->output_string .= ',';
							}
						}
					}

					$this->output_string .= ");\n";

					$this->write_output();
				}

				if ( 'off' !== $this->show_comments ) {
					$this->output_string .= "\n";
				}
			} else {
				// Empty table, nothing to export.
				if ( $show_comments && 'off' !== $this->show_comments ) {
					$this->output_string .= "--\n";
					$this->output_string .= '-- No rows to export from empty table `' . esc_attr( $table_name ) . "`\n";
					$this->output_string .= "--\n\n";
				}
			}

			if ( 'on' === $this->include_table_settings ) {
				// Export table settings
				$this->export_table_settings( $table_name, $show_comments );
			}

			$this->write_output();

			$wpdadb->suppress_errors = $save_suppress_errors;
		}

		/**
		 * Export table settings
		 *
		 * @param         $table_name Table for which settings will be exported
		 * @param boolean $show_comments Argument is needed for direct call from WPDA_Repository! (do not remove).
		 *
		 * @since   2.6.1
		 */
		protected function export_table_settings( $table_name, $show_comments = true ) {
			global $wpdb;

			if ( $show_comments && 'off' !== $this->show_comments ) {
				$this->output_string .= "--\n";
				$this->output_string .= '-- Export table settings for table `' . esc_attr( $table_name ) . "`\n";
				$this->output_string .= "--\n";
			}

			if ( '' === $this->schema_name || $wpdb->dbname === $this->schema_name ) {
				$schema_name = '{wp_schema}';
			} else {
				$schema_name = $this->schema_name;
			}

			if ( 'on' === $this->include_table_settings ) {
				// Export column labels
				$query = "select * from {$wpdb->prefix}wpda_table_settings where wpda_table_name = '$table_name'";
				$rows  = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
				if ( 1 === $wpdb->num_rows ) {
					$this->output_string .=
						'DELETE FROM `{wp_prefix}wpda_table_settings` ' .
						"WHERE `wpda_table_name` = '" . esc_attr( $table_name ) . "';";
					$this->output_string .= "\n";
					$this->output_string .=
						'INSERT INTO `{wp_prefix}wpda_table_settings` ' .
						'(`wpda_schema_name`, `wpda_table_name`, `wpda_table_settings`) ' .
						'VALUES ' .
						"('" . esc_sql( $schema_name ) . "', '" . esc_sql( $table_name ) . "', '" .
						esc_sql( $rows[0]['wpda_table_settings'] ) . "');";
					$this->output_string .= "\n";
				}

				// Export media columns
				$query = "select * from {$wpdb->prefix}wpda_media where media_table_name = '$table_name'";
				$rows  = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
				$this->output_string .=
					'DELETE FROM `{wp_prefix}wpda_media` ' .
					"WHERE `media_table_name` = '" . esc_attr( $table_name ) . "';";
				$this->output_string .= "\n";
				foreach ( $rows as $row ) {
					$this->output_string .=
						'INSERT INTO `{wp_prefix}wpda_media` ' .
						'(`media_schema_name`, `media_table_name`, `media_column_name`, `media_type`, `media_activated`) ' .
						'VALUES ' .
						"('" . esc_sql( $schema_name ) . "', '" . esc_sql( $table_name ) . "', '" .
						esc_sql( $row['media_column_name'] ) . "', '" . esc_sql( $row['media_type'] ) . "', '" .
						esc_sql( $row['media_activated'] ) . "');";
					$this->output_string .= "\n";
				}

				// Export table menus
				$query = "select * from {$wpdb->prefix}wpda_menus where menu_table_name = '$table_name'";
				$rows  = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
				$this->output_string .=
					'DELETE FROM `{wp_prefix}wpda_menus` ' .
					"WHERE `menu_table_name` = '" . esc_attr( $table_name ) . "';";
				$this->output_string .= "\n";
				foreach ( $rows as $row ) {
					$this->output_string .=
						'INSERT INTO `{wp_prefix}wpda_menus` ' .
						'(`menu_schema_name`, `menu_table_name`, `menu_name`, `menu_slug`, `menu_role`) ' .
						'VALUES ' .
						"('" . esc_sql( $schema_name ) . "', '" . esc_sql( $table_name ) . "', '" .
						esc_sql( $row['menu_name'] ) . "', '" . esc_sql( $row['menu_slug'] ) . "', '" .
						esc_sql( $row['menu_role'] ) . "');";
					$this->output_string .= "\n";
				}
			}

			$this->output_string .= "\n";
		}

		/**
		 * Save column data types
		 *
		 * This method creates a named array for all column names of a table in form:
		 * 'column_name' => 'data_type'
		 *
		 * Argument $table_columns can be retrieved from WPDA_List_Columns->set_table_columns(). It must be prepared
		 * however with the idea that the instance of WPDA_List_Columns can be reused for best performance.
		 *
		 * In fact this is just an array conversion.
		 *
		 * @param array $table_columns Column_names and data_types of a table (table name not used here).
		 *
		 * @return array Named array 'column_name' => 'data_type' for all columns in the table.
		 * @since   1.0.0
		 *
		 */
		protected function column_data_types( $table_columns ) {
			$column_data_types = [];

			foreach ( $table_columns as $column_value ) {
				$column_data_types[ $column_value['column_name'] ] = $column_value['data_type'];
			}

			return $column_data_types;
		}

		/**
		 * Check if data type is numeric
		 *
		 * @param string $data_type Data type (simple).
		 *
		 * @return bool
		 * @since   1.0.0
		 *
		 */
		protected function is_numeric( $data_type ) {
			return ( 'number' === WPDA::get_type( $data_type ) );
		}

		/**
		 * Set back MySQL environment
		 *
		 * @since   1.0.0
		 */
		protected function db_end() {
			if ( 'off' === $this->mysql_set ) {
				return;
			}

			$this->output_string = '';

			$this->output_string .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
			$this->output_string .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
			$this->output_string .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";

			$this->write_output();
		}

		/**
		 * Processing on invalid arguments
		 *
		 * @since   1.0.0
		 */
		protected function wrong_arguments() {
			wp_die();
		}

		/**
		 * A row export was requested
		 *
		 * @since   1.0.0
		 */
		protected function export_rows() {
			// Check if table exists to prevent SQL injection.
			$wpda_dictionary_exists = new WPDA_Dictionary_Exist( $this->schema_name, $this->table_list );
			if ( ! $wpda_dictionary_exists->table_exists() ) {
				wp_die();
			}

			// Get table columns and data types.
			$wpda_list_columns = WPDA_List_Columns_Cache::get_list_columns( $this->schema_name, $this->table_list );
			$table_columns     = $wpda_list_columns->get_table_columns();

			// Create array for fast column_name based access.
			$column_data_types = $this->column_data_types( $table_columns );

			// Get primary key columns.
			$table_primary_key = $wpda_list_columns->get_table_primary_key();

			// Check validity request. All primary key columns must be supplied. Return error if
			// primary key columns are missing.
			foreach ( $table_primary_key as $key ) {
				if ( ! isset( $key ) ) {
					$this->wrong_arguments();
				}
			}

			global $wpdb;

			// Build where clause based on primary key.
			$where = '';

			// Use first column of the primary key to loop through arguments. Add additional arguments in the loop.
			// A mismatch in the number of argument is possible as long as the columns match based on the first column
			// of the primary key. Other mismatches won't be taken into account.
			$count_pk = count( $_REQUEST[ $table_primary_key[0] ] );
			for ( $i = 0; $i < $count_pk; $i ++ ) {
				$and = '';
				foreach ( $table_primary_key as $key ) {
					$and .= '' === $and ? '(' : ' and ';
					if ( $this->is_numeric( $column_data_types[ $key ] ) ) {
						$and .= $wpdb->prepare( "`$key` = %d", $_REQUEST[ $key ][ $i ] ); // WPCS: unprepared SQL OK.
					} else {
						$and .= $wpdb->prepare( "`$key` = %s", stripslashes( $_REQUEST[ $key ][ $i ] ) ); // WPCS: unprepared SQL OK.
					}
				}

				$and .= '' === $and ? '' : ')';

				$where .= '' === $where ? ' where ' : ' or ';
				$where .= $and;
			}

			$this->header( $this->table_list );
			$this->db_begin();
			$this->insert_rows( $this->table_list, $where );
			$this->db_end();
		}

		/**
		 * Define output stream
		 *
		 * Used to stream an export to a file. Streaming helps to support exports of large files.
		 *
		 * @param Stream $output_stream Handle to output stream
		 *
		 * @since 2.0.13
		 */
		public function set_output_stream( $output_stream ) {
			$this->output_stream = $output_stream;
		}

		/**
		 * Depending on the type of export (streaming or echoing) the output is written to the export file.
		 *
		 * @since 2.0.13
		 */
		protected function write_output() {
			if ( null === $this->output_stream ) {
				echo $this->output_string;
			} else {
				fwrite( $this->output_stream, $this->output_string );
			}
			$this->output_string = '';
		}

	}

}
