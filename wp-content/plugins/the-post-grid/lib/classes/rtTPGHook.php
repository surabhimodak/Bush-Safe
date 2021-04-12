<?php
if ( ! class_exists( 'rtTPGHook' ) ):
	class rtTPGHook {

		function __construct() {
			add_filter( 'tpg_author_arg', array( $this, 'filter_author_args' ), 10 );
		}

		function filter_author_args( $args ) {
			$defaults = array( 'role__in' => array( 'administrator', 'editor', 'author' ) );

			return wp_parse_args( $args, $defaults );
		}

	}

endif;