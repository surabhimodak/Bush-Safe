<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess
 */

namespace WPDataAccess {

	use Cassandra\Schema;
	use WP_Data_Access_Admin;
	use WPDataAccess\Connection\WPDADB;
	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataProjects\WPDP;

	/**
	 * Class WPDA
	 *
	 * Plugin default values and settings are managed through this class. Every plugin option has a default value
	 * which is maintained in an array together with the option name. Options are only saved in $wpdb->options when
	 * they are changed. Otherwise the default values are used. After reading option values from $wpdb->options the
	 * values are cached as many of them are used in multiple
	 * places during the processing of a request.
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA {

		/**
		 * Label for WordPress tables
		 */
		const TABLE_TYPE_WP = 'wordpress table';
		/**
		 * Label for WPDA plugin tables
		 */
		const TABLE_TYPE_WPDA = 'plugin table';

		// Options are stored in arrays (OPTION_ARRAYS):
		// [0] = option name (as saved in wp_options).
		// [1] = default value (used if option is not available in wp_options).
		// Application options.
		/**
		 * Option wpda_version and it's default value
		 */
		const OPTION_WPDA_VERSION = [ 'wpda_version', '4.2.1' ];
		/**
		 * Option wpda_setup_error and it's default value
		 */
		const OPTION_WPDA_SETUP_ERROR = [
			'wpda_setup_error',
			''
		]; // Values: off = do not display setup errors, other = show.
		/**
		 * Option wpda_show_whats_new and it's default value
		 */
		const OPTION_WPDA_SHOW_WHATS_NEW = [
			'wpda_show_whats_new',
			'off'
		]; // Values: off = hide link to what's new page, other = show.
		/**
		 * Option wpda_uninstall_tables and it's default value
		 */
		const OPTION_WPDA_UNINSTALL_TABLES = [ 'wpda_uninstall_tables', 'off' ]; // On uninstall drop WPDA tables.
		/**
		 * Option wpda_uninstall_options and it's default value
		 */
		const OPTION_WPDA_UNINSTALL_OPTIONS = [ 'wpda_uninstall_options', 'off' ]; // On uninstall delety WPDA options.
		/**
		 * Option wpda_datatables_version and it's default value
		 */
		const OPTION_WPDA_DATATABLES_VERSION = [ 'wpda_datatables_version', '1.10.20' ];
		/**
		 * Option wpda_datatables_responsive_version and it's default value
		 */
		const OPTION_WPDA_DATATABLES_RESPONSIVE_VERSION = [ 'wpda_datatables_responsive_version', '2.2.3' ];

		const OPTION_PLUGIN_HIDE_ADMIN_MENU = [ 'wpda_plugin_hide_admin_menu', 'off' ];

		const OPTION_PLUGIN_PANEL_COOKIES = [ 'wpda_plugin_panel_cookies', 'clear' ];

		const OPTION_PLUGIN_SECRET_KEY_DEFAULT = 'enter-your-secret-key-here';
		const OPTION_PLUGIN_SECRET_IV_DEFAULT  = 'enter-your-secret-iv-here';

		const OPTION_PLUGIN_SECRET_KEY = [ 'wpda_plugin_secret_key', self::OPTION_PLUGIN_SECRET_KEY_DEFAULT ];
		const OPTION_PLUGIN_SECRET_IV  = [ 'wpda_plugin_secret_iv', self::OPTION_PLUGIN_SECRET_IV_DEFAULT ];

		const OPTION_PLUGIN_WPDATAACCESS_POST = [ 'wpda_plugin_wpdataaccess_post', 'on' ];
		const OPTION_PLUGIN_WPDATAACCESS_PAGE = [ 'wpda_plugin_wpdataaccess_page', 'on' ];
		const OPTION_PLUGIN_WPDADIEHARD_POST  = [ 'wpda_plugin_wpdadiehard_post', 'on' ];
		const OPTION_PLUGIN_WPDADIEHARD_PAGE  = [ 'wpda_plugin_wpdadiehard_page', 'on' ];
		const OPTION_PLUGIN_WPDADATAFORMS_POST = [ 'wpda_plugin_wpdadataforms_post', 'on' ];
		const OPTION_PLUGIN_WPDADATAFORMS_PAGE = [ 'wpda_plugin_wpdadataforms_page', 'on' ];
		const OPTION_PLUGIN_WPDADATAFORMS_ALLOW_ANONYMOUS_ACCESS = [ 'wpda_plugin_wpdadataforms_allow_anonymous_access', 'off' ];
		const OPTION_PLUGIN_WPDADATAFORMS_DEBUG_MODE = [ 'wpda_plugin_wpdadataforms_debug_mode', 'off' ];
		const OPTION_PLUGIN_WPDAREPORT_POST = [ 'wpda_plugin_wpdareport_post', 'on' ];
		const OPTION_PLUGIN_WPDAREPORT_PAGE = [ 'wpda_plugin_wpdareport_page', 'on' ];

