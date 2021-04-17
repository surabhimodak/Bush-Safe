<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities {

	use WPDataAccess\Connection\WPDADB;
	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists;
	use WPDataAccess\Plugin_Table_Models\WPDA_Media_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_Table_Settings_Model;
	use WPDataAccess\Plugin_Table_Models\WPDA_User_Menus_Model;
	use WPDataAccess\WPDA;
	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns;

	/**
	 * Class WPDA_Table_Actions
	 *
	 * @author  Peter Schulz
	 * @since   2.0.13
	 */
	class WPDA_Table_Actions {

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
		 * Database table structure
		 *
		 * @var array
		 */
		protected $table_structure;

		/**
		 * Original create table statement
		 *
		 * @var string
		 */
		protected $create_table_stmt_orig;

		/**
		 * Reformatted create table statement
		 *
		 * @var string
		 */
		protected $create_table_stmt;

		/**
		 * Database indexes
		 *
		 * @var array
		 */
		protected $indexes;

		/**
		 * Database foreign key constraints
		 *
		 * @var array
		 */
		protected $foreign_keys;

		/**
		 * Indicates if table is a WordPress table
		 *
		 * @var boolean
		 */
		protected $is_wp_table;

		/**
		 * Possible values: Table and View
		 *
		 * @var string
		 */
		protected $dbo_type;

		/**
		 * Handle to instance of WPDA_List_Columns
		 *
		 * @var WPDA_List_Columns
		 */
		protected $wpda_list_columns;

		/**
		 * Row number in the list table
		 *
		 * @var int
		 */
		protected $rownum;

		/**
		 * Shows the specifications for the specified table or view
		 *
		 * There are four tabs provided:
		 *
		 * TAB Actions
		 * Provides actions for the given table or view, like export, rename, copy, drop, alter, and so on. A button
		 * is provided for every possible action. For some actions additional info can be provided through input fields
		 * like the type of download for an export. Not all buttons are available for all tables and views. WordPress
		 * tables for example cannot be dropped. Views for example can not be truncated. Which buttons are provided
		 * depends on the table or view.
		 *
		 * TAB Structure
		 * Shows the columns and their attributes.
		 *
		 * TAB Indexes
		 * Shows the indexes for the specified table. Not available for views.
		 *
		 * TAB SQL
		 * Shows the create table or views statement for the given table of view. A button is provided to copy
		 * this statement to the clipboard.
		 *
		 * @since   2.0.13
		 */
		public function show() {
			if ( ! isset( $_REQUEST['table_name'] ) || ! isset( $_REQUEST['wpdaschema_name'] ) || ! isset( $_REQUEST['rownum'] ) ) {
				wp_die( __( 'ERROR: Wrong arguments', 'wp-data-access' ) );
			} else {
				$this->schema_name = sanitize_text_field( wp_unslash( $_REQUEST['wpdaschema_name'] ) ); // input var okay.
				$this->table_name  = sanitize_text_field( wp_unslash( $_REQUEST['table_name'] ) ); // input var okay.
				$this->rownum      = sanitize_text_field( wp_unslash( $_REQUEST['rownum'] ) ); // input var okay.

				$wpda_data_dictionary = new WPDA_Dictionary_Exist( $this->schema_name, $this->table_name );
				if ( ! $wpda_data_dictionary->table_exists() ) {
					echo '<div>' . __( 'ERROR: Invalid table name or not authorized', 'wp-data-access' ) . '</div>';

					return;
				}

				$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '?'; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, "wpda-actions-{$this->table_name}" ) ) {
					echo '<div>' . __( 'ERROR: Not authorized', 'wp-data-access' ) . '</div>';

					return;
				}

				$this->dbo_type = isset( $_REQUEST['dbo_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['dbo_type'] ) ) : null; // input var okay.

				$this->is_wp_table = WPDA::is_wp_table( $this->table_name );

				$wpdadb= WPDADB::get_db_connection( $this->schema_name );
				if ( null === $wpdadb ) {
					wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				}
				$query                 = "show full columns from `{$wpdadb->dbname}`.`{$this->table_name}`";
				$this->table_structure = $wpdadb->get_results( $query, 'ARRAY_A' );

				if ( strpos( strtoupper( $this->dbo_type ), 'TABLE' ) !== false ) {
					$this->dbo_type = 'Table';
					$query          = "show create table `{$wpdadb->dbname}`.`{$this->table_name}`";
					$create_table   = $wpdadb->get_results( $query, 'ARRAY_A' );
					if ( isset( $create_table[0]['Create Table'] ) ) {
						$this->create_table_stmt_orig = $create_table[0]['Create Table'];
						$this->create_table_stmt      = preg_replace( "/\(/", "<br/>(", $this->create_table_stmt_orig, 1 );
						$this->create_table_stmt      = preg_replace( '/\,\s\s\s/', '<br/>,   ', $this->create_table_stmt );
						$pos                          = strrpos( $this->create_table_stmt, ')' );
						if ( false !== $pos ) {
							$this->create_table_stmt =
								substr( $this->create_table_stmt, 0, $pos - 1 ) .
								"<br/>)" .
								substr( $this->create_table_stmt, $pos + 1 );
						}

						$query         = "show indexes from `{$wpdadb->dbname}`.`{$this->table_name}`";
						$this->indexes = $wpdadb->get_results( $query, 'ARRAY_A' );

						$query = $wpdadb->prepare(
							"
								select constraint_name AS constraint_name, 
									   column_name AS column_name, 
									   referenced_table_name AS referenced_table_name, 
									   referenced_column_name AS referenced_column_name
								from   information_schema.key_column_usage
								where table_schema = %s
								  and table_name   = %s
								  and referenced_table_name is not null
								order by ordinal_position
							",
							[
								$wpdadb->dbname,
								$this->table_name,
							]
						);
						$this->foreign_keys = $wpdadb->get_results( $query, 'ARRAY_A' );
					} else {
						$this->create_table_stmt = __( 'Error reading create table statement', 'wp-data-access' );
					}
				} elseif ( strtoupper( $this->dbo_type ) === 'VIEW' ) {
					$this->dbo_type = 'View';
					$query          = "show create view `{$wpdadb->dbname}`.`{$this->table_name}`";
					$create_table   = $wpdadb->get_results( $query, 'ARRAY_A' );
					if ( isset( $create_table[0]['Create View'] ) ) {
						$this->create_table_stmt_orig = $create_table[0]['Create View'];
						$this->create_table_stmt      = str_replace( "AS select", "AS<br/>select", $this->create_table_stmt_orig );
						$this->create_table_stmt      = str_replace( "from", "<br/>from", $this->create_table_stmt );
					}
				}
				$this->wpda_list_columns =
					WPDA_List_Columns_Cache::get_list_columns( $this->schema_name, $this->table_name );
				?>
				<div id="<?php echo esc_attr( $this->rownum ); ?>-tabs">
					<div class="nav-tab-wrapper" style="padding-top: 0 !important;">
						<?php
						echo '<a id="' . esc_attr( $this->rownum ) . '-sel-1" class="nav-tab nav-tab-active wpda-manage-nav-tab' .
							 '" href="javascript:void(0)" onclick="settab(\'' . esc_attr( $this->rownum ) . '\', \'1\');" 
							style="font-size:inherit;">' .
							 '<span class="dashicons dashicons-admin-tools wpda_settings_icon"></span>' .
							 '<span class="wpda_settings_label">' . __( 'Actions', 'wp-data-access' ) . '</span>' .
							 '</a>';
						if ( 'Table' === $this->dbo_type || 'View' === $this->dbo_type ) {
							echo '<a id="' . esc_attr( $this->rownum ) . '-sel-6" class="nav-tab wpda-manage-nav-tab' .
							     '" href="javascript:void(0)" onclick="settab(\'' . esc_attr( $this->rownum ) . '\', \'6\');" 
								style="font-size:inherit;">' .
							     '<span class="dashicons dashicons-admin-generic wpda_settings_icon"></span> ' .
								 '<span class="wpda_settings_label">' . __( 'Settings', 'wp-data-access' ) . '</span>' .
							     '</a>';
						}
						if ( '' !== $this->dbo_type ) {
							echo '<a id="' . esc_attr( $this->rownum ) . '-sel-2" class="nav-tab wpda-manage-nav-tab' .
								 '" href="javascript:void(0)" onclick="settab(\'' . esc_attr( $this->rownum ) . '\', \'2\');"
								style="font-size:inherit;">' .
								 '<span class="dashicons dashicons-list-view wpda_settings_icon"></span> ' .
								 '<span class="wpda_settings_label">' . __( 'Columns', 'wp-data-access' ) . '</span>' .
								 '</a>';
						}
						if ( 'Table' === $this->dbo_type ) {
							echo '<a id="' . esc_attr( $this->rownum ) . '-sel-3" class="nav-tab wpda-manage-nav-tab' .
							     '" href="javascript:void(0)" onclick="settab(\'' . esc_attr( $this->rownum ) . '\', \'3\');" 
								style="font-size:inherit;">' .
							     '<span class="dashicons dashicons-controls-forward wpda_settings_icon"></span> ' .
								 '<span class="wpda_settings_label">' . __( 'Indexes', 'wp-data-access' ) . '</span>' .
							     '</a>';
						}
						if ( 'Table' === $this->dbo_type ) {
							echo '<a id="' . esc_attr( $this->rownum ) . '-sel-5" class="nav-tab wpda-manage-nav-tab' .
							     '" href="javascript:void(0)" onclick="settab(\'' . esc_attr( $this->rownum ) . '\', \'5\');" 
								style="font-size:inherit;">' .
							     '<span class="dashicons dashicons-networking wpda_settings_icon"></span> ' .
								 '<span class="wpda_settings_label">' . __( 'Foreign Keys', 'wp-data-access' ) . '</span>' .
							     '</a>';
						}
						if ( 'Table' === $this->dbo_type || 'View' === $this->dbo_type ) {
							echo '<a id="' . esc_attr( $this->rownum ) . '-sel-4" class="nav-tab wpda-manage-nav-tab' .
							     '" href="javascript:void(0)" onclick="settab(\'' . esc_attr( $this->rownum ) . '\', \'4\');" 
								style="font-size:inherit;">' .
							     '<span class="dashicons dashicons-editor-code wpda_settings_icon"></span> ' .
								 '<span class="wpda_settings_label">' . __( 'SQL', 'wp-data-access' ) . '</span>' .
							     '</a>';
						}
						?>
					</div>
					<div id="<?php echo esc_attr( $this->rownum ); ?>-tab-1" style="padding:3px;">
						<?php $this->tab_actions(); ?>
					</div>
					<div id="<?php echo esc_attr( $this->rownum ); ?>-tab-6" style="padding:3px;display:none;">
						<?php $this->tab_settings(); ?>
					</div>
					<?php
					if ( '' !== $this->dbo_type ) {
						?>
						<div id="<?php echo esc_attr( $this->rownum ); ?>-tab-2" style="padding:3px;display:none;">
							<?php $this->tab_structure(); ?>
						</div>
						<?php
					}
					if ( 'Table' === $this->dbo_type ) {
						?>
						<div id="<?php echo esc_attr( $this->rownum ); ?>-tab-3" style="padding:3px;display:none;">
							<?php $this->tab_index(); ?>
						</div>
						<?php
					}
					if ( 'Table' === $this->dbo_type ) {
						?>
						<div id="<?php echo esc_attr( $this->rownum ); ?>-tab-5" style="padding:3px;display:none;">
							<?php $this->tab_foreign_keys(); ?>
						</div>
						<?php
					}
					if ( 'Table' === $this->dbo_type || 'View' === $this->dbo_type ) {
						?>
						<div id="<?php echo esc_attr( $this->rownum ); ?>-tab-4" style="padding:3px;display:none;">
							<?php $this->tab_sql(); ?>
						</div>
						<?php
					}
					?>
				</div>
				<div style="height:0;padding:0;margin:0;"></div>
				<script type='text/javascript'>
					jQuery(function () {
						var sql_to_clipboard = new ClipboardJS("#button-copy-clipboard-<?php echo esc_attr( $this->rownum ); ?>");
						sql_to_clipboard.on('success', function (e) {
							jQuery.notify('<?php echo __( 'SQL successfully copied to clipboard!', 'wp-data-access' ); ?>','info');
						});
						sql_to_clipboard.on('error', function (e) {
							jQuery.notify('<?php echo __( 'Could not copy SQL to clipboard!', 'wp-data-access' ); ?>','error');
						});
						jQuery("#rename-table-from-<?php echo esc_attr( $this->rownum ); ?>").on('keyup paste', function () {
							this.value = this.value.replace(/[^\w\$\_]/g, '');
						});
						jQuery("#copy-table-from-<?php echo esc_attr( $this->rownum ); ?>").on('keyup paste', function () {
							this.value = this.value.replace(/[^\w\$\_]/g, '');
						});
					});
				</script>
				<?php
			}
		}

		/**
		 * Provides content for table settings
		 */
		public function tab_settings() {
			$wp_nonce_action_top_settings     = "wpda-settings-{$this->table_name}";
			$wp_nonce_top_settings            = wp_create_nonce( $wp_nonce_action_top_settings );
			$settings_table_top_form_id       = 'settings_table_form_' . $this->table_name;
			$settings_table_top_form_settings = 'settings_table_form_settings_' . $this->table_name;
			$settings_table_top_form          =
				"<form" .
				" id='" . $settings_table_top_form_id . "'" .
				" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_MAIN ) . "'" .
				" method='post'>" .
				"<input type='hidden' name='action' value='settings-table' />" .
				"<input type='hidden' name='settings_table_name' value='" . $this->table_name . "' />" .
				"<input type='hidden' name='settings' id='" . esc_attr( $settings_table_top_form_settings ) . "' value='' />" .
				"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_top_settings ) . "' />" .
				"</form>";

			$wp_nonce_action_settings     = "wpda-settings-{$this->table_name}";
			$wp_nonce_settings            = wp_create_nonce( $wp_nonce_action_settings );
			$settings_table_form_id       = 'settings_table_form_' . $this->table_name;
			$settings_table_form_settings = 'settings_table_form_settings_' . $this->table_name;
			$settings_table_form          =
				"<form" .
				" id='" . $settings_table_form_id . "'" .
				" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_MAIN ) . "'" .
				" method='post'>" .
				"<input type='hidden' name='action' value='settings-table' />" .
				"<input type='hidden' name='settings_table_name' value='" . $this->table_name . "' />" .
				"<input type='hidden' name='settings' id='" . esc_attr( $settings_table_form_settings ) . "' value='' />" .
				"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_settings ) . "' />" .
				"</form>";

			$wp_nonce_action_delete_menu = "wpda-delete-menu-{$this->table_name}";
			$wp_nonce_delete_menu        = wp_create_nonce( $wp_nonce_action_delete_menu );
			$delete_menu_table_form_id   = 'delelete_menu_table_form_' . $this->table_name;
			$delete_menu_table_form      =
				"<form" .
				" id='" . $delete_menu_table_form_id . "'" .
				" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_MAIN ) . "'" .
				" method='post'>" .
				"<input type='hidden' name='action' value='delete-menu' />" .
				"<input type='hidden' name='menu_id' id='" . esc_attr( $this->rownum ) . "_menu_id' value='' />" .
				"<input type='hidden' name='menu_table_name' value='" . $this->table_name . "' />" .
				"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_delete_menu ) . "' />" .
				"</form>";

			$settings_db = WPDA_Table_Settings_Model::query( $this->table_name, $this->schema_name );
			if ( isset( $settings_db[0]['wpda_table_settings'] ) ) {
				$settings_db_custom = json_decode( $settings_db[0]['wpda_table_settings'] );
				$sql_dml            = 'UPDATE';
			} else {
				$settings_db_custom = (object) null;
				$sql_dml            = 'INSERT';
			}

			if ( has_filter('wpda_add_column_settings') ) {
				// Use filter
				$column_settings_add_column = apply_filters( 'wpda_add_column_settings', null );
			}

			if ( isset( $column_settings_add_column ) && is_array( $column_settings_add_column ) ) {
				$array_valid = true;
				foreach ( $column_settings_add_column as $add_column ) {
					if (
						! isset( $add_column['label'] ) ||
						! isset( $add_column['hint'] ) ||
						! isset( $add_column['name_prefix'] ) ||
						! isset( $add_column['type'] ) ||
						! isset( $add_column['default'] ) ||
						! isset( $add_column['disable'] )
					) {
						$array_valid = false;
						break;
					}
				}
				if ( ! $array_valid ) {
					$column_settings_add_column = [];
				}
			} else {
				$column_settings_add_column = [];
			}

			$engine = WPDA_Dictionary_Lists::get_engine( $this->schema_name, $this->table_name );
			?>
			<style>
				.wpda_table_settings_caret {
					cursor: pointer;
					vertical-align: text-bottom;
					font-size: 90%;
					font-weight: bold;
				}

				.wpda_table_settings_caret::before {
					content: "\25B6";
					color: black;
					display: inline-block;
					margin-right: 6px;
				}

				.wpda_table_settings_caret_down::before {
					transform: rotate(90deg);
				}

				.wpda_table_settings_nested {
					padding-top: 10px;
					padding-left: 20px;
					padding-bottom: 10px;
					display: none;
				}

				.wpda_table_settings {
					border-collapse: collapse;
				}

				.wpda_table_settings th {
					text-align: left;
					font-weight: bold;
					padding: 0;
					margin: 0;
					padding-bottom: 5px;
				}

				.wpda_table_settings th:nth-child(n) {
					padding: 0 10px 5px 10px;
				}

				.wpda_table_settings td {
					vertical-align: middle;
					padding: 0;
					margin: 0;
				}

				.wpda_table_settings td:nth-child(n) {
					padding: 0 10px 0 10px;
				}

				.wpda_table_settings tr:nth-child(even) {
					background: #fff;
				}
			</style>
			<table class="widefat striped rows wpda-structure-table">
				<tr>
					<td>
						<script type='text/javascript'>
							jQuery("#wpda_invisible_container").append("<?php echo $settings_table_top_form; ?>");
							jQuery("#wpda_invisible_container").append("<?php echo $settings_table_form; ?>");
							jQuery("#wpda_invisible_container").append("<?php echo $delete_menu_table_form; ?>");
						</script>
						<div id="wpda_table_settings_<?php echo esc_attr( $this->rownum ); ?>">
							<div style="font-weight:bold; padding-bottom:10px;">
								<span class="dashicons dashicons-warning"></span>
								<span style="vertical-align:middle">
									<?php echo __( 'Please note that these settings only work within in the plugin', 'wp-data-access' ); ?>
								</span>
							</div>
							<ul style="margin:0; padding:0;">
								<?php
								do_action(
									'wpda_prepend_table_settings',
									$this->schema_name,
									$this->table_name,
									$this->table_structure
								);
								?>
								<li>
									<span class="wpda_table_settings_caret"><?php echo __( 'Table Settings', 'wp-data-access' ); ?></span>
									<a href="https://wpdataaccess.com/docs/documentation/data-explorer/table-settings/" target="_blank">
										<span class="dashicons dashicons-editor-help wpda_tooltip"
											  title="<?php echo __( 'Define table settings [help opens in a new tab or window]', 'wp-data-access' ); ?>"
											  style="cursor:pointer"></span>
									</a>
									<ul class="wpda_table_settings_nested">
										<div style="font-size:90%;font-weight:bold;">
											<?php echo __( 'Row count (InnoDB only)', 'wp-data-access' ); ?>
										</div>
										<div style="font-size:90%;">
											<label class="wpda_action_font">
												<input type="radio"
													   name="<?php echo esc_attr( $this->table_name ); ?>_row_count_estimate"
													   value="true"
													   <?php
													   echo $engine !== 'InnoDB' ? ' disabled ' : '';
													   if (
													   		isset( $settings_db_custom->table_settings->row_count_estimate ) &&
															$settings_db_custom->table_settings->row_count_estimate
													   ) {
														   echo ' checked ';
													   }
													   ?>
												>
												Show estimate row count (faster but less accurate)
											</label>
											<br/>
											<label class="wpda_action_font">
												<input type="radio"
													   name="<?php echo esc_attr( $this->table_name ); ?>_row_count_estimate"
													   value="false"
													   <?php
													   echo $engine !== 'InnoDB' ? ' disabled ' : '';
													   if (
														   isset( $settings_db_custom->table_settings->row_count_estimate ) &&
														   ! $settings_db_custom->table_settings->row_count_estimate
													   ) {
														   echo ' checked ';
													   }
													   ?>
												>
												Show real row count
											</label>
											<br/>
											<label class="wpda_action_font">
												<input type="radio"
													   name="<?php echo esc_attr( $this->table_name ); ?>_row_count_estimate"
													   value=""
													   <?php
													   echo $engine !== 'InnoDB' ? ' disabled ' : '';
													   if ( isset( $settings_db_custom->table_settings->row_count_estimate ) ) {
													   		if ( null === $settings_db_custom->table_settings->row_count_estimate ) {
																echo ' checked ';
															}
													   } else {
													   		echo ' checked ';
													   }
													   ?>
												>
												Use plugin default (current value=<?php echo esc_attr( WPDA::get_option( WPDA::OPTION_BE_INNODB_COUNT ) ); ?>)
												[<a href="options-general.php?page=wpdataaccess&tab=backend">change plugin default</a>]
											</label>
										</div>
										<br/>
										<div style="font-size:90%;font-weight:bold;">
											<?php echo __( 'Access control', 'wp-data-access' ); ?>
										</div>
										<div style="font-size:90%;">
											<label class="wpda_action_font">
												<input type="checkbox"
													   id="<?php echo esc_attr( $this->table_name ); ?>_row_level_security"
													<?php
													if ( isset( $settings_db_custom->table_settings->row_level_security ) ) {
														echo 'true'===$settings_db_custom->table_settings->row_level_security ? 'checked' : '';
													}
													?>
												/>
												<?php echo __( 'Enable row level access control (adds token to row actions)', 'wp-data-access' ); ?>
											</label>
										</div>
										<br/>
										<div style="font-size:90%;font-weight:bold;">
											<?php echo __( 'Process hyperlink columns as', 'wp-data-access' ); ?>
										</div>
										<div style="font-size:90%;">
											<label for="<?php echo esc_attr( $this->table_name ); ?>table_top_setting_hyperlink_definition_json">
												<input type="radio"
													   id="<?php echo esc_attr( $this->table_name ); ?>table_top_setting_hyperlink_definition_json"
													   name="<?php echo esc_attr( $this->table_name ); ?>table_top_setting_hyperlink_definition"
													   value="json"
													   class="wpda_table_top_setting_item wpda_action_font"
													   <?php
													   if ( isset( $settings_db_custom->table_settings->hyperlink_definition ) ) {
														   echo 'json' === $settings_db_custom->table_settings->hyperlink_definition ? 'checked' : '';
													   } else {
														   echo 'checked';
													   }
													   ?>
												/>
												<?php echo __( 'Preformatted JSON (allows individual label and target setting)', 'wp-data-access' ); ?>
											</label>
											<br/>
											<label for="<?php echo esc_attr( $this->table_name ); ?>table_top_setting_hyperlink_definition_text">
												<input type="radio"
													   id="<?php echo esc_attr( $this->table_name ); ?>table_top_setting_hyperlink_definition_text"
													   name="<?php echo esc_attr( $this->table_name ); ?>table_top_setting_hyperlink_definition"
													   value="text"
													   class="wpda_table_top_setting_item wpda_action_font"
													   <?php
													   if ( isset( $settings_db_custom->table_settings->hyperlink_definition ) ) {
														   echo 'text' === $settings_db_custom->table_settings->hyperlink_definition ? 'checked' : '';
													   }
													   ?>
												/>
												<?php echo __( 'Plain text (column name used as link, opens a new tab or window)', 'wp-data-access' ); ?>
											</label>
										</div>
										<br/>
										<div>
											<button type="button"
												   	id="wpda_<?php echo esc_attr( $this->rownum ); ?>_save_table_settings"
												   	class="button button-primary"
												   	style="font-size: 90%;">
												<span class="material-icons wpda_icon_on_button">check</span>
												<?php echo __( 'Save Table Settings', 'wp-data-access' ); ?>
											</button>
											<button type="button"
												   	id="wpda_<?php echo esc_attr( $this->rownum ); ?>_cancel_table_settings"
												   	class="button button-primary"
												   	style="font-size: 90%;">
												<span class="material-icons wpda_icon_on_button">cancel</span>
												<?php echo __( 'Cancel', 'wp-data-access' ); ?>
											</button>
										</div>
									</ul>
								</li>
								<li>
									<span class="wpda_table_settings_caret"><?php echo __( 'Column Settings', 'wp-data-access' ); ?></span>
									<a href="https://wpdataaccess.com/docs/documentation/data-explorer/table-settings/" target="_blank">
										<span class="dashicons dashicons-editor-help wpda_tooltip"
											  title="<?php echo __( 'Define column labels and (media) types [help opens in a new tab or window]', 'wp-data-access' ); ?>"
											  style="cursor:pointer"></span>
									</a>
									<ul class="wpda_table_settings_nested">
										<table class="wpda_table_settings">
											<tr>
												<th style="padding-left:0;vertical-align:bottom;"><?php echo __( 'Column', 'wp-data-access' ); ?></th>
												<th>
													<?php echo __( 'Label on List Table', 'wp-data-access' ); ?>
													<span
															class="dashicons dashicons-editor-help wpda_tooltip"
															title="<?php echo __( 'Define column labels for list tables', 'wp-data-access' ); ?>"
															style="cursor:pointer;vertical-align:bottom;"></span>
												</th>
												<th>
													<?php echo __( 'Label on Data Entry Form', 'wp-data-access' ); ?>
													<span class="dashicons dashicons-editor-help wpda_tooltip"
														  title="<?php echo __( 'Define column labels for data entry forms', 'wp-data-access' ); ?>"
														  style="cursor:pointercursor:pointer;vertical-align:bottom;"></span>
												</th>
												<th>
													<?php echo __( 'Column Type', 'wp-data-access' ); ?>
													<span class="dashicons dashicons-editor-help wpda_tooltip"
														  title="<?php echo __( 'Featured plugin column types', 'wp-data-access' ); ?>"
														  style="cursor:pointer;vertical-align:text-bottom;"></span>
												</th>
												<?php foreach ( $column_settings_add_column as $add_column ) { ?>
													<th>
														<?php echo $add_column['label']; ?>
														<span class="dashicons dashicons-editor-help wpda_tooltip"
															  title="<?php echo $add_column['hint']; ?>"
															  style="cursor:pointer;vertical-align:text-bottom;"></span>
													</th>
												<?php } ?>
											</tr>
											<?php
											$columns    = $this->wpda_list_columns->get_table_column_headers();
											$media_pool = WPDA_Media_Model::get_pool();
											$media_cols = [];
											if ( isset( $media_pool[ $this->schema_name ][ $this->table_name ] ) ) {
												$media_cols = $media_pool[ $this->schema_name ][ $this->table_name ];
											}
											foreach ( $this->table_structure as $column ) {
												$label_list_table = $this->wpda_list_columns->get_column_label( $column['Field'] );
												$label_data_entry = isset( $columns[ $column['Field'] ] ) ? $columns[ $column['Field'] ] : '';
												$option           = isset( $media_cols[ $column['Field'] ] ) ? $media_cols[ $column['Field'] ] : '';
												?>
												<tr>
													<td style="padding-left:0;">
														<?php echo esc_attr( $column['Field'] ); ?>
													</td>
													<td>
														<input
																id="list_label_<?php echo esc_attr( $column['Field'] ); ?>"
																class="wpda_table_setting_item wpda_action_font"
																type="text"
																value="<?php echo esc_attr( $label_list_table ); ?>"
																style="font-size: 90%;"
														>
													</td>
													<td>
														<input
																id="form_label_<?php echo esc_attr( $column['Field'] ); ?>"
																class="wpda_table_setting_item wpda_action_font"
																type="text"
																value="<?php echo esc_attr( $label_data_entry ); ?>"
																style="font-size: 90%;"
														>
													</td>
													<td style="text-align:center;">
														<select id="column_media_<?php echo esc_attr( $column['Field'] ); ?>"
																class="wpda_table_setting_item wpda_action_font"
																style="font-size: 90%; height: 30px;"
														>
															<option value="" <?php echo '' === $option ? 'selected' : ''; ?>>
															</option>
															<option value="Attachment" <?php echo 'Attachment' === $option ? 'selected' : ''; ?>>
																Attachment
															</option>
															<option value="Audio" <?php echo 'Audio' === $option ? 'selected' : ''; ?>>
																Audio
															</option>
															<option value="Hyperlink" <?php echo 'Hyperlink' === $option ? 'selected' : ''; ?>>
																Hyperlink
															</option>
															<option value="Image" <?php echo 'Image' === $option ? 'selected' : ''; ?>>
																Image
															</option>
															<option value="ImageURL" <?php echo 'ImageURL' === $option ? 'selected' : ''; ?>>
																Image URL
															</option>
															<option value="Video" <?php echo 'Video' === $option ? 'selected' : ''; ?>>
																Video
															</option>
														</select>
														<input type="hidden"
															   id="column_media_<?php echo esc_attr( $column['Field'] ); ?>_dml"
															   value="<?php echo isset( $media_cols[ $column['Field'] ] ) ? 'UPDATE' : 'INSERT'; ?>"
														/>
														<input type="hidden"
															   id="column_media_<?php echo esc_attr( $column['Field'] ); ?>_old"
															   value="<?php echo $option; ?>"
														/>
													</td>
													<?php foreach ( $column_settings_add_column as $add_column ) { ?>
														<td>
															<input
																type="<?php echo $add_column['type']; ?>"
																id="<?php echo $add_column['name_prefix'] . esc_attr( $column['Field'] ); ?>"
																class="wpda_table_setting_item wpda_action_font wpda_tooltip"
																<?php
																if ( 'keys' === $add_column['disable'] ) {
																	$primary_key = $this->wpda_list_columns->get_table_primary_key();
																	if ( in_array($column['Field'], $primary_key) ) {
																		echo 'disabled="disabled" title="Not available for key columns"';
																	}
																}
																if ( 'checkbox' === $add_column['type'] ) {
																	$column_name = $add_column['name_prefix'] . esc_attr( $column['Field'] );
																	if ( isset( $settings_db_custom->custom_settings->$column_name ) ) {
																		echo $settings_db_custom->custom_settings->$column_name ? 'checked' : '';
																	} else {
																		echo $add_column['default'];
																	}
																}
																?>
															>
														</td>
														<td></td>
													<?php } ?>
												</tr>
												<?php
											}
											?>
										</table>
										<br/>
										<div>
											<button type="button"
												   	id="wpda_<?php echo esc_attr( $this->rownum ); ?>_save_column_settings"
												   	class="button button-primary"
												   	style="font-size: 90%;">
												<span class="material-icons wpda_icon_on_button">check</span>
												<?php echo __( 'Save Column Settings', 'wp-data-access' ); ?>
											</button>
											<button type="button"
												   	id="wpda_<?php echo esc_attr( $this->rownum ); ?>_cancel_column_settings"
												   	class="button button-primary"
												   	style="font-size: 90%;">
												<span class="material-icons wpda_icon_on_button">cancel</span>
												<?php echo __( 'Cancel', 'wp-data-access' ); ?>
											</button>
										</div>
									</ul>
								</li>
								<li>
									<span class="wpda_table_settings_caret"><?php echo __( 'Dynamic Hyperlinks', 'wp-data-access' ); ?></span>
									<a href="https://wpdataaccess.com/docs/documentation/data-explorer/table-settings/" target="_blank">
										<span class="dashicons dashicons-editor-help wpda_tooltip"
											  title="<?php echo sprintf( __( 'Add dynamic hyperlinks to table `%s` [help opens in a new tab or window]', 'wp-data-access' ), esc_attr( $this->table_name ) ); ?>"
											  style="cursor:pointer"></span>
									</a>
									<ul class="wpda_table_settings_nested">
										<table class="wpda_table_settings">
											<tr>
												<th style="padding-left:0;">
													<span style="vertical-align:bottom;"><?php echo __( 'Hyperlink Label', 'wp-data-access' ); ?></span>
												</th>
												<th>
													<span style="vertical-align:bottom;"><?php echo __( '+List?', 'wp-data-access' ); ?></span>
													<span class="dashicons dashicons-editor-help wpda_tooltip"
														  title="<?php echo __( 'Add hyperlink to list', 'wp-data-access' ); ?>"
														  style="cursor:pointer;"></span>
												</th>
												<th>
													<span style="vertical-align:bottom;"><?php echo __( '+Form?', 'wp-data-access' ); ?></span>
													<span class="dashicons dashicons-editor-help wpda_tooltip"
														  title="<?php echo __( 'Add hyperlink to form', 'wp-data-access' ); ?>"
														  style="cursor:pointer;"></span>
												</th>
												<th>
													<span style="vertical-align:bottom;"><?php echo __( '+Window?', 'wp-data-access' ); ?></span>
													<span class="dashicons dashicons-editor-help wpda_tooltip"
														  title="<?php echo __( 'Opens URL in a new tab or window', 'wp-data-access' ); ?>"
														  style="cursor:pointer;"></span>
												</th>
												<th>
													<span style="vertical-align:bottom;"><?php echo __( 'HTML', 'wp-data-access' ); ?></span>
													<span class="dashicons dashicons-editor-help wpda_tooltip"
														  title="<?php echo __( 'Just the URL! For example:
														  
https://yoursite.com/services.php?name=$$column_name$$

Variable $$column_name$$ will be replaced with the value of column $$column_name$$ in table `' . esc_attr( $this->table_name ) . '`.', 'wp-data-access' ); ?>"
														  style="cursor:pointer;"></span>
												</th>
												<th></th>
											</tr>
											<?php
											$no_hyperlinks = 0;
											if ( isset( $settings_db_custom->hyperlinks ) && is_array( $settings_db_custom->hyperlinks ) ) {
												foreach ( $settings_db_custom->hyperlinks as $hyperlink ) {
													$hyperlink_label  = isset( $hyperlink->hyperlink_label ) ? $hyperlink->hyperlink_label : '';
													$hyperlink_list   = isset( $hyperlink->hyperlink_list ) && true === $hyperlink->hyperlink_list ? 'checked' : '';
													$hyperlink_form   = isset( $hyperlink->hyperlink_form ) && true === $hyperlink->hyperlink_form ? 'checked' : '';
													$hyperlink_target = isset( $hyperlink->hyperlink_target ) && true === $hyperlink->hyperlink_target ? 'checked' : '';
													$hyperlink_html   = isset( $hyperlink->hyperlink_html ) ? $hyperlink->hyperlink_html : '';
													?>
													<tr id="wpda_<?php echo esc_attr( $this->rownum ); ?>_add_hyperlink_<?php echo $no_hyperlinks; ?>">
														<td style="padding-left:0;vertical-align:top;">
															<input id="<?php echo esc_attr( $this->rownum ); ?>_hyperlink_label_<?php echo $no_hyperlinks; ?>"
																   class="wpda_action_font"
																   type="text"
																   value="<?php echo esc_attr( $hyperlink_label ); ?>"
															/>
															<input id="<?php echo esc_attr( $this->rownum ); ?>_hyperlink_label_<?php echo $no_hyperlinks; ?>_old"
																   type="hidden"
																   value="<?php echo esc_attr( $hyperlink_label ); ?>"
															/>
														</td>
														<td style="vertical-align:top;text-align:center;">
															<input id="<?php echo esc_attr( $this->rownum ); ?>_hyperlink_list_<?php echo $no_hyperlinks; ?>"
																   class="wpda_action_font"
																   type="checkbox"
																   style="margin-top:7px;"
																   <?php echo $hyperlink_list; ?>
															/>
														</td>
														<td style="vertical-align:top;text-align:center;">
															<input id="<?php echo esc_attr( $this->rownum ); ?>_hyperlink_form_<?php echo $no_hyperlinks; ?>"
																   class="wpda_action_font"
																   type="checkbox"
																   style="margin-top:7px;"
																   <?php echo $hyperlink_form; ?>
															/>
														</td>
														<td style="vertical-align:top;text-align:center;">
															<input id="<?php echo esc_attr( $this->rownum ); ?>_hyperlink_target_<?php echo $no_hyperlinks; ?>"
																   class="wpda_action_font"
																   type="checkbox"
																   style="margin-top:7px;"
																   <?php echo $hyperlink_target; ?>
															/>
														</td>
														<td style="vertical-align:top;">
															<textarea id="<?php echo esc_attr( $this->rownum ); ?>_hyperlink_html_<?php echo $no_hyperlinks; ?>"
																	  rows="5"
																	  class="wpda_action_font"
																	  style="min-width:300px;resize:both;"
															><?php echo urldecode( $hyperlink_html ); ?></textarea>
															<input id="<?php echo esc_attr( $this->rownum ); ?>_hyperlink_html_<?php echo $no_hyperlinks; ?>_old"
																   type="hidden"
																   value="<?php echo $hyperlink_html; ?>"
															/>
														</td>
														<td style="vertical-align:top;">
															<a href="javascript:void(0)"
															   class="dashicons dashicons-trash"
															   style="margin-top:4px;"
															   onclick="if (confirm('<?php echo sprintf( __( 'Delete hyperlink %s?', 'wp-data-access' ), esc_attr( $hyperlink_label ) ); ?>')) { jQuery('#wpda_<?php echo esc_attr( $this->rownum ); ?>_add_hyperlink_<?php echo $no_hyperlinks; ?>').remove(); }"
															></a>
														</td>
													</tr>
													<?php
													$no_hyperlinks++;
												}
											}
											?>
											<tr id="wpda_<?php echo esc_attr( $this->rownum ); ?>_add_hyperlink_link"
												<?php if ( 0 === $no_hyperlinks ) {
													echo 'style="display:none;background:inherit;"';
												} else {
													echo 'style="background:inherit;"';
												} ?>
											>
												<td colspan="3" style="padding-left:0;">
													<a class="wpda_table_settings_add_menu" href="javascript:void(0);"
													   onclick="jQuery('#wpda_<?php echo esc_attr( $this->rownum ); ?>_add_hyperlink_link').hide(); jQuery('#wpda_<?php echo esc_attr( $this->rownum ); ?>_add_hyperlink').show();">
														&raquo; Add Hyperlink
													</a>
												</td>
											</tr>
											<tr id="wpda_<?php echo esc_attr( $this->rownum ); ?>_add_hyperlink"
												<?php if ( 0 !== $no_hyperlinks ) {
													echo 'style="display:none;"';
												} ?>
											>
												<td style="padding-left:0;vertical-align:top;">
													<input id="<?php echo esc_attr( $this->rownum ); ?>_hyperlink_label_<?php echo $no_hyperlinks; ?>"
														   class="wpda_action_font"
														   type="text"
														   value=""
													/>
													<input id="<?php echo esc_attr( $this->rownum ); ?>_hyperlink_label_<?php echo $no_hyperlinks; ?>_old"
														   type="hidden"
														   value=""
													/>
												</td>
												<td style="vertical-align:top;text-align:center;">
													<input id="<?php echo esc_attr( $this->rownum ); ?>_hyperlink_list_<?php echo $no_hyperlinks; ?>"
														   class="wpda_action_font"
														   type="checkbox"
														   style="margin-top:7px;"
													/>
												</td>
												<td style="vertical-align:top;text-align:center;">
													<input id="<?php echo esc_attr( $this->rownum ); ?>_hyperlink_form_<?php echo $no_hyperlinks; ?>"
													       class="wpda_action_font"
													       type="checkbox"
														   style="margin-top:7px;"
													/>
												</td>
												<td style="vertical-align:top;text-align:center;">
													<input id="<?php echo esc_attr( $this->rownum ); ?>_hyperlink_target_<?php echo $no_hyperlinks; ?>"
														   class="wpda_action_font"
														   type="checkbox"
														   style="margin-top:7px;"
													/>
												</td>
												<td style="vertical-align:top;">
													<textarea id="<?php echo esc_attr( $this->rownum ); ?>_hyperlink_html_<?php echo $no_hyperlinks; ?>"
													          rows="5"
															  class="wpda_action_font"
															  style="min-width:300px;resize:both;"
													></textarea>
													<input id="<?php echo esc_attr( $this->rownum ); ?>_hyperlink_html_<?php echo $no_hyperlinks; ?>_old"
														   type="hidden"
														   value=""
													/>
												</td>
											</tr>
										</table>
										<br/>
										<div>
											<button type="button"
												   	id="wpda_<?php echo esc_attr( $this->rownum ); ?>_save_hyperlinks"
												   	class="button button-primary"
												   	style="font-size: 90%;">
												<span class="material-icons wpda_icon_on_button">check</span>
												<?php echo __( 'Save Dynamic Hyperlinks', 'wp-data-access' ); ?>
											</button>
											<button type="button"
												   	id="wpda_<?php echo esc_attr( $this->rownum ); ?>_cancel_hyperlinks"
												   	class="button button-primary"
												   	style="font-size: 90%;">
												<span class="material-icons wpda_icon_on_button">cancel</span>
												<?php echo __( 'Cancel', 'wp-data-access' ); ?>
											</button>
											<input id="no_hyperlink_<?php echo esc_attr( $this->rownum ); ?>"
												   type="hidden" value="<?php echo $no_hyperlinks; ?>">
										</div>
									</ul>
								</li>
								<li>
									<span class="wpda_table_settings_caret"><?php echo __( 'Dashboard Menus', 'wp-data-access' ); ?></span>
									<a href="https://wpdataaccess.com/docs/documentation/data-explorer/add-table-to-dashboard-menu/" target="_blank">
										<span class="dashicons dashicons-editor-help wpda_tooltip"
											  title="<?php echo sprintf( __( 'Add table `%s` to a WordPress dashboard menu [help opens in a new tab or window]', 'wp-data-access' ), esc_attr( $this->table_name ) ); ?>"
											  style="cursor:pointer;vertical-align:text-bottom;"></span>
									</a>
									<ul class="wpda_table_settings_nested">
										<table class="wpda_table_settings">
											<tr>
												<th style="padding-left:0;">
													<span style="vertical-align:bottom;"><?php echo __( 'Menu Name', 'wp-data-access' ); ?></span>
													<span class="dashicons dashicons-editor-help wpda_tooltip"
														  title="<?php echo __( 'Name of your sub menu item', 'wp-data-access' ); ?>"
														  style="cursor:pointer;"></span>
												</th>
												<th>
													<span style="vertical-align:bottom;"><?php echo __( 'Menu Slug', 'wp-data-access' ); ?></span>
													<span class="dashicons dashicons-editor-help wpda_tooltip"
														  title="<?php echo __( 'Menu slug of the main menu to which your sub menu should be added', 'wp-data-access' ); ?>"
														  style="cursor:pointer;"></span>
												</th>
												<th>
													<span style="vertical-align:bottom;"><?php echo __( 'Roles Authorized', 'wp-data-access' ); ?></span>
													<span class="dashicons dashicons-editor-help wpda_tooltip"
														  title="<?php echo __( 'User roles authorized to see sub menu item', 'wp-data-access' ); ?>"
														  style="cursor:pointer;"></span>
												</th>
											</tr>
											<?php
											$no_menus    = 0;
											$table_menus = WPDA_User_Menus_Model::get_table_menus( $this->table_name, $this->schema_name );
											foreach ( $table_menus as $table_menu ) {
												?>
												<tr>
													<td style="padding-left:0;vertical-align:top;">
														<input id="menu_name_<?php echo $no_menus; ?>"
															   class="wpda_action_font" type="text"
															   value="<?php echo esc_attr( $table_menu['menu_name'] ); ?>"
															   style="font-size: 90%;"
														>
														<input id="menu_name_<?php echo $no_menus; ?>_old" type="hidden"
															   value="<?php echo esc_attr( $table_menu['menu_name'] ); ?>">
													</td>
													<td style="vertical-align:top;">
														<input id="menu_slug_<?php echo $no_menus; ?>"
															   class="wpda_action_font" type="text"
															   value="<?php echo esc_attr( $table_menu['menu_slug'] ); ?>"
															   style="font-size: 90%;"
														>
														<input id="menu_slug_<?php echo $no_menus; ?>_old" type="hidden"
															   value="<?php echo esc_attr( $table_menu['menu_slug'] ); ?>">
													</td>
													<td>
														<select id="menu_roles_<?php echo $no_menus; ?>"
																class="wpda_action_font" multiple size="5"
																style="font-size: 90%;"
														>
															<?php
															global $wp_roles;
															foreach ( $wp_roles->roles as $role => $val ) {
																$selected = false !== strpos( $table_menu['menu_role'], $role ) ? 'selected' : '';
																$role_label = isset( $val['name'] ) ? $val['name'] : $role;
																echo "<option value='$role' $selected>$role_label</option>";
															}
															?>
														</select>
														<a href="javascript:void(0)"
														   class="dashicons dashicons-trash"
														   onclick="if (confirm('<?php echo sprintf( __( 'Delete menu %s?', 'wp-data-access' ), esc_attr( $table_menu['menu_name'] ) ); ?>')) { jQuery('#<?php echo esc_attr( $this->rownum ); ?>_menu_id').val('<?php echo esc_attr( $table_menu['menu_id'] ); ?>'); jQuery('#<?php echo $delete_menu_table_form_id; ?>').submit(); }"
														></a>
														<input id="menu_id_<?php echo $no_menus; ?>"
															   class="wpda_table_setting_item_menu" type="hidden"
															   value="<?php echo esc_attr( $table_menu['menu_id'] ); ?>">
														<input id="menu_role_<?php echo $no_menus; ?>_old" type="hidden"
															   value="<?php echo esc_attr( $table_menu['menu_role'] ); ?>">
													</td>
												</tr>
												<?php
												$no_menus++;
											}
											?>
											<tr id="wpda_<?php echo esc_attr( $this->rownum ); ?>_add_menu_link"
											<?php if ( 0 === $no_menus ) {
												echo 'style="display:none;background:inherit;"';
											} else {
												echo 'style="background:inherit;"';
											} ?>
											>
												<td colspan="3" style="padding-left:0;">
													<a class="wpda_table_settings_add_menu" href="javascript:void(0);"
													   onclick="jQuery('#wpda_<?php echo esc_attr( $this->rownum ); ?>_add_menu_link').hide(); jQuery('#wpda_<?php echo esc_attr( $this->rownum ); ?>_add_menu').show();">
														&raquo; Add menu
													</a>
												</td>
											</tr>
											<tr id="wpda_<?php echo esc_attr( $this->rownum ); ?>_add_menu"
											<?php if ( 0 !== $no_menus ) {
												echo 'style="display:none;"';
											} ?>
											>
												<td style="padding-left:0;vertical-align:top;">
													<input id="menu_name_<?php echo $no_menus; ?>"
														   class="wpda_action_font" type="text" value=""
														   style="font-size: 90%;"
													>
													<input id="menu_name_<?php echo $no_menus; ?>_old" type="hidden"
														   value="">
												</td>
												<td style="vertical-align:top;">
													<input id="menu_slug_<?php echo $no_menus; ?>"
														   class="wpda_action_font" type="text" value=""
														   style="font-size: 90%;"
													>
													<input id="menu_slug_<?php echo $no_menus; ?>_old" type="hidden"
														   value="">
												</td>
												<td>
													<select id="menu_roles_<?php echo $no_menus; ?>"
															class="wpda_action_font" multiple size="5"
															style="font-size: 90%;"
													>
														<?php
														global $wp_roles;
														foreach ( $wp_roles->roles as $role => $val ) {
															$role_label = isset( $val['name'] ) ? $val['name'] : $role;
															echo "<option value='$role'>$role_label</option>";
														}
														?>
													</select>
													<input id="menu_id_<?php echo $no_menus; ?>"
														   class="wpda_table_setting_item_menu" type="hidden" value="">
													<input id="menu_role_<?php echo $no_menus; ?>_old" type="hidden"
														   value="">
												</td>
											</tr>
										</table>
										<br/>
										<div>
											<button type="button"
											       	id="wpda_<?php echo esc_attr( $this->rownum ); ?>_save_dashboard_menus"
											       	class="button button-primary"
											       	style="font-size: 90%;"
											>
												<span class="material-icons wpda_icon_on_button">check</span>
												<?php echo __( 'Save Dashboard Menus', 'wp-data-access' ); ?>
											</button>
											<button type="button"
											       	id="wpda_<?php echo esc_attr( $this->rownum ); ?>_cancel_dashboard_menus"
											       	class="button button-primary"
											       	style="font-size: 90%;"
											>
												<span class="material-icons wpda_icon_on_button">cancel</span>
												<?php echo __( 'Cancel', 'wp-data-access' ); ?>
											</button>
										</div>
									</ul>
								</li>
								<?php do_action('wpda_append_table_settings'); ?>
							</ul>
							<input class="wpda_table_setting_item" type="hidden" id="wpda_<?php echo esc_attr( $this->rownum ); ?>_sql_dml"
								   value="<?php echo $sql_dml; ?>"
							/>
						</div>
					</td>
				</tr>
			</table>
			<script type='text/javascript'>
				jQuery(function () {
					// Menu actions
					jQuery('.wpda_table_settings_caret').off("click");
					jQuery('.wpda_table_settings_caret').on('click', function () {
						if (jQuery(this).hasClass('wpda_table_settings_caret_down')) {
							jQuery(this).parent().find('.wpda_table_settings_nested').toggle();
							jQuery(this).removeClass('wpda_table_settings_caret_down');
						} else {
							jQuery(this).parent().parent().find('.wpda_table_settings_caret_down').removeClass('wpda_table_settings_caret_down');
							jQuery(this).parent().parent().find('.wpda_table_settings_nested').hide();
							jQuery(this).parent().find('.wpda_table_settings_nested').toggle();
							jQuery(this).addClass('wpda_table_settings_caret_down');
						}
					});

					// Table settings
					jQuery('#wpda_<?php echo esc_attr( $this->rownum ); ?>_save_table_settings').click( function() {
						return submit_table_settings(
							'<?php echo esc_attr( $this->rownum ); ?>',
							'<?php echo $settings_table_top_form_settings; ?>',
							'<?php echo $settings_table_top_form_id; ?>',
							'<?php echo esc_attr( $this->table_name ); ?>'
						);
					});
					jQuery('#wpda_<?php echo esc_attr( $this->rownum ); ?>_cancel_table_settings').click( function() {
						jQuery('#wpda_admin_menu_actions_<?php echo esc_attr( $this->rownum ); ?>').toggle();
						wpda_toggle_row_actions('<?php echo esc_attr( $this->rownum ); ?>');
					});
					
					// Column settings
					jQuery('#wpda_<?php echo esc_attr( $this->rownum ); ?>_save_column_settings').click( function() {
						return submit_column_settings(
							'<?php echo esc_attr( $this->rownum ); ?>',
							'<?php echo $settings_table_form_settings; ?>',
							'<?php echo $settings_table_form_id; ?>'
						);
					});
					jQuery('#wpda_<?php echo esc_attr( $this->rownum ); ?>_cancel_column_settings').click( function() {
						jQuery('#wpda_admin_menu_actions_<?php echo esc_attr( $this->rownum ); ?>').toggle();
						wpda_toggle_row_actions('<?php echo esc_attr( $this->rownum ); ?>');
					});

					// Dynamic hyperlinks
					jQuery('#wpda_<?php echo esc_attr( $this->rownum ); ?>_save_hyperlinks').click( function() {
						return submit_hyperlinks(
							'<?php echo esc_attr( $this->rownum ); ?>',
							'<?php echo $settings_table_form_settings; ?>',
							'<?php echo $settings_table_form_id; ?>'
						);
					});
					jQuery('#wpda_<?php echo esc_attr( $this->rownum ); ?>_cancel_hyperlinks').click( function() {
						jQuery('#wpda_admin_menu_actions_<?php echo esc_attr( $this->rownum ); ?>').toggle();
						wpda_toggle_row_actions('<?php echo esc_attr( $this->rownum ); ?>');
					});

					// Dashboard menus
					jQuery('#wpda_<?php echo esc_attr( $this->rownum ); ?>_save_dashboard_menus').click( function() {
						return submit_dashboard_menus(
							'<?php echo esc_attr( $this->rownum ); ?>',
							'<?php echo $settings_table_form_settings; ?>',
							'<?php echo $settings_table_form_id; ?>'
						);
					});
					jQuery('#wpda_<?php echo esc_attr( $this->rownum ); ?>_cancel_dashboard_menus').click( function() {
						jQuery('#wpda_admin_menu_actions_<?php echo esc_attr( $this->rownum ); ?>').toggle();
						wpda_toggle_row_actions('<?php echo esc_attr( $this->rownum ); ?>');
					});
					jQuery('.wpda_tooltip').tooltip();
				});

				var custom_column_settings = [];
				<?php
					foreach ( $column_settings_add_column as $add_column ) {
						echo 'custom_column_settings.push("' . $add_column['name_prefix'] . '");';
					}
				?>
			</script>
			<?php
		}

		/**
		 * Provides content for tab Structure
		 */
		protected function tab_structure() {
			?>
			<table class="widefat striped rows wpda-structure-table">
				<tr>
					<th class="nobr"><strong><?php echo __( 'Column Name', 'wp-data-access' ); ?></strong></th>
					<th class="nobr"><strong><?php echo __( 'Data Type', 'wp-data-access' ); ?></strong></th>
					<th><strong><?php echo __( 'Collation', 'wp-data-access' ); ?></strong></th>
					<th><strong><?php echo __( 'Null?', 'wp-data-access' ); ?></strong></th>
					<th><strong><?php echo __( 'Key?', 'wp-data-access' ); ?></strong></th>
					<th class="nobr"><strong><?php echo __( 'Default Value', 'wp-data-access' ); ?></strong></th>
					<th style="width:80%;"><strong><?php echo __( 'Extra', 'wp-data-access' ); ?></strong></th>
				</tr>
				<?php
				foreach ( $this->table_structure as $column ) {
					?>
					<tr>
						<td class="nobr"><?php echo esc_attr( $column['Field'] ); ?></td>
						<td class="nobr"><?php echo esc_attr( $column['Type'] ); ?></td>
						<td class="nobr"><?php echo esc_attr( $column['Collation'] ); ?></td>
						<td class="nobr"><?php echo esc_attr( $column['Null'] ); ?></td>
						<td class="nobr"><?php echo esc_attr( $column['Key'] ); ?></td>
						<td class="nobr"><?php echo esc_attr( $column['Default'] ); ?></td>
						<td><?php echo esc_attr( $column['Extra'] ); ?></td>
					</tr>
					<?php
				}
				?>
			</table>
			<?php
		}

		protected function tab_foreign_keys() {
			if ( false !== strpos( $this->create_table_stmt_orig, 'ENGINE=InnoDB' ) ) {
				?>
				<table class="widefat striped rows wpda-structure-table">
					<tr>
						<th class="nobr">
							<strong><?php echo __( 'Constraint Name', 'wp-data-access' ); ?></strong>
						</th>
						<th class="nobr">
							<strong><?php echo __( 'Column Name', 'wp-data-access' ); ?></strong>
						</th>
						<th class="nobr">
							<strong><?php echo __( 'Referenced Table Name', 'wp-data-access' ); ?></strong>
						</th>
						<th class="nobr" style="width:80%;">
							<strong><?php echo __( 'Referenced Column Name', 'wp-data-access' ); ?></strong>
						</th>
					</tr>
					<?php
					if ( 0 === count( $this->foreign_keys ) ) {
						echo '<tr><td colspan="4">' . __( 'No foreign keys defined for this table', 'wp-data-access' ) . '</td></tr>';
					}
					$constraint_name = '';
					foreach ( $this->foreign_keys as $foreign_key ) {
						$show_item = $constraint_name !== $foreign_key['constraint_name'];
						?>
						<tr>
							<td class="nobr">
								<?php echo $show_item ? esc_attr( $foreign_key['constraint_name'] ) : ''; ?>
							</td>
							<td class="nobr">
								<?php echo esc_attr( $foreign_key['column_name'] ); ?>
							</td>
							<td class="nobr">
								<?php echo $show_item ? esc_attr( $foreign_key['referenced_table_name'] ) : ''; ?>
							</td>
							<td class="nobr">
								<?php echo esc_attr( $foreign_key['referenced_column_name'] ); ?>
							</td>
						</tr>
						<?php
						$constraint_name = $foreign_key['constraint_name'];
					}
					?>
				</table>
				<?php
			}
		}

		/**
		 * Provides content for tab Indexes
		 */
		protected function tab_index() {
			?>
			<table class="widefat striped rows wpda-structure-table">
				<tr>
					<th class="nobr"><strong><?php echo __( 'Index Name', 'wp-data-access' ); ?></strong></th>
					<th><strong><?php echo __( 'Unique?', 'wp-data-access' ); ?></strong></th>
					<th><strong>#</strong></th>
					<th class="nobr"><strong><?php echo __( 'Column Name', 'wp-data-access' ); ?></strong></th>
					<th><strong><?php echo __( 'Collation', 'wp-data-access' ); ?></strong></th>
					<th class="nobr"><strong><?php echo __( 'Index Prefix?', 'wp-data-access' ); ?></strong></th>
					<th><strong><?php echo __( 'Null?', 'wp-data-access' ); ?></strong></th>
					<th class="nobr" style="width:80%;">
						<strong><?php echo __( 'Index Type', 'wp-data-access' ); ?></strong></th>
				</tr>
				<?php
				if ( 0 === count( $this->indexes ) ) {
					echo '<tr><td colspan="8">' . __( 'No indexes defined for this table', 'wp-data-access' ) . '</td></tr>';
				}
				$current_index_name = '';
				foreach ( $this->indexes as $index ) {
					if ( $current_index_name !== $index['Key_name'] ) {
						$current_index_name = esc_attr( $index['Key_name'] );
						$new_index          = true;
					} else {
						$new_index = false;
					}
					?>
					<tr>
						<td class="nobr">
							<?php if ( $new_index ) {
								echo esc_attr( $index['Key_name'] );
							} ?>
						</td>
						<td class="nobr">
							<?php if ( $new_index ) {
								echo '0' === $index['Non_unique'] ? 'Yes' : 'No';
							} ?>
						</td>
						<td class="nobr">
							<?php echo esc_attr( $index['Seq_in_index'] ); ?>
						</td>
						<td class="nobr">
							<?php echo esc_attr( $index['Column_name'] ); ?>
						</td>
						<td class="nobr">
							<?php echo 'A' === $index['Collation'] ? 'Ascending' : 'Not sorted'; ?>
						</td>
						<td class="nobr">
							<?php echo esc_attr( $index['Sub_part'] ); ?>
						</td>
						<td class="nobr">
							<?php echo '' === $index['Null'] ? 'NO' : esc_attr( $index['Null'] ); ?>
						</td>
						<td><?php echo esc_attr( $index['Index_type'] ); ?></td>
					</tr>
					<?php
				}
				?>
			</table>
			<?php
		}

		/**
		 * Provides content for tab SQL
		 */
		protected function tab_sql() {
			?>
			<table class="widefat striped rows wpda-structure-table">
				<tr>
					<td>
						<?php echo wp_kses( $this->create_table_stmt, [ 'br' => [] ] ); ?>
					</td>
					<td style="text-align: right;">
						<a id="button-copy-clipboard-<?php echo esc_attr( $this->rownum ); ?>"
						   href="javascript:void(0)"
						   class="button button-primary"
						   data-clipboard-text="<?php echo $this->create_table_stmt_orig; ?>"
						>
							<span class="material-icons wpda_icon_on_button">content_copy</span>
							<?php echo __( 'Copy to clipboard', 'wp-data-access' ); ?>
						</a>
					</td>
				</tr>
			</table>
			<?php
		}

		/**
		 * Provides content for tab Actions
		 */
		protected function tab_actions() {
			?>
			<table class="widefat striped rows wpda-structure-table">
				<?php
				$this->tab_export();
				if ( $this->is_wp_table === false && ( 'Table' === $this->dbo_type || 'View' === $this->dbo_type ) ) {
					$this->tab_rename();
				}
				if ( 'Table' === $this->dbo_type ) {
					$this->tab_copy();
				}
				if ( 'Table' === $this->dbo_type && $this->is_wp_table === false ) {
					$this->tab_truncate();
				}
				if ( $this->is_wp_table === false && ( 'Table' === $this->dbo_type || 'View' === $this->dbo_type ) ) {
					$this->tab_drop();
				}
				if ( 'Table' === $this->dbo_type ) {
					$this->tab_optimize();
				}
				if ( 'Table' === $this->dbo_type ) {
					$this->tab_alter();
				}
				?>
			</table>
			<?php
		}

		/**
		 * Provides content for Export action
		 */
		protected function tab_export() {
			$check_export_access = 'true';
			if ( 'on' === WPDA::get_option( WPDA::OPTION_BE_CONFIRM_EXPORT ) ) {
				$check_export_access = "confirm('Export table $this->table_name?')";
			}
			$wp_nonce_action = 'wpda-export-*';
			$wp_nonce        = wp_create_nonce( $wp_nonce_action );
			$src             = '';
			if ( ! is_admin() ) {
				// Add admin path for public access
				$src = admin_url() . 'admin.php';
			}
			$src = "?action=wpda_export&type=table&wpdaschema_name={$this->schema_name}&table_names={$this->table_name}&_wpnonce=$wp_nonce&format_type=";

			global $wpdb;
			$export_variable_prefix = false;
			if ( strpos( $this->table_name, $wpdb->prefix ) === 0 ) {
				// Offer an extra SQL option: SQL (add WP prefix)
				$export_variable_prefix = true;
			}
			$export_variable_prefix_option = ( 'on' === WPDA::get_option( WPDA::OPTION_BE_EXPORT_VARIABLE_PREFIX ) );
			?>
			<tr>
				<td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
					<a href="<?php echo esc_attr( $src ); ?>"
					   id="wpda_export_button_<?php echo esc_attr( $this->rownum ); ?>"
					   target="_blank"
					   class="button button-primary"
					   onclick="if (<?php echo esc_attr( $check_export_access ); ?>) { update_export_button_<?php echo esc_attr( $this->rownum ); ?>(); return true; } else { return false; }"
					   style="display:block;"
					>
						<?php echo __( 'EXPORT', 'wp-data-access' ); ?>
					</a>
				</td>
				<td style="vertical-align:middle;">
					<span><?php echo __( 'Export', 'wp-data-access' ); ?> <strong><?php echo __( 'table', 'wp-data-access' ); ?> `<?php echo esc_attr( $this->table_name ); ?>`</strong> <?php echo __( 'to', 'wp-data-access' ); ?>: </span>
					<select id="format_type_<?php echo esc_attr( $this->rownum ); ?>"
							name="format_type"
							class="wpda_action_font"
							style="height:inherit;padding-top:3px;">
						<?php if ( 'Table' === $this->dbo_type ) { ?>
							<option value="sql" <?php echo $export_variable_prefix_option ? '' : 'selected'; ?>>SQL</option>
							<?php if ( $export_variable_prefix ) { ?>
								<option value="sqlpre" <?php echo $export_variable_prefix_option ? 'selected' : ''; ?>>
									<?php echo __( 'SQL (add WP prefix)', 'wp-data-access' ); ?>
								</option>
							<?php } ?>
						<?php } ?>
						<option value="xml">XML</option>
						<option value="json">JSON</option>
						<option value="excel">Excel</option>
						<option value="csv">CSV</option>
					</select>
					<label style="vertical-align:text-top;">
						<input id="include_table_settings_<?php echo esc_attr( $this->rownum ); ?>"
							   type="checkbox"
							   class="wpda_action_font"
						>
						<?php echo __( 'Include table settings (SQL only)', 'wp-data-access' ); ?>&nbsp;
					</label>
					<script type="text/javascript">
						function update_export_button_<?php echo esc_attr( $this->rownum ); ?>() {
							jQuery('#wpda_export_button_<?php echo esc_attr( $this->rownum ); ?>').attr(
								'href',
								'<?php echo str_replace( "&amp;", "&", esc_attr( $src ) ); ?>' +
								jQuery('#format_type_<?php echo esc_attr( $this->rownum ); ?>').val() +
								'&include_table_settings=' +
								(jQuery('#include_table_settings_<?php echo esc_attr( $this->rownum ); ?>').prop('checked') ? 'on' : 'off')
							);
						}
					</script>
				</td>
			</tr>
			<?php
		}

		/**
		 * Provides content for Rename action
		 */
		protected function tab_rename() {
			$wp_nonce_action_rename = "wpda-rename-{$this->table_name}";
			$wp_nonce_rename        = wp_create_nonce( $wp_nonce_action_rename );
			$rename_table_form_id   = 'rename_table_form_' . esc_attr( $this->table_name );
			$rename_table_form      =
				"<form" .
				" id='" . $rename_table_form_id . "'" .
				" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_MAIN ) . "'" .
				" method='post'>" .
				"<input type='hidden' name='action' value='rename-table' />" .
				"<input type='hidden' name='rename_table_name_old' value='" . esc_attr( $this->table_name ) . "' />" .
				"<input type='hidden' name='rename_table_name_new' id='rename_table_name_" . esc_attr( $this->rownum ) . "' value='' />" .
				"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_rename ) . "' />" .
				"</form>";
			?>
			<tr>
				<td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
					<script type='text/javascript'>
						jQuery("#wpda_invisible_container").append("<?php echo $rename_table_form; ?>");
					</script>
					<a href="javascript:void(0)"
					   class="button button-primary"
					   onclick="if (jQuery('#rename-table-from-<?php echo esc_attr( $this->rownum ); ?>').val()==='') { alert('<?php echo __( 'Please enter a valid table name', 'wp-data-access' ); ?>'); return false; } if (confirm('<?php echo __( 'Rename', 'wp-data-access' ) . ' ' . esc_attr( strtolower( $this->dbo_type ) ) . '?'; ?>')) { jQuery('#rename_table_name_<?php echo esc_attr( $this->rownum ); ?>').val(jQuery('#rename-table-from-<?php echo esc_attr( $this->rownum ); ?>').val()); jQuery('#<?php echo $rename_table_form_id; ?>').submit(); }"
					   style="display:block;"
					>
						<?php echo __( 'RENAME', 'wp-data-access' ); ?>
					</a>
				</td>
				<td style="vertical-align:middle;">
					<?php echo __( 'Rename', 'wp-data-access' ); ?>
					<strong><?php echo esc_attr( strtolower( $this->dbo_type ) ); ?>
						`<?php echo esc_attr( $this->table_name ); ?>`</strong> to:
					<input type="text" id="rename-table-from-<?php echo esc_attr( $this->rownum ); ?>" value=""
						   class="wpda_action_font">
				</td>
			</tr>
			<?php
		}

		/**
		 * Provides content for Copy action
		 */
		protected function tab_copy() {
			$wp_nonce_action_copy = "wpda-copy-{$this->table_name}";
			$wp_nonce_copy        = wp_create_nonce( $wp_nonce_action_copy );
			$copy_table_form_id   = 'copy_table_form_' . esc_attr( $this->table_name );
			$copy_table_form      =
				"<form" .
				" id='$copy_table_form_id'" .
				" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_MAIN ) . "'" .
				" method='post'>" .
				"<input type='hidden' name='action' value='copy-table' />" .
				"<input type='hidden' name='copy_table_name_src' value='" . esc_attr( $this->table_name ) . "' />" .
				"<input type='hidden' name='copy_table_name_dst' id='copy_table_name_" . esc_attr( $this->table_name ) . "' value='' />" .
				"<input type='checkbox' name='copy-table-data' id='copy_table_data_" . esc_attr( $this->rownum ) . "' checked />" .
				"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_copy ) . "' />" .
				"</form>";
			?>
			<tr>
				<td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
					<script type='text/javascript'>
						jQuery("#wpda_invisible_container").append("<?php echo $copy_table_form; ?>");
					</script>
					<a href="javascript:void(0)"
					   class="button button-primary"
					   onclick="if (jQuery('#copy-table-from-<?php echo esc_attr( $this->rownum ); ?>').val()==='') { alert('<?php echo __( 'Please enter a valid table name', 'wp-data-access' ); ?>'); return false; } if (confirm('<?php echo __( 'Copy', 'wp-data-access' ) . ' ' . esc_attr( strtolower( $this->dbo_type ) ) . '?'; ?>')) { jQuery('#copy_table_name_<?php echo esc_attr( $this->table_name ); ?>').val(jQuery('#copy-table-from-<?php echo esc_attr( $this->rownum ); ?>').val()); jQuery('#<?php echo $copy_table_form_id; ?>').submit(); }"
					   style="display:block;"
					>
						<?php echo __( 'COPY', 'wp-data-access' ); ?>
					</a>
				</td>
				<td style="vertical-align:middle;">
					<?php echo __( 'Copy', 'wp-data-access' ); ?>
					<strong><?php echo esc_attr( strtolower( $this->dbo_type ) ); ?>
						`<?php echo esc_attr( $this->table_name ); ?>
						`</strong> <?php echo __( 'to', 'wp-data-access' ); ?>:
					<input type="text" id="copy-table-from-<?php echo esc_attr( $this->rownum ); ?>" value=""
						   class="wpda_action_font">
					<label style="vertical-align:baseline">
						<input type="checkbox"
							   checked
							   onclick="jQuery('#copy_table_data_<?php echo esc_attr( $this->rownum ); ?>').prop('checked', jQuery(this).is(':checked'));"
							   class="wpda_action_font"
						>
						<?php echo __( 'Copy data', 'wp-data-access' ); ?>
					</label>
				</td>
			</tr>
			<?php
		}

		/**
		 * Provides content for Truncate action
		 */
		protected function tab_truncate() {
			$wp_nonce_action_truncate = "wpda-truncate-{$this->table_name}";
			$wp_nonce_truncate        = wp_create_nonce( $wp_nonce_action_truncate );
			$truncate_table_form_id   = 'truncate_table_form_' . esc_attr( $this->table_name );
			$truncate_table_form      =
				"<form" .
				" id='$truncate_table_form_id'" .
				" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_MAIN ) . "'" .
				" method='post'>" .
				"<input type='hidden' name='action' value='truncate' />" .
				"<input type='hidden' name='truncate_table_name' value='" . esc_attr( $this->table_name ) . "' />" .
				"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_truncate ) . "' />" .
				"</form>";
			?>
			<tr>
				<td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
					<script type='text/javascript'>
						jQuery("#wpda_invisible_container").append("<?php echo $truncate_table_form; ?>");
					</script>
					<a href="javascript:void(0)"
					   class="button button-primary"
					   onclick="if (confirm('<?php echo __( 'Truncate table?', 'wp-data-access' ); ?>')) { jQuery('#<?php echo $truncate_table_form_id; ?>').submit(); }"
					   style="display:block;"
					>
						<?php echo __( 'TRUNCATE', 'wp-data-access' ); ?>
					</a>
				</td>
				<td style="vertical-align:middle;">
					<?php echo __( 'Permanently delete all data from', 'wp-data-access' ); ?>
					<strong><?php echo esc_attr( strtolower( $this->dbo_type ) ); ?>
						`<?php echo esc_attr( $this->table_name ); ?>`</strong>
					.<br/>
					<strong><?php echo __( 'This action cannot be undone!', 'wp-data-access' ); ?></strong>
				</td>
			</tr>
			<?php
		}

		/**
		 * Provides content for Drop action
		 */
		protected function tab_drop() {
			$wp_nonce_action_drop = "wpda-drop-{$this->table_name}";
			$wp_nonce_drop        = wp_create_nonce( $wp_nonce_action_drop );
			if ( 'View' === $this->dbo_type ) {
				$msg_drop = __( 'Drop view?', 'wp-data-access' );
			} else {
				$msg_drop = __( 'Drop table?', 'wp-data-access' );
			}
			$drop_table_form_id = 'drop_table_form_' . esc_attr( $this->table_name );
			$drop_table_form    =
				"<form" .
				" id='$drop_table_form_id'" .
				" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_MAIN ) . "'" .
				" method='post'>" .
				"<input type='hidden' name='action' value='drop' />" .
				"<input type='hidden' name='drop_table_name' value='" . esc_attr( $this->table_name ) . "' />" .
				"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_drop ) . "' />" .
				"</form>";
			?>
			<tr>
				<td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
					<script type='text/javascript'>
						jQuery("#wpda_invisible_container").append("<?php echo $drop_table_form; ?>");
					</script>
					<a href="javascript:void(0)"
					   class="button button-primary"
					   onclick="if (confirm('<?php echo $msg_drop; ?>')) { jQuery('#<?php echo $drop_table_form_id; ?>').submit(); }"
					   style="display:block;"
					>
						<?php echo __( 'DROP', 'wp-data-access' ); ?>
					</a>
				</td>
				<td style="vertical-align:middle;">
					<?php echo __( 'Permanently delete', 'wp-data-access' ); ?>
					<strong><?php echo esc_attr( strtolower( $this->dbo_type ) ); ?>
						`<?php echo esc_attr( $this->table_name ); ?>`</strong>
					<?php echo __( 'and all table data from the database.', 'wp-data-access' ); ?><br/>
					<strong><?php echo __( 'This action cannot be undone!', 'wp-data-access' ); ?></strong>
				</td>
			</tr>
			<?php
		}

		/**
		 * Provides content for Optimize action
		 *
		 * Data_length
		 * Index_length
		 * Data_free
		 */
		protected function tab_optimize() {
			$wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( null === $wpdadb ) {
				wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
			}

			$table_structure             = $wpdadb->get_row( $wpdadb->prepare( 'show table status like %s', $this->table_name ) );
			$query_innodb_file_per_table = $wpdadb->get_row( "show session variables like 'innodb_file_per_table'" );

			if ( ! empty( $query_innodb_file_per_table ) ) {
				$innodb_file_per_table = ( 'ON' === $query_innodb_file_per_table->Value );
			} else {
				$innodb_file_per_table = true;
			}

			if ( 'InnoDB' === $table_structure->Engine && ! $innodb_file_per_table ) {
				return;
			}

			$consider_optimize =
				$table_structure->Data_free > 0 && $table_structure->Data_length > 0 &&
				( $table_structure->Data_free / $table_structure->Data_length > 0.2 );

			$wp_nonce_action_optimize = "wpda-optimize-{$this->table_name}";
			$wp_nonce_optimize        = wp_create_nonce( $wp_nonce_action_optimize );
			$optimize_table_form_id   = 'optimize_table_form_' . esc_attr( $this->table_name );
			$optimize_table_form      =
				"<form" .
				" id='$optimize_table_form_id'" .
				" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_MAIN ) . "'" .
				" method='post'>" .
				"<input type='hidden' name='action' value='optimize-table' />" .
				"<input type='hidden' name='optimize_table_name' value='" . esc_attr( $this->table_name ) . "' />" .
				"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_optimize ) . "' />" .
				"</form>";
			$msg_optimize             = __( 'Optimize table?', 'wp-data-access' );
			?>
			<tr>
				<td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
					<script type='text/javascript'>
						jQuery("#wpda_invisible_container").append("<?php echo $optimize_table_form; ?>");
					</script>
					<a href="javascript:void(0)"
					   class="button button-primary"
					   onclick="if (confirm('<?php echo $msg_optimize; ?>')) { jQuery('#<?php echo $optimize_table_form_id; ?>').submit(); }"
					   style="display:block;<?php if ( ! $consider_optimize ) {
						   echo 'opacity:0.5;';
					   } ?>"
					>
						<?php echo __( 'OPTIMIZE', 'wp-data-access' ); ?>
					</a>
				</td>
				<td style="vertical-align:middle;<?php if ( ! $consider_optimize ) {
					echo 'opacity:0.5;';
				} ?>">
					<?php echo __( 'Optimize', 'wp-data-access' ); ?>
					<strong><?php echo esc_attr( strtolower( $this->dbo_type ) ); ?>
						`<?php echo esc_attr( $this->table_name ); ?>`</strong>.<br/>
					<?php
					if ( $consider_optimize ) {
						?>
						<strong><?php echo __( 'MySQL locks the table during the time OPTIMIZE TABLE is running!', 'wp-data-access' ); ?></strong>
						<?php
					} else {
						?>
						<strong><?php echo __( 'Table optimization not considered useful! But you can...', 'wp-data-access' ); ?></strong>
						<?php
					}
					?>
				</td>
			</tr>
			<?php
		}

		/**
		 * Provides content for Alter action
		 */
		protected function tab_alter() {
			$wp_nonce_action_alter = "wpda-alter-{$this->table_name}";
			$wp_nonce_alter        = wp_create_nonce( $wp_nonce_action_alter );
			$alter_table_form_id   = 'alter_table_form_' . esc_attr( $this->table_name );
			$alter_table_form      =
				"<form" .
				" id='$alter_table_form_id'" .
				" action='?page=" . esc_attr( \WP_Data_Access_Admin::PAGE_DESIGNER ) . "'" .
				" method='post'>" .
				"<input type='hidden' name='action' value='edit' />" .
				"<input type='hidden' name='action2' value='init' />" .
				"<input type='hidden' name='wpda_schema_name' value='" . esc_attr( $this->schema_name ) . "' />" .
				"<input type='hidden' name='wpda_schema_name_re' value='" . esc_attr( $this->schema_name ) . "' />" .
				"<input type='hidden' name='wpda_table_name' value='" . esc_attr( $this->table_name ) . "' />" .
				"<input type='hidden' name='wpda_table_name_re' value='" . esc_attr( $this->table_name ) . "' />" .
				"<input type='hidden' name='_wpnonce' value='" . esc_attr( $wp_nonce_alter ) . "' />" .
				"<input type='hidden' name='page_number' value='1' />" .
				"<input type='hidden' name='caller' value='dataexplorer' />" .
				"</form>";
			?>
			<tr>
				<td style="box-sizing:border-box;text-align:center;white-space:nowrap;width:150px;vertical-align:middle;">
					<script type='text/javascript'>
						jQuery("#wpda_invisible_container").append("<?php echo $alter_table_form; ?>");
					</script>
					<a href="javascript:void(0)"
					   class="button button-primary"
					   onclick="if (confirm('<?php echo __( 'Alter table?', 'wp-data-access' ); ?>')) { jQuery('#<?php echo $alter_table_form_id; ?>').submit(); }"
					   style="display:block;"
					>
						<?php echo __( 'ALTER', 'wp-data-access' ); ?>
					</a>
				</td>
				<td style="vertical-align:middle;">
					<?php echo __( 'Loads', 'wp-data-access' ); ?>
					<strong><?php echo esc_attr( strtolower( $this->dbo_type ) ); ?>
						`<?php echo esc_attr( $this->table_name ); ?>`</strong>
					<?php echo __( 'into the Data Designer.', 'wp-data-access' ); ?>
				</td>
			</tr>
			<?php
		}

	}

}
