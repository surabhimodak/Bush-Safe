<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Parent_Child
 */

namespace WPDataProjects\Parent_Child {

	use WPDataAccess\Connection\WPDADB;
	use WPDataAccess\WPDA;
	use WPDataProjects\List_Table\WPDP_List_Table_Lookup;

	/**
	 * Class WPDP_Child_List_Table extends WPDP_List_Table_Lookup
	 *
	 * @see WPDP_List_Table_Lookup
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Child_List_Table extends WPDP_List_Table_Lookup {

		/**
		 * Possible values: edit and view
		 *
		 * @var string
		 */
		protected $mode;

		/**
		 * Parent key values
		 *
		 * @var array
		 */
		protected $parent;

		/**
		 * Child relationships (for actual parent)
		 *
		 * @var array
		 */
		protected $child;

		/**
		 * Tab value of actual child
		 *
		 * @var string
		 */
		protected $child_tab = '';

		/**
		 * Flexible usage of IN and NOT IN :-o
		 *
		 * @var string
		 */
		protected $where_in = 'in'; // Just to save code! (opposite using in WPDP_Child_List_Table_Selection: not in)

		/**
		 * Indicates that this is a list table for a child
		 *
		 * Parent and stand-alone list tables do not have this property.
		 *
		 * @var bool
		 */
		public $is_child = true;

		/**
		 * Overwrites WPDP_Child_List_Table constructor
		 *
		 * @param array $args
		 */
		public function __construct( $args = [] ) {
			if ( isset( $args['mode'] ) ) {
				$this->mode = $args['mode'];
			} else {
				wp_die( __( 'ERROR: Wrong arguments [missing mode]', 'wp-data-access' ) );
			}

			if ( isset( $args['parent'] ) ) {
				$this->parent = $args['parent'];
			} else {
				wp_die( __( 'ERROR: Wrong arguments [missing parent]', 'wp-data-access' ) );
			}

			if ( isset( $args['child'] ) ) {
				$this->child = $args['child'];
			} else {
				wp_die( __( 'ERROR: Wrong arguments [missing child]', 'wp-data-access' ) );
			}

			$args['child_request'] = true;
			$args['allow_insert']  = 'off';

			if ( 'view' === $this->mode ) {
				$args['allow_delete']  = 'off';
			} else {
				$args['allow_delete']  = 'on';
			}

			if ( 'edit' === $this->mode ) {
				$args['bulk_actions_enabled'] = true;
			} else {
				$args['bulk_actions_enabled'] = false;
			}

			if ( isset( $_REQUEST['child_tab'] ) ) {
				$this->child_tab = sanitize_text_field( wp_unslash( $_REQUEST['child_tab'] ) ); // input var okay.
			}

			$this->page_number_item_name = 'child_page_number';

			parent::__construct( $args );
		}

		/**
		 * Overwrite method get_search_value to add parent arguments
		 *
		 * @param null $param_cookie_name
		 *
		 * @return string
		 */
		protected function get_search_value( $param_cookie_name = null ) {
			$parent_key_values = '';
			foreach ( $this->parent['parent_key_value'] as $parent_key_value ) {
				$parent_key_values .= esc_attr( $parent_key_value );
			}

			return parent::get_search_value(
				$this->page . '_search_child_' . $parent_key_values .
				str_replace( '.', '_', $this->table_name )
			);
		}

		/**
		 * Overwrites method search_box to add parent arguments
		 *
		 * @param string $text
		 * @param string $input_id
		 */
		public function search_box( $text, $input_id ) {
			parent::search_box( $text, $input_id );

			$this->add_parent_args();
		}

