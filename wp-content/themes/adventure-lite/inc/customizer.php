<?php
/**
 * Adventure Lite Theme Customizer
 *
 * @package Adventure Lite
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function adventure_lite_customize_register( $wp_customize ) {
	
	//Add a class for titles
    class adventure_lite_Info extends WP_Customize_Control {
        public $type = 'info';
        public $label = '';
        public function render_content() {
        ?>
			<h3><?php echo esc_html( $this->label ); ?></h3>
        <?php
        }
    }
	

	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	
	$wp_customize->add_setting('color_scheme',array(
			'default'	=> '#e64d43',
			'sanitize_callback'	=> 'sanitize_hex_color'
	));
	
	$wp_customize->add_control(
		new WP_Customize_Color_Control($wp_customize,'color_scheme',array(
			'label' => esc_html__('Color Scheme','adventure-lite'),			
			 'description'	=> esc_html__('More color options in PRO Version','adventure-lite'),	
			'section' => 'colors',
			'settings' => 'color_scheme'
		))
	);
	
	
	// Slider Section		
	$wp_customize->add_section( 'slider_section', array(
            'title' => esc_html__('Slider Settings', 'adventure-lite'),
            'priority' => null,
            'description'	=> esc_html__('Featured Image Size Should be ( 1420 X 549 ) More slider settings available in PRO Version','adventure-lite'),		
        )
    );
	
	
	$wp_customize->add_setting('page-setting7',array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback'	=> 'adventure_lite_sanitize_integer'
	));
	
	$wp_customize->add_control('page-setting7',array(
			'type'	=> 'dropdown-pages',
			'label'	=> esc_html__('Select page for slide one:','adventure-lite'),
			'section'	=> 'slider_section'
	));	
	
	$wp_customize->add_setting('page-setting8',array(
			'default' => '0',
			'capability' => 'edit_theme_options',			
			'sanitize_callback'	=> 'adventure_lite_sanitize_integer'
	));
	
	$wp_customize->add_control('page-setting8',array(
			'type'	=> 'dropdown-pages',
			'label'	=> esc_html__('Select page for slide two:','adventure-lite'),
			'section'	=> 'slider_section'
	));	
	
	$wp_customize->add_setting('page-setting9',array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback'	=> 'adventure_lite_sanitize_integer'
	));
	
	$wp_customize->add_control('page-setting9',array(
			'type'	=> 'dropdown-pages',
			'label'	=> esc_html__('Select page for slide three:','adventure-lite'),
			'section'	=> 'slider_section'
	));	
	
	//Slider hide
	$wp_customize->add_setting('hide_slides',array(
			'sanitize_callback' => 'adventure_lite_sanitize_checkbox',
			'default' => true,
	));	 

	$wp_customize->add_control( 'hide_slides', array(
    	   'section'   => 'slider_section',    	 
		   'label'	=> esc_html__('Uncheck To Show Slider','adventure-lite'),
    	   'type'      => 'checkbox'
     )); // Slider Section	
	 
	 
	 // Section One 6 Info Box
	 
	$wp_customize->add_section('section_one', array(
		'title'	=> esc_html__('Home Section 1 Six Info Boxes','adventure-lite'),
		'description'	=> esc_html__('Select Pages from the dropdown for home 6 info boxes','adventure-lite'),
		'priority'	=> null
	));	
	
	
	$wp_customize->add_setting('pageinfobox-column12',	array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback' => 'adventure_lite_sanitize_integer',
		));
 
	$wp_customize->add_control(	'pageinfobox-column12',array('type' => 'dropdown-pages',
			'section' => 'section_one',
	));	
	
	
	$wp_customize->add_setting('pageinfobox-column13',	array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback' => 'adventure_lite_sanitize_integer',
		));
 
	$wp_customize->add_control(	'pageinfobox-column13',array('type' => 'dropdown-pages',
			'section' => 'section_one',
	));
	
	$wp_customize->add_setting('pageinfobox-column14',	array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback' => 'adventure_lite_sanitize_integer',
		));
 
	$wp_customize->add_control(	'pageinfobox-column14',array('type' => 'dropdown-pages',
			'section' => 'section_one',
	));
	
	$wp_customize->add_setting('pageinfobox-column15',	array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback' => 'adventure_lite_sanitize_integer',
		));
 
	$wp_customize->add_control(	'pageinfobox-column15',array('type' => 'dropdown-pages',
			'section' => 'section_one',
	));	
	
	$wp_customize->add_setting('pageinfobox-column16',	array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback' => 'adventure_lite_sanitize_integer',
		));
 
	$wp_customize->add_control(	'pageinfobox-column16',array('type' => 'dropdown-pages',
			'section' => 'section_one',
	));	
	
	
	$wp_customize->add_setting('pageinfobox-column17',	array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback' => 'adventure_lite_sanitize_integer',
		));
 
	$wp_customize->add_control(	'pageinfobox-column17',array('type' => 'dropdown-pages',
			'section' => 'section_one',
	));	
	
	//Hide Info Boxes
	$wp_customize->add_setting('hide_infobox',array(
			'sanitize_callback' => 'adventure_lite_sanitize_checkbox',
			'default' => true,
	));	 

	$wp_customize->add_control( 'hide_infobox', array(
    	   'section'   => 'section_one',    	 
		   'label'	=> esc_html__('Uncheck To Show This Section','adventure-lite'),
    	   'type'      => 'checkbox'
     )); 
	 
	 // Section One 6 Info Box
 
	// Home Three Boxes Section 	
	$wp_customize->add_section('section_second', array(
		'title'	=> esc_html__('Home Section 2 Three Boxes','adventure-lite'),
		'description'	=> esc_html__('Select Pages from the dropdown for home three boxes section','adventure-lite'),
		'priority'	=> null
	));	
	
	$wp_customize->add_setting('section2_title',array(
			'default'	=> null,
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('section2_title',array(
			'label'	=> esc_html__('Section Title','adventure-lite'),
			'section'	=> 'section_second',
			'setting'	=> 'section2_title'
	));	
	
	$wp_customize->add_setting('page-column1',	array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback' => 'adventure_lite_sanitize_integer',
		));
 
	$wp_customize->add_control(	'page-column1',array('type' => 'dropdown-pages',
			'section' => 'section_second',
	));	
	
	
	$wp_customize->add_setting('page-column2',	array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback' => 'adventure_lite_sanitize_integer',
		));
 
	$wp_customize->add_control(	'page-column2',array('type' => 'dropdown-pages',
			'section' => 'section_second',
	));
	
	$wp_customize->add_setting('page-column3',	array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback' => 'adventure_lite_sanitize_integer',
		));
 
	$wp_customize->add_control(	'page-column3',array('type' => 'dropdown-pages',
			'section' => 'section_second',
	));
	
	//Hide Page Boxes Column Section
	$wp_customize->add_setting('hide_pagethreeboxes',array(
			'sanitize_callback' => 'adventure_lite_sanitize_checkbox',
			'default' => true,
	));	 

	$wp_customize->add_control( 'hide_pagethreeboxes', array(
    	   'section'   => 'section_second',    	 
		   'label'	=> esc_html__('Uncheck To Show This Section','adventure-lite'),
    	   'type'      => 'checkbox'
     )); // Hide Page Boxes Column Section
	
	$wp_customize->add_section('social_sec',array(
			'title'	=> esc_html__('Social Settings','adventure-lite'),				
			'description'	=> esc_html__('More social icon available in PRO Version','adventure-lite'),		
			'priority'		=> null
	));
	
	
	$wp_customize->add_setting('fb_link',array(
			'default'	=> null,
			'sanitize_callback'	=> 'esc_url_raw'	
	));
	
	$wp_customize->add_control('fb_link',array(
			'label'	=> esc_html__('Add facebook link here','adventure-lite'),
			'section'	=> 'social_sec',
			'setting'	=> 'fb_link'
	));	
	$wp_customize->add_setting('twitt_link',array(
			'default'	=> null,
			'sanitize_callback'	=> 'esc_url_raw'
	));
	
	$wp_customize->add_control('twitt_link',array(
			'label'	=> esc_html__('Add twitter link here','adventure-lite'),
			'section'	=> 'social_sec',
			'setting'	=> 'twitt_link'
	));
	$wp_customize->add_setting('gplus_link',array(
			'default'	=> null,
			'sanitize_callback'	=> 'esc_url_raw'
	));
	$wp_customize->add_control('gplus_link',array(
			'label'	=> esc_html__('Add google plus link here','adventure-lite'),
			'section'	=> 'social_sec',
			'setting'	=> 'gplus_link'
	));
	$wp_customize->add_setting('linked_link',array(
			'default'	=> null,
			'sanitize_callback'	=> 'esc_url_raw'
	));
	$wp_customize->add_control('linked_link',array(
			'label'	=> esc_html__('Add linkedin link here','adventure-lite'),
			'section'	=> 'social_sec',
			'setting'	=> 'linked_link'
	));
	
	$wp_customize->add_section('footer_area',array(
			'title'	=> esc_html__('Footer Area','adventure-lite'),
			'priority'	=> null,
	));
	$wp_customize->add_setting('adventure_lite_options[credit-info]', array(
            'type' => 'info_control',
            'capability' => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field'
        )
    );
    $wp_customize->add_control( new adventure_lite_Info( $wp_customize, 'cred_section', array(
        'section' => 'footer_area',
        'settings' => 'adventure_lite_options[credit-info]'
        ) )
    );
	
	$wp_customize->add_setting('newsfeed_title',array(
			'default'	=> null,
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('newsfeed_title',array(
			'label'	=> esc_html__('Add title for latest news feed','adventure-lite'),
			'section'	=> 'footer_area',
			'setting'	=> 'newsfeed_title'
	));	
	
	$wp_customize->add_setting('about_title',array(
			'default'	=> null,
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('about_title',array(
			'label'	=> esc_html__('Add title for about us','adventure-lite'),
			'section'	=> 'footer_area',
			'setting'	=> 'about_title'
	));	
		
	$wp_customize->add_setting( 'about_description', array(
			'default'	=> null,				
			'sanitize_callback' => 'esc_textarea',
	) );

	$wp_customize->add_control( 'about_description', array(
			'type' => 'textarea',
			'label' => esc_html__( 'About Description', 'adventure-lite' ),   
			'section' => 'footer_area',   
			'setting'	=> 'about_description',
	) );
	
	$wp_customize->add_setting('contact_title',array(
			'default'	=> null,
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	
	$wp_customize->add_control('contact_title',array(
			'label'	=> esc_html__('Add title for footer contact info','adventure-lite'),
			'section'	=> 'footer_area',
			'setting'	=> 'contact_title'
	));
	
	$wp_customize->add_section('contact_sec',array(
			'title'	=> esc_html__('Contact Details','adventure-lite'),
			'description'	=> esc_html__('Add you contact details here','adventure-lite'),
			'priority'	=> null
	));	
	
	
	$wp_customize->add_setting('contact_add',array(
			'default'	=> null,
			'sanitize_callback'	=> 'esc_textarea',
	));
	
	$wp_customize->add_control(	'contact_add', array(
				'type' => 'textarea',
				'label'	=> esc_html__('Add contact address here','adventure-lite'),
				'section'	=> 'contact_sec',
				'setting'	=> 'contact_add'
	));
	$wp_customize->add_setting('contact_no',array(
			'default'	=> null,
			'sanitize_callback'	=> 'sanitize_text_field'
	));
	$wp_customize->add_control('contact_no',array(
			'label'	=> esc_html__('Add contact number here.','adventure-lite'),
			'section'	=> 'contact_sec',
			'setting'	=> 'contact_no'
	));
	$wp_customize->add_setting('contact_mail',array(
			'default'	=> null,
			'sanitize_callback'	=> 'sanitize_email'
	));
	
	$wp_customize->add_control('contact_mail',array(
			'label'	=> esc_html__('Add you email here','adventure-lite'),
			'section'	=> 'contact_sec',
			'setting'	=> 'contact_mail'
	));
	
    $wp_customize->add_setting('adventure_lite_options[layout-info]', array(
            'type' => 'info_control',
            'capability' => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field'
        )
    );
    $wp_customize->add_control( new adventure_lite_Info( $wp_customize, 'layout_section', array(
        'section' => 'theme_layout_sec',
        'settings' => 'adventure_lite_options[layout-info]',
        'priority' => null
        ) )
    );
	  
    $wp_customize->add_setting('adventure_lite_options[font-info]', array(
            'type' => 'info_control',
            'capability' => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field'
        )
    );
    $wp_customize->add_control( new adventure_lite_Info( $wp_customize, 'font_section', array(
        'section' => 'theme_font_sec',
        'settings' => 'adventure_lite_options[font-info]',
        'priority' => null
        ) )
    );	
	  
    $wp_customize->add_setting('adventure_lite_options[info]', array(
            'type' => 'info_control',
            'capability' => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field'
        )
    );
    $wp_customize->add_control( new adventure_lite_Info( $wp_customize, 'doc_section', array(
        'section' => 'theme_doc_sec',
        'settings' => 'adventure_lite_options[info]',
        'priority' => 10
        ) )
    );		
}
add_action( 'customize_register', 'adventure_lite_customize_register' );

//Integer
function adventure_lite_sanitize_integer( $input ) {
    if( is_numeric( $input ) ) {
        return intval( $input );
    }
}

function adventure_lite_sanitize_checkbox( $checked ) {
	// Boolean check.
	return ( ( isset( $checked ) && true == $checked ) ? true : false );
}

//setting inline css.
function adventure_lite_custom_css() {
    wp_enqueue_style(
        'adventure-lite-custom-style',
        get_template_directory_uri() . '/css/adventure_lite_custom_script.css'
    );
        $color = get_theme_mod( 'color_scheme' ); //E.g. #e64d43
		$header_text_color = get_header_textcolor();
		
        $custom_css = "
                	#sidebar ul li a:hover,
					.threebox:hover h3,
					.cols-3 ul li a:hover, .cols-3 ul li.current_page_item a,					
					.phone-no strong,					
					.left a:hover,
					.blog_lists h4 a:hover,
					.recent-post h6 a:hover,
					.postmeta a:hover,
					.recent-post .morebtn:hover, .blocksbox a, .recent-post a
					{ 
						 color: {$color} !important;
					}
					.logo, .sitenav ul li a:hover, .sitenav ul li.current_page_item a, .sitenav ul li.menu-item-has-children.hover, .sitenav ul li.current-menu-parent a.parent, .slide_info .slide_more, .squarebox:hover, .pagination .nav-links span.current, .pagination .nav-links a:hover,
					#commentform input#submit:hover,
					.slide_info .slide_more:hover,							
					.nivo-controlNav a.active,				
					h3.widget-title,				
					.wpcf7 input[type='submit'],					
					.social-icons a:hover,
					a.ReadMore,
					input.search-submit, .blocksbox a .block-count
					{ 
					   background-color: {$color} !important;
					}
					.head-info-area{
						border-top-color: {$color} !important;
					}
					#menubar,
					h2.section-title::after,
					h2.section-title, .blockthumb
					{ 
					   border-color:{$color};
					}
					
					.logo h2, .logo p, .phoneno, .emltp a{
					   color: #$header_text_color;
					}										
				";
        wp_add_inline_style( 'adventure-lite-custom-style', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'adventure_lite_custom_css' ); 
          
/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function adventure_lite_customize_preview_js() {
	wp_enqueue_script( 'adventure_lite_customizer', get_template_directory_uri() . '/js/customize-preview.js', array( 'customize-preview' ), '20130508', true );
}
add_action( 'customize_preview_init', 'adventure_lite_customize_preview_js' );