		// MySQL date and time formats
		const DB_DATE_FORMAT     = 'Y-m-d';
		const DB_TIME_FORMAT     = 'H:i:s';
		const DB_DATETIME_FORMAT = 'Y-m-d H:i:s';

		// Plugin options. (date and time settings)
		const OPTION_PLUGIN_DATE_FORMAT      = [ 'wpda_plugin_date_format', 'Y-m-d' ];
		const OPTION_PLUGIN_DATE_PLACEHOLDER = [ 'wpda_plugin_date_placeholder', 'yyyy-mm-dd' ];
		const OPTION_PLUGIN_TIME_FORMAT      = [ 'wpda_plugin_time_format', 'H:i' ];
		const OPTION_PLUGIN_TIME_PLACEHOLDER = [ 'wpda_plugin_time_placeholder', 'hh:mi' ];
		const OPTION_PLUGIN_SET_FORMAT       = [ 'wpda_plugin_set_format', 'csv' ];

		// Back-end options.
		/**
		 * Option wpda_be_load_datatables and it's default value
		 */
		const OPTION_BE_LOAD_DATATABLES = [ 'wpda_be_load_datatables', 'on' ];
		/**
		 * Option wpda_be_load_datatables_response and it's default value
		 */
		const OPTION_BE_LOAD_DATATABLES_RESPONSE = [ 'wpda_be_load_datatables_response', 'on' ];
		/**
		 * Option wpda_be_table_access and it's default value
		 */
		const OPTION_BE_TABLE_ACCESS = [ 'wpda_be_table_access', 'show' ]; // Values: show, hide and select.
		/**
		 * Option wpda_be_table_access_selected and it's default value
		 */
		const OPTION_BE_TABLE_ACCESS_SELECTED = [ 'wpda_be_table_access_selected', '' ]; // Tables authorized in WPDA.
		/**
		 * Option wpda_be_view_link and it's default value
		 */
		const OPTION_BE_VIEW_LINK = [ 'wpda_be_view_link', 'on' ]; // Show view link in list table.
		/**
		 * Option wpda_be_allow_insert and it's default value
		 */
		const OPTION_BE_ALLOW_INSERT = [
			'wpda_be_allow_insert',
			'on'
		]; // Show insert link in list table (simple form).
		/**
		 * Option wpda_be_allow_update and it's default value
		 */
		const OPTION_BE_ALLOW_UPDATE = [
			'wpda_be_allow_update',
			'on'
		]; // Show update link in list table (simple form).
		/**
		 * Option wpda_be_allow_delete and it's default value
		 */
		const OPTION_BE_ALLOW_DELETE = [ 'wpda_be_allow_delete', 'on' ]; // Show delete link in list table.
		/**
		 * Option wpda_be_wpda_export_rows and it's default value
		 */
		const OPTION_BE_EXPORT_ROWS = [
			'wpda_be_wpda_export_rows',
			'on'
		]; // Show export link in list table (row export).
		/**
		 * Option wpda_be_wpda_export_variable_prefix and it's default value
		 */
		const OPTION_BE_EXPORT_VARIABLE_PREFIX = [
			'wpda_be_wpda_export_variable_prefix',
			'on'
		]; // Allows to import into repository with different wpdb prefix.
		/**
		 * Option wpda_be_wpda_allow_imports and it's default value
		 */
		const OPTION_BE_ALLOW_IMPORTS = [ 'wpda_be_wpda_allow_imports', 'on' ]; // Allow to import data (main only).
		/**
		 * Option wpda_be_confirm_export and it's default value
		 */
		const OPTION_BE_CONFIRM_EXPORT = [ 'wpda_be_confirm_export', 'on' ]; // Ask for confirmation before exporting.
		/**
		 * Option wpda_be_confirm_view and it's default value
		 */
		const OPTION_BE_CONFIRM_VIEW = [ 'wpda_be_confirm_view', 'on' ]; // Ask for confirmation before viewing.
		/**
		 * Option wpda_be_pagination and it's default value
		 */
		const OPTION_BE_PAGINATION = [ 'wpda_be_pagination', '10' ];
		/**
		 * Option wpda_be_remember_search and it's default value
		 */
		const OPTION_BE_REMEMBER_SEARCH = [ 'wpda_be_remember_search', 'on' ];
		/**
		 * Option wpda_be_innodb_count and it's default value
		 */
		const OPTION_BE_INNODB_COUNT = [ 'wpda_be_innodb_count', 100000 ];
		/**
		 * Option wpda_be_design_mode and it's default value
		 */
		const OPTION_BE_DESIGN_MODE = [ 'wpda_be_design_mode', 'advanced' ]; // Default design mode (basic/advanced).
		/**
		 * Option wpda_be_text_wrap_switch and it's default value
		 */
		const OPTION_BE_TEXT_WRAP_SWITCH = [ 'wpda_be_text_wrap_switch', 'off' ];
		/**
		 * Option wpda_be_text_wrap and it's default value
		 */
		const OPTION_BE_TEXT_WRAP = [ 'wpda_be_text_wrap', 400 ];
		/**
		 * Option wpda_be_debug and it's default value
		 */
		const OPTION_BE_DEBUG = [ 'wpda_be_debug', 'off' ];

