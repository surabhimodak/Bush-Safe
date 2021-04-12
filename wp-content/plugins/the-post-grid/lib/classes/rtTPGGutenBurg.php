<?php
if (!defined('WPINC')) {
    die;
}

if (!class_exists('rtTPGGutenBurg')):

    class rtTPGGutenBurg
    {
        function __construct() {
            add_action('enqueue_block_assets', array($this, 'block_assets'));
            add_action('enqueue_block_editor_assets', array($this, 'block_editor_assets'));
            if(function_exists('register_block_type')) {
                register_block_type('rttpg/post-grid', array(
                    'render_callback' => array($this,'render_shortcode'),
                ));
            }
        }

        static function render_shortcode( $atts ){
            if(!empty($atts['gridId']) && $id = absint($atts['gridId'])){
                return do_shortcode( '[the-post-grid id="' . $id . '"]' );
            }
        }


        function block_assets() {
            wp_enqueue_style('wp-blocks');
        }

        function block_editor_assets() {
            global $rtTPG;
            // Scripts.
            wp_enqueue_script(
                'rt-tpg-cgb-block-js',
                $rtTPG->assetsUrl . "js/post-grid-blocks.js",
                array('wp-blocks', 'wp-i18n', 'wp-element'),
                (defined('WP_DEBUG') && WP_DEBUG) ? time() : RT_THE_POST_GRID_VERSION,
                true
            );
            wp_localize_script('rt-tpg-cgb-block-js', 'rttpgGB', array(
                'short_codes' => $rtTPG->getAllTPGShortCodeList(),
                'icon' => $rtTPG->assetsUrl . 'images/rt-tpg-menu.png',
            ));
            wp_enqueue_style('wp-edit-blocks');
        }
    }

endif;