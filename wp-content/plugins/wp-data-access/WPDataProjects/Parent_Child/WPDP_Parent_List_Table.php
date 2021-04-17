<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Parent_Child
 */

namespace WPDataProjects\Parent_Child {

	use WPDataAccess\Connection\WPDADB;
	use WPDataProjects\List_Table\WPDP_List_Table_Lookup;
	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;

	/**
	 * Class WPDP_Parent_List_Table extends WPDP_List_Table_Lookup
	 *
	 * @see WPDP_List_Table_Lookup
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Parent_List_Table extends WPDP_List_Table_Lookup {

		/**
		 * Project info
		 *
		 * @var array
		 */
		protected $project;

		/**
		 * User message
		 *
		 * @var string
		 */
		protected $message_confirm_delete;

		/**
		 * WPDP_Parent_List_Table constructor
		 *
		 * @param array $args
		 */
		public function __construct( $args = [] ) {
			if ( isset( $args['project'] ) ) {
				$this->project = $args['project'];
			} else {
				wp_die( __( 'ERROR: Wrong arguments', 'wp-data-access' ) );
			}

			$args['allow_import'] = 'off';

			parent::__construct( $args );

			$this->message_confirm_delete =
				__( "Delete current item and all its relationships?\\nThis action cannot be undone.\\n\'Cancel\' to stop, \'OK\' to delete.", 'wp-data-access' );

			if ( isset( $args['where_clause'] ) && '' !== $args['where_clause'] ) {
				$this->where = $args['where_clause'];
			}

			if ( isset( $args['orderby_clause'] ) && '' !== $args['orderby_clause'] ) {
				$this->orderby = $args['orderby_clause'];
			}
		}

		/**
		 * Overwrites method bind_action_buttons to set message_confirm_delete
		 */
		protected function bind_action_buttons() {
			?>
			<script type='text/javascript'>
				jQuery(function () {
					jQuery("#doaction").click(function () {
						return wpdp_action_button("<?php echo $this->message_confirm_delete; ?>");
					});
					jQuery("#doaction2").click(function () {
						return wpdp_action_button("<?php echo $this->message_confirm_delete; ?>");
					});
				});
			</script>
			<?php
		}

		/**
		 * Overwrites method column_default_add_action to set mode
		 *
		 * @param array  $item
		 * @param string $column_name
		 * @param array  $actions
		 */
		protected function column_default_add_action( $item, $column_name, &$actions ) {
			if ( 'view' === $this->project->get_mode() ) {
				unset( $actions['edit'] );
				unset( $actions['delete'] );
				?>
				<script type='text/javascript'>
					jQuery("#view_form_" + <?php echo( self::$list_number - 1 ) ?>).append('<input type="hidden" name="mode" value="view">');
					jQuery("#edit_form_" + <?php echo( self::$list_number - 1 ) ?>).remove();
					jQuery("#delete_form_" + <?php echo( self::$list_number - 1 ) ?>).remove();
				</script>
				<?php
			} else {
				?>
				<script type='text/javascript'>
					jQuery("#view_form_" + <?php echo( self::$list_number - 1 ) ?>).append('<input type="hidden" name="mode" value="view">');
					jQuery("#edit_form_" + <?php echo( self::$list_number - 1 ) ?>).append('<input type="hidden" name="mode" value="edit">');
					jQuery("#delete_form_" + <?php echo( self::$list_number - 1 ) ?>).append('<input type="hidden" name="mode" value="edit">');
				</script>
				<?php
				if ( 'off' !== $this->allow_delete ) {
					$title             = __( 'Delete parent row and its child rows', 'wp-data-access' );
					$actions['delete'] = sprintf(
						'
					    <a  href="javascript:void(0)" class="wpda_tooltip"
					    	title="' . $title . '"
					        onclick="if (confirm(\'%s\')) jQuery(\'%s\').submit()"
					        >
							<span style="white-space:nowrap">
								<span class="material-icons wpda_icon_on_button">delete</span>
								%s
					        </span>
                        </a>
                    ',
						$this->message_confirm_delete,
						'#delete_form_' . strval( self::$list_number - 1 ),
						__( 'Delete', 'wp-data-access' )
					);
				}
			}
		}

