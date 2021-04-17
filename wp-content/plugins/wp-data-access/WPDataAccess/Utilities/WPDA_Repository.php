<?PHP

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\Plugin_Table_Models\WPDA_Logging_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_Media_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_Publisher_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_Design_Table_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_Table_Settings_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_User_Menus_Model;
	use WPDataAccess\Plugin_Table_Models\WPDP_Page_Model;
	use WPDataAccess\Plugin_Table_Models\WPDP_Project_Model;
	use WPDataAccess\Plugin_Table_Models\WPDP_Project_Design_Table_Model;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Repository
	 *
	 * Recreate repository objects.
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_Repository {

		const CREATE_TABLE = [
			'wpda_logging'        => [
				'create_table_logging.sql'
			],
			'wpda_menus'          => [
				'create_table_menus.sql',
			],
			'wpda_table_settings' => [
				'create_table_table_settings.sql',
			],
			'wpda_table_design'   => [
				'create_table_table_design.sql',
				'create_table_table_design_alter1.sql',
				'create_table_table_design_alter3.sql',
				'create_table_table_design_alter3.sql'
			],
			'wpda_publisher'      => [
				'create_table_publisher.sql'
			],
			'wpda_media'          => [
				'create_table_media.sql'
			],
			'wpda_project'        => [
				'create_table_project.sql'
			],
			'wpda_project_page'   => [
				'create_table_project_page.sql'
			],
			'wpda_project_table'  => [
				'create_table_project_table.sql'
			],
			'wpda_csv_uploads'  => [
				'create_table_csv_uploads.sql'
			]
		];

		const DROP_TABLE = [
			'wpda_logging'        => [
				'drop_table_logging.sql'
			],
			'wpda_menus'          => [
				'drop_table_menus.sql'
			],
			'wpda_table_settings' => [
				'drop_table_table_settings.sql'
			],
			'wpda_table_design'   => [
				'drop_table_table_design.sql'
			],
			'wpda_publisher'      => [
				'drop_table_publisher.sql'
			],
			'wpda_media'          => [
				'drop_table_media.sql'
			],
			'wpda_project'        => [
				'drop_table_project.sql'
			],
			'wpda_project_page'   => [
				'drop_table_project_page.sql'
			],
			'wpda_project_table'  => [
				'drop_table_project_table.sql'
			],
			'wpda_csv_uploads'  => [
				'drop_table_csv_uploads.sql'
			]
		];

		protected $sql_repository_dir = '';

		public function __construct() {
			$this->sql_repository_dir = plugin_dir_path( dirname( __FILE__ ) ) . '../admin/repository/';
		}

		/**
		 * Recreate repository (save as much data as possible)
		 *
		 * @since   2.0.11
		 */
		public function recreate() {
			global $wpdb;

			$suppress = $wpdb->suppress_errors( true );

			$bck_postfix  = WPDA_Restore_Repository::BACKUP_TABLE_EXTENSION . date( 'YmdHis' );
			foreach ( static::CREATE_TABLE as $key => $value ) {
				$table_name   = $wpdb->prefix . $key;

				// Check if table exists
				$table_exists = new WPDA_Dictionary_Exist( $wpdb->dbname, $table_name );
				$table_check  = $table_exists->table_exists( false );

				if ( $table_check ) {
					// Create backup table
					$bck_table_name   = $wpdb->prefix . $key . $bck_postfix;
					$sql_create_table = "create table $bck_table_name as select * from $table_name";
					$wpdb->query( $sql_create_table );
				}

				$bck_table_name = null;
				$same_cols      = null;

				if ( $table_check ) {
					// Create temporary table to check for changes
					if ( $this->run_script( $value[0], '_new' ) ) {
						// Check if table structure was changed
						$query = "select column_name,ordinal_position,data_type,column_type from (
							select column_name, ordinal_position, data_type, column_type, count(1) rowcount
							from information_schema.columns
							where table_schema = '%s'
							and table_name in ('%s','%s')
							group by column_name, ordinal_position, data_type, column_type
							having count(1) = 1
						) A";

						$table_diff = $wpdb->get_row( $wpdb->prepare( $query, $wpdb->dbname, $table_name, "{$table_name}_new"  ) );

						// Drop check table
						$this->run_script( static::DROP_TABLE[ $key ][0], '_new' );

						if ( $table_diff !== null ) {
							// Drop old repository table
							$this->run_script( static::DROP_TABLE[ $key ][0], '' );

							// Create new repository table
							foreach ( $value as $sql_file ) {
								$this->run_script( $sql_file );
							}

							// Get columns matching old and new repository table columns to restore as much values as possible
							$sql_check_table =
								"select c1.column_name as column_name
								 from information_schema.columns c1
								 where c1.table_schema = '%s'
								   and c1.table_name   = '%s'
								   and c1.column_name in (
								       select c2.column_name
								       from   information_schema.columns c2
								       where  c2.table_schema = '%s'
								       and    c2.table_name   = '%s'
								       )
							    ";
							$same_cols = $wpdb->get_results(
									$wpdb->prepare( $sql_check_table, $wpdb->dbname, $table_name, $wpdb->dbname, "{$table_name}_new" )
									, 'ARRAY_A' );

							// Restore repository table data
							$selected_columns = '';
							foreach ( $same_cols as $same_col ) {
								$selected_columns .= $same_col['column_name'] . ',';
							}
							$selected_columns = substr( $selected_columns, 0, strlen( $selected_columns ) - 1 );
							$sql_restore      =
								"insert into `{$table_name}` ({$selected_columns}) " .
								"select {$selected_columns} from `{$bck_table_name}`";
							$wpdb->query( $sql_restore );
						}
					}
				} else {
					// Create table
					foreach ( $value as $sql_file ) {
						$this->run_script( $sql_file );
					}
				}

				if ( 'on' !== WPDA::get_option( WPDA::OPTION_MR_KEEP_BACKUP_TABLES ) ) {
					// Drop backup table
					$this->run_script( static::DROP_TABLE[ $key ][0], $bck_postfix );
				}

			}

			$this->cleanup();

			$wpdb->suppress_errors( $suppress );
		}

		/**
		 * Cleanup plugin repository tables
		 */
		protected function cleanup() {
			global $wpdb;

			// Remove previous publisher list table settings
			$wpdb->query(
				"delete from {$wpdb->prefix}usermeta where meta_key like '%columnshidden%' and meta_key like '%wpda_publisher%'"
			);

			// Remove previous project page list table settings
			$wpdb->query(
				"delete from {$wpdb->prefix}usermeta where meta_key like '%columnshidden%' and meta_key like '%wpda_project%'"
			);
		}

		/**
		 * Create repository
		 *
		 * @since   1.0.0
		 */
		public function create() {
			foreach ( static::CREATE_TABLE as $key => $value ) {
				foreach ( $value as $sql_file ) {
					$this->run_script( $sql_file );
				}
			}
		}

		/**
		 * Drop repository
		 *
		 * @since   1.0.0
		 */
		public function drop() {
			foreach ( static::DROP_TABLE as $key => $value ) {
				foreach ( $value as $sql_file ) {
					$this->run_script( $sql_file );
				}
			}
		}

		/**
		 * Run SQL script file
		 *
		 * @param string $sql_file SQL script file name
		 * @param string $wpda_postfix WPDA postfix
		 *
		 * @return mixed Result of the query taken from the SQL script file
		 *
		 * @since   2.0.11
		 */
		public function run_script( $sql_file, $wpda_postfix = '' ) {
			$sql_repository_file   = $this->sql_repository_dir . $sql_file;
			$sql_repository_handle = fopen( $sql_repository_file, 'r' );

			if ( $sql_repository_handle ) {
				// Read file content and close handle.
				$sql_repository_file_content = fread( $sql_repository_handle, filesize( $sql_repository_file ) );
				fclose( $sql_repository_handle );

				global $wpdb;

				// Replace WP prefix and WPDA prefix.
				$sql_repository_file_content = str_replace( '{wp_prefix}', $wpdb->prefix, $sql_repository_file_content );
				$sql_repository_file_content = str_replace( '{wpda_prefix}', 'wpda', $sql_repository_file_content ); // for backward compatibility
				$sql_repository_file_content = str_replace( '{wpda_postfix}', $wpda_postfix, $sql_repository_file_content );

				// Run script.
				return $wpdb->query( $sql_repository_file_content ); // WPCS: unprepared SQL OK; db call ok; no-cache ok.
			}
		}


		/**
		 * Inform user if repository is invalid
		 *
		 * @since   1.0.0
		 */
		public function inform_user() {
			if ( ! is_admin() ) {
				return;
			}

			if ( isset( $_REQUEST['setup_error'] ) && 'off' === $_REQUEST['setup_error'] ) {
				// Turn off menu management not available message.
				WPDA::set_option( WPDA::OPTION_WPDA_SETUP_ERROR, 'off' );
			} else {
				if ( 'off' !== WPDA::get_option( WPDA::OPTION_WPDA_SETUP_ERROR ) ) {
					// Check if repository tables exist.
					if ( ! WPDA_User_Menus_Model::table_exists() ||
					     ! WPDA_Design_Table_Model::table_exists() ||
					     ! WPDP_Project_Design_Table_Model::table_exists() ||
					     ! WPDA_Publisher_Model::table_exists() ||
					     ! WPDA_Logging_Model::table_exists() ||
					     ! WPDA_Media_Model::table_exists() ||
					     ! WPDP_Project_Model::table_exists() ||
					     ! WPDP_Page_Model::table_exists() ||
					     ! WPDA_Table_Settings_Model::table_exists()
					) {
						$msg = new WPDA_Message_Box(
							[
								'message_text' =>
									__( 'Some features of WP Data Access are currently not available.', 'wp-data-access' ) .
									' ' .
									__( 'ACTION', 'wp-data-access' ) .
									': ' .
									'<a href="?page=wpdataaccess&tab=repository">' . __( 'Recreate repository', 'wp-data-access' ) . '</a>' .
									' ' .
									__( 'to to solve this problem.', 'wp-data-access' ) .
									' [' .
									'<a href="?' . $_SERVER['QUERY_STRING'] . '&setup_error=off">' . __( 'do not show this message again', 'wp-data-access' ) . '</a>' .
									']',
							]
						);

						$msg->box();
					}
				}
			}
		}

		public static function whats_new() {
			if ( ! is_admin() ) {
				return;
			}

			$url = admin_url() . 'admin.php?action=wpda_show_whats_new';

			if ( 'off' !== WPDA::get_option( WPDA::OPTION_WPDA_SHOW_WHATS_NEW ) ) {
				$color = 'color: #a00;';
				$url  .= '&whats_new=off';
			} else {
				$color = '';
			}

			?>
			<a href="<?php echo $url; ?>" class="wpda_tooltip"
			   target="_blank" title="What's New? - opens in a new tab or window">
				<span class="material-icons"
					  style="font-size: 26px;vertical-align: sub;<?php echo $color; ?>">update</span></a>
			<?php
		}

		public function create_new_backup() {
			global $wpdb;

			$bck_postfix  = WPDA_Restore_Repository::BACKUP_TABLE_EXTENSION . date( 'YmdHis' );
			foreach ( static::CREATE_TABLE as $key => $value ) {
				$table_name   = $wpdb->prefix . $key;

				// Check if table exists
				$table_exists = new WPDA_Dictionary_Exist( $wpdb->dbname, $table_name );

				if ( $table_exists->table_exists( false ) ) {
					// Create backup table
					$bck_table_name   = $wpdb->prefix . $key . $bck_postfix;
					$sql_create_table = "create table $bck_table_name as select * from $table_name";
					$wpdb->query( $sql_create_table );
				}
			}
		}

	}

}