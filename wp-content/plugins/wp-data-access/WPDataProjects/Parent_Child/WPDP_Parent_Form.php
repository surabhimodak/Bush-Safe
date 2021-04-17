<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Parent_Child
 */

namespace WPDataProjects\Parent_Child {

	use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;
	use WPDataProjects\Data_Dictionary\WPDP_List_Columns_Cache;
	use WPDataProjects\Simple_Form\WPDP_Simple_Form;

	/**
	 * Class WPDP_Parent_Form extends WPDP_Simple_Form
	 *
	 * This class presumes the existence of (a) child relationship(s) (1:n or n:m). While the parent is maintained in
	 * the top of the page, child relationship(s) are maintained below the parent. If multiple child relationships are
	 * found, the user can switch between them via tabs.
	 *
	 * @see WPDP_Simple_Form
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Parent_Form extends WPDP_Simple_Form {

		/**
		 * Possible values: edit and view
		 *
		 * @var string
		 */
		protected $mode;

		/**
		 * Parent key(s) column names
		 *
		 * @var array
		 */
		protected $parent_key = [];
		/**
		 * Parent key(s) values
		 *
		 * @var array
		 */
		protected $parent_key_value = [];

		/**
		 * Child relationships
		 *
		 * @var array
		 */
		protected $children = [];

		/**
		 * Tabs (1 for every child)
		 *
		 * @var array
		 */
		protected $tabs = [];

		/**
		 * Current tab
		 *
		 * @var string
		 */
		protected $current_tab = null;

		/**
		 * Requested child action
		 *
		 * @var string
		 */
		protected $child_action;

		/**
		 * Possible values: TRUE and null
		 *
		 * TRUE = request is a child request
		 * All other values (including null) = request is a parent request
		 *
		 * @var mixed
		 */
		protected $child_request;

		/**
		 * Relationships found for actual parent
		 *
		 * @var array
		 */
		protected $relations;

		/**
		 * Class used to instantiate edit form
		 *
		 * @var mixed|string
		 */
		protected $edit_form_class = 'WPDataProjects\\Parent_Child\\WPDP_Child_Form';

		/**
		 * Class used to instantiate list table
		 *
		 * @var mixed|string
		 */
		protected $list_table_class = 'WPDataProjects\\Parent_Child\\WPDP_Child_List_Table';

		/**
		 * Action value during post (saved because it might change during request processing)
		 *
		 * @var string
		 */
		protected $action_posted;

		/**
		 * Overwrites WPDP_Parent_Form constructor
		 *
		 * @param string $schema_name Database schema name
		 * @param string $table_name Database table name
		 * @param object $wpda_list_columns Handle to WPDP_List_Columns object
		 * @param array  $args Arguments
		 * @param array  $relationship Relationships
		 */
		public function __construct(
			$schema_name,
			$table_name,
			&$wpda_list_columns,
			$args = [],
			$relationship = []
		) {
			if ( isset( $args['mode'] ) ) {
				$this->mode = $args['mode'];
			} else {
				wp_die( __( 'ERROR: Wrong arguments [missing mode]', 'wp-data-access' ) );
			}

			if ( isset( $args['child_request'] ) ) {
				$this->child_request = $args['child_request'];
			} else {
				wp_die( __( 'ERROR: Wrong arguments [missing child_request]', 'wp-data-access' ) );
			}

			$action = null;
			if ( isset( $_REQUEST['action'] ) ) {
				// Possible values: "new", "edit" and "view".
				$action              = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // input var okay.
				$this->action_posted = $action;
			} elseif ( isset( $args['action'] ) ) {
				$action              = $args['action'];
				$this->action_posted = $action;
			}

			$this->relations  = $relationship;

			$args['back_to_list_text'] = __( 'Parent List', 'wp-data-access' );

			parent::__construct( $schema_name, $table_name, $wpda_list_columns, $args );

			if ( 'new' !== $action || $this->child_request ) {
				if ( isset( $this->relations['parent']['key'] ) && is_array( $this->relations['parent']['key'] ) ) {
					foreach ( $this->relations['parent']['key'] as $key ) {
						if ( isset( $_REQUEST[ 'WPDA_PARENT_KEY*' . $key ] ) ) {
							array_push( $this->parent_key, $key );
							$this->parent_key_value[ $key ] = sanitize_text_field( wp_unslash( $_REQUEST[ 'WPDA_PARENT_KEY*' . $key ] ) ); // input var okay.
						} elseif ( isset( $_REQUEST[ $key ] ) ) {
							array_push( $this->parent_key, $key );
							$this->parent_key_value[ $key ] = sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ); // input var okay.
						}

						if ( is_array( $relationship['children'] ) ) {
							$this->children = $relationship['children'];
						}
					}
				}
			}

