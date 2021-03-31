<?php 
/**
 * Adventure Lite functions and definitions
 *
 * @package Adventure Lite
 */
 $GLOBALS['content_width'] = 780; /* pixels */ 

/**
 * Set the content width based on the theme's design and stylesheet.
 */

if ( ! function_exists( 'adventure_lite_setup' ) ) : 
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 */
 
function adventure_lite_setup() {
	load_theme_textdomain( 'adventure-lite', get_template_directory() . '/languages' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support('woocommerce');
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-header' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'custom-logo', array(
		'height'      => 125,
		'width'       => 250,
		'flex-height' => true,
	) );	
 
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'adventure-lite' ),		
	) );
	add_theme_support( 'custom-background', array(
		'default-color' => 'ffffff'
	) );
	add_editor_style( 'adventure-lite-editor-style.css' );
} 
endif; // adventure_lite_setup

add_action( 'after_setup_theme', 'adventure_lite_setup' );

if ( ! function_exists( 'adventure_lite_excerpt_length' ) ) {
	/**
	 * Filter the except length to 15 characters.
	 * Returns default on admin side
	 *
	 * @return int - modified excerpt length.
	 */
	function adventure_lite_excerpt_length( $length ) {
		return is_admin() ? $length : 15;
	}
}
add_filter( 'excerpt_length', 'adventure_lite_excerpt_length', 999 );

function adventure_lite_widgets_init() { 	
	
	register_sidebar( array(
		'name'          => esc_html__( 'Blog Sidebar', 'adventure-lite' ),
		'description'   => esc_html__( 'Appears on blog page sidebar', 'adventure-lite' ),
		'id'            => 'sidebar-1',
		'before_widget' => '',		
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3><aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
	) );
	
	register_sidebar( array(
		'name'          => esc_html__( 'Header Right Widget', 'adventure-lite' ),
		'description'   => esc_html__( 'Appears on top of the header', 'adventure-lite' ),
		'id'            => 'header-right-widget',
		'before_widget' => '',		
		'before_title'  => '',
		'after_title'   => '',
		'after_widget'  => '',
	) );		
	
}
add_action( 'widgets_init', 'adventure_lite_widgets_init' );


function adventure_lite_font_url(){
		$font_url = '';		
		
		/* Translators: If there are any character that are not
		* supported by Roboto Condensed, trsnalate this to off, do not
		* translate into your own language.
		*/
		$robotocondensed = _x('on','robotocondensed:on or off','adventure-lite');		
		
		
		/* Translators: If there has any character that are not supported 
		*  by Scada, translate this to off, do not translate
		*  into your own language.
		*/
		$scada = _x('on','Scada:on or off','adventure-lite');	
		$lato = _x('on','Lato:on or off','adventure-lite');	
		
		if('off' !== $robotocondensed ){
			$font_family = array();
			
			if('off' !== $robotocondensed){
				$font_family[] = 'Roboto Condensed:300,400,600,700,800,900';
			}
			
			if('off' !== $lato){
				$font_family[] = 'Lato:100,100i,300,300i,400,400i,700,700i,900,900i';
			}			
						
			$query_args = array(
				'family'	=> urlencode(implode('|',$font_family)),
			);
			
			$font_url = add_query_arg($query_args,'//fonts.googleapis.com/css');
		}
		
	return $font_url;
	}


function adventure_lite_scripts() {
	wp_enqueue_style('adventure-lite-font', adventure_lite_font_url(), array());
	wp_enqueue_style( 'adventure-lite-basic-style', get_stylesheet_uri() );
	wp_enqueue_style( 'adventure-lite-editor-style', get_template_directory_uri()."/adventure-lite-editor-style.css" );
	wp_enqueue_style( 'nivo-slider', get_template_directory_uri()."/css/nivo-slider.css" );
	wp_enqueue_style( 'adventure-lite-responsive', get_template_directory_uri()."/css/adventure-lite-responsive.css" );		
	wp_enqueue_style( 'adventure-lite-base-style', get_template_directory_uri()."/css/adventure-lite-style-base.css" );
	wp_enqueue_script( 'jquery-nivo', get_template_directory_uri() . '/js/jquery.nivo.slider.js', array('jquery') );
	wp_enqueue_script( 'adventure-lite-custom-js', get_template_directory_uri() . '/js/adventure-lite-custom.js' );	
		

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'adventure_lite_scripts' );

define('ADVENTURE_LITE_SKTTHEMES_URL','https://www.sktthemes.org','adventure-lite');
define('ADVENTURE_LITE_SKTTHEMES_PRO_THEME_URL','https://www.sktthemes.org/shop/adventure-wordpress-theme/','adventure-lite');
define('ADVENTURE_LITE_SKTTHEMES_FREE_THEME_URL','https://www.sktthemes.org/shop/free-travel-blog-wordpress-theme/','adventure-lite');
define('ADVENTURE_LITE_SKTTHEMES_THEME_DOC','http://sktthemesdemo.net/documentation/adventure-documentation/','adventure-lite');
define('ADVENTURE_LITE_SKTTHEMES_LIVE_DEMO','http://sktperfectdemo.com/demos/adventure/','adventure-lite');
define('ADVENTURE_LITE_SKTTHEMES_THEMES','https://www.sktthemes.org/themes/','adventure-lite');

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template for about theme.
 */
require get_template_directory() . '/inc/about-themes.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

// get slug by id
function adventure_lite_get_slug_by_id($id) {
	$post_data = get_post($id, ARRAY_A);
	$slug = $post_data['post_name'];
	return $slug; 
}

if ( ! function_exists( 'adventure_lite_the_custom_logo' ) ) :
/**
 * Displays the optional custom logo.
 *
 * Does nothing if the custom logo is not available.
 *
 */
function adventure_lite_the_custom_logo() {
	if ( function_exists( 'the_custom_logo' ) ) {
		the_custom_logo();
	}
}
endif;

require_once get_template_directory() . '/customize-pro/example-1/class-customize.php';


/**
 *
 * Style For About Theme Page
 *
 */
function adventure_lite_admin_about_page_css_enqueue($hook) {
   if ( 'appearance_page_adventure_lite_guide' != $hook ) {
        return;
    }
    wp_enqueue_style( 'adventure-lite-about-page-style', get_template_directory_uri() . '/css/adventure-lite-about-page-style.css' );
}
add_action( 'admin_enqueue_scripts', 'adventure_lite_admin_about_page_css_enqueue' );

// WordPress wp_body_open backward compatibility
if ( ! function_exists( 'wp_body_open' ) ) {
    function wp_body_open() {
        do_action( 'wp_body_open' );
    }
}

/**
 * Include the Plugin_Activation class.
 */

require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';
add_action( 'tgmpa_register', 'adventure_lite_register_required_plugins' );
 
function adventure_lite_register_required_plugins() {
	$plugins = array(
		array(
			'name'      => 'SKT Templates',
			'slug'      => 'skt-templates',
			'required'  => false,
		) 				
	);

	$config = array(
		'id'           => 'tgmpa',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'skt-install-plugins', // Menu slug.
		'parent_slug'  => 'themes.php',            // Parent menu slug.
		'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => false,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.
	);

	tgmpa( $plugins, $config );
}