		/**
		 * Overwrite method
		 *
		 * @param array $item
		 *
		 * @return string
		 */
		protected function add_parent_args_as_string( $item ) {
			$parent_args = '';
			if ( isset( $this->project ) ) {
				$parents = $this->project->get_parent();
				if ( isset( $parents['key'] ) && is_array( $parents['key'] ) ) {
					foreach ( $parents['key'] as $key ) {
						$value = null;
						if ( isset( $item[ $key ] ) ) {
							$value = $item[ $key ];
						}
						$parent_args .= "<input type='hidden' name='WPDA_PARENT_KEY*" . esc_attr( $key ) . "' value='" . esc_attr( $value ) . "' >";
					}
				}
			}
			return $parent_args;
		}

		/**
		 * Overwrite method get_bulk_actions to add action 'Delete Permanently'
		 *
		 * @return array
		 */
		public function get_bulk_actions() {
			if ( 'view' === $this->project->get_mode() ) {
				return '';
			}

			if ( ! $this->bulk_actions_enabled ) {
				// Bulk actions disabled.
				return '';
			}

			if ( empty( $this->wpda_list_columns->get_table_primary_key() ) ) {
				// Tables has no primary key: no bulk actions allowed!
				// Primary key is neccesary to ensure uniqueness.
				return '';
			}

			if ( 'off' !== $this->allow_delete ) {
				$actions = parent::get_bulk_actions();
			} else {
				$actions = [];
			}

			return $actions;
		}

		/**
		 * Overwrites method delete_row to delete child rows on parent delete
		 *
		 * @param string $where
		 *
		 * @return mixed
		 */
		public function delete_row( $where ) {
			foreach ( $this->project->get_children() as $child ) {
				if ( isset( $child['relation_nm'] ) ) {
					$table_name        = $child['relation_nm']['child_table'];
					$row_to_be_deleted = [];
					$i                 = 0;
					foreach ( $where as $key ) {
						$row_to_be_deleted[ $child['relation_nm']['child_table_where'][ $i ] ] = $key;
						$i ++;
					}
					$this->delete_row_relationship( $table_name, $row_to_be_deleted );
				} elseif ( isset( $child['relation_1n'] ) ) {
					$table_name        = $child['table_name'];
					$row_to_be_deleted = [];
					$i                 = 0;
					foreach ( $where as $key ) {
						$row_to_be_deleted[ $child['relation_1n']['child_key'][ $i ] ] = $key;
						$i ++;
					}
					$this->delete_row_relationship( $table_name, $row_to_be_deleted );
				}
			}

			return parent::delete_row( $where );
		}

		/**
		 * Delete relationship
		 *
		 * @param string $table_name Database table name
		 * @param string $where SQL where clause
		 *
		 * @return mixed Return value of SQL delete
		 */
		public function delete_row_relationship( $table_name, $where ) {
			$wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( null === $wpdadb ) {
				wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
			}

			$cannot_delete_from_view = new WPDA_Dictionary_Exist( $this->schema_name, $table_name );
			if ( $cannot_delete_from_view->is_view() ) {
				// Cannot delete rows from view
				return;
			}

			return $wpdadb->delete( $table_name, $where ); // db call ok; no-cache ok.
		}

		/**
		 * Overwrites method add_header_button to add arguments
		 *
		 * @param string $add_param
		 */
		protected function add_header_button( $add_param = '' ) {
			if ( 'edit' === $this->project->get_mode() && 'off' !== $this->allow_insert ) {
				if ( is_admin() ) {
					$url = "?page={$this->page}";
				} else {
					$url = '';
				}
				?>
				<form
						method="post"
						action="<?php echo esc_attr( $url ); ?>"
						style="display: inline-block; vertical-align: unset;"
				>
					<div>
						<input type="hidden" name="action" value="new">
						<input type="hidden" name="mode" value="edit">
						<input type="hidden" name="table_name" value="<?php echo esc_attr( $this->table_name ); ?>">
						<button type="submit" class="page-title-action">
							<span class="material-icons wpda_icon_on_button">add_circle</span>
							<?php echo __( 'Add New', 'wp-data-access' ); ?>
						</button>
					</div>
				</form>
				<?php
				if ( null !== $this->wpda_import ) {
					$this->wpda_import->add_button( __( 'Import', 'wp-data-access' ) );
				}
			}
		}

	}

}
