<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Design_Table
 */

namespace WPDataAccess\Design_Table {

	use WPDataAccess\Connection\WPDADB;
	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;
	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataAccess\Utilities\WPDA_Reverse_Engineering;
	use WPDataAccess\Plugin_Table_Models\WPDA_Design_Table_Model;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Design_Table_Form
	 *
	 * This class provides the user interface of the Data Designer tool. The form can be used to create or alter table
	 * and index designs. Tables and indexes can be created from their design by using the appropriate buttons.
	 *
	 * Tables and their indexes can be reverse engineered from the database. This process can be started from scratch
	 * or applied to an existing table design. For the last situation users can use the reconcile button. This will
	 * bring the table and index design in the same state as their physical database counterpart.
	 *
	 * @author  Peter Schulz
	 * @since   1.1.0
	 */
	class WPDA_Design_Table_Form {

		const NEW_LINE = '<br/>';

		/**
		 * Page name (wpda_designer)
		 *
		 * @var string|null
		 */
		protected $page = null;

		/**
		 * Action argument
		 *
		 * @var string|null
		 */
		protected $action = null;

		/**
		 * Saved value of action2 argument used for later equations
		 *
		 * @var string|null
		 */
		protected $action2 = null;

		/**
		 * Action2 argument
		 *
		 * @var string|null
		 */
		protected $action2_posted = null;

		/**
		 * Object of type WPDA_Design_Table_Model used for data manipulation
		 *
		 * @var object|null
		 */
		protected $model = null;

		/**
		 * Table name
		 *
		 * @var string|null
		 */
		protected $wpda_table_name = null;

		/**
		 * Schema name
		 *
		 * @var string|null
		 */
		protected $wpda_schema_name = null;

		/**
		 * Table design (taken from WPDA_Design_Table_Model)
		 *
		 * @var array|null
		 */
		protected $wpda_table_design = null;

		/**
		 * Indicates whether the table was found in the database
		 *
		 * @var boolean|null
		 */
		protected $table_exists = null;

		/**
		 * Indicates whether the structure of the database table equals the table design
		 *
		 * @var boolean|null
		 */
		protected $table_altered = false;

		/**
		 * Indicates whether the table is a WordPress table
		 *
		 * @var boolean|null
		 */
		protected $is_wp_table = false;

		/**
		 * Create table statement for the designed table at startup. Updates in the user interface are not immediately
		 * reflected. User needs to reload/save the page.
		 *
		 * @var string
		 */
		protected $create_table_statement = '';

		/**
		 * Alter table statement for the designed table at startup. Updates in the user interface are not immediately
		 * reflected. User needs to reload/save the page.
		 *
		 * @var array
		 */
		protected $alter_table_statement = [];


		/**
		 * Create index statement(s) for the designed index(es) at startup. Updates in the user interface are not
		 * immediately reflected. User needs to reload/save the page.
		 *
		 * @var string
		 */
		protected $create_index_statement = '';

		/**
		 * Indicates where a create table statement succeeded
		 *
		 * @var boolean|null
		 */
		protected $create_table_succeeded = null;

		/**
		 * Indicates where a alter table statement succeeded
		 *
		 * @var boolean|null
		 */
		protected $alter_table_succeeded = null;

		/**
		 * Indicates where a create index statement failed
		 *
		 * @var boolean|null
		 */
		protected $create_index_failed = null;

		/**
		 * Named array holding table columns
		 *
		 * @see WPDA_List_Columns_Cache::get_list_columns()
		 *
		 * @var array|null
		 */
		protected $table_columns = null;

		/**
		 * Allowed values are: Basic or Advanced. Can be supplied as an argument. Taken from WPDA class f no argument
		 * is provided.
		 *
		 * @see WPDA::OPTION_BE_DESIGN_MODE
		 *
		 * @var string|null
		 */
		protected $design_mode = null;

		/**
		 * Last error from wpdb
		 *
		 * @var string|null
		 */
		protected $wpdb_error = null;

		/**
		 * Indicates whether columns or indexes were deleted in the actual design. If true checkbox "Show deleted
		 * columns and indexes" will be accessible.
		 *
		 * @var bool
		 */
		protected $deleted_columns_and_indexes = false;

		/**
		 * Indicates whether indexes were updated in the actual design.
		 *
		 * @var bool
		 */
		protected $updated_indexes = false;

		/**
		 * Holds the structure of the real physical database table
		 *
		 * @var array|null
		 */
		protected $real_table = null;

		/**
		 * Holds the structure of the real physical database indexes
		 *
		 * @var array|null
		 */
		protected $real_indexes = null;

		/**
		 * Indicates whether the design has indexes.
		 *
		 * @var bool
		 */
		protected $indexes_found = false;

		/**
		 * Argument which can be used to jump back to another page than the default table list for table designs.
		 *
		 * @var string
		 */
		protected $caller = '';

		/**
		 * Holds all available databases
		 *
		 * @var array
		 */
		protected $database = [];

		protected $fulltext_support = false;

		/**
		 * WPDA_Design_Table_Form constructor.
		 *
		 * @since 1.1.0
		 */
		public function __construct() {
			if ( isset( $_REQUEST['page'] ) ) {
				$this->page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ); // input var okay.
			} else {
				wp_die( __( 'ERROR: Wrong arguments [page not found]', 'wp-data-access' ) );
			}

			if ( isset( $_REQUEST['action'] ) ) {
				$this->action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // input var okay.
			}

