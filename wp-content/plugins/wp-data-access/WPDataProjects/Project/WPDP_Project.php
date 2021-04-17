<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Project
 */

namespace WPDataProjects\Project {

	use WPDataAccess\WPDA;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;
	use WPDataAccess\Plugin_Table_Models\WPDP_Project_Design_Table_Model;

	/**
	 * Class WPDP_Project
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Project {

		/**
		 * Project structure
		 *
		 * @var array|null
		 */
		protected $project = null;

		/**
		 * Project ID
		 *
		 * @var string|null
		 */
		protected $project_id = null;

		/**
		 * Page ID
		 *
		 * @var string|null
		 */
		protected $page_id = null;

		/**
		 * WPDP_Project constructor
		 *
		 * Set project and page ID. Sets the structure for the Data Projects tool main page if no project ID is
		 * provided. Sets the structure for the Data Projects tool table options page if project ID is
		 * 'wpda_sys_tables'.
		 *
		 * @param string $project_id Project ID
		 * @param string $page_id Page ID
		 */
		public function __construct( $project_id = null, $page_id = null ) {
			$this->project_id = $project_id;
			$this->page_id    = $page_id;

			if ( null === $this->project_id ) {
				$this->init_self();
			} elseif ( 'wpda_sys_tables' === $this->project_id ) {
				$this->init_self_tables();
			} else {
				$this->init_project_page( $this->project_id, $this->page_id );
			}
		}

		/**
		 * Sets the structure for the Data Projects tool main page
		 */
		protected function init_self() {
			global $wpdb;
			$this->project =
				[
					'mode'     => 'edit',
					'setname'   => '',
					'title'    => '',
					'subtitle' => '',
					'parent'   =>
						[
							'key'       => [ 'project_id' ],
							'data_type' => [ 'number' ],
						],
					'children' =>
						[
							[
								'table_name'      => $wpdb->prefix . 'wpda_project_page',
								'setname'         => '',
								'tab_label'       => 'Pages',
								'default_where'   => '',
								'default_orderby' => '',
								'relation_1n'     =>
									[
										'parent_key' => [ 'project_id' ],
										'child_key'  => [ 'project_id' ],
										'data_type'  => [ 'number' ],
									],
							],
						],
				];
		}

		/**
		 * Sets the structure for the Data Projects tool table options page
		 */
		protected function init_self_tables() {
			$this->project =
				[
					'mode'      => 'edit',
					'setname'   => '',
					'title'     => '',
					'subtitle'  => '',
					'parent'    =>
						[
							'key'       => [ 'wpda_table_name' ],
							'data_type' => [ 'varchar' ],
						],
					'children.' =>
						[],
				];
		}

