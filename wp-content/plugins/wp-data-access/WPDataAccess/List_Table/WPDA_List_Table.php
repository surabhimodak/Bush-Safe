<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\List_Table
 */

namespace WPDataAccess\List_Table {

	use WPDataAccess\Connection\WPDADB;
	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns;
	use WPDataAccess\Macro\WPDA_Macro;
	use WPDataAccess\Plugin_Table_Models\WPDA_CSV_Uploads_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_Media_Model;
	use WPDataAccess\Utilities\WPDA_Import;
	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataAccess\Utilities\WPDA_Repository;
	use WPDataAccess\Wordpress_Original;
	use WPDataAccess\WPDA;
	use WPDataProjects\WPDP;
	use WPDataAccess\Plugin_Table_Models\WPDA_Table_Settings_Model;
	use WPDataAccess\Plugin_Table_Models\WPDP_Project_Design_Table_Model;

	/**
	 * Class WPDA_List_Table
	 *
	 * This class extends WordPress class WP_List_Table. The WordPress WP_List_Table is contained in the package as
	 * advised by WordPress and might be updated in future releases.
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_List_Table extends Wordpress_Original\WP_List_Table {

		/**
		 * Base table for list of all tables
		 */
		const LIST_BASE_TABLE = 'information_schema.tables';

		/**
		 * Default value bulk actions enabled
		 */
		const DEFAULT_BULK_ACTIONS_ENABLED = true;

		/**
		 * Default value bulk export enabled
		 */
		const DEFAULT_BULK_EXPORT_ENABLED = true;

		/**
		 * Default value bulk search enabled
		 */
		const DEFAULT_SEARCH_BOX_ENABLED = true;

		/**
		 * Table row number within the current response
		 *
		 * @var int
		 */
		static protected $list_number = 0;

		/**
		 * Menu slug of the current page
		 *
		 * @var string
		 */
		protected $page;

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
		protected $subtitle = '';

		protected $show_page_title = true;

		/**
		 * Database schema name
		 *
		 * @var string
		 */
		protected $schema_name = '';

		/**
		 * Database table name
		 *
		 * @var string
		 */
		protected $table_name = '';

		/**
		 * Where clause
		 *
		 * @var string
		 */
		protected $where = '';

		/**
		 * Order by clause
		 *
		 * @var string
		 */
		protected $orderby = '';

		/**
		 * Columns in query
		 *
		 * @var array
		 */
		protected $columns_queried;

		/**
		 * Number of rows displayed in list box
		 *
		 * @var int
		 */
		protected $items_per_page;

		/**
		 * Page number
		 *
		 * @var int
		 */
		protected $current_page = 1;

		/**
		 * Add search box?
		 *
		 * @var bool
		 */
		protected $search_box_enabled = WPDA_List_Table::DEFAULT_SEARCH_BOX_ENABLED;

		/**
		 * Enable bulk actions?
		 *
		 * @var bool
		 */
		protected $bulk_actions_enabled = WPDA_List_Table::DEFAULT_BULK_ACTIONS_ENABLED;

		/**
		 * Enable exports?
		 *
		 * @var bool
		 */
		protected $bulk_export_enabled = WPDA_List_Table::DEFAULT_BULK_EXPORT_ENABLED;

		/**
		 * Show view link?
		 *
		 * @var string on|off
		 */
		protected $show_view_link = null;

		/**
		 * Allow inserts?
		 *
		 * @var string on|off
		 */
		protected $allow_insert = null;

		/**
		 * Allow updates?
		 *
		 * @var string on|off
		 */
		protected $allow_update = null;

		/**
		 * Allow deletes?
		 *
		 * @var string on|off
		 */
		protected $allow_delete = null;

		/**
		 * Hides tablenav
		 *
		 * @var bool
		 */
		protected $hide_navigation = false;

		/**
		 * Reference to column list
		 *
		 * @var WPDA_List_Columns
		 */
		protected $wpda_list_columns;

		/**
		 * Reference to dictionary object
		 *
		 * @var WPDA_Dictionary_Exist
		 */
		protected $wpda_data_dictionary;

		/**
		 * Column headers (labels)
		 *
		 * @var array
		 */
		protected $column_headers;

		/**
		 * Reference to import object
		 *
		 * @var WPDA_Import
		 */
		protected $wpda_import = null;

		/**
		 * Child tab clicked (used for parent child relationships only)
		 *
		 * @var null
		 */
		protected $child_tab = '';

		/**
		 * Search string (entered by user or taken from cookie)
		 *
		 * @var null|string
		 */
		protected $search_value = null;

		/**
		 * Previous search string
		 *
		 * @var null|string
		 */
		protected $search_value_old = null;

		/**
		 * Page number item name (default 'page_number')
		 *
		 * The name can be changed for pages on multiple levels. This is needed to get back to the right page in
		 * parent-child page.
		 *
		 * @var string
		 */
		protected $page_number_item_name = 'page_number';

		/**
		 * Real page number
		 *
		 * @var null
		 */
		protected $page_number_link = '';

		/**
		 * Page number text item
		 *
		 * @var null
		 */
		protected $page_number_item = '';

		/**
		 * Make the default accessible to other classes
		 */
		const SEARCH_ITEM_NAME_DEFAULT = 'wpda_s';

		/**
		 * Name of search item (WP default 's')
		 *
		 * @var string
		 */
		protected $search_item_name = SELF::SEARCH_ITEM_NAME_DEFAULT;

		/**
		 * Name of the column containing action links
		 *
		 * Default is the first column displayed
		 *
		 * @var string
		 */
		protected $first_display_column = '';

		/**
		 * Table settings
		 *
		 * @var null|object
		 */
		protected $wpda_table_settings = null;

		/**
		 * Project table settings
		 *
		 * @var null|object
		 */
		protected $wpda_project_table_settings = null;

		/**
		 * Show help icon if hrlp url is available
		 *
		 * @var null|string
		 */
		protected $help_url = null;

		/**
		 * Store columns in array with column name as index for fast access
		 *
		 * @var array
		 */
		protected $columns_indexed = [];

		// Variables used to store table estimate row count data
		protected $table_engine  = '';
		protected $table_rows    = '';

		/**
		 * WPDA_List_Table constructor
		 *
		 * A table name must be provided in the constructor call. The table must be a valid MySQL database table to
		 * which acces (to the back-end) is granted. If no table name is provided, the table does not exist or access
		 * to the back-end for the given table is not granted, processing is stopped. It makes no sense to continue
		 * without a valid table. A check for table existence is performed to prevent SQL injection.
		 *
		 * There are two types of tables to build a list table on:
		 * + List of tables in the WordPress database schema
		 * + List of rows in a specific table
		 *
		 * To build a list of tables in the WordPress database schema, we query table 'information_schema.tables'
		 * (which is in fact a view). This is the only query allowed outside the WordPress database schema. The
		 * table/view name is stored in constant WPDA_List_Table::LIST_BASE_TABLE for validation purposes.
		 *
		 * A list of rows for a specific table is based on WordPress class WP_List_Table.
		 *
		 * WPDA_List_Table can be used to build list tables for views as well. View based list tables however, do not
		 * support insert, update, delete, import and export actions.
		 *
		 * A table name is not the only thing we need to build a list table. We also need to have access to the
		 * table columns. If no table columns are provided execution is stopped as well.
		 *
		 * @param array $args [
		 *
		 * 'table_name'              => (string) Database table name
		 *
		 * 'wpda_list_columns'       => (object) Reference to a WPDA_List_Columns object
		 *
		 * 'singular'                => (string) Singular object label
		 *
		 * 'plural'                  => (string) lural object label
		 *
		 * 'ajax'                    => (string) TRUE = list table support Ajax
		 *
		 * 'column_headers'          => (array|string) Column headers
		 *
		 * 'title'                   => (string) Page title
		 *
		 * 'subtitle'                => (string) Page subtitle
		 *
		 * 'bulk_export_enabled'     => (boolean)
		 *
		 * 'search_box_enabled'      => (boolean)
		 *
		 * 'bulk_actions_enabled'    => (boolean)
		 *
		 * 'show_view_link'          => (string) on|off
		 *
		 * 'allow_insert'            => (string) on|off
		 *
		 * 'allow_update'            => (string) on|off
		 *
		 * 'allow_delete'            => (string) on|off
		 *
		 * 'allow_import'            => (string) on|off
		 *
		 * 'hide_navigation'         => (boolean)
		 *
		 * 'default_where'			 => (string)
		 *
		 * ]
		 * @since   1.0.0
		 *
		 */
		public function __construct( $args = [] ) {
			global $wpdb;

			if ( ! isset( $args['table_name'] ) ) {
				// Calling WPDA_List_Table without a table_name doesn't make sense.
				wp_die( __( 'ERROR: Wrong arguments [no table argument]', 'wp-data-access' ) );
			}

			if ( ! isset( $args['wpda_list_columns'] ) ) {
				// Calling WPDA_List_Table without a column list is not allowed.
				wp_die( __( 'ERROR: Wrong arguments [no columns argument]', 'wp-data-access' ) );
			}

			parent::__construct(
				[
					'singular' => isset( $args['singular'] ) ? $args['singular'] : __( 'Row', 'wp-data-access' ),
					'plural'   => isset( $args['plural'] ) ? $args['plural'] : __( 'Rows', 'wp-data-access' ),
					'ajax'     => isset( $args['ajax'] ) ? $args['ajax'] : false,
				]
			);

			// Set schema name is available.
			if ( isset( $args['wpdaschema_name'] ) ) {
				$this->schema_name = $args['wpdaschema_name'];
			}

			// Set table name (availability already checked).
			$this->table_name = $args['table_name'];

			if ( $this->table_name !== self::LIST_BASE_TABLE ) {
				// Whenever a table name is provided through a URL we are risking an SQL injection attack. Our
				// defence mechanism here is to check whether the table name provided exists in our database.
				// If it does not we'll terminate the process with an error.
				// Although this check is only needed when a table name is provided through the URL we will perform
				// it in all situations. It is a fast query which makes our application much more safe and reliable.
				$this->wpda_data_dictionary = new WPDA_Dictionary_Exist( $this->schema_name, $this->table_name );
				if ( ! $this->wpda_data_dictionary->table_exists() ) {
					wp_die( __( 'ERROR: Invalid table name or not authorized', 'wp-data-access' ) );
				}
			}

			// Get menu slag of current page.
			if ( isset( $_REQUEST['page'] ) ) {
				$this->page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ); // input var okay.
			} else {
				// In order to show a list table we need a page.
				wp_die( __( 'ERROR: Wrong arguments [no page argument]', 'wp-data-access' ) );
			}

			// Use column list: argument wpda_list_columns (availability already checked).
			$this->wpda_list_columns = &$args['wpda_list_columns'];

