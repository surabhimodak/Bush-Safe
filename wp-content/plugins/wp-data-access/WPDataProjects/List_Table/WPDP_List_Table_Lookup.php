<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\List_Table
 */

namespace WPDataProjects\List_Table {

	use WPDataAccess\Connection\WPDADB;
	use WPDataAccess\WPDA;
	use WPDataAccess\List_Table\WPDA_List_Table;
	use WPDataProjects\Data_Dictionary\WPDP_List_Columns_Cache;
	use WPDataAccess\Plugin_Table_Models\WPDP_Project_Design_Table_Model;

	/**
	 * Class WPDP_List_Table_Lookup extends WPDA_List_Table
	 *
	 * This class implements the lookup functionality. A lookup is the opposite of a one to many relationship.
	 * (1) A lookup provides the user with a listbox of possible values
	 * (2) A lookup offers the user comfort to search in lookup strings (e.g. titles or descriptions)
	 *
	 * @see WPDA_List_Table
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_List_Table_Lookup extends WPDA_List_Table {

		/**
		 * Column options for the given table
		 *
		 * @var array|null
		 */
		protected $column_options_listtable;

		/**
		 * Options set name
		 *
		 * @var string
		 */
		protected $setname = 'default';

		/**
		 * WPDP_List_Table_Lookup constructor
		 *
		 * @param array $args
		 *
		 * @see WPDA_List_Table
		 *
		 */
		public function __construct( $args = [] ) {
			parent::__construct( $args );

			$this->setname = $this->wpda_list_columns->get_setname();
			$this->column_options_listtable = WPDP_Project_Design_Table_Model::get_column_options( $this->table_name, 'listtable', $this->setname, $this->schema_name );
		}

		/**
		 * Overwrites method get_sortable_columns
		 *
		 * @return array
		 * @see WPDA_List_Table::get_sortable_columns()
		 *
		 */
		public function get_sortable_columns() {
			if ( false !== strpos( $this->orderby, '-' ) ) {
				return [];
			}

			if ( null != $this->column_options_listtable ) {
				$columns = [];
				foreach ( $this->column_options_listtable as $column_option ) {
					if ( isset( $column_option->lookup ) && $column_option->lookup !== false ) {
						// Sorting on lookup columns is not possible.
					} else {
						$columns[ $column_option->column_name ] = [ $column_option->column_name, false ];
					}
				}

				return $columns;
			} else {
				return parent::get_sortable_columns();
			}
		}

		/**
		 * Overwrites method column_default
		 *
		 * @param array  $item List of all available items
		 * @param string $column_name Database column name
		 *
		 * @return mixed|string
		 * @see WPDA_List_Table::column_default()
		 *
		 */
		public function column_default( $item, $column_name ) {
			if ( null !== $item[$column_name] && null != $this->column_options_listtable ) {
				foreach ( $this->column_options_listtable as $column_option ) {
					if ( isset( $column_option->lookup ) ) {
						if ( $column_option->column_name === $column_name && $column_option->lookup !== false ) {
							$column_options_relationships = WPDP_Project_Design_Table_Model::get_column_options( $this->table_name, 'relationships', $this->setname, $this->schema_name );
							if ( null !== $column_options_relationships ) {
								foreach ( $column_options_relationships['relationships'] as $column_options_relationship ) {
									if (
										(
											'lookup' === $column_options_relationship->relation_type ||
											'autocomplete' === $column_options_relationship->relation_type
										) &&
									    $column_options_relationship->source_column_name[0] === $column_name
									) {
										if ( isset( $column_options_relationship->target_schema_name ) ) {
											$target_schema_name = $column_options_relationship->target_schema_name;
										} else {
											$target_schema_name = $this->schema_name;
										}
										$target_table_name  = $column_options_relationship->target_table_name;
										$target_column_name = $column_options_relationship->target_column_name[0];

										$data_type = null;
										foreach ( $column_options_relationships['table'] as $tableinfo ) {
											if ( $tableinfo->column_name === $column_name ) {
												$data_type = $tableinfo->data_type;
											}
										}

										$wpdadb = WPDADB::get_db_connection( $target_schema_name );
										if ( null !== $wpdadb ) {
											$where = '';
											for ( $i = 1; $i < count( $column_options_relationship->source_column_name ); $i++ ) {
												if ( isset( $item[ $column_options_relationship->source_column_name[ $i ] ] ) ) {
													$value            = $item[ $column_options_relationship->source_column_name[ $i ] ];
													$data_type_lookup = null;
													foreach ( $column_options_relationships['table'] as $tableinfo ) {
														if ( $tableinfo->column_name === $column_options_relationship->source_column_name[ $i ] ) {
															$data_type_lookup = $tableinfo->data_type;
														}
													}
													if ( 'number' == WPDA::get_type( $data_type_lookup ) ) {
														$where .= $wpdadb->prepare( " and {$column_options_relationship->target_column_name[ $i ]} = %d", [ $value ] );
													} else {
														$where .= $wpdadb->prepare( " and {$column_options_relationship->target_column_name[ $i ]} = %s", [ $value ] );
													}
												}
											}

											$lookup_value = $this->column_lookup(
												$target_column_name,
												$target_table_name,
												$column_option->lookup,
												$item[ $column_name ],
												$data_type,
												$where,
												$target_schema_name
											);
											if ( null !== $lookup_value ) {
												$item["lookup_id_$column_name"]     = $item[ $column_name ];
												$item["lookup_column_$column_name"] = $column_option->lookup;
												$item["lookup_value_$column_name"]  = $lookup_value;
											}
										}
									}
								}
							}
						}
					}
				}
			}

			return parent::column_default( $item, $column_name );
		}

