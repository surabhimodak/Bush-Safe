<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Simple_Form
 */

namespace WPDataProjects\Simple_Form {

	use WPDataAccess\Connection\WPDADB;
	use WPDataAccess\Simple_Form\WPDA_Simple_Form;
	use WPDataAccess\Simple_Form\WPDA_Simple_Form_Item_Autocomplete;
	use WPDataAccess\Simple_Form\WPDA_Simple_Form_Item_Enum;
	use WPDataAccess\Simple_Form\WPDA_Simple_Form_Item_Image;
	use WPDataAccess\Simple_Form\WPDA_Simple_Form_Item_Media;
	use WPDataAccess\Plugin_Table_Models\WPDP_Project_Design_Table_Model;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDP_Simple_Form extends WPDA_Simple_Form
	 *
	 * Uses table options to hide items and add lookups to data entry form
	 *
	 * @see WPDA_Simple_Form
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Simple_Form extends WPDA_Simple_Form {

		/**
		 * Options set name
		 *
		 * @var string
		 */
		protected $setname = 'default';

		/**
		 * WPDP_Simple_Form constructor.
		 *
		 * @param       $schema_name
		 * @param       $table_name
		 * @param       $wpda_list_columns
		 * @param array $args
		 */
		public function __construct( $schema_name, $table_name, &$wpda_list_columns, $args = [] ) {
			if ( isset ( $args['title'] ) ) {
				$this->title = $args['title'];
			}

			parent::__construct( $schema_name, $table_name, $wpda_list_columns, $args );

			$this->setname = $this->wpda_list_columns->get_setname();
		}

		/**
		 * Overwrites method prepare_items
		 *
		 * Uses table options to hide items and add lookups to data entry form
		 *
		 * @param bool $set_back_form_values
		 */
		protected function prepare_items( $set_back_form_values = false ) {
			parent::prepare_items( $set_back_form_values );

			foreach ( $this->wpda_list_columns->get_table_columns() as $columns ) {
				// Hide columns which have show attribute disabled.
				if ( isset ( $columns['show'] ) && ! $columns['show'] ) {
					$item_index = $this->get_item_index( $columns['column_name'] );
					if ( isset( $this->form_items[ $item_index ] ) ) {
						$this->form_items[ $item_index ]->set_hide_item( true );
					}
				}

				// Set default value if available
				if ( isset ( $columns['default'] ) && '' !== $columns['default'] ) {
					$item_default_value = $columns['default'];
					if ( '$$USERID$$' === $item_default_value ) {
						$item_default_value = WPDA::get_current_user_id();
					}
					if ( '$$USER$$' === $item_default_value ) {
						$item_default_value = WPDA::get_current_user_login();
					}
					if ( '$$EMAIL$$' === $item_default_value ) {
						$item_default_value = WPDA::get_current_user_email();
					}
					$item_index = $this->get_item_index( $columns['column_name'] );
					if ( isset( $this->form_items[ $item_index ] ) ) {
						$this->form_items[ $item_index ]->set_item_default_value( $item_default_value );
					}
				}
			}

			// Check if there are any lookup items defined for this table.
			$lookup_column_name = [];
			$tableform          = WPDP_Project_Design_Table_Model::get_column_options( $this->table_name, 'tableform', $this->setname, $this->schema_name );

			$i = 0;
			if ( null !== $tableform ) {
				foreach ( $tableform as $tableform_item ) {
					// Process lookup items
					if ( isset( $tableform_item->lookup ) && false !== $tableform_item->lookup ) {
						$lookup_column_name[ $tableform_item->column_name ] = $tableform_item->lookup;
					}

					if ( is_admin() ) {
						if ( isset( $tableform_item->item_type ) ) {
							// Process images
							if ( 'image' === $tableform_item->item_type ) {
								$class_path = explode( '\\', get_class( $this->form_items[ $i ] ) );
								$class_name = array_pop( $class_path );
								if ( 'WPDA_Simple_Form_Item_Image' !== $class_name ) {
									$this->form_items[ $i ] = new WPDA_Simple_Form_Item_Image( $this->form_items[ $i ] );
								}
							}

							// Process attachmemts
							if ( 'attachment' === $tableform_item->item_type ) {
								$class_path = explode( '\\', get_class( $this->form_items[ $i ] ) );
								$class_name = array_pop( $class_path );
								if ( 'WPDA_Simple_Form_Item_Media' !== $class_name ) {
									$this->form_items[ $i ] = new WPDA_Simple_Form_Item_Media( $this->form_items[ $i ] );
								}
							}
						}
					}

					$i ++;
				}
			}
			if ( sizeof( $lookup_column_name ) > 0 ) {
				// Process lookup items and create listboxes.
				$lookups       = [];
				$autocompletes = [];
				$relationships = WPDP_Project_Design_Table_Model::get_column_options( $this->table_name, 'relationships', $this->setname, $this->schema_name );

				if ( null !== $relationships ) {
					if ( isset( $relationships['relationships'] ) ) {
						foreach ( $relationships['relationships'] as $relationship ) {
							if ( isset( $relationship->relation_type ) ) {
								if ( 'lookup' === $relationship->relation_type ) {
									array_push( $lookups, $relationship );
								} elseif ( 'autocomplete' === $relationship->relation_type ) {
									array_push( $autocompletes, $relationship );
								}
							}
						}
					}
				}

				$i = 0;
				foreach ( $this->form_items as $item ) {
					if ( isset( $lookup_column_name[ $item->get_item_name() ] ) ) {
						foreach ( $autocompletes as $autocomplete ) {
							$source_column_name = $autocomplete->source_column_name[0];
							if ( $source_column_name === $item->get_item_name() ) {
								// TODO Add autocomplete lookup
								$this->form_items[ $i ] = new WPDA_Simple_Form_Item_Autocomplete( $item );
								$this->form_items[ $i ]->set_autocomplete( $autocomplete, $tableform );
								$this->form_items[ $i ]->set_item_class('hide_item');
							}
						}
						foreach ( $lookups as $lookup ) {
							// Lookups are always based on a single column. Use first element of array.
							$source_column_name = $lookup->source_column_name[0];
							if ( $source_column_name === $item->get_item_name() ) {
								// Add lookup listbox
								$target_column_name = $lookup->target_column_name[0];
								$target_table_name  = $lookup->target_table_name;

								if ( isset( $lookup->target_schema_name ) ) {
									$target_schema_name = $lookup->target_schema_name;
								} else {
									$target_schema_name = $this->schema_name;
								}

								if ( isset( $lookup_column_name[ $source_column_name ] ) ) {
									$wpdadb = WPDADB::get_db_connection( $target_schema_name );
									if ( null !== $wpdadb ) {
										$where = '';
										for ( $j = 1; $j < count( $lookup->source_column_name ); $j++ ) {
											$item_index = $this->get_item_index( $lookup->source_column_name[ $j ] );
											if ( false !== $item_index ) {
												$item_value = $this->parent['parent_key_value'][ $this->parent['parent_key'][ $j - 1 ] ];
												if ( 'number' == WPDA::get_type( $this->form_items[ $item_index ]->get_data_type() ) ) {
													$where .= $wpdadb->prepare(
														( '' === $where ? 'where' : 'and' ) . " {$lookup->target_column_name[ $j ]} = %d ",
														[ $item_value ]
													);
												} else {
													$where .= $wpdadb->prepare(
														( '' === $where ? 'where' : 'and' ) . " {$lookup->target_column_name[ $j ]} = %s ",
														[ $item_value ]
													);
												}
											}
										}

										if ( '' === $target_schema_name ) {
											$lookup_sql_table_name = "`$target_table_name`";
										} else {
											$lookup_sql_table_name = "`{$wpdadb->dbname}`.`$target_table_name`";
										}
										$lookup_sql =
											"select `{$lookup_column_name[ $source_column_name ]}`, `$target_column_name` " .
											"from $lookup_sql_table_name " .
											$where .
											"order by `{$lookup_column_name[ $source_column_name ]}`, `$target_column_name`";

										$rows = $wpdadb->get_results( $lookup_sql, 'ARRAY_A' );

										$lov_values  = [];
										$lov_options = [];

										if ( isset( $relationships['table'] ) ) {
											foreach ( $relationships['table'] as $table_column ) {
												if ( isset( $table_column->column_name ) && isset( $table_column->mandatory ) ) {
													if ( $table_column->column_name === $source_column_name && 'No' === $table_column->mandatory ) {
														array_push( $lov_values, '' );
														array_push( $lov_options, '' );
													}
												}
											}
										}

										foreach ( $rows as $row ) {
											$hide_id = false;
											foreach ( $tableform as $tableformitem ) {
												if ( isset( $tableformitem->column_name ) ) {
													if ( $tableformitem->column_name === $source_column_name ) {
														$hide_id = isset( $tableformitem->hide_lookup_key ) ?
															'on' === $tableformitem->hide_lookup_key : false;
														break;
													}
												}
											}
											if ( $hide_id ) {
												$lov_value = $row[ $lookup_column_name[ $source_column_name ] ];
											} else {
												$lov_value = $row[ $lookup_column_name[ $source_column_name ] ] . ' (' . $row[ $target_column_name ] . ')';
											}
											array_push( $lov_values, $lov_value );
											array_push( $lov_options, $row[ $target_column_name ] );
										}

										$item->set_enum( $lov_values );
										$item->set_enum_options( $lov_options );
										$this->form_items[ $i ] = new WPDA_Simple_Form_Item_Enum( $item );
									}
								}
							}
						}
					}
					$i ++;
				}
			}

		}

	}

}