			// Set pagination.
			if ( is_admin() ) {
				$this->items_per_page = WPDA::get_option( WPDA::OPTION_BE_PAGINATION );
			} else {
				$this->items_per_page = WPDA::get_option( WPDA::OPTION_FE_PAGINATION );
			}

			// Overwrite column header text if column headers were provided.
			$this->column_headers = isset( $args['column_headers'] ) ? $args['column_headers'] : '';

			// Set title.
			if ( isset( $args['title'] ) ) {
				$this->title = $args['title'];
			} else {
				$this->title = __( 'Table', 'wp-data-access' ) . ' ' . strtoupper( $this->table_name );
			}

			// Set subtitle.
			if ( isset( $args['subtitle'] ) ) {
				$this->subtitle = $args['subtitle'];
			} else {
				$wp_tables = $wpdb->tables( 'all', true );

				if ( isset( $wp_tables[ substr( $this->table_name, strlen( $wpdb->prefix ) ) ] ) ) {
					$this->subtitle = '<span class="dashicons dashicons-warning"></span> ' . WPDA::get_table_type_text( WPDA::TABLE_TYPE_WP );
				} elseif ( WPDA::is_wpda_table( $this->table_name ) ) {
					$this->subtitle = '<span class="dashicons dashicons-warning"></span> ' . WPDA::get_table_type_text( WPDA::TABLE_TYPE_WPDA );
				}
			}

			if ( ! ( isset( $args['allow_import'] ) && 'off' === $args['allow_import'] ) ) {
				try {
					// Instantiate WPDA_Import.
					$url =
						is_admin() ?
							"?page={$this->page}&wpdaschema_name={$this->schema_name}&table_name={$this->table_name}" :
							"?wpdaschema_name={$this->schema_name}&table_name={$this->table_name}";
					$this->wpda_import = new WPDA_Import(
						$url,
						$this->schema_name,
						$this->table_name
					);
				} catch ( \Exception $e ) {
					// If import is turned off instantition will fail. Handle is set to null (check in future calls).
					$this->wpda_import = null;
				}
			}

			if ( isset( $args['bulk_export_enabled'] ) ) {
				$this->bulk_export_enabled = $args['bulk_export_enabled'];
			}

			if ( isset( $args['search_box_enabled'] ) ) {
				$this->search_box_enabled = $args['search_box_enabled'];
			}

			if ( isset( $args['bulk_actions_enabled'] ) ) {
				$this->bulk_actions_enabled = $args['bulk_actions_enabled'];
			}

			if ( 'on' !== WPDA::get_option( WPDA::OPTION_BE_VIEW_LINK ) ) {
				$this->show_view_link = WPDA::get_option( WPDA::OPTION_BE_VIEW_LINK );
			} else {
				if ( isset( $args['show_view_link'] ) ) {
					$this->show_view_link = $args['show_view_link'];
				} else {
					$this->show_view_link = WPDA::get_option( WPDA::OPTION_BE_VIEW_LINK );
				}
			}

			if ( 'on' !== WPDA::get_option( WPDA::OPTION_BE_ALLOW_INSERT ) ) {
				$this->allow_insert = WPDA::get_option( WPDA::OPTION_BE_ALLOW_INSERT );
			} else {
				if ( isset( $args['allow_insert'] ) ) {
					$this->allow_insert = $args['allow_insert'];
				} else {
					$this->allow_insert = WPDA::get_option( WPDA::OPTION_BE_ALLOW_INSERT );
				}
			}

			if ( 'on' !== WPDA::get_option( WPDA::OPTION_BE_ALLOW_UPDATE ) ) {
				$this->allow_update = WPDA::get_option( WPDA::OPTION_BE_ALLOW_UPDATE );
			} else {
				if ( isset( $args['allow_update'] ) ) {
					$this->allow_update = $args['allow_update'];
				} else {
					$this->allow_update = WPDA::get_option( WPDA::OPTION_BE_ALLOW_UPDATE );
				}
			}

			if ( 'on' !== WPDA::get_option( WPDA::OPTION_BE_ALLOW_DELETE ) ) {
				$this->allow_delete = WPDA::get_option( WPDA::OPTION_BE_ALLOW_DELETE );
			} else {
				if ( isset( $args['allow_delete'] ) ) {
					$this->allow_delete = $args['allow_delete'];
				} else {
					$this->allow_delete = WPDA::get_option( WPDA::OPTION_BE_ALLOW_DELETE );
				}
			}

			if ( isset( $args['hide_navigation'] ) ) {
				$this->hide_navigation = $args['hide_navigation'];
			}

			$this->search_value = str_replace( "\'", "", $this->get_search_value() );
			if ( isset( $_REQUEST["{$this->search_item_name}_old_value"] ) ) {
				$this->search_value_old = wp_unslash( $_REQUEST["{$this->search_item_name}_old_value"] ); // input var okay.
			} else {
				$this->search_value_old = $this->search_value;
			}

			// Get page number(s).
			if ( 'page_number' !== $this->page_number_item_name ) {
				if ( isset( $_REQUEST['page_number'] ) ) {
					$requested_page_number  = sanitize_text_field( wp_unslash( $_REQUEST['page_number'] ) ); // input var okay.
					$this->page_number_link = "&page_number=" . $requested_page_number;
					$this->page_number_item = "<input type='hidden' name='page_number' value='" . $requested_page_number . "' />";
				}
			}
			$this->page_number_link .= "&paged=" . $this->get_pagenum();
			$this->page_number_item .= "<input type='hidden' name='" . $this->page_number_item_name . "' value='" . $this->get_pagenum() . "' />";

			// Add search arguments to link to return to same page
			foreach ( $_REQUEST as $key => $value) {
				if ( substr( $key, 0, 19) === 'wpda_search_column_' ) {
					if ( is_array( $value ) ) {
						foreach ( $value as $elem_key => $elem_value ) {
							$this->page_number_link .= "&{$elem_key}={$elem_value}";
							$this->page_number_item .= "<input type='hidden' name='{$key}[]' value='{$elem_value}' />";
						}
					} else {
						$this->page_number_link .= "&{$key}={$value}";
						$this->page_number_item .= "<input type='hidden' name='{$key}' value='{$value}' />";
					}
				}
			}

			// Check if a WHERE clause (filter) was defined
			if ( isset( $args[ 'default_where' ] ) ) {
				$this->where = $args[ 'default_where' ];
			}

			// Get table settings
			$wpda_table_settings = WPDA_Table_Settings_Model::query( $this->table_name, $this->schema_name );
			if ( isset( $wpda_table_settings[0]['wpda_table_settings'] ) ) {
				$this->wpda_table_settings = json_decode( $wpda_table_settings[0]['wpda_table_settings'] );
			}

			// Get table engine
			$this->table_engine = WPDA_Dictionary_Lists::get_engine( $this->schema_name, $this->table_name );

			// Get estimated row count
			$this->table_rows = WPDA::get_row_count_estimate( $this->schema_name, $this->table_name, $this->wpda_table_settings );

			// Get project table settings
			global $wpda_project_mode;
			if ( is_array( $wpda_project_mode ) ) {
				$setname                        = $wpda_project_mode['setname'];
				$wpda_project_table_settings    = null;
				$wpda_project_table_settings_db = WPDP_Project_Design_Table_Model::static_query( $this->schema_name, $this->table_name, $setname );
				if ( isset( $wpda_project_table_settings_db->tableinfo ) ) {
					$this->wpda_project_table_settings = $wpda_project_table_settings_db->tableinfo;
				}
			}

			if ( isset( $args['show_page_title'] ) && false === $args['show_page_title'] ) {
				$this->show_page_title = $args['show_page_title'];
			}

			if ( isset( $args['help_url'] ) ) {
				$this->help_url = $args['help_url'];
			}