		/**
		 * Performs lookup query
		 *
		 * @param string $target_column_name Lookup key column
		 * @param string $target_table_name Lookup tabel name
		 * @param string $source_column_name Source column name
		 * @param mixed  $value Lookup key value
		 * @param string $data_type Lookup key data type
		 * @param string $and Extension of where clause
		 * @param string $target_schema_name Database schema name
		 *
		 * @return mixed Lookup value
		 */
		protected function column_lookup( $target_column_name, $target_table_name, $source_column_name, $value, $data_type, $and, $target_schema_name ) {
			$wpdadb = WPDADB::get_db_connection( $target_schema_name );
			if ( $wpdadb === null ) {
				return null;
			}

			// Value is taken from database query. No need to prepare.
			if ( '' === $target_schema_name ) {
				$lookup_sql_table_name = "`$target_table_name`";
			} else {
				$lookup_sql_table_name = "`{$wpdadb->dbname}`.`$target_table_name`";
			}
			$sql = "select `$source_column_name` from $lookup_sql_table_name where `$target_column_name` = ";
			if ( 'number' === WPDA::get_type( $data_type ) ) {
				$sql = $wpdadb->prepare( $sql . "%d $and", [ $value ] );
			} else {
				$sql = $wpdadb->prepare( $sql . "%s $and", [ $value ] );
			}
			$set = $wpdadb->get_results( $sql, 'ARRAY_A' );

			if ( 1 === $wpdadb->num_rows ) {
				return $set[0][ $source_column_name ];
			} else {
				return null;
			}
		}

		/**
		 * Constructs the where clause
		 *
		 * Uses the table options to determine lookup table and columns. A subquery is added if a lookup is found.
		 */
		protected function construct_where_clause() {
			$perform_default_method = true;
			if ( null !== $this->search_value && '' !== $this->search_value ) {
				if ( null != $this->column_options_listtable ) {
					global $wpdb;
					$nowhere       = ( '' === $this->where );
					$search_values = '%' . esc_attr( $this->search_value ) . '%';
					$where_current = '';
					$where_first   = true;
					foreach ( $this->column_options_listtable as $column_option ) {
						if ( isset( $column_option->lookup ) && $column_option->lookup !== false ) {
							// Add a subquery for text columns.
							$column_options_relationships = WPDP_Project_Design_Table_Model::get_column_options( $this->table_name, 'relationships', $this->setname, $this->schema_name );
							if ( null !== $column_options_relationships ) {
								foreach ( $column_options_relationships['relationships'] as $column_options_relationship ) {
									if (
										(
											'lookup' === $column_options_relationship->relation_type ||
											'autocomplete' === $column_options_relationship->relation_type
										) &&
									    $column_options_relationship->source_column_name[0] === $column_option->column_name
									) {
										$target_table_name  = $column_options_relationship->target_table_name;
										$target_column_name = $column_options_relationship->target_column_name[0];

										$relationship_table         = WPDP_List_Columns_Cache::get_list_columns( $wpdb->dbname, $target_table_name, 'listtable', $this->setname );
										$relationship_table_columns = $relationship_table->get_table_columns();
										foreach ( $relationship_table_columns as $relationship_table_column ) {
											if ( $column_option->lookup === $relationship_table_column['column_name'] ) {
												if ( 'varchar' === $relationship_table_column['data_type'] || 'enum' === $relationship_table_column['data_type'] ) {
													if ( '' === $this->schema_name ) {
														$lookup_sql_table_name = "`$target_table_name`";
													} else {
														$lookup_sql_table_name = "`{$this->schema_name}`.`$target_table_name`";
													}
													$where_current_prepare = "`{$column_option->column_name}` in (select `$target_column_name` from $lookup_sql_table_name where `{$column_option->lookup}` like %s)";
													if ( ! $where_first ) {
														$where_current .= ' or ';
													}
													$where_current .= $wpdb->prepare( $where_current_prepare, $search_values ); // WPCS: unprepared SQL OK.
													$where_first   = false;
												}
												break;
											}
										}
									}
								}
							}

						} else {
							// This is already handled by the parent.
						}
					}

					$where_user_search = $this->get_where_clause();
					if ( '' !== $where_user_search ) {
						if ( '' === $where_current ) {
							$where_current = $where_user_search;
						} else {
							$where_current = "($where_current or $where_user_search)";
						}
					}

					if ( '' !== $where_current ) {
						$this->where = $nowhere ? " where $where_current " : " {$this->where} and ($where_current) ";
						$perform_default_method = false;
					}
				}
			}

			if ( $perform_default_method ) {
				parent::construct_where_clause();
			} else {
				$whereclause = $this->get_where_clause();

				if ( '' === $this->where ) {
					$this->where = " where {$whereclause} ";
				}

				if ( ' (1=2) ' !== $whereclause ) {
					// TODO Add other (non lookup) columns
					// $this->where .= " and {$whereclause} "; // Needs to be tested
				}
			}
		}

		protected function get_order_by() {
			$orderby = parent::get_order_by();

			if ( '' === $orderby && null !== $this->orderby && '' !== trim( $this->orderby ) ) {
				$orderby =
					' order by ' .
					trim( str_replace( 'order by ', '', $this->orderby ) );
			}

			return $orderby;
		}

	}

}