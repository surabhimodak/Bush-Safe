<?php

/**
 * Plugin Name:       WP Data Access
 * Plugin URI:        https://wpdataaccess.com/
 * Description:       Local and remote data administration, publication and app development tool available directly from the WordPress dashboard.
 * Version:           4.2.1
 * Author:            Passionate Programmers
 * Author URI:        https://wpdataaccess.com/
 * Text Domain:       wp-data-access
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 *
 *
 * @package plugin
 * @author  Peter Schulz
 * @since   1.0.0
 */
if ( !defined( 'WPINC' ) ) {
    die;
}
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Add freemius to WP Data Access

if ( function_exists( 'wpda_fremius' ) ) {
    wpda_fremius()->set_basename( false, __FILE__ );
} else {
    // Load WPDataAccess namespace.
    require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
    // Create a helper function for easy SDK access
    function wpda_fremius()
    {
        global  $wpda_fremius ;
        
        if ( !isset( $wpda_fremius ) ) {
            // Include Freemius SDK
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $wpda_fremius = fs_dynamic_init( array(
                'id'             => '6189',
                'slug'           => 'wp-data-access',
                'type'           => 'plugin',
                'public_key'     => 'pk_fc2d1714ca61c930152f6e326b575',
                'is_premium'     => false,
                'premium_suffix' => 'Premium',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                'days'               => 14,
                'is_require_payment' => false,
            ),
                'menu'           => array(
                'slug'    => 'wpda',
                'network' => true,
            ),
                'is_live'        => true,
            ) );
        }
        
        return $wpda_fremius;
    }
    
    // Init Freemius
    wpda_fremius();
    // Signal that SDK was initiated
    do_action( 'wpda_fremius_loaded' );
    // Change plugin settings info
    function wpda_row_meta( $links, $file )
    {
        
        if ( strpos( $file, plugin_basename( __FILE__ ) ) !== false ) {
            // Add settings link
            $settings_url = esc_url( get_admin_url() . 'options-general.php?page=wpdataaccess' );
            $settings_link = "<a href='{$settings_url}'>Settings</a>";
            array_push( $links, $settings_link );
        }
        
        return $links;
    }
    
    add_filter(
        'plugin_row_meta',
        'wpda_row_meta',
        10,
        2
    );
    /**
     * Activate plugin
     *
     * @author  Peter Schulz
     * @since   1.0.0
     */
    function activate_wp_data_access()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-data-access-switch.php';
        WP_Data_Access_Switch::activate();
    }
    
    register_activation_hook( __FILE__, 'activate_wp_data_access' );
    /**
     * Deactivate plugin
     *
     * @author  Peter Schulz
     * @since   1.0.0
     */
    function deactivate_wp_data_access()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-data-access-switch.php';
        WP_Data_Access_Switch::deactivate();
    }
    
    register_deactivation_hook( __FILE__, 'deactivate_wp_data_access' );
    /**
     * Check if database needs to be updated
     *
     * @author  Peter Schulz
     * @since   1.5.2
     */
    function wpda_update_db_check()
    {
        if ( WPDataAccess\WPDA::OPTION_WPDA_VERSION[1] !== get_option( WPDataAccess\WPDA::OPTION_WPDA_VERSION[0] ) ) {
            activate_wp_data_access();
        }
    }
    
    add_action( 'plugins_loaded', 'wpda_update_db_check' );
    /**
     * Uninstall blog
     *
     * This functions is called when the plugin is uninstalled. The following actions are performed:
     * + Drop plugin tables (unless settings indicate not to)
     * + Delete plugin options from $wpdb->options (unless settings indicate not to)
     *
     * Actions are processed on the current blog and are repeated for every blog on a multisite installation. Must be
     * called from the dashboard (WP_UNINSTALL_PLUGIN defined). User must have the proper privileges (activate_plugins).
     *
     * @author      Peter Schulz
     * @since       1.0.0
     */
    function wpda_uninstall_blog()
    {
        global  $wpdb ;
        $drop_tables = get_option( 'wpda_uninstall_tables' );
        
        if ( 'on' === $drop_tables ) {
            // Get all plugin table names (without WP prefix)
            $plugin_tables = WPDataAccess\WPDA::get_wpda_tables();
            foreach ( $plugin_tables as $plugin_table ) {
                // Loop through plugin tables
                // Drop plugin table
                $wpdb->query( "DROP TABLE IF EXISTS {$plugin_table}" );
                // Get plugin backup tables (if applicable)
                $query = "select table_name as table_name from information_schema.tables " . "where table_schema = '{$wpdb->dbname}' " . "  and table_name like '{$plugin_table}_BACKUP_%'";
                $backup_tables = $wpdb->get_results( $query, 'ARRAY_A' );
                foreach ( $backup_tables as $backup_table ) {
                    // Drop plugin backup table
                    $wpdb->query( "DROP TABLE IF EXISTS {$backup_table['table_name']}" );
                }
            }
        }
        
        $delete_options = get_option( 'wpda_uninstall_options' );
        
        if ( 'on' === $delete_options ) {
            // Delete all options from wp_options.
            $wpdb->query( "\n\t\t\tDELETE FROM {$wpdb->options}\n\t\t\tWHERE option_name LIKE 'wpda_%'\n\t\t" );
            // db call ok; no-cache ok.
        }
    
    }
    
    function wpda_uninstall()
    {
        
        if ( is_multisite() ) {
            global  $wpdb ;
            // Uninstall plugin for alll blogs one by one (will fail silently for blogs having no plugin tables/options).
            $blogids = $wpdb->get_col( "select blog_id from {$wpdb->blogs}" );
            // db call ok; no-cache ok.
            foreach ( $blogids as $blog_id ) {
                // Uninstall blog.
                switch_to_blog( $blog_id );
                wpda_uninstall_blog();
                restore_current_blog();
            }
        } else {
            // Uninstall on single site installation.
            wpda_uninstall_blog();
        }
    
    }
    
    wpda_fremius()->add_action( 'after_uninstall', 'wpda_uninstall' );
    // Send user to support page
    function wpda_support_forum_url( $wp_org_support_forum_url )
    {
        if ( wpda_fremius()->is_premium() ) {
            // Use different support page for premium version
            return 'https://users.freemius.com/store/2612';
        }
        return 'https://wordpress.org/support/plugin/wp-data-access/';
    }
    
    wpda_fremius()->add_filter( 'support_forum_url', 'wpda_support_forum_url' );
    // Add WP Data Access icon to freemius
    function wpda_freemius_icon()
    {
        return dirname( __FILE__ ) . '/freemius/assets/img/wpda.png';
    }
    
    wpda_fremius()->add_filter( 'plugin_icon', 'wpda_freemius_icon' );
    // Handle freemius menu items
    function wpda_freemius_menu_visible( $is_visible, $submenu_id )
    {
        // support, account, contact, pricing
        if ( $submenu_id === 'contact' ) {
            $is_visible = false;
        }
        if ( wpda_fremius()->is_premium() && $submenu_id === 'contact' ) {
            $is_visible = true;
        }
        return $is_visible;
    }
    
    wpda_fremius()->add_filter(
        'is_submenu_visible',
        'wpda_freemius_menu_visible',
        10,
        2
    );
    /**
     * Start plugin
     *
     * @author  Peter Schulz
     * @since   1.0.0
     */
    function run_wp_data_access()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-data-access.php';
        $wpdataaccess = new WP_Data_Access();
        $wpdataaccess->run();
    }
    
    run_wp_data_access();
}
