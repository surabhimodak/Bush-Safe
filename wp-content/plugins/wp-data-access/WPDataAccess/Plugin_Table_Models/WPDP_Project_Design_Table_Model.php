<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Plugin_Table_Models
 */

namespace WPDataAccess\Plugin_Table_Models {

	use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;
	use WPDataAccess\Utilities\WPDA_Message_Box;

	/**
	 * Class WPDP_Project_Design_Table_Model extends WPDA_Plugin_Table_Base_Model
	 *
	 * @see WPDA_Plugin_Table_Base_Model
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Project_Design_Table_Model extends WPDA_Plugin_Table_Base_Model {

		const BASE_TABLE_NAME = 'wpda_project_table';

		/**
		 * Cached table options
		 *
		 * @var null|array
		 */
		protected static $cache_table_options = null;

		/**
		 * Requested action
		 *
		 * @var string
		 */
		protected $action2;

		/**
		 * Name of table where design is stored
		 *
		 * @var string|null
		 */
		protected $table_name = null;

		/**
		 * Design schema name
		 *
		 * @var string
		 */
		protected $wpda_schema_name = '';

		/**
		 * Design table name
		 *
		 * @var string|null
		 */
		protected $wpda_table_name = null;

		/**
		 * Options set name (to allow multiple table definitions)
		 *
		 * @var string
		 */
		protected $wpda_table_setname = null;

		/**
		 * Check if options set name was changed
		 *
		 * @var string
		 */
		protected $wpda_table_setname_old = null;

		/**
		 * The actual table design
		 *
		 * @var string|null
		 */
		protected $wpda_table_design = null;

