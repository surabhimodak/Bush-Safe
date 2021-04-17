<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Data_Dictionary
 */

namespace WPDataProjects\Data_Dictionary {

	use Cassandra\Varint;
	use \WPDataAccess\Data_Dictionary\WPDA_List_Columns;
	use \WPDataAccess\Plugin_Table_Models\WPDP_Project_Design_Table_Model;

	/**
	 * Class WPDP_List_Columns
	 *
	 * Taken from WPDA_List_Columns. This class adds extra functionality for Data Projects. Column headers
	 * defined in 'Manage Table Options' are taken into account in this class.
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_List_Columns extends WPDA_List_Columns {

		/**
		 * Options set name
		 *
		 * @var string
		 */
		protected $setname = 'default';

		/**
		 * Possible values: listtable and tableform
		 *
		 * @var label_type
		 */
		protected $label_type;

		/**
		 * Column options as define in Data Projects
		 *
		 * @var null|array
		 */
		protected $column_options = null;

		protected $parent_columns = null;

		/**
		 * WPDP_List_Columns constructor.
		 *
		 * @param string $schema_name Database schema name
		 * @param string $table_name Database table name
		 * @param string $label_type Label type
		 * @param string $setname Options set name
		 */
		public function __construct( $schema_name, $table_name, $label_type, $setname = 'default' ) {
			$this->label_type = $label_type;
			$this->setname    = $setname;

			$column_options   = WPDP_Project_Design_Table_Model::get_column_options( $table_name, $this->label_type, $this->setname, $schema_name );
			if ( null !== $column_options ) {
				foreach ( $column_options as $column_option ) {
					if ( isset( $column_option->column_name ) ) {
						$option                                              = [
							'label'   => isset( $column_option->label ) ? $column_option->label : null,
							'show'    => isset( $column_option->show ) ? $column_option->show : null,
							'less'    => isset( $column_option->less ) ? $column_option->less : null,
							'default' => isset( $column_option->default ) ? $column_option->default : null,
						];
						$this->column_options[ $column_option->column_name ] = $option;
					}
				}
			}

			parent::__construct( $schema_name, $table_name );
		}

		/**
		 * Get column label (overwrites default method)
		 *
		 * Take column label from structure or default if not found (call parent method)
		 *
		 * @param string $column_name Database column name
		 *
		 * @return string Column label
		 */
		public function get_column_label( $column_name ) {
			if ( null !== $this->column_options && isset( $this->table_column_headers[ $column_name ] ) ) {
				return $this->table_column_headers[ $column_name ];
			} else {
				return parent::get_column_label( $column_name );
			}
		}

		/**
		 * Return options set name
		 *
		 * @return string
		 */
		public function get_setname() {
			return $this->setname;
		}

		public function get_all_columns() {
			return $this->parent_columns;
		}

		/**
		 * Set table columns (overwrites default method)
		 *
		 * Calls parent method to perform query and then sorts the result
		 */
		protected function set_table_columns() {
			parent::set_table_columns();

			// Save (all) columns
			$this->parent_columns = $this->get_table_columns();

			// Reorder table columns according to sequence defined by user.
			$table_columns_sorted = [];
			if ( ! isset( $this->table_columns ) ) {
				wp_die( __( 'ERROR: Wrong arguments [no table columns]', 'wp-data-access' ) );
			}

			if ( null !== $this->column_options ) {
				foreach ( $this->table_columns as $table_column ) {
					if ( isset( $this->column_options[ $table_column['column_name'] ] ) ) {
						$column_option = $this->column_options[ $table_column['column_name'] ];

						if ( isset( $column_option['show'] ) && 'on' === $column_option['show'] && 'listtable' === $this->label_type ) {
							$table_columns_sorted[ $table_column['column_name'] ] = $table_column;
						} elseif ( 'tableform' === $this->label_type ) {
							if ( isset( $column_option['show'] ) && 'off' === $column_option['show'] ) {
								$table_column['show'] = false;
							}

							if ( isset( $column_option['less'] ) && 'off' === $column_option['less'] ) {
								$table_column['less'] = false;
							}

							if ( isset( $column_option['default'] ) && '' !== $column_option['default'] ) {
								$table_column['default'] = $column_option['default'];
							}

							// $table_columns_sorted[] = $table_column;
							$table_columns_sorted[ $table_column['column_name'] ] = $table_column;
						}
					}
				}

				// Re-order columns
				$table_columns_resorted = [];
				foreach ( $this->column_options as $column_name => $column_options ) {
					if ( isset( $table_columns_sorted[ $column_name ] ) ) {
						$table_columns_resorted[] = $table_columns_sorted[ $column_name ];
					}
				}

				$this->table_columns = $table_columns_resorted;
			}
		}

		/**
		 * Set table column headers
		 *
		 * Use headers if a structure is found for the given table. Otherwise call parent to use the default.
		 */
		protected function set_table_column_headers() {
			if ( ! isset( $this->table_columns ) ) {
				wp_die( __( 'ERROR: Wrong arguments [no table columns]', 'wp-data-access' ) );
			}

			if ( null === $this->column_options ) {
				parent::set_table_column_headers();
			} else {
				$this->table_column_headers = [];

				foreach ( $this->table_columns as $key => $value ) {
					$column_option = $this->column_options[ $value['column_name'] ];
					if ( isset( $column_option['label'] ) ) {
						$this->table_column_headers[ $value['column_name'] ] = $column_option['label'];
					} else {
						$this->table_column_headers[ $value['column_name'] ] = $this->get_column_label( $value['column_name'] );
					}
				}
			}
		}

		/**
		 * Gets the index of the column
		 *
		 * @param string $column_options Array containing columns and their options
		 * @param string $column_name Database column name
		 *
		 * @return int Column index
		 */
		private function get_array_index( $column_options, $column_name ) {
			$index = 0;
			foreach ( $column_options as $column_option ) {
				if ( isset( $column_option->column_name ) && $column_option->column_name === $column_name ) {
					return $index;
				}
				$index ++;
			}
		}

	}

}