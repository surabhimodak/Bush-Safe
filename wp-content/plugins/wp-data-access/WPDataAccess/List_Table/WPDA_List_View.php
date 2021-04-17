<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\List_Table
 */

namespace WPDataAccess\List_Table {

	use WPDataAccess\CSV_Files\WPDA_CSV_List_Table;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;
	use WPDataAccess\Data_Publisher\WPDA_Publisher_List_Table;
	use WPDataAccess\Design_Table\WPDA_Design_Table_Form;
	use WPDataAccess\Design_Table\WPDA_Design_Table_List_Table;
	use WPDataAccess\Plugin_Table_Models\WPDA_CSV_Uploads_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_Design_Table_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_Publisher_Model;
	use WPDataAccess\Plugin_Table_Models\WPDP_Page_Model;
	use WPDataAccess\Plugin_Table_Models\WPDP_Project_Design_Table_Model;
	use WPDataAccess\Plugin_Table_Models\WPDP_Project_Model;
	use WPDataAccess\Utilities\WPDA_Repository;
	use WPDataAccess\WPDA;
	use WPDataProjects\Data_Dictionary\WPDP_List_Columns_Cache;
	use WPDataProjects\Project\WPDP_Project;
	use WPDataProjects\Project\WPDP_Project_Page_List;
	use WPDataProjects\Project\WPDP_Project_Project_List;
	use WPDataProjects\Project\WPDP_Project_Table_List;

	/**
	 * Class WPDA_List_View
	 *
	 * A list view is an object that consists of a WordPress list table and it's screen options (displayed in the top
	 * right corner). The list view combines these options by identifying the different stages that apply to building
	 * a page containing both.
	 *
	 * Stages:
	 * + Screen options are added in the constructor
	 * + The list table is created in {@see WPDA_List_View::display_list_table()}
	 *
	 * To make sure that screen options are displayed with the list table, the constructor is called in the
	 * 'admin_menu' hook {@see \WP_Data_Access::define_admin_hooks()}. A object reference is stored in the class
	 * and used later when the list table is created on the page displayed.
	 *
	 * @see WPDA_List_View::display_list_table()
	 * @see \WP_Data_Access::define_admin_hooks()
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_List_View {

		const HIDDENCOLUMNS_PREFIX = 'wpda_manage_columnshidden_';

		protected static $screen_settings_saved = false;

		/**
		 * Page hook suffix
		 *
		 * @var object|boolean Reference to (sub) menu or false
		 */
		protected $page_hook_suffix;

		/**
		 * Database schema name
		 *
		 * @var string
		 */
		protected $schema_name;

		/**
		 * Database table name
		 *
		 * @var string
		 */
		protected $table_name;

		/**
		 * Indicates if bulk actions are allow
		 *
		 * @var boolean
		 */
		protected $bulk_actions_enabled;

		/**
		 * Indicates if a search box is shown
		 *
		 * @var boolean
		 */
		protected $search_box_enabled;

		/**
		 * Indicates if bulk exports are allowed
		 *
		 * @var boolean
		 */
		protected $bulk_export_enabled;

		/**
		 * Classname of list table
		 *
		 * @var string
		 */
		protected $list_table_class;

		/**
		 * Classname of data entry form
		 *
		 * @var string
		 */
		protected $edit_form_class;

		/**
		 * Reference to list table
		 *
		 * @var WPDA_List_Table|WPDA_List_Table_Menu
		 */
		protected $wpda_list_table;

		/**
		 * Reference to list columns
		 *
		 * @var WPDA_List_Columns
		 */
		protected $wpda_list_columns = null;

		/**
		 * Page title
		 *
		 * @var string
		 */
		protected $title;

		/**
		 * Page subtitle
		 *
		 * @var string
		 */
		protected $subtitle;

		/**
		 * Action (taken from $_REQUEST)
		 *
		 * @var string
		 */
		protected $action = '';

		/**
		 * Column headers (labels)
		 *
		 * @var string
		 */
		protected $column_headers;

