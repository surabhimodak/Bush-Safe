<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Connection
 */

namespace WPDataAccess\Connection {

	use WPDataAccess\WPDA;

	/**
	 * Class WPDADB
	 *
	 * Manage local and remote database connections.
	 *
	 * @author  Peter Schulz
	 * @since   3.0.0
	 */
	class WPDADB {

		/**
		 * Database connections cached per database name (schema_name)
		 *
		 * Remote database are prefixed with "rdb:"
		 *
		 * @var array
		 */
		static protected $db_connections = [];

		/**
		 * Remote database access definitions
		 *
		 * @var array|bool
		 */
		static protected $remote_databases = false;

		/**
		 * Stores te lower_case_table_names db value
		 *
		 * @var null|int
		 */
		static protected $lower_case_table_names = null;

		/**
		 * Encrypt a string with the WPDA secret key and iv
		 *
		 * @param string $string String to be encrypted
		 *
		 * @return string
		 */
		public static function string_encrypt( $string ) {
			$secret_key = WPDA::get_option( WPDA::OPTION_PLUGIN_SECRET_KEY );
			$secret_iv  = WPDA::get_option( WPDA::OPTION_PLUGIN_SECRET_IV );

			$encrypt_method = "AES-256-CBC";
			$key            = hash( 'sha256', $secret_key );
			$iv             = substr( hash( 'sha256', $secret_iv ), 0, 16 );

			return base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
		}

		/**
		 * Decrypt a string with the WPDA secret key and iv
		 *
		 * @param string $string String to be decrypted
		 *
		 * @return string
		 */
		public static function string_decrypt( $string ) {
			$secret_key = WPDA::get_option( WPDA::OPTION_PLUGIN_SECRET_KEY );
			$secret_iv  = WPDA::get_option( WPDA::OPTION_PLUGIN_SECRET_IV );

			$encrypt_method = "AES-256-CBC";
			$key            = hash( 'sha256', $secret_key );
			$iv             = substr( hash( 'sha256', $secret_iv ), 0, 16 );

			return openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
		}

		public static function load_remote_databases() {
			if ( false == self::$remote_databases ) {
				$decrypted_databases = [];
				$encrypted_databases = get_option( 'wpda_remote_databases' );
				if ( false !== $encrypted_databases ) {
					foreach ( $encrypted_databases as $key => $val ) {
						$decrypted_databases[ self::string_decrypt( $key ) ] = [
							'host'       => self::string_decrypt( $val[0] ),
							'username'   => self::string_decrypt( $val[1] ),
							'password'   => self::string_decrypt( $val[2] ),
							'port'       => self::string_decrypt( $val[3] ),
							'database'   => self::string_decrypt( $val[4] ),
							'disabled'   => $val[5],
							'ssl'        => isset( $val[6] ) ? self::string_decrypt( $val[6] ) : 'off',
							'ssl_key'    => isset( $val[7] ) ? self::string_decrypt( $val[7] ) : '',
							'ssl_cert'   => isset( $val[8] ) ? self::string_decrypt( $val[8] ) : '',
							'ssl_ca'     => isset( $val[9] ) ? self::string_decrypt( $val[9] ) : '',
							'ssl_path'   => isset( $val[10] ) ? self::string_decrypt( $val[10] ) : '',
							'ssl_cipher' => isset( $val[11] ) ? self::string_decrypt( $val[11] ) : '',
						];
					}
				}
				self::$remote_databases = $decrypted_databases;
			}
		}

		public static function save_remote_databases() {
			self::load_remote_databases();

			$encrypted_databases = [];
			foreach ( self::$remote_databases as $key => $val ) {
				$encrypted_databases[ self::string_encrypt( $key ) ] = [
					0  => self::string_encrypt( $val[ 'host' ] ),
					1  => self::string_encrypt( $val[ 'username' ] ),
					2  => self::string_encrypt( $val[ 'password' ] ),
					3  => self::string_encrypt( $val[ 'port' ] ),
					4  => self::string_encrypt( $val[ 'database' ] ),
					5  => $val[ 'disabled' ],
					6  => isset( $val[ 'ssl' ] ) ? self::string_encrypt( $val[ 'ssl' ] ) : 'off',
					7  => isset( $val[ 'ssl_key' ] ) ? self::string_encrypt( $val[ 'ssl_key' ] ) : '',
					8  => isset( $val[ 'ssl_cert' ] ) ? self::string_encrypt( $val[ 'ssl_cert' ] ) : '',
					9  => isset( $val[ 'ssl_ca' ] ) ? self::string_encrypt( $val[ 'ssl_ca' ] ) : '',
					10 => isset( $val[ 'ssl_path' ] ) ? self::string_encrypt( $val[ 'ssl_path' ] ) : '',
					11 => isset( $val[ 'ssl_cipher' ] ) ? self::string_encrypt( $val[ 'ssl_cipher' ] ) : '',
				];
			}
			update_option( 'wpda_remote_databases', $encrypted_databases );
		}

