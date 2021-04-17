<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Cookies
 */

namespace WPDataAccess\Cookies {

	use WPDataAccess\WPDA;
	use WPDataAccess\List_Table\WPDA_List_Table;

	class WPDA_Cookies {

		const COOKIE_TIME_EXPIRATION = 365 * 24 * 3600;

		/**
		 * Menu slug or null
		 *
		 * @var null
		 */
		protected $page = null;

		/**
		 * WP_Data_Access_Admin constructor
		 *
		 * @since   1.0.0
		 */
		public function __construct() {
			if ( isset( $_REQUEST['page'] ) ) {
				$this->page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ); // input var okay.
			}
		}

		/**
		 * Handle plugin cookies
		 *
		 * Cookies are use to remember values during navigation: (max 1 hour)
		 * 1) SCHEMA NAME
		 * The schema name is saved as a cookie when changed in the Data Explorer. As long as the user stays within the
		 * page the saved schema is used. When the user moves to another page the value is destroyed. The user gets the
		 * default value on the next visit.
		 * 2) FAVOURITE SELECTION
		 * The favourite selection is saved as cookie when changed in the Data Explorer. As long as the user stays within
		 * the page the saved selection is used. When the user moves to another page the value is destroyed. The user gets
		 * the default value on the next visit.
		 * 3) SEARCH ARGUMENT
		 * Search arguments are saved as cookies per table. As long as the user stays within the same page the saved
		 * search value is used. When the user moves to another page the value is destroyed. This allows users to navigate
		 * between pages without losing the search value.
		 *
		 * @since   1.6.0
		 */
		public function handle_plugin_cookies() {
			$panel_cookies = WPDA::get_option( WPDA::OPTION_PLUGIN_PANEL_COOKIES );

			if ( $this->page === \WP_Data_Access_Admin::PAGE_MAIN ) {
				// Handle Data Explorer cookies (search cookie is handled in next section).
				// Handle cookie to remember active schema.
				$cookie_name = \WP_Data_Access_Admin::PAGE_MAIN . '_schema_name';
				if ( isset( $_REQUEST['wpda_main_db_schema'] ) && '' !== $_REQUEST['wpda_main_db_schema'] ) {
					$requested_db_schema = sanitize_text_field( wp_unslash( $_REQUEST['wpda_main_db_schema'] ) ); // input var okay.
					$this->set_cookie( $cookie_name, $requested_db_schema, time() + self::COOKIE_TIME_EXPIRATION );
				} else {
					if ( 'clear' === $panel_cookies ) {
						// Check referer: clear cookie on new page request.
						$url = parse_url( wp_get_referer() );
						if ( isset( $url['query'] ) ) {
							parse_str( $url['query'], $path );
							if ( isset( $path['page'] ) ) {
								$page = $path['page'];
								if ( $this->page !== $page ) {
									// New page request: reset cookie.
									$this->set_cookie( $cookie_name, '', time() - self::COOKIE_TIME_EXPIRATION );
								}
							}
						}
					}
				}

				// Handle cookie to remember favourite selection.
				$cookie_name = $this->page . '_favourites';
				if ( isset( $_REQUEST['wpda_main_favourites'] ) ) {
					$favourites = sanitize_text_field( wp_unslash( $_REQUEST['wpda_main_favourites'] ) ); // input var okay.
					$this->set_cookie( $cookie_name, $favourites, time() + self::COOKIE_TIME_EXPIRATION );
				} else {
					if ( 'clear' === $panel_cookies ) {
						// Check referer: clear cookie on new page request.
						$url = parse_url( wp_get_referer() );
						if ( isset( $url['query'] ) ) {
							parse_str( $url['query'], $path );
							if ( isset( $path['page'] ) ) {
								$page = $path['page'];
								if ( $this->page !== $page ) {
									// New page request: reset cookie.
									$this->set_cookie( $cookie_name, '', time() - self::COOKIE_TIME_EXPIRATION );
								}
							}
						}
					}
				}
			}

			// Handle cookie for search value.
			$table_name       =
				isset( $_REQUEST['table_name'] ) ?
					sanitize_text_field( wp_unslash( $_REQUEST['table_name'] ) ) :
					WPDA_List_Table::LIST_BASE_TABLE; // input var okay.
			$cookie_name      = $this->page . '_search_' . str_replace( '.', '_', $table_name );
			$search_item_name = WPDA_List_Table::SEARCH_ITEM_NAME_DEFAULT;
			if ( isset( $_REQUEST[ $search_item_name ] ) ) { // input var okay.
				$search_argument = wp_unslash( $_REQUEST[ $search_item_name ] ); // input var okay.
				$this->set_cookie( $cookie_name, $search_argument, time() + self::COOKIE_TIME_EXPIRATION );
			} else {
				if ( 'clear' === $panel_cookies ) {
					// Check referer: clear cookie on new page request.
					$url = parse_url( wp_get_referer() );
					if ( isset( $url['query'] ) ) {
						parse_str( $url['query'], $path );
						if ( isset( $path['page'] ) ) {
							$page = $path['page'];
							if ( $this->page !== $page ) {
								// New page request: reset cookie and all cookies for subpages.
								foreach ( $_COOKIE as $key => $value ) {
									if ( $this->page . '_search_' === substr( $key, 0, strlen( $this->page . '_search_' ) ) ) {
										$this->set_cookie( $key, '', time() - self::COOKIE_TIME_EXPIRATION );
									}
								}
							}
						}
					}
				}
			}
		}

		protected function set_cookie( $cookie_name, $cookie_value, $cookie_expires ) {
			if (PHP_VERSION_ID < 70300) {
				setcookie( $cookie_name, $cookie_value, $cookie_expires, '/; samesite=strict' );
			} else {
				setcookie($cookie_name, $cookie_value, [
					'expires'  => $cookie_expires,
					'path'     => '/',
					'samesite' => 'strict',
				]);
			}

			if ( '' === $cookie_value ) {
				unset( $_COOKIE[ $cookie_name ] );
			}
		}

	}

}