		/**
		 * Show view link in list table? (on|off)
		 *
		 * @var string
		 */
		protected $show_view_link = null;

		/**
		 * Allow insert? (on|off)
		 *
		 * @var string
		 */
		protected $allow_insert = null;

		/**
		 * Allow update? (on|off)
		 *
		 * @var string
		 */
		protected $allow_update = null;

		/**
		 * Allow delete? (on|off)
		 *
		 * @var string
		 */
		protected $allow_delete = null;

		/**
		 * Allow import? (on|off)
		 *
		 * @var string
		 */
		protected $allow_import = null;

		/**
		 * Default WHERE clause
		 *
		 * @var string
		 */
		protected $default_where = '';

		/**
		 * Contains child table name if child request, otherwise false
		 *
		 * I don't like this kind of programming! This should be in child class of course! The load action to add
		 * screen options however cannot be delayed and therefor must be in the parent call.
		 *
		 * @var bool|string
		 */
		protected $child_request = false;

		/**
		 * WPDA_List_View constructor
		 *
		 * Page hook suffix
		 *
		 * We first check if we have a page hook suffix. This is the reference to the sub menu to which we want to
		 * add the list view. If no page hook suffix is provided, the list table might be displayed as expected, the
		 * screen options in the top right corner however will not be shown.
		 *
		 * Database table usage
		 * + The constructor can be called with or without a table name. If a table name is provided, a list table is
		 * generated for that database table.
		 * + If no table name is provided, we need to checked if a table name argument was given in the request. If a
		 * table name was provided with the request, a list table is generated for that table.
		 * + If no table name is provided (neither as an argument nor with the request) a list of all tables available
		 * in the WordPress database schema is generated.
		 *
		 * Table names are always checked! We need to check:
		 * + if a table exists in our database schema and
		 * + if we have access to that table.
		 *
		 * These checks are performed to prevent SQL injection and misuse of our WordPress database. These checks
		 * however are not performed in this class. They are performed in class {@see WPDA_List_Table} as we do not
		 * perform any queries in this class. We do perform queries on the given tables in {@see WPDA_List_Table}.
		 *
		 * @param array $args [
		 *
		 * 'page_hook_suffix'     => (string|boolean) Page hook suffix of false (default = false)
		 *
		 * 'wpdaschema_name'          => (string) Database schema name (default = '')
		 *
		 * 'table_name'           => (string) Database table name (default = '')
		 *
		 * 'bulk_actions_enabled' => (boolean) Allow bulk actions? (default = TRUE)
		 *
		 * 'search_box_enabled'   => (boolean) Show search box? (default = TRUE)
		 *
		 * 'bulk_export_enabled'  => (boolean) Allow bulk exports? (default = TRUE)
		 *
		 * 'list_table_class'     => (string) Class providing list table functionality
		 *
		 * 'edit_form_class'      => (string) Class providing data entry functionality
		 *
		 * 'column_headers'       => (array|string) Column headers (default = '' : headers taken from data dictionary)
		 *
		 * 'title'                => (string) Page title (default = null)
		 *
		 * 'subtitle'             => (string) Page subtitle (default = null)
		 *
		 * 'show_view_link'       => (string) default = 'on'
		 *
		 * 'allow_insert'         => (string) default = 'off'
		 *
		 * 'allow_update'         => (string) default = 'off'
		 *
		 * 'allow_delete'         => (string) default = 'off'
		 *
		 * 'allow_import'         => (string) default = 'off'
		 *
		 * 'default_where'             => (string)
		 *
		 * ]
		 * @see WPDA_List_Table
		 *
		 * @since   1.0.0
		 *
		 */
		public function __construct( $args = [] ) {
			$args = wp_parse_args(
				$args, [
					'page_hook_suffix'     => false,
					'wpdaschema_name'      => '',
					'table_name'           => '',
					'bulk_actions_enabled' => WPDA_List_Table::DEFAULT_BULK_ACTIONS_ENABLED,
					'search_box_enabled'   => WPDA_List_Table::DEFAULT_SEARCH_BOX_ENABLED,
					'bulk_export_enabled'  => WPDA_List_Table::DEFAULT_BULK_EXPORT_ENABLED,
					'list_table_class'     => 'WPDataAccess\\List_Table\\WPDA_List_Table',
					'edit_form_class'      => 'WPDataAccess\\Simple_Form\\WPDA_Simple_Form',
					'column_headers'       => '',
					'title'                => null,
					'subtitle'             => null,
				]
			);

			// Check access arguments
			if ( isset( $args['show_view_link'] ) ) {
				$this->show_view_link = $args['show_view_link'];
			}
			if ( isset( $args['allow_insert'] ) ) {
				$this->allow_insert = $args['allow_insert'];
			}
			if ( isset( $args['allow_update'] ) ) {
				$this->allow_update = $args['allow_update'];
			}
			if ( isset( $args['allow_delete'] ) ) {
				$this->allow_delete = $args['allow_delete'];
			}
			if ( isset( $args['allow_import'] ) ) {
				$this->allow_import = $args['allow_import'];
			}

			$this->schema_name = $args['wpdaschema_name'];
			if ( '' === $this->schema_name ) {
				// No pre defined schema_name!
				if ( isset( $_REQUEST['wpdaschema_name'] ) ) {
					// Get schema name from URL.
					$this->schema_name = sanitize_text_field( wp_unslash( $_REQUEST['wpdaschema_name'] ) ); // input var okay.
				}
			}

			$this->table_name = $args['table_name'];
			if ( '' === $this->table_name ) {
				// No pre defined table_name!
				if ( isset( $_REQUEST['table_name'] ) ) {
					// Get table name from URL. (later we'll check if the table exists in the WordPress database to
					// protect ourselves against SQL injection).
					$this->table_name = sanitize_text_field( wp_unslash( $_REQUEST['table_name'] ) ); // input var okay.
				}
			}

			$this->page_hook_suffix = $args['page_hook_suffix'];

			// Set class to provide list table functionality.
			$this->list_table_class = $args['list_table_class'];

			// Set class for data entry form support (used for new, edit and view actions).
			$this->edit_form_class = $args['edit_form_class'];

			// Set page title.
			$this->title = $args['title'];

			// Set page subtitle.
			$this->subtitle = $args['subtitle'];

			$this->bulk_actions_enabled = $args['bulk_actions_enabled'];
			$this->search_box_enabled   = $args['search_box_enabled'];
			$this->bulk_export_enabled  = $args['bulk_export_enabled'];

			if ( isset( $args['action'] ) ) {
				$this->action = $args['action'];
			} else {
				if ( isset( $_REQUEST['action'] ) ) {
					$this->action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // input var okay; sanitization okay.
				}
			}

			if ( false !== $this->page_hook_suffix ) {
				if ( is_admin() &&
					(
						! ( 'new' === $this->action ||
					         'edit' === $this->action ||
					         'view' === $this->action ||
					         'user_menu' === $this->action
						)
					)
				) {
					$page_action = isset( $_REQUEST['page_action'] ) ? 'defined' : '';
					if ( $page_action === '' ) {
						// Add screen options.
						add_action( 'load-' . $this->page_hook_suffix, [ $this, 'page_screen_options' ] );
					}
				}
			}

			// Overwrite column header text.
			$this->column_headers = isset( $args['column_headers'] ) ? $args['column_headers'] : '';

			// Check for default WHERE clause
			if ( isset( $args['default_where'] ) ) {
				$this->default_where = $args['default_where'];
			}
		}

