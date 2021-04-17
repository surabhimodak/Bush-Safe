<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Project
 */

namespace WPDataProjects\Project {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists;
	use WPDataAccess\Plugin_Table_Models\WPDP_Project_Design_Table_Model;
	use WPDataAccess\Simple_Form\WPDA_Simple_Form_Item_Enum;
	use WPDataAccess\Simple_Form\WPDA_Simple_Form_Item_Set;
	use WPDataAccess\WPDA;
	use WPDataProjects\Parent_Child\WPDP_Child_Form;

	/**
	 * Class WPDP_Project_Page_Form extends WPDP_Child_Form
	 *
	 * @see WPDP_Child_Form
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Project_Page_Form extends WPDP_Child_Form {

		protected $database_tables = [];

		/**
		 * WPDP_Project_Page_Form constructor.
		 *
		 * @param       $schema_name
		 * @param       $table_name
		 * @param       $wpda_list_columns
		 * @param array $args
		 */
		public function __construct( $schema_name, $table_name, $wpda_list_columns, array $args = [] ) {
			// Add column labels.
			$args['column_headers'] = [
				'project_id'        => __( 'Project ID', 'wp-data-access' ),
				'page_id'           => __( 'Page ID', 'wp-data-access' ),
				'add_to_menu'       => __( 'Add To Menu', 'wp-data-access' ),
				'page_name'         => __( 'Menu Name', 'wp-data-access' ),
				'page_type'         => __( 'Type', 'wp-data-access' ),
				'page_schema_name'  => __( 'Schema Name', 'wp-data-access' ),
				'page_table_name'   => __( 'Table Name', 'wp-data-access' ),
				'page_setname'      => __( 'Template Set Name', 'wp-data-access' ),
				'page_mode'         => __( 'Mode', 'wp-data-access' ),
				'page_allow_insert' => __( 'Allow insert?', 'wp-data-access' ),
				'page_allow_delete' => __( 'Allow delete?', 'wp-data-access' ),
				'page_allow_import' => __( 'Allow import?', 'wp-data-access' ),
				'page_allow_bulk'   => __( 'Allow bulk actions?', 'wp-data-access' ),
				'page_content'      => __( 'Post', 'wp-data-access' ),
				'page_title'        => __( 'Title', 'wp-data-access' ),
				'page_subtitle'     => __( 'Subtitle', 'wp-data-access' ),
				'page_role'         => __( 'Role', 'wp-data-access' ),
				'page_where'        => __( 'WHERE Clause', 'wp-data-access' ),
				'page_sequence'     => __( 'Seq#', 'wp-data-access' ),
			];

			parent::__construct( $schema_name, $table_name, $wpda_list_columns, $args );
		}

