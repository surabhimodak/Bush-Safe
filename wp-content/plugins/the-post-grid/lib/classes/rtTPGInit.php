<?php

if (!class_exists('rtTPGInit')):
    class rtTPGInit
    {

        function __construct() {
            add_action('init', array($this, 'init'), 1);
            add_action('admin_menu', array($this, 'admin_menu'));
            add_action('plugins_loaded', array($this, 'the_post_grid_load_text_domain'));
            register_activation_hook(RT_THE_POST_GRID_PLUGIN_ACTIVE_FILE_NAME, array($this, 'activate'));
            register_deactivation_hook(RT_THE_POST_GRID_PLUGIN_ACTIVE_FILE_NAME, array($this, 'deactivate'));
            add_filter('plugin_action_links_' . RT_THE_POST_GRID_PLUGIN_ACTIVE_FILE_NAME,
                array($this, 'rt_post_grid_marketing'));
            add_action('admin_enqueue_scripts', array($this, 'settings_admin_enqueue_scripts'));
        }

        function init() {

            // Create the post grid post type
            $labels = array(
                'name'               => __('The Post Grid', 'the-post-grid'),
                'singular_name'      => __('The Post Grid', 'the-post-grid'),
                'add_new'            => __('Add New Grid', 'the-post-grid'),
                'all_items'          => __('All Grids', 'the-post-grid'),
                'add_new_item'       => __('Add New Post Grid', 'the-post-grid'),
                'edit_item'          => __('Edit Post Grid', 'the-post-grid'),
                'new_item'           => __('New Post Grid', 'the-post-grid'),
                'view_item'          => __('View Post Grid', 'the-post-grid'),
                'search_items'       => __('Search Post Grids', 'the-post-grid'),
                'not_found'          => __('No Post Grids found', 'the-post-grid'),
                'not_found_in_trash' => __('No Post Grids found in Trash', 'the-post-grid'),
            );

            global $rtTPG;

            register_post_type($rtTPG->post_type, array(
                'labels'          => $labels,
                'public'          => false,
                'show_ui'         => true,
                '_builtin'        => false,
                'capability_type' => 'page',
                'hierarchical'    => true,
                'menu_icon'       => $rtTPG->assetsUrl . 'images/rt-tpg-menu.png',
                'rewrite'         => false,
                'query_var'       => $rtTPG->post_type,
                'supports'        => array(
                    'title',
                ),
                'show_in_menu'    => true,
                'menu_position'   => 20,
            ));

            // register acf scripts
            $scripts = array();
            $styles = array();
            $scripts[] = array(
                'handle' => 'rt-isotope-js',
                'src'    => $rtTPG->assetsUrl . "vendor/isotope/isotope.pkgd.min.js",
                'deps'   => array('jquery', 'imagesloaded'),
                'footer' => true
            );
            $scripts[] = array(
                'handle' => 'rt-actual-height-js',
                'src'    => $rtTPG->assetsUrl . "vendor/actual-height/jquery.actual.min.js",
                'deps'   => array('jquery', 'imagesloaded'),
                'footer' => true
            );
            $scripts[] = array(
                'handle' => 'rt-tpg',
                'src'    => $rtTPG->assetsUrl . "js/rttpg.js",
                'deps'   => array('jquery'),
                'footer' => true
            );
            // register acf styles
            $styles['rt-fontawsome'] = $rtTPG->assetsUrl . 'vendor/font-awesome/css/font-awesome.min.css';
            $styles['rt-tpg'] = $rtTPG->assetsUrl . 'css/thepostgrid.css';

            if (is_admin()) {

                $scripts[] = array(
                    'handle' => 'rt-select2-js',
                    'src'    => $rtTPG->assetsUrl . "vendor/select2/select2.min.js",
                    'deps'   => array('jquery'),
                    'footer' => false
                );
                $scripts[] = array(
                    'handle' => 'rt-tpg-admin',
                    'src'    => $rtTPG->assetsUrl . "js/admin.js",
                    'deps'   => array('jquery'),
                    'footer' => true
                );
                $styles['rt-select2-css'] = $rtTPG->assetsUrl . 'vendor/select2/select2.css';
                $styles['rt-select2-bootstrap-css'] = $rtTPG->assetsUrl . 'vendor/select2/select2-bootstrap.css';
                $styles['rt-tpg-admin'] = $rtTPG->assetsUrl . 'css/admin.css';
            }

            $version = (defined('WP_DEBUG') && WP_DEBUG) ? time() : $rtTPG->options['version'];
            foreach ($scripts as $script) {
                wp_register_script($script['handle'], $script['src'], $script['deps'], $version,
                    $script['footer']);
            }


            foreach ($styles as $k => $v) {
                wp_register_style($k, $v, false, $version);
            }
        }

        function admin_menu() {
            global $rtTPG;
            add_submenu_page('edit.php?post_type=' . $rtTPG->post_type, __('Settings', "the-post-grid"), __('Settings', "the-post-grid"),
                'administrator', 'rttpg_settings', array($this, 'rttpg_settings'));
        }

        function rttpg_settings() {
            global $rtTPG;
            $rtTPG->render('settings.settings');
        }

        public function the_post_grid_load_text_domain() {
            load_plugin_textdomain('the-post-grid', false, RT_THE_POST_GRID_LANGUAGE_PATH);
        }

        function activate() {
            $this->insertDefaultData();
        }

        function deactivate() {

        }

        private function insertDefaultData() {
            global $rtTPG;
            update_option($rtTPG->options['installed_version'], $rtTPG->options['version']);
            if (!get_option($rtTPG->options['settings'])) {
                update_option($rtTPG->options['settings'], $rtTPG->defaultSettings);
            }
        }

        function rt_post_grid_marketing($links) {
            $links[] = '<a target="_blank" href="' . esc_url('http://demo.radiustheme.com/wordpress/plugins/the-post-grid/') . '">Demo</a>';
            $links[] = '<a target="_blank" href="' . esc_url('https://www.radiustheme.com/how-to-setup-configure-the-post-grid-free-version-for-wordpress/') . '">Documentation</a>';
            $links[] = '<a target="_blank" style="color: #39b54a;font-weight: 700;" href="' . esc_url('https://www.radiustheme.com/the-post-grid-pro-for-wordpress/') . '">Get Pro</a>';

            return $links;
        }


        function settings_admin_enqueue_scripts() {
            global $pagenow, $typenow, $rtTPG;

            // validate page
            if (!in_array($pagenow, array('edit.php'))) {
                return;
            }
            if ($typenow != $rtTPG->post_type) {
                return;
            }

            wp_enqueue_script(array(
                'jquery',
                'rt-select2-js',
                'rt-tpg-admin',
            ));

            // styles
            wp_enqueue_style(array(
                'rt-select2-css',
                'rt-tpg-admin',
            ));

            $nonce = wp_create_nonce($rtTPG->nonceText());
            wp_localize_script('rt-tpg-admin', 'rttpg',
                array(
                    'nonceID' => $rtTPG->nonceId(),
                    'nonce'   => $nonce,
                    'ajaxurl' => admin_url('admin-ajax.php')
                ));
        }
    }
endif;