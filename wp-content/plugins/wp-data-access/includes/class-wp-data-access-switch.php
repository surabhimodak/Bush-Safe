<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package plugin\includes
 */
use  WPDataAccess\Plugin_Table_Models\WPDA_Design_Table_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDA_Logging_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDA_Media_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDA_Publisher_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDA_Table_Settings_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDA_User_Menus_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDP_Page_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDP_Project_Model ;
use  WPDataAccess\Plugin_Table_Models\WPDP_Project_Design_Table_Model ;
use  WPDataAccess\Utilities\WPDA_Repository ;
use  WPDataAccess\WPDA ;
/**
 * Class WP_Data_Access_Switch
 *
 * Switch to:
 * + activate plugin {@see WP_Data_Access_Switch::activate()}
 * + deactive plugin {@see WP_Data_Access_Switch::deactivate()}
 *
 * @author  Peter Schulz
 * @since   1.0.0
 *
 * @see WP_Data_Access_Switch::activate()
 * @see WP_Data_Access_Switch::deactivate()
 */
class WP_Data_Access_Switch
{
    /**
     * Activate plugin WP Data Access
     *
     * The user must have the appropriate privileges to perform this operation.
     *
     * For single site installation {@see WP_Data_Access_Switch::activate_blog()} will be called. For multi site
     * installations {@see WP_Data_Access_Switch::activate_blog()} must be called for every blog.
     *
     * IMPORTANT!!!
     *
     * For blogs installed on multi site installations after activation of the plugin, activation of the plugin for
     * that blog will not be performed if the plugin is network activated. In that case the admin user of the blog
     * will receive a message when viewing a plugin page with an option to follow these steps manually.
     *
     * @since   1.0.0
     *
     * @see WP_Data_Access_Switch::activate_blog()
     */
    public static function activate()
    {
        
        if ( current_user_can( 'activate_plugins' ) ) {
            // Activate plugin.
            
            if ( is_multisite() ) {
                global  $wpdb ;
                // Multisite installation.
                $blogids = $wpdb->get_col( "select blog_id from {$wpdb->blogs}" );
                // db call ok; no-cache ok.
                foreach ( $blogids as $blog_id ) {
                    // Uninstall blog.
                    switch_to_blog( $blog_id );
                    self::activate_blog();
                    restore_current_blog();
                }
            } else {
                // Single site installation.
                self::activate_blog();
            }
        
        } else {
            // This blocks the site on unattended plugin update! (support topic 11472418 - tjgorman)
            // wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
        }
    
    }
    
    /**
     * Activate blog
     *
     * The user must have the appropriate privileges to perform this operation.
     *
     * On activation this method checks whether there has previously been a version of the plugin installed. For this
     * purpose the wp_options table read directly (usually done via class WPDA). If a value is found, this method
     * checks if the version number in wp_options is the same as the plugin version. If these are equal, no action is
     * needed. If they are not equal, this method will check if there is an upgrade or downgrade for the delta
     * between these releases.
     *
     * This action is performed on the 'active WordPress blog'. On single site there is only one blog. On multisite
     * installations it must be executed for every blog.
     *
     * On a fresh installation the following actions are performed:
     * + save plugin version number in wp_options {@see WPDA::set_option()}
     * + (re)create plugin repository {@see WPDA_Repository::recreate()}
     *
     * @since   1.0.0
     *
     * @see WPDA::set_option()
     * @see WPDA_Repository::create()
     */
    protected static function activate_blog()
    {
        
        if ( current_user_can( 'activate_plugins' ) ) {
            
            if ( WPDataAccess\WPDA::OPTION_WPDA_VERSION[1] !== get_option( WPDataAccess\WPDA::OPTION_WPDA_VERSION[0] ) ) {
                self::recreate_repository();
            } elseif ( !WPDA_User_Menus_Model::table_exists() || !WPDA_Design_Table_Model::table_exists() || !WPDP_Project_Design_Table_Model::table_exists() || !WPDA_Publisher_Model::table_exists() || !WPDA_Logging_Model::table_exists() || !WPDA_Media_Model::table_exists() || !WPDP_Project_Model::table_exists() || !WPDP_Page_Model::table_exists() || !WPDA_Table_Settings_Model::table_exists() ) {
                self::recreate_repository();
            }
            
            if ( wpda_fremius()->is_free_plan() ) {
                update_option( 'wpda_fulltext_support', 'off' );
            }
        } else {
            // This blocks the site on unattended plugin update! (support topic 11472418 - tjgorman)
            // wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
        }
    
    }
    
    /**
     * (re)create plugin repository
     *
     * If no repository is found, a new one is created
     * If a repository is found, the table structures are update and the data transferred
     */
    protected static function recreate_repository()
    {
        $wpda_repository = new WPDA_Repository();
        $wpda_repository->recreate();
        // Save (new) plugin version
        WPDA::set_option( WPDA::OPTION_WPDA_VERSION );
        // Show link to "What's New?" page on plugin pages in WordPress dashboard.
        WPDA::set_option( WPDA::OPTION_WPDA_SHOW_WHATS_NEW, 'on' );
    }
    
    /**
     * Deactivate plugin WP Data Access
     *
     * On deactivation we leave the repository and options as they are in case the user wants to reactivate the
     * plugin later again. Tables and options are deleted when the plugin is uninstalled. To keep tables and options
     * on uninstall change plugin settings (see uninstall settings).
     *
     * @since   1.0.0
     */
    public static function deactivate()
    {
        if ( !current_user_can( 'activate_plugins' ) ) {
            // This blocks the site on unattended plugin update! (support topic 11472418 - tjgorman)
            // wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
        }
    }

}