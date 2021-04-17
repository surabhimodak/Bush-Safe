<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package plugin\admin
 */
use  WPDataAccess\Backup\WPDA_Data_Export ;
use  WPDataAccess\Plugin_Table_Models\WPDA_CSV_Uploads_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDA_Design_Table_Model ;
use  WPDataAccess\List_Table\WPDA_List_View ;
use  WPDataAccess\Settings\WPDA_Settings ;
use  WPDataAccess\Plugin_Table_Models\WPDA_User_Menus_Model ;
use  WPDataAccess\Utilities\WPDA_Repository ;
use  WPDataAccess\WPDA ;
use  WPDataAccess\Plugin_Table_Models\WPDA_Publisher_Model ;
use  WPDataAccess\List_Table\WPDA_List_Table ;
use  WPDataAccess\CSV_Files\WPDA_CSV_Import ;
/**
 * Class WP_Data_Access_Admin
 *
 * Defines admin specific functionality for plugin WP Data Access.
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */
class WP_Data_Access_Admin
{
    /**
     * Menu slug for main page
     */
    const  PAGE_MAIN = 'wpda' ;
    /**
     * Menu slug for setting page
     */
    const  PAGE_SETTINGS = 'wpdataaccess' ;
    /**
     * Menu slug for explorer page
     */
    const  PAGE_EXPLORER = 'wpda_explorer' ;
    /**
     * Menu slug for main page
     */
    const  PAGE_PUBLISHER = 'wpda_publisher' ;
    /**
     * Menu slug for designer page
     */
    const  PAGE_DESIGNER = 'wpda_designer' ;
    /**
     * Menu slug for my tables page
     */
    const  PAGE_MY_TABLES = 'wpda_my_tables' ;
    /**
     * Page hook suffix to Data Explorer page or false
     *
     * @var string|false
     */
    protected  $wpda_data_explorer_menu ;
    /**
     * Page hook suffix to Data Designer page or false
     *
     * @var string|false
     */
    protected  $wpda_data_designer_menu ;
    /**
     * Page hook suffix to Data Publisher page or false
     *
     * @var string|false
     */
    protected  $wpda_data_publisher_menu ;
    /**
     * Reference to list view for Data Explorer page
     *
     * @var WPDA_List_View
     */
    protected  $wpda_data_explorer_view ;
    /**
     * Reference to list view for Data Designer page
     *
     * @var WPDA_List_View
     */
    protected  $wpda_data_designer_view ;
    /**
     * Reference to list view for Data Publisher page
     *
     * @var WPDA_List_View
     */
    protected  $wpda_data_publisher_view ;
    /**
     * Data Publisher sub menu title (dynamically set to support translations)
     *
     * @var string
     */
    protected  $title_submenu_publisher ;
    /**
     * Array of page hook suffixes to user defined sub menus
     *
     * @var array
     */
    protected  $wpda_my_table_list_menu = array() ;
    /**
     * Array of list view for user defined sub menus
     *
     * @var array
     */
    protected  $wpda_my_table_list_view = array() ;
    /**
     * Page hook suffix help page or false
     *
     * @var string|false
     */
    protected  $wpda_help ;
    /**
     * Main menu title (dynamically set to support translations)
     *
     * @var string
     */
    protected  $title_menu_menu ;
    /**
     * Data explorer sub menu title (dynamically set to support translations)
     *
     * @var string
     */
    protected  $title_submenu_explorer ;
    /**
     * Data designer sub menu title (dynamically set to support translations)
     *
     * @var string
     */
    protected  $title_submenu_designer ;
    /**
     * Menu slug or null
     *
     * @var null
     */
    protected  $page = null ;
    /**
     * WP_Data_Access_Admin constructor
     *
     * @since   1.0.0
     */
    public function __construct()
    {
        
        if ( isset( $_REQUEST['page'] ) ) {
            $this->page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) );
            // input var okay.
        }
    
    }
    
    /**
     * Add stylesheets to back-end
     *
     * The following stylesheets are added:
     * + Plugin stylesheet
     * + Visual editor stylesheet
     *
     * The plugin stylesheet is used to style the setting forms {@see WPDA_Settings}, simple forms
     * {@see \WPDataAccess\Simple_Form\WPDA_Simple_Form}.
     *
     * @since   1.0.0
     *
     * @see WPDA_Settings
     * @see \WPDataAccess\Simple_Form\WPDA_Simple_Form
     * @see WP_Data_Access_Public
     */
    public function enqueue_styles()
    {
        wp_enqueue_style( 'wp-jquery-ui-core' );
        wp_enqueue_style( 'wp-jquery-ui-dialog' );
        wp_enqueue_style( 'wp-jquery-ui-tabs' );
        wp_enqueue_style( 'wp-jquery-ui-sortable' );
        // WPDataAccess CSS.
        wp_register_style(
            'wpdataaccess',
            plugins_url( '../assets/css/wpda_style.css', __FILE__ ),
            [],
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
        );
        wp_enqueue_style( 'wpdataaccess' );
        // Add WP Data Projects stylesheet.
        wp_register_style(
            'wpdataprojects',
            plugins_url( '../WPDataProjects/assets/css/wpdp_style.css', __FILE__ ),
            [],
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
        );
        wp_enqueue_style( 'wpdataprojects' );
        // Register datetimepicker external library.
        wp_register_style(
            'datetimepicker',
            plugins_url( '../assets/css/jquery.datetimepicker.min.css', __FILE__ ),
            [],
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
        );
        if ( WPDA::get_option( WPDA::OPTION_BE_LOAD_DATATABLES ) === 'on' ) {
            // Load JQuery DataTables to test publication in the dashboard
            wp_register_style(
                'jquery_datatables',
                plugins_url( '../assets/css/jquery.dataTables.min.css', __FILE__ ),
                [],
                WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
            );
        }
        if ( WPDA::get_option( WPDA::OPTION_BE_LOAD_DATATABLES_RESPONSE ) === 'on' ) {
            // Load JQuery DataTables Responsive to test publication in the dashboard
            wp_register_style(
                'jquery_datatables_responsive',
                plugins_url( '../assets/css/responsive.dataTables.min.css', __FILE__ ),
                [],
                WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
            );
        }
        
        if ( WPDA::is_plugin_page( $this->page ) ) {
            // Load UI darkness theme
            wp_enqueue_style(
                'wpda_ui_darkness',
                plugins_url( '../assets/css/jquery-ui.min.css', __FILE__ ),
                [],
                WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
            );
            // Load material icons
            wp_enqueue_style(
                'wpda_material_icons',
                plugins_url( '../assets/icons/material-icons.css', __FILE__ ),
                [],
                WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
            );
        }
    
    }
    
    /**
     * Add scripts to back-end
     *
     * @since   1.0.0
     *
     * @see WP_Data_Access_Public
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-dialog' );
        wp_enqueue_script( 'jquery-ui-tabs' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'jquery-ui-tooltip' );
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        // Register wpda admin functions.
        wp_register_script(
            'wpda_admin_scripts',
            plugins_url( '../assets/js/wpda_admin.js', __FILE__ ),
            [],
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
        );
        wp_enqueue_script( 'wpda_admin_scripts' );
        // Add WP Data Projects JS functions.RERSEARCH
        wp_register_script(
            'wpdataprojects',
            plugins_url( '../WPDataProjects/assets/js/wpdp_admin.js', __FILE__ ),
            [],
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
        );
        wp_enqueue_script( 'wpdataprojects' );
        // Load JQuery DataTables to test publication in the dashboard
        if ( WPDA::get_option( WPDA::OPTION_BE_LOAD_DATATABLES ) === 'on' ) {
            wp_register_script(
                'jquery_datatables',
                plugins_url( '../assets/js/jquery.dataTables.min.js', __FILE__ ),
                [],
                WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
            );
        }
        // Load JQuery DataTables Responsive to test publication in the dashboard
        if ( WPDA::get_option( WPDA::OPTION_BE_LOAD_DATATABLES_RESPONSE ) === 'on' ) {
            wp_register_script(
                'jquery_datatables_responsive',
                plugins_url( '../assets/js/dataTables.responsive.min.js', __FILE__ ),
                [],
                WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
            );
        }
        // Ajax call to WPDA datables implementation to test publication in the dashboard
        $details = __( 'Row details', 'wp-data-access' );
        // Set title of modal window here to support i18n.
        $query_string = str_replace( ' ', '+', "?details={$details}" );
        $query_string .= '&wpda=' . WPDA::get_option( WPDA::OPTION_WPDA_VERSION );
        wp_register_script(
            'wpda_datatables',
            plugins_url( '../assets/js/wpda_datatables.js' . $query_string, __FILE__ ),
            [ 'jquery', 'jquery_datatables', 'jquery_datatables_responsive' ],
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
        );
        wp_localize_script( 'wpda_datatables', 'wpda_ajax', [
            'wpda_ajaxurl' => admin_url( 'admin-ajax.php' ),
        ] );
        // Register purl external library.
        wp_register_script(
            'purl',
            plugins_url( '../assets/js/purl.js', __FILE__ ),
            [ 'jquery' ],
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
        );
        if ( WPDA::is_plugin_page( $this->page ) ) {
            // Register notify external library.
            wp_enqueue_script(
                'wpda_notify',
                plugins_url( '../assets/js/notify.min.js', __FILE__ ),
                [ 'jquery' ],
                WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
            );
        }
        // Register datetimepicker external library.
        wp_register_script(
            'datetimepicker',
            plugins_url( '../assets/js/jquery.datetimepicker.full.min.js', __FILE__ ),
            [ 'jquery' ],
            WPDA::get_option( WPDA::OPTION_WPDA_VERSION )
        );
        // Register clipboard.js
        wp_enqueue_script( 'clipboard' );
        
        if ( self::PAGE_PUBLISHER === $this->page ) {
            $json_editing = WPDA::get_option( WPDA::OPTION_DP_JSON_EDITING );
            
            if ( WPDA::OPTION_DP_JSON_EDITING[1] === $json_editing ) {
                // Register codeEditor to support JSON editing in Data Publisher (table options advanced)
                $cm_settings['codeEditor'] = wp_enqueue_code_editor( [
                    'type'       => 'application/json',
                    'codemirror' => [
                    'autoRefresh' => true,
                ],
                ] );
                wp_enqueue_script( 'wp-theme-plugin-editor' );
                wp_localize_script( 'wp-theme-plugin-editor', 'cm_settings', $cm_settings );
            }
        
        }
        
        wp_enqueue_media();
    }
    
    /**
     * Add plugin menu and sub menus
     *
     * Adds the following menu and sub menus to the back-end menu:
     * + WP Data Access
     *   + Data Explorer
     *   + Data Designer
     *   + Data Projects
     *   + Manage Plugin
     *
     * Menu titles are dynamically set in {@see WP_Data_Access_Admin::set_menu_titles()} to support translations.
     *
     * @since   1.0.0
     *
     * @see WP_Data_Access_Admin::set_menu_titles()
     */
    public function add_menu_items()
    {
        global  $wpdb ;
        // Dynamically set menu titles.
        $this->set_menu_titles();
        
        if ( current_user_can( 'manage_options' ) ) {
            
            if ( 'on' === WPDA::get_option( WPDA::OPTION_PLUGIN_HIDE_ADMIN_MENU ) ) {
                // Show Data Projects
                $this->add_data_projects();
                // Hide admin menu
                return;
            }
            
            // Specific list tables (and forms) can be made available for specific capabilities:
            // managed in method add_menu_my_tables.
            // Main menu and items are only available to admin users (set capability to 'manage_options').
            add_menu_page(
                $this->title_menu_menu,
                $this->title_menu_menu,
                'manage_options',
                self::PAGE_MAIN,
                null,
                'dashicons-editor-table',
                999999999
            );
            // Add data explorer to WPDA menu.
            $this->wpda_data_explorer_menu = add_submenu_page(
                self::PAGE_MAIN,
                $this->title_menu_menu,
                $this->title_submenu_explorer,
                'manage_options',
                self::PAGE_MAIN,
                [ $this, 'data_explorer_page' ]
            );
            
            if ( $this->page === self::PAGE_MAIN ) {
                $args = [
                    'page_hook_suffix' => $this->wpda_data_explorer_menu,
                ];
                $this->wpda_data_explorer_view = new WPDA_List_View( $args );
            }
            
            // Add submenu for Data Publisher
            $data_publisher_table_found = WPDA_Publisher_Model::table_exists();
            
            if ( $data_publisher_table_found ) {
                $data_publisher_page = 'data_publisher_page';
            } else {
                $data_publisher_page = 'data_publisher_page_not_found';
            }
            
            $this->wpda_data_publisher_menu = add_submenu_page(
                self::PAGE_MAIN,
                $this->title_menu_menu,
                $this->title_submenu_publisher,
                'manage_options',
                self::PAGE_PUBLISHER,
                [ $this, $data_publisher_page ]
            );
            if ( $this->page === self::PAGE_PUBLISHER && $data_publisher_table_found ) {
                $this->wpda_data_publisher_view = new WPDA_List_View( [
                    'page_hook_suffix' => $this->wpda_data_publisher_menu,
                    'table_name'       => WPDA_Publisher_Model::get_base_table_name(),
                    'list_table_class' => 'WPDataAccess\\Data_Publisher\\WPDA_Publisher_List_Table',
                    'edit_form_class'  => 'WPDataAccess\\Data_Publisher\\WPDA_Publisher_Form',
                ] );
            }
            // Add Data Projects menu.
            $wpdp = new \WPDataProjects\WPDP();
            $wpdp->add_menu_items();
            // Add data designer to WPDA menu.
            $data_designer_table_found = WPDA_Design_Table_Model::table_exists();
            
            if ( $data_designer_table_found ) {
                $data_designer_page = 'data_designer_page';
            } else {
                $data_designer_page = 'data_designer_page_not_found';
            }
            
            $this->wpda_data_designer_menu = add_submenu_page(
                self::PAGE_MAIN,
                $this->title_menu_menu,
                $this->title_submenu_designer,
                'manage_options',
                self::PAGE_DESIGNER,
                [ $this, $data_designer_page ]
            );
            if ( $this->page === self::PAGE_DESIGNER && $data_designer_table_found ) {
                $this->wpda_data_designer_view = new WPDA_List_View( [
                    'page_hook_suffix' => $this->wpda_data_designer_menu,
                    'table_name'       => WPDA_Design_Table_Model::get_base_table_name(),
                    'list_table_class' => 'WPDataAccess\\Design_Table\\WPDA_Design_Table_List_Table',
                    'edit_form_class'  => 'WPDataAccess\\Design_Table\\WPDA_Design_Table_Form',
                    'subtitle'         => '',
                ] );
            }
        } else {
            $this->grant_access_to_data_publications();
        }
        
        $this->add_data_projects();
    }
    
    protected function add_data_projects()
    {
        // Add Data Projects
        $wpdp = new \WPDataProjects\WPDP();
        $wpdp->add_projects();
    }
    
    protected function grant_access_to_data_publications()
    {
        // Check user role
        $user_roles = WPDA::get_current_user_roles();
        if ( false === $user_roles || !is_array( $user_roles ) ) {
            // Cannot determine the user roles (not able to show menus)
            return;
        }
        $publication_roles = WPDA::get_option( WPDA::OPTION_DP_PUBLICATION_ROLES );
        if ( '' === $publication_roles || 'administrator' === $publication_roles ) {
            // No access
            return;
        }
        $user_has_role = false;
        foreach ( $user_roles as $user_role ) {
            if ( false !== strpos( $publication_roles, $user_role ) ) {
                $user_has_role = true;
            }
        }
        if ( !$user_has_role ) {
            // No access
            return;
        }
        // Grant access to main menu
        add_menu_page(
            $this->title_menu_menu,
            $this->title_menu_menu,
            WPDA::get_current_user_capability(),
            self::PAGE_PUBLISHER,
            null,
            'dashicons-editor-table',
            999999999
        );
        
        if ( WPDA_Publisher_Model::table_exists() ) {
            global  $wpdb ;
            // Add submenu for Data Publisher
            $this->wpda_data_publisher_menu = add_submenu_page(
                self::PAGE_PUBLISHER,
                $this->title_menu_menu,
                $this->title_submenu_publisher,
                WPDA::get_current_user_capability(),
                self::PAGE_PUBLISHER,
                [ $this, 'data_publisher_page' ]
            );
            if ( $this->page === self::PAGE_PUBLISHER ) {
                $this->wpda_data_publisher_view = new WPDA_List_View( [
                    'page_hook_suffix' => $this->wpda_data_publisher_menu,
                    'table_name'       => $wpdb->prefix . 'wpda_publisher',
                    'list_table_class' => 'WPDataAccess\\Data_Publisher\\WPDA_Publisher_List_Table',
                    'edit_form_class'  => 'WPDataAccess\\Data_Publisher\\WPDA_Publisher_Form',
                ] );
            }
        }
    
    }
    
    /**
     * Dynamically set menu titles
     *
     * Dynamically set menu titles to support translations.
     *
     * @since   1.0.0
     */
    protected function set_menu_titles()
    {
        $this->title_menu_menu = 'WP Data Access';
        $this->title_submenu_explorer = __( 'Data Explorer', 'wp-data-access' );
        $this->title_submenu_designer = __( 'Data Designer', 'wp-data-access' );
        $this->title_submenu_publisher = __( 'Data Publisher', 'wp-data-access' );
    }
    
    /**
     * Show data explorer main page
     *
     * Initialization of $this->wpda_data_explorer_view is done earlier in
     * {@see WP_Data_Access_Admin::add_menu_items()} to support screen options. This method just shows the page
     * containing the list table.
     *
     * @since   1.0.0
     *
     * @see WP_Data_Access_Admin::add_menu_items()
     */
    public function data_explorer_page()
    {
        
        if ( isset( $_REQUEST['page_action'] ) && 'wpda_backup' === $_REQUEST['page_action'] ) {
            $this->backup_page();
        } elseif ( isset( $_REQUEST['page_action'] ) && 'wpda_import_csv' === $_REQUEST['page_action'] ) {
            $this->import_csv();
        } else {
            $this->wpda_data_explorer_view->show();
        }
    
    }
    
    public function import_csv()
    {
        $csv_import = new WPDA_CSV_Import();
        $csv_import->show();
    }
    
    /**
     * Show data designer main page
     *
     * Initialization of $this->wpda_data_designer_view is done earlier in
     * {@see WP_Data_Access_Admin::add_menu_items()} to support screen options. This method just shows the page
     * containing the list table.
     *
     * @since   1.0.0
     *
     * @see WP_Data_Access_Admin::add_menu_items()
     */
    public function data_designer_page()
    {
        $this->wpda_data_designer_view->show();
    }
    
    /**
     * Data Designer repository table not found
     */
    public function data_designer_page_not_found()
    {
        $wpda_repository = new WPDA_Repository();
        $wpda_repository->inform_user();
        ?>
		<div class="wrap">
			<h1 class="wp-heading-inline">
				<span><?php 
        echo  $this->title_submenu_designer ;
        ?></span>
				<a href="https://wpdataaccess.com/docs/documentation/data-designer/" target="_blank" class="wpda_tooltip" title="Plugin Help - opens in a new tab or window">
					<span class="dashicons dashicons-editor-help"
						  style="text-decoration:none;vertical-align:top;font-size:30px;">
					</span></a>
			</h1>
			<p>
				<?php 
        echo  __( 'ERROR: Repository table not found!', 'wp-data-access' ) ;
        ?>
			</p>
		</div>
		<?php 
    }
    
    /**
     * Show data publisher main page
     */
    public function data_publisher_page()
    {
        $this->wpda_data_publisher_view->show();
    }
    
    /**
     * Data Publisher repository table not found
     */
    public function data_publisher_page_not_found()
    {
        $wpda_repository = new WPDA_Repository();
        $wpda_repository->inform_user();
        ?>
		<div class="wrap">
			<h1 class="wp-heading-inline">
				<span><?php 
        echo  $this->title_submenu_publisher ;
        ?></span>
				<a href="https://wpdataaccess.com/docs/documentation/data-publisher/" target="_blank" class="wpda_tooltip" title="Plugin Help - opens in a new tab or window">
					<span class="dashicons dashicons-editor-help"
						  style="text-decoration:none;vertical-align:top;font-size:30px;">
					</span></a>
			</h1>
			<p>
				<?php 
        echo  __( 'ERROR: Repository table not found!', 'wp-data-access' ) ;
        ?>
			</p>
		</div>
		<?php 
    }
    
    /**
     * Show data backup main page
     *
     * Calls a page to create automatic backups (in fact data exports) and offers possibilities to restore (in fact
     * data imports).
     *
     * @since   2.0.6
     *
     * @see WPDA_Data_Export::show_wp_cron()
     */
    public function backup_page()
    {
        $wpda_backup = new WPDA_Data_Export();
        
        if ( isset( $_REQUEST['action'] ) ) {
            $action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
            // input var okay.
            
            if ( 'new' === $action ) {
                $wpda_backup->create_export( 'add' );
            } elseif ( 'add' === $action ) {
                $wpda_backup->wpda_add_cron_job();
            } elseif ( 'remove' === $action ) {
                $wpda_backup->wpda_remove_cron_job();
            } elseif ( 'edit' === $action ) {
                $wpda_backup->create_export( 'update' );
            } elseif ( 'update' === $action ) {
                $wpda_backup->wpda_update_cron_job();
            }
        
        } else {
            $wpda_backup->show_wp_cron();
        }
    
    }
    
    /**
     * Add user defined sub menu
     *
     * WPDA allows users to create sub menu for table lists and simple forms. Sub menus can be added to the WPDA
     * menu or any other (external) menu. A sub menu is added to an external menu via the menu slug. Sub menus are
     * taken from {@see WPDA_User_Menus_Model}.
     *
     * This method is called from the admin_menu action with a lower priority to make sure other menus are available.
     * User defined menu items are added to avalable menus in this method. These can be WPDA menus or external menus
     * as mentioned in the according list table and edit form. WPDA menus are added to menu WP Data Tables. External
     * menus are added to the menu having the menu slug defined by the user.
     *
     * This method does not actually show the list tables! It just creates the menu items. When the user clicks on such
     * a dynamiccally defined menu item, method {@see WP_Data_Access_Admin::my_tables_page()} is called, which takes
     * care of showing the list table.
     *
     * @since   1.0.0
     *
     * @see WP_Data_Access_Admin::my_tables_page()
     * @see WPDA_User_Menus_Model
     */
    public function add_menu_my_tables()
    {
        // Dynamically set menu titles.
        $this->set_menu_titles();
        $menus_shown_to_current_user = [];
        // Add list tables to external menus.
        foreach ( WPDA_User_Menus_Model::list_external_menus() as $menu ) {
            $user_roles = WPDA::get_current_user_roles();
            $user_has_role = false;
            
            if ( '' === $menu->menu_role || null === $menu->menu_role ) {
                $user_has_role = in_array( 'administrator', $user_roles );
            } else {
                $user_role_array = explode( ',', $menu->menu_role );
                foreach ( $user_role_array as $user_role_array_item ) {
                    $user_has_role = in_array( $user_role_array_item, $user_roles );
                    if ( $user_has_role ) {
                        break;
                    }
                }
            }
            
            if ( $user_has_role ) {
                
                if ( !isset( $menus_shown_to_current_user[$menu->menu_slug . '/' . $menu->menu_name . '/' . $menu->menu_table_name . '/' . $menu->menu_schema_name] ) ) {
                    $menu_slug = self::PAGE_EXPLORER . '_' . $menu->menu_table_name;
                    $menu_index = $menu->menu_table_name;
                    
                    if ( $menu->menu_slug === self::PAGE_MAIN && empty($GLOBALS['admin_page_hooks'][$menu->menu_slug]) ) {
                        add_menu_page(
                            $this->title_menu_menu,
                            $this->title_menu_menu,
                            WPDA::get_current_user_capability(),
                            self::PAGE_MAIN,
                            null,
                            'dashicons-editor-table',
                            999999999
                        );
                        $menu_slug = self::PAGE_MAIN;
                        $menu_index = self::PAGE_MAIN;
                    }
                    
                    $this->wpda_my_table_list_menu[$menu_index] = add_submenu_page(
                        $menu->menu_slug,
                        $this->title_menu_menu . ' : ' . strtoupper( $menu->menu_table_name ),
                        $menu->menu_name,
                        WPDA::get_current_user_capability(),
                        $menu_slug,
                        [ $this, 'my_tables_page' ]
                    );
                    $this->wpda_my_table_list_view[$menu_index] = new WPDA_List_View( [
                        'page_hook_suffix' => $this->wpda_my_table_list_menu[$menu_index],
                        'wpdaschema_name'  => $menu->menu_schema_name,
                        'table_name'       => $menu->menu_table_name,
                    ] );
                    $menus_shown_to_current_user[$menu->menu_slug . '/' . $menu->menu_name . '/' . $menu_index . '/' . $menu->menu_schema_name] = true;
                }
            
            }
        }
    }
    
    /**
     * Show user defined menus
     *
     * A user defined menu that are added to the plugin menu in {@see WP_Data_Access_Admin::add_menu_my_tables()} is
     * shown here. This method is called when the user clicks on the menu item generated in
     * {@see WP_Data_Access_Admin::add_menu_my_tables()}.
     *
     * @since   1.0.0
     *
     * @see WP_Data_Access_Admin::add_menu_my_tables()
     */
    public function my_tables_page()
    {
        // Grab table name from menu slug.
        
        if ( null !== $this->page ) {
            
            if ( $this->page === self::PAGE_MAIN ) {
                $table = self::PAGE_MAIN;
            } else {
                
                if ( strpos( $this->page, self::PAGE_EXPLORER ) !== false ) {
                    $table = substr( $this->page, strlen( self::PAGE_EXPLORER . '_' ) );
                } else {
                    $table = substr( $this->page, strlen( self::PAGE_MY_TABLES . '_' ) );
                }
            
            }
            
            // Show list table.
            $this->wpda_my_table_list_view[$table]->show();
        }
    
    }

}