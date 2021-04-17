<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Plugin_Table_Models
 */

namespace WPDataAccess\Plugin_Table_Models {

	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Design_Table_Model
	 *
	 * @author  Peter Schulz
	 * @since   1.1.0
	 */
	class WPDA_Design_Table_Model extends WPDA_Plugin_Table_Base_Model {

		const BASE_TABLE_NAME = 'wpda_table_design';

		/**
		 * Name of table where design is stored
		 *
		 * @var string|null
		 */
		protected $table_name = null;

		/**
		 * Design table name
		 *
		 * @var string|null
		 */
		protected $wpda_table_name = null;

		/**
		 * Original design table name
		 *
		 * Used to detect a table name update.
		 *
		 * @var string|null
		 */
		protected $wpda_table_name_original = null;

		/**
		 * Design database name
		 *
		 * @var string|null
		 */
		protected $wpda_schema_name = null;

		/**
		 * Original design database name
		 *
		 * Used to detect a database name update.
		 *
		 * @var string|null
		 */
		protected $wpda_schema_name_original = null;

		/**
		 * The actual table design
		 *
		 * @var string|null
		 */
		protected $wpda_table_design = null;

		/**
		 * WPDA_Design_Table_Model constructor
		 *
		 * @since   1.1.0
		 */
		public function __construct() {
			$this->table_name = self::get_base_table_name();

			// Watch out for arrays! (array = starting export)
			if ( isset( $_REQUEST['wpda_table_name'] ) && ! is_array( $_REQUEST['wpda_table_name'] ) ) {
				$this->wpda_table_name = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_name'] ) );
			}

			if ( isset( $_REQUEST['wpda_table_name_original'] ) ) {
				$this->wpda_table_name_original = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_name_original'] ) );
			}

			if ( isset( $_REQUEST['wpda_schema_name'] ) && ! is_array( $_REQUEST['wpda_schema_name'] ) ) {
				$this->wpda_schema_name = sanitize_text_field( wp_unslash( $_REQUEST['wpda_schema_name'] ) );
			}

			if ( isset( $_REQUEST['wpda_schema_name_original'] ) ) {
				$this->wpda_schema_name_original = sanitize_text_field( wp_unslash( $_REQUEST['wpda_schema_name_original'] ) );
			}
		}

		/**
		 * Get design for a specific table name
		 *
		 * @param string|null $wpda_table_name_original Table name to be queried. If null query 'wpda_table_name'.
		 * @param string|null $wpda_schema_name_original Schema name to be queried. If null query 'wpda_schema_name'.
		 *
		 * @return int Number of rows.
		 */
		public function query( $wpda_table_name_original = null, $wpda_schema_name_original = null ) {
			if ( null === $this->wpda_table_name || null === $this->wpda_schema_name ) {
				return false;
			}

			global $wpdb;
			$query =
				$wpdb->prepare( "
							SELECT wpda_table_design
							  FROM {$this->table_name}
							 WHERE wpda_table_name = %s
							   AND wpda_schema_name = %s
						",
					[
						$wpda_table_name_original === null ? $this->wpda_table_name : $wpda_table_name_original,
						$wpda_schema_name_original === null ? $this->wpda_schema_name : $wpda_schema_name_original,
					]
				);

			$wpda_table_design_raw = $wpdb->get_results( $query, 'ARRAY_A' );
			if ( 1 === $wpdb->num_rows ) {
				$this->wpda_table_design = json_decode( $wpda_table_design_raw[0]['wpda_table_design'] );
			}

			return $wpdb->num_rows;
		}

		/**
		 * Return table design
		 *
		 * @return null|array
		 */
		public function get_table_design() {

			return $this->wpda_table_design;

		}