		/**
		 * Add parent arguments as hidden items
		 */
		protected function add_parent_args() {
			foreach ( $this->parent['parent_key'] as $parent_key ) {
				?>
				<input type='hidden'
					   name='WPDA_PARENT_KEY*<?php echo( esc_attr( $parent_key ) ); ?>'
					   value='<?php echo( esc_attr( $this->parent['parent_key_value'][ $parent_key ] ) ); ?>'
				/>
				<?php
			}
			?>
			<input type="hidden" name="mode" value="<?php echo esc_attr( $this->mode ); ?>">
			<input type='hidden' name='child_request' value='TRUE'/>
			<input type='hidden' name='child_tab' value='<?php echo esc_attr( $this->child_tab ); ?>'/>
			<?php
			if ( ! $this->has_items() ) {
				?>
				<input type='hidden' name='action' value='-1'/>
				<input type='hidden' name='action2' value='-1'/>
				<input type='hidden' name='paged' value='1'/>
				<?php
			}
		}

		/**
		 * Add parent arguments as string
		 */
		protected function add_parent_args_as_string( $item ) {
			$parent_args = '';

			foreach ( $this->parent['parent_key'] as $parent_key ) {
				$p_key       = esc_attr( $parent_key );
				$p_val       = esc_attr( $this->parent['parent_key_value'][ $parent_key ] );
				$parent_args .= "<input type='hidden' name='WPDA_PARENT_KEY*$p_key' value='$p_val'/>";
			}

			$mode      = esc_attr( $this->mode );
			$child_tab = esc_attr( $this->child_tab );

			$parent_args .= "<input type='hidden' name='mode' value='$mode'>";
			$parent_args .= "<input type='hidden' name='child_request' value='TRUE'/>";
			$parent_args .= "<input type='hidden' name='child_tab' value='$child_tab'/>";

			if ( ! $this->has_items() ) {
				$parent_args .= "<input type='hidden' name='action' value='-1'/>";
				$parent_args .= "<input type='hidden' name='action2' value='-1'/>";
				$parent_args .= "<input type='hidden' name='paged' value='1'/>";
			}

			return $parent_args;
		}