			$this->set_child_action_member();
			if ( $this->child_request ) {
				if ( 'list' === $this->action || '-1' === $this->action ) {
					$this->action = 'edit';
				} else {
					$this->action = 'view';
				}
			}
			if ( 'view' === $this->mode ) {
				$this->action = 'view';
			}

			foreach ( $this->children as $child ) {
				if ( null === $this->current_tab ) {
					$this->current_tab = $child['table_name'];
				}

				$this->tabs[ $child['table_name'] ] = $child['tab_label'];

				if ( isset( $child['relation_nm'] ) ) {
					$this->relations[ $child['table_name'] ]['relation_nm'] = $child['relation_nm'];
				} elseif ( isset( $child['relation_1n'] ) ) {
					$this->relations[ $child['table_name'] ]['relation_1n'] = $child['relation_1n'];
				}
			}

			if ( isset( $_REQUEST['child_tab'] ) ) {
				if ( isset( $this->tabs[ $_REQUEST['child_tab'] ] ) ) {
					$this->current_tab = sanitize_text_field( wp_unslash( $_REQUEST['child_tab'] ) ); // input var okay.
				}
			}

			if ( isset( $args['edit_form_class'] ) ) {
				$this->edit_form_class = $args['edit_form_class'];
			}

			if ( isset( $args['list_form_class'] ) ) {
				$this->list_table_class = $args['list_form_class'];
			}
		}

		/**
		 * Overwrite method to add show less/more functionality
		 *
		 * @param bool $set_back_form_values
		 */
		protected function prepare_items( $set_back_form_values = false ) {
			parent::prepare_items( $set_back_form_values );

			foreach ( $this->wpda_list_columns->get_table_columns() as $columns ) {
				if ( isset ( $columns['less'] ) && ! $columns['less'] ) {
					foreach ( $this->form_items as $item ) {
						if ( $item->get_item_name() === $columns['column_name'] ) {
							$item->set_item_class( 'row-show-less-more' );
						}
					}
				}
			}
		}

		/**
		 * Determine and set action to be performed on child
		 */
		protected function set_child_action_member() {
			if ( $this->child_request ) {
				if ( '-1' === $this->action ) {
					$this->child_action = $this->action2;
				} else {
					$this->child_action = $this->action;
				}
			} else {
				$this->child_action = 'list';
			}
		}

		/**
		 * Overwrite method show
		 *
		 * @param bool   $allow_save
		 * @param string $add_param
		 *
		 * @see WPDP_Simple_Form::show()
		 *
		 */
		public function show( $allow_save = true, $add_param = '' ) {
			if ( $this->child_request ) {
				parent::show( false, $add_param );
			} else {
				parent::show( $allow_save, $add_param );
			}
			$this->add_tabs();
			foreach ( $this->children as $child ) {
				if ( $child['table_name'] === $this->current_tab ) {
					if ( 'edit' === $this->child_action || 'new' === $this->child_action || 'view' === $this->child_action ) {
						$this->show_child_form( $child['table_name'], $child );
					} else {
						$this->show_child_list_table( $child['table_name'], $child );
					}
				}
			}
		}

		/**
		 * Show child form
		 *
		 * @param string $child_table_name Database table name
		 * @param array  $child Child info
		 */
		protected function show_child_form( $child_table_name, $child ) {
			if ( 'edit' === $this->mode && ( 'new' === $this->child_action || 'edit' === $this->child_action ) ) {
				$this->button_add_new( $child, $child_table_name );
				echo '<div style="clear:both;"></div>';
			}
			$wpda_list_columns = WPDP_List_Columns_Cache::get_list_columns( $this->schema_name, $child_table_name, 'tableform', $this->setname );
			$wpda_child_form   = new $this->edit_form_class(
				$this->schema_name,
				$child_table_name,
				$wpda_list_columns,
				[
					'show_title'        => false,
					'show_back_button'  => true,
					'mode'              => $this->mode,
					'parent'            => [
						'parent_key'       => $this->parent_key,
						'parent_key_value' => $this->parent_key_value,
					],
					'child'             => $child,
					'back_to_list_text' => __( 'Child List', 'wp-data-access' ),
				]
			);
			$wpda_child_form->show();
		}

		/**
		 * Show child list table
		 *
		 * @param string $child_table_name Database table name
		 * @param array  $child Child info
		 */
		protected function show_child_list_table( $child_table_name, $child ) {
			$is_list_table_selection = isset( $_REQUEST['list_table_selection'] );
			if ( 'edit' === $this->mode ) {
				$this->button_add_new( $child, $child_table_name );
			}
			if ( $is_list_table_selection || 'add' === $this->child_action || 'bulk-add' === $this->child_action ) {
				$list_table_class = 'WPDataProjects\\Parent_Child\\WPDP_Child_List_Table_Selection';
			} else {
				$list_table_class = $this->list_table_class;
			}
			$wpda_list_columns = WPDP_List_Columns_Cache::get_list_columns( $this->schema_name, $child_table_name, 'listtable', $this->setname );
			$wpda_list_table   = new $list_table_class(
				[
					'wpdaschema_name'   => $this->schema_name,
					'table_name'        => $child_table_name,
					'wpda_list_columns' => $wpda_list_columns,
					'mode'              => $this->mode,
					'title'             => '',
					'subtitle'          => '',
					'allow_import'      => 'off',
					'parent'            => [
						'parent_key'       => $this->parent_key,
						'parent_key_value' => $this->parent_key_value,
					],
					'child'             => $child,
				]
			);
			$wpda_list_table->show();
		}

		/**
		 * Add a tab for every child
		 */
		protected function add_tabs() {
			if ( sizeof( $this->children ) > 0 ) {
			?>
			<h2 class="nav-tab-wrapper">
				<?php
				$requested_page_number = 1;
				if ( isset( $_REQUEST['page_number'] ) ) {
					$requested_page_number = sanitize_text_field( wp_unslash( $_REQUEST['page_number'] ) ); // input var okay.
				}
				foreach ( $this->tabs as $tab => $name ) {
					$class        = ( $tab === $this->current_tab ) ? ' nav-tab-active' : '';
					$id_from_name = preg_replace( '/[^a-zA-Z0-9]/', '', $name );

					if ( is_admin() ) {
						$url = "?page={$this->page}&table_name={$this->table_name}";
					} else {
						$url = "?table_name={$this->table_name}";
					}
					global $wpdb;
					if ( '' !== $this->schema_name && $wpdb->dbname !== $this->schema_name ) {
						$url .= "&wpdaschema_name={$this->schema_name}";
					}

					?>
					<form action="<?php echo esc_attr( $url ); ?>"
						  method="post"
						  id="form_tab_<?php echo esc_attr( $id_from_name ); ?>"
					>
						<a class="nav-tab<?php echo esc_attr( $class ); ?>"
						   href="javascript:void(0)"
						   onclick="jQuery('#form_tab_<?php echo esc_attr( $id_from_name ); ?>').submit()"
						>
							<?php echo esc_attr( $name ); ?>
							<?php $this->add_parent_keys(); ?>
							<input type="hidden" name="action" value="list">
							<input type="hidden" name="mode" value="<?php echo esc_attr( $this->mode ); ?>">
							<input type='hidden' name='child_request' value='TRUE'/>
							<input type="hidden" name="child_tab" value="<?php echo esc_attr( $tab ); ?>">
							<input type='hidden' name='page_number'
								   value="<?php echo esc_attr( $requested_page_number ); ?>">
						</a>
					</form>
					<?php
				}
				?>
			</h2>
			<?php
			}
		}

		/**
		 * Add parent args to child request
		 */
		protected function add_parent_args() {
			$child_tab = '';
			if ( isset( $_REQUEST['child_tab'] ) ) {
				$child_tab = sanitize_text_field( wp_unslash( $_REQUEST['child_tab'] ) ); // input var okay.
			} ?>
			<input type='hidden' name='child_tab' value='<?php echo esc_attr( $child_tab ); ?>'/>
			<?php
		}

		/**
		 * Overwrites method get_url_arguments
		 */
		protected function get_url_arguments() {
			// Default bahaviour.
			parent::get_url_arguments();

			// When we are coming from a child we'll need to get our parent key
			if ( isset( $this->relations['parent']['key'] ) && is_array( $this->relations['parent']['key'] ) ) {
				foreach ( $this->relations['parent']['key'] as $key ) {
					if ( isset( $_REQUEST[ 'WPDA_PARENT_KEY*' . $key ] ) ) {
						$this->form_items_new_values[ $key ] = sanitize_text_field( wp_unslash( $_REQUEST[ 'WPDA_PARENT_KEY*' . $key ] ) ); // input var okay.
					}
				}
			}
		}

		/**
		 * Add button new to page top
		 *
		 * @param array  $child Child info
		 * @param string $child_table_name Database table name
		 */
		protected function button_add_new( $child, $child_table_name ) {
			// Check if table has primary key. If not, disable adding a new record.
			$check_pk = WPDA_List_Columns_Cache::get_list_columns( $this->schema_name, $child_table_name );
			$title    = "Add new row";

			if ( is_admin() ) {
				$url = "?page={$this->page}&table_name={$this->table_name}";
			} else {
				$url = "?table_name={$this->table_name}";
			}
			global $wpdb;
			if ( '' !== $this->schema_name && $wpdb->dbname !== $this->schema_name ) {
				$url .= "&wpdaschema_name={$this->schema_name}";
			}

			if ( ! empty( $check_pk->get_table_primary_key() ) ) {
				?>
				<form
						method="post"
						action="<?php echo esc_attr( $url ); ?>"
						style="padding-top:15px;padding-left:5px;float:left;"
				>
					<?php $this->add_parent_keys( 'WPDA_PARENT_KEY*' ); ?>
					<?php echo $this->page_number_item; ?>
					<input type="hidden" name="mode" value="edit">
					<input type="hidden" name="child_request" value="TRUE">
					<input type="hidden" name="child_tab" value="<?php echo esc_attr( $child_table_name ); ?>">
					<input type="hidden" name="action" value="new">
					<button type="submit" class="button wpda_tooltip" title="<?php echo $title; ?>">
						<span class="material-icons wpda_icon_on_button">add_circle</span>
						<?php echo __( 'Add New', 'wp-data-access' ); ?>
					</button>
				</form>
				<?php
			}
			if ( isset( $child['relation_nm'] ) ) {
				?>
				<form
						method="post"
						action="<?php echo esc_attr( $url ); ?>"
						style="padding-top:15px;padding-left:5px;float:left;"
				>
					<?php $this->add_parent_keys( 'WPDA_PARENT_KEY*' ); ?>
					<?php echo $this->page_number_item; ?>
					<input type="hidden" name="mode" value="edit">
					<input type="hidden" name="child_request" value="TRUE">
					<input type="hidden" name="child_tab" value="<?php echo esc_attr( $child_table_name ); ?>">
					<input type="hidden" name="action" value="add">
					<button type="submit" class="button wpda_tooltip"
							title="Add existing row"
					>
						<span class="material-icons wpda_icon_on_button">add</span>
						<?php echo __( 'Add Existing', 'wp-data-access' ); ?>
					</button>
				</form>
				<?php
			}
		}

		/**
		 * Add parent keys as hidden items
		 *
		 * @param string $name_prefix Added prefix to be flexible
		 */
		protected function add_parent_keys( $name_prefix = '' ) {
			foreach ( $this->parent_key as $parent_key ) {
				?>
				<input type="hidden"
					   name="<?php echo esc_attr( $name_prefix ) . esc_attr( $parent_key ); ?>"
					   value="<?php echo esc_attr( $this->parent_key_value[ $parent_key ] ); ?>">
				<?php
			}
		}

		/**
		 * Overwrite method to add show less/more functionality
		 */
		public function add_form_logic() {
			if ( 'new' !== $this->action_posted || $this->child_request ) {
				?>
				<input type="button"
					   value="<?php echo __( 'show more', 'wp-data-access' ); ?> >>>"
					   class="button"
					   id="show_more_less_button"
					   style="float:right;display:none;">
				<script type='text/javascript'>
					function show_more_less() {
						jQuery('.row-show-less-more').toggle();
						if (jQuery('#show_more_less_button').val() === '<?php echo __( 'show more', 'wp-data-access' ); ?> >>>') {
							jQuery('#show_more_less_button').val('<<< <?php echo __( 'show less', 'wp-data-access' ); ?>');
						} else {
							jQuery('#show_more_less_button').val('<?php echo __( 'show more', 'wp-data-access' ); ?> >>>');
						}
					}

					if (jQuery('.row-show-less-more').length > 0) {
						jQuery('#show_more_less_button').show();

						jQuery('#show_more_less_button').on('click', function() {
							show_more_less();
						});
					}
				</script>
				<?php
			} else {
				?>
				<script type='text/javascript'>
					jQuery('.row-show-less-more').show();
				</script>
				<?php
			}
		}

	}

}