		// Front-end options.
		/**
		 * Option wpda_fe_load_datatables and it's default value
		 */
		const OPTION_FE_LOAD_DATATABLES = [ 'wpda_fe_load_datatables', 'on' ];
		/**
		 * Option wpda_fe_load_datatables_response and it's default value
		 */
		const OPTION_FE_LOAD_DATATABLES_RESPONSE = [ 'wpda_fe_load_datatables_response', 'on' ];
		/**
		 * Option wpda_fe_table_access and it's default value
		 */
		const OPTION_FE_TABLE_ACCESS = [ 'wpda_fe_table_access', 'select' ]; // Values: hide, show, select.
		/**
		 * Option wpda_fe_table_access_selected and it's default value
		 */
		const OPTION_FE_TABLE_ACCESS_SELECTED = [ 'wpda_fe_table_access_selected', '' ]; // Tables authorized in WPDA.
		/**
		 * Option wpda_fe_pagination and it's default value
		 */
		const OPTION_FE_PAGINATION = [ 'wpda_fe_pagination', '10' ];

		// Manage Repository options.
		/**
		 * Option wpda_mr_keep_backup_tables and it's default value
		 */
		const OPTION_MR_KEEP_BACKUP_TABLES = [ 'wpda_mr_keep_backup_tables', 'on' ];

		// Data Backup options.
		/**
		 * Option wpda_db_local_path and it's default value
		 */
		const OPTION_DB_LOCAL_PATH = [ 'wpda_db_local_path', '' ];
		/**
		 * Option wpda_db_dropbox_path and it's default value
		 */
		const OPTION_DB_DROPBOX_PATH = [ 'wpda_db_dropbox_path', '/wp-data-access/' ];

		// Data Publisher options.
		/**
		 * Option wpda_dp_publication_roles and it's default value
		 */
		const OPTION_DP_PUBLICATION_ROLES = [ 'wpda_dp_publication_roles', '' ];
		const OPTION_DP_LANGUAGE          = [ 'wpda_dp_language', 'English' ];
		const OPTION_DP_JSON_EDITING      = [ 'wpda_dp_json_editing', 'validate' ];

		// Role Management
		const OPTION_WPDA_ENABLE_ROLE_MANAGEMENT = [ 'wpda_rm_enable_role_management', 'off' ];
		const OPTION_WPDA_USE_ROLES_IN_SHORTCODE = [ 'wpda_rm_use_roles_in_shortcode', 'off' ];

		// Prefixes to store backend access for non WP databases
		const BACKEND_OPTIONNAME_DATABASE_ACCESS   = 'WPDA_BE_TABLE_ACCESS_';
		const BACKEND_OPTIONNAME_DATABASE_SELECTED = 'WPDA_BE_TABLE_SELECTED_';

		// Prefixes to store frontend access for non WP databases
		const FRONTEND_OPTIONNAME_DATABASE_ACCESS   = 'WPDA_FE_TABLE_ACCESS_';
		const FRONTEND_OPTIONNAME_DATABASE_SELECTED = 'WPDA_FE_TABLE_SELECTED_';

		const WPDA_DT_UI_THEME_DEFAULT   = [ 'wpda_dt_ui_theme_default', 'smoothness' ];

		/**
		 * List of plugin tables
		 */
		const WPDA_TABLES = [
			'wpda_logging'        => true,
			'wpda_menus'          => true,
			'wpda_table_settings' => true,
			'wpda_table_design'   => true,
			'wpda_publisher'      => true,
			'wpda_media'          => true,
			'wpda_project'        => true,
			'wpda_project_page'   => true,
			'wpda_project_table'  => true,
			'wpda_csv_uploads'    => true,
			'wpda_pro_reports'	  => true,
		];

		/**
		 * List containing all plugin tables
		 *
		 * @var array
		 */
		static protected $wpda_tables = [];

		// Option values once queried from wp_options are stored in an array for re-use during request to prevent
		// executing same query on wp_options multiple times.
		/**
		 * Options cache array
		 *
		 * @var array
		 */
		static protected $option_cache = [];

		/**
		 * List containing all WordPress tables
		 *
		 * @var array
		 */
		static protected $wp_tables = [];