		/**
		 * WPDP_Project_Design_Table_Model constructor
		 *
		 * Get action2 arguments
		 */
		public function __construct() {
			$this->table_name = self::get_base_table_name();

			if ( isset( $_REQUEST['wpda_schema_name'] ) ) {
				$this->wpda_schema_name = sanitize_text_field( wp_unslash( $_REQUEST['wpda_schema_name'] ) );
			}

			// Watch out for arrays! (array = starting export)
			if ( isset( $_REQUEST['wpda_table_name'] ) && ! is_array( $_REQUEST['wpda_table_name'] ) ) {
				$this->wpda_table_name = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_name'] ) );
			}

			if ( isset( $_REQUEST['wpda_table_setname'] ) ) {
				$this->wpda_table_setname = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_setname'] ) );
			}

			if ( isset( $_REQUEST['wpda_table_setname_old'] ) ) {
				$this->wpda_table_setname_old = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_setname_old'] ) );
			}

			if ( isset( $_REQUEST['action2'] ) ) {
				$this->action2 = sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) );
			}
		}

		/**
		 * Prepare query to use setname (after reverse engineering)
		 *
		 * @param $wpda_table_setname
		 */
		public function prepare_query( $wpda_table_setname ) {
			$this->wpda_table_setname = $wpda_table_setname;
		}

		/**
		 * Get design for a specific table name
		 *
		 * @return int Number of rows.
		 */
		public function query() {
			if ( null === $this->wpda_table_name ) {
				return false;
			}

			global $wpdb;
			$query =
				$wpdb->prepare( "
							SELECT wpda_table_design
							  FROM {$this->table_name}
							 WHERE wpda_schema_name = %s
							  AND  wpda_table_name = %s
							  AND  wpda_table_setname = %s
						",
					[
						$this->wpda_schema_name,
						$this->wpda_table_name,
						null === $this->wpda_table_setname_old ? $this->wpda_table_setname : $this->wpda_table_setname_old,
					]
				);

			$wpda_table_design_raw = $wpdb->get_results( $query, 'ARRAY_A' );
			if ( $wpdb->num_rows > 0 ) {
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
		 * Return table design
		 *
		 * @return null|array
		 */
		public function get_table_setname() {
			return $this->wpda_table_setname;
		}

		/**
		 * Overwrite method validate to add table options validation
		 *
		 * @return array
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

			if ( isset( $this->wpda_table_design->tableform_column_options ) ) {
				if ( count( $this->wpda_table_design->table ) !== count( $this->wpda_table_design->tableform_column_options ) ) {
					$structure_messages[] = [ 'ERR', 'Invalid structure [run reconcile]' ];
				}
			}

			return $structure_messages;
		}

		/**
		 * Overwrite method prepare_update to prepare table options
		 *
		 */
		public function prepare_update() {
			$this->query();

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

			switch ( $this->action2 ) {
				case 'relation':
					$this->prepare_update_relation();
					break;
				case 'listtable':
					$this->prepare_update_listtable();
					break;
				case 'tableform':
					$this->prepare_update_tableform();
					break;
				case 'tableinfo':
					$this->prepare_update_tableinfo();
			}
		}

		/**
		 * Set relationships to prepare update
		 */
		protected function prepare_update_relation() {
			unset( $this->wpda_table_design->relationships );
			if ( isset( $_REQUEST['row_num'] ) ) {
				$no_columns = count( $_REQUEST['row_num'] );
				if (
					isset( $_REQUEST['relation_type'] ) &&
					$no_columns === count( $_REQUEST['relation_type'] ) &&
					isset( $_REQUEST['source_column_name'] ) &&
					$no_columns === count( $_REQUEST['source_column_name'] ) &&
					isset( $_REQUEST['target_table_name'] ) &&
					$no_columns === count( $_REQUEST['target_table_name'] ) &&
					isset( $_REQUEST['target_column_name'] ) &&
					$no_columns === count( $_REQUEST['target_column_name'] )
				) {
					for ( $i = 0; $i < $no_columns; $i ++ ) {
						$relation_type      = sanitize_text_field( wp_unslash( $_REQUEST['relation_type'][ $i ] ) );
						$source_column_name = sanitize_text_field( wp_unslash( $_REQUEST['source_column_name'][ $i ] ) );
						$target_table_name  = sanitize_text_field( wp_unslash( $_REQUEST['target_table_name'][ $i ] ) );
						$target_column_name = sanitize_text_field( wp_unslash( $_REQUEST['target_column_name'][ $i ] ) );
						if ( isset( $_REQUEST['target_schema_name'][ $i ] ) ) {
							$target_schema_name = sanitize_text_field( wp_unslash( $_REQUEST['target_schema_name'][ $i ] ) );
						} else {
							$target_schema_name = '';
						}

						if ( 'nm' === $relation_type ) {
							if ( isset( $_REQUEST[ 'relation_table_name_' . $i ] ) ) {
								$relation_table_name = sanitize_text_field( wp_unslash( $_REQUEST[ 'relation_table_name_' . $i ] ) );
							} else {
								$relation_table_name = '';
							}
							if ( trim( $relation_table_name ) === '' ) {
								$msg = new WPDA_Message_Box(
									[
										'message_text'           => __( 'Invalid array [missing required fields]', 'wp-data-access' ),
										'message_type'           => 'error',
										'message_is_dismissible' => false,
									]
								);
								$msg->box();

								return;
							}
						}

						if (
							trim( $relation_type ) !== '' &&
							trim( $source_column_name ) !== '' &&
							trim( $target_table_name ) !== '' &&
							trim( $target_column_name ) !== ''
						) {
							$source_column_name_array = [];
							$target_column_name_array = [];

							array_push( $source_column_name_array, $source_column_name );
							array_push( $target_column_name_array, $target_column_name );

							if ( isset( $_REQUEST['num_source_column_name'][ $i ] ) ) {
								$num_source_column_name = sanitize_text_field( wp_unslash( $_REQUEST['num_source_column_name'][ $i ] ) );
								if ( is_numeric( $num_source_column_name ) ) {
									for ( $j = 1; $j <= $num_source_column_name; $j ++ ) {
										if (
											isset( $_REQUEST[ 'source_column_name_' . $i . '_' . $j ] ) &&
											isset( $_REQUEST[ 'target_column_name_' . $i . '_' . $j ] )
										) {
											array_push( $source_column_name_array, sanitize_text_field( wp_unslash( $_REQUEST[ 'source_column_name_' . $i . '_' . $j ] ) ) );
											array_push( $target_column_name_array, sanitize_text_field( wp_unslash( $_REQUEST[ 'target_column_name_' . $i . '_' . $j ] ) ) );
										}
									}
								}
							}

							$this->wpda_table_design->relationships[ $i ]['relation_type']      = $relation_type;
							$this->wpda_table_design->relationships[ $i ]['source_column_name'] = $source_column_name_array;
							$this->wpda_table_design->relationships[ $i ]['target_table_name']  = $target_table_name;
							$this->wpda_table_design->relationships[ $i ]['target_column_name'] = $target_column_name_array;
							if ( 'nm' === $relation_type ) {
								$this->wpda_table_design->relationships[ $i ]['relation_table_name'] = $relation_table_name;
							} elseif ( 'lookup' === $relation_type || 'autocomplete' === $relation_type ) {
								$this->wpda_table_design->relationships[ $i ]['target_schema_name']  = $target_schema_name;
							}
						}
					}
				} else {
					$msg = new WPDA_Message_Box(
						[
							'message_text'           => __( 'Invalid array [missing required fields]', 'wp-data-access' ),
							'message_type'           => 'error',
							'message_is_dismissible' => false,
						]
					);
					$msg->box();
				}
			}
		}

		/**
		 * Set table options for list table to prepare update
		 */
		protected function prepare_update_listtable() {
			$column_options = $this->get_column_options_from_request();
			if ( null !== $column_options ) {
				$this->wpda_table_design->listtable_column_options = $column_options;
			}
		}

		/**
		 * Set table options for data entry form to prepare update
		 */
		protected function prepare_update_tableform() {
			$column_options = $this->get_column_options_from_request();
			if ( null !== $column_options ) {
				$this->wpda_table_design->tableform_column_options = $column_options;
			}
		}

		/**
		 * Set table level options to prepare update
		 */
		protected function prepare_update_tableinfo() {
			if ( isset( $_REQUEST['table_setname'] ) ) {
				$this->wpda_table_setname = sanitize_text_field( wp_unslash( $_REQUEST['table_setname'] ) );
			}

			if ( isset( $_REQUEST['tab_label'] ) ) {
				$this->wpda_table_design->tableinfo->tab_label =
					sanitize_text_field( wp_unslash( $_REQUEST['tab_label'] ) );
			}

			if ( isset( $_REQUEST['default_where'] ) ) {
				$this->wpda_table_design->tableinfo->default_where =
					sanitize_text_field( wp_unslash( $_REQUEST['default_where'] ) );
			}

			if ( isset( $_REQUEST['default_orderby'] ) ) {
				$this->wpda_table_design->tableinfo->default_orderby =
					sanitize_text_field( wp_unslash( $_REQUEST['default_orderby'] ) );
			}

			$settings_db = WPDA_Table_Settings_Model::query( $this->wpda_table_name, $this->wpda_schema_name );
			if ( isset( $settings_db[0]['wpda_table_settings'] ) && '' !== $settings_db[0]['wpda_table_settings'] ) {
				$settings = json_decode( $settings_db[0]['wpda_table_settings'] );
				if ( isset( $settings->hyperlinks ) && is_array( $settings->hyperlinks ) ) {
					$hyperlinks       = [];
					$hyperlinks_child = [];
					foreach ( $settings->hyperlinks as $hyperlink ) {
						if ( isset( $hyperlink->hyperlink_label ) ) {
							$hyperlink_label_replace_spaces = ucfirst( str_replace( ' ', '_', $hyperlink->hyperlink_label ) );

							if ( isset( $_REQUEST[ "{$hyperlink_label_replace_spaces}_hyperlink" ] ) ) {
								$hyperlinks[ $hyperlink->hyperlink_label ] = true;
							} else {
								$hyperlinks[ $hyperlink->hyperlink_label ] = false;
							}

							if ( isset( $_REQUEST[ "{$hyperlink_label_replace_spaces}_hyperlink_child" ] ) ) {
								$hyperlinks_child[ $hyperlink->hyperlink_label ] = true;
							} else {
								$hyperlinks_child[ $hyperlink->hyperlink_label ] = false;
							}
						}
					}
					$this->wpda_table_design->tableinfo->hyperlinks_parent = $hyperlinks;
					$this->wpda_table_design->tableinfo->hyperlinks_child  = $hyperlinks_child;
				}
			}

			if ( has_filter('wpda_data_projects_save_table_option') ) {
				$this->wpda_table_design->tableinfo->custom_table_settings = apply_filters(
					'wpda_data_projects_save_table_option',
					'',
					$this->wpda_schema_name,
					$this->wpda_table_name
				);
			}
		}

		/**
		 * Get table column options
		 *
		 * @return array|null
		 */
		protected function get_column_options_from_request() {
			if ( isset( $_REQUEST['list_item_name'] ) ) {
				$tableform_column_options = [];
				$i                        = 0;
				foreach ( $_REQUEST['list_item_name'] as $column_name ) {
					$tableform_column_options[] = [
						'column_name'     => $column_name,
						'label'           => isset( $_REQUEST[ $column_name ] ) ?
							sanitize_text_field( wp_unslash( $_REQUEST[ $column_name ] ) ) :
							ucfirst( str_replace( '_', ' ', $column_name ) ),
						'less'            => isset( $_REQUEST["{$column_name}_less"] ) ? 'on' : 'off',
						'show'            => isset( $_REQUEST["{$column_name}_show"] ) ? 'on' : 'off',
						'lookup'          => isset( $_REQUEST["{$column_name}_lookup"] ) ?
							sanitize_text_field( wp_unslash( $_REQUEST["{$column_name}_lookup"] ) ) :
							false,
						'hide_lookup_key' => isset( $_REQUEST["{$column_name}_hide_lookup_key"] ) ? 'on' : 'off',
					];
					if ( isset( $_REQUEST["{$column_name}_default"] ) && '' !== $_REQUEST["{$column_name}_default"] ) {
						$tableform_column_options[ $i ]['default'] =
							sanitize_text_field( wp_unslash( $_REQUEST["{$column_name}_default"] ) );
					}
					$i ++;
				}

				return $tableform_column_options;
			} else {
				return null;
			}
		}

		/**
		 * Reconcile table
		 *
		 * @param object $table_structure Table structure
		 * @param string $param_keep_options Possible values: 'on' and 'off'
		 *
		 * @return mixed Result of SQL update
		 */
		public function reconcile(
			$table_structure,
			$param_keep_options
		) {
			if ( 1 === $this->query() ) {
				// Table definition.
				$this->wpda_table_design->design_mode = $table_structure['design_mode'];
				$this->wpda_table_design->engine      = $table_structure['engine'];
				$this->wpda_table_design->collation   = $table_structure['collation'];
				// Table columns.
				$this->wpda_table_design->table = $table_structure['table'];
				// Table indexes.
				$this->wpda_table_design->indexes = $table_structure['indexes'];

				if ( 'on' !== $param_keep_options ) {
					// Clear arrays.
					$this->wpda_table_design->listtable_column_options = [];
					$this->wpda_table_design->tableform_column_options = [];
					$this->wpda_table_design->tableinfo                = [
						"tab_label" => "",
						"default_where" => "",
						"default_orderby" => "",
						"custom_table_settings" => "",
					];
				} else {
					// Remove non existing columns from arrays.
					$new_listtable_column_options = [];
					foreach ( $this->wpda_table_design->listtable_column_options as $listtable_column_option ) {
						$column_found = false;
						foreach ( $this->wpda_table_design->table as $table_column ) {
							if ( $table_column->column_name === $listtable_column_option->column_name ) {
								$column_found = true;
							}
						}
						if ( $column_found ) {
							// Add only column to array that were found in the table definition.
							array_push( $new_listtable_column_options, $listtable_column_option );
						}
					}
					$this->wpda_table_design->listtable_column_options = $new_listtable_column_options;
					$new_tableform_column_options                      = [];
					foreach ( $this->wpda_table_design->tableform_column_options as $tableform_column_option ) {
						$column_found = false;
						foreach ( $this->wpda_table_design->table as $table_column ) {
							if ( $table_column->column_name === $tableform_column_option->column_name ) {
								$column_found = true;
							}
						}
						if ( $column_found ) {
							// Add only column to array that were found in the table definition.
							array_push( $new_tableform_column_options, $tableform_column_option );
						}
					}
					$this->wpda_table_design->tableform_column_options = $new_tableform_column_options;
				}

				$wpda_list_columns    = WPDA_List_Columns_Cache::get_list_columns( $this->wpda_schema_name, $this->wpda_table_name );
				$table_column_headers = $wpda_list_columns->get_table_column_headers();

				foreach ( $this->wpda_table_design->table as $column ) {
					if ( 'on' !== $param_keep_options ) {
						// Every column must be added to array.
						$this->reconcile_add_column(
							$column,
							$wpda_list_columns->get_column_label( $column->column_name ),
							isset( $table_column_headers[ $column->column_name ] ) ?
								$table_column_headers[ $column->column_name ] :
								$wpda_list_columns::get_default_column_label( $column->column_name )
						);
					} else {
						// Add only new columns to array.
						$column_found = false;
						foreach ( $this->wpda_table_design->listtable_column_options as $listtable_column_option ) {
							if ( isset( $column->column_name ) && isset( $listtable_column_option->column_name ) ) {
								if ( $column->column_name === $listtable_column_option->column_name ) {
									$column_found = true;
									break;
								}
							}
						}
						if ( ! $column_found ) {
							// Add column to both arrays.
							$this->reconcile_add_column(
								$column,
								$wpda_list_columns->get_column_label( $column->column_name ),
								isset( $table_column_headers[ $column->column_name ] ) ?
									$table_column_headers[ $column->column_name ] :
									$wpda_list_columns::get_default_column_label( $column->column_name )
							);
						}
					}
				}
			}
			global $wpdb;

			return
				$wpdb->update(
					$this->table_name,
					[
						'wpda_table_design' => json_encode( $this->wpda_table_design ),
					],
					[
						'wpda_schema_name'    => $this->wpda_schema_name,
						'wpda_table_name'    => $this->wpda_table_name,
						'wpda_table_setname' => $this->wpda_table_setname,
					]
				);
		}

		/**
		 * Reconcile a columns
		 *
		 * @param object $column Column info
		 */
		private function reconcile_add_column( $column, $label_list, $label_form ) {
			$this->wpda_table_design->listtable_column_options[] =
				[
					"column_name" => $column->column_name,
					"label"       => $label_list,
					"show"        => "on",
				];
			$this->wpda_table_design->tableform_column_options[] =
				[
					"column_name" => $column->column_name,
					"label"       => $label_form,
					"show"        => "on",
				];
		}

		/**
		 * Get column options for specific table name and label type
		 *
		 * @param string $table_name Database table name
		 * @param string $label_type Label type
		 * @param string $setname Options set name
		 *
		 * @return array|null
		 */
		public static function get_column_options( $table_name, $label_type, $setname = 'default', $schema_name = '' ) {
			if ( ! isset( self::$cache_table_options[ "$table_name.$setname" ] ) ) {
				global $wpdb;
				$query = $wpdb->prepare(
					"
		              SELECT wpda_table_design
		                FROM " . self::get_base_table_name() . "
		               WHERE wpda_schema_name = %s
		                 AND wpda_table_name = %s
		                 AND ( wpda_table_setname = %s OR wpda_table_setname = 'default')
		               ORDER BY IF( wpda_table_setname='default', 1 , 0 )
		            ",
					[
						$schema_name,
						$table_name,
						$setname,
					]
				);

				$table_json = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
				if ( $wpdb->num_rows > 0 ) {
					if ( isset( $table_json[0]['wpda_table_design'] ) ) {
						$table_obj = json_decode( $table_json[0]['wpda_table_design'] );
						if ( isset( $table_obj->table ) ) {
							self::$cache_table_options[ "$table_name.$setname" ]['table'] = $table_obj->table;
						}
						self::$cache_table_options[ "$table_name.$setname" ]['tableinfo'] = isset( $table_obj->tableinfo ) ? $table_obj->tableinfo : null;
						if ( isset( $table_obj->tableform_column_options ) ) {
							self::$cache_table_options[ "$table_name.$setname" ]['tableform'] = $table_obj->tableform_column_options;
						}
						if ( isset( $table_obj->listtable_column_options ) ) {
							self::$cache_table_options[ "$table_name.$setname" ]['listtable'] = $table_obj->listtable_column_options;
						}
						if ( isset( $table_obj->relationships ) ) {
							self::$cache_table_options[ "$table_name.$setname" ]['relationships'] = $table_obj->relationships;
						}
					}
				}
			}

			if ( 'tableform' === $label_type && isset( self::$cache_table_options[ "$table_name.$setname" ]['tableform'] ) ) {
				return self::$cache_table_options[ "$table_name.$setname" ]['tableform'];
			}
			if ( 'listtable' === $label_type && isset( self::$cache_table_options[ "$table_name.$setname" ]['listtable'] ) ) {
				return self::$cache_table_options[ "$table_name.$setname" ]['listtable'];
			}
			if ( 'relationships' === $label_type && isset( self::$cache_table_options[ "$table_name.$setname" ]['relationships'] ) ) {
				return [
					'table'         => isset( self::$cache_table_options[ "$table_name.$setname" ]['table'] ) ? self::$cache_table_options[ "$table_name.$setname" ]['table'] : null,
					'tableinfo'     => isset( self::$cache_table_options[ "$table_name.$setname" ]['tableinfo'] ) ? self::$cache_table_options[ "$table_name.$setname" ]['tableinfo'] : null,
					'relationships' => self::$cache_table_options[ "$table_name.$setname" ]['relationships'],
				];
			}
			if ( 'tableinfo' === $label_type ) {
				return isset( self::$cache_table_options[ "$table_name.$setname" ]['tableinfo'] ) ? self::$cache_table_options[ "$table_name.$setname" ]['tableinfo'] : null;
			}

			return null;
		}

		/**
		 * Overwrites method insert_reverse_engineered to add table options
		 *
		 * @param $wpda_table_name
		 * @param $wpda_table_setname
		 * @param $wpda_table_design
		 *
		 * @return bool
		 */
		public static function insert_reverse_engineered( $wpda_table_name, $wpda_table_setname, $wpda_table_design, $wpda_schema_name = '' ) {
			global $wpdb;

			$table_name = self::get_base_table_name();

			$wpda_table_design['table_type'] = WPDA_Design_Table_Model::get_table_type( $wpda_table_name, $wpda_schema_name );

			$wpda_table_design['listtable_column_options'] = [];
			$wpda_table_design['tableform_column_options'] = [];
			$wpda_table_design['tableinfo']                = [
				"tab_label" => "",
				"default_where" => "",
				"default_orderby" => "",
				"custom_table_settings" => "",
			];

			$wpda_list_columns    = WPDA_List_Columns_Cache::get_list_columns( $wpda_schema_name, $wpda_table_name );
			$table_column_headers = $wpda_list_columns->get_table_column_headers();

			foreach ( $wpda_table_design['table'] as $column ) {
				$label                                           = $wpda_list_columns->get_column_label( $column->column_name );
				$wpda_table_design['listtable_column_options'][] =
					[
						"column_name" => $column->column_name,
						"label"       => $label,
						"show"        => "on",
					];

				$label                                           = isset( $table_column_headers[ $column->column_name ] ) ?
					$table_column_headers[ $column->column_name ] : $wpda_list_columns::get_default_column_label( $column->column_name );
				$wpda_table_design['tableform_column_options'][] =
					[
						"column_name" => $column->column_name,
						"label"       => $label,
						"show"        => "on",
					];
			}

			return
				(
					1 === $wpdb->insert(
						$table_name,
						[
							'wpda_schema_name'   => $wpda_schema_name,
							'wpda_table_name'    => $wpda_table_name,
							'wpda_table_setname' => $wpda_table_setname,
							'wpda_table_design'  => json_encode( $wpda_table_design ),
						]
					)
				);
		}

		/**
		 * Update table design
		 *
		 * @return bool TRUE = update successfull, FALSE ; update failed.
		 */
		public function update() {
			if ( null === $this->wpda_table_name ) {
				return false;
			}

			$this->prepare_update();

			$setname_old =
				$this->wpda_table_setname !== $this->wpda_table_setname_old ?
					$this->wpda_table_setname_old :
					$this->wpda_table_setname;

			global $wpdb;
			$result_update = $wpdb->update(
				$this->table_name,
				[
					'wpda_table_setname' => $this->wpda_table_setname,
					'wpda_table_design'  => json_encode( $this->wpda_table_design ),
				],
				[
					'wpda_schema_name'   => $this->wpda_schema_name,
					'wpda_table_name'    => $this->wpda_table_name,
					'wpda_table_setname' => $setname_old,
				]
			);
			if ( ! $result_update && $this->wpda_table_setname !== $this->wpda_table_setname_old ) {
				$this->wpda_table_setname = $this->wpda_table_setname_old;
			}

			return $result_update;
		}

		/**
		 * Get table design from function arguments instead of HTTP arguments
		 *
		 * @param $wpda_schema_name
		 * @param $wpda_table_name
		 * @param $wpda_set_name
		 *
		 * @return mixed|null
		 */
		public static function static_query( $wpda_schema_name, $wpda_table_name, $wpda_set_name ) {
			$wpda_table_design_raw = self::do_static_query( $wpda_schema_name, $wpda_table_name, $wpda_set_name );

			global $wpdb;
			if ( $wpdb->num_rows === 0 && $wpda_set_name !== 'default' ) {
				$wpda_table_design_raw = self::do_static_query( $wpda_schema_name, $wpda_table_name, 'default' );
			}

			if ( $wpdb->num_rows > 0 ) {
				return json_decode( $wpda_table_design_raw[0]['wpda_table_design'] );
			} else {
				return null;
			}
		}

		protected static function do_static_query( $wpda_schema_name, $wpda_table_name, $wpda_set_name ) {
			global $wpdb;
			$query =
				$wpdb->prepare( "
							SELECT wpda_table_design
							  FROM " . self::get_base_table_name() . "
							 WHERE wpda_schema_name = %s
							  AND  wpda_table_name = %s
							  AND  wpda_table_setname = %s
						",
					[
						$wpda_schema_name,
						$wpda_table_name,
						$wpda_set_name,
					]
				);

			return $wpdb->get_results( $query, 'ARRAY_A' );
		}

	}

}