			foreach( $this->wpda_list_columns->get_table_columns() as $table_columns ) {
				$this->columns_indexed[ $table_columns['column_name'] ] = $table_columns;
			}
		}

		/**
		 * Overwrite method
		 *
		 * @return int|void
		 */
		public function get_pagenum() {
			if ( isset( $_REQUEST['page_number'] ) ) {
				$pagenum = isset( $_REQUEST['page_number'] ) ? absint( $_REQUEST['page_number'] ) : 0;

				if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] ) {
					$pagenum = $this->_pagination_args['total_pages'];
				}

				return max( 1, $pagenum );
			} else {
				return parent::get_pagenum();
			}
		}

		/**
		 * Set columns to be queries
		 *
		 * Set columns to be queried and shown in the list table.
		 * By default all columns in the table will be displayed.
		 *
		 * @param mixed $columns_queried Column list.
		 *
		 * @since   1.0.0
		 *
		 */
		public function set_columns_queried( $columns_queried ) {

			$this->columns_queried = $columns_queried;

		}

		/**
		 * Enable or disable bulk actions
		 *
		 * @param boolean $bulk_actions_enabled TRUE = allowed, FALSE = not allowed.
		 *
		 * @since   1.0.0
		 *
		 */
		public function set_bulk_actions_enabled( $bulk_actions_enabled ) {

			$this->bulk_actions_enabled = $bulk_actions_enabled;

		}

		/**
		 * Enable or disable bulk export options
		 *
		 * @param boolean $bulk_export_enabled TRUE = allowed, FALSE = not allowed.
		 *
		 * @since   1.0.0
		 *
		 */
		public function set_bulk_export_enabled( $bulk_export_enabled ) {

			$this->bulk_export_enabled = $bulk_export_enabled;

		}

		/**
		 * Show or hide search box
		 *
		 * @param boolean $search_box_enabled TRUE = show search box, FALSE = do not show search bow.
		 *
		 * @since   1.0.0
		 *
		 */
		public function set_search_box_enabled( $search_box_enabled ) {

			$this->search_box_enabled = $search_box_enabled;

		}

		/**
		 * Set page title
		 *
		 * @param string $title Page title.
		 *
		 * @since   1.0.0
		 *
		 */
		public function set_title( $title ) {

			$this->title = $title;

		}

		/**
		 * Set page subtitle
		 *
		 * @param string $subtitle Page subtitle.
		 *
		 * @since   1.0.0
		 *
		 */
		public function set_subtitle( $subtitle ) {

			$this->subtitle = $subtitle;

		}

		/**
		 * I18n text displayed when no data found
		 *
		 * @since   1.0.0
		 */
		public function no_items() {

			echo __( 'No data found', 'wp-data-access' );

		}

		/**
		 * Use this method to build parent child relationships.
		 *
		 * Overwrite this function if you want to use the list table as a child list table related to some parent
		 * form. You can add parent arguments to calls to make sure you get back to the right parent.
		 *
		 * @param array  $item Column info.
		 * @return string String contains parent arguments.
		 * @since   1.5.0
		 *
		 */
		protected function add_parent_args_as_string( $item ) {
			return '';
		}

		/**
		 * Render columns
		 *
		 * Three links are provided for every list table row:
		 * + 'View' => Link to view form (readonly data entry form)
		 * + 'Edit' => Link to data entry form (updatable)
		 * + 'Delete' => Link to delete record from database table
		 *
		 * Links to view, edit and delete records are only available if a primary key for the table is found. Without
		 * a primary key unique identification of records is not possible.
		 *
		 * Links to action are offered through hidden forms to prevent long URL's and problem when refreshing pages
		 * manually. More information about how and why is provided in source code at place where I thoug the
		 * information could be helpful.
		 *
		 * @param array  $item Column info.
		 * @param string $column_name Column name.
		 *
		 * @return mixed Value display in list table column
		 * @since   1.0.0
		 *
		 */
		public function column_default( $item, $column_name ) {

			if ( $this->wpda_list_columns->get_table_columns()[0]['column_name'] === $column_name ||
			     $column_name === $this->first_display_column ) {
				// First column: add row actions.
				$count = count( $this->wpda_list_columns->get_table_primary_key() );
				if ( 0 === $count ) {
					// No actions without a primary key!
					// This automatically covers view processing correctly.
					return $this->render_column_content( $item, $column_name );
				} else {
					// Check rights.
					if ( 'off' === $this->show_view_link && 'off' === $this->allow_update && 'off' === $this->allow_delete ) {
						// No rights!
						$actions = [];
						$this->column_default_add_action( $item, $column_name, $actions );
						if ( is_array( $actions ) && count( $actions ) > 0 ) {
							return sprintf( '%1$s %2$s', $this->render_column_content( $item, $column_name ), $this->row_actions( $actions ) );
						} else {
							return sprintf( '%1$s', $this->render_column_content( $item, $column_name ) );
						}
					}

					// Check if row security is enabled
					$row_security =
						isset( $this->wpda_table_settings->table_settings->row_level_security ) &&
						'true' === $this->wpda_table_settings->table_settings->row_level_security;

					// To prevent our URLs containing many arguments, we'll use post to submit row actions. Since our
					// list table is build within a form and we cannot use nested forms we'll use a container (id =
					// wpda_invisible_container) defined outside the list table, and add our forms to that container.
					// We'll use jQuery to add our forms to the container. From the links in our rows we can then just
					// submit any form in that container with jQuery as well.
					$form_id           = '_' . self::$list_number ++;
					$wp_nonce_keys     = '';
					$row_security_keys = '';
					// We need to add keys and values for multi column primary keys.
					foreach ( $this->wpda_list_columns->get_table_primary_key() as $key ) {
						$wp_nonce_keys     .= "-{$item[$key]}";
						if ( $row_security ) {
							$row_security_keys .= "-{$key}-{$item[$key]}";
						}
					}

					if ( $row_security ) {
						$row_security_nonce       = wp_create_nonce( "wpda-row-level-security-{$this->table_name}{$row_security_keys}" );
						$row_security_nonce_field =
							"<input type='hidden' name='rownonce' value='" . esc_attr( $row_security_nonce ) . "' />";
					} else {
						$row_security_nonce_field = '';
					}

					// Prepare url
					if ( is_admin() ) {
						$url = "?page={$this->page}";
					} else {
						$url = '';
					}
					global $wpdb;
					if ( '' !== $this->schema_name && $wpdb->dbname !== $this->schema_name ) {
						$url .= $url === '' ? '?' : '&';
						$url .= "wpdaschema_name={$this->schema_name}";
					}
					$url .= $url === '' ? '?' : '&';
					$url .= "table_name={$this->table_name}";
					$url = esc_attr( $url );

					if ( 'on' === $this->show_view_link ) {
						// Build the row action.
						// Use jQuery to add form to container.
						$view_form =
							"<form" .
							" id='view_form{$form_id}'" .
							" action='{$url}'" .
							" method='post'>" .
							$this->get_key_input_fields( $item ) .
							$this->add_parent_args_as_string( $item ) .
							"<input type='hidden' name='action' value='view' />" .
							$row_security_nonce_field .
							$this->page_number_item .
							"</form>"
						?>

						<script type='text/javascript'>
							jQuery("#wpda_invisible_container").append("<?php echo $view_form; ?>");
						</script>

						<?php

						// Add link to submit form.
						$actions['view'] = sprintf(
							'<a href="javascript:void(0)" 
                                    class="edit wpda_tooltip"
                                    title="%s"
                                    onclick="jQuery(\'#%s\').submit()">
                                    <span style="white-space: nowrap">
										<span class="material-icons wpda_icon_on_button">visibility</span>
										%s
                                    </span>
                                </a>
                                ',
							__( 'View row data', 'wp-data-access' ),
							"view_form{$form_id}",
							__( 'View', 'wp-data-access' )
						);
					}

					if ( 'on' === $this->allow_update || WPDA::is_wpda_table( $this->table_name ) ) {
						// Build the row action.
						// Use jQuery to add form to container.
						$edit_form =
							"<form" .
							" id='edit_form{$form_id}'" .
							" action='{$url}'" .
							" method='post'>" .
							$this->get_key_input_fields( $item ) .
							$this->add_parent_args_as_string( $item ) .
							"<input type='hidden' name='action' value='edit' />" .
							$row_security_nonce_field .
							$this->page_number_item .
							"</form>";
						?>

						<script type='text/javascript'>
							jQuery("#wpda_invisible_container").append("<?php echo $edit_form; ?>");
						</script>

						<?php

						// Add link to submit form.
						$actions['edit'] = sprintf(
							'<a href="javascript:void(0)" 
                                    class="edit wpda_tooltip"
                                    title="%s"  
                                    onclick="jQuery(\'#%s\').submit()">
                                    <span style="white-space: nowrap">
										<span class="material-icons wpda_icon_on_button">edit</span>
										%s
                                    </span>
                                </a>
                                ',
							__( 'Edit row data', 'wp-data-access' ),
							"edit_form{$form_id}",
							__( 'Edit', 'wp-data-access' )
						);
					}

					if ( 'on' === $this->allow_delete || WPDA::is_wpda_table( $this->table_name ) ) {
						// Build the row action.
						$wp_nonce_action = "wpda-delete-{$this->table_name}$wp_nonce_keys";
						$wp_nonce        = esc_attr( wp_create_nonce( $wp_nonce_action ) );
						// Use jQuery to add form to container.
						$delete_form =
							"<form" .
							" id='delete_form{$form_id}'" .
							" action='{$url}'" .
							" method='post'>" .
							$this->get_key_input_fields( $item ) .
							$this->add_parent_args_as_string( $item ) .
							"<input type='hidden' name='action' value='delete' />" .
							"<input type='hidden' name='_wpnonce' value='$wp_nonce'>" .
							$row_security_nonce_field .
							$this->page_number_item .
							"</form>";
						?>

						<script type='text/javascript'>
							jQuery("#wpda_invisible_container").append("<?php echo $delete_form; ?>");
						</script>

						<?php

						// Add link to submit form.
						$warning           = __( "You are about to permanently delete these items from your site.\\nThis action cannot be undone.\\n\\'Cancel\\' to stop, \\'OK\\' to delete.", 'wp-data-access' );
						$actions['delete'] = sprintf(
							'<a href="javascript:void(0)" 
                                    class="delete wpda_tooltip"
                                    title="%s"
                                    onclick="if (confirm(\'%s\')) jQuery(\'#%s\').submit()">
                                    <span style="white-space: nowrap">
										<span class="material-icons wpda_icon_on_button">delete</span>
										%s
                                    </span>
                                </a>
                                ',
							__( 'Delete row data (this cannot be undone)', 'wp-data-access' ),
							$warning,
							"delete_form{$form_id}",
							__( 'Delete', 'wp-data-access' )
						);
					}

					// Developers can add actions by adding their own implementation of following method.
					$this->column_default_add_action( $item, $column_name, $actions );

					if ( has_filter('wpda_column_default') ) {
						// Use filter
						$filter = apply_filters(
							'wpda_column_default',
							'',
							$item,
							$column_name,
							$this->table_name,
							$this->schema_name,
							$this->wpda_list_columns,
							self::$list_number,
							$this
						);

						if ( null !== $filter ) {
							return sprintf( '%1$s %2$s', $filter, $this->row_actions( $actions ) );
						}
					}

					return sprintf( '%1$s %2$s', $this->render_column_content( $item, $column_name ), $this->row_actions( $actions ) );
				}
			} else {
				if ( substr( $column_name, 0, 15 ) === 'wpda_hyperlink_') {
					$hyperlink_no = substr( $column_name, 15 );
					if ( isset( $this->wpda_table_settings->hyperlinks[$hyperlink_no] ) ) {
						$hyperlink      = $this->wpda_table_settings->hyperlinks[$hyperlink_no];
						$hyperlink_html = isset( $hyperlink->hyperlink_html ) ? $hyperlink->hyperlink_html : '';

						if ( '' !== $hyperlink_html ) {
							// Substitute values
							foreach ( $item as $key => $value ) {
								$hyperlink_html = str_replace( "\$\${$key}\$\$", $value, $hyperlink_html );
							}
						}

						$macro          = new WPDA_Macro( $hyperlink_html );
						$hyperlink_html = $macro->exe_macro();

						if ( '' !== $hyperlink_html ) {
							// Link is not empty AFTER substitution
							if ( false !== strpos( ltrim( $hyperlink_html ), '&lt;' ) ) {
								return html_entity_decode( $hyperlink_html );
							} else {
								$hyperlink_label  = isset( $hyperlink->hyperlink_label ) ? $hyperlink->hyperlink_label : '';
								$hyperlink_target = isset( $hyperlink->hyperlink_target ) ? $hyperlink->hyperlink_target : false;

								$target = true === $hyperlink_target ? "target='_blank'" : '';

								return "<a href='" . str_replace( ' ', '+', trim( $hyperlink_html ) ) . "' {$target}>{$hyperlink_label}</a>";
							}
						} else {
							return '';
						}
					}

					return 'ERROR';
				} else {
					// Check if column is of type media
					$media_type = WPDA_Media_Model::get_column_media( $this->table_name, $column_name, $this->schema_name );
					if ( 'Image' === $media_type ) {
						$image_ids = explode( ',', $item[ $column_name ] );
						$image_src = '';

						foreach ( $image_ids as $image_id ) {
							$url = wp_get_attachment_url( esc_attr( $image_id ) );
							if ( false !== $url ) {
								$title     = get_the_title( esc_attr( $image_id ) );
								$image_src .= '' !== $image_src ? '<br/>' : '';
								$image_src .= sprintf( '<img src="%s" class="wpda_tooltip" title="%s" width="100%%">', $url, $title );
							}
						}

						return $image_src;
					} elseif ( 'ImageURL' === $media_type ) {
						return sprintf( '<img src="%s" class="wpda_tooltip" width="100%%">', $item[ $column_name ] );
					} elseif ( 'Attachment' === $media_type ) {
						$media_ids   = explode( ',', $item[ $column_name ] );
						$media_links = '';

						foreach ( $media_ids as $media_id ) {
							$url = wp_get_attachment_url( esc_attr( $media_id ) );
							if ( false !== $url ) {
								$mime_type = get_post_mime_type( $media_id );
								if ( false !== $mime_type ) {
									$title       = get_the_title( esc_attr( $media_id ) );
									$media_links .= self::column_media_attachment( $url, $title, $mime_type );
								}
							}
						}

						return $media_links;
					} elseif ( 'Hyperlink' === $media_type ) {
						if ( null === $item[ $column_name ] || '' === $item[ $column_name ] ) {
							return '';
						} else {
							if ( ! (
									isset( $this->wpda_table_settings->table_settings->hyperlink_definition ) &&
									'text' === $this->wpda_table_settings->table_settings->hyperlink_definition
								)
							) {
								$hyperlink = json_decode( $item[ $column_name ], true );
								if ( is_array( $hyperlink ) &&
									 isset( $hyperlink['label'] ) &&
								     isset( $hyperlink['url'] ) &&
								     isset( $hyperlink['target'] )
								) {
									if ( '' === $hyperlink['url'] ) {
										return ''; // $hyperlink['label'];
									} else {
										return "<a href='{$hyperlink['url']}' target='{$hyperlink['target']}'>{$hyperlink['label']}</a>";
									}
								} else {
									return '';
								}
							} else {
								$hyperlink_label = $this->wpda_list_columns->get_column_label($column_name);
								return "<a href='{$item[ $column_name ]}' target='_blank'>{$hyperlink_label}</a>";
							}
						}
					} elseif ( 'Audio' === $media_type ) {
						$audio_ids = explode( ',', $item[ $column_name ] );
						$audio_src = '';

						foreach ( $audio_ids as $audio_id ) {
							if ( 'audio' === substr( get_post_mime_type( $audio_id ), 0, 5 ) ) {
								$url = wp_get_attachment_url( esc_attr( $audio_id ) );
								if ( false !== $url ) {
									$title = get_the_title( esc_attr( $audio_id ) );
									if ( false !== $url ) {
										$audio_src .=
											'<div title="' . $title . '" class="wpda_tooltip">' .
											do_shortcode( '[audio src="' . $url . '"]' ) .
											'</div>';
									}
								}
							}
						}

						return $audio_src;
					} elseif ( 'Video' === $media_type ) {
						$video_ids = explode( ',', $item[ $column_name ] );
						$video_src = '';

						foreach ( $video_ids as $video_id ) {
							if ( 'video' === substr( get_post_mime_type( $video_id ), 0, 5 ) ) {
								$url = wp_get_attachment_url( esc_attr( $video_id ) );
								if ( false !== $url ) {
									if ( false !== $url ) {
										$video_src .=
											do_shortcode( '[video src="' . $url . '"]' );
									}
								}
							}
						}

						return $video_src;
					}
				}

				if ( has_filter('wpda_column_default') ) {
					// Use filter
					$filter = apply_filters(
						'wpda_column_default',
						'',
						$item,
						$column_name,
						$this->table_name,
						$this->schema_name,
						$this->wpda_list_columns,
						self::$list_number,
						$this
					);

					if ( null !== $filter ) {
						return $filter;
					}
				}

				if (
					'csv' !== WPDA::get_option( WPDA::OPTION_PLUGIN_SET_FORMAT ) &&
					isset( $this->columns_indexed[ $column_name ]['data_type'] ) &&
					'set' === $this->columns_indexed[ $column_name ]['data_type']
				) {
					$list      = '<' . WPDA::get_option( WPDA::OPTION_PLUGIN_SET_FORMAT ) . '>';
					$listarray = explode( ',', $item[ $column_name ] );

					foreach ( $listarray as $listitem ) {
						$list .= "<li>{$listitem}</li>";
					}
					$list .= '</' . WPDA::get_option( WPDA::OPTION_PLUGIN_SET_FORMAT ) . '>';

					return $list;
				}

				return $this->render_column_content( $item, $column_name );
			}

		}

		public static function column_media_attachment( $url, $title, $mime_type ) {
			if ( 'image' === substr( $mime_type, 0, 5 ) ) {
				$class = 'dashicons-format-image';
			} elseif ( 'audio' === substr( $mime_type, 0, 5 ) ) {
				$class = 'dashicons-playlist-audio';
			} elseif ( 'video' === substr( $mime_type, 0, 5 ) ) {
				$class = 'dashicons-playlist-video';
			} elseif ( 'application' === substr( $mime_type, 0, 11 ) ) {
				$class = 'dashicons-media-document';
			} else {
				$class = 'dashicons-external';
			}
			return sprintf( '<a href="%s" title="%s" target="_blank"><span class="dashicons %s wpda_attachment_icon"></span></a>', $url, $title, $class );
		}

		/**
		 * Overwrite method to prevent double row actions
		 * @param object $item
		 * @param string $column_name
		 * @param string $primary
		 *
		 * @return string
		 */
		protected function handle_row_actions( $item, $column_name, $primary ) {
			return '';
		}

		/**
		 * Render column content
		 *
		 * Strip content if too long and replace & character.
		 *
		 * @param string $column_content Unprepared column content
		 * @param string $column_name Database column name
		 *
		 * @return string Rendered column content
		 */
		protected function render_column_content( $item, $column_name ) {
			$column_content =
				isset( $item[ "lookup_value_{$column_name}" ] ) ?
					$item[ "lookup_value_{$column_name}" ] :
					$item[ $column_name ];

			if ( 'off' === WPDA::get_option( WPDA::OPTION_BE_TEXT_WRAP_SWITCH ) &&
			     WPDA::get_option( WPDA::OPTION_BE_TEXT_WRAP ) < strlen( $column_content )
			) {
				$title = sprintf(
					__( 'Output limited to %1$s characters', 'wp-data-access' ),
					WPDA::get_option( WPDA::OPTION_BE_TEXT_WRAP )
				);

				return substr( esc_html( str_replace( '&', '&amp;', $column_content ) ), 0, WPDA::get_option( WPDA::OPTION_BE_TEXT_WRAP ) ) .
				       ' <a href="javascript:void(0)" title="' . $title . '">&bull;&bull;&bull;</a>';
			} else {
				$column_data_type = $this->wpda_list_columns->get_column_data_type( $column_name );
				switch ( $column_data_type  ) {
					case 'date': // date only
						if ( '' === $column_content || null === $column_content ) {
							$column_content = '';
						} else {
							$column_content = date_i18n( get_option( 'date_format' ), strtotime( $column_content ) );
						}
						break;
					case 'time': // time only
						if ( '' === $column_content || null === $column_content ) {
							$column_content = '';
						} else {
							$column_content = date_i18n( get_option( 'time_format' ), strtotime( $column_content ) );
						}
						break;
					case 'datetime': // date + time
					case 'timestamp':
						if ( '' === $column_content || null === $column_content ) {
							$column_content = '';
						} else {
							$column_content =
								date_i18n( get_option( 'date_format' ) . ' ' .
								get_option( 'time_format' ), strtotime( $column_content ) );
						}
				}

				return esc_html( str_replace( '&', '&amp;', $column_content ) );
			}
		}

		/**
		 * Generate hidden fields for keys
		 *
		 * @param array $item Column info.
		 *
		 * @return string String containg key items and values.
		 */
		protected function get_key_input_fields( $item ) {

			$key_input_fields = '';

			foreach ( $this->wpda_list_columns->get_table_primary_key() as $key ) {
				$key_name         = esc_attr( $key );
				$key_value        = esc_attr( $item[ $key ] );
				$key_input_fields .= "<input type='hidden' name='$key_name' value='$key_value' />";
			}

			return $key_input_fields;

		}

		/**
		 * Override this method to add actions to first column of row
		 *
		 * @param array  $item Item information.
		 * @param string $column_name Column name.
		 * @param array  $actions Array of actions to be added to row.
		 */
		protected function column_default_add_action( $item, $column_name, &$actions ) {
			if ( has_filter('wpda_column_default_add_action') ) {
				$filter = apply_filters(
					'wpda_column_default_add_action',
					'',
					$item,
					$column_name,
					$this->table_name,
					$this->schema_name,
					$this->wpda_list_columns,
					self::$list_number,
					$actions,
					$this
				);
				if ( null !== $filter ) {
					$actions = $filter;
				}
			}
		}

		/**
		 * Render bulk edit checkbox
		 *
		 * @param array $item Column list.
		 *
		 * @return string Content for checkbox column.
		 * @since   1.0.0
		 *
		 */
		public function column_cb( $item ) {

			if ( ! $this->bulk_actions_enabled ) {
				// Bulk actions disabled.
				return '';
			}

			if ( empty( $this->wpda_list_columns->get_table_primary_key() ) ) {
				// Table has no primary key: no bulk actions allowed!
				// Primary key is used to ensure uniqueness!
				return '';
			}

			// Build CB value: Use json format for multi column primary keys.
			$cb_value = (object) null;
			foreach ( $this->wpda_list_columns->get_table_primary_key() as $primary_key_column ) {
				// JSON string key and values will be double quoted. Therefor a slash must be added to double quotes in values.
				$cb_value->$primary_key_column = esc_attr( str_replace( '"', '\"', $item[ $primary_key_column ] ) );
			}

			return "<input type='checkbox' name='bulk-selected[]' value='" . json_encode( $cb_value ) . "' />";

		}

		/**
		 * Show list table page
		 *
		 * Inside the list table page, the list table is shown. The necessary functionality to show the list table
		 * specifically is found in method {@see WPDA_List_Table::display()}.
		 *
		 * @since   1.0.0
		 *
		 * @see WPDA_List_Table::display()
		 */
		public function show() {
			// Check for import requested.
			if ( null !== $this->wpda_import ) {
				$this->wpda_import->check_post();
			}

			// Prepare list table items.
			$this->prepare_items();

			// Show list table.
			?>

			<div class="wrap<?php echo is_admin() ? '' : ' wp-core-ui'; ?>">
				<?php if ( $this->show_page_title ) { ?>
				<h1 class="wp-heading-inline">

					<?php

					if (
							self::LIST_BASE_TABLE !== $this->table_name &&
							(
								current_user_can( 'manage_options' ) &&
								\WP_Data_Access_Admin::PAGE_MAIN === $this->page
							)
					) {

						?>

						<a
								href="?page=<?php echo esc_attr( $this->page ); ?>"
								style="display: inline-block; vertical-align: unset;"
								class="dashicons dashicons-arrow-left-alt"
								title="<?php echo \WP_Data_Access_Admin::PAGE_MAIN === $this->page ? __( 'Data Explorer', 'wp-data-access' ) : __( 'Favourites', 'wp-data-access' ); ?>"
						></a>

						<?php

					}

					switch( $this->page ) {
						case \WP_Data_Access_Admin::PAGE_MAIN:
							$help_url = 'https://wpdataaccess.com/docs/documentation/data-explorer/';
							break;
						case \WP_Data_Access_Admin::PAGE_DESIGNER:
							$help_url = 'https://wpdataaccess.com/docs/documentation/data-designer/';
							break;
						case \WP_Data_Access_Admin::PAGE_PUBLISHER:
							$help_url = 'https://wpdataaccess.com/docs/documentation/data-publisher/';
							break;
						case WPDP::PAGE_MAIN:
							$help_url = '';
							break;
						default:
							$help_url = null === $this->help_url ? '' : $this->help_url;
					}
					?>

					<span style="vertical-align:top;">
						<?php
						if (
								self::LIST_BASE_TABLE !== $this->table_name &&
								(
									substr( $this->page, 0, 13 ) === \WP_Data_Access_Admin::PAGE_EXPLORER ||
									\WP_Data_Access_Admin::PAGE_MAIN === $this->page
								)
						) {
							$this->subtitle = $this->title;
							$this->title    = __( 'Data Explorer', 'wp-data-access' );
							if ( wpda_fremius()->is_premium() ) {
								$this->title = __( 'Premium', 'wp-data-access' ) . ' ' . $this->title;
							}
						}
						echo esc_attr( $this->title );
						?>
					</span>

					<?php
					if ( '' !== $help_url ) {
						?>
						&nbsp;
						<span>
							<a href="<?php echo $help_url; ?>" target="_blank"
							   class="wpda_tooltip" title="Plugin Help - opens in a new tab or window">
								<span class="material-icons" style="font-size: 26px; vertical-align: sub;">help</span></a>
							<?php
							if (
									self::LIST_BASE_TABLE === $this->table_name &&
									\WP_Data_Access_Admin::PAGE_MAIN === $this->page
							) {
								WPDA_Repository::whats_new();
							}
							?>
						</span>
						<?php
					}
					?>
				</h1>
				<?php } ?>

				<?php
				$this->add_header_button();
				?>

				<div class="wpda_subtitle"><strong><?php echo wp_kses( $this->subtitle, [ 'span' => [ 'class' => [] ] ] ); ?></strong></div>
				<div id="wpda_invisible_container" style="display:none;"></div>

				<?php

				// Add import container.
				if ( null !== $this->wpda_import ) {
					$this->wpda_import->add_container();
				}

				// Add custom code before the list table
				do_action_ref_array( 'wpda_before_list_table', [$this] );

				// Prepare url
				if ( is_admin() ) {
					$url = "?page={$this->page}";
				} else {
					$url = '';
				}
				global $wpdb;
				if ( self::LIST_BASE_TABLE !== $this->table_name ) {
					if ( '' !== $this->schema_name && $wpdb->dbname !== $this->schema_name ) {
						$url .= $url === '' ? '?' : '&';
						$url .= "wpdaschema_name={$this->schema_name}";
					}
					$url .= $url === '' ? '?' : '&';
					$url .= "table_name={$this->table_name}";
				}
				?>

				<form
						id="wpda_main_form"
						method="post"
						action="<?php echo esc_attr( $url ); ?>"
				>

					<?php

					if ( $this->search_box_enabled ) {
						$this->search_box( __( 'search', 'wp-data-access' ), 'search_id' );
					}

					$this->display();

					if ( '' === $this->get_bulk_actions() ) {
						// Add action item containing valie -1. This will allow sorting if no bulk action listbox is displayed.
						?>
						<input type="hidden" name="action" value="-1"/>
						<?php
					}

					?>

					<input id="wpda_main_form_orderby" type="hidden" name="orderby"
						   value="<?php echo ( isset( $_REQUEST['orderby'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) ) : ''; // input var okay. ?>"/>
					<input id="wpda_main_form_order" type="hidden" name="order"
						   value="<?php echo ( isset( $_REQUEST['order'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) : ''; // input var okay. ?>"/>
					<input id="wpda_main_form_post_mime_type" type="hidden" name="post_mime_type"
						   value="<?php echo ( isset( $_REQUEST['post_mime_type'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['post_mime_type'] ) ) ) : ''; // input var okay. ?>"/>
					<input id="wpda_main_form_detached" type="hidden" name="detached"
						   value="<?php echo ( isset( $_REQUEST['detached'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['detached'] ) ) ) : ''; // input var okay. ?>"/>
					<input id="wpda_main_db_schema" type="hidden" name="wpda_main_db_schema"
						   value="<?php echo ( isset( $_REQUEST['wpda_main_db_schema'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['wpda_main_db_schema'] ) ) ) : ''; // input var okay. ?>"/>
					<input id="wpda_main_favourites" type="hidden" name="wpda_main_favourites"
						   value="<?php echo ( isset( $_REQUEST['wpda_main_favourites'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['wpda_main_favourites'] ) ) ) : ''; // input var okay. ?>"/>
					<?php wp_nonce_field( 'wpda-export-*', '_wpnonce', false ); ?>
					<?php wp_nonce_field( 'wpda-delete-*', '_wpnonce2', false ); ?>
					<?php wp_nonce_field( 'wpda-drop-*', '_wpnonce3', false ); ?>
					<?php wp_nonce_field( 'wpda-truncate-*', '_wpnonce4', false ); ?>
					<?php
					foreach ( $_REQUEST as $key => $value) {
						if ( substr( $key, 0, 19) === 'wpda_search_column_' ) {
							if ( is_array( $value ) ) {
								foreach ( $value as $elem_key => $elem_value ) {
									echo "<input type='hidden' name='{$key}[]' value='$elem_value'/>";
								}
							} else {
								echo "<input type='hidden' name='$key' value='$value'/>";
							}
						}
					}
					?>
				</form>
			</div>
			<script type='text/javascript'>
				jQuery(function() {
					jQuery( '.wpda_tooltip' ).tooltip();
				});
			</script>
			<?php $this->bind_action_buttons(); ?>

			<?php
			// Add custom code after the list table
			do_action_ref_array( 'wpda_after_list_table', [$this] );
		}

		/**
		 * Bind javascript code to action buttons
		 *
		 * @since   1.6.2
		 */
		protected function bind_action_buttons() {
			?>
			<script type='text/javascript'>
				jQuery(function () {
					jQuery("#doaction").click(function () {
						return wpda_action_button();
					});
					jQuery("#doaction2").click(function () {
						return wpda_action_button();
					});
				});
			</script>
			<?php
		}

		/**
		 * Add button to page header
		 *
		 * By default "add new" and "import" buttons are added (depending on the settings). Overwrite this method to
		 * add your own buttons.
		 *
		 * @param string $add_param Parameter to be added to form action.
		 *
		 * @since   1.0.1
		 *
		 */
		protected function add_header_button( $add_param = '' ) {
			if ( 'off' === $this->allow_insert ) {
				if ( null !== $this->wpda_import ) {
					$this->wpda_import->add_button();
				}
			} else {
				if ( WPDA::is_wpda_table( $this->table_name ) ||
				     ( 'on' === WPDA::get_option( WPDA::OPTION_BE_ALLOW_INSERT ) &&
				       count( $this->wpda_list_columns->get_table_primary_key() ) ) > 0
				) {
					$storage_type =
						WPDA::is_wpda_table( $this->table_name ) ?
							__( 'respository', 'wp-data-access' ) : __( 'table', 'wp-data-access' );

					// Prepare url
					if ( is_admin() ) {
						$url = "?page={$this->page}";
					} else {
						$url = '';
					}
					global $wpdb;
					if ( '' !== $this->schema_name && $wpdb->dbname !== $this->schema_name ) {
						$url .= $url === '' ? '?' : '&';
						$url .= "wpdaschema_name={$this->schema_name}";
					}
					$url .= $url === '' ? '?' : '&';
					$url .= "table_name={$this->table_name}{$add_param}";
					?>
					<form
							method="post"
							action="<?php echo esc_attr( $url ); ?>"
							style="display: inline-block; vertical-align: baseline;"
					>
						<div>
							<input type="hidden" name="action" value="new">
							<button type="submit" class="page-title-action wpda_tooltip"
									title="<?php echo sprintf( __( 'Add new %s to %s', 'wp-data-access' ), $this->_args['singular'], $storage_type ); ?>"
							>
								<span class="material-icons wpda_icon_on_button">add_circle</span>
								<?php echo __( 'Add New', 'wp-data-access' ); ?>
							</button>
							<?php
							// Add import button to title.
							if ( null !== $this->wpda_import ) {
								$this->wpda_import->add_button();
							}
							?>
						</div>
					</form>
					<?php
				} else {
					// Add import button to title.
					if ( null !== $this->wpda_import ) {
						$this->wpda_import->add_button();
					}
				}
			}
		}

		/**
		 * Prepares the list of items for displaying
		 *
		 * Overwrites WP_List_Table::prepare_items()
		 *
		 * @since   1.0.0
		 */
		public function prepare_items() {
			// Construct where clause with search values provided in the search box.
			// Result (where clause) is written to $this->where.
			$this->construct_where_clause();

			$this->process_bulk_action();

			if ( is_admin() ) {
				$option = 'wpda_rows_per_page_' . str_replace( '.', '_', $this->table_name );
				$this->items_per_page = $this->get_items_per_page( $option, WPDA::get_option( WPDA::OPTION_BE_PAGINATION ) );
			} else {
				$this->items_per_page = WPDA::get_option( WPDA::OPTION_FE_PAGINATION );
			}

			$this->current_page   = $this->get_pagenum();
			$total_items          = $this->record_count();
			$total_pages          = ceil( $total_items / $this->items_per_page );
			if ( $this->search_value != $this->search_value_old ) {
				$this->current_page = 1;
			}
			if ( $this->current_page > $total_pages ) {
				$this->current_page = $total_pages;
			}
			$this->set_pagination_args(
				[
					'total_items' => $total_items,
					'total_pages' => $total_pages,
					'per_page'    => $this->items_per_page,
				]
			);

			$this->get_rows(); // Written to $this->items in base class (WP_List_Table).

			$columns = $this->get_columns();

			$hidden = $this->get_hidden_columns();
			if ( false === $hidden ) {
				if ( is_admin() ) {
					// List table in backend
					if ( $this->table_name === self::LIST_BASE_TABLE ) {
						$table_name = str_replace( '.', '_', WPDA_List_Table::LIST_BASE_TABLE );
					} else {
						global $wpdb;
						if ( $this->schema_name === $wpdb->dbname && $this->table_name === WPDA_CSV_Uploads_Model::get_base_table_name() ) {
							$table_name = $this->table_name; // csv upload = exception
						} else {
							$table_name = str_replace( '.', '_', $this->schema_name . $this->table_name );
						}
					}
					$hidden = get_user_option( WPDA_List_View::HIDDENCOLUMNS_PREFIX . get_current_screen()->id . $table_name );
					if ( false === $hidden ) {
						$hidden = [];
					}
				}
			}
			if ( ! is_array( $hidden ) ) {
				$hidden = [];
			}

			$sortable              = $this->get_sortable_columns();
			$primary               = $this->get_primary_column();
			$this->_column_headers = [ $columns, $hidden, $sortable, $primary ];
		}

		/**
		 * Build where clause
		 *
		 * Arguments might come from the URL so we need to check for SQL injection and use prepare. The resulting
		 * where clause is written directly to member $this->where.
		 *
		 * @since   1.0.0
		 */
		protected function construct_where_clause() {
			$whereclause = $this->get_where_clause();
			if ( '' !== $whereclause ) {
				$this->where = '' === $this->where ? " where ($whereclause) " : " {$this->where} and ($whereclause) ";
			}
		}

		public function get_where_clause() {
			return WPDA::construct_where_clause(
				$this->schema_name,
				$this->table_name,
				$this->wpda_list_columns->get_searchable_table_columns(),
				$this->search_value
			);
		}

		/**
		 * Process bulk actions
		 *
		 * Delete and bulk-delete actions use the primary key list as a reference. Before performing the actual delete
		 * statement(s) we'll check the provided column names against the data dictionary. This will protect us against
		 * SQL injection attacks.
		 *
		 * For export actions table names will not be checked here. They are handed over WPDA_Export where the validity
		 * and access rights are checked anyway (to be safe).
		 *
		 * All actions can be processed only with a valid wpnonce value.
		 *
		 * To perform actions the user must have the appropriate rights.
		 *
		 * @since   1.0.0
		 */
		public function process_bulk_action() {

			switch ( $this->current_action() ) {
				case 'delete':
					// Check access rights.
					if ( 'on' !== $this->allow_delete ) {
						// Deleting records from list table is not allowed.
						wp_die( __( 'ERROR: Not authorized [delete not allowed]', 'wp-data-access' ) );
					}

					// Prepare wp_nonce action security check.
					$wp_nonce_action = "wpda-delete-{$this->table_name}";

					$row_to_be_deleted = []; // Gonna hold the row to be deleted.
					$i                 = 0; // Index, necessary for multi column keys.

					// Check all key columns.
					foreach ( $this->wpda_list_columns->get_table_primary_key() as $key ) {
						// Check if key is available.
						if ( ! isset( $_REQUEST[ $key ] ) ) { // input var okay.
							wp_die( __( 'ERROR: Invalid URL [missing primary key values]', 'wp-data-access' ) );
						}

						// Write key value pair to array.
						$row_to_be_deleted[ $i ]['key']   = $key;
						$row_to_be_deleted[ $i ]['value'] = sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ); // input var okay.
						$i ++;

						// Add key values to wp_nonce action.
						$wp_nonce_action .= '-' . sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ); // input var okay.
					}

					// Check if delete is allowed.
					$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
					if ( ! wp_verify_nonce( $wp_nonce, $wp_nonce_action ) ) {
						wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
					}

					// All key column values available: delete record.
					// Prepare named array for delete operation.
					$next_row_to_be_deleted = [];
					$count_rows             = count( $row_to_be_deleted );
					for ( $i = 0; $i < $count_rows; $i ++ ) {
						$next_row_to_be_deleted[ $row_to_be_deleted[ $i ]['key'] ] = $row_to_be_deleted[ $i ]['value'];
					}

					if ( $this->delete_row( $next_row_to_be_deleted ) ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text' => __( 'Row deleted', 'wp-data-access' ),
							]
						);
						$msg->box();
					} else {
						$msg = new WPDA_Message_Box(
							[
								'message_text'           => __( 'Could not delete row', 'wp-data-access' ),
								'message_type'           => 'error',
								'message_is_dismissible' => false,
							]
						);
						$msg->box();
					}

					break;
				case 'bulk-delete':
					// Check access rights.
					if ( $this->allow_delete !== 'on' ) {
						// Deleting records from list table is not allowed.
						die( __( 'ERROR: Not authorized [delete not allowed]', 'wp-data-access' ) );
					}

					// We first need to check if all the necessary information is available.
					if ( ! isset( $_REQUEST['bulk-selected'] ) ) { // input var okay.
						// Nothing to delete.
						$msg = new WPDA_Message_Box(
							[
								'message_text' => __( 'Nothing to delete', 'wp-data-access' ),
							]
						);
						$msg->box();

						return;
					}

					// Check if delete is allowed.
					$wp_nonce_action = 'wpda-delete-*';
					$wp_nonce        = isset( $_REQUEST['_wpnonce2'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce2'] ) ) : ''; // input var okay.
					if ( ! wp_verify_nonce( $wp_nonce, $wp_nonce_action ) ) {
						die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
					}

					$bulk_rows = $_REQUEST['bulk-selected'];
					$no_rows   = count( $bulk_rows ); // # rows to be deleted.

					$rows_to_be_deleted = []; // Gonna hold rows to be deleted.

					for ( $i = 0; $i < $no_rows; $i ++ ) {
						// Write "json" to named array. Need to strip slashes twice. Once for the normal conversion
						// and once extra for the pre-conversion of double quotes in method column_cb().
						$row_object = json_decode( stripslashes( stripslashes( $bulk_rows[ $i ] ) ), true );
						if ( $row_object ) {
							$j = 0; // Index used to build array.

							// Check all key columns.
							foreach ( $this->wpda_list_columns->get_table_primary_key() as $key ) {
								// Check if key is available.
								if ( ! isset( $row_object[ $key ] ) ) {
									wp_die( __( 'ERROR: Invalid URL [missing primary key values]', 'wp-data-access' ) );
								}

								// Write key value pair to array.
								$rows_to_be_deleted[ $i ][ $j ]['key']   = $key;
								$rows_to_be_deleted[ $i ][ $j ]['value'] = $row_object[ $key ];
								$j ++;

							}
						}
					}

					// Looks like eveything is there. Delete records from table...
					$no_key_cols              = count( $this->wpda_list_columns->get_table_primary_key() );
					$rows_succesfully_deleted = 0; // Number of rows succesfully deleted.
					$rows_with_errors         = 0; // Number of rows that could not be deleted.
					for ( $i = 0; $i < $no_rows; $i ++ ) {
						// Prepare named array for delete operation.
						$next_row_to_be_deleted = [];

						$row_found = true;
						for ( $j = 0; $j < $no_key_cols; $j ++ ) {
							if ( isset( $rows_to_be_deleted[ $i ][ $j ]['key'] ) ) {
								$next_row_to_be_deleted[ $rows_to_be_deleted[ $i ][ $j ]['key'] ] = $rows_to_be_deleted[ $i ][ $j ]['value'];
							} else {
								$row_found = false;
							}
						}

						if ( $row_found ) {
							if ( $this->delete_row( $next_row_to_be_deleted ) ) {
								// Row(s) succesfully deleted.
								$rows_succesfully_deleted ++;
							} else {
								// An error occured during the delete operation: increase error count.
								$rows_with_errors ++;
							}
						} else {
							// An error occured during the delete operation: increase error count.
							$rows_with_errors ++;
						}
					}

					// Inform user about the results of the operation.
					$message = '';

					if ( 1 === $rows_succesfully_deleted ) {
						$message = __( 'Row deleted', 'wp-data-access' );
					} elseif ( $rows_succesfully_deleted > 1 ) {
						$message = "$rows_succesfully_deleted " . __( 'rows deleted', 'wp-data-access' );
					}

					if ( '' !== $message ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text' => $message,
							]
						);
						$msg->box();
					}

					$message = '';

					if ( $rows_with_errors > 0 ) {
						$message = __( 'Not all rows have been deleted', 'wp-data-access' );
					}

					if ( '' !== $message ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text'           => $message,
								'message_type'           => 'error',
								'message_is_dismissible' => false,
							]
						);
						$msg->box();
					}

					break;
				case 'bulk-export':
				case 'bulk-export-xml':
				case 'bulk-export-json':
				case 'bulk-export-excel':
				case 'bulk-export-csv':
					// Check access rights.
					if ( ! WPDA::is_wpda_table( $this->table_name ) ) {
						if ( 'on' !== WPDA::get_option( WPDA::OPTION_BE_EXPORT_ROWS ) ) {
							// Exporting rows from list table is not allowed.
							die( __( 'ERROR: Not authorized [export not allowed]', 'wp-data-access' ) );
						}
					}

					// We first need to check if all the necessary information is available.
					if ( ! isset( $_REQUEST['bulk-selected'] ) ) { // input var okay.
						// Nothing to export.
						$msg = new WPDA_Message_Box(
							[
								'message_text' => __( 'Nothing to export', 'wp-data-access' ),
							]
						);
						$msg->box();

						return;
					}

					// Check if export is allowed.
					$wp_nonce_action = 'wpda-export-*';
					$wp_nonce        = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : ''; // input var okay.
					if ( ! wp_verify_nonce( $wp_nonce, $wp_nonce_action ) ) {
						die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
					}

					$bulk_rows = $_REQUEST['bulk-selected'];
					$no_rows   = count( $bulk_rows ); // # rows to be exported.

					$format_type = '';
					switch ( $this->current_action() ) {
						case 'bulk-export-xml':
							$format_type = 'xml';
							break;
						case 'bulk-export-json':
							$format_type = 'json';
							break;
						case 'bulk-export-excel':
							$format_type = 'excel';
							break;
						case 'bulk-export-csv':
							$format_type = 'csv';
					}

					$querystring = '';
					if ( ! is_admin() ) {
						if( 'diehard' === $this->page ) {
							$querystring = admin_url() . 'admin-ajax.php';
						} else {
							$querystring = admin_url() . 'admin.php';
						}
					}
					$querystring .= "?action=wpda_export&type=row&mysql_set=off&show_create=off&show_comments=off&wpdaschema_name={$this->schema_name}&table_names={$this->table_name}&_wpnonce=$wp_nonce&format_type=$format_type";

					$j = 0;
					for ( $i = 0; $i < $no_rows; $i ++ ) {
						// Write "json" to named array. Need to strip slashes twice. Once for the normal conversion
						// and once extra for the pre-conversion of double quotes in method column_cb().
						$row_object = json_decode( stripslashes( stripslashes( $bulk_rows[ $i ] ) ), true );
						if ( $row_object ) {
							// Check all key columns.
							foreach ( $this->wpda_list_columns->get_table_primary_key() as $key ) {
								// Check if key is available.
								if ( ! isset( $row_object[ $key ] ) ) {
									wp_die( __( 'ERROR: Invalid URL', 'wp-data-access' ) );
								}

								// Write key value pair to array.
								$querystring .= "&{$key}[{$j}]=" . urlencode( $row_object[ $key ] );
							}
							$j ++;
						}
					}

					// Provide a link to row export (export starts when clicked)
					$msg = new WPDA_Message_Box(
						[
							'message_text' =>
								"<a id='wpda_export_link' target='_blank' href='$querystring'>" .
									__( 'Click here to download your export file', 'wp-data-access' ) .
								'</a>',
						]
					);
					$msg->box();
					?>
					<script type="text/javascript">
						jQuery(function() {
							jQuery('#wpda_export_link').on('mouseup', function() {
								// Hide link after click to prevent large exports being started more than ones
								jQuery('#wpda_export_link').parent().parent().hide();
							});
						});
					</script>
					<?php
			}

		}

		/**
		 * Delete record from database table
		 *
		 * The table must have a primary key and the values for all primary key colummns must be provided in the
		 * request. The where clause must be a named array in format: ['column_name'] = 'value'
		 *
		 * @param string $where Where clause.
		 *
		 * @return mixed
		 * @since   1.0.0
		 *
		 */
		public function delete_row( $where ) {
			$wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( null === $wpdadb ) {
				if ( is_admin() ) {
					wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				} else {
					die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				}
			}

			return $wpdadb->delete( $this->table_name, $where );
		}

		/**
		 * Number of records in database table
		 *
		 * Returns the number of records in the database table. Where clause must be prepared in advance and written
		 * to $this->where ({@see WPDA_List_Table::construct_where_clause()})
		 *
		 * @return null|string Number of rows in the current table
		 * @since   1.0.0
		 *
		 */
		public function record_count() {
			if ( $this->table_rows > -1 && '' === $this->where ) {
				// Use estimate row count
				return $this->table_rows;
			}

			// Get real row count
			$wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( null === $wpdadb ) {
				if ( is_admin() ) {
					wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				} else {
					die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				}
			}

			if ( '' === $this->schema_name ) {
				$query = "
					select count(*) 
					from `{$this->table_name}`
					{$this->where}
				";
			} else {
				if ( $this->table_name === self::LIST_BASE_TABLE ) {
					$query = "
						select count(*) 
						from {$this->table_name}
						{$this->where}
					";
				} else {
					$query = "
						select count(*) 
						from `{$wpdadb->dbname}`.`{$this->table_name}`
						{$this->where}
					";
				}
			}

			return $wpdadb->get_var( $query ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
		}

		/**
		 * Perform query to retrieve rows from database
		 *
		 * Where clause must be prepared in advance and written to $this->where
		 * ({@see WPDA_List_Table::construct_where_clause()}).
		 *
		 * No return value. Result is directly written to $this->items (base class member).
		 *
		 * @since   1.0.0
		 */
		public function get_rows() {
			$wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( null === $wpdadb ) {
				if ( is_admin() ) {
					wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				} else {
					die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				}
			}

			// Selected columns cannot be changed by the user at this time. No check for SQL injection needed now.
			// This might change in the future when users are allowed to change or set this value. A method named
			// column_exists() in WPDA_Dictionary_Checks is already available for this purpose.
			$selected_columns = '*';
			if ( isset( $this->columns_queried ) ) {
				$selected_columns = implode( ',', $this->columns_queried );
			}

			if ( isset( $this->wpda_table_settings->hyperlinks ) ) {
				$i = 0;
				foreach ( $this->wpda_table_settings->hyperlinks as $hyperlink ) {
					$skip_column = false;

					if ( null !== $this->wpda_project_table_settings ) {
						$hyperlink_label = $hyperlink->hyperlink_label;

						if ( ! property_exists( $this, 'is_child') ) {
							if (
								isset( $this->wpda_project_table_settings->hyperlinks_parent->$hyperlink_label ) &&
								! $this->wpda_project_table_settings->hyperlinks_parent->$hyperlink_label
							) {
								$skip_column = true;
							}
						} else {
							if (
								isset( $this->wpda_project_table_settings->hyperlinks_child->$hyperlink_label ) &&
								! $this->wpda_project_table_settings->hyperlinks_child->$hyperlink_label
							) {
								$skip_column = true;
							}
						}
					}

					if (
						! $skip_column &&
						isset( $hyperlink->hyperlink_list ) &&
						true === $hyperlink->hyperlink_list
					) {
						$hyperlink_label = isset( $hyperlink->hyperlink_label ) ? $hyperlink->hyperlink_label : '';
						$hyperlink_html  = isset( $hyperlink->hyperlink_html ) ? $hyperlink->hyperlink_html : '';
						if ( $hyperlink_label !== '' && $hyperlink_html !== '' ) {
							// Add hyperlink columns (details are added in method column_default)
							$selected_columns .= ", '' as wpda_hyperlink_$i";
						}
					}
					$i ++;
				}
			}

			if ( '' === $this->schema_name ) {
				$query = "
					select $selected_columns
					from `{$this->table_name}`
					{$this->where}
				";
			} else {
				if ( $this->table_name === self::LIST_BASE_TABLE ) {
					$query = "
						select $selected_columns 
						from {$this->table_name}
						{$this->where}
					";
				} else {
					$query = "
						select $selected_columns
						from `{$wpdadb->dbname}`.`{$this->table_name}`
						{$this->where}
					";
				}
			}

			$query  .= $this->get_order_by(); // Add order by
			$query  .= " limit {$this->items_per_page}";
			$offset = ( $this->current_page - 1 ) * $this->items_per_page;
			if ( $offset > 0 ) {
				$query .= " offset $offset";
			}

			$this->items = $wpdadb->get_results( $query, 'ARRAY_A' ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
		}

		protected function get_order_by() {
			if ( ! empty( $_REQUEST['orderby'] ) ) {
				$orderby_arg = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ); // input var okay.

				if ( ! empty( $_REQUEST['order'] ) ) {
					$order_arg = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ); // input var okay.
				} else {
					$order_arg = '';
				}

				$columns = $this->get_sortable_columns();

				// Check column name for SQL injection.
				if ( isset( $columns[ $orderby_arg ] ) || $this->wpda_data_dictionary->column_exists( $orderby_arg ) ) {
					// Column name exists in current table, safely continue...
					$orderby = " order by $orderby_arg";

					// Prevent SQL injection for order. If 'desc' is found result wille be ordered desc. In all other
					// cases we'll order asc.
					$orderby .= strtolower( trim( $order_arg ) ) === 'desc' ? ' desc' : ' asc';

					return $orderby;
				} else {
					// The user provided a column name which is not in the table. Most probably the result of a
					// SQL injection attack, so let's terminate.
					wp_die( __( 'ERROR: Invalid URL [invalid column name]', 'wp-data-access' ) );
				}
			} else {
				return '';
			}
		}

		/**
		 * Return an associative array of columns
		 *
		 * @return array
		 * @since   1.0.0
		 *
		 */
		public function get_columns() {

			$columns = [];

			if ( $this->bulk_actions_enabled ) {
				if ( ! empty( $this->wpda_list_columns->get_table_primary_key() ) ) {
					// Tables has primary key: bulk actions allowed!
					// Primary key is used to ensure uniqueness.
					$actions = $this->get_bulk_actions();
					if ( is_array( $actions ) && 0 < sizeof( $actions ) ) {
						$columns = [ 'cb' => '<input type="checkbox" />' ];
					}
				}
			}

			$columnlist = $this->wpda_list_columns->get_table_column_headers();
			foreach ( $columnlist as $key => $value ) {
				$columns[ $key ] = $value;
				// Check for alternative column header.
				if ( isset( $this->column_headers[ $key ] ) ) {
					// Alternative header found: use it.
					$columns[ $key ] = $this->column_headers[ $key ];
				} else {
					// Default behaviour: get column header from generated label.
					$columns[ $key ] = $this->wpda_list_columns->get_column_label( $key );
				}
			}

			if ( isset( $this->wpda_table_settings->hyperlinks ) ) {
				$i = 0;
				foreach ( $this->wpda_table_settings->hyperlinks as $hyperlink ) {
					if ( isset( $hyperlink->hyperlink_list ) && true === $hyperlink->hyperlink_list ) {
						$skip_column = false;

						if ( null !== $this->wpda_project_table_settings ) {
							$hyperlink_label = $hyperlink->hyperlink_label;

							if ( ! property_exists( $this, 'is_child') ) {
								if (
									isset( $this->wpda_project_table_settings->hyperlinks_parent->$hyperlink_label ) &&
									! $this->wpda_project_table_settings->hyperlinks_parent->$hyperlink_label
								) {
									$skip_column = true;
								}
							} else {
								if (
									isset( $this->wpda_project_table_settings->hyperlinks_child->$hyperlink_label ) &&
									! $this->wpda_project_table_settings->hyperlinks_child->$hyperlink_label
								) {
									$skip_column = true;
								}
							}
						}

						if ( ! $skip_column ) {
							$hyperlink_label                = isset( $hyperlink->hyperlink_label ) ? $hyperlink->hyperlink_label : '';
							$columns["wpda_hyperlink_{$i}"] = $hyperlink_label; // Add hyperlink label
						}
					}
					$i++;
				}
			}

			return $columns;

		}

		/**
		 * List of columns to make sortable
		 *
		 * @return array
		 * @since   1.0.0
		 *
		 */
		public function get_sortable_columns() {

			$columns = [];

			// Get column names from result set.
			if ( $this->items ) {
				foreach ( $this->items[0] as $key => $value ) {

					$columns[ $key ] = [ $key, false ];

				}
			}

			return $columns;

		}

		/**
		 * Display the search box
		 *
		 * @param string $text The 'submit' button label.
		 * @param string $input_id ID attribute value for the search input field.
		 *
		 * @since   1.0.0
		 *
		 */
		public function search_box( $text, $input_id ) {
			$input_id = $input_id . '-search-input';

			// Allow external user to add search actions (like icons) to the search box
			do_action( 'wpda_add_search_actions', $this->schema_name, $this->table_name, $this->wpda_table_settings, $this->wpda_list_columns );
			?>

			<p class="search-box" <?php echo ! $this->has_items() ? 'style="padding-bottom:10px;"' : ''; ?>>
				<input type="search" id="<?php echo esc_attr( $input_id ); ?>"
					   name="<?php echo esc_attr( $this->search_item_name ); ?>"
					   value="<?php echo esc_attr( $this->search_value ); ?>"/>
				<?php
				if ( is_admin() ) {
					submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) );
				} else {
					wpdadiehard_submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) );
				}
				?>
				<input type="hidden" name="<?php echo esc_attr( $this->search_item_name ); ?>_old_value"
					   value="<?php echo esc_attr( $this->search_value ); ?>"/>
			</p>

			<?php
			// Allow external user to add search html (like extra items) below the search box
			do_action( 'wpda_add_search_filter', $this->schema_name, $this->table_name, $this->wpda_table_settings, $this->wpda_list_columns );
		}

		/**
		 * Get search value (entered by the user or taken from cookie).
		 *
		 * @return string
		 * @since   1.5.0
		 *
		 */
		protected function get_search_value() {
			if ( 'off' === WPDA::get_option( WPDA::OPTION_BE_REMEMBER_SEARCH ) ) {
				if ( isset( $_REQUEST[ $this->search_item_name ] ) ) {
					return wp_unslash( $_REQUEST[ $this->search_item_name ] ); // input var okay.
				}
			}

			$cookie_name = $this->page . '_search_' . str_replace( '.', '_', $this->table_name );
			if ( isset( $_REQUEST[ $this->search_item_name ] ) && '' !== $_REQUEST[ $this->search_item_name ] ) { // input var okay.
				return wp_unslash( $_REQUEST[ $this->search_item_name ] ); // input var okay.
			} elseif ( isset( $_COOKIE[ $cookie_name ] ) ) {
				return wp_unslash( $_COOKIE[ $cookie_name ] ); // input var okay.
			} else {
				return null;
			}
		}

		/**
		 * Print column headers
		 *
		 * Overriding original method print_column_headers to support post instead of get.
		 * Changes are marked!
		 *
		 * @param boolean $with_id Whether to set the id attribute or not.
		 *
		 * @since   1.0.0
		 *
		 * @staticvar $cb_counter int
		 *
		 */
		public function print_column_headers( $with_id = true ) {
			list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

			// *********************
			// *** BEGIN CHANGES ***
			// *********************
			// Code removed.
			// *******************
			// *** END CHANGES ***
			// *******************
			if ( isset( $_REQUEST['orderby'] ) ) {
				$current_orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ); // input var okay.
			} else {
				$current_orderby = '';
			}

			if ( isset( $_REQUEST['order'] ) && 'desc' === sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) { // input var okay.
				$current_order = 'desc';
			} else {
				$current_order = 'asc';
			}

			if ( ! empty( $columns['cb'] ) ) {
				static $cb_counter = 1;
				$columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
				                 . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
				$cb_counter ++;
			}

			foreach ( $columns as $column_key => $column_display_name ) {
				$class = [ 'manage-column', "column-$column_key" ];

				if ( in_array( $column_key, $hidden ) ) {
					$class[] = 'hidden';
				}

				if ( 'cb' === $column_key ) {
					$class[] = 'check-column';
				} elseif ( in_array( $column_key, [ 'posts', 'comments', 'links' ] ) ) {
					$class[] = 'num';
				}

				if ( $column_key === $primary ) {
					$class[] = 'column-primary';
				}

				if ( isset( $sortable[ $column_key ] ) ) {
					list( $orderby, $desc_first ) = $sortable[ $column_key ];

					if ( $current_orderby === $orderby ) {
						$order   = 'asc' === $current_order ? 'desc' : 'asc';
						$class[] = 'sorted';
						$class[] = $current_order;
					} else {
						$order   = $desc_first ? 'desc' : 'asc';
						$class[] = 'sortable';
						$class[] = $desc_first ? 'asc' : 'desc';
					}

					// *********************
					// *** BEGIN CHANGES ***
					// *********************
					// Code removed.
					// *******************
					// *** END CHANGES ***
					// *******************
				}

				$tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
				$scope = ( 'th' === $tag ) ? 'scope="col"' : '';
				$id    = $with_id ? "id='$column_key'" : '';

				if ( ! empty( $class ) ) {
					$class = "class='" . join( ' ', $class ) . "'";
				}

				// *********************
				// *** BEGIN CHANGES ***
				// *********************
				echo '<' . esc_attr( $tag ) . ' ' . wp_kses_data( $scope ) . ' ' . wp_kses_data( $id ) . ' ' .
				     wp_kses_data( $class ) . '>';

				if ( isset( $sortable[ $column_key ] ) ) {

					?>
					<a href="javascript:void(0)"
					   onclick="jQuery('#wpda_main_form_orderby').val('<?php echo esc_attr( $orderby ); ?>'); jQuery('#wpda_main_form_order').val('<?php echo esc_attr( $order ); ?>'); jQuery('#wpda_main_form').submit();">
						<span><?php echo wp_kses_data( $column_display_name ); ?></span><span
								class="sorting-indicator"></span>
					</a>
					<?php

				} else {
					echo wp_kses(
						$column_display_name,
						[
							'input' => [
								'id'   => [],
								'type' => [],
							],
							'label' => [
								'class' => [],
								'for'   => [],
							],
						]
					);
				}

				echo '</' . esc_attr( $tag ) . '>';

				// *******************
				// *** END CHANGES ***
				// *******************
			}
		}

		/**
		 * Returns an associative array containing the bulk action
		 *
		 * @return array
		 * @since   1.0.0
		 *
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

			if ( $this->allow_delete === 'on' ) {
				$actions = [
					'bulk-delete' => __( 'Delete Permanently', 'wp-data-access' ),
				];
			}

			if (
				$this->bulk_export_enabled && (
					WPDA::is_wpda_table( $this->table_name ) ||
					WPDA::get_option( WPDA::OPTION_BE_EXPORT_ROWS ) === 'on'
				)
			) {
				$actions['bulk-export']       = __( 'Export to SQL', 'wp-data-access' );
				$actions['bulk-export-xml']   = __( 'Export to XML', 'wp-data-access' );
				$actions['bulk-export-json']  = __( 'Export to JSON', 'wp-data-access' );
				$actions['bulk-export-excel'] = __( 'Export to Excel', 'wp-data-access' );
				$actions['bulk-export-csv']   = __( 'Export to CSV', 'wp-data-access' );
			}

			return $actions;

		}

		/**
		 * Generates the table navigation
		 *
		 * Generates the table navigation above or bellow the table and removes the
		 * _wp_http_referrer and _wpnonce because it generates a error about URL too large.
		 *
		 * @param string $which CSS Class name.
		 *
		 * @return void
		 * @since   1.0.0
		 *
		 */
		protected function display_tablenav( $which ) {
			if ( ! $this->hide_navigation && $this->items ) {

				?>
				<div class="tablenav <?php echo esc_attr( $which ); ?>">
					<div class="alignleft actions">
						<?php $this->bulk_actions( $which ); ?>
					</div>
					<?php
					$this->extra_tablenav( $which );
					$this->pagination( $which );
					?>
					<br class="clear"/>
				</div>
				<?php
			} else {
				?>
				<br class="clear"/>
				<?php
			}

		}

		/**
		 * Display the pagination
		 *
		 * Overriding original method pagination to support post instead of get.
		 * Changes are marked!
		 *
		 * @param string $which CSS Class name.
		 *
		 * @since   1.0.0
		 *
		 */
		protected function pagination( $which ) {
			if ( empty( $this->_pagination_args ) ) {
				return;
			}

			$total_items     = $this->_pagination_args['total_items'];
			$total_pages     = $this->_pagination_args['total_pages'];
			$infinite_scroll = false;
			if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
				$infinite_scroll = $this->_pagination_args['infinite_scroll'];
			}

			if ( 'top' === $which && $total_pages > 1 ) {
				$this->screen->render_screen_reader_content( 'heading_pagination' );
			}

			// Add estimate character if row_count_estimate is enabled
			$estimate = 'InnoDB' == $this->table_engine && $this->table_rows > -1 ? '~' : '';

			/* translators: %s: number of items (2x) */
			$output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), $estimate . number_format_i18n( $total_items ) ) . '</span>';

			$current = $this->get_pagenum();
			if ( $this->search_value != $this->search_value_old ) {
				$current = 1;
			}

			// *********************
			// *** BEGIN CHANGES ***
			// *********************
			// Code removed.
			// *******************
			// *** END CHANGES ***
			// *******************
			$page_links = array();

			$total_pages_before = '<span class="paging-input">';
			$total_pages_after  = '</span></span>';

			$disable_first = $disable_last = $disable_prev = $disable_next = false;

			if ( 1 === (int) $current ) {
				$disable_first = true;
				$disable_prev  = true;
			}
			if ( 2 === (int) $current ) {
				$disable_first = true;
			}
			if ( (int) $current === (int) $total_pages ) {
				$disable_last = true;
				$disable_next = true;
			}
			if ( (int) $current === (int) $total_pages - 1 ) {
				$disable_last = true;
			}

			// *********************
			// *** BEGIN CHANGES ***
			// *********************
			$link_with_post_support = "
            <a class='%s' 
                href='javascript:void(0)' 
                onclick='jQuery(\"#current-page-selector\").val(\"%s\"); jQuery(\"#wpda_main_form\").submit();'>
                <span class='screen-reader-text'>%s</span>
                <span aria-hidden='true'>%s</span>
            </a>";
			// *******************
			// *** END CHANGES ***
			// *******************
			if ( $disable_first ) {
				$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
			} else {
				// *********************
				// *** BEGIN CHANGES ***
				// *********************
				$page_links[] = sprintf(
					$link_with_post_support,
					'first-page button',
					'',
					__( 'First page' ),
					'&laquo;'
				);
				// *******************
				// *** END CHANGES ***
				// *******************
			}

			if ( $disable_prev ) {
				$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
			} else {
				// *********************
				// *** BEGIN CHANGES ***
				// *********************
				$page_links[] = sprintf(
					$link_with_post_support,
					'prev-page button',
					max( 1, $current - 1 ),
					__( 'Previous page' ),
					'&lsaquo;'
				);
				// *******************
				// *** END CHANGES ***
				// *******************
			}

			if ( 'bottom' === $which ) {
				$html_current_page  = $current;
				$total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
			} else {
				$html_current_page = sprintf(
					"%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
					'<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
					$current,
					strlen( $total_pages )
				);
			}
			$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
			/* translators: %s: current page/total pages */
			$page_links[] = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

			if ( $disable_next ) {
				$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
			} else {
				// *********************
				// *** BEGIN CHANGES ***
				// *********************
				$page_links[] = sprintf(
					$link_with_post_support,
					'next-page button',
					min( $total_pages, $current + 1 ),
					__( 'Next page' ),
					'&rsaquo;'
				);
				// *******************
				// *** END CHANGES ***
				// *******************
			}

			if ( $disable_last ) {
				$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
			} else {
				// *********************
				// *** BEGIN CHANGES ***
				// *********************
				$page_links[] = sprintf(
					$link_with_post_support,
					'last-page button',
					$total_pages,
					__( 'Last page' ),
					'&raquo;'
				);
				// *******************
				// *** END CHANGES ***
				// *******************
			}

			$pagination_links_class = 'pagination-links';
			if ( ! empty( $infinite_scroll ) ) {
				$pagination_links_class = ' hide-if-js';
			}
			$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

			if ( $total_pages ) {
				$page_class = $total_pages < 2 ? ' one-page' : '';
			} else {
				$page_class = ' no-pages';
			}
			$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

			echo $this->_pagination;
		}

	}

}