		/**
		 * Constructs the where clause depending on the relationship type
		 *
		 * Adds a subquery for n:m relationships. Adds parent key value comparison for 1:n relationships.
		 */
		protected function construct_where_clause() {
			$wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( null === $wpdadb ) {
				if ( is_admin() ) {
					wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				} else {
					die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				}
			}

			if (
				isset( $this->child['default_where'] ) &&
				null !== $this->child['default_where'] &&
				'' !== trim( $this->child['default_where'] )
			) {
				$default_where = " and ( {$this->child['default_where']} ) ";
				$default_where = WPDA::substitute_environment_vars( $default_where );
			} else {
				$default_where = '';
			}

			if ( isset( $this->child['relation_nm'] ) ) {
				$child_table        = $this->child['relation_nm']['child_table'];
				$parent_key         = $this->child['relation_nm']['parent_key'];
				$child_table_select = $this->child['relation_nm']['child_table_select'];
				$child_table_where  = $this->child['relation_nm']['child_table_where'];
				$data_type          = $this->child['relation_nm']['data_type'];

				$parent_key_column_names = '';
				$select_column_names     = '';
				$index                   = 0;

				foreach ( $parent_key as $key ) {
					if ( $key === reset( $parent_key ) ) {
						$parent_key_column_names .= "(`$key`";
						$select_column_names     .= '`' . $child_table_select[ $index ] . '`';
					} else {
						$parent_key_column_names .= ",`$key`";
						$select_column_names     .= ', `' . $child_table_select[ $index ] . '`';
					}
					$index ++;
				}
				$parent_key_column_names .= ')';

				$index = 0;
				$where = '';

				foreach ( $child_table_where as $child_where ) {
					if ( $child_where === reset( $child_table_where ) ) {
						$and = '';
					} else {
						$and = ' and ';
					}
					if ( 'number' === strtolower( $data_type[ $index ] ) ) {
						$where .=
							$wpdadb->prepare(
								" $and `$child_where` = %f ",
								$this->parent['parent_key_value'][ $this->parent['parent_key'][ $index ] ]
							); // WPCS: unprepared SQL OK.
					} else {
						$where .=
							$wpdadb->prepare(
								" $and `$child_where` = %s ",
								$this->parent['parent_key_value'][ $this->parent['parent_key'][ $index ] ]
							); // WPCS: unprepared SQL OK.
					}
					$index ++;
				}

				if ( '' === $this->schema_name ) {
					$schema_table_name = "`$child_table`";
				} else {
					$schema_table_name = "`{$wpdadb->dbname}`.`$child_table`";
				}

				$this->where =
					" where ($parent_key_column_names {$this->where_in} " .
					" (select $select_column_names from $schema_table_name where $where)) "; // WPCS: unprepared SQL OK.
			} elseif ( isset( $this->child['relation_1n'] ) ) {
				$child_key  = $this->child['relation_1n']['child_key'];
				$data_type  = $this->child['relation_1n']['data_type'];

				if (
					isset( $this->child['relation_1n']['parent_key'] ) &&
					is_array( $this->child['relation_1n']['parent_key'] )
				) {
					$parent_key = $this->child['relation_1n']['parent_key'];
				} else {
					$parent_key = null;
				}

				$index = 0;
				$where = '';

				foreach ( $child_key as $key ) {
					if ( $key === reset( $child_key ) ) {
						$and = '';
					} else {
						$and = ' and ';
					}

					if ( null === $parent_key ) {
						$parent_key_value = $this->parent['parent_key_value'][ $this->parent['parent_key'][ $index ] ];
					} else {
						if ( $parent_key == $this->parent['parent_key'] ) {
							$parent_key_value = $this->parent['parent_key_value'][ $this->parent['parent_key'][ $index ] ];
						} else {
							if ( isset( $_REQUEST[ 'WPDA_PARENT_KEY*' . $parent_key[ $index ] ] ) ) {
								$parent_key_value = sanitize_text_field( wp_unslash( $_REQUEST[ 'WPDA_PARENT_KEY*' . $parent_key[ $index ] ] ) ); // input var okay.
							} elseif ( isset( $_REQUEST[ $parent_key[ $index ] ] ) ) {
								$parent_key_value = sanitize_text_field( wp_unslash( $_REQUEST[ $parent_key[ $index ] ] ) ); // input var okay.
							} else  {
								wp_die( '<p style="clear: both; padding: 10px;">' . __( 'ERROR: No value for parent key found', 'wp-data-access' ) . '</p>' );
							}
						}
					}

					if ( isset( $data_type[ $index ] ) && 'number' === strtolower( $data_type[ $index ] ) ) {
						$where .= $wpdadb->prepare(
							" $and `$key` = %f ",
							$parent_key_value
						); // WPCS: unprepared SQL OK.
					} else {
						$where .= $wpdadb->prepare(
							" $and `$key` = %s ",
							$parent_key_value
						); // WPCS: unprepared SQL OK.
					}

					$index++;
				}

				$this->where = " where ($where) "; // WPCS: unprepared SQL OK.
			}

			// Add default where
			$this->where .= $default_where;

			parent::construct_where_clause();
		}

		protected function get_order_by() {
			$orderby = parent::get_order_by();
			if ( '' !== $orderby ) {
				return $orderby;
			}

			if (
				isset( $this->child['default_orderby'] ) &&
				null !== $this->child['default_orderby'] &&
				'' !== trim( $this->child['default_orderby'] )
			) {
				$default_orderby = " order by {$this->child['default_orderby']} ";
			} else {
				$default_orderby = '';
			}

			// Add default order by
			return $default_orderby; // WPCS: unprepared SQL OK.
		}