		/**
		 * Perform validations and return array containing errors
		 *
		 * @return array List of errors found.
		 */
		public function validate() {
			$structure_messages = [];

			if ( ! isset( $this->wpda_table_design->design_mode ) ) {
				$structure_messages[] = [ 'ERR', 'Invalid structure [missing element design_mode]' ];
			}
			if ( ! isset( $this->wpda_table_design->engine )
			     && (
				     ! isset( $this->wpda_table_design->table_type )
				     ||
				     ( isset( $this->wpda_table_design->table_type ) && 'TABLE' === $this->wpda_table_design->table_type )
			     )
			) {
				$structure_messages[] = [ 'ERR', 'Invalid structure [missing element engine]' ];
			}
			if ( ! isset( $this->wpda_table_design->collation )
			     && (
				     ! isset( $this->wpda_table_design->table_type )
				     ||
				     ( isset( $this->wpda_table_design->table_type ) && 'TABLE' === $this->wpda_table_design->table_type )
			     )
			) {
				$structure_messages[] = [ 'ERR', 'Invalid structure [missing element collation]' ];
			}
			if ( ! isset( $this->wpda_table_design->table ) ) {
				$structure_messages[] = [ 'ERR', 'Invalid structure [missing element table]' ];
			} else {
				$unique_column_names = [];
				foreach ( $this->wpda_table_design->table as $column ) {
					$unique_column_names[ $column->column_name ] = true;
				}
				if ( count( $unique_column_names ) !== count( $this->wpda_table_design->table ) ) {
					$structure_messages[] = [ 'ERR', 'Column name must be unique within a table' ];
				}
			}
			if ( ! isset( $this->wpda_table_design->indexes ) ) {
				$structure_messages[] = [ 'ERR', 'Invalid structure [missing element indexes]' ];
			} else {
				$unique_index_names = [];
				foreach ( $this->wpda_table_design->indexes as $index ) {
					$unique_index_names[ $index->index_name ] = true;
				}
				if ( count( $unique_index_names ) !== count( $this->wpda_table_design->indexes ) ) {
					$structure_messages[] = [ 'ERR', 'Index name must be unique within a table' ];
				}
			}
			return $structure_messages;

		}

		/**
		 * Prepare insert statement (process arguments)
		 *
		 * @since 1.1.0
		 */
		public function prepare_insert() {
			$this->wpda_table_design = (object) null;

			if ( isset( $_REQUEST['design_mode'] ) ) {
				$this->wpda_table_design->design_mode = sanitize_text_field( wp_unslash( $_REQUEST['design_mode'] ) );
			} else {
				$this->wpda_table_design->design_mode = WPDA::get_option( WPDA::OPTION_BE_DESIGN_MODE );
			}

			if ( isset( $_REQUEST['engine'] ) ) {
				$this->wpda_table_design->engine = sanitize_text_field( wp_unslash( $_REQUEST['engine'] ) );
			} else {
				$this->wpda_table_design->engine = '';
			}

			if ( isset( $_REQUEST['collation'] ) ) {
				$this->wpda_table_design->collation = sanitize_text_field( wp_unslash( $_REQUEST['collation'] ) );
			} else {
				$this->wpda_table_design->collation = '';
			}

			$this->wpda_table_design->table = $this->get_table_structure();

			$this->wpda_table_design->indexes = [];
		}

		/**
		 * Add a new table design
		 *
		 * @return bool TRUE = insert successful, FALSE ; insert failed.
		 * @since 1.1.0
		 *
		 */
		public function insert() {
			if ( null === $this->wpda_table_name || null === $this->wpda_schema_name ) {
				return false;
			}

			$this->prepare_insert();

			$this->wpda_table_design->table_type = self::get_table_type( $this->table_name, $this->wpda_schema_name );

			global $wpdb;

			return
				$wpdb->insert(
					$this->table_name,
					[
						'wpda_table_name'   => $this->wpda_table_name,
						'wpda_schema_name'  => $this->wpda_schema_name,
						'wpda_table_design' => json_encode( $this->wpda_table_design ),
					]
				);

		}

