<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'rtTPGElementor' ) ):

	class rtTPGElementor {
		function __construct() {
			if ( did_action( 'elementor/loaded' ) ) {
				add_action( 'elementor/widgets/widgets_registered', array( $this, 'init' ) );
			}
		}

		function init() {
			global $rtTPG;
			require_once( $rtTPG->libPath . '/vendor/RtElementorWidget.php' );

			// Register widget
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new RtElementorWidget() );
		}
	}

endif;