		/**
		 * Set columns to be queried
		 *
		 * If not set all column (*) will be selected/set/queried.
		 *
		 * @param mixed $columns_queried Column array, '' or '*'.
		 *
		 * @since   1.0.0
		 *
		 */
		public function set_columns_queried( $columns_queried ) {

			$this->wpda_list_table->set_columns_queried( $columns_queried );

		}

		/**
		 * Enable or disable bulk actions
		 *
		 * If enabled user can perform actions on multiple rows at once.
		 *
		 * @param boolean $bulk_actions_enabled TRUE = allow bulk actions, FALSE = no bulk actions.
		 *
		 * @since   1.0.0
		 *
		 */
		public function set_bulk_actions_enabled( $bulk_actions_enabled ) {

			$this->bulk_actions_enabled = $bulk_actions_enabled;

		}

		/**
		 * Enable search box
		 *
		 * Shows a search box if enabled. In WP Data Access only columns with data type varchar or enum are searched.
		 *
		 * @param boolean $search_box_enabled TRUE = show search box, FALSE = no search box.
		 *
		 * @since   1.0.0
		 *
		 */
		public function set_search_box_enabled( $search_box_enabled ) {

			$this->search_box_enabled = $search_box_enabled;

		}

		/**
		 * Display page
		 *
		 * Page types to be displayed:
		 * + List table
		 * + View form
		 * + Data entry form (add new)
		 * + Data entry form (update)
		 *
		 * The type of table displayed depend on the value of the action argument provided in the request. The value
		 * of argument action is stored in $this->action in the constructor.
		 *
		 * @since   1.0.0
		 */
		public function show() {
			// Add datetimepicker
			wp_enqueue_style( 'datetimepicker' );
			wp_enqueue_script( 'datetimepicker' );

			// Prepare columns for list table. Needed in get_column_headers() and handed over to list table to prevent
			// processing the same queries multiple times.
			if ( null === $this->wpda_list_columns ) {
				$this->wpda_list_columns = WPDA_List_Columns_Cache::get_list_columns( $this->schema_name, $this->table_name );
			}

			$wpda_repository = new WPDA_Repository();
			$wpda_repository->inform_user();

			switch ( $this->action ) {

				case 'new':  // Show edit form in editing mode to create new records.
				case 'edit': // Show edit form in editing mode to update records.
				case 'view': // Show edit form in view mode to view records.
					$this->display_edit_form();
					break;

				case 'create_table': // Show form to create new table.
					$this->display_design_menu();
					break;

				default: // Show list (default).
					$this->display_list_table();

			}

		}

