<?php
/**
 * Lalita WordPress theme.
 *
 * Please do not make any edits to this file. All edits should be done in a child theme.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Set our theme version.
define( 'LALITA_VERSION', '1.2.1' );

if ( ! function_exists( 'lalita_setup' ) ) {
	add_action( 'after_setup_theme', 'lalita_setup' );
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 */
	function lalita_setup() {
		// Make theme available for translation.
		load_theme_textdomain( 'lalita' );

		// Add theme support for various features.
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'post-formats', array( 'aside', 'image', 'quote', 'link', 'status' ) );
		add_theme_support( 'woocommerce' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'html5', array( 'comment-form', 'comment-list', 'gallery', 'caption' ) );
		add_theme_support( 'customize-selective-refresh-widgets' );

		add_theme_support( 'custom-logo', array(
			'height' => 72,
			'width' => 240,
			'flex-height' => true,
			'flex-width' => true
		) );
		
		add_theme_support( "custom-header",
			array(
				'default-image'          => '',
				'flex-height'            => false,
				'flex-width'             => false,
				'uploads'                => true,
				'random-default'         => false,
				'header-text'            => false,
				'wp-head-callback'       => '',
				'admin-head-callback'    => '',
				'admin-preview-callback' => '',
			)
		);

		// Register primary menu.
		register_nav_menus( array(
			'primary' => __( 'Primary Menu', 'lalita' ),
		) );

		/**
		 * Set the content width to something large
		 * We set a more accurate width in lalita_smart_content_width()
		 */
		global $content_width;
		if ( ! isset( $content_width ) ) {
			$content_width = 1200; /* pixels */
		}

		// This theme styles the visual editor to resemble the theme style.
		add_editor_style( 'css/admin/editor-style.css' );
	}
}

if ( ! function_exists( 'lalita_background_setup' ) ) {
	add_action( 'after_setup_theme', 'lalita_background_setup' );
	/**
	 * Sets up background defaults and registers support for WordPress features.
	 *
	 */
	function lalita_background_setup() {		
		add_theme_support( "custom-background",
			array(
				'default-color' 		 => 'fefefe',
				'default-image'          => '',
				'default-repeat'         => 'repeat',
				'default-position-x'     => 'left',
				'default-position-y'     => 'top',
				'default-size'           => 'auto',
				'default-attachment'     => '',
				'wp-head-callback'       => '_custom_background_cb',
				'admin-head-callback'    => '',
				'admin-preview-callback' => ''
			)
		);
	}
}

/**
 * Get all necessary theme files
 */
get_template_part( 'inc/theme', 'functions' );
get_template_part( 'inc/defaults' );
get_template_part( 'inc/class', 'css' );
get_template_part( 'inc/css', 'output' );
get_template_part( 'inc/general' );
get_template_part( 'inc/customizer' );
get_template_part( 'inc/markup' );
get_template_part( 'inc/element', 'classes' );
get_template_part( 'inc/typography' );
get_template_part( 'inc/plugin', 'compat' );
get_template_part( 'inc/class-tgm-plugin', 'activation' );

if ( is_admin() ) {
	require get_template_directory() . '/inc/meta-box.php';
	require get_template_directory() . '/inc/dashboard.php';
}

/**
 * Load our theme structure
 */
get_template_part( 'inc/structure/archives' );
get_template_part( 'inc/structure/comments' );
get_template_part( 'inc/structure/featured', 'images' );
get_template_part( 'inc/structure/footer' );
get_template_part( 'inc/structure/header' );
get_template_part( 'inc/structure/navigation' );
get_template_part( 'inc/structure/post', 'meta' );
get_template_part( 'inc/structure/sidebars' );
get_template_part( 'inc/structure/social', 'bar' );

if ( ! function_exists( 'lalita_theme_uri_link' ) ) {
	function lalita_theme_uri_link() {
		return 'https://wpkoi.com/lalita-wpkoi-wordpress-theme/';
	}
}

define('LALITA_THEME_URL','https://wpkoi.com/lalita-wpkoi-wordpress-theme/');
define('LALITA_WPKOI_AUTHOR_URL','https://wpkoi.com/');
define('LALITA_WPKOI_SOCIAL_URL','https://www.facebook.com/wpkoithemes/');
define('LALITA_WORDPRESS_REVIEW','https://wordpress.org/support/theme/lalita/reviews/?filter=5');
define('LALITA_DOCUMENTATION','https://wpkoi.com/docs/');
define('LALITA_FONT_AWESOME_LINK','https://wpkoi.com/docs/custom-icons/');