		/**
		 * Overwrites method column_default_add_action
		 *
		 * Adds parent arguments
		 *
		 * @param array  $item
		 * @param string $column_name
		 * @param array  $actions
		 */
		protected function column_default_add_action( $item, $column_name, &$actions ) {
			$form_id       = '_' . self::$list_number ++;
			$wp_nonce_keys = '';
			foreach ( $this->wpda_list_columns->get_table_primary_key() as $key ) {
				$wp_nonce_keys .= '-' . esc_attr( $item[ $key ] );
			}

			// Prepare argument schema name.
			if ( '' === $this->schema_name ) {
				$schema_name = '';
			} else {
				$schema_name = "&wpdaschema_name={$this->schema_name}";
			}

			// Prepare argument page.
			$page = esc_attr( $this->page );

			// Prepare argument table name.
			$table_name = esc_attr( $this->table_name );

			$wp_nonce_action = "wpda-delete-{$this->table_name}$wp_nonce_keys";
			$wp_nonce        = wp_create_nonce( $wp_nonce_action );

			$delete_form =
				"<form" .
				" id='delete_form$form_id'" .
				" action='?page=$page$schema_name&table_name=$table_name'" .
				" method='post'>" .
				$this->get_key_input_fields( $item ) .
				$this->add_parent_args_as_string( $item ) .
				"<input type='hidden' name='action' value='delete' />" .
				"<input type='hidden' name='_wpnonce' value='$wp_nonce'>" .
				$this->page_number_item .
				"</form>";
			?>
			<script type='text/javascript'>
				jQuery("#wpda_invisible_container").append("<?php echo $delete_form; ?>");
			</script>
			<?php
			if ( isset( $this->child['relation_nm'] ) ) {
				$link_label = __( 'Delete Relationship', 'wp-data-access' );
				$link_title = __( 'Delete child row (this only affects the relationship table)', 'wp-data-access' );
			} else {
				$link_label = __( 'Delete', 'wp-data-access' );
				$link_title = __( 'Delete child row', 'wp-data-access' );
			}
			$warning = __("You are about to permanently delete these items from your site.\\nThis action cannot be undone.\\n\\'Cancel\\' to stop, \\'OK\\' to delete.",'wp-data-access');
			$actions['delete'] = sprintf(
				'<a href="javascript:void(0)" 
					class="delete wpda_tooltip"  
					title="%s"
					onclick="if (confirm(\'%s\')) jQuery(\'#%s\').submit()">
						<span style="white-space:nowrap">
							<span class="material-icons wpda_icon_on_button">delete</span>
							%s
						</span>
				</a>
				',
				$link_title,
				$warning,
				"delete_form$form_id",
				$link_label
			);
		}

		/**
		 * Overwrites method get_bulk_actions
		 *
		 * Adds 'Delete Relationship' action for n:m relationships and 'Delete' action for 1:n relationships to bulk
		 * listbox.
		 *
		 * @return array|string
		 */
		public function get_bulk_actions() {
			if ( ! $this->bulk_actions_enabled ) {
				// Bulk actions disabled.
				return '';
			}

			if ( empty( $this->wpda_list_columns->get_table_primary_key() ) ) {
				// Tables has no primary key: no bulk actions allowed!
				// Primary key is neccesary to ensure uniqueness.
				return '';
			}

			$actions = [];

			if ( isset( $this->child['relation_nm'] ) ) {
				$actions['bulk-delete'] = __( 'Delete Relationship', 'wp-data-access' );
			} else {
				$actions['bulk-delete'] = __( 'Delete', 'wp-data-access' );
			}

			return $actions;
		}

		/**
		 * Overwrite method delete_row
		 *
		 * Adds parent arguments
		 *
		 * @param string $where
		 *
		 * @return mixed
		 */
		public function delete_row( $where ) {
			// Expand named array with parent key for delete operation.
			$next_row_to_be_deleted = [];
			$i                      = 0;
			foreach ( $where as $key => $value ) {
				if ( isset( $this->child['relation_nm'] ) ) {
					$next_row_to_be_deleted[ $this->child['relation_nm']['child_table_select'][ $i ] ] = $value;
					$next_row_to_be_deleted[ $this->child['relation_nm']['child_table_where'][ $i ] ]  =
						$this->parent['parent_key_value'][ $this->parent['parent_key'][ $i ] ];
				} else {
					$next_row_to_be_deleted[ $key ] = $value;
				}
				$i ++;
			}

			if ( isset( $this->child['relation_nm'] ) ) {
				$table_name = $this->child['relation_nm']['child_table'];
			} else {
				$table_name = $this->table_name;
			}

			$wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( null === $wpdadb ) {
				if ( is_admin() ) {
					wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				} else {
					die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				}
			}

			return $wpdadb->delete( $table_name, $next_row_to_be_deleted ); // db call ok; no-cache ok.
		}

	}

}