		public static function get_remote_databases( $include_disabled = false ) {
			self::load_remote_databases();

			if ( $include_disabled ) {
				return self::$remote_databases;
			} else {
				$exclude_disabled = self::$remote_databases;
				foreach ( self::$remote_databases as $key => $remote_database ) {
					if ( $remote_database['disabled'] ) {
						unset( $exclude_disabled[ $key ] );
					}
				}
				return $exclude_disabled;
			}
		}

		public static function get_remote_database( $database, $include_disabled = false ) {
			self::load_remote_databases();

			if ( isset( self::$remote_databases[ $database ] ) ) {
				if ( $include_disabled ) {
					return self::$remote_databases[ $database ];
				} else {
					if ( ! self::$remote_databases[ $database ]['disabled'] ) {
						return self::$remote_databases[ $database ];
					} else {
						return false;
					}
				}
			} else {
				return false;
			}
		}

		public static function add_remote_database(
			$database, $host, $user, $passwd, $port, $schema,
			$ssl, $ssl_key, $ssl_cert, $ssl_ca, $ssl_path, $ssl_cipher
		) {
			self::load_remote_databases();

			if ( false === self::get_remote_database( $database ) ) {
				self::$remote_databases[ $database ] = [
					'host'       => $host,
					'username'   => $user,
					'password'   => $passwd,
					'port'       => $port,
					'database'   => $schema,
					'disabled'   => false,
					'ssl'        => $ssl,
					'ssl_key'    => $ssl_key,
					'ssl_cert'   => $ssl_cert,
					'ssl_ca'     => $ssl_ca,
					'ssl_path'   => $ssl_path,
					'ssl_cipher' => $ssl_cipher,
				];
				self::save_remote_databases();

				return true;
			} else {
				return false;
			}
		}

		public static function del_remote_database( $database ) {
			self::load_remote_databases();

			if ( false === self::get_remote_database( $database, true ) ) {
				return false;
			} else {
				unset( self::$remote_databases[ $database ] );
				self::save_remote_databases();

				return true;
			}
		}

		public static function upd_remote_database(
			$database, $host, $user, $passwd, $port, $schema, $disabled, $database_old,
			$ssl, $ssl_key, $ssl_cert, $ssl_ca, $ssl_path, $ssl_cipher
		) {
			self::load_remote_databases();

			if ( false !== $database_old && $database !== $database_old ) {
				self::add_remote_database(
					$database, $host, $user, $passwd, $port, $schema,
					$ssl, $ssl_key, $ssl_cert, $ssl_ca, $ssl_path, $ssl_cipher
				);
				self::del_remote_database( $database_old );

				return true;
			} else {
				if ( false === self::get_remote_database( $database, true ) ) {
					return false;
				} else {
					self::$remote_databases[ $database ] = [
						'host'       => $host,
						'username'   => $user,
						'password'   => $passwd,
						'port'       => $port,
						'database'   => $schema,
						'disabled'   => $disabled,
						'ssl'        => $ssl,
						'ssl_key'    => $ssl_key,
						'ssl_cert'   => $ssl_cert,
						'ssl_ca'     => $ssl_ca,
						'ssl_path'   => $ssl_path,
						'ssl_cipher' => $ssl_cipher,
					];
					self::save_remote_databases();

					return true;
				}
			}
		}

