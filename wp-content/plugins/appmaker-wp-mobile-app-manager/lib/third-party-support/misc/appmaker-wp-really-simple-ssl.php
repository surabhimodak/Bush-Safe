<?php
/**
 * Plugin : Really Simple Ssl
 * Url : https://wordpress.org/plugins/really-simple-ssl/
 *
 * To remove javascript ssl redirect.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'rsssl_javascript_redirect', '__return_empty_string', 2000, 1 );
