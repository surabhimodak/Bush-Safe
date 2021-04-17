<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Project
 */

namespace WPDataProjects\Project {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists;
	use WPDataAccess\Plugin_Table_Models\WPDA_Table_Settings_Model;
	use WPDataProjects\Data_Dictionary\WPDP_List_Columns_Cache;
	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataAccess\Plugin_Table_Models\WPDP_Project_Design_Table_Model;
	use \WPDataAccess\Utilities\WPDA_Reverse_Engineering;
	use WPDataProjects\WPDP;

	/**
	 * Class WPDP_Project_Table_Form
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Project_Table_Form {

		/**
		 * Menu slug
		 *
		 * @var string
		 */
		protected $page = null;

		/**
		 * Database schema name
		 *
		 * @var string
		 */
		protected $wpda_schema_name = '';

		/**
		 * Database schema name (as saved in column wpda_schema_name)
		 *
		 * Used to allow older versions of the plugin to have an empty schema name stored in column wpda_schema_name.
		 *
		 * @var string
		 */
		protected $wpda_schema_name_db = '';

		/**
		 * Database table name
		 *
		 * @var string
		 */
		protected $wpda_table_name = null;

		/**
		 * Options set name
		 *
		 * @var string
		 */
		protected $wpda_table_setname = null;

		/**
		 * Database table structure
		 *
		 * @var array|null
		 */
		protected $table_structure = null;

		/**
		 * Handle to instance of class WPDA_Dictionary_Exist
		 *
		 * @var object|null
		 */
		protected $wpda_data_dictionary = null;

		/**
		 * Handle to instance of class WPDP_List_Columns
		 *
		 * @var object|null
		 */
		protected $wpda_list_columns = null;

		/**
		 * Requested action
		 *
		 * @var string
		 */
		protected $action = null;

		/**
		 * Requested action2
		 *
		 * @var string
		 */
		protected $action2 = null;

		/**
		 * Indicate whether the table has a primary key
		 *
		 * @var boolean|null
		 */
		protected $has_primary_key = null;

		/**
		 * Available tabs
		 *
		 * @var array
		 */
		protected $tabs;

		/**
		 * Current tab
		 *
		 * @var
		 */
		protected $current_tab;

		const WPNONCE_SEED = 'wpda-data-templates-';

		protected $wpnonce;
		protected $wpnonce_requested = null;

		/**
		 * WPDP_Project_Table_Form constructor
		 */
		public function __construct() {
			global $wpdb;

			if ( isset( $_REQUEST['page'] ) ) {
				$this->page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ); // input var okay.
			} else {
				wp_die( __( 'ERROR: Wrong arguments [missing page]', 'wp-data-access' ) );
			}

			if ( isset( $_REQUEST['action'] ) ) {
				$this->action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
			}