		static protected $plugin_pages = [
			WP_Data_Access_Admin::PAGE_MAIN,
			WP_Data_Access_Admin::PAGE_SETTINGS,
			WP_Data_Access_Admin::PAGE_EXPLORER,
			WP_Data_Access_Admin::PAGE_PUBLISHER,
			WP_Data_Access_Admin::PAGE_DESIGNER,
			WP_Data_Access_Admin::PAGE_MY_TABLES,
			WPDP::PAGE_MAIN,
			WPDP::PAGE_TEMPLATES,
		];

		public static function is_plugin_page( $page ) {
			return (
				'wpda_wpdp_' === substr( $page, 0, 10 ) ||
				WP_Data_Access_Admin::PAGE_EXPLORER === substr( $page, 0, 13 ) ||
				in_array( $page, self::$plugin_pages)
			);
		}

		public static function register_plugin_page( $page ) {
			array_push( self::$plugin_pages, $page );
		}

		/**
		 * Translated table label
		 *
		 * Returns a translated table label. The label depends on the type of table. Provided through a function to
		 * support internationalization.
		 *
		 * @param string $table_type Table type (use WPDA constants).
		 *
		 * @return string Translated table type.
		 * @since   1.0.0
		 *
		 */
		public static function get_table_type_text( $table_type ) {

			switch ( $table_type ) {

				case self::TABLE_TYPE_WP:
					return __( 'WordPress table', 'wp-data-access' );

				case self::TABLE_TYPE_WPDA:
					return __( 'plugin table', 'wp-data-access' );

				default:
					return $table_type;

			}

		}

		/**
		 * Get plugin option values
		 *
		 * Get value for pluginoption saved in wp_options. If option is not found in $wpdb->options the default value
		 * for that option is returned. Option values once taken from $wpdb->options are cached to prevent execution
		 * of the same query multiple times during teh processing of a request.
		 *
		 * @param array $option OPTION_ARRAY (use class constants).
		 *
		 * @return mixed Value for OPTION_ARRAY ($option): (1) cached value (2) wp_options value (3) default value.
		 * @since   1.0.0
		 *
		 */
		public static function get_option( $option ) {

			if ( isset( self::$option_cache[ $option[0] ] ) ) {
				return self::$option_cache[ $option[0] ]; // Re-use cached value.
			}

			$option_value = get_option( $option[0] );
			if ( ! $option_value ) {
				// Option not found in wp_options: save default value for re-use.
				self::$option_cache[ $option[0] ] = $option[1];
			} else {
				// Option found in wp_options: save for re-use.
				self::$option_cache[ $option[0] ] = $option_value;
			}

			return self::$option_cache[ $option[0] ]; // Return saved value.

		}

		/**
		 * Delete all plugin options from table wp_options
		 *
		 * @since   1.0.0
		 */
		public static function clear_all_options() {

			global $wpdb;

			$wpdb->query(
				"
				DELETE FROM wp_options
				WHERE option_name LIKE 'wpda_%'
			"
			); // db call ok; no-cache ok.

			self::$option_cache = []; // Reset cache.

		}

		/**
		 * Load all WordPress tables
		 *
		 * @since   1.1.0
		 */
		public static function load_wp_tables() {

			if ( 0 === count( self::$wp_tables ) ) {
				try {
					global $wpdb;

					if ( ! is_multisite() ) {
						foreach ( $wpdb->tables( 'all', true ) as $table ) {
							self::$wp_tables[ $table ] = $table;
						}
					} else {
						$query = "select blog_id from {$wpdb->blogs}";
						$blogs = $wpdb->get_results( $query, 'ARRAY_N' );
						foreach ( $blogs as $blog ) {
							foreach ( $wpdb->tables( $blog === reset( $blogs ) ? 'all' : 'blog', true, $blog[0] ) as $table ) {
								self::$wp_tables[ $table ] = $table;
							}
						}
					}

					return true;
				} catch ( \Exception $e ) {
					wp_die( 'ERROR: ' . $e->getMessage() );
				}
			}

		}

		/**
		 * Checks if a table is a WordPress table
		 *
		 * @param string $table_name Table name.
		 *
		 * @return bool TRUE = WordPress table
		 * @since   1.1.0
		 *
		 */
		public static function is_wp_table( $table_name ) {

			self::load_wp_tables();

			if ( 0 === count( self::$wp_tables ) ) {
				return false;
			}

			return isset( self::$wp_tables[ $table_name ] );

		}

		/**
		 * List containing all WordPress tables
		 *
		 * @return array
		 * @since   1.1.0
		 *
		 */
		public static function get_wp_tables() {

			self::load_wp_tables();

			if ( 0 === count( self::$wp_tables ) ) {
				wp_die( __( 'ERROR: No WordPress table found', 'wp-data-access' ) );
			}

			return self::$wp_tables;

		}