		/**
		 * Overwrites method prepare_items for specific user interaction
		 *
		 * @param bool $set_back_form_values
		 */
		protected function prepare_items( $set_back_form_values = false ) {
			parent::prepare_items( $set_back_form_values );

			global $wpdb;

			// Get available databases
			$schema_names    = WPDA_Dictionary_Lists::get_db_schemas();
			$databases       = [];
			foreach ( $schema_names as $schema_name ) {
				array_push( $databases, $schema_name['schema_name'] );

				// Check table access to prepare table listbox content
				if ( $wpdb->dbname === $schema_name['schema_name'] ) {
					$table_access = WPDA::get_option( WPDA::OPTION_BE_TABLE_ACCESS );
				} else {
					$table_access = get_option( WPDA::BACKEND_OPTIONNAME_DATABASE_ACCESS . $schema_name['schema_name'] );
					if ( false === $table_access ) {
						$table_access = 'show';
					}
				}
				switch ( $table_access ) {
					case 'show':
						$tables = $this->get_all_db_tables( $schema_name['schema_name'] );
						break;
					case 'hide':
						$tables = $this->get_all_db_tables( $schema_name['schema_name'] );
						// Remove WordPress tables from listbox content
						$tables_named = [];
						foreach ( $tables as $table ) {
							$tables_named[ $table ] = true;
						}
						foreach ( $wpdb->tables( 'all', true ) as $wp_table ) {
							unset( $tables_named[ $wp_table ] );
						}
						$tables = [];
						foreach ( $tables_named as $key => $value ) {
							array_push( $tables, $key );
						}
						break;
					default:
						// Show only selected tables and views
						if ( $wpdb->dbname === $schema_name['schema_name'] ) {
							$tables = WPDA::get_option( WPDA::OPTION_BE_TABLE_ACCESS_SELECTED );
						} else {
							$tables = get_option( WPDA::BACKEND_OPTIONNAME_DATABASE_SELECTED . $schema_name['schema_name'] );
							if ( false === $tables ) {
								$tables = '';
							}
						}
				}
				$this->database_tables[ $schema_name['schema_name'] ] = $tables;
			}

			$tables = [];
			$column_index = $this->get_item_index( 'page_schema_name' );
			if ( false !== $column_index ) {
				$pub_schema_name = $this->form_items[ $column_index ]->get_item_value();
				if ( '' === $pub_schema_name || null === $pub_schema_name ) {
					$pub_schema_name = WPDA::get_user_default_scheme();
				}
				if ( isset( $this->database_tables[ $pub_schema_name ] ) ) {
					$tables = $this->database_tables[ $pub_schema_name ];
				}
			}

			$i = 0;
			foreach ( $this->form_items as $item ) {
				if ( 'page_type' === $item->get_item_name() ) {
					$item_js =
						'function set_item_visibility(page_type) { ' .
						'  if (page_type===\'static\') { ' .
						'     jQuery(\'[name="page_content"]\').parent().parent().show(); ' .
						'     jQuery(\'[name="page_table_name"]\').parent().parent().hide(); ' .
						'     jQuery(\'[name="page_mode"]\').parent().parent().hide(); ' .
						'     jQuery(\'[name="page_allow_insert"]\').parent().parent().hide(); ' .
						'     jQuery(\'[name="page_allow_delete"]\').parent().parent().hide(); ' .
						'  } else { ' .
						'     jQuery(\'[name="page_table_name"]\').parent().parent().show(); ' .
						'     jQuery(\'[name="page_mode"]\').parent().parent().show(); ' .
						'     jQuery(\'[name="page_allow_insert"]\').parent().parent().show(); ' .
						'     jQuery(\'[name="page_allow_delete"]\').parent().parent().show(); ' .
						'     jQuery(\'[name="page_content"]\').parent().parent().hide(); ' .
						'  } ' .
						'} ' .
						'jQuery(function () { ' .
						'  jQuery(\'[name="page_type"]\').change(function() { ' .
						'    set_item_visibility(jQuery(this).val()); ' .
						'  }); ' .
						'  set_item_visibility(jQuery(\'[name="page_type"]\').val()); ' .
						'});';
					$item->set_item_js( $item_js );
				} elseif ( 'page_content' === $item->get_item_name() ) {
					$posts = get_posts(
						[
							'post_status' => '%',
							'orderby'     => 'ID',
						]
					);

					$lov         = [];
					$lov_options = [];
					// For some reason get_posts always sorts DESC on ID: reverse array.
					$posts_reverse = array_reverse( $posts );
					// Set first element to blank.
					array_push( $lov, '' );
					array_push( $lov_options, '0' );
					foreach ( $posts_reverse as $post ) {
						$post_element = $post->post_title . ' (ID=' . $post->ID . ')';
						array_push( $lov, $post_element );
						array_push( $lov_options, $post->ID );
					}

					$item->set_enum( $lov );
					$item->set_enum_options( $lov_options );
					$this->form_items[ $i ] = new WPDA_Simple_Form_Item_Enum( $item );
				} elseif ( 'page_schema_name' === $item->get_item_name() ) {
					// Prepare listbox for column pub_schema_name
					if ( '' === $item->get_item_value() || null === $item->get_item_value() ) {
						$item->set_item_value( WPDA::get_user_default_scheme() );
					}
					$item->set_enum( $databases );
					$this->form_items[ $i ] = new WPDA_Simple_Form_Item_Enum( $item );
				} elseif ( 'page_table_name' === $item->get_item_name() ) {
					$item->set_enum( $tables );
					$this->form_items[ $i ] = new WPDA_Simple_Form_Item_Enum( $item );
				} elseif ( 'page_role' === $item->get_item_name() ) {
					global $wp_roles;
					$lov         = [];
					$lov_options = [];
					foreach ( $wp_roles->roles as $role => $val ) {
						array_push( $lov_options, $role );
						array_push( $lov, isset( $val['name'] ) ? $val['name'] : $role );
					}
					$item->set_enum( $lov );
					$item->set_enum_options( $lov_options );
					$this->form_items[ $i ] = new WPDA_Simple_Form_Item_Set( $item );
				} elseif ( 'page_setname' === $item->get_item_name() ) {
					global $wpdb;
					$query    = 'select distinct wpda_table_setname from ' .
					            WPDP_Project_Design_Table_Model::get_base_table_name() .
								' order by wpda_table_setname';
					$setnames = $wpdb->get_results( $query, 'ARRAY_A' );
					$lov      = [];
					foreach ( $setnames as $setname ) {
						array_push( $lov, $setname['wpda_table_setname'] );
					}
					if ( 0 === count( $lov ) ) {
						array_push( $lov, 'default' );
					}
					$item->set_enum( $lov );
					$this->form_items[ $i ] = new WPDA_Simple_Form_Item_Enum( $item );
				}
				$i ++;
			}
		}

		/**
		 * Get all db tables and views
		 *
		 * @param string $database Database schema name
		 *
		 * @return array
		 */
		protected function get_all_db_tables( $database ) {
			$tables    = [];
			$db_tables = WPDA_Dictionary_Lists::get_tables( true, $database ); // select all db tables and views
			foreach ( $db_tables as $db_table ) {
				array_push( $tables, $db_table['table_name'] ); // add table or view to array
			}

			return $tables;
		}

		/**
		 * Overwrites method show
		 *
		 * @param bool   $allow_save
		 * @param string $add_param
		 */
		public function show( $allow_save = true, $add_param = '' ) {
			parent::show( $allow_save, $add_param );

			?>
			<script type='text/javascript'>
				var database_tables = new Object();
				<?php
				foreach ( $this->database_tables as $key => $value ) {
					echo "database_tables['$key'] = " . json_encode( $value ) . ";";
				}
				?>

				jQuery(function () {
					jQuery('[name="page_schema_name"]').on('change', function () {
						jQuery('[name="page_table_name"]').empty();
						var tables = database_tables[jQuery(this).val()];
						for ( var i = 0; i < tables.length; i++ ) {
							jQuery('<option/>', {
								value: tables[i],
								html: tables[i]
							}).appendTo('[name="page_table_name"]');
						}
					});
				});
			</script>
			<?php
		}

	}

}