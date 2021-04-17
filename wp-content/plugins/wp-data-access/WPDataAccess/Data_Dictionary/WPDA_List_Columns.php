<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Data_Dictionary
 */

namespace WPDataAccess\Data_Dictionary {

	use WPDataAccess\Connection\WPDADB;
	use WPDataAccess\Plugin_Table_Models\WPDA_Table_Settings_Model;

	/**
	 * Class WPDA_List_Columns
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_List_Columns {

		/**
		 * Database connection
		 *
		 * @var null
		 */
		protected $wpdadb = null;

		/**
		 * Database schema name
		 *
		 * @var string
		 */
		protected $schema_name;

		/**
		 * Database table name
		 *
		 * @var string
		 */
		protected $table_name;

		/**
		 * Columns of $this->table_name
		 *
		 * @var array
		 */
		protected $table_columns = [];

		/**
		 * Columns of $this->searchable_table_columns
		 *
		 * @var array
		 */
		protected $searchable_table_columns = [];

		/**
		 * Column data type sorted on column name
		 *
		 * @var array
		 */
		protected $table_column_data_type = [];

		/**
		 * Column type sorted on column name
		 *
		 * @var array
		 */
		protected $table_column_type = [];

		/**
		 * Primary key columns of $this->table_name
		 *
		 * @var array
		 */
		protected $table_primary_key = [];

		/**
		 * Primary key columns of $this->table_name (named)
		 *
		 * @var array
		 */
		protected $table_primary_key_check = [];

		/**
		 * Auto increment column name of $this->table_name or false
		 *
		 * @var bool|string
		 */
		protected $auto_increment_column_name = false;

		/**
		 * Column headers for $this->table_name
		 *
		 * @var array
		 */
		protected $table_column_headers = [];

		/**
		 * Table settings as define in Data Explorer
		 *
		 * @var null||Object
		 */
		protected $table_settings = null;

		/**
		 * WPDA_List_Columns constructor
		 *
		 * @param string $schema_name Database schema name.
		 * @param string $table_name Database table name.
		 *
		 * @since   1.0.0
		 *
		 */
		public function __construct( $schema_name, $table_name ) {
			// Set schema and table name.
			if ( '' === $schema_name ) {
				global $wpdb;
				$this->schema_name = $wpdb->dbname;
			} else {
				$this->schema_name = $schema_name;
			}

			$this->wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( null === $this->wpdadb ) {
				if ( is_admin() ) {
					wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				} else {
					die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				}
			}

			if ( '' !== $table_name ) {
				$this->table_name = $table_name;

				// Get table settings
				$table_settings_db = WPDA_Table_Settings_Model::query( $this->table_name, $this->schema_name );
				if ( isset( $table_settings_db[0]['wpda_table_settings'] ) ) {
					$this->table_settings = json_decode( $table_settings_db[0]['wpda_table_settings'] );
				}

				// Get dictionary info
				$this->set_table_columns();
				$this->set_table_primary_key();
				$this->set_table_column_headers();
			}
		}

		/**
		 * Get column label for list table
		 *
		 * Returns the label of a column according to a pre defined format. Call must contain column name.
		 * Column must be in $this->table_name.
		 *
		 * @param string $column_name Column name as defined in the data dictionary.
		 *
		 * @return string Label for $column_name.
		 * @since   1.0.0
		 *
		 */
		public function get_column_label( $column_name ) {

			if ( isset( $this->table_settings->list_labels->$column_name ) ) {
				return $this->table_settings->list_labels->$column_name;
			} else {
				return $this->get_default_column_label( $column_name );
			}

		}

		/**
		 * Return column data type
		 *
		 * @param $column_name Database column name
		 *
		 * @return string|null
		 * @since   2.7.2
		 *
		 */
		public function get_column_data_type( $column_name ) {
			return isset( $this->table_column_data_type[ $column_name ] ) ? $this->table_column_data_type[ $column_name ] : null;
		}

		/**
		 * Return column type
		 *
		 * @param $column_name Database column name
		 *
		 * @return string|null
		 * @since   2.7.2
		 *
		 */
		public function get_column_type( $column_name ) {
			return isset( $this->table_column_type[ $column_name ] ) ? $this->table_column_type[ $column_name ] : null;
		}