			if ( isset( $_REQUEST['action2'] ) ) {
				$this->action2 = sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) );
			}

			$this->tabs       = [
				'tableinfo' => __( 'Table Settings', 'wp-data-access' ),
				'relation'  => __( 'Relationships', 'wp-data-access' ),
				'listtable' => __( 'List Table', 'wp-data-access' ),
				'tableform' => __( 'Data Entry', 'wp-data-access' ),
				'reconcile' => __( 'Reconcile', 'wp-data-access' ),
			];
			$this->current_tab = 'tableinfo';
			if ( isset( $_REQUEST['tab'] ) ) {
				$tab = sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ); // input var okay.
				if ( isset( $this->tabs[ $tab ] ) ) {
					$this->current_tab = $tab;
				}
			}

			$this->wpnonce_requested = isset( $_POST['wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wpnonce'] ) ) : null; // input var okay.

			if ( isset( $_REQUEST['wpda_table_name'] ) && ! is_array( $_REQUEST['wpda_table_name'] ) ) {
				$wpda_project_design_table_model = new WPDP_Project_Design_Table_Model();
				if ( 'reconcile' === $this->action ) {
					$wpda_table_name_re = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_name'] ) ); // input var okay.
					$this->is_authorized( $wpda_table_name_re );

					$wpda_schema_name_re      = isset( $_REQUEST['wpda_schema_name'] ) ?
						sanitize_text_field( wp_unslash( $_REQUEST['wpda_schema_name'] ) ) : ''; // input var okay.
					$wpda_reverse_engineering = new WPDA_Reverse_Engineering( $wpda_table_name_re, $wpda_schema_name_re );
					$table_structure          = $wpda_reverse_engineering->get_designer_format( 'advanced' );
					if ( isset( $_REQUEST['keep_options'] ) ) {
						$param_keep_options = sanitize_text_field( wp_unslash( $_REQUEST['keep_options'] ) );
					} else {
						$param_keep_options = 'off';
					}
					$result_update = $wpda_project_design_table_model->reconcile( $table_structure, $param_keep_options );
					if ( false === $result_update ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text'           => __( 'Update failed', 'wp-data-access' ),
								'message_type'           => 'error',
								'message_is_dismissible' => false,
							]
						);
						$msg->box();
					}
					if ( 0 === $result_update ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text' => __( 'Nothing to save', 'wp-data-access' ),
							]
						);
						$msg->box();
					} else {
						$msg = new WPDA_Message_Box(
							[
								'message_text' => __( 'Succesfully saved changes to database', 'wp-data-access' ),
							]
						);
						$msg->box();
					}
				} elseif ( 'reverse_engineering' === $this->action ) {
					if ( isset( $_REQUEST['wpda_table_name'] ) ) {
						$wpda_table_name_re       = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_name'] ) ); // input var okay.
						$this->is_authorized( WPDP_Project_Design_Table_Model::get_base_table_name() );

						$wpda_schema_name_re      = isset( $_REQUEST['wpda_schema_name'] ) ?
							sanitize_text_field( wp_unslash( $_REQUEST['wpda_schema_name'] ) ) : ''; // input var okay.
						$wpda_reverse_engineering = new WPDA_Reverse_Engineering( $wpda_table_name_re, $wpda_schema_name_re );
						$table_structure          = $wpda_reverse_engineering->get_designer_format( 'advanced' );
						if ( count( $table_structure ) > 0 ) {
							$this->wpda_schema_name   = $wpda_schema_name_re;
							$this->wpda_table_name    = $wpda_table_name_re;
							$this->get_unique_setname();
							$this->wpda_table_design  = $table_structure;
						} else {
							wp_die( __( 'ERROR: Reverse engineering table failed [invalid structure]', 'wp-data-access' ) );
						}
						if ( ! WPDP_Project_Design_Table_Model::insert_reverse_engineered(
								$this->wpda_table_name,
								$this->wpda_table_setname,
								$this->wpda_table_design,
								$this->wpda_schema_name
						) ) {
							$db_error = '' === $wpdb->last_error ? '' : ' [' . $wpdb->last_error . ']';
							wp_die( __( 'ERROR: Reverse engineering table failed' . $db_error, 'wp-data-access' ) );
						} else {
							// Convert named array to object (needed to display structure).
							$this->wpda_table_design = json_decode( json_encode( $table_structure ) );
							$wpda_project_design_table_model->prepare_query( $this->wpda_table_setname );
						}
						$this->action2 = 'edit';
						$msg           = new WPDA_Message_Box(
							[
								'message_text' => __( 'Table added to respository', 'wp-data-access' ),
							]
						);
						$msg->box();
					} else {
						wp_die( __( 'ERROR: Wrong arguments', 'wp-data-access' ) );
					}
				} elseif ( null !== $this->action2 ) {
					// Check authorization
					$table_name = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_name'] ) ); // input var okay.
					$this->is_authorized( $table_name );

					$result_update = $wpda_project_design_table_model->update();
					if ( false === $result_update ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text'           => __( 'Update failed', 'wp-data-access' ),
								'message_type'           => 'error',
								'message_is_dismissible' => false,
							]
						);
						$msg->box();
					} else {
						if ( 0 === $result_update ) {
							$msg = new WPDA_Message_Box(
								[
									'message_text' => __( 'Nothing to save', 'wp-data-access' ),
								]
							);
							$msg->box();
						} else {
							$msg = new WPDA_Message_Box(
								[
									'message_text' => __( 'Succesfully saved changes to database', 'wp-data-access' ),
								]
							);
							$msg->box();
						}
					}
				}

				$wpda_project_design_table_model->query();
				$structure_messages = $wpda_project_design_table_model->validate();

				foreach ( $structure_messages as $messages ) {
					if ( 'ERR' === $messages[0] ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text'           => $messages[1],
								'message_type'           => 'error',
								'message_is_dismissible' => false,
							]
						);
						$msg->box();
					} else {
						$msg = new WPDA_Message_Box(
							[
								'message_text' => $messages[1],
							]
						);
						$msg->box();
					}
				}

				$this->table_structure      = $wpda_project_design_table_model->get_table_design();
				$this->wpda_table_setname	= $wpda_project_design_table_model->get_table_setname();
				$this->wpda_schema_name     = isset( $_REQUEST['wpda_schema_name'] ) ?
					sanitize_text_field( wp_unslash( $_REQUEST['wpda_schema_name'] ) ) : ''; // input var okay.
				$this->wpda_schema_name_db  = $this->wpda_schema_name;
				if ( '' === $this->wpda_schema_name ) {
					$this->wpda_schema_name = $wpdb->dbname;
				}
				$this->wpda_table_name      = sanitize_text_field( wp_unslash( $_REQUEST['wpda_table_name'] ) );
				$this->wpda_data_dictionary = new WPDA_Dictionary_Exist( $this->wpda_schema_name, $this->wpda_table_name );

				if ( $this->wpda_data_dictionary->table_exists() ) {
					$this->wpda_list_columns = WPDP_List_Columns_Cache::get_list_columns( $this->wpda_schema_name, $this->wpda_table_name, 'tableform', $this->wpda_table_setname );
					if ( 0 === count( $this->wpda_list_columns->get_table_primary_key() ) ) {
						$this->has_primary_key = false;
					} else {
						$this->has_primary_key = true;
					}
				} else {
					wp_die( __( 'ERROR: Invalid table name or not authorized', 'wp-data-access' ) );
				}
			} else {
				wp_die( __( 'ERROR: Argument wpda_table_name not found', 'wp-data-access' ) );
			}

			$this->wpnonce = wp_create_nonce( self::WPNONCE_SEED . $this->wpda_table_name );
		}

		protected function is_authorized( $table_name ) {
			if ( ! wp_verify_nonce( $this->wpnonce_requested, self::WPNONCE_SEED . $table_name ) ) {
				wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
			}
		}

		/**
		 * Create a unique setname for this table
		 */
		public function get_unique_setname() {
			$this->wpda_table_setname = 'default'; // Default for first options set

			global $wpdb;
			$query = "select 'x' from " . WPDP_Project_Design_Table_Model::get_base_table_name() .
					 ' where wpda_table_name = %s';

			$wpdb->get_results( $wpdb->prepare( $query, [ $this->wpda_table_name ] ) );
			if ( $wpdb->num_rows > 0 ) {
				$query = "select 'x' from " . WPDP_Project_Design_Table_Model::get_base_table_name() .
				         ' where wpda_schema_name = %s and wpda_table_name = %s and wpda_table_setname = %s';

				$i = $wpdb->num_rows + 1;
				$this->wpda_table_setname = "options_set_$i";
				$wpdb->get_results( $wpdb->prepare( $query, [ $this->wpda_schema_name, $this->wpda_table_name, $this->wpda_table_setname ] ) );
				while ( $wpdb->num_rows > 0 ) {
					// Search untill a free options set is found
					$this->wpda_table_setname = "options_set_$i";
					$wpdb->get_results( $wpdb->prepare( $query, [ $this->wpda_schema_name, $this->wpda_table_name, $this->wpda_table_setname ] ) );
					$i++;
				}
			}
		}

		/**
		 * Adds tabs to page
		 */
		protected function add_tabs() {
			?>
			<div style="display:none">
				<?php
				foreach ( $this->tabs as $tab => $name ) {
					$form_id     = esc_attr( $tab ) . '_form_id';
					$page        = esc_attr( WPDP::PAGE_TEMPLATES );
					$schema_name = esc_attr( $this->wpda_schema_name_db );
					$table_name  = esc_attr( $this->wpda_table_name );
					$set_name    = esc_attr( $this->wpda_table_setname );
					$tab_value   = esc_attr( $tab );

					echo "
						<form id='{$form_id}' method='post' action='?page={$page}&tab={$tab_value}'>
							<input type='hidden' name='wpda_schema_name' value='{$schema_name}'/>
							<input type='hidden' name='wpda_table_name' value='{$table_name}'/>
							<input type='hidden' name='wpda_table_setname' value='{$set_name}'/>
							<input type='hidden' name='wpda_table_setname_old' value='{$set_name}'/>
							<input type='hidden' name='action' value='edit'/>
						</form>
					";
				}
				?>
			</div>
			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( $this->tabs as $tab => $name ) {
					$class = ( $tab === $this->current_tab ) ? ' nav-tab-active' : '';
					echo
						'<a class="nav-tab' . esc_attr( $class ) . '"' .
							' href="javascript:void(0)"' .
							' onclick="submit_form(\'' . esc_attr( $tab ) . '_form_id\')"' .
						'>' . esc_attr( $name ) . '</a>';
				}
				?>
			</h2>
			<div class="wpdp-spacer"></div>
			<?php
		}

		/**
		 * Show page
		 */
		public function show() {
			$this->add_tabs();

			switch ( $this->current_tab ) {
				case "tableinfo":
					$this->show_table_info();
					break;
				case "relation":
					if ( true === $this->has_primary_key ) {
						$this->show_relations();
					} else {
						$this->show_view();
					}
					break;
				case "listtable":
					$this->show_list_table();
					break;
				case "tableform":
					if ( true === $this->has_primary_key ) {
						$this->show_table_form();
					} else {
						$this->show_view();
					}
					break;
				case "reconcile":
					$this->show_reconcile();
					break;
			}
			?>
			<script type='text/javascript'>
				function submit_form(form_id) {
					if (wpda_columns_updated===true) {
						if (confirm("Your changes will not be saved! Continue?")) {
							jQuery('#' + form_id).submit();
						}
					} else {
						jQuery('#' + form_id).submit();
					}
				}
				jQuery(function () {
					jQuery('#wpda_table_structure').sortable();
					jQuery('#wpda_list_table_options').sortable();
					jQuery('#wpda_form_options').sortable();
					jQuery('.wpda_tooltip').tooltip();
				});
			</script>
			<?php
			if ( 'view' === $this->action ) {
				// Set all forms to read only
				?>
				<script type='text/javascript'>
					jQuery(function () {
						jQuery("input").attr("disabled", true);
						jQuery("select").attr("disabled", true);
					});
				</script>
				<?php
			} else {
				// Keep track of changes
				?>
				<script type='text/javascript'>
					var wpda_columns_updated = false;
					jQuery(function () {
						jQuery("input").on("change", function() { wpda_columns_updated = true; });
						jQuery("select").on("change", function() { wpda_columns_updated = true; });
					});
				</script>
				<?php
			}
		}

		protected function show_view() {
			?>
			<div class="wpdp-spacer"></div>
			<div>
				<fieldset class="wpda_fieldset wpdp_fieldset">
					<strong>
						Sorry, this feature is only available for tables containing a primary or unique key.
					</strong>
				</fieldset>
			</div>
			<?php
		}

		/**
		 * Show table info
		 */
		protected function show_table_info() {
			global $wpdb;
			if ( '' === $this->wpda_schema_name || $wpdb->dbname === $this->wpda_schema_name ) {
				$wpda_table_name = $this->wpda_table_name;
			} else {
				$wpda_table_name = $this->wpda_table_name . ' (' . $this->wpda_schema_name . ')';
			}

			$tab_label         = isset( $this->table_structure->tableinfo->tab_label ) ?
				$this->table_structure->tableinfo->tab_label : '';
			$defaul_where      = isset( $this->table_structure->tableinfo->default_where ) ?
				$this->table_structure->tableinfo->default_where : '';
			$defaul_orderby    = isset( $this->table_structure->tableinfo->default_orderby ) ?
				$this->table_structure->tableinfo->default_orderby : '';
			$hyperlinks_parent = isset( $this->table_structure->tableinfo->hyperlinks_parent ) ?
				$this->table_structure->tableinfo->hyperlinks_parent : [];
			$hyperlinks_child  = isset( $this->table_structure->tableinfo->hyperlinks_child ) ?
				$this->table_structure->tableinfo->hyperlinks_child : [];

			$html       = '';
			$html_child = '';

			$settings_db = WPDA_Table_Settings_Model::query( $this->wpda_table_name, $this->wpda_schema_name );
			if ( isset( $settings_db[0]['wpda_table_settings'] ) && '' !== $settings_db[0]['wpda_table_settings'] ) {
				$settings = json_decode( $settings_db[0]['wpda_table_settings'] );
				if ( isset( $settings->hyperlinks ) && is_array( $settings->hyperlinks ) ) {
					foreach ( $settings->hyperlinks as $hyperlink ) {
						if ( isset( $hyperlink->hyperlink_label ) ) {
							$hyperlink_label = $hyperlink->hyperlink_label;

							if ( isset( $hyperlinks_parent->$hyperlink_label ) ) {
								$checked = $hyperlinks_parent->$hyperlink_label ? 'checked' : '';
							} else {
								$checked = 'checked';
							}
							$html .=
								"<label style='padding-right: 10px;'>
									<input type='checkbox' name='{$hyperlink_label}_hyperlink' style='width: 16px; height: 16px;' {$checked}/>
									{$hyperlink_label}
								</label>";

							if ( isset( $hyperlinks_child->$hyperlink_label ) ) {
								$checked = $hyperlinks_child->$hyperlink_label ? 'checked' : '';
							} else {
								$checked = 'checked';
							}
							$html_child .=
								"<label style='padding-right: 10px;'>
									<input type='checkbox' name='{$hyperlink_label}_hyperlink_child' style='width: 16px; height: 16px;' {$checked}/>
									{$hyperlink_label}
								</label>";
						}
					}
				}
			}

			if ( '' === $html && '' === $html_child ) {
				$hint = 'Hyperlinks';
			} else {
				$hint =
					__( 'Hyperlinks - uncheck to disable', 'wp-data-access' ) .
					' <span class="dashicons dashicons-editor-help wpda_tooltip" title="Use table settings to define where hyperlinks are shown."></span>';
			}

			if ( '' === $html ) {
				$html = '--';
			}

			if ( '' === $html_child ) {
				$html_child = '--';
			}
			?>
			<form id="wpdp_form_table_info" method="post">

				<fieldset class="wpda_fieldset wpdp_fieldset">
					<legend>
						<label style="font-weight: normal;">
							<?php echo __( 'Manage table settings for table ', 'wp-data-access' ); ?>
						</label>
						<label>
							<?php echo esc_attr( $wpda_table_name ); ?>
						</label>
					</legend>

					<table class="wpda-table-structure wpdp-table-structure">
						<tr>
							<td style="text-align: right; padding-right: 5px; width: 140px;" class="wpdp-label-colomn">
								<label>
									<?php echo __( 'Template set name', 'wp-data-access' ) ?>
								</label>
							</td>
							<td>
								<input type="text" name="wpda_table_setname" value="<?php echo esc_attr( $this->wpda_table_setname ); ?>"/>
								<input type="hidden" name="wpda_table_setname_old" value="<?php echo esc_attr( $this->wpda_table_setname ); ?>"/>
							</td>
							<td style="width:5px;"></td>
							<td style="width:1%;">
								<span title="A template set is used to connect a table to a Data Project." class="material-icons pointer wpda_tooltip">help</span>
							</td>
						</tr>
						<tr>
							<td style="text-align: right; padding-right: 5px;">
								<label>
									<?php echo __( 'Tab label', 'wp-data-access' ) ?>
								</label>
							</td>
							<td>
								<input type="text" name="tab_label" value="<?php echo esc_attr( $tab_label ); ?>"/>
							</td>
							<td style="width:5px;"></td>
							<td style="width:1%;">
								<span title="Label shown on tab when table is shown as child table." class="material-icons pointer wpda_tooltip">help</span>
							</td>
						</tr>
					</table>
				</fieldset>

				<div class="wpdp-spacer"></div>
				<div class="wpdp-spacer"></div>

				<fieldset class="wpda_fieldset wpdp_fieldset">
					<legend>
						<label style="font-weight: normal;">
							<?php echo $hint; ?>
						</label>
					</legend>

					<table class="wpda-table-structure wpdp-table-structure">
						<tr>
							<td style="text-align: right; padding-right: 5px; padding-top: 10px;" class="wpdp-label-colomn">
								<label>
									<?php echo __( 'Hyperlinks parent', 'wp-data-access' ) ?>
								</label>
							</td>
							<td style="padding-top: 10px;">
								<?php echo $html; ?>
							</td>
						</tr>
						<tr>
							<td style="text-align: right; padding-right: 5px;" class="wpdp-label-colomn">
								<label>
									<?php echo __( 'Hyperlinks child', 'wp-data-access' ) ?>
								</label>
							</td>
							<td>
								<?php echo $html_child; ?>
							</td>
						</tr>
					</table>
				</fieldset>

				<?php do_action( 'wpda_data_projects_add_table_option', $this->wpda_schema_name, $this->wpda_table_name ); ?>

				<div class="wpdp-spacer"></div>
				<div class="wpdp-spacer"></div>

				<fieldset class="wpda_fieldset wpdp_fieldset">
					<legend>
						<label style="font-weight: normal;">
							<?php echo __( 'Child table setting only', 'wp-data-access' ); ?>
							<span class="dashicons dashicons-editor-help wpda_tooltip" title="These settings only affect child tables! Parent table settings are available on Data Projects page."></span>
						</label>
					</legend>

					<table class="wpda-table-structure wpdp-table-structure">
						<tr>
							<td style="text-align: right; padding-right: 5px;" class="wpdp-label-colomn">
								<label>
									<?php echo __( 'Default WHERE', 'wp-data-access' ) ?>
								</label>
							</td>
							<td>
								<input type="text" name="default_where" value="<?php echo esc_attr( $defaul_where ); ?>"/>
							</td>
							<td style="width:5px;"></td>
							<td style="width:1%;">
								<span title="Enter a valid sql where clause, for example: name like 'Peter%'" class="material-icons pointer wpda_tooltip">help</span>
							</td>
						</tr>
						<tr>
							<td style="text-align: right; padding-right: 5px;" class="wpdp-label-colomn">
								<label>
									<?php echo __( 'Default ORDER BY', 'wp-data-access' ) ?>
								</label>
							</td>
							<td>
								<input type="text" name="default_orderby" value="<?php echo esc_attr( $defaul_orderby ); ?>"/>
							</td>
							<td style="width:5px;"></td>
							<td style="width:1%;">
								<span title="Enter a valid sql order by clause, for example: course_date desc, student_name asc" class="material-icons pointer wpda_tooltip">help</span>
							</td>
						</tr>
					</table>
				</fieldset>

				<div class="wpdp-button-panel">
					<input type="hidden" name="wpda_schema_name"
						   value="<?php echo esc_attr( $this->wpda_schema_name_db ); ?>"/>
					<input type="hidden" name="wpda_table_name"
						   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
					<input type="hidden" name="action" value="edit"/>
					<input type="hidden" name="action2" value="tableinfo"/>
					<input type="hidden" name="wpnonce"
						   value="<?php echo esc_attr( $this->wpnonce ); ?>"/>
					<button type="submit" class="button button-primary">
						<span class="material-icons wpda_icon_on_button">check</span>
						<?php echo __( 'Save table info', 'wp-data-access' ); ?>
					</button>
				</div>

			</form>
			<?php
		}

		/**
		 * Show relationships
		 */
		protected function show_relations() {
			$source_table_columns = $this->wpda_list_columns->get_table_columns();
			$target_table_names   = WPDA_Dictionary_Lists::get_tables( true, $this->wpda_schema_name );
			$available_databases  = WPDA_Dictionary_Lists::get_db_schemas();
			$i                    = 0;

			global $wpdb;
			if ( '' === $this->wpda_schema_name || $wpdb->dbname === $this->wpda_schema_name ) {
				$wpda_table_name = $this->wpda_table_name;
			} else {
				$wpda_table_name = $this->wpda_table_name . ' (' . $this->wpda_schema_name . ')';
			}
			?>
			<script type='text/javascript'>
				var row_num = 0;
				var col_num = [];

				function wpdp_get_tables(schema_name, index, target_id, selected_value, target_column_name = false, source_column_name= false) {
					var url = location.pathname + '?action=wpda_get_tables';
					var data = { wpdaschema_name: schema_name };
					jQuery.post(
						url,
						data,
						function (data) {
							jQuery(target_id).find('option').remove();
							var jsonData = JSON.parse(data);
							for (i = 0; i < jsonData.length; i++) {
								jQuery(target_id).append(
									newitem = jQuery("<option></option>")
									.attr("value", jsonData[i]['table_name'])
									.text(jsonData[i]['table_name'])
								);
								if (jsonData[i]['table_name'] === selected_value) {
									newitem.attr("selected", true);
								}
							}
							if (!target_column_name) {
								jQuery(target_id).trigger("change");
							} else {
								wpdp_get_columns(selected_value, index, '#target_column_name_' + index, target_column_name[0], schema_name);
								for (i=1; i<target_column_name.length; i++) {
									add_column(index, target_column_name[i]);
									jQuery('#source_column_name_' + index + '_' + i).val(source_column_name[i]);
									wpdp_get_columns(selected_value, index, '#target_column_name_' + index + '_' + i, target_column_name[i], schema_name);
								}
							}
						}
					);
				}

				function wpdp_get_columns(table_name, index, target_id, selected_value, schema_name = '') {
					if (schema_name === '') {
						schema_name = '<?php echo $this->wpda_schema_name; ?>';
					}
					var url = location.pathname + '?action=wpda_get_columns';
					var data = { wpdaschema_name: schema_name, table_name: table_name };
					jQuery.post(
						url,
						data,
						function (data) {
							jQuery(target_id).find('option').remove();
							var jsonData = JSON.parse(data);
							for (i = 0; i < jsonData.length; i++) {
								jQuery(target_id).append(
									newitem = jQuery("<option></option>")
									.attr("value", jsonData[i]['column_name'])
									.text(jsonData[i]['column_name'])
								);
								if (jsonData[i]['column_name'] === selected_value) {
									newitem.attr("selected", true);
								}
							}
						}
					);
				}

				function add_row(relation_type, source_column_name, target_table_name, target_column_name, relation_table_name, target_schema_name = '') {
					if (relation_type === undefined || relation_type === '') {
						relation_type = '1n';
					}
					if (source_column_name === undefined || source_column_name === '') {
						source_column_name = [''];
					}
					if (relation_table_name === undefined || relation_table_name === '') {
						relation_table_name = '';
					}
					if (target_schema_name === '' || target_schema_name === '<?php echo $this->wpda_schema_name; ?>') {
						target_schema_name = '<?php echo $this->wpda_schema_name; ?>';
						populate_lookup = false;
					} else {
						populate_lookup = true;
					}
					var new_row = '<tr id="relation_' + row_num + '">' +
						'<td class="wpda-table-structure-first-column-move wpda-table-structure-top">' +
						'	<span class="dashicons dashicons-move grabbable" style="padding-top:5px;"></span>' +
						'    <input type="hidden" name="row_num[]" value="' + row_num + '" />' +
						'</td>' +
						'<td class="wpda-table-structure-top">' +
						'    <select id="relation_type_' + row_num + '" name="relation_type[]" onchange="change_relationship(event, ' + row_num + ')">' +
						'        <option value="1n" ' + (relation_type === '1n' ? 'selected' : '') + '>1:n</option>' +
						'        <option value="nm" ' + (relation_type === 'nm' ? 'selected' : '') + '>n:m</option>' +
						'        <option value="lookup" ' + (relation_type === 'lookup' ? 'selected' : '') + '>lookup listbox</option>' +
						'        <option value="autocomplete" ' + (relation_type === 'autocomplete' ? 'selected' : '') + '>lookup autocomplete</option>' +
						'    </select>' +
						'</td>' +
						'<td class="wpda-table-structure-top">' +
						'    <select name="source_column_name[]" id="source_column_name_' + row_num + '">' +
						<?php
						foreach ( $source_table_columns as $column ) {
						?>
						'<option value="<?php echo esc_attr( $column['column_name'] ); ?>" ' + (source_column_name[0] === "<?php echo esc_attr( $column['column_name'] ); ?>" ? "selected" : "") + '><?php echo $column['column_name']; ?></option>' +
						<?php
						}
						?>
						'    </select>' +
						'    <input type="hidden" name="num_source_column_name[]" id="num_source_column_name_' + row_num + '" value="0" />' +
						'</td>' +
						'<td class="wpda-table-structure-top">' +
						'    <a href="javascript:void(0)" style="vertical-align:-webkit-baseline-middle;padding-top:7px;"' +
						'       class="dashicons dashicons-plus wpda_tooltip" title="Add column"' +
						'       onclick="add_column(' + row_num + ')" id="remove_column_names_' + row_num + '"' +
						'    ></a>' +
						'</td>' +
						'<td class="wpda-table-structure-top">' +
						'    <select id="target_schema_name_' + row_num + '" name="target_schema_name[]" onchange="wpdp_get_tables(jQuery(this).val(), ' + row_num + ', \'#target_table_name_' + row_num + '\', \'\')" class="wpda_tooltip" title="Create lookup to other database"' + (relation_type==="lookup"||relation_type==="autocomplete" ? "" : "style=\'display:none;\'") + '>' +
						<?php
						foreach ( $available_databases as $available_database ) {
						?>
						'<option value="<?php echo esc_attr( $available_database['schema_name'] ); ?>" ' + (target_schema_name === "<?php echo esc_attr( $available_database['schema_name'] ); ?>" ? "selected" : "") + '><?php echo esc_attr( $available_database['schema_name'] ); ?></option>' +
						<?php
						}
						?>
						'    </select>' +
						'    <select id="target_table_name_' + row_num + '" name="target_table_name[]" onchange="wpdp_get_columns(jQuery(this).val(), ' + row_num + ', \'#target_column_name_' + row_num + '\', \'\', jQuery(\'#target_schema_name_' + row_num + '\').val())">' +
						'        <option value=""></option>' +
						<?php
						foreach ( $target_table_names as $target_table_name ) {
						?>
						'<option value="<?php echo esc_attr( $target_table_name['table_name'] ); ?>" ' + (target_table_name === "<?php echo esc_attr( $target_table_name['table_name'] ); ?>" ? "selected" : "") + '><?php echo esc_attr( $target_table_name['table_name'] ); ?></option>' +
						<?php
						}
						?>
						'    </select>' +
						'</td>' +
						'<td class="wpda-table-structure-top">' +
						'    <select name="target_column_name[]" id="target_column_name_' + row_num + '"></select>' +
						'</td>' +
						'<td class="wpda-table-structure-top">' +
						'    <select name="relation_table_name_' + row_num + '" id="relation_table_name_' + row_num + '"></select>' +
						'</td>' +
						'<td class="wpda-table-structure-last-column wpda-table-structure-top">' +
						'	<a href="javascript:void(0)" class="dashicons dashicons-trash" onclick="rem_row(event)" style="float:right;padding-top:5px;"></a>' +
						'</td>' +
						'</tr>';
					if (jQuery("#wpda_table_structure tr").length === 0) {
						jQuery("#wpda_table_structure").append(new_row);
					} else {
						jQuery("#wpda_table_structure tr:last").after(new_row);
					}
					col_num[row_num] = 0;
					if (jQuery('#target_table_name_' + row_num + ' option:selected').val() !== '') {
						wpdp_get_columns(jQuery('#target_table_name_' + row_num).val(), row_num, '#target_column_name_' + row_num, target_column_name[0]);
						for (i = 1; i < target_column_name.length; i++) {
							add_column(row_num, target_column_name[i]);
							jQuery('#source_column_name_' + row_num + '_' + i).val(source_column_name[i]);
						}
					}
					jQuery('#relation_table_name_' + row_num).append("<option value=''></option>");
					<?php
					foreach ( $target_table_names as $target_table_name ) {
					$option =
						"<option value='" . esc_attr( $target_table_name['table_name'] ) . "'>" .
						esc_attr( $target_table_name['table_name'] ) .
						"</option>";
					?>
					jQuery('#relation_table_name_' + row_num).append("<?php echo $option; ?>");
					jQuery('#relation_table_name_' + row_num).val(relation_table_name);
					<?php
					}
					?>
					if (relation_type === '1n' || relation_type === 'lookup' || relation_type === 'autocomplete') {
						jQuery('#relation_table_name_' + row_num).hide();
					}
					if (populate_lookup) {
						// If lookup table is stored in another schema, we need to populate the listboxes with ajax calls
						wpdp_get_tables(jQuery('#target_schema_name_' + row_num).val(), row_num, '#target_table_name_' + row_num, target_table_name, target_column_name, source_column_name);
					}
					row_num++;
				}

				function change_relationship(e, index) {
					if (jQuery(e.target).val() === 'nm') {
						jQuery('#relation_table_name_' + index).show();
					} else {
						jQuery('#relation_table_name_' + index).hide();
					}

					if (jQuery(e.target).val() === 'lookup' || jQuery(e.target).val() === 'autocomplete') {
						jQuery('#target_schema_name_' + index).show();
					} else {
						jQuery('#target_schema_name_' + index).hide();
					}
				}

				function rem_row(e) {
					var curr_id = e.target.parentNode.parentNode.id;
					if (confirm("Delete relationship?")) {
						jQuery("#" + curr_id).remove();
					}
				}

				function add_column(index, selected_value = '') {
					var source_column_name_list =
						'<select name="source_column_name_' + index + '_' + (col_num[index] + 1) + '" id="source_column_name_' + index + '_' + (col_num[index] + 1) + '">' +
						<?php foreach ( $source_table_columns as $column )
						{
						?>
						'<option value="<?php echo esc_attr( $column['column_name'] ); ?>"><?php echo $column['column_name']; ?></option>' +
						<?php
						}
						?>
						'</select>';
					jQuery(source_column_name_list).insertAfter(jQuery('#source_column_name_' + index).parent().children().last());

					var remove_column_names_list =
						'<div id="remove_column_names_' + index + '_' + (col_num[index] + 1) + '" style="padding-top:4px;">' +
						'    <a href="javascript:void(0)"' +
						'       style="vertical-align:-webkit-baseline-middle;padding-top:5px;"' +
						'       class="dashicons dashicons-minus wpda_tooltip"' +
						'       title="Remove column"' +
						'       onclick="rem_column(' + index + ',' + (col_num[index] + 1) + ')"' +
						'    ></a>' +
						'</div>';
					jQuery(remove_column_names_list).insertAfter(jQuery('#remove_column_names_' + index).parent().children().last());

					var target_column_name_list =
						'<select name="target_column_name_' + index + '_' + (col_num[index] + 1) + '" id="target_column_name_' + index + '_' + (col_num[index] + 1) + '">' +
						'</select>';
					jQuery(target_column_name_list).insertAfter(jQuery('#target_column_name_' + index).parent().children().last());
					if (
						jQuery('#relation_type_' + index).val()==='lookup' ||
						jQuery('#relation_type_' + index).val()==='autocomplete'
					) {
						// Is lookup accessing a remote database?
						wpdp_get_columns(
							jQuery('#target_table_name_' + index).val(),
							index,
							'#target_column_name_' + index + '_' + eval(col_num[index] + 1),
							selected_value,
							jQuery('#target_schema_name_' + index).val()
						);
					} else {
						wpdp_get_columns(
							jQuery('#target_table_name_' + index).val(),
							index,
							'#target_column_name_' + index + '_' + eval(col_num[index] + 1),
							selected_value
						);
					}

					col_num[index] += 1;
					jQuery('#num_source_column_name_' + index).val(col_num[index]);
				}

				function rem_column(index, seq) {
					jQuery('#source_column_name_' + index + '_' + seq).remove();
					jQuery('#remove_column_names_' + index + '_' + seq).remove();
					jQuery('#target_column_name_' + index + '_' + seq).remove();
				}
			</script>
			<form id="wpdp_form_relations" method="post">
				<fieldset class="wpda_fieldset wpdp_fieldset">
					<legend>
						<label style="font-weight: normal;">
							<?php echo __( 'Manage relationships for table', 'wp-data-access' ); ?>
						</label>
						<label>
							<?php echo esc_attr( $wpda_table_name ); ?>
						</label>
					</legend>

					<table class="wpda-table-structure">
						<thead>
						<tr>
							<th class="wpda-table-structure-first-column-move"></th>
							<th>
								<?php echo __( 'Type', 'wp-data-access' ) ?>
							</th>
							<th>
								<?php echo __( 'Source column name', 'wp-data-access' ) ?>
							</th>
							<th style="width:20px;"></th>
							<th>
								<?php echo __( 'Target table name', 'wp-data-access' ) ?>
							</th>
							<th>
								<?php echo __( 'Target column name', 'wp-data-access' ) ?>
							</th>
							<th>
								<span style="vertical-align:inherit">
									<?php echo __( 'Relation table name (only n:m)', 'wp-data-access' ) ?>
								</span>
								<span
										class="dashicons dashicons-info wpda_tooltip"
										title="<?php echo __( 'Table shown on the other end of the n:m relationship (instead of target table shown for 1:n relationships). Not available for 1:n relationships.', 'wp-data-access' ) ?>"
										style="cursor:pointer;"
								></span>
							</th>
							<th class="wpda-table-structure-last-column">
								<a href="javascript:void(0)"
								   style="vertical-align:-webkit-baseline-middle;float:right;"
								   class="dashicons dashicons-plus add-row"
								   onclick="add_row()"
								></a>
							</th>
						</tr>
						</thead>
						<tbody id="wpda_table_structure">
						</tbody>
					</table>
				</fieldset>

				<div class="wpdp-button-panel">
					<input type="hidden" name="wpda_schema_name"
						   value="<?php echo esc_attr( $this->wpda_schema_name_db ); ?>"/>
					<input type="hidden" name="wpda_table_name"
						   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
					<input type="hidden" name="wpda_table_setname"
						   value="<?php echo esc_attr( $this->wpda_table_setname ); ?>"/>
					<input type="hidden" name="wpda_table_setname_old"
						   value="<?php echo esc_attr( $this->wpda_table_setname ); ?>"/>
					<input type="hidden" name="action" value="edit"/>
					<input type="hidden" name="action2" value="relation"/>
					<input type="hidden" name="wpnonce"
						   value="<?php echo esc_attr( $this->wpnonce ); ?>"/>
					<button type="submit" class="button button-primary">
						<span class="material-icons wpda_icon_on_button">check</span>
						<?php echo __( 'Save relationships', 'wp-data-access' ); ?>
					</button>
				</div>

			</form>
			<?php
			if ( isset( $this->table_structure->relationships ) ) {
				$relationships = $this->table_structure->relationships;
				if ( 0 < count( $relationships ) ) {
					foreach ( $relationships as $relationship ) {
						?>
						<script type='text/javascript'>
							<?php
							if ( ! is_array( $relationship->source_column_name ) ) {
								$source_column_name_array = '[""]';
							} else {
								$source_column_name_array = wp_json_encode( $relationship->source_column_name );
							}
							echo "var source_column_name_array = " . $source_column_name_array . ";\n";

							if ( ! is_array( $relationship->target_column_name ) ) {
								$target_column_name_array = '[""]';
							} else {
								$target_column_name_array = wp_json_encode( $relationship->target_column_name );
							}
							echo "var target_column_name_array = " . $target_column_name_array . ";\n";
							if ( isset( $relationship->relation_table_name ) ) {
								$relation_table_name = $relationship->relation_table_name;
							} else {
								$relation_table_name = '';
							}
							if ( isset( $relationship->target_schema_name ) ) {
								$target_schema_name = $relationship->target_schema_name;
							} else {
								$target_schema_name = '';
							}
							?>
							add_row(
								'<?php echo esc_attr( $relationship->relation_type ); ?>',
								source_column_name_array,
								'<?php echo esc_attr( $relationship->target_table_name ); ?>',
								target_column_name_array,
								'<?php echo esc_attr( $relation_table_name ); ?>',
								'<?php echo esc_attr( $target_schema_name ); ?>'
							);
						</script>
						<?php
					}
				} else {
					?>
					<script type='text/javascript'>
						add_row('', '', '', '');
					</script>
					<?php
				}
			} else {
				?>
				<script type='text/javascript'>
					add_row('', '', '', '');
				</script>
				<?php
			}
		}

		/**
		 * Show list table settings
		 */
		protected function show_list_table() {
			if ( null === $this->table_structure ) {
				return __( 'Invalid structure', 'wp-data-access' );
			}

			global $wpdb;
			if ( '' === $this->wpda_schema_name || $wpdb->dbname === $this->wpda_schema_name ) {
				$wpda_table_name = $this->wpda_table_name;
			} else {
				$wpda_table_name = $this->wpda_table_name . ' (' . $this->wpda_schema_name . ')';
			}
			?>
			<form id="wpdp_form_labels" method="post">
				<fieldset class="wpda_fieldset wpdp_fieldset">
					<legend>
						<label style="font-weight: normal;">
							<?php echo __( 'Manage columns for list table of table', 'wp-data-access' ); ?>
						</label>
						<label>
							<?php echo esc_attr( $wpda_table_name ); ?>
						</label>
					</legend>

					<table class="wpda-table-structure">
						<thead>
						<tr>
							<th class="wpda-table-structure-first-column-move"></th>
							<th>
								<?php echo __( 'Column name', 'wp-data-access' ) ?>
							</th>
							<th>
								<?php echo __( 'Data type', 'wp-data-access' ) ?>
							</th>
							<th>
								<?php echo __( 'Key?', 'wp-data-access' ) ?>
							</th>
							<th>
								<?php echo __( 'Mandatory?', 'wp-data-access' ) ?>
							</th>
							<th></th>
							<th>
								<?php echo __( 'List label (uncheck to hide column)', 'wp-data-access' ) ?>
							</th>
							<th></th>
							<th>
								<?php echo __( 'Lookup', 'wp-data-access' ) ?>
							</th>
						</tr>
						</thead>
						<tbody id="wpda_list_table_options">
						<?php
						$table_structure = [];
						if ( isset( $this->table_structure->listtable_column_options ) ) {
							$structure = $this->table_structure->listtable_column_options;
							foreach ( $this->table_structure->table as $column ) {
								$table_structure[ $column->column_name ] = $column;
							}
						} else {
							$structure = $this->table_structure->table;
						}
						$i = 0;
						foreach ( $structure as $column ) {
							$column_name = $column->column_name;
							if ( isset( $this->table_structure->listtable_column_options ) && isset ( $table_structure[ $column_name ] ) ) {
								$data_type      = $table_structure[ $column_name ]->data_type;
								$type_attribute = $table_structure[ $column_name ]->type_attribute;
								$key            = $table_structure[ $column_name ]->key;
								$mandatory      = $table_structure[ $column_name ]->mandatory;
								$max_length     = $table_structure[ $column_name ]->max_length;
							} else {
								$msg = new WPDA_Message_Box(
									[
										'message_text'           => __( "Column $column_name not found for list table", 'wp-data-access' ),
										'message_type'           => 'error',
										'message_is_dismissible' => false,
									]
								);
								$msg->box();
								break;
							}
							if ( '' === $max_length ) {
								$data_type = $data_type . ' ' . $type_attribute;
							} else {
								$data_type = $data_type . ' (' . $max_length . ') ' . $type_attribute;
							}
							if ( isset( $this->table_structure->listtable_column_options ) ) {
								$show_in_list      = 'on' === $column->show ? 'checked' : '';
								$label_in_list     = $column->label;
								$lookup_in_list    = isset( $column->lookup ) ? $column->lookup : '';
							} else {
								$show_in_list      = 'checked';
								$label_in_list     = ucfirst( str_replace( '_', ' ', $column_name ) );
								$lookup_in_list    = '';
							}
							$i ++;
							?>
							<tr id="listtable_<?php echo esc_attr( $i ); ?>">
								<td class="wpda-table-structure-first-column-move">
									<span class="dashicons dashicons-move grabbable" style="float:left;"></span>
								</td>
								<td>
									<?php echo esc_attr( $column_name ); ?>
									<input type="hidden" name="list_item_name[]"
										   value="<?php echo esc_attr( $column_name ); ?>"/>
								</td>
								<td>
									<?php echo esc_attr( $data_type ); ?>
								</td>
								<td>
									<?php echo esc_attr( $key ); ?>
								</td>
								<td>
									<?php echo esc_attr( $mandatory ); ?>
								</td>
								<td style="text-align:right;width:16px;">
									<input type="checkbox"
										   name="<?php echo esc_attr( $column_name ); ?>_show"
										<?php echo esc_attr( $show_in_list ); ?>
										   style="vertical-align:middle;width:16px;height:16px;"
									/>
								</td>
								<td>
									<input type="text"
										   name="<?php echo esc_attr( $column_name ); ?>"
										   value="<?php echo esc_attr( $label_in_list ); ?>"
										   style="vertical-align:middle;"
									/>
								</td>
								<td></td>
								<td class="wpda-table-structure-last-column">
									<?php
									$has_lookup = false;
									if ( isset( $this->table_structure->relationships ) ) {
										foreach ( $this->table_structure->relationships as $relationship ) {
											if (
												$column_name === $relationship->source_column_name[0] &&
												(
													'lookup' === $relationship->relation_type ||
													'autocomplete' === $relationship->relation_type
												)
											) {
												if ( isset( $relationship->target_schema_name ) ) {
													$target_schema_name = $relationship->target_schema_name;
												} else {
													$target_schema_name = $this->wpda_schema_name;
												}
												$lookup_column_list =
													WPDA_Dictionary_Lists::get_table_columns(
														$relationship->target_table_name,
														$target_schema_name
													);
												?>
												<select name="<?php echo esc_attr( $column_name ); ?>_lookup">
													<?php
													foreach ( $lookup_column_list as $lookup_column ) {
														?>
														<option value="<?php echo esc_attr( $lookup_column['column_name'] ); ?>"
															<?php if ( $lookup_in_list === $lookup_column['column_name'] ) {
																echo 'selected';
															} ?>
														>
															<?php echo $lookup_column['column_name']; ?>
														</option>
														<?php
													}
													?>
												</select>
												<?php
												$has_lookup = true;
											}
										}
									}
									if ( ! $has_lookup ) {
										echo '--';
									}
									?>
								</td>
							</tr>
							<?php
						}
						?>
						</tbody>
					</table>
				</fieldset>

				<div class="wpdp-button-panel">
					<input type="hidden" name="wpda_schema_name"
						   value="<?php echo esc_attr( $this->wpda_schema_name_db ); ?>"/>
					<input type="hidden" name="wpda_table_name"
						   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
					<input type="hidden" name="wpda_table_setname"
						   value="<?php echo esc_attr( $this->wpda_table_setname ); ?>"/>
					<input type="hidden" name="wpda_table_setname_old"
						   value="<?php echo esc_attr( $this->wpda_table_setname ); ?>"/>
					<input type="hidden" name="action" value="edit"/>
					<input type="hidden" name="action2" value="listtable"/>
					<input type="hidden" name="wpnonce"
						   value="<?php echo esc_attr( $this->wpnonce ); ?>"/>
					<button type="submit" class="button button-primary">
						<span class="material-icons wpda_icon_on_button">check</span>
						<?php echo __( 'Save list table columns', 'wp-data-access' ); ?>
					</button>
				</div>

			</form>
			<?php
		}

		/**
		 * Show data entry form settings
		 */
		protected function show_table_form() {
			if ( null === $this->table_structure ) {
				return __( 'Invalid structure', 'wp-data-access' );
			}

			global $wpdb;
			if ( '' === $this->wpda_schema_name || $wpdb->dbname === $this->wpda_schema_name ) {
				$wpda_table_name = $this->wpda_table_name;
			} else {
				$wpda_table_name = $this->wpda_table_name . ' (' . $this->wpda_schema_name . ')';
			}
			?>
			<form id="wpdp_form_labels" method="post">
				<fieldset class="wpda_fieldset wpdp_fieldset">
					<legend>
						<label style="font-weight: normal;">
							<?php echo __( 'Manage columns for data entry form for table', 'wp-data-access' ); ?>
						</label>
						<label>
							<?php echo esc_attr( $wpda_table_name ); ?>
						</label>
					</legend>

					<table class="wpda-table-structure">
						<thead>
						<tr>
							<th class="wpda-table-structure-first-column-move"></th>
							<th>
								<?php echo __( 'Column name', 'wp-data-access' ) ?>
							</th>
							<th>
								<?php echo __( 'Data type', 'wp-data-access' ) ?>
							</th>
							<th>
								<?php echo __( 'Key?', 'wp-data-access' ) ?>
							</th>
							<th>
								<?php echo __( 'Mandatory?', 'wp-data-access' ) ?>
							</th>
							<th>
								<?php echo __( 'Less?', 'wp-data-access' ) ?>
							</th>
							<th></th>
							<th>
								<?php echo __( 'Form label (uncheck to hide column)', 'wp-data-access' ) ?>
							</th>
							<th></th>
							<th>
								<?php echo __( 'Default value', 'wp-data-access' ) ?>
							</th>
							<th></th>
							<th>
								<?php echo __( 'Lookup', 'wp-data-access' ) ?>
							</th>
							<th>
								<span style="vertical-align:inherit">
									<?php echo __( 'ID?', 'wp-data-access' ) ?>
								</span>
								<span
										class="dashicons dashicons-info wpda_tooltip"
										title="<?php echo __( 'Enable to hide ID in lookup', 'wp-data-access' ) ?>"
										style="cursor:pointer;"
								></span>

							</th>
						</tr>
						</thead>
						<tbody id="wpda_form_options">
						<?php
						$table_structure = [];
						if ( isset( $this->table_structure->tableform_column_options ) ) {
							$structure = $this->table_structure->tableform_column_options;
							foreach ( $this->table_structure->table as $column ) {
								$table_structure[ $column->column_name ] = $column;
							}
						} else {
							$structure = $this->table_structure->table;
						}
						$i = 0;
						foreach ( $structure as $column ) {
							$column_name = $column->column_name;
							if ( isset( $this->table_structure->tableform_column_options ) && isset ( $table_structure[ $column_name ] ) ) {
								$data_type      = $table_structure[ $column_name ]->data_type;
								$db_data_type   = $data_type;
								$type_attribute = $table_structure[ $column_name ]->type_attribute;
								$key            = $table_structure[ $column_name ]->key;
								$mandatory      = $table_structure[ $column_name ]->mandatory;
								$max_length     = $table_structure[ $column_name ]->max_length;
							} else {
								$msg = new WPDA_Message_Box(
									[
										'message_text'           => __( "Column $column_name not found for data entry form", 'wp-data-access' ),
										'message_type'           => 'error',
										'message_is_dismissible' => false,
									]
								);
								$msg->box();
								break;
							}
							if ( '' === $max_length ) {
								$data_type = $data_type . ' ' . $type_attribute;
							} else {
								$data_type = $data_type . ' (' . $max_length . ') ' . $type_attribute;
							}
							if ( isset( $this->table_structure->tableform_column_options ) ) {
								$show_on_form   = 'on' === $column->show ? 'checked' : '';
								$label_on_form  = $column->label;
								$lookup_in_list = isset( $column->lookup ) ? $column->lookup : '';
							} else {
								$show_on_form      = 'checked';
								$label_on_form     = ucfirst( str_replace( '_', ' ', $column_name ) );
								$lookup_in_list    = '';
							}
							if ( isset( $column->less ) ) {
								$less_on_form = 'on' === $column->less ? 'checked' : '';
							} else {
								$less_on_form = 'checked';
							}
							if ( isset( $column->default ) ) {
								$default_on_form = esc_html( $column->default );
							} else {
								$default_on_form = '';
							}
							if ( isset( $column->hide_lookup_key ) ) {
								$hide_id = 'on' === $column->hide_lookup_key ? 'checked' : '';;
							} else {
								$hide_id = 'checked';
							}
							$i ++;
							?>
							<tr id="tableform_<?php echo esc_attr( $i ); ?>">
								<td class="wpda-table-structure-first-column-move">
									<span class="dashicons dashicons-move grabbable" style="float:left;"></span>
								</td>
								<td>
									<?php echo esc_attr( $column_name ); ?>
									<input type="hidden" name="list_item_name[]"
										   value="<?php echo esc_attr( $column_name ); ?>"/>
								</td>
								<td>
									<?php echo esc_attr( $data_type ); ?>
								</td>
								<td>
									<?php echo esc_attr( $key ); ?>
								</td>
								<td>
									<?php echo esc_attr( $mandatory ); ?>
								</td>
								<td>
									<input type="checkbox" name="<?php echo esc_attr( $column_name ); ?>_less"
										   style="vertical-align:middle;width:16px;height:16px;"
										<?php echo $less_on_form; ?>
									/>
								</td>
								<td style="text-align:right;width:16px;">
									<?php
									if ( $this->wpda_list_columns->get_auto_increment_column_name() === $column_name ) {
										// Allow to hide auto_increment column
										$key       = 'No';
										$mandatory = 'No';
									}
									?>
									<input type="checkbox"
										   name="<?php echo esc_attr( $column_name ); ?>_show"
										<?php echo esc_attr( $show_on_form ); ?>
										<?php if ( 'Yes' === $key || 'Yes' === $mandatory ) {
											echo ' disabled="disabled"';
										} ?>
										   style="vertical-align:middle;width:16px;height:16px;"
									/>
									<?php if ( 'Yes' === $key || 'Yes' === $mandatory ) { ?>
										<input name="<?php echo esc_attr( $column_name ); ?>_show"
											   type="hidden"
											   value="true"/>
									<?php } ?>
								</td>
								<td>
									<input type="text"
										   name="<?php echo esc_attr( $column_name ); ?>"
										   value="<?php echo esc_attr( $label_on_form ); ?>"
										   style="vertical-align:middle;"
									/>
								</td>
								<td></td>
								<td>
									<input type="text"
										   name="<?php echo esc_attr( $column_name ); ?>_default"
										   value="<?php echo esc_attr( $default_on_form ); ?>"
										   style="vertical-align:middle;"
									/>
								</td>
								<td></td>
								<td>
									<?php
									$has_lookup = false;
									if ( isset( $this->table_structure->relationships ) ) {
										foreach ( $this->table_structure->relationships as $relationship ) {
											if (
												$column_name === $relationship->source_column_name[0] &&
												(
													'lookup' === $relationship->relation_type ||
													'autocomplete' === $relationship->relation_type
												)
											) {
												if ( isset( $relationship->target_schema_name ) ) {
													$target_schema_name = $relationship->target_schema_name;
												} else {
													$target_schema_name = $this->wpda_schema_name;
												}
												$lookup_column_list =
													WPDA_Dictionary_Lists::get_table_columns(
														$relationship->target_table_name,
														$target_schema_name
													);
												?>
												<select name="<?php echo esc_attr( $column_name ); ?>_lookup">
													<?php
													foreach ( $lookup_column_list as $lookup_column ) {
														?>
														<option value="<?php echo esc_attr( $lookup_column['column_name'] ); ?>"
															<?php if ( $lookup_in_list === $lookup_column['column_name'] ) {
																echo 'selected';
															} ?>
														>
															<?php echo $lookup_column['column_name']; ?>
														</option>
														<?php
													}
													?>
												</select>
												<?php
												$has_lookup = true;
											}
										}
									}
									if ( ! $has_lookup ) {
										echo '--';
									}
									?>
								</td>
								<td class="wpda-table-structure-last-column">
									<?php if ( $has_lookup ) { ?>
										<input type="checkbox"
											   name="<?php echo esc_attr( $column_name ); ?>_hide_lookup_key"
											   <?php echo $hide_id; ?>
											   style="vertical-align:middle;width:16px;height:16px;"
										/>
									<?php } ?>
								</td>
							</tr>
							<?php
						}
						?>
						</tbody>
					</table>
				</fieldset>

				<div class="wpdp-button-panel">
					<input type="hidden" name="wpda_schema_name"
						   value="<?php echo esc_attr( $this->wpda_schema_name_db ); ?>"/>
					<input type="hidden" name="wpda_table_name"
						   value="<?php echo esc_attr( $this->wpda_table_name ); ?>"/>
					<input type="hidden" name="wpda_table_setname"
						   value="<?php echo esc_attr( $this->wpda_table_setname ); ?>"/>
					<input type="hidden" name="wpda_table_setname_old"
						   value="<?php echo esc_attr( $this->wpda_table_setname ); ?>"/>
					<input type="hidden" name="action" value="edit"/>
					<input type="hidden" name="action2" value="tableform"/>
					<input type="hidden" name="wpnonce"
						   value="<?php echo esc_attr( $this->wpnonce ); ?>"/>
					<button type="submit" class="button button-primary">
						<span class="material-icons wpda_icon_on_button">check</span>
						<?php echo __( 'Save data entry form columns', 'wp-data-access' ); ?>
					</button>
				</div>

			</form>
			<?php
		}

		protected function show_reconcile() {
			?>
			<div class="wrap" style="padding-left:8px;">
				<form method="post">
					<fieldset class="wpda_fieldset wpdp_fieldset">
						<legend>
							<label style="font-weight: normal;">
								<?php echo __( 'Reconcile columns for table', 'wp-data-access' ); ?>
							</label>
							<label>
								<?php echo esc_attr( $this->wpda_table_name ); ?>
							</label>
						</legend>

						<div>
							<strong>Update table design from database table</strong>
							<ul class="wpdp-reconcile">
								<li>Add new columns</li>
								<li>Remove deleted columns</li>
								<li>Change column settings</li>
								<li>Update settings</li>
							</ul>
							<label style="line-height:30px;padding-bottom:8px">
								<input type="checkbox" name="keep_options" style="vertical-align:middle;">
								<strong>Keep options?</strong> (enable to keep settings of unchanges columns)
							</label>
						</div>
					</fieldset>

					<div class="wpdp-spacer"></div>
					<div class="wpdp-spacer"></div>

					<div class="wpdp-button-panel">
						<input type="hidden" name="action" value="reconcile"/>
						<input type="hidden" name="wpda_schema_name"
							   value="<?php echo esc_attr( $this->wpda_schema_name_db ); ?>">
						<input type="hidden" name="wpda_table_name"
							   value="<?php echo esc_attr( $this->wpda_table_name ); ?>">
						<input type="hidden" name="wpda_table_setname"
							   value="<?php echo esc_attr( $this->wpda_table_setname ); ?>"/>
						<input type="hidden" name="wpnonce"
							   value="<?php echo esc_attr( $this->wpnonce ); ?>"/>
						<button type="submit" class="button button-primary wpda_tooltip"
								onclick="return (confirm('<?php echo __( 'Reconcile table? Your current modifications will be lost!' ); ?>'));"
						>
							<span class="material-icons wpda_icon_on_button">check</span>
							<?php echo __( 'Reconcile Table', 'wp-data-access' ); ?>
						</button>
					</div>
				</form>
			</div>
			<?php
		}

	}

}
