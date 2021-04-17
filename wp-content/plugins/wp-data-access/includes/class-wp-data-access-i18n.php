<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package plugin\includes
 */

/**
 * Class WP_Data_Access_i18n
 *
 * Loads internationalization files
 *
 * @author  Peter Schulz
 * @since   1.0.0
 */
class WP_Data_Access_i18n {

	/**
	 * Load plugin internationalization files
	 *
	 * @since   1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'wp-data-access',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

}