		/**
		 * Get default column label
		 *
		 * Returns the default label of a column according to a pre defined format. Call must contain column name.
		 * Column must be in $this->table_name.
		 *
		 * @param string $column_name Column name as defined in the data dictionary.
		 *
		 * @return string Default label for $column_name.
		 * @since   1.0.0
		 *
		 */
		public function get_default_column_label( $column_name ) {

			return ucwords( str_replace( '_', ' ', $column_name ) );

		}

		/**
		 * Check if column is part of primary key
		 *
		 * @param string $column_name Column name as defined in the data dictionary.
		 *
		 * @return bool TRUE = column is part of primary key, FALSE = column is not part of primary key.
		 * @since   1.0.0
		 *
		 */
		public function is_primary_key_column( $column_name ) {

			return ( isset( $this->table_primary_key_check[ $column_name ] ) );

		}

		/**
		 * Get columns
		 *
		 * @return array Column of $this->table_name.
		 * @since   1.0.0
		 *
		 */
		public function get_table_columns() {

			return $this->table_columns;

		}

		/**
		 * Get searchable columns
		 *
		 * @return array Column of $this->table_name.
		 * @since   1.0.0
		 *
		 */
		public function get_searchable_table_columns() {

			return $this->searchable_table_columns;

		}

		/**
		 * Set table columns
		 *
		 * Column info is taken from the MySQL data dictionary. For each column in $this->table_name the following
		 * column info is stored:
		 * + Column name
		 * + Data type (MySQL data type)
		 * + Extra (needed to find auto increment columns)
		 * + Column type (needed for columns with data type enum: column type holds allowed values)
		 * + Null values allowed?
		 *
		 * Since MariaDB 10.2.7 and higher handles default values different than other DBMSs we have to take care of
		 * the quotes it places at the beginning and the end. This involves the implication that users cannot use
		 * default values that start and end with a single quote. Which seems common sense for me.
		 *
		 * @since   1.0.0
		 */
		protected function set_table_columns() {
			$query = $this->wpdadb->prepare(
				'
	              SELECT column_name AS column_name, 
	                     data_type AS data_type,
	                     extra AS extra,
	                     column_type AS column_type,
	                     is_nullable AS is_nullable,
	                     IF(LEFT(column_default,1)=\'\'\'\' AND RIGHT(column_default,1)=\'\'\'\', SUBSTR(column_default,2,LENGTH(column_default)-2), column_default) AS column_default,
	              		 character_maximum_length AS character_maximum_length,
	                     numeric_scale AS numeric_scale,
	                     numeric_precision AS numeric_precision
	                FROM information_schema.columns 
	               WHERE table_schema = %s
	                 AND table_name   = %s
	               ORDER BY ordinal_position
	            ',
				[
					$this->wpdadb->dbname,
					$this->table_name,
				]
			);

			$this->table_columns            = $this->wpdadb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
			$this->searchable_table_columns = $this->table_columns; // Contains all columns and is not modified

			foreach ( $this->table_columns as $column ) {
				if ( isset( $column['column_name'] ) && isset( $column[ 'data_type' ] ) ) {
					$this->table_column_data_type[ $column['column_name'] ] = $column['data_type'];
				}

				if ( isset( $column['column_name'] ) && isset( $column[ 'column_type' ] ) ) {
					$this->table_column_type[ $column['column_name'] ] = $column['column_type'];
				}
			}

		}

		/**
		 * Get primary key columns
		 *
		 * @return array Primary key columns of $this->table_name.
		 * @since   1.0.0
		 *
		 */
		public function get_table_primary_key() {
			return $this->table_primary_key;
		}

		/**
		 * Set primary key columns
		 *
		 * Primary key columns are taken from the MySQL data dictionary.
		 *
		 * Newer versions of MariaDB no longer seem to support JOIN USING. Rewritten to old school join. MySQL supports
		 * both types of joins.
		 *
		 * @since   1.0.0
		 */
		protected function set_table_primary_key() {
			$result = $this->get_table_unique_indexes();

			if ( 0 < $this->wpdadb->num_rows ) {
				// Check if there is a primary key
				$has_primary_key = false;
				foreach ( $result as $row ) {
					if ( 'PRIMARY' === $row['Key_name'] ) {
						$this->table_primary_key[]                            = $row['Column_name'];
						$this->table_primary_key_check[ $row['Column_name'] ] = true;

						foreach ( $this->table_columns as $table_column ) {
							if ( $table_column['column_name'] === $row['Column_name'] &&
							     'auto_increment' === $table_column['extra']
							) {
								// Save auto_increment column name.
								$this->auto_increment_column_name = $row['Column_name'];
							}
						}

						$has_primary_key = true;
					}
				}

				if ( ! $has_primary_key ) {
					// Use non unique index as an alternative
					$key_name                                   = $result[0]['Key_name'];
					$this->table_primary_key[]                  = $result[0]['Column_name'];
					$this->table_primary_key_check[ $key_name ] = true;
					$i                                          = 1;
					while ( $i < sizeof( $result ) ) {
						if ( $key_name === $result[ $i ]['Key_name'] ) {
							$this->table_primary_key[]                                     = $result[ $i ]['Column_name'];
							$this->table_primary_key_check[ $result[ $i ]['Column_name'] ] = true;
						}
						$i ++;
					}
				}
			}
		}

		protected function get_table_unique_indexes() {
			$suppress = $this->wpdadb->suppress_errors( true );
			$query =
				sprintf(
					"SHOW INDEX FROM `%s`.`%s` WHERE non_unique = 0",
					$this->wpdadb->dbname,
					$this->table_name
				);
			$result = $this->wpdadb->get_results( $query, 'ARRAY_A' );
			$this->wpdadb->suppress_errors( $suppress );

			return $result;
		}

		public function get_table_alternative_keys() {
			$result = $this->get_table_unique_indexes();
			$return = [];

			if ( 0 < $this->wpdadb->num_rows ) {
				$index_name = '';
				$index      = [];
				foreach ( $result as $row ) {
					if ( $index_name !== $row['Key_name'] ) {
						if ( '' !== $index_name ) {
							array_push( $return, $row['Column_name'] );
							$index = [];
						}
						$index_name = $row['Key_name'];
					}
					array_push( $index, $row['Column_name'] );
				}
				array_push( $return, $index );
			}

			return $return;
		}

		/**
		 * Get column headers
		 *
		 * @return array
		 * @since   1.0.0
		 *
		 */
		public function get_table_column_headers() {

			return $this->table_column_headers;

		}

		/**
		 * Set column headers (= column labels in data entry forms)
		 *
		 * For now column headers are defined equal to their names. If a column is part of the primary key, this is
		 * reflected in the column header.
		 *
		 * @since   1.0.0
		 */
		protected function set_table_column_headers() {

			if ( ! isset( $this->table_columns ) ) {
				wp_die( __( 'ERROR: Wrong arguments [no columns found]', 'wp-data-access' ) );
			}

			$primary_nr                 = 0;
			$this->table_column_headers = [];
			foreach ( $this->table_columns as $key => $value ) {

				if ( isset( $this->table_settings->form_labels ) ) {
					if ( isset( $this->table_settings->form_labels->{$value['column_name']} ) ) {
						$label = $this->table_settings->form_labels->{$value['column_name']};
					} else {
						$label = $this->get_default_column_label( $value['column_name'] );
					}
				} else {
					$label = $this->get_default_column_label( $value['column_name'] );

					if ( $this->is_primary_key_column( $value['column_name'] ) ) {
						$key_text = __( 'key', 'wp-data-access' );
						if ( count( $this->table_primary_key ) > 1 ) {
							$label .= " ($key_text #" . ( ++ $primary_nr ) . ')';
						} else {
							$label .= " ($key_text)";
						}
					}
				}

				$this->table_column_headers[ $value['column_name'] ] = $label;
			}

		}

		/**
		 * Get name of auto increment column
		 *
		 * @return bool|string Name of auto increment column or false if no auto increment column exists
		 * @since   1.0.0
		 *
		 */
		public function get_auto_increment_column_name() {

			return $this->auto_increment_column_name;

		}

	}

}
