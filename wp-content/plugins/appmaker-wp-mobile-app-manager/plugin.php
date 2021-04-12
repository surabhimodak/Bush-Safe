<?php
/*
Plugin Name: Appmaker WP - Convert WordPress to Native Android & iOS App
Plugin URI: https://appmaker.xyz/wordpress
Description: Connect WordPress site to Android and iOS mobile app using Appmaker.xyz
Version: 0.4.3
Author: Appmaker
Author URI: https://appmaker.xyz/wordpress
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( __FILE__ ) . '/lib/class-appmaker-wp-options.php';

/**
 * Class APPMAKER_WP
 */
class APPMAKER_WP {

	static $root;
	/**
	 * @var APPMAKER_WP_API
	 */
	static $api;

	static $version = '0.4.3';

	public static function init() {
		register_activation_hook( __FILE__, self::func( 'activated' ) );
		register_deactivation_hook( __FILE__, self::func( 'deactivate' ) );
		register_uninstall_hook( __FILE__, self::func( 'uninstall' ) );
		add_action( 'plugins_loaded', self::func( 'init_plugins_loaded' ) );
		defined( 'APPMAKER_WP_API' ) || define( 'APPMAKER_WP_API', true );
		add_action( 'admin_notices', self::func( 'plugin_configure' ) );
	}

	public static function plugin_configure() {

		$options = get_option( 'appmaker_wp_settings' );

		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) && ( isset( $_GET['page'] ) && 'appmaker-wp-admin' === $_GET['page'] ) ) {
			?>
			<div class="notice is-dismissible" style = "background: #FFC107;">
			<div class="warning-box" style = "background-color: #FFC107; padding: 20px; color: #212121; color: #212121;">
			<div style="display: flex;">
			<img src="https://72f4acea6b.to.intercept.rest/stateless-appmaker-pages-wp/2019/10/b178d69e-appmaker-logo.png" width="100px" height="100px" style="margin-right:20px"  />
			<div>
			<h1 style = "margin-top: 0;">Looks like this is a WooCommerce website</h1>
							<p style = "font-size: 18px; margin-top: 0.1rem;">You have installed the wrong plugin. To convert this store to a mobile app, you should install
								<b>"Appmaker WooCommerce plugin"</b>.</p>
			</div>	
			</div>
				<a href="plugin-install.php?s=appmaker+wc&tab=search&type=term" style = "padding:12px 20px; background-color: #000; color: #fff; text-decoration: none; border-radius: 4px; display: inline-block; font-size: 18px; margin-right: 30px; margin-top: 10px;">Install Appmaker WooCommerce Plugin</a>
			</div>
			</div>

			<?php
		}
		if ( empty( $options['api_key'] ) && ! ( isset( $_POST['appmaker_wp_settings'] ) && ! empty( $_POST['appmaker_wp_settings']['api_key'] ) ) ) {
			?>
			<div class="notice notice-error" style="display: flex;">
				<a href="https://appmaker.xyz/wordpress/?utm_source=wordpress-plugin&utm_medium=admin-notice&utm_campaign=after-plugin-install" class="logo" style="margin: auto;"><img src="https://storage.googleapis.com/stateless-appmaker-pages-wp/2019/04/10b81502-mask-group-141.png" alt="Appmaker.xyz"/></a>
				<div style="flex-grow: 1; margin: 15px 15px;">
					<h4 style="margin: 0;">Configure app to continue</h4>
					<p><?php echo __( 'Ouch!😓 It appears that your WordPress App is not configured correctly. Kindly configure with correct API details.', 'appmaker-woocommerce-mobile-app-manager' ); ?></p>
				</div>
				<a href="options-general.php?page=appmaker-wp-admin" class="button button-primary" style="margin: auto 15px; background-color: #f16334; border-color: #f16334; text-shadow: none; box-shadow: none;">Take me there !</a>
			</div>
			<?php
		}
	}

	public static function func( $func ) {
		return array( self::name(), $func );
	}

	public static function name() {
		return 'APPMAKER_WP';
	}

	public static function init_plugins_loaded() {

		if ( ! self::compatible_version() ) {
			return;
		}

		if ( is_admin() ) {
			new APPMAKER_WP_Options();
		}

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), self::func( 'add_plugin_action_links' ) );
		self::$root = dirname( __FILE__ );
		self::load_appmaker_rest_api();

	}

	public static function compatible_version() {
		if ( version_compare( $GLOBALS['wp_version'], '4.4', '<' ) ) {
			return false;
		}

		return true;
	}

	public static function load_appmaker_rest_api() {
		if ( ! class_exists( 'APPMAKER_WP_API' ) ) {
			include_once self::$root . '/lib/class-appmaker-wp-api.php';
		}
		self::$api = APPMAKER_WP_API::get_instance();
	}

	/**
	 * Registers default REST API routes.
	 *
	 * @since 4.4.0
	 */
	public static function create_initial_rest_routes() {

	}

	public static function add_plugin_action_links( $links ) {
		return array_merge(
			array(
				'settings'   => '<a href="' . get_bloginfo( 'wpurl' ) . '/wp-admin/options-general.php?page=appmaker-wp-admin">Settings</a>',
				// 'docs' => '<a target="_blank" href="https://appmaker.xyz/wordpress/docs/">Docs</a>',
				'create_app' => '<a target="_blank" href="https://appmaker.xyz/wordpress?utm_source=wordpress-plugin&utm_medium=plugins-page&utm_campaign=after-plugin-install">Create App</a>',
			),
			$links
		);
	}

	public static function disabled_notice() {
		$class   = 'notice notice-error';
		$message = __( 'Appmaker WP Mobile App Manager WordPress 4.4 or higher', 'appmaker_wp' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );

	}

	public static function activated() {
		if ( ! self::compatible_version() ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			add_action( 'admin_notices', self::func( 'disabled_notice' ) );
			wp_die( __( 'Appmaker Mobile App Manager requires WordPress 4.4 or higher. Contact support@appmaker.xyz for more information', 'appmaker_wp' ) );
		}
		do_action( 'appmaker_wp_plugin_activate' );
	}

	public static function uninstall() {
		// flush_rewrite_rules();
		do_action( 'appmaker_wp_plugin_uninstall' );
	}

	public static function deactivate() {
		do_action( 'appmaker_wp_plugin_deactivate' );
	}

}
function appmaker_wp_plugin_activated( $plugin ) {
	if ( $plugin == plugin_basename( __FILE__ ) ) {
		do_action( 'APPMAKER_WC_Plugin_activate' );
		wp_safe_redirect( admin_url( 'options-general.php?page=appmaker-wp-admin' ) );
		exit;
	}
}
add_action( 'activated_plugin', 'appmaker_wp_plugin_activated' );


APPMAKER_WP::init();