		/**
		 * Creates the structure for the given project page
		 *
		 * @param string $project_id Project ID
		 * @param string $page_id Page ID
		 */
		protected function init_project_page( $project_id, $page_id ) {
			global $wpdb;
			$query = $wpdb->prepare(
				"
                    select * from {$wpdb->prefix}wpda_project_page 
                    where project_id = %d 
                      and page_id    = %d
                ",
				[
					$project_id,
					$page_id,
				]
			);

			$project_page = $wpdb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
			if ( 0 === $wpdb->num_rows ) {
				return;
			}

			$schema_name  = $project_page[0]['page_schema_name'];
			$table_name   = $project_page[0]['page_table_name'];
			$page_setname = $project_page[0]['page_setname'];

			$relationships = WPDP_Project_Design_Table_Model::get_column_options( $table_name, 'relationships', $page_setname, $schema_name );
			if ( ! isset( $relationships['relationships'] ) || null === $relationships['relationships'] ) {
				$this->project =
					[
						'mode'     => $project_page[0]['page_mode'],
						'setname'  => $page_setname,
						'title'    => $project_page[0]['page_title'],
						'subtitle' => $project_page[0]['page_subtitle'],
						'parent'   => [],
						'children' => [],
					];

				return;
			}

			// Add parent column and data type
			$wpda_list_columns            = WPDA_List_Columns_Cache::get_list_columns( $schema_name, $table_name );
			$parent_primary_key           = $wpda_list_columns->get_table_primary_key();
			$parent_primary_key_data_type = [];
			foreach ( $parent_primary_key as $pk ) {
				if ( isset( $relationships['table'] ) && null !== $relationships['table'] ) {
					foreach ( $relationships['table'] as $column ) {
						if ( $column->column_name === $pk ) {
							array_push( $parent_primary_key_data_type, WPDA::get_type( $column->data_type ) );
							break;
						}
					}
				}
			}

			// Add children
			$children = [];
			foreach ( $relationships['relationships'] as $relationship ) {
				$child_key_data_type = [];
				if ( '1n' === $relationship->relation_type ) {
					$n_relationship = WPDP_Project_Design_Table_Model::get_column_options( $relationship->target_table_name, 'tableinfo', $page_setname, $schema_name );

					if ( isset( $n_relationship->tab_label ) ) {
						$tab_label = $n_relationship->tab_label;
					} else {
						$tab_label = '';
					}

					foreach ( $relationship->source_column_name as $source_column_name ) {
						if ( ! in_array( $source_column_name, $parent_primary_key ) ) {
							array_push( $parent_primary_key, $source_column_name );
							foreach ( $relationships['table'] as $column ) {
								if ( $column->column_name === $source_column_name ) {
									array_push( $parent_primary_key_data_type, WPDA::get_type( $column->data_type ) );
									break;
								}
							}
						}
					}

					foreach ( $relationship->target_column_name as $target_column_name ) {
						foreach ( $relationships['table'] as $column ) {
							if ( $column->column_name === $target_column_name ) {
								array_push( $child_key_data_type, WPDA::get_type( $column->data_type ) );
								break;
							}
						}
					}

					$child = [
						'table_name'                               => $relationship->target_table_name,
						'tab_label'                                => $tab_label === '' ? $relationship->target_table_name : $tab_label,
						'default_where'                            => isset( $n_relationship->default_where ) ? $n_relationship->default_where : '',
						'default_orderby'                          => isset( $n_relationship->default_orderby ) ? $n_relationship->default_orderby : '',
						'relation_' . $relationship->relation_type => [
							'parent_key' => $relationship->source_column_name,
							'child_key'  => $relationship->target_column_name,
							'data_type'  => $child_key_data_type,
						]
					];
					array_push( $children, $child );
				} elseif ( 'nm' === $relationship->relation_type ) {
					$nm_relationships = WPDP_Project_Design_Table_Model::get_column_options( $relationship->relation_table_name, 'relationships', $page_setname, $schema_name );

					if ( isset( $nm_relationships['tableinfo'] ) && isset( $nm_relationships['tableinfo']->tab_label ) ) {
						$tab_label = $nm_relationships['tableinfo']->tab_label;
					} else {
						$tab_label = '';
					}

					if ( null !== $nm_relationships['relationships'] ) {
						$nm_relationship_found = null;
						foreach ( $nm_relationships['relationships'] as $nm_relationship ) {
							if ( $nm_relationship->target_table_name === $relationship->target_table_name ) {
								$nm_relationship_found = $nm_relationship;
								break;
							}
						}

						if ( null !== $nm_relationship_found ) {
							foreach ( $nm_relationship_found->source_column_name as $source_column_name ) {
								foreach ( $nm_relationships['table'] as $column ) {
									if ( $column->column_name === $source_column_name ) {
										array_push( $child_key_data_type, WPDA::get_type( $column->data_type ) );
										if ( ! in_array( $source_column_name, $parent_primary_key ) ) {
											array_push( $parent_primary_key, $source_column_name );
											array_push( $parent_primary_key_data_type, WPDA::get_type( $column->data_type ) );
										}
										break;
									}
								}
							}

							$child = [
								'table_name'      => $relationship->relation_table_name,
								'tab_label'       => $tab_label === '' ? $relationship->relation_table_name : $tab_label,
								'default_where'   => isset( $nm_relationships['tableinfo']->default_where ) ? $nm_relationships['tableinfo']->default_where : '',
								'default_orderby' => isset( $nm_relationships['tableinfo']->default_orderby ) ? $nm_relationships['tableinfo']->default_orderby : '',
								'relation_nm'     => [
									'child_table'        => $relationship->target_table_name,
									'parent_key'         => $nm_relationship->source_column_name,
									'child_table_select' => $nm_relationship->target_column_name,
									'child_table_where'  => $relationship->target_column_name,
									'data_type'          => $child_key_data_type,
								]
							];
							array_push( $children, $child );
						}
					}
				}
			}

			// Prepare parent key
			$parent = [
				'key'       => $parent_primary_key,
				'data_type' => $parent_primary_key_data_type,
			];

			$this->project =
				[
					'mode'     => $project_page[0]['page_mode'],
					'setname'  => $page_setname,
					'title'    => $project_page[0]['page_title'],
					'subtitle' => $project_page[0]['page_subtitle'],
					'parent'   => $parent,
					'children' => $children,
				];
		}


		/**
		 * Returns the project structure
		 *
		 * @return array
		 */
		public function get_project() {
			return $this->project;
		}

		/**
		 * Returns the project mode or null if not available
		 *
		 * @return string|null
		 */
		public function get_mode() {
			if ( isset( $this->project['mode'] ) ) {
				return $this->project['mode'];
			} else {
				return null;
			}
		}

		/**
		 * Returns the project setname or null if not available
		 *
		 * @return string|null
		 */
		public function get_setname() {
			if ( isset( $this->project['setname'] ) ) {
				return $this->project['setname'];
			} else {
				return null;
			}
		}

		/**
		 * Returns the project title or null if not available
		 *
		 * @return string|null
		 */
		public function get_title() {
			if ( isset( $this->project['title'] ) ) {
				return $this->project['title'];
			} else {
				return null;
			}
		}

		/**
		 * Returns the project sub title or null if not available
		 *
		 * @return string|null
		 */
		public function get_subtitle() {
			if ( isset( $this->project['subtitle'] ) ) {
				return $this->project['subtitle'];
			} else {
				return null;
			}
		}

		/**
		 * Returns the project parent info or null if not available
		 *
		 * @return array|null
		 */
		public function get_parent() {
			if ( isset( $this->project['parent'] ) ) {
				return $this->project['parent'];
			} else {
				return null;
			}
		}

		/**
		 * Returns the project children or null if not available
		 *
		 * @return array|null
		 */
		public function get_children() {
			if ( isset( $this->project['children'] ) ) {
				return $this->project['children'];
			} else {
				return null;
			}
		}

	}

}
