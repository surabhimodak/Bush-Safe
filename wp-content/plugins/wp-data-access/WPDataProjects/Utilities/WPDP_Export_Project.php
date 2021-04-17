<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Utilities
 */

namespace WPDataProjects\Utilities {

	use WPDataAccess\WPDA;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;
	use WPDataAccess\Plugin_Table_Models\WPDP_Project_Model;
	use WPDataAccess\Plugin_Table_Models\WPDP_Page_Model;
	use WPDataAccess\Plugin_Table_Models\WPDP_Project_Design_Table_Model;

	/**
	 * Class WPDP_Export_Project
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Export_Project {

		/**
		 * Project ID
		 *
		 * @var string
		 */
		protected $project_id = null;

		/**
		 * Options set to be exported
		 *
		 * @var array
		 */
		protected $optionsets = [];

		/**
		 * Main method to start export
		 *
		 * This method checks arguments and starts the export according to the arguments provided.
		 *
		 * @since   1.0.0
		 */
		public function export() {

			// Get arguments.
			$this->project_id = isset( $_REQUEST['project_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['project_id'] ) ) : null; // input var okay.

			if ( null !== $this->project_id ) {
				// Check if export is allowed.
				$wp_nonce = isset( $_REQUEST['wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpnonce'] ) ) : '?'; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, 'wpdp-export-project-' . $this->project_id ) ) {
					wp_die();
				}
				$this->export_tables();
			} else {
				$this->wrong_arguments();
			}

		}

		/**
		 * Export project tables
		 *
		 * @since   2.0.0
		 */
		protected function export_tables() {
			$this->header( $this->project_id );
			$this->db_begin();

			global $wpdb;
			$this->insert_rows(
				WPDP_Project_Model::get_base_table_name(),
				$wpdb->prepare( 'where project_id = %d', [ $this->project_id ] ),
				WPDP_Project_Model::BASE_TABLE_NAME
			);
			$this->insert_rows(
				WPDP_Page_Model::get_base_table_name(),
				$wpdb->prepare( 'where project_id = %d', [ $this->project_id ] ),
				WPDP_Page_Model::BASE_TABLE_NAME
			);

			if ( sizeof( $this->optionsets ) > 0 ) {
				$this->get_child_optionsets();
				$this->insert_optionsets();
			}

			$this->db_end();
		}

		/**
		 * Set export header (filename)
		 *
		 * @param $project_id
		 */
		protected function header( $project_id ) {
			header( 'Content-type: text/plain; charset=utf-8' );
			header( "Content-Disposition: attachment; filename=wpda_project_$project_id.sql" );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
		}

		/**
		 * Set MySQL environment
		 *
		 * @since   2.0.0
		 */
		protected function db_begin() {
			global $wpdb;

			echo "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
			echo "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
			echo "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
			echo '/*!40101 SET NAMES ' . esc_attr( $wpdb->charset ) . " */;\n\n";
		}

		/**
		 * Write insert into statement
		 *
		 * @param string $table_name Database table name.
		 * @param string $where SQL where clause.
		 *
		 * @since   2.0.0
		 *
		 */
		public function insert_rows( $table_name, $where, $table_name_without_prefix ) {

			global $wpdb;

			$save_suppress_errors  = $wpdb->suppress_errors;
			$wpdb->suppress_errors = true;

			$query = "select * from $table_name $where";
			$rows  = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			if ( $wpdb->num_rows > 0 ) {
				// Prepare row export: get column names and data types.
				$wpda_list_columns = WPDA_List_Columns_Cache::get_list_columns( '', $table_name ); // repository tables only
				$table_columns     = $wpda_list_columns->get_table_columns();

				// Create array for fast column_name based access.
				$column_data_types = $this->column_data_types( $table_columns );

				// Exports rows.
				echo "--\n";
				echo '-- Export table `' . esc_attr( $table_name ) . "`\n";
				echo "--\n";

				echo "INSERT INTO `{wp_prefix}{$table_name_without_prefix}` ";

				$process_first_row = true;
				foreach ( $rows[0] as $column_name => $column_value ) {
					if (
						! ( WPDP_Project_Model::get_base_table_name() === $table_name && 'project_id' === $column_name ) &&
						! ( WPDP_Page_Model::get_base_table_name() === $table_name && 'page_id' === $column_name )
					) {
						echo $process_first_row ? '(' : ', ';
						echo '`' . esc_attr( $column_name ) . '`';

						$process_first_row = false;
					}
				}

				echo ') VALUES ';

				$first_row = true;
				foreach ( $rows as $row ) {
					if ( ! $first_row ) {
						echo ',';
					} else {
						$first_row = false;
					}
					echo "\n(";

					$keys        = array_keys( $row );
					$last_column = end( $keys );
					foreach ( $row as $column_name => $column_value ) {
						if (
							! ( WPDP_Project_Model::get_base_table_name() === $table_name && 'project_id' === $column_name ) &&
							! ( WPDP_Page_Model::get_base_table_name() === $table_name && 'page_id' === $column_name )
						) {
							if ( $this->is_numeric( $column_data_types[ $column_name ] ) ) {
								if ( WPDP_Page_Model::get_base_table_name() === $table_name && 'project_id' === $column_name ) {
									echo '@PROJECT_ID';
								} else {
									if ( null === $column_value ) {
										echo 'null';
									} else {
										echo esc_attr( $column_value );
									}
								}
							} else {
								if (
									(
										(
											WPDP_Page_Model::get_base_table_name() === $table_name &&
											'page_schema_name' === $column_name
										) ||
										(
											WPDP_Project_Design_Table_Model::get_base_table_name() === $table_name &&
											'wpda_schema_name' === $column_name
										)
									) &&
									( '' === $column_value || $wpdb->dbname === $column_value )
								) {
									echo "'{wp_schema}'";
								} else {
									if ( null === $column_value ) {
										echo 'null';
									} else {
										echo "'" . esc_sql( $column_value ) . "'";
									}
								}
							}
							if ( $column_name !== $last_column ) {
								echo ',';
							}
						}
					}

					echo ')';

					if ( WPDP_Page_Model::get_base_table_name() === $table_name ) {
						$this->add_optionset(
							$row['page_schema_name'],
							$row['page_table_name'],
							$row['page_setname']
						);
					}
				}

				echo ';';
				if ( WPDP_Project_Model::get_base_table_name() === $table_name ) {
					echo "\n";
					echo 'SET @PROJECT_ID = LAST_INSERT_ID();';
				}
				echo "\n\n";
			} else {
				// Empty table, nothing to export.
				echo "--\n";
				echo '-- No rows to export from empty table `' . esc_attr( $table_name ) . "`\n";
				echo "--\n\n";
			}

			$wpdb->suppress_errors = $save_suppress_errors;

		}

		protected function add_optionset( $schema_name, $table_name, $setname ) {
			if ( ! isset( $this->optionsets[ $schema_name ][ $table_name ][ $setname ] ) ) {
				$this->optionsets[ $schema_name ][ $table_name ][ $setname ] = true;
			}
		}

		protected function insert_optionsets() {
			$where = 'where ';
			foreach ( $this->optionsets as $schema_name => $tables ) {
				$where .= "(`wpda_schema_name`='{$schema_name}' and (";
				$first_optionset = true;
				foreach ( $tables as $table_name => $optionsets ) {
					foreach ( $optionsets as $optionset => $dummy ) {
						if ( ! $first_optionset ) {
							$where .= ' or ';
						}
						$where .= "(`wpda_table_name`='{$table_name}' and `wpda_table_setname`='$optionset')";
						$first_optionset = false;
					}
				}
				$where .= '))';
			}

			$this->insert_rows(
				WPDP_Project_Design_Table_Model::get_base_table_name(),
				$where,
				WPDP_Project_Design_Table_Model::BASE_TABLE_NAME
			);
		}

		protected function get_child_optionsets() {
			foreach ( $this->optionsets as $schema_name => $tables ) {
				foreach ( $tables as $table_name => $optionsets ) {
					foreach ( $optionsets as $optionset => $dummy ) {
						$set = WPDP_Project_Design_Table_Model::static_query( $schema_name, $table_name, $optionset );
						if ( isset( $set->relationships ) ) {
							foreach ( $set->relationships as $relationship ) {
								if ( isset( $relationship->relation_type ) ) {
									if ( '1n' === $relationship->relation_type ) {
										if ( isset( $relationship->target_table_name ) ) {
											$this->add_optionset(
												$schema_name,
												$relationship->target_table_name,
												$optionset
											);
										}
									} elseif ( 'nm' === $relationship->relation_type ) {
										if ( isset( $relationship->relation_table_name ) ) {
											$this->add_optionset(
												$schema_name,
												$relationship->relation_table_name,
												$optionset
											);
										}
									}
								}
							}
						}
					}
				}
			}
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
		 * @since   2.0.0
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
		 * @since   2.0.0
		 *
		 */
		protected function is_numeric( $data_type ) {

			return ( 'number' === WPDA::get_type( $data_type ) );

		}

		/**
		 * Set back MySQL environment
		 *
		 * @since   2.0.0
		 */
		protected function db_end() {

			echo "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
			echo "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
			echo "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";

		}

		/**
		 * Processing on invalid arguments
		 *
		 * @since   2.0.0
		 */
		protected function wrong_arguments() {

			wp_die();

		}

	}

}