		/**
		 * Returns a list of all plugin tables
		 *
		 * @return array List of plugin tables
		 * @since   1.0.0
		 *
		 */
		public static function get_wpda_tables() {

			global $wpdb;
			$wpda_tables = [];

			foreach ( self::WPDA_TABLES as $key => $value ) {
				$wpda_tables[] = $wpdb->prefix . $key;
			}

			return $wpda_tables;

		}

		/**
		 * Check if repository column represents a schema name
		 *
		 * @param string $table_name Table name
		 * @param string $column_name Column name
		 *
		 * @return bool
		 */
		public static function column_is_schema_name( $table_name, $column_name ) {
			if ( 0 === sizeof( self::$wpda_tables ) ) {
				// Cache schema names
				global $wpdb;
				self::$wpda_tables[$wpdb->prefix . 'wpda_media.media_schema_name' ] = true;
				self::$wpda_tables[$wpdb->prefix . 'wpda_project_page.page_schema_name' ] = true;
				self::$wpda_tables[$wpdb->prefix . 'wpda_menus.menu_schema_name' ] = true;
				self::$wpda_tables[$wpdb->prefix . 'wpda_project_table.wpda_schema_name' ] = true;
				self::$wpda_tables[$wpdb->prefix . 'wpda_publisher.pub_schema_name' ] = true;
				self::$wpda_tables[$wpdb->prefix . 'wpda_table_design.wpda_schema_name' ] = true;
				self::$wpda_tables[$wpdb->prefix . 'wpda_table_settings.wpda_schema_name' ] = true;
			}

			return isset( self::$wpda_tables[ "{$table_name}.{$column_name}" ] );
		}

		/**
		 * Return the default value for a plugin option
		 *
		 * @param array $option OPTION_ARRAY (use class constants).
		 *
		 * @return mixed Default value for OPTION_ARRAY ($option).
		 * @since   1.0.0
		 *
		 */
		public static function get_option_default( $option ) {

			return $option[1]; // Default option value.

		}

		/**
		 * Checks if table is plugin table
		 *
		 * NOTE
		 * Variable $phpdoc_supported_solution is a temporary variable that does not add any functionality to this
		 * function. It only serves the purpose to get class WPDA in the documentation!!! If the isset statement in
		 * the return is performed directly on self::WPDA_TABLES, class WPDA will not appear in the phpdoc generated
		 * documentation. To avoid class WPDA to be undocumented, we use $phpdoc_supported_solution.
		 *
		 * @param string $real_table_name Table name to be checked.
		 *
		 * @return bool TRUE = $table_name is a WPDA table, FALSE = $table_name is not a WPDA table.
		 * @since   1.0.0
		 *
		 */
		public static function is_wpda_table( $real_table_name ) {
			if ( null === $real_table_name ) {
				return false;
			}

			global $wpdb;

			$phpdoc_supported_solution = self::WPDA_TABLES; // DO NOT DELETE THIS TO MAKE THE CODE SIMPLER!!! (read).

			return
				(
					isset(
						$phpdoc_supported_solution[ substr( $real_table_name, strlen( $wpdb->prefix ) ) ]
					) && (
						$wpdb->prefix === substr( $real_table_name, 0, strlen( $wpdb->prefix ) )
					)
				);
		}

		/**
		 * Save plugin option
		 *
		 * Saves a plugin option in $wpdb->options.
		 *
		 * @param array $option OPTION_ARRAYS (use class constants).
		 * @param mixed $value Value to be saved for $option. If null set to default.
		 *
		 * @since   1.0.0
		 *
		 */
		public static function set_option( $option, $value = null ) {

			try {

				if ( is_null( $value ) ) {
					$option_value = $option[1]; // Set option to default.
				} else {
					$option_value = $value; // Set option value.
				}

				update_option( $option[0], $option_value ); // Save option value in wp_options.

				self::$option_cache[ $option[0] ] = $option_value; // Save for re-use.

			} catch ( \Exception $e ) {

				die( 'ERROR: ' . esc_html( $e->errorMessage() ) );

			}

		}

		/**
		 * Simplify data type for simple forms
		 *
		 * Data types used in plugin a re simplified for simple form usage.
		 *
		 * @param string $arg Data type as known to the MySQL database.
		 *
		 * @return string Simplified data type (mainly used to recognize when to use quotes).
		 * @since   1.0.0
		 *
		 * @see \WPDataAccess\Simple_Form\WPDA_Simple_Form
		 *
		 */
		public static function get_type( $arg ) {

			switch ( $arg ) {

				case 'tinyint':
				case 'smallint':
				case 'mediumint':
				case 'int':
				case 'bigint':
				case 'float':
				case 'double':
				case 'decimal':
				case 'year':
					return 'number';

				case 'date':
				case 'datetime':
				case 'timestamp':
					return 'date';

				case 'time':
					return 'time';

				case 'enum':
					return 'enum';

				case 'set':
					return 'set';

				default:
					return 'string';

			}

		}

