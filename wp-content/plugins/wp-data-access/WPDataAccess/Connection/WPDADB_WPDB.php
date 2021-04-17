<?php

namespace WPDataAccess\Connection {

	class WPDADB_WPDB extends \wpdb {

		private $ssl;
		private $ssl_key;
		private $ssl_cert;
		private $ssl_ca;
		private $ssl_path;
		private $ssl_cipher;

		/**
		 * Overwrite WPDADB_WPDB constructor
		 *
		 * Save schema_name
		 *
		 * @param $dbuser
		 * @param $dbpassword
		 * @param $dbname
		 * @param $dbhost
		 * @param $ssl
		 * @param $ssl_key
		 * @param $ssl_cert
		 * @param $ssl_ca
		 * @param $ssl_path
		 * @param $ssl_cipher
		 */
		public function __construct(
			$dbuser, $dbpassword, $dbname, $dbhost,
			$ssl, $ssl_key, $ssl_cert, $ssl_ca, $ssl_path, $ssl_cipher
		) {
			$this->ssl        = $ssl;
			$this->ssl_key    = $ssl_key;
			$this->ssl_cert   = $ssl_cert;
			$this->ssl_ca     = $ssl_ca;
			$this->ssl_path   = $ssl_path;
			$this->ssl_cipher = $ssl_cipher;

			if ( '' === trim( $this->ssl_path ) ) {
				$this->ssl_path = null;
			}

			if ( '' === trim( $this->ssl_cipher ) ) {
				$this->ssl_cipher = null;
			}

			parent::__construct( $dbuser, $dbpassword, $dbname, $dbhost );
		}

		public function is_connected() {
			return $this->dbh->connect_errno === 0;
		}

		/**
		 * Overwrite method
		 *
		 * This just adds a call to private method @ssl()
		 *
		 * @param bool $allow_bail
		 *
		 * @return bool
		 */
		public function db_connect( $allow_bail = false ) {
			$this->is_mysql = true;

			/*
			 * Deprecated in 3.9+ when using MySQLi. No equivalent
			 * $new_link parameter exists for mysqli_* functions.
			 */
			$new_link     = defined( 'MYSQL_NEW_LINK' ) ? MYSQL_NEW_LINK : true;
			$client_flags = defined( 'MYSQL_CLIENT_FLAGS' ) ? MYSQL_CLIENT_FLAGS : 0;

			if ( $this->use_mysqli ) {
				$this->dbh = mysqli_init();

				$host    = $this->dbhost;
				$port    = null;
				$socket  = null;
				$is_ipv6 = false;

				$host_data = $this->parse_db_host( $this->dbhost );
				if ( $host_data ) {
					list( $host, $port, $socket, $is_ipv6 ) = $host_data;
				}

				/*
				 * If using the `mysqlnd` library, the IPv6 address needs to be enclosed
				 * in square brackets, whereas it doesn't while using the `libmysqlclient` library.
				 * @see https://bugs.php.net/bug.php?id=67563
				 */
				if ( $is_ipv6 && extension_loaded( 'mysqlnd' ) ) {
					$host = "[$host]";
				}

				if ( 'on' === $this->ssl ) {
					mysqli_ssl_set(
						$this->dbh, $this->ssl_key, $this->ssl_cert, $this->ssl_ca, $this->ssl_path, $this->ssl_cipher
					);
					$client_flags = MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
				}

				mysqli_report( MYSQLI_REPORT_STRICT );
				try {
					if ( WP_DEBUG ) {
						if ( ! mysqli_real_connect( $this->dbh, $host, $this->dbuser, $this->dbpassword, null, $port, $socket, $client_flags ) ) {
							mysqli_report( MYSQLI_REPORT_OFF );
							return false;
						}
					} else {
						// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
						if ( ! @mysqli_real_connect( $this->dbh, $host, $this->dbuser, $this->dbpassword, null, $port, $socket, $client_flags ) ) {
							mysqli_report( MYSQLI_REPORT_OFF );
							return false;
						}
					}
				} catch ( \mysqli_sql_exception $e ) {
					mysqli_report( MYSQLI_REPORT_OFF );
					return false;
				}
				mysqli_report( MYSQLI_REPORT_OFF );

				if ( $this->dbh->connect_errno ) {
					$this->dbh = null;

					/*
					 * It's possible ext/mysqli is misconfigured. Fall back to ext/mysql if:
					 *  - We haven't previously connected, and
					 *  - WP_USE_EXT_MYSQL isn't set to false, and
					 *  - ext/mysql is loaded.
					 */
					$attempt_fallback = true;

					if ( $this->has_connected ) {
						$attempt_fallback = false;
					} elseif ( defined( 'WP_USE_EXT_MYSQL' ) && ! WP_USE_EXT_MYSQL ) {
						$attempt_fallback = false;
					} elseif ( ! function_exists( 'mysql_connect' ) ) {
						$attempt_fallback = false;
					}

					if ( $attempt_fallback ) {
						$this->use_mysqli = false;
						return $this->db_connect( $allow_bail );
					}
				}
			} else {
				if ( WP_DEBUG ) {
					$this->dbh = mysql_connect( $this->dbhost, $this->dbuser, $this->dbpassword, $new_link, $client_flags );
				} else {
					// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
					$this->dbh = @mysql_connect( $this->dbhost, $this->dbuser, $this->dbpassword, $new_link, $client_flags );
				}
			}

			if ( ! $this->dbh && $allow_bail ) {
				wp_load_translations_early();

				// Load custom DB error template, if present.
				if ( file_exists( WP_CONTENT_DIR . '/db-error.php' ) ) {
					require_once WP_CONTENT_DIR . '/db-error.php';
					die();
				}

				$message = '<h1>' . __( 'Error establishing a database connection' ) . "</h1>\n";

				$message .= '<p>' . sprintf(
					/* translators: 1: wp-config.php, 2: Database host. */
						__( 'This either means that the username and password information in your %1$s file is incorrect or we can&#8217;t contact the database server at %2$s. This could mean your host&#8217;s database server is down.' ),
						'<code>wp-config.php</code>',
						'<code>' . htmlspecialchars( $this->dbhost, ENT_QUOTES ) . '</code>'
					) . "</p>\n";

				$message .= "<ul>\n";
				$message .= '<li>' . __( 'Are you sure you have the correct username and password?' ) . "</li>\n";
				$message .= '<li>' . __( 'Are you sure you have typed the correct hostname?' ) . "</li>\n";
				$message .= '<li>' . __( 'Are you sure the database server is running?' ) . "</li>\n";
				$message .= "</ul>\n";

				$message .= '<p>' . sprintf(
					/* translators: %s: Support forums URL. */
						__( 'If you&#8217;re unsure what these terms mean you should probably contact your host. If you still need help you can always visit the <a href="%s">WordPress Support Forums</a>.' ),
						__( 'https://wordpress.org/support/forums/' )
					) . "</p>\n";

				$this->bail( $message, 'db_connect_fail' );

				return false;
			} elseif ( $this->dbh ) {
				if ( ! $this->has_connected ) {
					$this->init_charset();
				}

				$this->has_connected = true;

				$this->set_charset( $this->dbh );

				$this->ready = true;
				$this->set_sql_mode();
				$this->select( $this->dbname, $this->dbh );

				return true;
			}

			return false;
		}

		public function get_connect_errno() {
			return $this->dbh->connect_errno;
		}

		public function get_connect_error() {
			return $this->dbh->connect_error;
		}

	}

}