			if ( isset( $_REQUEST['action2'] ) ) {
				$this->action2        = sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) ); // input var okay.
				$this->action2_posted = $this->action2;
			}

			if ( isset( $_REQUEST['design_mode'] ) ) {
				$this->design_mode = sanitize_text_field( wp_unslash( $_REQUEST['design_mode'] ) ); // input var okay.
			} else {
				$this->design_mode = WPDA::get_option( WPDA::OPTION_BE_DESIGN_MODE ); // Default design mode.
			}

			if ( isset( $_REQUEST['caller'] ) ) {
				$this->caller = sanitize_text_field( wp_unslash( $_REQUEST['caller'] ) ); // input var okay.
			}

			$this->fulltext_support = get_option( 'wpda_fulltext_support' );

			global $wpdb;

			if ( 'init' === $this->action2 ) {
				if ( isset( $_REQUEST['wpda_table_name'] ) && isset( $_REQUEST['wpda_schema_name'] ) ) {
					// Check if table is already in repository.
					$query =
						$wpdb->prepare(
							'select * from ' . WPDA_Design_Table_Model::get_base_table_name() .
							" where wpda_schema_name = %s and wpda_table_name = %s ",
							[
								sanitize_text_field( wp_unslash( $_REQUEST['wpda_schema_name'] ) ),
								sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_name'] ) ),
							]
						);
					$wpdb->get_results( $query );
					if ( 1 === $wpdb->num_rows ) {
						// Table already in repository.
						$this->action2 = 'edit';
					} else {
						// Table not in repository.
						$this->action2 = 'wpda_reverse_engineering';
					}
				} else {
					wp_die( __( 'ERROR: Wrong arguments [table not found]', 'wp-data-access' ) );
				}
			}

			if ( 'wpda_reverse_engineering' === $this->action2 || 'wpda_reconcile' === $this->action2 ) {
				if ( isset( $_REQUEST['wpda_table_name_re'] ) && isset( $_REQUEST['wpda_schema_name_re'] ) ) {
					$wpda_table_name_re  = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_name_re'] ) );
					$wpda_schema_name_re = sanitize_text_field( wp_unslash( $_REQUEST['wpda_schema_name_re'] ) );
					if ( 'wpda_reconcile' === $this->action2 ) {
						// Before table can be reconciled old table structure must be deleted.
						$wpdb->query(
							$wpdb->prepare(
								'delete from ' . WPDA_Design_Table_Model::get_base_table_name() .
								" where wpda_table_name = %s " .
								"   and wpda_schema_name = %s ",
								[
									$wpda_table_name_re,
									$wpda_schema_name_re,
								]
							)
						);
					}
					// Start reverse engineering tabel.
					$wpda_reverse_engineering = new WPDA_Reverse_Engineering( $wpda_table_name_re, $wpda_schema_name_re );
					$this->design_mode        = isset( $_REQUEST['design_mode_re'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['design_mode_re'] ) ) : $this->design_mode; // input var okay.
					$table_structure          = $wpda_reverse_engineering->get_designer_format( $this->design_mode );
					if ( count( $table_structure ) > 0 ) {
						if ( isset( $_REQUEST['wpda_table_name'] ) && '' !== trim( $_REQUEST['wpda_table_name'] ) ) {
							$this->wpda_table_name = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_name'] ) );
						} else {
							$this->wpda_table_name = $wpda_table_name_re;
						}
						if ( isset( $_REQUEST['wpda_schema_name'] ) && '' !== trim( $_REQUEST['wpda_schema_name'] ) ) {
							$this->wpda_schema_name = sanitize_text_field( wp_unslash( $_REQUEST['wpda_schema_name'] ) );
						} else {
							$this->wpda_schema_name = $wpda_schema_name_re;
						}
						$this->wpda_table_design = $table_structure;
					} else {
						wp_die( __( 'ERROR: Reverse engineering table failed [invalid structure]', 'wp-data-access' ) );
					}
					if ( ! WPDA_Design_Table_Model::insert_reverse_engineered( $this->wpda_table_name, $this->wpda_schema_name, $this->wpda_table_design ) ) {
						wp_die( __( 'ERROR: Reverse engineering table failed [insert failed]', 'wp-data-access' ) );
					} else {
						// Convert named array to object (needed to display structure).
						$this->wpda_table_design = json_decode( json_encode( $table_structure ) );
					}
					$this->action2 = 'edit';
				} else {
					wp_die( __( 'ERROR: Wrong arguments [table not found]', 'wp-data-access' ) );
				}
			} elseif ( isset( $_REQUEST['wpda_table_name'] ) && isset( $_REQUEST['wpda_schema_name'] ) ) {
				$this->wpda_table_name  = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_name'] ) );
				$this->wpda_schema_name = sanitize_text_field( wp_unslash( $_REQUEST['wpda_schema_name'] ) );
				$this->model            = new WPDA_Design_Table_Model();

				if ( 'new' === $this->action2 ) {
					$insert_result = $this->model->insert();
					if ( false === $insert_result || $insert_result < 1 ) {
						wp_die( __( 'ERROR: Insert failed', 'wp-data-access' ) );
					}
					$this->action2 = 'edit'; // Show saved records and allow editing.
				} elseif ( 'edit' === $this->action2 && 'init' !== $this->action2_posted ) {
					$result_update = $this->model->update();
					if ( false === $result_update ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text'           => __( 'Update failed', 'wp-data-access' ),
								'message_type'           => 'error',
								'message_is_dismissible' => false,
							]
						);
						$msg->box();
					}
					if ( 0 === $result_update ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text' => __( 'Nothing to save', 'wp-data-access' ),
							]
						);
						$msg->box();
					} else {
						$msg = new WPDA_Message_Box(
							[
								'message_text' => __( 'Succesfully saved changes to database', 'wp-data-access' ),
							]
						);
						$msg->box();
					}
				}

				$this->model->query();
				$structure_messages = $this->model->validate();
				foreach ( $structure_messages as $messages ) {
					if ( 'ERR' === $messages[0] ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text'           => $messages[1],
								'message_type'           => 'error',
								'message_is_dismissible' => false,
							]
						);
						$msg->box();
					} else {
						$msg = new WPDA_Message_Box(
							[
								'message_text' => $messages[1],
							]
						);
						$msg->box();
					}
				}
				$this->wpda_table_design = $this->model->get_table_design();
				$this->design_mode       = $this->wpda_table_design->design_mode;

				$this->action2 = 'edit'; // Editing mode.
			} else {
				$this->action2 = 'new'; // Design new table from scratch.
			}

			if ( null !== $this->wpda_table_name && null !== $this->wpda_schema_name ) {
				// Check if table name already exists in database.
				if ( $this->wpda_schema_name === $wpdb->dbname ) {
					$wp_tables = $wpdb->tables( 'all', true );
					if ( isset( $wp_tables[ substr( $this->wpda_table_name, strlen( $wpdb->prefix ) ) ] ) ) {
						$this->is_wp_table  = true;
						$this->table_exists = true;
					} else {
						$this->does_table_exist();
					}
				} else {
					$this->does_table_exist();
				}

				// Get design structure for real database table.
				$this->get_table_structure();

				if (
					'create_table' === $this->action2_posted ||
					'show_create_table_script' === $this->action2_posted
				) {
					// Perform create table (and indexes) script.
					$this->create_table();
					$this->does_table_exist();
					$this->get_table_structure();
				} elseif ( 'alter_table' === $this->action2_posted ) {
					// Generate alter table script and perform script.
					$this->do_alter_table();
					$this->get_table_structure();
				} elseif ( 'show_alter_table_script' === $this->action2_posted ) {
					// Generate alter table script and show result in overlay.
					$this->alter_table();
				} elseif ( 'create_table_index' === $this->action2_posted ) {
					// Perform create index(es) script.
					$this->create_index();
					$this->get_table_structure();
				} elseif ( 'drop_table' === $this->action2_posted ) {
					// Perform drop table script.
					$this->drop_table();
					$this->does_table_exist();
				} elseif ( 'drop_table_index' === $this->action2_posted ) {
					// Perform drop table script.
					$this->drop_indexes();
					$this->get_table_structure();
				}

				if ( $this->table_exists ) {
					// Get database table structure.
					$wpda_list_columns = WPDA_List_Columns_Cache::get_list_columns( $this->wpda_schema_name, $this->wpda_table_name );
					$table_columns     = $wpda_list_columns->get_table_columns();

					// Convert indexed array to named array to improve access.
					foreach ( $table_columns as $table_column ) {
						$this->table_columns[ $table_column['column_name'] ] = $table_column;
					}
				}
			}

			$this->databases        = WPDA_Dictionary_Lists::get_db_schemas();
		}

		/**
		 * Get table and index structure from design
		 *
		 * @since 2.0.14
		 */
		private function get_table_structure() {
			if ( $this->table_exists ) {
				$get_table_structure = new WPDA_Reverse_Engineering( $this->wpda_table_name, $this->wpda_schema_name );
				$real_structure      = $get_table_structure->get_designer_format( $this->design_mode );
				$this->real_table    = $real_structure['table'];
				$this->real_indexes  = $real_structure['indexes'];
			}
		}

		/**
		 * Check if table exists in our database
		 *
		 * @since 2.0.14
		 */
		private function does_table_exist() {
			if ( 'rdb:' !== substr( $this->wpda_schema_name, 0, 4) ) {
				$wpda_dictionary_exists = new WPDA_Dictionary_Exist( $this->wpda_schema_name, $this->wpda_table_name );
				$this->table_exists     = $wpda_dictionary_exists->plain_table_exists();
				if ( ! $this->table_exists ) {
					$this->real_table   = null;
					$this->real_indexes = null;
				}
			} else {
				// No remote checks
				$this->real_table   = null;
				$this->real_indexes = null;
			}
		}

		/**
		 * Perform alter table
		 *
		 * Call $this->alter_table() to generate alter table script and process result taken
		 * from $this->create_table_statement.
		 *
		 * @see WPDA_Design_Table_Form::alter_table()
		 * @see WPDA_Design_Table_Form::create_table_statement
		 *
		 * @since 2.0.14
		 */
		protected function do_alter_table() {
			$this->alter_table();

			$wpdadb = WPDADB::get_db_connection( $this->wpda_schema_name );
			if ( null === $wpdadb ) {
				wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->wpda_schema_name ) );
			}

			$suppress               = $wpdadb->suppress_errors( true );
			$create_table_statement = str_replace( self::NEW_LINE, "\n", $this->create_table_statement );
			if ( '' !== $create_table_statement ) {
				// Process alter table script (store in $create_table_statement)
				$sql_end = strpos( $create_table_statement, ";\n" );
				while ( false !== $sql_end ) {
					$sql                    = rtrim( substr( $create_table_statement, 0, $sql_end ) );
					$create_table_statement = substr( $create_table_statement, strpos( $create_table_statement, $sql ) + strlen( $sql ) + 1 );
					if ( ! $wpdadb->query( $sql ) ) {
						$this->wpdb_error            = $wpdadb->last_error;
						$this->alter_table_succeeded = false;

						return;
					}
					$sql_end = strpos( $create_table_statement, ";\n" );
				}
			}
			$wpdadb->suppress_errors( $suppress );
		}

		/**
		 * Generate alter table script
		 *
		 * Alter table script is written to $this->create_table_statement.
		 *
		 * @see WPDA_Design_Table_Form::create_table_statement
		 *
		 * @since 2.0.14
		 */
		protected function alter_table() {
			$create_keys_design = [];
			$create_keys_real   = [];

			// Find deleted and changed indexes
			foreach ( $this->real_indexes as $real_index ) {
				$drop_real_index    = false;
				$design_index_found = false;
				foreach ( $this->wpda_table_design->indexes as $design_index ) {
					if ( $real_index['index_name'] === $design_index->index_name ) {
						$design_index_found = true;
						if (
							$real_index['unique'] != $design_index->unique ||
							$real_index['column_names'] != $design_index->column_names
						) {
							$drop_real_index = true;
							break;
						}
						break;
					}
				}
				if ( $drop_real_index || ! $design_index_found ) {
					$this->create_table_statement .=
						"DROP INDEX `{$real_index['index_name']}` ON `{$this->wpda_table_name}`;" . self::NEW_LINE;
				}
			}
			if ( '' !== $this->create_table_statement ) {
				$this->create_table_statement .= self::NEW_LINE;
			}

			// Find new and changed columns
			foreach ( $this->wpda_table_design->table as $design_column ) {
				$design_column_found = false;
				foreach ( $this->real_table as $real_column ) {
					if ( $real_column->column_name === $design_column->column_name ) {
						if ( $real_column != $design_column ) {
							// Modify column
							$this->alter_table_column( $design_column, 'MODIFY' );
						}
						$design_column_found = true;
						break;
					}
				}

				if ( ! $design_column_found ) {
					// Add new column
					$this->alter_table_column( $design_column, 'ADD' );
				}

				if ( 'Yes' === $design_column->key ) {
					$create_keys_design[] = $design_column->column_name;
				}
			}

			// Find deleted columns
			foreach ( $this->real_table as $real_column ) {
				$real_column_found = false;
				foreach ( $this->wpda_table_design->table as $design_column ) {
					if ( $real_column->column_name === $design_column->column_name ) {
						$real_column_found = true;
						break;
					}
				}

				if ( ! $real_column_found ) {
					// Drop column
					array_push(
						$this->alter_table_statement,
						"DROP COLUMN `{$real_column->column_name}`," . self::NEW_LINE
					);
				}

				if ( 'Yes' === $real_column->key ) {
					$create_keys_real[] = $real_column->column_name;
				}
			}

			if ( 0 < count( $this->alter_table_statement ) ) {
				$this->create_table_statement .= "ALTER TABLE `{$this->wpda_table_name}` ";
				foreach ( $this->alter_table_statement as $sql ) {
					$this->create_table_statement .= $sql;
				}
				$this->create_table_statement =
					substr( $this->create_table_statement, 0, strrpos( $this->create_table_statement, ',' ) ) .
					";" . self::NEW_LINE . self::NEW_LINE;

				$array_difference_1 = array_diff( $create_keys_design, $create_keys_real );
				$array_difference_2 = array_diff( $create_keys_real, $create_keys_design );
				if ( 0 !== count( $array_difference_1 ) || 0 !== count( $array_difference_2 ) ) {
					if ( 0 < count( $create_keys_real ) ) {
						$this->create_table_statement =
							"ALTER TABLE `{$this->wpda_table_name}` DROP PRIMARY KEY;" .
							self::NEW_LINE . self::NEW_LINE . $this->create_table_statement;
					}
					if ( 0 < count( $create_keys_design ) ) {
						$alter_table_statement =
							"ALTER TABLE `{$this->wpda_table_name}` ADD PRIMARY KEY  ";
						foreach ( $create_keys_design as $key ) {
							$alter_table_statement .= $key === reset( $create_keys_design ) ? '(' : ',';
							$alter_table_statement .= "`$key`";
						}
						$alter_table_statement .= ');' . self::NEW_LINE . self::NEW_LINE;

						$this->create_table_statement .= $alter_table_statement;
					}
				}
			}

			// Add new and changed indexes
			foreach ( $this->wpda_table_design->indexes as $design_index ) {
				$real_index_found = false;
				$create_new_index = false;
				foreach ( $this->real_indexes as $real_index ) {
					if ( $real_index['index_name'] === $design_index->index_name ) {
						$real_index_found = true;
						if (
							$real_index['unique'] != $design_index->unique ||
							$real_index['column_names'] != $design_index->column_names
						) {
							$create_new_index = true;
							break;
						}
						break;
					}
				}
				if ( ! $real_index_found || $create_new_index ) {
					$unique = '';
					if ( 'Yes' === $design_index->unique ) {
						$unique = 'UNIQUE';
					} elseif ( 'FULLTEXT' === $design_index->unique ) {
						if ( 'on' === $this->fulltext_support ) {
							$unique = 'FULLTEXT';
						} else {
							$unique = '';
						}
					}
					$column_names_array           = explode( ',', $design_index->column_names );
					$column_names                 = '`' . implode( '`,`', $column_names_array ) . '`';
					$this->create_table_statement .=
						"CREATE $unique INDEX `{$design_index->index_name}` ON `{$this->wpda_table_name}` ($column_names);" .
						self::NEW_LINE;
				}
			}
		}

		/**
		 * Alter column format
		 *
		 * @param object $design_column Column definition
		 * @param string $keyword ADD or MODIFY
		 *
		 * @since 2.0.14
		 *
		 */
		private function alter_table_column( $design_column, $keyword ) {
			$alter_table_statement = "$keyword COLUMN `{$design_column->column_name}` ";
			$alter_table_statement .= $design_column->data_type;
			if ( '' !== $design_column->max_length ) {
				$alter_table_statement .= "($design_column->max_length)";
			}
			if ( 'enum' === $design_column->data_type || 'set' === $design_column->data_type ) {
				$alter_table_statement .= '(' . $design_column->list . ')';
			}
			$alter_table_statement .= ' ';
			$alter_table_statement .= 'Yes' === $design_column->mandatory ? 'NOT NULL' : 'NULL';
			if ( '' !== $design_column->default ) {
				$alter_table_statement .= " DEFAULT {$design_column->default}";
			}
			if ( '' !== $design_column->extra ) {
				$alter_table_statement .= ' ';
				$alter_table_statement .= $design_column->extra;
			}
			$alter_table_statement .= ',' . self::NEW_LINE;

			array_push(
				$this->alter_table_statement,
				$alter_table_statement
			);
		}

		/**
		 * Drop database table
		 *
		 * Does not drop WordPress tables.
		 *
		 * @since 2.0.14
		 */
		protected function drop_table() {
			if ( $this->is_wp_table ) {
				$msg = new WPDA_Message_Box(
					[
						'message_text'           => sprintf( __( 'Cannot drop WordPress table `%s`', 'wp-data-access' ), $this->wpda_table_name ),
						'message_type'           => 'error',
						'message_is_dismissible' => false,
					]
				);
				$msg->box();

				return;
			}

			if ( $this->table_exists ) {
				$wpdadb = WPDADB::get_db_connection( $this->wpda_schema_name );
				if ( null === $wpdadb ) {
					wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->wpda_schema_name ) );
				}

				$suppress             = $wpdadb->suppress_errors( true );
				$drop_table_statement = "DROP TABLE `{$this->wpda_table_name}`";
				if ( $wpdadb->query( $drop_table_statement ) ) {
					$msg = new WPDA_Message_Box(
						[
							'message_text' => sprintf( __( 'Table `%s` dropped', 'wp-data-access' ), $this->wpda_table_name ),
						]
					);
					$msg->box();
				} else {
					$msg = new WPDA_Message_Box(
						[
							'message_text'           => __( 'DROP TABLE failed', 'wp-data-access' ),
							'message_type'           => 'error',
							'message_is_dismissible' => false,
						]
					);
					$msg->box();
					$this->create_table_statement = $drop_table_statement;
				}
				$wpdadb->suppress_errors( $suppress );
			}
		}

		/**
		 * Perform create table statement
		 *
		 * @since 1.1.0
		 */
		protected function create_table() {
			$this->create_table_statement = "CREATE TABLE `{$this->wpda_table_name}`" . self::NEW_LINE;

			$create_keys = [];
			foreach ( $this->wpda_table_design->table as $row ) {
				$this->create_table_statement .= $row === reset( $this->wpda_table_design->table ) ? '(' : ',';
				$this->create_table_statement .= "`{$row->column_name}`";
				$this->create_table_statement .= ' ';
				$this->create_table_statement .= $row->data_type;
				if ( '' !== $row->max_length ) {
					$this->create_table_statement .= "($row->max_length)";
				}
				if ( '' !== $row->type_attribute ) {
					$this->create_table_statement .= " {$row->type_attribute} ";
				}
				if ( 'enum' === $row->data_type || 'set' === $row->data_type ) {
					$this->create_table_statement .= '(' . $row->list . ')';
				}
				$this->create_table_statement .= ' ';
				$this->create_table_statement .= 'Yes' === $row->mandatory ? 'NOT NULL' : 'NULL';
				if ( '' !== $row->default ) {
					$this->create_table_statement .= " DEFAULT {$row->default}";
				}
				if ( '' !== $row->extra ) {
					$this->create_table_statement .= ' ';
					$this->create_table_statement .= $row->extra;
				}
				if ( 'Yes' === $row->key ) {
					$create_keys[] = $row->column_name;
				}
				$this->create_table_statement .= self::NEW_LINE;
			}
			if ( 0 < count( $create_keys ) ) {
				$this->create_table_statement .= ',PRIMARY KEY ';
				foreach ( $create_keys as $key ) {
					$this->create_table_statement .= $key === reset( $create_keys ) ? '(' : ',';
					$this->create_table_statement .= "`$key`";
				}
				$this->create_table_statement .= ')';
				$this->create_table_statement .= self::NEW_LINE;
			}
			$this->create_table_statement .= ')';
			if ( isset( $this->wpda_table_design->engine ) && '' !== $this->wpda_table_design->engine ) {
				$this->create_table_statement .= ' ENGINE ' . $this->wpda_table_design->engine;
			}
			if ( isset( $this->wpda_table_design->collation ) && '' !== $this->wpda_table_design->collation ) {
				$collation = explode( '_', $this->wpda_table_design->collation );
				$this->create_table_statement .= ' DEFAULT CHARACTER SET ' . $collation[0] . ' COLLATE=' . $this->wpda_table_design->collation;
			}
			$this->create_table_statement .= ';' . self::NEW_LINE . self::NEW_LINE;

			if ( 'show_create_table_script' === $this->action2_posted ) {
				// Just show CREATE TABLE script!
				// SQL script is available in $this->create_table_statement and can be shown on the page.
				$this->create_index();
			} else {
				// Create table and indexes.
				$wpdadb = WPDADB::get_db_connection( $this->wpda_schema_name );
				if ( null === $wpdadb ) {
					wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->wpda_schema_name ) );
				}

				$suppress = $wpdadb->suppress_errors( true );

				$this->create_table_succeeded = $wpdadb->query( str_replace( self::NEW_LINE, '', $this->create_table_statement ) );
				if ( $this->create_table_succeeded ) {
					$msg = new WPDA_Message_Box(
						[
							'message_text' => __( 'Table created', 'wp-data-access' ),
						]
					);
					$msg->box();

				} else {
					$msg = new WPDA_Message_Box(
						[
							'message_text'           => __( 'CREATE TABLE failed', 'wp-data-access' ),
							'message_type'           => 'error',
							'message_is_dismissible' => false,
						]
					);
					$msg->box();
					$this->wpdb_error = $wpdadb->last_error;
				}

				$wpdadb->suppress_errors( $suppress );
			}
		}

		/**
		 * Show Data Designer form
		 *
		 * @since 1.1.0
		 */
		public function show() {
			?>
			<script type='text/javascript'>
				var row_num = 1;
				var index_num = 1;

				var table_updated = false;
				var index_updated = false;

				var no_cols_selected = 'no column(s) selected';

				function disable_page() {
					jQuery(".wpda_view").prop("readonly", true).prop("disabled", true).addClass("disabled");
					disable_table();
					disable_index();
					disable_create_buttons();
					jQuery('#reconcile_button').prop("readonly", true).prop("disabled", true).addClass("disabled");
					jQuery('.design_mode').prop("readonly", true).prop("disabled", true).addClass("disabled");
					jQuery('.column_names').prop("readonly", true).prop("disabled", true).addClass("disabled");
				}

				function disable_table() {
					jQuery(".wpda_view_table").prop("readonly", true).prop("disabled", true).addClass("disabled");
				}

				function disable_index() {
					jQuery(".wpda_view_index").prop("readonly", true).prop("disabled", true).addClass("disabled");
				}

				function disable_create_buttons() {
					jQuery("#button_show_create_table").prop("readonly", true).prop("disabled", true).addClass("disabled");
					jQuery("#button_show_alter_table").prop("readonly", true).prop("disabled", true).addClass("disabled");
					jQuery("#button_create_table").prop("readonly", true).prop("disabled", true).addClass("disabled");
					jQuery("#button_alter_table").prop("readonly", true).prop("disabled", true).addClass("disabled");
					jQuery("#button_create_index").prop("readonly", true).prop("disabled", true).addClass("disabled");
					jQuery("#button_recreate_index").prop("readonly", true).prop("disabled", true).addClass("disabled");
				}

				function rem_row(e) {
					var curr_id = e.target.parentNode.parentNode.id;
					if (confirm("Delete column?")) {
						jQuery("#" + curr_id).remove();
						updated_table();
					}
				}

				function rem_index(e) {
					var curr_id = e.target.parentNode.parentNode.id;
					if (confirm("Delete index?")) {
						jQuery("#" + curr_id).remove();
						updated_indexes();
					}
				}

				function updated_table() {
					disable_create_buttons();
					disable_index();
					table_updated = true;
				}

				function updated_indexes() {
					disable_create_buttons();
					disable_table();
					index_updated = true;
				}

				function check_basic_data_type(row_num) {
					check_numeric_items_off();
					switch (jQuery('#basic_data_type_' + row_num).val()) {
						case 'Text':
							jQuery('#data_type_' + row_num).val('text');
							jQuery('#max_length_' + row_num).val('').attr('class', 'wpda_nodataentryallowed wpda_view_table');
							break;
						case 'Integer':
							jQuery('#data_type_' + row_num).val('int');
							jQuery('#max_length_' + row_num).attr('class', 'wpda_digits_only wpda_view_table');
							break;
						case 'Real':
							jQuery('#data_type_' + row_num).val('float');
							jQuery('#max_length_' + row_num).attr('class', 'wpda_real wpda_view_table');
							break;
						case 'List':
							jQuery('#data_type_' + row_num).val('enum');
							jQuery('#max_length_' + row_num).val('').attr('class', 'wpda_nodataentryallowed wpda_view_table');
							break;
						case 'Boolean':
							jQuery('#data_type_' + row_num).val('tinyint');
							jQuery('#max_length_' + row_num).attr('class', 'wpda_digits_only wpda_view_table');
							break;
						case 'Datetime':
							jQuery('#data_type_' + row_num).val('datetime');
							jQuery('#max_length_' + row_num).val('').attr('class', 'wpda_nodataentryallowed wpda_view_table');
							break;
						case 'Binary':
							jQuery('#data_type_' + row_num).val('binary');
							jQuery('#max_length_' + row_num).attr('class', 'wpda_digits_only wpda_view_table');
							break;
						case 'Blob':
							jQuery('#data_type_' + row_num).val('blob');
							jQuery('#max_length_' + row_num).val('').attr('class', 'wpda_nodataentryallowed wpda_view_table');
							break;
						case '*ID':
							jQuery('#data_type_' + row_num).val('int');
							jQuery('#key_' + row_num).val('Yes');
							jQuery('#mandatory_' + row_num).val('Yes');
							jQuery('#max_length_' + row_num).val('').attr('class', 'wpda_digits_only wpda_view_table');
							jQuery('#extra_' + row_num).val('AUTO_INCREMENT');
							jQuery('#default_' + row_num).val('');
							jQuery('#list_' + row_num).val('');
							break;
						case '*TimestampC':
							jQuery('#data_type_' + row_num).val('timestamp');
							jQuery('#key_' + row_num).val('No');
							jQuery('#mandatory_' + row_num).val('No');
							jQuery('#max_length_' + row_num).val('').attr('class', 'wpda_nodataentryallowed wpda_view_table');
							jQuery('#extra_' + row_num).val('');
							jQuery('#default_' + row_num).val('CURRENT_TIMESTAMP');
							jQuery('#list_' + row_num).val('');
							break;
						case '*TimestampU':
							jQuery('#data_type_' + row_num).val('timestamp');
							jQuery('#key_' + row_num).val('No');
							jQuery('#mandatory_' + row_num).val('No');
							jQuery('#max_length_' + row_num).val('').attr('class', 'wpda_nodataentryallowed wpda_view_table');
							jQuery('#extra_' + row_num).val('ON UPDATE CURRENT_TIMESTAMP');
							jQuery('#default_' + row_num).val('CURRENT_TIMESTAMP');
							jQuery('#list_' + row_num).val('');
							break;
					}
					check_numeric_items_on();
				}

				function check_data_type(row_num) {
					check_numeric_items_off();
					console.log(row_num, jQuery('#data_type_' + row_num + ' :selected').parent().attr('label'));
					switch (jQuery('#data_type_' + row_num + ' :selected').parent().attr('label')) {
						case 'Integer':
							jQuery('#basic_data_type_' + row_num).val('Integer');
							jQuery('#max_length_' + row_num).attr('class', 'wpda_digits_only wpda_view_table');
							break;
						case 'Text':
							jQuery('#basic_data_type_' + row_num).val('Text');
							switch (jQuery('#data_type_' + row_num + ' :selected').val()) {
								case 'char':
								case 'varchar':
									jQuery('#max_length_' + row_num).attr('class', 'wpda_digits_only wpda_view_table');
									break;
								default:
									jQuery('#max_length_' + row_num).val('').attr('class', 'wpda_nodataentryallowed wpda_view_table');
							}
							break;
						case 'List':
							jQuery('#basic_data_type_' + row_num).val('List');
							jQuery('#max_length_' + row_num).val('').attr('class', 'wpda_nodataentryallowed wpda_view_table');
							break;
						case 'Date and Time':
							jQuery('#basic_data_type_' + row_num).val('Datetime');
							jQuery('#max_length_' + row_num).val('').attr('class', 'wpda_nodataentryallowed wpda_view_table');
							break;
						case 'Real':
							jQuery('#basic_data_type_' + row_num).val('Real');
							jQuery('#max_length_' + row_num).attr('class', 'wpda_real wpda_view_table');
							break;
						case 'Binary':
							switch (jQuery('#data_type_' + row_num + ' :selected').val()) {
								case 'binary':
								case 'varbinary':
									jQuery('#basic_data_type_' + row_num).val('Binary');
									jQuery('#max_length_' + row_num).attr('class', 'wpda_digits_only wpda_view_table');
									break;
								default:
									jQuery('#basic_data_type_' + row_num).val('Blob');
									jQuery('#max_length_' + row_num).val('').attr('class', 'wpda_nodataentryallowed wpda_view_table');
							}
							break;
						case 'Boolean':
							jQuery('#basic_data_type_' + row_num).val('Boolean');
							jQuery('#max_length_' + row_num).attr('class', 'wpda_digits_only wpda_view_table');
							break;
					}
					check_numeric_items_on();
				}

				function check_numeric_items_off() {
					jQuery('.wpda_digits_only').off('keyup paste');
					jQuery('.wpda_real').off('keyup paste');
					jQuery('.wpda_nodataentryallowed').off('keyup paste');
				}

				function check_numeric_items_on() {
					jQuery('.wpda_digits_only').on('keyup paste', function () {
						this.value = this.value.replace(/[^\d]/g, '');
					});
					jQuery('.wpda_real').on('keyup paste', function () {
						this.value = this.value.replace(/[^0-9,]/g, '');
					});
					jQuery('.wpda_nodataentryallowed').on('keyup paste', function () {
						this.value = '';
					});
				}

				function pre_submit() {
					if (jQuery('#wpda_table_name').val() === '') {
						alert('<?php echo __( 'Table name cannot be empty' ); ?>');
						return false;
					}
					if ('<?php echo esc_attr( $this->action2 ); ?>' === 'new') {
						if (wpda_db_table_name[jQuery('#wpda_table_name').val()]) {
							alert('<?php echo __( 'Table name already used for another table design' ); ?>');
							return false;
						}
					}
					var all_columns_entered = true;
					jQuery("input[name='column_name[]']").each(function () {
						if (jQuery(this).val() === '') {
							alert('<?php echo __( 'Column names cannot be empty' ); ?>');
							all_columns_entered = false;
						}
					});
					if (!all_columns_entered) {
						return false;
					}
					// Enable all listboxes that have been disable to add arguments to request.
					jQuery("select[id^='key']").each(function () {
						jQuery(this).attr('disabled', false);
					});
					jQuery("select[id^='mandatory']").each(function () {
						jQuery(this).attr('disabled', false);
					});
					return true;
				}

				function add_row(design_mode, init = false, column_name = '', basic_data_type = '', data_type = '', type_attribute = '', key = '', mandatory = '', max_length = '', extra = '', default_value = '', list = '', row_action = '') {
					var row_class = '';
					switch (row_action) {
						case 'u':
							row_class = 'wpda_column_updated';
							break;
						case 'i':
							row_class = 'wpda_column_inserted';
							break;
						case 'd':
							row_class = 'wpda_column_deleted';
					}
					var basic_columns = '<td>' +
						'<select name="basic_data_type[]" id="basic_data_type_' + row_num + '" onchange="check_basic_data_type(' + row_num + ')" class="wpda_view_table">' +
						'	<option value="Text" ' + (basic_data_type === 'Text' ? 'selected' : '') + '>Text</option>' +
						'	<option value="Integer" ' + (basic_data_type === 'Integer' ? 'selected' : '') + '>Integer</option>' +
						'	<option value="Real" ' + (basic_data_type === 'Real' ? 'selected' : '') + '>Real</option>' +
						'	<option value="List" ' + (basic_data_type === 'List' ? 'selected' : '') + '}>List</option>' +
						'	<option value="Boolean" ' + (basic_data_type === 'Boolean' ? 'selected' : '') + '>Boolean</option>' +
						'	<option value="Datetime" ' + (basic_data_type === 'Datetime' ? 'selected' : '') + '>Datetime</option>' +
						'	<option value="Binary" ' + (basic_data_type === 'Binary' ? 'selected' : '') + '>Binary</option>' +
						'	<option value="Blob" ' + (basic_data_type === 'Blob' ? 'selected' : '') + '>Blob</option>' +
						'	<option value="*ID" ' + (basic_data_type === '*ID' ? 'selected' : '') + '>* Numeric ID (auto increment)</option>' +
						'	<option value="*TimestampC" ' + (basic_data_type === '*TimestampC' ? 'selected' : '') + '>* Timestamp (date created)</option>' +
						'	<option value="*TimestampU" ' + (basic_data_type === '*TimestampU' ? 'selected' : '') + '>* Timestamp (last updated)</option>' +
						'</select>' +
						'<input type="hidden" name="data_type[]" id="data_type_' + row_num + '" value="' + data_type + '">' +
						'<input type="hidden" name="type_attribute[]" id="type_attribute_' + row_num + '" value="' + type_attribute + '">' +
						'</td>';
					var advanced_columns = '<td>' +
						'<select name="data_type[]" id="data_type_' + row_num + '" onchange="check_data_type(' + row_num + ')" class="wpda_view_table">' +
						'	<option value="" ' + (data_type === '' ? 'selected' : '') + '></option>' +
						'	<optgroup label="Integer">' +
						'		<option value="bit" ' + (data_type === 'bit' ? 'selected' : '') + '>bit</option>' +
						'		<option value="tinyint" ' + (data_type === 'tinyint' ? 'selected' : '') + '>tinyint</option>' +
						'		<option value="smallint" ' + (data_type === 'smallint' ? 'selected' : '') + '>smallint</option>' +
						'		<option value="mediumint" ' + (data_type === 'mediumint' ? 'selected' : '') + '>mediumint</option>' +
						'		<option value="int" ' + (data_type === 'int' ? 'selected' : '') + '>int</option>' +
						'		<option value="bigint" ' + (data_type === 'bigint' ? 'selected' : '') + '>bigint</option>' +
						'	</optgroup>' +
						'	<optgroup label="Text">' +
						'		<option value="char" ' + (data_type === 'char' ? 'selected' : '') + '>char</option>' +
						'		<option value="varchar" ' + (data_type === 'varchar' ? 'selected' : '') + '>varchar</option>' +
						'		<option disabled="disabled">-</option>' +
						'		<option value="tinytext" ' + (data_type === 'tinytext' ? 'selected' : '') + '>tinytext</option>' +
						'		<option value="text" ' + (data_type === 'text' ? 'selected' : '') + '>text</option>' +
						'		<option value="mediumtext" ' + (data_type === 'mediumtext' ? 'selected' : '') + '>mediumtext</option>' +
						'		<option value="longtext" ' + (data_type === 'longtext' ? 'selected' : '') + '>longtext</option>' +
						'	</optgroup>' +
						'	<optgroup label="List">' +
						'		<option value="enum" ' + (data_type === 'enum' ? 'selected' : '') + '>enum</option>' +
						'		<option value="set" ' + (data_type === 'set' ? 'selected' : '') + '>set</option>' +
						'	</optgroup>' +
						'	<optgroup label="Date and Time">' +
						'		<option value="date" ' + (data_type === 'date' ? 'selected' : '') + '>date</option>' +
						'		<option value="datetime" ' + (data_type === 'datetime' ? 'selected' : '') + '>datetime</option>' +
						'		<option value="timestamp" ' + (data_type === 'timestamp' ? 'selected' : '') + '>timestamp</option>' +
						'		<option value="time" ' + (data_type === 'time' ? 'selected' : '') + '>time</option>' +
						'		<option value="year" ' + (data_type === 'year' ? 'selected' : '') + '>year</option>' +
						'	</optgroup>' +
						'	<optgroup label="Real">' +
						'		<option value="decimal" ' + (data_type === 'decimal' ? 'selected' : '') + '>decimal</option>' +
						'		<option value="double" ' + (data_type === 'double' ? 'selected' : '') + '>double</option>' +
						'		<option value="float" ' + (data_type === 'float' ? 'selected' : '') + '>float</option>' +
						'	</optgroup>' +
						'	<optgroup label="Binary">' +
						'		<option value="binary" ' + (data_type === 'binary' ? 'selected' : '') + '>binary</option>' +
						'		<option value="varbinary" ' + (data_type === 'varbinary' ? 'selected' : '') + '>varbinary</option>' +
						'		<option disabled="disabled">-</option>' +
						'		<option value="tinyblob" ' + (data_type === 'tinyblob' ? 'selected' : '') + '>tinyblob</option>' +
						'		<option value="blob" ' + (data_type === 'blob' ? 'selected' : '') + '>blob</option>' +
						'		<option value="mediumblob" ' + (data_type === 'mediumblob' ? 'selected' : '') + '>mediumblob</option>' +
						'		<option value="longblob" ' + (data_type === 'longblob' ? 'selected' : '') + '>longblob</option>' +
						'	</optgroup>' +
						'	<optgroup label="Boolean">' +
						'		<option value="boolean" ' + (data_type === 'boolean' ? 'selected' : '') + '>boolean</option>' +
						'	</optgroup>' +
						'</select>' +
						'<input type="hidden" name="basic_data_type[]" id="basic_data_type_' + row_num + '" value="' + basic_data_type + '">' +
						'</td>' +
						'<td>' +
						'	<select name="type_attribute[]" id="type_attribute_' + row_num + '" class="wpda_view_table">' +
						'		<option value=""></option>' +
						'		<option value="unsigned" ' + (type_attribute === 'unsigned' ? 'selected' : '') + '>unsigned</option>' +
						'		<option value="unsigned zerofill" ' + (type_attribute === 'unsigned zerofill' ? 'selected' : '') + '>unsigned zerofill</option>' +
						'	</select>' +
						'</td>';
					var new_row = '<tr id="row_num_' + row_num + '" class="' + row_class + '">' +
						'<td class="wpda-table-structure-first-column-move">' +
						'	<span class="dashicons dashicons-move grabbable" style="float:left;"></span>' +
						'</td>' +
						'<td>' +
						'	<input type="text" name="column_name[]" id="column_name_' + row_num + '"' +
						'		maxlength="64" value="' + column_name + '" class="wpda_mysql_names wpda_view_table">' +
						'</td>' +
						(design_mode === 'basic' ? basic_columns : advanced_columns) +
						'<td>' +
						'	<select name="key[]" id="key_' + row_num + '" class="wpda_view_table">' +
						'		<option value="No" ' + (key === 'No' ? 'selected' : '') + '>No</option>' +
						'		<option value="Yes" ' + (key === 'Yes' ? 'selected' : '') + '>Yes</option>' +
						'	</select>' +
						'</td>' +
						'<td>' +
						'	<select name="mandatory[]" id="mandatory_' + row_num + '" class="wpda_view_table">' +
						'		<option value="No"' + (mandatory === 'No' ? 'selected' : '') + '>No</option>' +
						'		<option value="Yes"' + (mandatory === 'Yes' ? 'selected' : '') + '>Yes</option>' +
						'	</select>' +
						'</td>' +
						'<td>' +
						'	<input type="text" name="max_length[]" id="max_length_' + row_num + '"' +
						'		value="' + (max_length === '0' ? '' : max_length) + '" class="' + (basic_data_type === 'Real' ? 'wpda_float' : 'wpda_digits_only') + ' wpda_view_table">' +
						'</td>' +
						'<td>' +
						'	<input type="text" name="extra[]" id="extra_' + row_num + '" value="' + extra + '" class="wpda_view_table">' +
						'</td>' +
						'<td>' +
						'	<input type="text" name="default[]" id="default_' + row_num + '" value="' + default_value + '" class="wpda_view_table">' +
						'</td>' +
						'<td>' +
						'	<input type="text" name="list[]" id="list_' + row_num + '" value="' + list + '" class="wpda_view_table">' +
						'	<input type="hidden" name="row_action[]" value="' + row_action + '">' +
						'</td>' +
						'<td class="wpda-table-structure-last-column">' +
						'	<a href="javascript:void(0)" onclick="rem_row(event)" class="dashicons dashicons-trash wpda_view_table wpda_tooltip" title="Remove column from table design"></a>' +
						'</td>' +
						'</tr>';
					if (jQuery("#wpda_table_structure tr").length === 0) {
						jQuery("#wpda_table_structure").append(new_row);
					} else {
						jQuery("#wpda_table_structure tr:last").after(new_row);
					}
					<?php if ( 'basic' === $this->design_mode ) { ?>
					check_basic_data_type(row_num);
					<?php } else { ?>
					check_data_type(row_num)
					<?php } ?>
					row_num++;
					jQuery('.wpda_mysql_names').off('keyup paste');
					jQuery('.wpda_mysql_names').on('keyup paste', function () {
						this.value = this.value.replace(/[^\w\$\_]/g, '');
					});
					check_numeric_items_off();
					check_numeric_items_on();
					jQuery(".wpda_view_table").off('change paste keyup', updated_table);
					jQuery(".wpda_view_table").on('change paste keyup', updated_table);
					jQuery("a.wpda_view_table").off();
					jQuery("a.wpda_view_table").on('click', updated_table);
					jQuery(".wpda_column_deleted").find("input,select").attr('disabled', true);
					if (!init) {
						updated_table();
					}
				}

				function select_available(e) {

					var option = jQuery("#columns_available option:selected");
					var add_to = jQuery("#columns_selected");

					option.remove();
					new_option = add_to.append(option);

					if (jQuery("#columns_selected option[value='']").length > 0) {
						// Remove ALL from selected list.
						jQuery("#columns_selected option[value='']").remove();
					}

					jQuery('select#columns_selected option').prop("selected", false);

				}

				function select_selected(e) {

					var option = jQuery("#columns_selected option:selected");
					if (option[0].value === '') {
						// Cannot remove ALL.
						return;
					}

					var add_to = jQuery("#columns_available");

					option.remove();
					add_to.append(option);

					if (jQuery('select#columns_selected option').length === 0) {
						jQuery("#columns_selected").append(jQuery('<option></option>').attr('value', '').text(no_cols_selected));
					}

					jQuery('select#columns_available option').prop("selected", false);
				}

				function show_index_dialog(e) {
					if ('<?php echo esc_attr( $this->action ); ?>' === 'view') {
						return;
					}

					var item_id = e.target.id;
					var index_row_num = item_id.substr(item_id.lastIndexOf("_") + 1);

					if (jQuery('#add_columns_' + index_row_num).hasClass('disabled') === true) {
						return;
					}

					var columns_available = jQuery(
						'<select id="columns_available" name="columns_available[]" multiple size="8" style="width:200px" onchange="select_available()">' +
						'</select>'
					);
					jQuery("input[name='column_name[]']").each(function () {
						columns_available.append(jQuery('<option></option>').attr('value', jQuery(this).val()).text(jQuery(this).val()));
					});

					var columns_selected = jQuery(
						'<select id="columns_selected" name="columns_selected[]" multiple size="8" style="width:200px" onchange="select_selected()">' +
						'<option value="">' + no_cols_selected + '</option>' +
						'</select>'
					);

					var dialog_table = jQuery('<table style="width:410px"></table>');
					var dialog_table_row = dialog_table.append(jQuery('<tr></tr>'));
					dialog_table_row.append(jQuery('<td width="50%"></td>').append(columns_available));
					dialog_table_row.append(jQuery('<td width="50%"></td>').append(columns_selected));

					// var dialog_table_row_available = dialog_table.append(jQuery('<tr></tr>').append(jQuery('<td width="50%"></td>')));
					// dialog_table_row_available.append(columns_available);
					//
					// var dialog_table_row_selected = dialog_table.append(jQuery('<tr></tr>').append(jQuery('<td width="50%"></td>')));
					// dialog_table_row_selected.append(columns_selected);

					var dialog_text = jQuery('<div style="width:410px"></div>');
					var dialog = jQuery('<div></div>');

					dialog.append(dialog_text);
					dialog.append(dialog_table);

					jQuery(dialog).dialog(
						{
							dialogClass: 'wp-dialog no-close',
							title: 'Add column(s) to index',
							modal: true,
							autoOpen: true,
							closeOnEscape: false,
							resizable: false,
							width: 'auto',
							buttons: {
								"Close": function () {

									var selected_columns = '';
									jQuery("#columns_selected option").each(
										function () {
											selected_columns += jQuery(this).val() + ',';
										}
									);
									if (selected_columns !== '') {
										selected_columns = selected_columns.slice(0, -1);
									}
									jQuery('#column_names_' + index_row_num).val(selected_columns);

									jQuery(this).dialog('destroy').remove();

									updated_indexes();

								},
								"Cancel": function () {

									jQuery(this).dialog('destroy').remove();

								}
							}
						}
					);
					jQuery(".ui-button-icon-only").hide();
				}

				function add_index(init = false, index_name = '', unique = '', column_names = '', row_action = '') {
					var row_class = '';
					switch (row_action) {
						case 'u':
							row_class = 'wpda_column_updated';
							break;
						case 'i':
							row_class = 'wpda_column_inserted';
							break;
						case 'd':
							row_class = 'wpda_column_deleted';
					}
					<?php if ( 'on' === $this->fulltext_support ) { ?>
					var fulltext_support = '<option value="FULLTEXT"' + (unique === 'FULLTEXT' ? 'selected' : '') + '>Full Text</option>';
					<?php } else { ?>
					var fulltext_support = '';
					<?php } ?>
					var new_index = '<tr id="idx_row_num_' + index_num + '" class="' + row_class + '">' +
						'<td style="padding-left:10px;">' +
						'	<input type="text" name="index_name[]" id="index_name_' + index_num + '"' +
						'		value="' + index_name + '" class="wpda_view_index wpda_mysql_names">' +
						'</td>' +
						'<td>' +
						'	<select name="unique[]" id="unique_' + index_num + '" class="wpda_view_index">' +
						'		<option value="No"' + (unique === 'No' ? 'selected' : '') + '>Non unique</option>' +
						'		<option value="Yes"' + (unique === 'Yes' ? 'selected' : '') + '>Unique</option>' +
						fulltext_support +
						'	</select>' +
						'</td>' +
						'<td>' +
						'	<input type="text" name="column_names[]" id="column_names_' + index_num + '" class="column_names"' +
						'		value="' + column_names + '" onclick="show_index_dialog(event)" readonly>' +
						'</td>' +
						'<td>' +
						'</td>' +
						'	<input type="button" name="add_columns[]" id="add_columns_' + index_num + '"' +
						'			value="Add column(s)" onclick="show_index_dialog(event)" class="wpda_view_index">' +
						'	<input type="hidden" name="row_action[]" value="' + row_action + '">' +
						'</td>' +
						'<td class="wpda-table-structure-last-column" style="width:20px;">' +
						'		<a href="javascript:void(0)" onclick="rem_index(event)" class="dashicons dashicons-trash wpda_view_index wpda_tooltip" title="Remove index from table design"></a>' +
						'	</td>' +
						'</tr>';
					if (jQuery("#wpda_index_structure tr").length === 0) {
						jQuery("#wpda_index_structure").append(new_index);
					} else {
						jQuery("#wpda_index_structure tr:last").after(new_index);
					}
					index_num++;
					jQuery('.wpda_mysql_names').off('keyup paste');
					jQuery('.wpda_mysql_names').on('keyup paste', function () {
						this.value = this.value.replace(/[^\w\$\_]/g, '');
					});
					jQuery('#submit_indexes').attr('disabled', false);
					jQuery(".wpda_view_index").off('change paste keyup', updated_indexes);
					jQuery(".wpda_view_index").on('change paste keyup', updated_indexes);
					jQuery("a.wpda_view_index").off();
					jQuery("a.wpda_view_index").on('click', updated_indexes);
					jQuery(".wpda_column_deleted").find("input,select").attr('disabled', true);
					if (!init) {
						updated_indexes();
					}
				}

				function switch_mode(e) {
					if ('new' === '<?php echo esc_attr( $this->action2 ); ?>' && !table_updated && !index_updated) {
						if (e.target.value !== '<?php echo esc_attr( $this->design_mode ); ?>') {
							if (confirm('Switch to ' + e.target.value + ' mode?')) {
								jQuery('#switch_mode_form').submit();
							} else {
								return false;
							}
						}
					} else if ('edit' === '<?php echo esc_attr( $this->action2 ); ?>' || table_updated || index_updated) {
						if (e.target.value !== '<?php echo esc_attr( $this->design_mode ); ?>') {
							if (confirm('Switch to ' + e.target.value + ' mode?')) {
								jQuery('#design_table_form').submit();
							} else {
								return false;
							}
						}
					}
				}

				function pre_submit_re() {
					if (wpda_db_table_name[jQuery('select[name="wpda_table_name_re"]').val()]) {
						alert('<?php echo __( 'Table name already used for another table design' ); ?>');
						return false;
					}
					jQuery('#design_mode_re').val(jQuery('input[name="design_mode"]:checked').val());
					return true;
				}
				
				function get_tables() {
					schema_name = jQuery('#wpda_schema_name_re_list').val();
					var url = location.pathname + '?action=wpda_get_tables';
					var data = {wpdaschema_name: schema_name};
					jQuery.post(
						url,
						data,
						function (data) {
							jQuery('#wpda_table_name_re_list').find('option').remove();
							var jsonData = JSON.parse(data);
							for (i = 0; i < jsonData.length; i++) {
								jQuery('#wpda_table_name_re_list').append(
									jQuery("<option></option>")
									.attr("value", jsonData[i]['table_name'])
									.text(jsonData[i]['table_name'])
								);
							}
						}
					);
				}
			</script>
			<div class="wrap">
				<h1>
					<a
							href="?page=<?php echo '' === $this->caller ? esc_attr( $this->page ) : \WP_Data_Access_Admin::PAGE_MAIN; ?>"
							style="display: inline-block; vertical-align: unset;"
							class="dashicons dashicons-arrow-left-alt wpda_tooltip"
							title="<?php echo __( 'List', 'wp-data-access' ); ?>"
					></a>
					<span><?php echo __( 'Data Designer', 'wp-data-access' ); ?></span>
					<a href="https://wpdataaccess.com/docs/documentation/data-designer/" target="_blank"
					   title="Plugin Help - open a new tab or window" class="wpda_tooltip">
						<span class="material-icons" style="font-size: 26px; vertical-align: sub;">help</span>
					</a>
				</h1>
				<div style="display:none;">
					<form id="wpda_create_table_form"
						  action="?page=<?php echo esc_attr( $this->page ); ?>"
						  method="post">
						<input type="hidden" name="action" value="edit"/>
						<input type="hidden" name="action2" value="create_table"/>
						<input type="hidden" name="wpda_table_name"
							   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
						<input type="hidden" name="wpda_schema_name"
							   value="<?php echo esc_attr( $this->wpda_schema_name ); ?>"/>
						<input type='hidden' name='caller' value='<?php echo esc_attr( $this->caller ); ?>'/>
					</form>
					<form id="wpda_alter_table_form"
						  action="?page=<?php echo esc_attr( $this->page ); ?>"
						  method="post">
						<input type="hidden" name="action" value="edit"/>
						<input type="hidden" name="action2" value="alter_table"/>
						<input type="hidden" name="wpda_table_name"
							   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
						<input type="hidden" name="wpda_schema_name"
							   value="<?php echo esc_attr( $this->wpda_schema_name ); ?>"/>
						<input type='hidden' name='caller' value='<?php echo esc_attr( $this->caller ); ?>'/>
					</form>
					<form id="wpda_drop_table_form"
						  action="?page=<?php echo esc_attr( $this->page ); ?>"
						  method="post">
						<input type="hidden" name="action" value="edit"/>
						<input type="hidden" name="action2" value="drop_table"/>
						<input type="hidden" name="wpda_table_name"
							   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
						<input type="hidden" name="wpda_schema_name"
							   value="<?php echo esc_attr( $this->wpda_schema_name ); ?>"/>
						<input type='hidden' name='caller' value='<?php echo esc_attr( $this->caller ); ?>'/>
					</form>
					<form id="wpda_create_index_form"
						  action="?page=<?php echo esc_attr( $this->page ); ?>"
						  method="post" style="margin-left:1px;margin-right:1px;">
						<input type="hidden" name="action" value="edit"/>
						<input type="hidden" name="action2" value="create_table_index"/>
						<input type="hidden" name="wpda_table_name"
							   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
						<input type="hidden" name="wpda_schema_name"
							   value="<?php echo esc_attr( $this->wpda_schema_name ); ?>"/>
						<input type='hidden' name='caller' value='<?php echo esc_attr( $this->caller ); ?>'/>
					</form>
					<form id="wpda_drop_index_form"
						  action="?page=<?php echo esc_attr( $this->page ); ?>"
						  method="post" style="margin-left:1px;margin-right:1px;">
						<input type="hidden" name="action" value="edit"/>
						<input type="hidden" name="action2" value="drop_table_index"/>
						<input type="hidden" name="wpda_table_name"
							   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
						<input type="hidden" name="wpda_schema_name"
							   value="<?php echo esc_attr( $this->wpda_schema_name ); ?>"/>
						<input type='hidden' name='caller' value='<?php echo esc_attr( $this->caller ); ?>'/>
					</form>
				</div>
				<style>
					#overlay_show_create_table {
						height: 400px;
						width: 600px;
						position: fixed;
						display: none;
						top: 50%;
						left: 50%;
						transform: translate(-50%, -50%);
						-ms-transform: translate(-50%, -50%);
						right: 0;
						bottom: 0;
						background-color: #f9f9f9;
						opacity: .95;
						border: 1px solid #ccc;
						cursor: pointer;
						z-index: 1000;
					}

					#overlay_show_create_table_text {
						height: 360px;
						width: 400px;
						padding: 10px;
						position: relative;
						top: 50%;
						left: 220px;
						transform: translate(-50%, -50%);
						-ms-transform: translate(-50%, -50%);
						color: black;
						overflow-y: auto;
						background-color: white;
						border: 1px solid #ccc;
					}
				</style>
				<div id="overlay_show_create_table">
					<div id="overlay_show_create_table_text">
						<?php echo $this->create_table_statement . $this->create_index_statement; ?>
					</div>
					<div style="position: absolute; bottom: 0; right: 0; padding-right: 10px; padding-bottom: 10px;">
						<a id="button-copy-clipboard" href="javascript:void(0)" class="button button-secondary"
						   style="text-align:center;width:150px;"
						   data-clipboard-text="<?php echo str_replace( self::NEW_LINE, "\n", $this->create_table_statement ) . str_replace( self::NEW_LINE, "\n", $this->create_index_statement ); ?>">
							<span class="material-icons wpda_icon_on_button">content_copy</span>
							<?php echo __( 'Copy to clipboard' ); ?>
						</a>
						<br/>
						<div style="height: 5px;"></div>
						<a href="javascript:void(0)" class="button button-primary"
						   style="text-align:center;width:150px;"
						   onclick="jQuery('#overlay_show_create_table').hide()">
							<span class="material-icons wpda_icon_on_button">cancel</span>
							<?php echo __( 'Close' ); ?>
						</a>
					</div>
				</div>
				<script type='text/javascript'>
					jQuery(function () {
						<?php if ( 'show_create_table_script' === $this->action2_posted || 'show_alter_table_script' === $this->action2_posted ) { ?>
						jQuery('#overlay_show_create_table').show();
						<?php } ?>
						var sql_to_clipboard = new ClipboardJS('#button-copy-clipboard');
						sql_to_clipboard.on('success', function (e) {
							jQuery.notify('<?php echo __( 'SQL successfully copied to clipboard!' ); ?>','info');
						});
						sql_to_clipboard.on('error', function (e) {
							jQuery.notify('<?php echo __( 'Could not copy SQL to clipboard!' ); ?>','error');
						});
						jQuery('#wpda_table_structure').sortable();
						jQuery( '.wpda_tooltip' ).tooltip();
					});
				</script>
				<?php
				if ( ! $this->wpda_table_design ) {
					// Allow loading table from database into designer (reverse engineering).
					$table_list = WPDA_Dictionary_Lists::get_tables( false );
					?>
					<div id="wpda_reverse_engineering" style="display: none">
						<br/>
						<div class="wpda_reverse_engineering">
							<form id="wpda_reverse_engineering_form"
								  action="?page=<?php echo esc_attr( $this->page ); ?>" method="post">
								<label><?php echo __( 'Load table from database' ); ?> </label>
								<select name="wpda_schema_name_re" id="wpda_schema_name_re_list" onchange="get_tables()">
									<?php
									foreach ( $this->databases as $database ) {
										if ( null === $this->wpda_schema_name ) {
											global $wpdb;
											$dbs = WPDA::get_user_default_scheme();
										} else {
											$dbs = $this->wpda_schema_name;
										}
										$selected = $dbs === $database['schema_name'] ? 'selected' : '';
										echo "<option value='{$database['schema_name']}' $selected>{$database['schema_name']}</option>";
									}
									?>
								</select>
								<select name="wpda_table_name_re" id="wpda_table_name_re_list">
									<?php
									foreach ( $table_list as $key => $value ) {
										echo '<option value="' . esc_attr( $value['table_name'] ) . '">' . esc_attr( $value['table_name'] ) . '</option>';
									}
									?>
								</select>
								<input type="hidden" name="action" value="edit"/>
								<input type="hidden" name="action2" value="wpda_reverse_engineering"/>
								<input type="hidden" name="wpda_table_name" id="wpda_table_name_re"
									   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
								<input type="hidden" name="wpda_schema_name" id="wpda_schema_name_re"
									   value="<?php echo esc_attr( $this->wpda_schema_name ); ?>"/>
								<input type="hidden" id="design_mode_re" name="design_mode_re"/>
								<input type='hidden' name='caller' value='<?php echo esc_attr( $this->caller ); ?>'/>
								<input type="submit"
									   class="button button-primary"
									   value="Start Reverse Engineering"
									   onclick="return pre_submit_re()"
								>
								<a href="javascript:void(0)" onclick="jQuery('#wpda_reverse_engineering').hide()"
								   class="button"><?php echo __( 'Dismiss' ); ?></a>
							</form>
						</div>
					</div>
					<?php

				}
				if ( $this->table_exists ) {
					// Add reconcile form.
					?>
					<div style="display:none;">
						<form id="wpda_reconcile_form"
							  action="?page=<?php echo esc_attr( $this->page ); ?>" method="post">
							<input type="hidden" name="action" value="edit"/>
							<input type="hidden" name="action2" value="wpda_reconcile"/>
							<input type="hidden" name="wpda_table_name_re"
								   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
							<input type="hidden" name="wpda_schema_name_re"
								   value="<?php echo esc_attr( $this->wpda_schema_name ); ?>"/>
							<input type='hidden' name='caller' value='<?php echo esc_attr( $this->caller ); ?>'/>
						</form>
					</div>
					<?php
				}
				?>
				<div style="text-align:right">
					<a href="https://wpdataaccess.com/docs/documentation/data-designer/overview/"
					   target="_blank" style="text-decoration:none">
						<span class="material-icons wpda_icon_on_button">help_outline</span>
						What is the difference between a table design and a database table?
					</a>
				</div>
				<div>
					<div style="display:none;">
						<form id="show_create_table_form" action="?page=<?php echo esc_attr( $this->page ); ?>"
							  method="post">
							<input type="hidden" name="action" value="edit"/>
							<input type="hidden" name="action2" value="show_create_table_script"/>
							<input type="hidden" name="wpda_table_name"
								   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
							<input type="hidden" name="wpda_schema_name"
								   value="<?php echo esc_attr( $this->wpda_schema_name ); ?>"/>
							<input type='hidden' name='caller' value='<?php echo esc_attr( $this->caller ); ?>'/>
						</form>
						<form id="show_alter_table_form" action="?page=<?php echo esc_attr( $this->page ); ?>"
							  method="post">
							<input type="hidden" name="action" value="edit"/>
							<input type="hidden" name="action2" value="show_alter_table_script"/>
							<input type="hidden" name="wpda_table_name"
								   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
							<input type="hidden" name="wpda_schema_name"
								   value="<?php echo esc_attr( $this->wpda_schema_name ); ?>"/>
							<input type='hidden' name='caller' value='<?php echo esc_attr( $this->caller ); ?>'/>
						</form>
					</div>
					<form id="design_table_form"
						  action="?page=<?php echo esc_attr( $this->page ); ?>"
						  autocomplete="off"
						  method="post" onsubmit="return pre_submit()">
						<fieldset class="wpda_fieldset">
							<legend>
								<span>
									<?php echo __( 'Table definition', 'wp-data-access' ); ?>
								</span>
							</legend>
						<table class="wpda-table-structure">
							<thead>
							<tr>
								<td class="wpda-table-structure-first-column">
									<label for "wpda_schema_name"><?php echo __( 'Database' ); ?> </label>
								</td>
								<td>
									<select name="wpda_schema_name" id="wpda_schema_name" style="width:100%;max-width:100%;">
									<?php
									foreach ( $this->databases as $database ) {
										if ( null === $this->wpda_schema_name ) {
											global $wpdb;
											$dbs = WPDA::get_user_default_scheme();
										} else {
											$dbs = $this->wpda_schema_name;
										}
										$selected = $dbs === $database['schema_name'] ? 'selected' : '';
										echo "<option value='{$database['schema_name']}' $selected>{$database['schema_name']}</option>";
									}
									?>
									</select>
								</td>
								<td colspan="4">
								</td>
								<td colspan="4" class="wpda-table-structure-last-column">
									<span style="float:right;">
										<label>
											<input type="radio"
												   name="design_mode"
												   value="basic"
												   class="design_mode"
												   onclick="return switch_mode(event)"
												<?php echo 'basic' === $this->design_mode ? 'checked' : ''; ?>
											>
											<?php echo __( 'Basic Design Mode' ); ?>
										</label>
										<label>
											<input type="radio"
												   name="design_mode"
												   value="advanced"
												   class="design_mode"
												   onclick="return switch_mode(event)"
												<?php echo 'advanced' === $this->design_mode ? 'checked' : ''; ?>
											>
											<?php echo __( 'Advanced Design Mode' ); ?>
										</label>
									</span>
								</td>							</tr>
							<tr>
								<td class="wpda-table-structure-first-column">
									<label for "wpda_table_name"><?php echo __( 'Table name' ); ?> </label>
								</td>
								<td>
									<input type="text" name="wpda_table_name" id="wpda_table_name" maxlength="64"
										   style="width:100%;max-width:100%;"
										   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"
										   onchange="jQuery('#wpda_table_name_re').val(jQuery(this).val())"
										   class="wpda_mysql_names wpda_view_table"
									/>
								</td>
								<td colspan="4">
									<?php
									if ( $this->table_exists ) {
										if ( $this->is_wp_table ) {
											?>
											<span style="vertical-align:-webkit-baseline-middle; cursor:pointer;"
												  title="<?php echo __( 'You cannot use a WordPress table name', 'wp-data-access' ); ?>"
												  class="dashicons dashicons-flag wpda_tooltip">
											</span>
											<?php
										} else {
											?>
											<span title="<?php echo __( 'A table with this name already exists in the database', 'wp-data-access' ); ?>"
												  class="material-icons pointer wpda_tooltip" style="padding-top:3px">
												info
											</span>
											<?php
										}
										?>
										<a href="javascript:void(0)"
										   	id="reconcile_button"
											onclick="if (confirm('<?php echo __( 'Reconcile table? Your current modifications will be lost!' ); ?>')) { jQuery('#wpda_reconcile_form').submit(); }"
											class="button wpda_tooltip"
											title="Update table design from database table (overwrites current design)">
											<span class="material-icons wpda_icon_on_button">history</span>
											<?php echo __( 'Reconcile' ); ?>
										</a>
										<?php
									} else {
										if ( 'rdb:' === substr( $this->wpda_schema_name, 0, 4) ) {
											$title = __( 'Remote table: not checked!', 'wp-data-access' );
										} else {
											$title = __( 'New table', 'wp-data-access' );
										}
										?>
										<span style="vertical-align:-webkit-baseline-middle; cursor:pointer;"
											  title="<?php echo $title; ?>"
											  class="dashicons dashicons-warning wpda_tooltip">
										</span>
										<?php
									}
									?>
									<input type="hidden" name="wpda_table_name_original"
										   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
									<input type="hidden" name="wpda_schema_name_original"
										   value="<?php echo esc_attr( $this->wpda_schema_name ); ?>"/>
									<?php
									if ( ! $this->wpda_table_design ) {
										?>
										<a href="javascript:void(0)"
										   onclick="jQuery('#wpda_reverse_engineering').show()"
										   class="button wpda_view_table wpda_tooltip"
										   title="<?php echo __( 'Load table from database' ); ?>"
										>
											<?php echo __( 'Reverse engineering' ); ?>
										</a>
										<?php
									}
									?>
								</td>
								<td colspan="4" class="wpda-table-structure-last-column">
								</td>
							</tr>
							<?php if ( 'advanced' === $this->design_mode ) { ?>
								<tr>
									<td class="wpda-table-structure-first-column">
										<label for "engine"><?php echo __( 'Engine' ); ?> </label>
									</td>
									<td>
										<select name="engine" id="engine" style="width:100%;max-width:100%;" class="wpda_view_table">
											<?php
											$engines      = WPDA_Dictionary_Lists::get_engines();
											$engine_saved = isset ( $this->wpda_table_design->engine ) ? $this->wpda_table_design->engine : '';
											foreach ( $engines as $engine ) {
												$selected_tag = '';
												if ( '' === $engine_saved ) {
													if ( 'DEFAULT' === $engine['support'] ) {
														$selected_tag = 'selected';
													}
												} else {
													if ( $engine_saved === $engine['engine'] ) {
														$selected_tag = 'selected';
													}
												}
												?>
												<option value="<?php echo esc_attr( $engine['engine'] ); ?>" <?php echo esc_attr( $selected_tag ); ?>>
													<?php echo esc_attr( $engine['engine'] ); ?>
												</option>
												<?php
											}
											?>
										</select>
									</td>
									<td colspan="8" class="wpda-table-structure-last-column">
										<span style="float:right;">
											<a id="button_show_create_table" href="javascript:void(0)"
											   class="button button-secondary wpda_tooltip"
											   title="Generates a create table script from table and index design."
											   onclick="jQuery('#show_create_table_form').submit();">
												<span class="material-icons wpda_icon_on_button">code</span>
												<?php echo __( 'Show CREATE TABLE script' ); ?>
											</a>
											<a id="button_show_alter_table" href="javascript:void(0)"
											   class="button button-secondary wpda_tooltip"
											   title="Generates a alter table script from table and index design."
											   onclick="jQuery('#show_alter_table_form').submit();">
												<span class="material-icons wpda_icon_on_button">code</span>
												<?php echo __( 'Show ALTER TABLE script' ); ?>
											</a>
										</span>
									</td>
								</tr>
								<tr>
									<td class="wpda-table-structure-first-column">
										<label for "collation"><?php echo __( 'Collation' ); ?> </label>
									</td>
									<td>
										<select name="collation" id="collation" style="width:100%;max-width:100%;"
												class="wpda_view_table">
											<?php
											$character_set_name = '';
											$default_collation  = WPDA_Dictionary_Lists::get_default_collation();
											$collations         = WPDA_Dictionary_Lists::get_collations();
											$collation_saved    = isset( $this->wpda_table_design->collation ) ? $this->wpda_table_design->collation : '';
											foreach ( $collations as $collation ) {
												if ( $character_set_name !== $collation['character_set_name'] ) {
													if ( '' !== $character_set_name ) {
														echo '</optgroup>';
													}
													$character_set_name = $collation['character_set_name'];
													echo '<optgroup label="' . esc_attr( $collation['character_set_name'] ) . '">';
												}
												$selected_tag = '';
												if ( '' === $collation_saved ) {
													if ( $collation['collation_name'] === $default_collation[0]['default_collation_name'] ) {
														$selected_tag = 'selected';
													}
												} else {
													if ( $collation_saved === $collation['collation_name'] ) {
														$selected_tag = 'selected';
													}
												}
												?>
												<option value="<?php echo esc_attr( $collation['collation_name'] ); ?>" <?php echo esc_attr( $selected_tag ); ?>>
													<?php echo esc_attr( $collation['collation_name'] ); ?>
												</option>
												<?php
											}
											?>
										</select>
										<?php echo '</optgroup>'; ?>
									</td>
									<td colspan="8" class="wpda-table-structure-last-column">
										<span style="float:right;">
											<label id="checkbox_show_deleted_label">
												<input id="checkbox_show_deleted" type="checkbox"
													   onclick="if (jQuery(this).is(':checked')) { jQuery('.wpda_column_deleted').show(); } else {  jQuery('.wpda_column_deleted').hide(); }">
												<?php echo __( 'Show deleted columns and indexes' ); ?>
											</label>
										</span>
									</td>
								</tr>
							<?php } ?>
							</thead>
						</table>
						</fieldset>
						<br/>
						<fieldset class="wpda_fieldset">
							<legend>
								<span>
									<?php echo __( 'Add columns', 'wp-data-access' ); ?>
								</span>
							</legend>
						<table class="wpda-table-structure" style="border-collapse: collapse;">
							<thead>
								<tr>
									<th class="wpda-table-structure-first-column-move"></th>
									<th>
										<?php echo __( 'Column name' ); ?>
									</th>
									<th>
										<?php echo __( 'Column type' ); ?>
									</th>
									<?php if ( 'advanced' === $this->design_mode ) { ?>
										<th>
											<?php echo __( 'Type attribute' ); ?>
										</th>
									<?php } ?>
									<th style="min-width:60px;">
										<?php echo __( 'Key?' ); ?>
									</th>
									<th style="min-width:90px;">
										<?php echo __( 'Mandatory?' ); ?>
									</th>
									<th>
										<?php echo __( 'Max length' ); ?>
									</th>
									<th>
										<?php echo __( 'Extra' ); ?>
										<span title="Possible values:

auto_increment
on update current_timestamp (for column types: timestamp and datetime)
virtual generated
virtual stored
default_generated
stored generated

Or combined:
default_generated on update current_timestamp" class="material-icons pointer wpda_tooltip" style="font-size: 140%;vertical-align: middle;">

											info
										</span>
									</th>
									<th>
										<?php echo __( 'Default value' ); ?>
									</th>
									<th>
										<?php echo __( 'List values' ); ?>
									</th>
									<th class="wpda-table-structure-last-column">
										<a href="javascript:void(0)"
										   onclick="add_row('<?php echo esc_attr( $this->design_mode ); ?>')"
										   style="vertical-align:-webkit-baseline-middle;"
										   class="dashicons dashicons-plus wpda_view_table wpda_tooltip"
										   title="Add new column to table design"
										></a>
									</th>
								</tr>
							</thead>
							<tbody id="wpda_table_structure"></tbody>
							<tfoot>
							<tr>
								<td colspan="<?php echo 'basic' === $this->design_mode ? '9' : '10'; ?>">
									<input type="hidden" name="submitted_changes" value="table"/>
									<input type="hidden" name="action" value="edit"/>
									<input type="hidden" name="action2"
										   value="<?php echo esc_attr( $this->action2 ); ?>"
									/>
									<?php if ( 'basic' === $this->design_mode ) { ?>
										<input type="hidden" name="engine" id="engine"
											   value="<?php echo isset( $this->wpda_table_design->engine ) ? esc_attr( $this->wpda_table_design->engine ) : ''; ?>"
										/>
										<input type="hidden" name="collation" id="collation"
											   value="<?php echo isset( $this->wpda_table_design->collation ) ? esc_attr( $this->wpda_table_design->collation ) : ''; ?>"
										/>
									<?php } ?>
									<a href="javascript:void(0)"
									   title="Creates database table from design. Does not create indexes."
									   class="button wpda_tooltip wpda_view<?php if ( $this->table_exists ) {
										   echo ' disabled';
									   } ?>"
									   onclick="if ( confirm('Create database table `<?php echo $this->wpda_schema_name; ?>`.`<?php echo $this->wpda_table_name; ?>`?\nDoes not create indexes!') ) { jQuery('#wpda_create_table_form').submit(); }"
										<?php if ( $this->table_exists || 'new' === strtolower( $this->action2 ) ) {
											echo ' readonly disabled';
										} ?>
									>
										<span class="material-icons wpda_icon_on_button">check_circle</span>
										<?php echo __( 'CREATE TABLE', 'wp-data-access' ); ?>
									</a>
									<a id="button_alter_table" href="javascript:void(0)" class="button wpda_view wpda_tooltip"
									   title="Writes design changes to database table and indexes."
									   onclick="if ( confirm('Alter database table `<?php echo $this->wpda_schema_name; ?>`.`<?php echo $this->wpda_table_name; ?>`?\nAlters modified indexes as well!') ) { jQuery('#wpda_alter_table_form').submit(); }"
									>
										<span class="material-icons wpda_icon_on_button">update</span>
										<?php echo __( 'ALTER TABLE', 'wp-data-access' ); ?>
									</a>
									<a href="javascript:void(0)"
									   title="This action drops your database table! Not your table design... This cannot be undone."
									   class="button wpda_tooltip wpda_view<?php if ( ! $this->table_exists ) {
										   echo ' disabled';
									   } ?>"
									   onclick="if ( confirm('Drop database table `<?php echo $this->wpda_schema_name; ?>`.`<?php echo $this->wpda_table_name; ?>`?\nTable design will not be deleted!') ) { jQuery('#wpda_drop_table_form').submit(); }"
										<?php if ( ! $this->table_exists ) {
											echo ' readonly disabled';
										} ?>
									>
										<span class="material-icons wpda_icon_on_button">delete</span>
										<?php echo __( 'DROP TABLE', 'wp-data-access' ); ?>
									</a>
									<input type='hidden' name='caller'
										   value='<?php echo esc_attr( $this->caller ); ?>'/>
									<button type="submit" class="button button-primary wpda_view_table wpda_tooltip"
											title="This does NOT create the table! Is just saves your table design..."
									>
										<span class="material-icons wpda_icon_on_button">check</span>
										Save Table Design
									</button>
								</td>
							</tr>
							</tfoot>
						</table>
						</fieldset>
					</form>
					<form id="wpda_reset_form" action="?page=<?php echo esc_attr( $this->page ); ?>" method="post">
						<input type="hidden" name="action" value="edit"/>
						<?php
						if ( 'new' !== $this->action2 ) {
							?>
							<input type="hidden" name="wpda_table_name"
								   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
							<input type="hidden" name="wpda_schema_name"
								   value="<?php echo esc_attr( $this->wpda_schema_name ); ?>"/>
							<?php
						}
						?>
						<input type='hidden' name='caller' value='<?php echo esc_attr( $this->caller ); ?>'/>
					</form>
					<form id="switch_mode_form" method="post"
						  action="?page=<?php echo esc_attr( $this->page ); ?>&table_name=<?php
					      echo WPDA_Design_Table_Model::get_base_table_name(); ?>">
						<input type="hidden" name="design_mode"
							   value="<?php echo 'basic' === $this->design_mode ? 'advanced' : 'basic'; ?>">
						<input type="hidden" name="action" value="edit">
						<input type='hidden' name='caller' value='<?php echo esc_attr( $this->caller ); ?>'/>
					</form>
				</div>
				<br/>
				<div>
					<form id="design_table_form_indexes" action="?page=<?php echo esc_attr( $this->page ); ?>"
						  method="post">
						<fieldset class="wpda_fieldset">
							<legend>
								<span>
									<?php echo __( 'Add indexes' ); ?>
								</span>
							</legend>
						<table class="wpda-table-structure" style="border-collapse: collapse;">
							<thead>
							<tr>
								<th style="padding-left:10px;">
									<?php echo __( 'Index name' ); ?>
								</th>
								<th>
									<?php echo __( 'Type?' ); ?>
								</th>
								<th>
									<?php echo __( 'Column name(s)' ); ?>
								</th>
								<th></th>
								<th class="wpda-table-structure-last-column" style="width:20px;">
									<a href="javascript:void(0)" onclick="add_index()"
									   style="vertical-align: -webkit-baseline-middle;"
									   class="dashicons dashicons-plus wpda_view_index wpda_tooltip"
									   title="Add new index to table design"
									></a>
								</th>
							</tr>
							</thead>
							<tbody id="wpda_index_structure">
							</tbody>
							<tfoot>
							<tr>
								<td colspan="5">
									<input type="hidden" name="submitted_changes" value="indexes"/>
									<input type="hidden" name="action" value="edit"/>
									<input type="hidden" name="action2"
										   value="<?php echo esc_attr( $this->action2 ); ?>"/>
									<input type="hidden" name="wpda_table_name"
										   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
									<input type="hidden" name="wpda_table_name_original"
										   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
									<input type="hidden" name="wpda_schema_name"
										   value="<?php echo esc_attr( $this->wpda_schema_name ); ?>"/>
									<input type="hidden" name="wpda_schema_name_original"
										   value="<?php echo esc_attr( $this->wpda_schema_name ); ?>"/>
									<a id="wpda_create_index" href="javascript:void(0)"
									   title="Drops all indexes and recreates them."
									   class="button wpda_view wpda_tooltip"
									   onclick="if ( confirm('<?php echo __( 'Drop all deleted indexes and recreate all changed indexes for table' . ' `' . $this->wpda_table_name . '`?' ); ?>') ) { jQuery('#wpda_create_index_form').submit(); }"
									>
										<span class="material-icons wpda_icon_on_button">check_circle</span>
										<?php echo __( '(RE)CREATE INDEXES', 'wp-data-access' ); ?>
									</a>
									<a id="wpda_drop_index" href="javascript:void(0)"
									   title="This action drops your indexes from the database! This cannot be undone."
									   class="button wpda_view wpda_tooltip"
									   onclick="if ( confirm('<?php echo __( 'Drop all indexes for table' . ' `' . $this->wpda_table_name . '`?\n' . __( 'Does not drop primary key indexes and index designs!' ) ); ?>'))  { jQuery('#wpda_drop_index_form').submit(); }"
									>
										<span class="material-icons wpda_icon_on_button">delete</span>
										<?php echo __( 'DROP INDEXES', 'wp-data-access' ); ?>
									</a>
									<input type='hidden' name='caller'
										   value='<?php echo esc_attr( $this->caller ); ?>'/>
									<a id="submit_indexes" href="javascript:void(0)"
									   title="Does NOT create indexes! It just saves your index design..."
									   onclick="if (!jQuery(this).attr('disabled')) { jQuery('#design_table_form_indexes').submit(); } else { alert('<?php echo __( 'Save table design changes first!' ); ?>'); }"
									   class="button button-primary wpda_view_index wpda_tooltip">
										<span class="material-icons wpda_icon_on_button">check</span>
										<?php echo __( 'Save Indexes' ); ?>
									</a>
								</td>
							</tr>
							</tfoot>
						</table>
						</fieldset>
					</form>
				</div>
				<?php
				if ( null !== $this->create_table_statement && false === $this->create_table_succeeded ) {
					?>
					<br/>
					<div class="wpda_design_table">
						<table class="wpda-table-structure">
							<tfoot>
							<tr>
								<td>
									<h3><?php echo __( 'The following CREATE TABLE statement failed' ); ?></h3>
									<div>
										<div style="padding:10px; text-align: left; width: fit-content; margin: 0 auto;">
											<?php echo $this->create_table_statement; ?>
										</div>
									</div>
									<div>
										<strong><?php echo $this->wpdb_error; ?></strong>
									</div>
								</td>
							</tr>
							</tfoot>
						</table>
					</div>
					<?php
				}
				if ( null !== $this->create_index_failed && 0 < count( $this->create_index_failed ) ) {
					?>
					<br/>
					<div class="wpda_design_table">
						<table class="wpda-table-structure">
							<tfoot>
							<tr>
								<td>
									<h3><?php echo __( 'The following CREATE INDEX statement(s) failed' ); ?></h3>
									<div>
										<div style="padding:10px; text-align: left; width: fit-content; margin: 0 auto;">
											<?php
											foreach ( $this->create_index_failed as $index_failed ) {
												echo "$index_failed<br/>";
											}
											?>
										</div>
									</div>
								</td>
							</tr>
							</tfoot>
						</table>
					</div>
					<?php
				}
				if ( null !== $this->create_table_statement && false === $this->alter_table_succeeded ) {
					?>
					<br/>
					<div class="wpda_design_table">
						<table class="wpda-table-structure">
							<tfoot>
							<tr>
								<td>
									<h3><?php echo __( 'The following ALTER TABLE statement failed' ); ?></h3>
									<div>
										<div style="padding:10px; text-align: left; width: fit-content; margin: 0 auto;">
											<?php echo $this->create_table_statement; ?>
										</div>
									</div>
									<div>
										<strong><?php echo $this->wpdb_error; ?></strong>
									</div>
								</td>
							</tr>
							</tfoot>
						</table>
					</div>
					<?php
				}
				?>

			</div>
			<?php

			if ( $this->wpda_table_design ) {
				// Display table design.
				foreach ( $this->wpda_table_design->table as $design_column ) {
					if ( ! $this->table_exists ) {
						// New table cannot be compared with real table.
						$column_changed = '';
					} else {
						// Check for table structure changes.
						if ( isset( $this->table_columns[ $design_column->column_name ] ) ) {
							// Check column arguments.
							$column_changed = '';
							foreach ( $this->real_table as $real_column ) {
								if ( $real_column->column_name === $design_column->column_name ) {
									if ( $real_column != $design_column ) {
										if ( strtolower( $real_column->extra ) === 'auto_increment' && strtolower( $design_column->extra ) === 'auto_increment' ) {
											$tmp_real_column   = clone $real_column;
											$tmp_design_column = clone $design_column;
											unset( $tmp_real_column->extra );
											unset( $tmp_design_column->extra );
											if ( $tmp_real_column != $tmp_design_column ) {
												$column_changed      = 'u';
												$this->table_altered = true;
											}
										} else {
											$column_changed      = 'u';
											$this->table_altered = true;
										}
									}
								}
							}
						} else {
							// New column.
							$column_changed      = 'i';
							$this->table_altered = true;
						}
					}
					?>
					<script type='text/javascript'>
						add_row(
							'<?php echo esc_attr( $this->design_mode ); ?>',
							true,
							'<?php echo esc_attr( $design_column->column_name ); ?>',
							'<?php echo esc_attr( WPDA_Design_Table_Model::datatype2basic( esc_attr( $design_column->data_type ) ) ); ?>',
							'<?php echo esc_attr( $design_column->data_type ); ?>',
							'<?php echo esc_attr( $design_column->type_attribute ); ?>',
							'<?php echo esc_attr( $design_column->key ); ?>',
							'<?php echo esc_attr( $design_column->mandatory ); ?>',
							'<?php echo esc_attr( $design_column->max_length ); ?>',
							'<?php echo esc_attr( $design_column->extra ); ?>',
							'<?php echo esc_attr( $design_column->default ); ?>',
							'<?php echo esc_attr( $design_column->list ); ?>',
							'<?php echo $column_changed; ?>'
						);
					</script>
					<?php
				}
				if ( $this->table_exists ) {
					// Check for deleted columns.
					foreach ( $this->table_columns as $table_column ) {
						$column_found = false;
						foreach ( $this->wpda_table_design->table as $design_column ) {
							if ( $design_column->column_name === $table_column['column_name'] ) {
								$column_found = true;
								break;
							}
						}
						if ( ! $column_found ) {
							break;
						}
					}

					if ( ! $column_found ) {
						// Add deleted column(s) to form and mark as readonly.
						foreach ( $this->table_columns as $table_column ) {
							$column_found = false;
							foreach ( $this->wpda_table_design->table as $design_column ) {
								if ( $design_column->column_name === $table_column['column_name'] ) {
									$column_found = true;
									break;
								}
							}
							if ( ! $column_found ) {
								foreach ( $this->real_table as $real_column ) {
									if ( $real_column->column_name === $table_column['column_name'] ) {
										?>
										<script type='text/javascript'>
											add_row(
												'<?php echo esc_attr( $this->design_mode ); ?>',
												true,
												'<?php echo esc_attr( $real_column->column_name ); ?>',
												'<?php echo esc_attr( WPDA_Design_Table_Model::datatype2basic( esc_attr( $real_column->data_type ) ) ); ?>',
												'<?php echo esc_attr( $real_column->data_type ); ?>',
												'<?php echo esc_attr( $real_column->type_attribute ); ?>',
												'<?php echo esc_attr( $real_column->key ); ?>',
												'<?php echo esc_attr( $real_column->mandatory ); ?>',
												'<?php echo esc_attr( $real_column->max_length ); ?>',
												'<?php echo esc_attr( $real_column->extra ); ?>',
												'<?php echo esc_attr( $real_column->default ); ?>',
												'<?php echo esc_attr( $real_column->list ); ?>',
												'd'
											);
										</script>
										<?php
										$this->table_altered               = true;
										$this->deleted_columns_and_indexes = true;
									}
								}
							}
						}
					}
				}

				// Display indexes.
				$indexes_found = false;
				foreach ( $this->wpda_table_design->indexes as $design_index ) {
					$index_changed = '';
					if ( $this->table_exists ) {
						$index_found = false;
						// Check if index was changed or new.
						foreach ( $this->real_indexes as $real_index ) {
							if ( $design_index->index_name === $real_index['index_name'] ) {
								$index_found = true;
								if (
									$real_index['unique'] != $design_index->unique ||
									$real_index['column_names'] != $design_index->column_names
								) {
									$index_changed         = 'u';
									$this->updated_indexes = true;
								}
								break;
							}
						}
						if ( ! $index_found ) {
							$index_changed         = 'i';
							$this->updated_indexes = true;
						}
					}
					?>
					<script type='text/javascript'>
						add_index(
							true,
							'<?php echo esc_attr( $design_index->index_name ); ?>',
							'<?php echo esc_attr( $design_index->unique ); ?>',
							'<?php echo esc_attr( $design_index->column_names ); ?>',
							'<?php echo $index_changed; ?>'
						);
					</script>
					<?php
					$indexes_found = true;
				}

				if ( $this->table_exists ) {
					foreach ( $this->real_indexes as $real_index ) {
						$real_indexes_found = false;
						foreach ( $this->wpda_table_design->indexes as $design_index ) {
							if ( $real_index['index_name'] === $design_index->index_name ) {
								$real_indexes_found = true;
								break;
							}
						}

						if ( ! $real_indexes_found ) {
							// Index was dropped.
							?>
							<script type='text/javascript'>
								add_index(
									true,
									'<?php echo esc_attr( $real_index['index_name'] ); ?>',
									'<?php echo esc_attr( $real_index['unique'] ); ?>',
									'<?php echo esc_attr( $real_index['column_names'] ); ?>',
									'<?php echo 'd'; ?>'
								);
							</script>
							<?php
							$this->deleted_columns_and_indexes = true;
							$this->updated_indexes             = true;
						}
					}
				}

				if ( ! $indexes_found ) {
					?>
					<script type='text/javascript'>
						add_index(true);
					</script>
					<?php
				}
			} else {
				// Display one empty row.
				?>
				<script type='text/javascript'>
					add_row('<?php echo esc_attr( $this->design_mode ); ?>', true);
					add_index(true);
					disable_index();
					disable_create_buttons();
				</script>
				<?php
			}

			?>
			<script type='text/javascript'>
				<?php if ( ! $this->wpda_table_design ) { ?>
				jQuery('#button_show_create_table').prop("readonly", true).prop("disabled", true).addClass("disabled");
				<?php } ?>
				<?php
				if ( ! $this->wpda_table_design || ! $this->table_altered ) {
				if ( ! $this->updated_indexes ) {
				?>
				jQuery("#button_show_alter_table").prop("readonly", true).prop("disabled", true).addClass("disabled");
				jQuery("#button_alter_table").prop("readonly", true).prop("disabled", true).addClass("disabled");
				<?php
				}
				}
				?>
				<?php if ( ! $this->updated_indexes ) { ?>
				jQuery('#wpda_create_index').prop("readonly", true).prop("disabled", true).addClass("disabled");
				<?php } ?>
				<?php if ( ! $this->real_indexes ) { ?>
				jQuery('#wpda_drop_index').prop("readonly", true).prop("disabled", true).addClass("disabled");
				<?php } ?>
			</script>
			<?php

			if ( 'view' === $this->action ) {
				?>
				<script type='text/javascript'>
					disable_page();
				</script>
				<?php
			}

			if ( $this->is_wp_table ) {
				// WP table names are not allowed: disable create table button.
				?>
				<script type='text/javascript'>
					disable_page();
				</script>
				<?php
			}

			// Save all table names in array table_name check.
			?>
			<script type='text/javascript'>
				var wpda_db_table_name = [];
				<?php
				$designer_table_list = WPDA_Design_Table_Model::get_designer_table_list();
				foreach ( $designer_table_list as $key => $value ) {
					echo 'wpda_db_table_name["' . esc_attr( $value['wpda_table_name'] ) . '"]=true;';
				}
				if ( ! $this->deleted_columns_and_indexes ) {
					echo "jQuery('#checkbox_show_deleted_label').addClass('label_disabled');";
					echo "jQuery('#checkbox_show_deleted').attr('disabled', true);";
				}
				?>
			</script>
			<?php
		}

		/**
		 * Drop all indexes from database
		 *
		 * @since 2.0.14
		 */
		protected function drop_indexes() {
			foreach ( $this->real_indexes as $real_index ) {
				$this->drop_index( $real_index['index_name'] );
			}
		}

		/**
		 * Drop a specific index from database
		 *
		 * @param string $index_name Name of index to be dropped
		 *
		 * @since 2.0.14
		 *
		 */
		protected function drop_index( $index_name ) {
			$wpdadb = WPDADB::get_db_connection( $this->wpda_schema_name );
			if ( null === $wpdadb ) {
				wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->wpda_schema_name ) );
			}

			$suppress = $wpdadb->suppress_errors( true );

			// Index is deleted from table design: drop index
			$drop_index_statement = "DROP INDEX `$index_name` ON `{$this->wpda_table_name}`";
			if ( $wpdadb->query( $drop_index_statement ) ) {
				$msg = new WPDA_Message_Box(
					[
						'message_text' => sprintf( __( 'Index `%s` dropped', 'wp-data-access' ), $index_name ),
					]
				);
				$msg->box();
			} else {
				$msg = new WPDA_Message_Box(
					[
						'message_text'           => __( 'DROP INDEX failed', 'wp-data-access' ),
						'message_type'           => 'error',
						'message_is_dismissible' => false,
					]
				);
				$msg->box();
				$this->create_index_failed[] = $drop_index_statement;
			}
			$wpdadb->suppress_errors( $suppress );
		}

		/**
		 * Create indexes from design
		 *
		 * @since 2.0.14
		 */
		protected function create_index() {
			$wpdadb = WPDADB::get_db_connection( $this->wpda_schema_name );
			if ( null === $wpdadb ) {
				wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->wpda_schema_name ) );
			}

			$suppress = $wpdadb->suppress_errors( true );

			if ( 'show_create_table_script' !== $this->action2_posted ) {
				$this->drop_indexes();
			}

			// Recreate indexes.
			foreach ( $this->wpda_table_design->indexes as $index ) {
				if ( "" === $index->index_name || '' === $index->column_names ) {
					continue;
				}

				$unique = '';
				if ( 'Yes' === $index->unique ) {
					$unique = 'UNIQUE';
				} elseif ( 'FULLTEXT' === $index->unique ) {
					if ( 'on' === $this->fulltext_support ) {
						$unique = 'FULLTEXT';
					} else {
						$unique = '';
					}
				}
				$column_names_array           = explode( ',', $index->column_names );
				$column_names                 = '`' . implode( '`,`', $column_names_array ) . '`';
				$create_index_statement       =
					"CREATE $unique INDEX `{$index->index_name}` ON `{$this->wpda_table_name}` ($column_names)";
				$this->create_index_statement .= $create_index_statement . ';' . self::NEW_LINE;

				if ( 'show_create_table_script' === $this->action2_posted ) {
					continue;
				}

				if ( $wpdadb->query( $create_index_statement ) ) {
					$msg = new WPDA_Message_Box(
						[
							'message_text' => sprintf( __( 'Index `%s` created', 'wp-data-access' ), $index->index_name ),
						]
					);
					$msg->box();
				} else {
					$msg = new WPDA_Message_Box(
						[
							'message_text'           => __( 'CREATE INDEX failed', 'wp-data-access' ),
							'message_type'           => 'error',
							'message_is_dismissible' => false,
						]
					);
					$msg->box();
					$this->create_index_failed[] = $create_index_statement;
				}
			}

			$wpdadb->suppress_errors( $suppress );
		}
	}

}