		/**
		 * Get database connection
		 *
		 * Remote schema name starts with prefix "rdb:"
		 *
		 * @param string $schema_name Database (schema) name
		 *
		 * @return mixed|\wpdb
		 */
		public static function get_db_connection( $schema_name ) {
			global $wpdb;
			if ( 'rdb:' === substr( $schema_name, 0, 4 ) ) {
				// Remote database (other ip|port)
				self::load_remote_databases();
				if ( ! isset( self::$db_connections[ $schema_name ] ) ) {
					if ( isset( self::$remote_databases[ $schema_name ] ) ) {
						$host = self::$remote_databases[ $schema_name ]['host'];
						if (
							self::$remote_databases[ $schema_name ]['port'] !== '' &&
							self::$remote_databases[ $schema_name ]['port'] !== '3306'
						) {
							$host .= ':' . self::$remote_databases[ $schema_name ]['port'];
						}
						$wpda_remote_database = new WPDADB_WPDB(
							self::$remote_databases[ $schema_name ]['username'],
							self::$remote_databases[ $schema_name ]['password'],
							self::$remote_databases[ $schema_name ]['database'],
							$host,
							self::$remote_databases[ $schema_name ]['ssl'],
							self::$remote_databases[ $schema_name ]['ssl_key'],
							self::$remote_databases[ $schema_name ]['ssl_cert'],
							self::$remote_databases[ $schema_name ]['ssl_ca'],
							self::$remote_databases[ $schema_name ]['ssl_path'],
							self::$remote_databases[ $schema_name ]['ssl_cipher']
						);
						if ( $wpda_remote_database->get_connect_errno() === 0 ) {
							self::$db_connections[ $schema_name ] = $wpda_remote_database;
						} else {
							// Connection failed
							self::$db_connections[ $schema_name ] = null;
						}
					} else {
						// Remote schema name not found
						self::$db_connections[ $schema_name ] = null;
					}
				}

				return self::$db_connections[ $schema_name ];
			} else {
				// Database runs in local WordPress instance
				if ( '' === $schema_name || null === $schema_name || self::iswpdb( $schema_name ) ) {
					return $wpdb;
				} else {
					if ( ! isset( self::$db_connections[ $schema_name ] ) ) {
						self::$db_connections[ $schema_name ] = new \wpdb( DB_USER, DB_PASSWORD, $schema_name, DB_HOST );
					}

					return self::$db_connections[ $schema_name ];
				}
			}
		}

		public static function iswpdb( $schema_name ) {
			global $wpdb;
			if ( null === self::$lower_case_table_names ) {
				$lower_case_table_names = $wpdb->get_results( "SHOW VARIABLES LIKE 'lower_case_table_names'", 'ARRAY_N' );
				if ( is_array( $lower_case_table_names ) && isset( $lower_case_table_names[0][1] ) ) {
					self::$lower_case_table_names = $lower_case_table_names[0][1];
				} else {
					self::$lower_case_table_names = 0;
				}
			}
			switch ( self::$lower_case_table_names ) {
				case 1:
				case 2:
					return strtolower( $wpdb->dbname ) === $schema_name;
					break;
				default:
					return $wpdb->dbname === $schema_name;
			}
		}

		/**
		 * Check if a connection with a remote database can be established
		 *
		 * @return \wpdb
		 */
		public function check_remote_database_connection() {
			echo 'Preparing connection...<br/>';

			$host   = isset( $_REQUEST['host'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['host'] ) ) : ''; // input var okay.
			$user   = isset( $_REQUEST['user'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['user'] ) ) : ''; // input var okay.
			$passwd = isset( $_REQUEST['passwd'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['passwd'] ) ) : ''; // input var okay.
			$port   = isset( $_REQUEST['port'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['port'] ) ) : ''; // input var okay.
			$schema = isset( $_REQUEST['schema'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schema'] ) ) : ''; // input var okay.

			// Add ssl
			$ssl        = isset( $_REQUEST['ssl'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ssl'] ) ) : ''; // input var okay.
			$ssl_key    = isset( $_REQUEST['ssl_key'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ssl_key'] ) ) : ''; // input var okay.
			$ssl_cert   = isset( $_REQUEST['ssl_cert'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ssl_cert'] ) ) : ''; // input var okay.
			$ssl_ca     = isset( $_REQUEST['ssl_ca'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ssl_ca'] ) ) : ''; // input var okay.
			$ssl_path   = isset( $_REQUEST['ssl_path'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ssl_path'] ) ) : ''; // input var okay.
			$ssl_cipher = isset( $_REQUEST['ssl_cipher'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ssl_cipher'] ) ) : ''; // input var okay.

			if ( '' === $host || '' === $user || '' === $passwd || '' === $schema ) {
				return false;
			}

			if ( $port !== '' && $port !== '3306' ) {
				$host .= ':' . $port;
			}

			echo 'Establishing connection...<br/>';

			$wpdadb = new WPDADB_WPDB(
				$user, $passwd, $schema, $host,
				$ssl, $ssl_key, $ssl_cert, $ssl_ca, $ssl_path, $ssl_cipher
			);

			if ( $wpdadb->get_connect_errno() !== 0 ) {
				echo "
					<br/>
					ERROR {$wpdadb->get_connect_errno()} - {$wpdadb->get_connect_error()}
					<br/><br/>
					<strong>Connection failed!</strong>
				";
			} else {
				$query = $wpdadb->prepare(
					'select 1 from information_schema.tables where table_schema = %s',
					[
						$schema
					]
				);
				$wpdadb->get_results( $query, 'ARRAY_A' );

				echo "
					<br/>
					Connection established...
					<br/>
					Counting tables...
					<br/>
					Found {$wpdadb->num_rows} tables on remote host...
					<br/><br/>
					<strong>Remote database connection valid!</strong>
				";
			}
		}

	}

}