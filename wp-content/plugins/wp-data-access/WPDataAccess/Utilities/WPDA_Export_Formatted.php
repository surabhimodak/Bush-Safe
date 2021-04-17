<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities {

	use WPDataAccess\Connection\WPDADB;
	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\WPDA;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;

	/**
	 * Class WPDA_Export_Formatted
	 *
	 * This class should not be instantiated directly. Use it to built exports for specific types. Overwrite methods
	 * header, row and footer for your own type specific export.
	 *
	 * @author  Peter Schulz
	 * @since    2.0.13
	 */
	class WPDA_Export_Formatted {

		/**
		 * Query
		 *
		 * Select statement used to perform export.
		 *
		 * @var string
		 */
		protected $statement = '';

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
		protected $table_names = '';

		/**
		 * Array containing selected rows
		 *
		 * @var null
		 */
		protected $rows = null;

		/**
		 * Number of rows found
		 *
		 * @var int
		 */
		protected $row_count = 0;

		/**
		 * Select columns
		 *
		 * @see WPDA_List_Columns::get_table_columns()
		 *
		 * @var array|null
		 */
		protected $columns = null;

		/**
		 * Column data types
		 *
		 * @var array
		 */
		protected $data_types = [];

		/**
		 * Primary key columns
		 * @var array
		 */
		protected $table_primary_key = [];

		/**
		 * Where clause added to query
		 *
		 * @var string
		 */
		protected $where = '';

		/**
		 * Handle to table columns
		 *
		 * @var object|null
		 */
		protected $wpda_list_columns = null;

		/**
		 * WPDA_Export_Formatted constructor.
		 *
		 * @since    2.0.13
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
		}

		/**
		 * Main method to get arguments and start export.
		 *
		 * @since   2.0.13
		 */
		public function export() {
			// Check if export is allowed.
			$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '?'; // input var okay.
			if ( ! wp_verify_nonce( $wp_nonce, 'wpda-export-*' ) ) {
				wp_die();
			}

			if ( isset( $_REQUEST['wpdaschema_name'] ) ) {
				$this->schema_name = sanitize_text_field( wp_unslash( $_REQUEST['wpdaschema_name'] ) ); // input var okay.
			}

			if ( isset( $_REQUEST['table_names'] ) ) {
				$this->table_names = sanitize_text_field( wp_unslash( $_REQUEST['table_names'] ) ); // input var okay.
			} else {
				// No table to export.
				wp_die();
			}

			// Check if table exists to prevent SQL injection.
			$wpda_dictionary_exists = new WPDA_Dictionary_Exist( $this->schema_name, $this->table_names );
			if ( ! $wpda_dictionary_exists->table_exists() ) {
				wp_die();
			}

			// Check if table exists and access is granted.
			$this->wpda_list_columns = WPDA_List_Columns_Cache::get_list_columns( $this->schema_name, $this->table_names );
			$this->columns           = $this->wpda_list_columns->get_table_columns();
			foreach ( $this->columns as $column ) {
				$this->data_types[ $column['column_name'] ] = $column['data_type'];
			}

			// Get primary key columns.
			$this->table_primary_key = $this->wpda_list_columns->get_table_primary_key();

			// Check validity request. All primary key columns must be supplied. Return error if
			// primary key columns are missing.
			foreach ( $this->table_primary_key as $key ) {
				if ( ! isset( $key ) ) {
					wp_die();
				}
			}

			if ( isset( $this->table_primary_key[0] ) && isset( $_REQUEST[ $this->table_primary_key[0] ] ) ) {
				// Build where clause.
				global $wpdb;
				$count_pk = count( $_REQUEST[ $this->table_primary_key[0] ] );
				for ( $i = 0; $i < $count_pk; $i ++ ) {
					$and = '';
					foreach ( $this->table_primary_key as $key ) {
						$and .= '' === $and ? '(' : ' and ';
						if ( $this->is_numeric( $this->data_types[ $key ] ) ) {
							$and .= $wpdb->prepare( "`$key` = %d", $_REQUEST[ $key ][ $i ] ); // WPCS: unprepared SQL OK.
						} else {
							$and .= $wpdb->prepare( "`$key` = %s", stripslashes( $_REQUEST[ $key ][ $i ] ) ); // WPCS: unprepared SQL OK.
						}
					}

					$and .= '' === $and ? '' : ')';

					$this->where .= '' === $this->where ? ' where ' : ' or ';
					$this->where .= $and;
				}
			}

			$this->get_rows();
			if ( false !== $this->rows ) {
				$this->send_export_file();
			}
		}

		/**
		 * Perform query and get rows
		 *
		 * Result is stored in $this->rows.
		 *
		 * @since    2.0.13
		 */
		protected function get_rows() {
			$wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( null === $wpdadb ) {
				if ( is_admin() ) {
					wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				} else {
					die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				}
			}

			if ( '' === $this->schema_name ) {
				$this->statement = "select * from `{$this->table_names}` {$this->where}";
			} else {
				$this->statement = "select * from `{$wpdadb->dbname}`.`{$this->table_names}` {$this->where}";
			}
			$this->rows      = $wpdadb->get_results( $this->statement, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
			$this->row_count = $wpdadb->num_rows;
		}

		/**
		 * Send export file to browser
		 *
		 * @since    2.0.13
		 */
		protected function send_export_file() {
			$this->header();
			foreach ( $this->rows as $row ) {
				$this->row( $row );
			}
			$this->footer();
		}

		/**
		 * Implement file header here
		 */
		protected function header() { }

		/**
		 * Implement how to process a row here
		 *
		 * @param $row
		 */
		protected function row( $row ) { }

		/**
		 * Implement file footer here
		 */
		protected function footer() { }

		/**
		 * Check if data type is numeric
		 *
		 * @param string $data_type Column data type
		 *
		 * @return bool TRUE = numeric
		 */
		protected function is_numeric( $data_type ) {
			return ( 'number' === WPDA::get_type( $data_type ) );
		}

	}

}
