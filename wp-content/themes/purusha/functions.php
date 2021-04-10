<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

add_action( 'after_setup_theme', 'lalita_background_setup' );
/**
 * Overwrite parent theme background defaults and registers support for WordPress features.
 *
 */
function lalita_background_setup() {
	add_theme_support( "custom-background",
		array(
			'default-color' 		 => '222222',
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

/**
 * Overwrite theme URL
 *
 */
function lalita_theme_uri_link() {
	return 'https://wpkoi.com/purusha-wpkoi-wordpress-theme/';
}

/**
 * Overwrite parent theme's blog header function
 *
 */
add_action( 'lalita_after_header', 'lalita_blog_header_image', 11 );
function lalita_blog_header_image() {

	if ( ( is_front_page() && is_home() ) || ( is_home() ) ) { 
		$blog_header_image 			=  lalita_get_setting( 'blog_header_image' ); 
		$blog_header_title 			=  lalita_get_setting( 'blog_header_title' ); 
		$blog_header_text 			=  lalita_get_setting( 'blog_header_text' ); 
		$blog_header_button_text 	=  lalita_get_setting( 'blog_header_button_text' ); 
		$blog_header_button_url 	=  lalita_get_setting( 'blog_header_button_url' ); 
		if ( $blog_header_image != '' ) { ?>
		<div class="page-header-image grid-parent page-header-blog">
        	<div class="page-header-blog-inner">
                <div class="page-header-blog-content-h grid-container">
                    <div class="page-header-blog-content">
                    <?php if ( ( $blog_header_title != '' ) || ( $blog_header_text != '' ) ) { ?>
                        <div class="page-header-blog-text">
                            <?php if ( $blog_header_title != '' ) { ?>
                            <h2><?php echo wp_kses_post( $blog_header_title ); ?></h2>
                            <div class="clearfix"></div>
                            <?php } ?>
                            <?php if ( $blog_header_title != '' ) { ?>
                            <p><?php echo wp_kses_post( $blog_header_text ); ?></p>
                            <div class="clearfix"></div>
                            <?php } ?>
                        </div>
                        <div class="page-header-blog-button">
                            <?php if ( $blog_header_button_text != '' ) { ?>
                            <a class="read-more button" href="<?php echo esc_url( $blog_header_button_url ); ?>"><?php echo esc_html( $blog_header_button_text ); ?></a>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    </div>
                </div>
                <div class="page-header-blog-inner-img"><img src="<?php echo esc_url($blog_header_image); ?>" /></div>
            </div>
		</div>
		<?php
		}
	}
}

if ( ! function_exists( 'purusha_remove_parent_dynamic_css' ) ) {
	add_action( 'init', 'purusha_remove_parent_dynamic_css' );
	/**
	 * The dynamic styles of the parent theme added inline to the parent stylesheet.
	 * For the customizer functions it is better to enqueue after the child theme stylesheet.
	 */
	function purusha_remove_parent_dynamic_css() {
		remove_action( 'wp_enqueue_scripts', 'lalita_enqueue_dynamic_css', 50 );
	}
}

if ( ! function_exists( 'purusha_enqueue_parent_dynamic_css' ) ) {
	add_action( 'wp_enqueue_scripts', 'purusha_enqueue_parent_dynamic_css', 50 );
	/**
	 * Enqueue this CSS after the child stylesheet, not after the parent stylesheet.
	 *
	 */
	function purusha_enqueue_parent_dynamic_css() {
		$css = lalita_base_css() . lalita_font_css() . lalita_advanced_css() . lalita_spacing_css() . lalita_no_cache_dynamic_css();

		// escaped secure before in parent theme
		wp_add_inline_style( 'lalita-child', $css );
	}
}

// Extra cutomizer functions
if ( ! function_exists( 'purusha_customize_register' ) ) {
	add_action( 'customize_register', 'purusha_customize_register' );
	function purusha_customize_register( $wp_customize ) {
		
		// Blog image effect
		$wp_customize->add_setting(
			'purusha_settings[img_effect]',
			array(
				'default' => 'enable',
				'type' => 'option',
				'sanitize_callback' => 'purusha_sanitize_choices'
			)
		);

		$wp_customize->add_control(
			'purusha_settings[img_effect]',
			array(
				'type' => 'select',
				'label' => __( 'Blog image effect', 'purusha' ),
				'choices' => array(
					'enable' => __( 'Enable', 'purusha' ),
					'disable' => __( 'Disable', 'purusha' )
				),
				'settings' => 'purusha_settings[img_effect]',
				'section' => 'lalita_blog_section',
				'priority' => 29
			)
		);
		
		// Preloader
		$wp_customize->add_setting(
			'purusha_settings[purusha_preloader]',
			array(
				'default' => 'enable',
				'type' => 'option',
				'sanitize_callback' => 'purusha_sanitize_choices'
			)
		);

		$wp_customize->add_control(
			'purusha_settings[purusha_preloader]',
			array(
				'type' => 'select',
				'label' => __( 'Preloader', 'purusha' ),
				'choices' => array(
					'enable' => __( 'Enable', 'purusha' ),
					'disable' => __( 'Disable', 'purusha' )
				),
				'settings' => 'purusha_settings[purusha_preloader]',
				'section' => 'title_tagline',
				'priority' => 2
			)
		);
		
		// Logo pulse effect
		$wp_customize->add_setting(
			'purusha_settings[logo_pulse]',
			array(
				'default' => 'enable',
				'type' => 'option',
				'sanitize_callback' => 'purusha_sanitize_choices'
			)
		);

		$wp_customize->add_control(
			'purusha_settings[logo_pulse]',
			array(
				'type' => 'select',
				'label' => __( 'Purusha logo pulse', 'purusha' ),
				'choices' => array(
					'enable' => __( 'Enable', 'purusha' ),
					'disable' => __( 'Disable', 'purusha' )
				),
				'settings' => 'purusha_settings[logo_pulse]',
				'section' => 'title_tagline',
				'priority' => 2
			)
		);
		
		// Nicescroll
		$wp_customize->add_setting(
			'purusha_settings[nicescroll]',
			array(
				'default' => 'enable',
				'type' => 'option',
				'sanitize_callback' => 'purusha_sanitize_choices'
			)
		);

		$wp_customize->add_control(
			'purusha_settings[nicescroll]',
			array(
				'type' => 'select',
				'label' => __( 'Scrollbar style', 'purusha' ),
				'choices' => array(
					'enable' => __( 'Enable', 'purusha' ),
					'disable' => __( 'Disable', 'purusha' )
				),
				'settings' => 'purusha_settings[nicescroll]',
				'section' => 'lalita_layout_container',
				'priority' => 20
			)
		);
		
		// Cursor
		$wp_customize->add_setting(
			'purusha_settings[cursor]',
			array(
				'default' => 'enable',
				'type' => 'option',
				'sanitize_callback' => 'purusha_sanitize_choices'
			)
		);

		$wp_customize->add_control(
			'purusha_settings[cursor]',
			array(
				'type' => 'select',
				'label' => __( 'Cursor image', 'purusha' ),
				'choices' => array(
					'enable' => __( 'Enable', 'purusha' ),
					'disable' => __( 'Disable', 'purusha' )
				),
				'settings' => 'purusha_settings[cursor]',
				'section' => 'lalita_layout_container',
				'priority' => 20
			)
		);
		
	}
}

if ( ! function_exists( 'purusha_sanitize_choices' ) ) {
	/**
	 * Sanitize choices.
	 *
	 */
	function purusha_sanitize_choices( $input, $setting ) {
		// Ensure input is a slug
		$input = sanitize_key( $input );

		// Get list of choices from the control
		// associated with the setting
		$choices = $setting->manager->get_control( $setting->id )->choices;

		// If the input is a valid key, return it;
		// otherwise, return the default
		return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
	}
}

if ( ! function_exists( 'purusha_body_classes' ) ) {
	add_filter( 'body_class', 'purusha_body_classes' );
	/**
	 * Adds custom classes to the array of body classes.
	 *
	 */
	function purusha_body_classes( $classes ) {
		// Get Customizer settings
		$purusha_settings = get_option( 'purusha_settings' );
		
		$img_effect  = 'enable';
		$preloader   = 'enable';
		$logo_pulse  = 'enable';
		$nicescroll  = 'enable';
		$cursor		 = 'enable';
		if ( isset( $purusha_settings['img_effect'] ) ) {
			$img_effect = $purusha_settings['img_effect'];
		}
		if ( isset( $purusha_settings['purusha_preloader'] ) ) {
			$preloader = $purusha_settings['purusha_preloader'];
		}
		if ( isset( $purusha_settings['logo_pulse'] ) ) {
			$logo_pulse = $purusha_settings['logo_pulse'];
		}
		if ( isset( $purusha_settings['nicescroll'] ) ) {
			$nicescroll = $purusha_settings['nicescroll'];
		}
		if ( isset( $purusha_settings['cursor'] ) ) {
			$cursor = $purusha_settings['cursor'];
		}
		
		// Blog image function
		if ( $img_effect != 'disable' ) {
			$classes[] = 'purusha-img-effect';
		}
		
		// Preloader
		if ( $preloader != 'disable' ) {
			$classes[] = 'purusha-body-preloader';
		}
		
		// Logo pulse function
		if ( $logo_pulse != 'disable' ) {
			$classes[] = 'purusha-logo-pulse';
		}
		
		// Scrollbar style function
		if ( $nicescroll != 'disable' ) {
			$classes[] = 'purusha-scrollbar-style';
		}
		
		// Scrollbar style function
		if ( $cursor != 'disable' ) {
			$classes[] = 'purusha-cursor-style';
		}
		
		return $classes;
	}
}

if ( ! function_exists( 'purusha_scripts' ) ) {
	add_action( 'wp_enqueue_scripts', 'purusha_scripts' );
	/**
	 * Enqueue script
	 */
	function purusha_scripts() {
		
		$purusha_settings = get_option( 'purusha_settings' );
		$cursor		 = 'enable';
		$preloader   = 'enable';
		if ( isset( $purusha_settings['cursor'] ) ) {
			$cursor = $purusha_settings['cursor'];
		}
		if ( isset( $purusha_settings['purusha_preloader'] ) ) {
			$preloader = $purusha_settings['purusha_preloader'];
		}

		wp_enqueue_script( 'purusha-menu-control', esc_url( get_stylesheet_directory_uri() ) . "/js/menu-control.js", array( 'jquery'), LALITA_VERSION, true );
		
		if ( $preloader != 'disable' ) {	
			wp_enqueue_script( 'purusha-preloader', esc_url( get_stylesheet_directory_uri() ) . "/js/purusha-preloader.js", array( 'jquery'), LALITA_VERSION, true );
		}
		
		if ( $cursor != 'disable' ) {
			wp_enqueue_style( 'purusha-magic-mouse', esc_url( get_stylesheet_directory_uri() ) . "/inc/magic-mouse/magic-mouse.min.css", false, LALITA_VERSION, 'all' );
			wp_enqueue_script( 'purusha-magic-mouse', esc_url( get_stylesheet_directory_uri() ) . "/inc/magic-mouse/magic-mouse.min.js", array( 'jquery'), LALITA_VERSION, true );
		}
	}
}

if ( ! function_exists( 'purusha_preloader' ) ) {
	add_action( 'lalita_before_header', 'purusha_preloader' );
	function purusha_preloader() {
		$purusha_settings = get_option( 'purusha_settings' );
		$preloader   = 'enable';
		if ( isset( $purusha_settings['purusha_preloader'] ) ) {
			$preloader = $purusha_settings['purusha_preloader'];
		}
		
		if ( $preloader != 'disable' ) {
		?>
		<div class="purusha-preloader">
        	<h3><?php esc_html_e( 'Loading...', 'purusha' ); ?></h3>
        </div>
        <?php
		}
	}
}