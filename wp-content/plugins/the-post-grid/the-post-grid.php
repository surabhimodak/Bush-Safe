<?php
/**
 * Plugin Name: The Post Grid
 * Plugin URI: http://demo.radiustheme.com/wordpress/plugins/the-post-grid/
 * Description: Fast & Easy way to display WordPress post in Grid, List & Isotope view ( filter by category, tag, author..)  without a single line of coding.
 * Author: RadiusTheme
 * Version: 2.3.6.3
 * Text Domain: the-post-grid
 * Domain Path: /languages
 * Author URI: https://radiustheme.com/
 */
if (!defined('ABSPATH')) {
    exit;
}

$plugin_data = get_file_data(__FILE__, ['Version' => 'Version'], false);
define('RT_THE_POST_GRID_VERSION', $plugin_data['Version']);
define('RT_THE_POST_GRID_PLUGIN_PATH', dirname(__FILE__));
define('RT_THE_POST_GRID_PLUGIN_ACTIVE_FILE_NAME', plugin_basename(__FILE__));
define('RT_THE_POST_GRID_PLUGIN_URL', plugins_url('', __FILE__));
define('RT_THE_POST_GRID_LANGUAGE_PATH', dirname(plugin_basename(__FILE__)) . '/languages');

require('lib/init.php');