		/**
		 * Display data entry form
		 *
		 * Called when action is:
		 * + 'new' to add a new record to the table
		 * + 'edit' to update a record
		 * + 'view" to show a record (readonly)
		 *
		 * Class WPDA_Simple_Form is the default class used to generate data entry forms. This class provides dynamic
		 * generation of data entry forms for any table, as long as the table has a primary key. The primary key is
		 * necessary to perform updates (unique identification of records).
		 *
		 * For more specific data entry forms WPDA_Simple_Form can be extended. These classes need to implement some
		 * methods to work properly. Check out {@see \WPDataAccess\Simple_Form\WPDA_Simple_Form} for more information.
		 *
		 * @since   1.0.0
		 *
		 * @see \WPDataAccess\Simple_Form\WPDA_Simple_Form
		 */
		protected function display_edit_form() {

			$form = new $this->edit_form_class(
				$this->schema_name,
				$this->table_name,
				$this->wpda_list_columns
			);
			$form->show();

		}

		/**
		 * Adds Data Designer menu-item to plugin menu
		 */
		protected function display_design_menu() {
			$form = new WPDA_Design_Table_Form;
			$form->show();
		}

		/**
		 * Display list table
		 *
		 * There are two type of list tables here:
		 * + List of tables in the WordPress database schema
		 * + List of rows in a specific table
		 *
		 * A list of tables in the WordPress database schema is in fact a list of rows as well. The MySQL base table
		 * (which is in fact a view) used to show this information is 'information_schema.tables'. The list of tables
		 * contains a link to a list table for every table.
		 *
		 * The list of rows is provided by class {@see WPDA_List_Table}. WPDA_List_Table extends Wordprees class
		 * WP_List_Table.
		 *
		 * The list of tables is provided by class {@see WPDA_List_Table_Menu}. WPDA_List_Table_Menu extends class
		 * WPDA_List_Table.
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_List_Table
		 * @see WPDA_List_Table_Menu
		 */
		protected function display_list_table() {
			if ( '' === $this->table_name ) {
				// List all tables in the database.
				$this->list_table_class = 'WPDataAccess\\List_Table\\WPDA_List_Table_Menu';
			}

			$args = [
				'wpdaschema_name'   => $this->schema_name,
				'table_name'        => $this->table_name,
				'wpda_list_columns' => $this->wpda_list_columns,
				'column_headers'    => $this->column_headers,
				'default_where'     => $this->default_where,
			];
			if ( null !== $this->show_view_link ) {
				$args['show_view_link'] = $this->show_view_link;
			}
			if ( null !== $this->allow_insert ) {
				$args['allow_insert'] = $this->allow_insert;
			}
			if ( null !== $this->allow_update ) {
				$args['allow_update'] = $this->allow_update;
			}
			if ( null !== $this->allow_delete ) {
				$args['allow_delete'] = $this->allow_delete;
			}
			if ( null !== $this->allow_import ) {
				$args['allow_import'] = $this->allow_import;
			}

			$this->wpda_list_table = new $this->list_table_class( $args );

			$this->wpda_list_table->set_bulk_actions_enabled( $this->bulk_actions_enabled );
			$this->wpda_list_table->set_search_box_enabled( $this->search_box_enabled );
			$this->wpda_list_table->set_bulk_export_enabled( $this->bulk_export_enabled );

			// Reset page title and subtitle to allow empty titles and subtitles as well.
			if ( null !== $this->title ) {
				$this->wpda_list_table->set_title( $this->title );
			}
			if ( null !== $this->subtitle ) {
				$this->wpda_list_table->set_subtitle( $this->subtitle );
			}

			$this->wpda_list_table->show();
		}

