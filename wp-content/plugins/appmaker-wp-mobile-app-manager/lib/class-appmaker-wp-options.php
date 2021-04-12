<?php

class APPMAKER_WP_Options
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action('admin_menu', array( $this, 'add_plugin_page' ));
        add_action('admin_init', array( $this, 'page_init' ));
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Appmaker WP Mobile App Manager Settings',
            'Appmaker WP Settings',
            'manage_options',
            'appmaker-wp-admin',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option('appmaker_wp_settings');
        ?>
        <div class="wrap">
            <h2>Appmaker Settings</h2>
             <?php include_once dirname(__FILE__) . '/class-appmaker-admin-page.php'; ?>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        add_action('admin_head-settings_page_appmaker-wp-admin', array($this,'admin_hook_css'));
        register_setting(
            'appmaker_key_options', // Option group
            'appmaker_wp_settings', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_api', // ID
            'API Credentials', // Title
            array( $this, 'print_section_info' ), // Callback
            'appmaker-setting-admin' // Page
        );

        add_settings_field(
			'project_id',
			'Project ID',
			array( $this, 'project_id_callback' ),
			'appmaker-setting-admin',
			'setting_section_api'
		);

        add_settings_field(
            'api_key', // ID
            'API Key', // Title
            array( $this, 'api_key_callback' ), // Callback
            'appmaker-setting-admin', // Page
            'setting_section_api' // Section
        );

        add_settings_field(
            'api_secret',
            'API Secret',
            array( $this, 'api_secret_callback' ),
            'appmaker-setting-admin',
            'setting_section_api'
        );
    }

    public function admin_hook_css()
    {
        ?>
        <style>
            html{
                margin: 0;
                padding: 0;
                border: 0;
                box-sizing:border-box;
            }
            ol, ul {
                list-style: none;
                padding-inline-start: 0;
            }
            body {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 14px;
                color: rgb(0, 0, 0);
                min-width: 320px;
                background-color: #f5f5f5;
            }
            input,textarea,select{
                font-family: Arial, Helvetica, sans-serif;
            }
            a{
                color: rgb(14, 14, 14);
                text-decoration: none;
            }
            a:hover,.submit:hover{
                filter: alpha(opacity = 85);
                -moz-opacity: 0.85;
                -khtml-opacity: 0.85;
                opacity: 0.85;
            }
            p{
                line-height: 1.5rem;
            }
            label{
                margin-bottom: 5px;
            }
            *,*:before,*:after{box-sizing:inherit;}
            .row {
                display: -webkit-flex;
                display: flex;
            }
            .column.main{
                flex: 3;
            }
            .column.side{
                flex: 1;
                margin-left: 1.6rem;
            }
            /* custom css */
            .logo{
                margin: 20px 0;
            }

            .navbar, .main-box, .testimonials{
                background-color: #fff;
                border: #E2E2E2 1px solid;
            }
            .main-box{
                margin-top: 1rem;
            }
            .box-header{
                border-bottom: #E2E2E2 1px solid;
                padding: 0 20px;
            }
            .box-body{
                padding: 0 20px;
            }
            .casestudy .column{
                padding: 20px 20px 20px 0;
            }
            .navbar ul li{
                display: inline-block;
                font-size: 1.1rem;
                padding: 0 20px;
            }
            .current{
                border-bottom: 2px solid #000;
                padding: 13px 0;
            }
            form{
                padding: 2rem 0;
            }
            form > *{
                display: block;
            }
            form input{
                margin-bottom: 20px;
                padding: 5px 3px;
                font-size: 0.9rem;
            }
            .support a{
                color: #0277BD;
                display: block;
                margin-bottom: 10px;
            }
            a.button-custom{
                text-align: center;
                width: 100%;
                background-color: #0277BD;
                padding: 12px 50px;
                display: block;
                color: #fff;
                margin-top: 1rem;
                font-size: 1.05rem;
            }
            input[type="submit"]{
                background-color: #0277BD;
                padding: 8px 50px;
                border: none;
                color: #fff;
                font-size: 1.05rem;
                cursor: pointer;
            }
            .testimonials{
                margin-top: 1rem;
            }
            .testimonials .thumb{
                width: 200px;
                height: 200px;

            }
            .testimonials .testimonial-content{
                margin: auto 12px;
            }
            .testimonials .testimonial-content h2{
                margin-bottom: 0;
            }
            .social-media li{
                display: inline-block;
                margin-right: 10px;
            }
            .infograph-container{
                position: relative;
            }
            .infograph-container::before{
                position: absolute;
                content: '';
                width: 67%;
                height: 2px;
                background-color: #fff;
                top: 34px;
                left: 140px;
            }
            .infograph{
                padding: 90px 25px 15px 25px;
                text-align: center;
                position: relative;
            }
            .infograph h5{
                color: #fff;
                background-color: #0277BD;
                padding: 10px 15px;
                display: block;
                border-radius: 50%;
                position: absolute;
                top: 0;
                right: 45%;
            }
            @media (max-width: 600px) {
                .row {
                    -webkit-flex-direction: column;
                    flex-direction: column;
                }
            }
        </style>
        <?php
    }
    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     *
     * @return array
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if ( isset( $input['project_id'] ) ) {
			$new_input['project_id'] = sanitize_text_field( $input['project_id'] );
			if ( ! is_numeric( $new_input['project_id'] ) ) {
				$new_input['project_id'] = '';
			}
        }
        
        if (isset($input['api_key']) ) {
            $new_input['api_key'] = sanitize_text_field($input['api_key']);
        }

        if (isset($input['api_secret']) ) {
            $new_input['api_secret'] = sanitize_text_field($input['api_secret']);
        }

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your Appmaker API settings below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function api_key_callback()
    {
        printf(
            '<input type="text" id="api_key" name="appmaker_wp_settings[api_key]" value="%s" />',
            isset($this->options['api_key']) ? esc_attr($this->options['api_key']) : ''
        );
    }

    /**
	 * Get the settings option array and print one of its values
	 */
	public function project_id_callback() {
		printf(
			'<input type="text" id="project_id" name="appmaker_wp_settings[project_id]" value="%s" />',
			isset( $this->options['project_id'] ) ? esc_attr( $this->options['project_id'] ) : ''
		);
	}

    /**
     * Get the settings option array and print one of its values
     */
    public function api_secret_callback()
    {
        printf(
            '<input type="text" id="api_secret" name="appmaker_wp_settings[api_secret]" value="%s" />',
            isset($this->options['api_secret']) ? esc_attr($this->options['api_secret']) : ''
        );
    }
}