		/**
		 * Get structure of table design from post variables
		 *
		 * @return array Table design.
		 * @since 1.1.0
		 *
		 */
		protected function get_table_structure() {
			$table_structure = [];

			if ( isset( $_REQUEST['column_name'] ) ) {
				$no_columns = count( $_REQUEST['column_name'] );
				for ( $i = 0; $i < $no_columns; $i ++ ) {
					if ( isset( $_REQUEST['row_action'][ $i ] ) && 'd' === $_REQUEST['row_action'][ $i ] ) {
						// Do not process deleted rows.
						continue;
					}

					$column_name    = sanitize_text_field( wp_unslash( $_REQUEST['column_name'][ $i ] ) );
					$data_type      = sanitize_text_field( wp_unslash( $_REQUEST['data_type'][ $i ] ) );
					$type_attribute = sanitize_text_field( wp_unslash( $_REQUEST['type_attribute'][ $i ] ) );
					$key            = sanitize_text_field( wp_unslash( $_REQUEST['key'][ $i ] ) );
					$mandatory      = sanitize_text_field( wp_unslash( $_REQUEST['mandatory'][ $i ] ) );
					$max_length     = sanitize_text_field( wp_unslash( $_REQUEST['max_length'][ $i ] ) );
					$extra          = sanitize_text_field( wp_unslash( $_REQUEST['extra'][ $i ] ) );
					$default        = sanitize_text_field( wp_unslash( $_REQUEST['default'][ $i ] ) );
					$list           = sanitize_text_field( wp_unslash( $_REQUEST['list'][ $i ] ) );

					$table_structure[ $i ]['column_name']    = $column_name;
					$table_structure[ $i ]['data_type']      = $data_type;
					$table_structure[ $i ]['type_attribute'] = $type_attribute;
					$table_structure[ $i ]['key']            = $key;
					$table_structure[ $i ]['mandatory']      = $mandatory;
					$table_structure[ $i ]['max_length']     = $max_length;
					$table_structure[ $i ]['extra']          = $extra;
					$table_structure[ $i ]['default']        = $default;
					$table_structure[ $i ]['list']           = $list;
				}
			}

			return $table_structure;
		}

		/**
		 * Get table indexes from post variables
		 *
		 * @return array Index design.
		 * @since 1.1.0
		 *
		 */
		protected function get_indexes() {
			$indexes = [];

			if ( isset( $_REQUEST['column_names'] ) ) {
				$no_columns = count( $_REQUEST['column_names'] );
				for ( $i = 0; $i < $no_columns; $i ++ ) {
					if ( isset( $_REQUEST['row_action'][ $i ] ) && 'd' === $_REQUEST['row_action'][ $i ] ) {
						// Do not process deleted rows.
						continue;
					}

					$index_name   = sanitize_text_field( wp_unslash( $_REQUEST['index_name'][ $i ] ) );
					$unique       = sanitize_text_field( wp_unslash( $_REQUEST['unique'][ $i ] ) );
					$column_names = sanitize_text_field( wp_unslash( $_REQUEST['column_names'][ $i ] ) );

					$indexes[ $i ]['index_name']   = $index_name;
					$indexes[ $i ]['unique']       = $unique;
					$indexes[ $i ]['column_names'] = $column_names;
				}
			}

			return $indexes;
		}

		/**
		 * Prepare update statement
		 *
		 * @since 1.1.0
		 *
		 * Get table structure from database and update it using the arguments in the request.
		 */
		public function prepare_update() {
			$this->query(
				$this->wpda_table_name_original === $this->wpda_table_name ? null : $this->wpda_table_name_original,
				$this->wpda_schema_name_original === $this->wpda_schema_name ? null : $this->wpda_schema_name_original
			);

			if ( isset( $_REQUEST['design_mode'] ) ) {
				$this->wpda_table_design->design_mode = sanitize_text_field( wp_unslash( $_REQUEST['design_mode'] ) );
			}

			if ( isset( $_REQUEST['engine'] ) ) {
				$this->wpda_table_design->engine = sanitize_text_field( wp_unslash( $_REQUEST['engine'] ) );
			}

			if ( isset( $_REQUEST['collation'] ) ) {
				$this->wpda_table_design->collation = sanitize_text_field( wp_unslash( $_REQUEST['collation'] ) );
			}

			if ( isset( $_REQUEST['column_name'] ) ) {
				$this->wpda_table_design->table = $this->get_table_structure();
			} else {
				if ( isset( $_REQUEST['submitted_changes'] ) && 'table' === $_REQUEST['submitted_changes'] ) {
					$this->wpda_table_design->table = [];
				}
			}

			if ( isset( $_REQUEST['column_names'] ) ) {
				$this->wpda_table_design->indexes = $this->get_indexes();
			} else {
				if ( isset( $_REQUEST['submitted_changes'] ) && 'indexes' === $_REQUEST['submitted_changes'] ) {
					$this->wpda_table_design->indexes = [];
				}
			}
		}