		/**
		 * Set page screen options
		 *
		 * Provided are column selection (enable/disable) and rows per page. The table name is included in the meta_key
		 * to save screen options per table.
		 *
		 * @since   1.0.0
		 */
		public function page_screen_options() {
			if ( isset( $_REQUEST['child_tab'] ) ) {
				$this->child_request = sanitize_text_field( wp_unslash( $_REQUEST['child_tab'] ) ); // input var okay; sanitization okay.
			}

			if ( false !== $this->child_request ) {
				$table_name = str_replace( '.', '_', $this->schema_name . $this->child_request );
			} else {
				global $wpdb;
				if ( $this->schema_name === $wpdb->dbname && $this->table_name === WPDA_CSV_Uploads_Model::get_base_table_name() ) {
					$table_name = $this->table_name; // csv upload = exception
				} else {
					$table_name = str_replace( '.', '_', $this->schema_name . $this->table_name );
				}
			}

			add_filter( 'screen_settings', [ $this, 'show_screen_options' ], 10, 2 );
			$this->set_screen_option();
			$screen = get_current_screen();

			if ( is_object( $screen ) && $screen->id === $this->page_hook_suffix ) {
				if ( '' === $this->table_name ) {
					// The WordPress Database Table List doesn't have a table_name at this stage. Use the base table
					// defined in WPDA_List_Table_Menu instead.
					$table_name = str_replace( '.', '_', WPDA_List_Table::LIST_BASE_TABLE );
					// Set default column display values for repository tables if screen options is activated
					// for the first time
					if ( is_admin() &&
						 false === get_user_option( self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . $table_name )
					) {
						$hidden = [
							'create_time',
							'data_size',
							'index_size',
							'overhead',
							'table_collation',
						];

						update_user_meta(
							get_current_user_id(),
							self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . $table_name,
							$hidden
						);
					}
				} else {
					// Set default column display values for repository tables if screen options is activated
					// for the first time
					if ( WPDA_Design_Table_Model::get_base_table_name() === $this->table_name ) {
						if ( is_admin() &&
							 false === get_user_option( self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . $table_name )
						) {
							$hidden = [
								'wpda_table_design',
							];

							update_user_meta(
								get_current_user_id(),
								self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . $table_name,
								$hidden
							);
						}
					} elseif ( WPDA_Publisher_Model::get_base_table_name() === $this->table_name ) {
						if ( is_admin() &&
							 false === get_user_option( self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . $table_name )
						) {
							$hidden = [
								'pub_column_names',
								'pub_responsive_popup_title',
								'pub_responsive_cols',
								'pub_responsive_type',
								'pub_responsive_modal_hyperlinks',
								'pub_responsive_icon',
								'pub_format',
								'pub_default_where',
								'pub_default_orderby',
								'pub_table_options_searching',
								'pub_table_options_ordering',
								'pub_table_options_paging',
								'pub_table_options_advanced',
								'pub_table_options_nl2br',
								'pub_sort_icons',
								'pub_show_advanced_settings',
							];

							update_user_meta(
								get_current_user_id(),
								self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . $table_name,
								$hidden
							);
						}
					} elseif ( WPDP_Project_Model::get_base_table_name() === $this->table_name ) {
						if ( is_admin() &&
							 false === get_user_option( self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . $table_name )
						) {
							$hidden = [
								'project_description',
								'project_sequence',
							];

							update_user_meta(
								get_current_user_id(),
								self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . $table_name,
								$hidden
							);

							// Set default columns for project pages
							if ( false === get_user_option( self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . WPDP_Page_Model::get_base_table_name() ) ) {
								$hidden = [
									'project_id',
									'page_schema_name',
									'page_setname',
									'page_allow_insert',
									'page_allow_delete',
									'page_allow_import',
									'page_allow_bulk',
									'page_content',
									'page_title',
									'page_subtitle',
									'page_where',
									'page_orderby',
									'page_sequence',
								];

								update_user_meta(
									get_current_user_id(),
									self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . WPDP_Page_Model::get_base_table_name(),
									$hidden
								);
							}
						}
					}  elseif ( WPDP_Project_Design_Table_Model::get_base_table_name() === $this->table_name ) {
						if ( is_admin() &&
							 false === get_user_option( self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . $table_name )
						) {
							$hidden = [
							];

							update_user_meta(
								get_current_user_id(),
								self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . $table_name,
								$hidden
							);
						}
					}  elseif ( WPDA_CSV_Uploads_Model::get_base_table_name() === $this->table_name ) {
						if ( is_admin() &&
							false === get_user_option( self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . $table_name )
						) {
							$hidden = [
								'csv_real_file_name',
								'csv_mapping',
							];

							update_user_meta(
								get_current_user_id(),
								self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . $table_name,
								$hidden
							);
						}
					}

					// Allow external user to store defaults through action hook wpda_default_screen_option.
					do_action( 'wpda_default_screen_option', $this->table_name, $table_name );
				}

				// Add column selection
				if ( false !== $this->child_request ) {
					if ( WPDP_Page_Model::get_base_table_name() === $this->child_request ) {
						$cols = WPDP_Project_Page_List::column_headers_labels();
						$hidden = get_user_meta(
							get_current_user_id(),
							self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . WPDP_Page_Model::get_base_table_name()
						);
					} else {
						$setname  = 'default';
						if ( isset( $_REQUEST['page'] ) ) {
							$ids = explode( '_', sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) );
							if ( 4 === count( $ids ) ) {
								$proj_id = $ids[2];
								$page_id = $ids[3];
								$project = new WPDP_Project( $proj_id, $page_id );
								if ( null !== $project->get_project() ) {
									if ( null !== $project->get_setname() ) {
										$setname = $project->get_setname();
									}
								}
							}
						}
						$wpdp_list_columns_child = WPDP_List_Columns_Cache::get_list_columns( $this->schema_name, $this->child_request, 'listtable', $setname );
						$cols                    = $wpdp_list_columns_child->get_table_column_headers();
						$hidden = get_user_meta(
							get_current_user_id(),
							self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . $table_name
						);
					}
				} else {
					$cols = $this->get_column_headers();
					$hidden = get_user_meta(
						get_current_user_id(),
						self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . $table_name
					);
				}
				foreach ( $cols as $col => $label ) {
					if ( '' !== $label ) {
						$args = [
							'option'      => self::HIDDENCOLUMNS_PREFIX . $table_name . $col,
							'column_name' => $col,
							'label'       => $label,
							'value'       => isset( $hidden[0] ) && in_array( $col, $hidden[0] ) ? '' : 'checked="checked"',
							'default'     => '',
						];
						add_screen_option( $col, $args );
					}
				}

				// Add pagination
				$pagination = WPDA::get_option( WPDA::OPTION_BE_PAGINATION );
				$args       = [
					'label'   => __( 'Number of items per page', 'wp-data-access' ),
					'default' => $pagination,
					'option'  => 'wpda_rows_per_page_' . $table_name,
				];
				add_screen_option( 'per_page', $args );
			}
		}

		/**
		 * Get column headers
		 *
		 * @return array
		 * @since   1.0.0
		 *
		 */
		public function get_column_headers() {
			if ( '' === $this->table_name ) {
				// We're on the Data Explorer main page. Use user defined labels.
				return WPDA_List_Table_Menu::column_headers_labels();
			} elseif ( WPDA_Design_Table_Model::get_base_table_name() === $this->table_name ) {
				// We're on the Data Designer page. Use user defined labels.
				return WPDA_Design_Table_List_Table::column_headers_labels();
			} elseif ( WPDA_Publisher_Model::get_base_table_name() === $this->table_name ) {
				// We're on the Data Publisher page. Use user defined labels.
				return WPDA_Publisher_List_Table::column_headers_labels();
			} elseif ( WPDP_Project_Model::get_base_table_name() === $this->table_name ) {
				// We're on the Data Publisher page. Use user defined labels.
				return WPDP_Project_Project_List::column_headers_labels();
			} elseif ( WPDP_Project_Design_Table_Model::get_base_table_name() === $this->table_name ) {
				// We're on the Data Publisher page. Use user defined labels.
				return WPDP_Project_Table_List::column_headers_labels();
			} elseif ( WPDP_Page_Model::get_base_table_name() === $this->table_name ) {
				// We're on the Data Publisher page. Use user defined labels.
				return WPDP_Project_Page_List::column_headers_labels();
			} elseif ( WPDA_CSV_Uploads_Model::get_base_table_name() === $this->table_name ) {
				// We're on the CSV import page. Use user defined labels.
				return WPDA_CSV_List_Table::column_headers_labels();
			} else {
				if ( has_filter('wpda_get_column_headers') ) {
					// Use filter
					$column_headers = apply_filters( 'wpda_get_column_headers', $this->schema_name, $this->table_name );
					if ( null !== $column_headers ) {
						return $column_headers;
					}
				}
				// We're on the Data Explorer table page. Use table column labels.
				if ( null === $this->wpda_list_columns ) {
					$this->wpda_list_columns = WPDA_List_Columns_Cache::get_list_columns( $this->schema_name, $this->table_name );
				}
				return $this->wpda_list_columns->get_table_column_headers();
			}
		}

		/**
		 * Save screen options
		 *
		 * @return mixed
		 * @since   1.0.0
		 *
		 */
		public function set_screen_option() {
			$screen = get_current_screen();
			if (
				is_object( $screen ) &&
				$screen->id === $this->page_hook_suffix &&
				isset( $_REQUEST['screenoptionnonce'] )
			) {
				//check_admin_referer( 'screen-options-nonce', 'screenoptionnonce' );

				if (
					isset( $_REQUEST['wp_screen_options']['option'] ) &&
					isset( $_REQUEST['wp_screen_options']['value'] )
				) {
					update_user_meta(
						WPDA::get_current_user_id(),
						sanitize_text_field( wp_unslash( $_REQUEST['wp_screen_options']['option'] ) ),
						sanitize_text_field( wp_unslash( $_REQUEST['wp_screen_options']['value'] ) ) // input var okay.
					);
				}

				if ( false !== $this->child_request ) {
					$table_name = str_replace( '.', '_', $this->schema_name . $this->child_request );
					if ( WPDP_Page_Model::get_base_table_name() === $this->child_request ) {
						$cols = WPDP_Project_Page_List::column_headers_labels();
					} else {
						$wpda_list_columns_child = WPDA_List_Columns_Cache::get_list_columns( $this->schema_name, $this->child_request );
						$cols                    = $wpda_list_columns_child->get_table_column_headers();
					}
				} else {
					if ( '' === $this->table_name ) {
						$table_name = str_replace( '.', '_', WPDA_List_Table::LIST_BASE_TABLE );
					} else {
						global $wpdb;
						if ( $this->schema_name === $wpdb->dbname && $this->table_name === WPDA_CSV_Uploads_Model::get_base_table_name() ) {
							$table_name = $this->table_name; // csv upload = exception
						} else {
							$table_name = str_replace( '.', '_', $this->schema_name . $this->table_name );
						}
					}
					$cols = $this->get_column_headers();
				}

				$cols_hidden = [];
				foreach ( $cols as $col => $label ) {
					if ( isset( $_REQUEST[ $col . '-hide-setting' ] ) && 'HIDE' === $_REQUEST[ $col . '-hide-setting' ] ) {
						array_push( $cols_hidden, $col );
					}
				}

				if ( false !== $this->child_request && WPDP_Page_Model::get_base_table_name() === $this->child_request ) {
					update_user_meta(
						get_current_user_id(),
						self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . $this->child_request,
						$cols_hidden
					);
				} else {
					update_user_meta(
						get_current_user_id(),
						self::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . $table_name,
						$cols_hidden
					);
				}
			}
		}

		public function show_screen_options( $status, $args ) {
			ob_start();
			?>
			<fieldset class="metabox-prefs">
				<legend><?php echo __( 'Columns' ); ?></legend>
				<?php
				$screen = get_current_screen();
				foreach ( $screen->get_options() as $screen_option ) {
					if ( self::HIDDENCOLUMNS_PREFIX === substr( $screen_option['option'], 0, 26 ) &&
					     '' !== $screen_option['label']
					) {
						$hidden_value = '' === $screen_option['value'] ? 'HIDE' : 'SHOW';
						?>
						<label>
							<input type="checkbox"
								   id="<?php echo $screen_option['column_name']; ?>-hide"
								   name="<?php echo $screen_option['column_name']; ?>-hide"
								   value="<?php echo $screen_option['column_name']; ?>"
								   class="hide-column-tog"
								   <?php echo $screen_option['value']; ?>
								   onclick="update_column_setting(this)"
							><?php echo $screen_option['label']; ?>
							<input type="hidden"
								   id="<?php echo $screen_option['column_name']; ?>-hide-setting"
								   name="<?php echo $screen_option['column_name']; ?>-hide-setting"
							       value="<?php echo $hidden_value; ?>"
					        >
						</label>
						<?php
					}
				}
				?>
			</fieldset>
			<?php
			if ( false !== $this->child_request ) {
				echo "<input type=\"hidden\" name=\"child_tab\" value=\"{$this->child_request}\">";
			}
			?>
			<script type='text/javascript'>
				function update_column_setting(item) {
					if (jQuery(item).is(':checked')) {
						jQuery('#'+jQuery(item).attr('name')+'-setting').val('SHOW');
					} else {
						jQuery('#'+jQuery(item).attr('name')+'-setting').val('HIDE');
					}
				}
				jQuery(function () {
					// Switch fieldset: columns first = WordPress default
					jQuery("#adv-settings fieldset:first").insertAfter(jQuery("#adv-settings fieldset:last"));
				});
			</script>
			<?php
			return ob_get_clean();
		}

	}

}
