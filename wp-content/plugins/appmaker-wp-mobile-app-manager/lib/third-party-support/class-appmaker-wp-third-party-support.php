<?php
/**
 * 3rd party plugin supports
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 3rd party plugin supports
 */
class APPMAKER_WP_Third_Party_Support {
	/**
	 * Load all plugin supports
	 */
	static function init() {
		if ( class_exists( 'REALLY_SIMPLE_SSL' ) ) {
			require_once 'misc/appmaker-wp-really-simple-ssl.php';
		}
	}
}