		/**
		 * Log a message in the database
		 *
		 * Use this method to log messages to the database.
		 *
		 * NOTE Don't use $wpdb->insert! You'll miss a lot of information...
		 *
		 * @param $log_id   string Id to identify/find logged data.
		 * @param $log_type string Possible values: 'FATAL', 'ERROR', 'WARN', 'INFO', 'DEBUG', 'TRACE'
		 * @param $log_msg  string Any text (max length 4096kb).
		 *
		 * @since   2.0.7
		 *
		 */
		public static function log( $log_id, $log_type, $log_msg ) {
			global $wpdb;

			$sql =
				$wpdb->prepare(
					'INSERT INTO ' . $wpdb->prefix .
					'wpda_logging (log_time, log_id, log_type, log_msg) VALUES (now(), %s, %s, %s)'
					, $log_id
					, $log_type
					, $log_msg
				);
			$wpdb->query( $sql );

		}

		/**
		 * Get user role
		 *
		 * @return mixed Current user roles or FALSE if not logged in.
		 * @since   2.0.8
		 *
		 */
		public static function get_current_user_roles() {
			if ( is_user_logged_in() ) {
				$user = wp_get_current_user();
				if ( ! is_array( $user->roles ) ) {
					return ( array ) $user->roles;
				} else {
					return $user->roles;
				}
			} else {
				return false;
			}
		}

		/**
		 * Get user capability
		 *
		 * @return mixed Current users first capability or FALSE if not logged in.
		 * @since   2.0.8
		 *
		 */
		public static function get_current_user_capability() {
			if ( is_user_logged_in() ) {
				$user    = wp_get_current_user();
				$allcaps = [];
				foreach ( $user->allcaps as $key => $val ) {
					array_push( $allcaps, $key );
				}

				return $allcaps[0];
			} else {
				return false;
			}
		}

		/**
		 * Convert memory value to integer.
		 *
		 * @param $memory_value string Memory value (eg 128M)
		 *
		 * @return integer Converted value in decimal
		 * @since   2.0.8
		 *
		 */
		public static function convert_memory_to_decimal( $memory_value ) {
			if ( preg_match( '/^(\d+)(.)$/', $memory_value, $matches ) ) {
				if ( $matches[2] == 'G' ) {
					return $matches[1] * 1024 * 1024 * 1024;
				} else if ( $matches[2] == 'M' ) {
					return $matches[1] * 1024 * 1024;
				} else if ( $matches[2] == 'K' ) {
					return $matches[1] * 1024;
				}
			}
		}

		public static function get_current_user_id() {
			global $current_user;
			if ( isset( $current_user->ID ) ) {
				return $current_user->ID;
			} else {
				$wp_user = wp_get_current_user();
				if ( isset( $wp_user->ID ) ) {
					return $wp_user->ID;
				} else {
					return - 1;
				}
			}
		}

		public static function get_current_user_login() {
			global $current_user;
			if ( isset( $current_user->user_login ) ) {
				return $current_user->user_login;
			} else {
				$wp_user = wp_get_current_user();
				if ( isset( $wp_user->data->user_login ) ) {
					return $wp_user->data->user_login;
				} else {
					return 'anonymous';
				}
			}
		}

		public static function get_current_user_email() {
			global $current_user;
			if ( isset( $current_user->user_email ) ) {
				return $current_user->user_email;
			} else {
				$wp_user = wp_get_current_user();
				if ( isset( $wp_user->data->user_email ) ) {
					return $wp_user->data->user_email;
				} else {
					return 'anonymous';
				}
			}
		}

		/**
		 * Replaces environment variables in where clause with appropriate values
		 *
		 * @param $where_clause
		 *
		 * @return string
		 */
		public static function substitute_environment_vars( $where_clause ) {
			if ( strpos( $where_clause, '$$USERID$$' ) ) {
				$user_id      = WPDA::get_current_user_id();
				$where_clause = str_replace( '$$USERID$$', $user_id, $where_clause );
			}

			if ( strpos( $where_clause, '$$USER$$' ) ) {
				$user_login   = WPDA::get_current_user_login();
				$where_clause = str_replace( '$$USER$$', "'" . $user_login . "'", $where_clause );
			}

			if ( strpos( $where_clause, '$$EMAIL$$' ) ) {
				$user_email      = WPDA::get_current_user_email();
				$where_clause = str_replace( '$$EMAIL$$', $user_email, $where_clause );
			}

			return $where_clause;
		}