		/**
		 * Update table design
		 *
		 * @return bool TRUE = update successfull, FALSE ; update failed.
		 * @since 1.1.0
		 *
		 */
		public function update() {
			if ( null === $this->wpda_table_name || null === $this->wpda_schema_name ) {
				return false;
			}

			$this->prepare_update();

			global $wpdb;

			return
				$wpdb->update(
					$this->table_name,
					[
						'wpda_table_name'   => $this->wpda_table_name,
						'wpda_schema_name'  => $this->wpda_schema_name,
						'wpda_table_design' => json_encode( $this->wpda_table_design ),
					],
					[
						'wpda_table_name'  => $this->wpda_table_name_original === $this->wpda_table_name ? $this->wpda_table_name : $this->wpda_table_name_original,
						'wpda_schema_name' => $this->wpda_schema_name_original === $this->wpda_schema_name ? $this->wpda_schema_name : $this->wpda_schema_name_original,
					]
				);

		}

		/**
		 * Simple list containing all table names in data designer table
		 *
		 * @return array
		 */
		public static function get_designer_table_list() {
			global $wpdb;

			$query = 'SELECT wpda_table_name FROM ' . self::get_base_table_name();

			return $wpdb->get_results( $query, 'ARRAY_A' );
		}

		/**
		 * Reverse engineer table and store result in data designer table
		 *
		 * @param $wpda_table_name
		 * @param $wpda_schema_name
		 * @param $wpda_table_design
		 *
		 * @return bool
		 */
		public static function insert_reverse_engineered( $wpda_table_name, $wpda_schema_name, $wpda_table_design ) {
			global $wpdb;

			$table_name = self::get_base_table_name();

			$wpda_table_design['table_type'] = self::get_table_type( $wpda_table_name, $wpda_schema_name );

			return
				(
					1 === $wpdb->insert(
						$table_name,
						[
							'wpda_table_name'   => $wpda_table_name,
							'wpda_schema_name'  => $wpda_schema_name,
							'wpda_table_design' => json_encode( $wpda_table_design ),
						]
					)
				);
		}

		/**
		 * Get table type from MySQL dictionary
		 *
		 * @param string $schema_name Schema name
		 * @param string $table_name Table name
		 *
		 * @return bool
		 */
		public static function get_table_type( $table_name, $schema_name ) {
			global $wpdb;

			$query =
				$wpdb->prepare( "
							SELECT table_type AS table_type
							  FROM information_schema.tables
							 WHERE table_schema = %s
							   AND table_name   = %s
						",
					[
						$schema_name,
						$table_name,
					]
				); // db call ok; no-cache ok.

			$result = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			if ( 1 !== $wpdb->num_rows ) {
				return false;
			} else {
				if ( strpos( $result[0]['table_type'], 'VIEW' ) === false ) {
					return 'TABLE';
				} else {
					return 'VIEW';
				}
			}
		}

		/**
		 * Convert MySQL data type to basic data type (used in basic mode)
		 *
		 * @param string $arg MySQL data type.
		 *
		 * @return string Basic data type.
		 * @since   1.6.0
		 *
		 */
		public static function datatype2basic( $arg ) {
			switch ( $arg ) {
				case 'tinyint':
				case 'smallint':
				case 'mediumint':
				case 'int':
				case 'integer':
				case 'bigint':
				case 'year':
					return 'Integer';

				case 'decimal':
				case 'float':
				case 'double':
				case 'real':
					return 'Real';

				case 'bool':
				case 'boolean':
					return 'Boolean';

				case 'date':
				case 'time':
				case 'datetime':
				case 'timestamp':
					return 'Datetime';

				case 'enum':
				case 'set':
					return 'List';

				case 'binary':
				case 'varbinary':
				case 'tinyblob':
				case 'blob':
				case 'mediumblob':
				case 'longblob':
					return 'Binary';

				default:
					return 'Text';
			}
		}

	}

}
