<?php

if ( ! class_exists( 'rtTPGFrontEnd' ) ):

	class rtTPGFrontEnd {
		function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'rt_tpg_enqueue_styles' ) );
		}

		function rt_tpg_enqueue_styles() {
			wp_enqueue_style( 'rt-tpg' );
			global $rtTPG;
			$settings = get_option( $rtTPG->options['settings'] );
			$css      = isset( $settings['custom_css'] ) ? trim( $settings['custom_css'] ) : null;
			if ( $css ) {
				wp_add_inline_style( 'rt-tpg', $css );
			}
			$scriptBefore = isset( $settings['script_before_item_load'] ) ? stripslashes( $settings['script_before_item_load'] ) : null;
			$scriptAfter  = isset( $settings['script_after_item_load'] ) ? stripslashes( $settings['script_after_item_load'] ) : null;
			$scriptLoaded = isset( $settings['custom_script_loaded'] ) ? stripslashes( $settings['custom_script_loaded'] ) : null;
			$script       = "(function($){
				$('.rt-tpg-container').on('tpg_item_before_load', function(){{$scriptBefore}});
				$('.rt-tpg-container').on('tpg_item_after_load', function(){{$scriptAfter}});
				$('.rt-tpg-container').on('tpg_loaded', function(){{$scriptLoaded}});
			})(jQuery);";
			wp_add_inline_script( 'rt-tpg', $script );
		}
	}
endif;