		/**
		 * Where clause construction (use filter if applied)
		 *
		 * Param $columns = associative array containing the following column info taken from the data dictionary:
		 *  $columns['column_name']    => information_schema.tables.column_name
		 *  $columns['data_type']      => information_schema.tables.data_type
		 * 	$columns['extra']          => information_schema.tables.extra
		 * 	$columns['column_type'     => information_schema.tables.column_type
		 * 	$columns['is_nullable'     => information_schema.tables.is_nullable
		 * 	$columns['column_default'] => information_schema.tables.column_default
		 *
		 * @param string $schema_name Schema name (optional)
		 * @param string $table_name Table name (optional)
		 * @param string $columns Array containing table columns
		 * @param string $search Search string entered by user
		 *
		 * @return string Where clause between ()
		 */
		public static function construct_where_clause( $schema_name, $table_name, $columns, $search ) {
			$where_search_args = wpda::add_wpda_search_args( $columns );

			if ( has_filter('wpda_construct_where_clause') ) {
				// Use search filter
				$filter = apply_filters(
					'wpda_construct_where_clause',
					'',
					$schema_name,
					$table_name,
					$columns,
					$search
				);
				if ( null !== $filter ) {
					if ( '' === $where_search_args ) {
						return $filter;
					} else {
						if ( '' === $filter ) {
							return $where_search_args;
						} else {
							return "{$filter} and {$where_search_args}";
						}
					}
				}
			}

			// Default search behaviour
			if ( '' === $search || null === $search || ! is_array( $columns ) ) {
				return $where_search_args;
			}

			global $wpdb;
			$where_columns = [];

			foreach ( $columns as $column ) {
				if ( 'string' === WPDA::get_type( $column['data_type'] ) ) {
					$where_columns[] = $wpdb->prepare( "`{$column['column_name']}` like '%s'", '%' . esc_sql( $search ) . '%' ); // WPCS: unprepared SQL OK.
				}
			}

			if ( 0 === count( $where_columns ) ) {
				return '' === $where_search_args ? ' (1=2) ' : $where_search_args;
			}

			if ( '' === $where_search_args ) {
				return ' (' . implode( ' or ', $where_columns ) . ') ';
			} else {
				return ' (' . implode( ' or ', $where_columns ) . ') ' . "and {$where_search_args}";
			}
		}

		public static function add_wpda_search_args( $columns ) {
			$where_columns = [];

			if ( is_array( $columns ) ) {
				global $wpdb;
				foreach ( $columns as $column ) {
					$column_name = $column['column_name'];
					if ( isset( $_REQUEST["wpda_search_column_{$column_name}"] ) ) {
						if ( is_array( $_REQUEST["wpda_search_column_{$column_name}"] ) ) {
							// Handle multiple values for same column with OR
							$where_columns_arr = [];
							foreach ( $_REQUEST["wpda_search_column_{$column_name}"] as $value ) {
								$column_date_type = $column['data_type'];
								$column_value     = sanitize_text_field( wp_unslash( urldecode( $value ) ) ); // input var okay.
								if ( '' !== $column_value ) {
									if ( 'string' === WPDA::get_type( $column_date_type ) ) {
										$where_columns_arr[] = $wpdb->prepare( "`{$column_name}` like '%s'", esc_sql( $column_value ) ); // WPCS: unprepared SQL OK.
									} elseif ( 'number' === WPDA::get_type( $column_date_type ) ) {
										$where_columns_arr[] = $wpdb->prepare( "`{$column_name}` = '%d'", esc_sql( $column_value ) ); // WPCS: unprepared SQL OK.
									}
								}
							}
							if ( sizeof( $where_columns_arr ) > 0 ) {
								$where_columns[] = ' (' . implode( ' or ', $where_columns_arr ) . ') ';
							}
						} else {
							// Handle single value
							$column_date_type = $column['data_type'];
							$column_value     = sanitize_text_field( wp_unslash( urldecode( $_REQUEST["wpda_search_column_{$column_name}"] ) ) ); // input var okay.
							if ( '' !== $column_value ) {
								if ( 'string' === WPDA::get_type( $column_date_type ) ) {
									$where_columns[] = $wpdb->prepare( "`{$column_name}` like '%s'", esc_sql( $column_value ) ); // WPCS: unprepared SQL OK.
								} elseif ( 'number' === WPDA::get_type( $column_date_type ) ) {
									$where_columns[] = $wpdb->prepare( "`{$column_name}` = '%d'", esc_sql( $column_value ) ); // WPCS: unprepared SQL OK.
								}
							}
						}
					}
				}
			}

			if ( 0 === count( $where_columns ) ) {
				return '';
			} else {
				return ' (' . implode( ' and ', $where_columns ) . ') ';
			}
		}

		public static function schema_disabled( $schema_name ) {
			if ( 'rdb:' === substr( $schema_name, 0, 4) ) {
				$rdb = WPDADB::get_remote_database( $schema_name, true );
				if ( false === $rdb ) {
					return false;
				}
				return true === $rdb[ 'disabled' ];
			}
			return false;
		}

		/**
		 * Check if database schema exists (local and remote databases)
		 *
		 * @param string $schema_name Database schema
		 *
		 * @return bool Exists?
		 */
		public static function schema_exists( $schema_name ) {
			if ( self::schema_disabled( $schema_name ) ) {
				return false;
			}

			$wpdadb = WPDADB::get_db_connection( $schema_name );
			if ( null === $wpdadb ) {
				return false;
			}
			if ( method_exists( $wpdadb, 'is_connected' ) ) {
				if ( ! $wpdadb->is_connected() ) {
					$msg = new WPDA_Message_Box(
						[
							'message_text'           => __( "Remote database '{$schema_name}' not available [check connection: Settings > WP Data Access]", 'wp-data-access' ),
							'message_type'           => 'error',
							'message_is_dismissible' => false,
						]
					);
					$msg->box();

					return false;
				}
			}

			$wpdadb->query(
				$wpdadb->prepare( '
							SELECT TRUE FROM information_schema.schemata WHERE schema_name = %s
						',
					[
						$wpdadb->dbname,
					]
				)
			); // db call ok; no-cache ok.
			$wpdadb->get_results(); // WPCS: unprepared SQL OK; db call ok; no-cache ok.

			return 1 === $wpdadb->num_rows;
		}

		public static function get_user_default_scheme() {
			$default_databases = get_option('wpda_default_database');
			if ( false !== $default_databases ) {
				$user_id = get_current_user_id();
				if (
					$user_id > 0 &&
					isset( $default_databases[ $user_id ] ) &&
					self::schema_exists( $default_databases[ $user_id ] )
				) {
					return $default_databases[ $user_id ];
				}
			}

			global $wpdb;
			return $wpdb->dbname;
		}

		public static function shortcode_popup() {
			?>
			<script type="text/javascript">
				jQuery(function() {
					var clipboard = new ClipboardJS('.wpda_shortcode_clipboard');
				});
			</script>
			<style type="text/css">
                .wpda_shortcode_text {
                    text-align: center;
                    font-size: 105%;
					white-space: nowrap;
                }
                .wpda_shortcode_buttons {
                    text-align: center;
                }
                .button.wpda_shortcode_button {
                    width: 100px !important;
                }
				.wpda_shortcode_link {
					text-decoration: none;
					font-weight: bold;
				}
			</style>
			<?php
		}

		public static function is_editing_post() {
			global $pagenow;
			if ( $pagenow === 'post.php' || $pagenow === 'edit.php'  || $pagenow === 'post-new.php' ) {
				// Editing post in classic editor
				return '';
			}

			if ( isset( $_SERVER["CONTENT_TYPE"] ) && 'application/json' === $_SERVER["CONTENT_TYPE"] ) {
				// Editing post in Gutenberg editor
				return null;
			}

			return false;
		}

		/**
		 * Get estimated number of rows in a table
		 *
		 * @param $schema_name Schema name
		 * @param $table_name Table name
			 * @param $wpda_table_settings Table settings (query WPDA_Table_Settings_Model)
		 *
		 * @return int row count estimate or -1 if no estimate available
		 */
		public static function get_row_count_estimate( $schema_name, $table_name, $wpda_table_settings ) {
			global $wpdb;

			$wpdadb     = WPDADB::get_db_connection( $schema_name );
			$table_info = $wpdadb->get_results(
				$wpdadb->prepare(
					"
								select engine as engine,
								       table_rows as table_rows
								 from `information_schema`.`tables`
								where `table_schema` = %s
								  and `table_name` = %s
							",
					[
						'' === $schema_name ? $wpdb->dbname : $schema_name,
						$table_name
					]
				),
				'ARRAY_A'
			);

			if ( 1 === sizeof( $table_info ) ) {
				if ( 'InnoDB' !== $table_info[0]['engine'] ) {
					return $table_info[0]['table_rows'];
				} else {
					if (
						isset( $wpda_table_settings->table_settings->row_count_estimate ) &&
						$wpda_table_settings->table_settings->row_count_estimate !== null
					) {
						if ( $wpda_table_settings->table_settings->row_count_estimate ) {
							return $table_info[0]['table_rows'];
						} else {
							return -1;
						}
					} else {
						$innodb_count = WPDA::get_option( WPDA::OPTION_BE_INNODB_COUNT );

						if ( $table_info[0]['table_rows'] > $innodb_count ) {
							return $table_info[0]['table_rows'];
						} else {
							return -1;
						}
					}
				}
			} else {
				return -1; // This should not happen (just to be sure)
			}
		}

	}

}
