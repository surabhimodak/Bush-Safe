<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities {

	use WPDataAccess\Connection\WPDADB;

	/**
	 * Class WPDA_Import_File
	 *
	 * Loads the content of an import file and imports it.
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_Import_File {

		/**
		 * Pointer to import file
		 *
		 * @var string
		 */
		protected $file_pointer;

		/**
		 * Content of import file
		 *
		 * @var string
		 */
		protected $file_content;

		/**
		 * WPDA_Import constructor
		 *
		 * Create file pointer.
		 *
		 * @param string $file_path Full path of script (import) file.
		 *
		 * @since   1.0.0
		 *
		 */
		public function __construct( $file_path ) {

			$this->file_pointer = fopen( $file_path, 'rb' );

		}

		/**
		 * Close file.
		 *
		 * @since   2.0.12
		 */
		public function __destruct() {
			fclose( $this->file_pointer );
		}

		/**
		 * Import file content
		 *
		 * Import method writes the content of the import file to the database. Security checks:
		 * + Only INSERT INTO is allowed: no other DML, DDL and DCL statements allowed
		 * + Only inserts into the table name provided are allowed
		 * + Use explain to check the number of tables affect: more than 1 looks like SQL injection
		 *
		 * Since wpdb-query() only processes one query at a time we only need to check the type of statement at the
		 * beginning of the script ($file_content).
		 *
		 * This method only return -1 if a failure occurs or the number of rows inserted. The number of probable error
		 * cause is to huge and complex to check all possibilities. Exports created from the WP Data Access table list
		 * should normally import without problems. For manually created imports responsibility is with the developer.
		 *
		 * @param string $schema_name Schema in which this import allowes inserts.
		 * @param string $table_name Table in which this import allowes inserts.
		 * @param string $hide_errors ON = hide errors, OFF = show errors.
		 *
		 * @since   1.0.0
		 *
		 */
		public function import( $schema_name, $table_name, $hide_errors ) {
			global $wpdb;

			$wpdadb = WPDADB::get_db_connection( $schema_name );
			if ( null === $wpdadb ) {
				if ( is_admin() ) {
					wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $schema_name ) );
				} else {
					die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $schema_name ) );
				}
			}

			$suppress = $wpdadb->suppress_errors( 'on' === $hide_errors );

			$this->file_content = '';
			$rows               = 0;
			$rows_failed        = 0;

			if ( false !== $this->file_pointer ) {
				while ( ! feof( $this->file_pointer ) ) {
					$this->file_content .= fread( $this->file_pointer, 4096 );

					// Replace WP prefix and WPDA prefix.
					$this->file_content = str_replace( '{wp_schema}', $wpdb->dbname, $this->file_content );
					$this->file_content = str_replace( '{wp_prefix}', $wpdb->prefix, $this->file_content );
					$this->file_content = str_replace( '{wpda_prefix}', 'wpda', $this->file_content ); // for backward compatibility

					// Find and process SQL statements.
					$sql_end_unix    = strpos( $this->file_content, ";\n" );
					$sql_end_windows = strpos( $this->file_content, ";\r\n" );
					while ( false !== $sql_end_unix || false !== $sql_end_windows ) {
						if ( false === $sql_end_unix ) {
							$sql_end = $sql_end_windows;
						} elseif ( false === $sql_end_windows ) {
							$sql_end = $sql_end_unix;
						} else {
							$sql_end = min( $sql_end_unix, $sql_end_windows );
						}
						$sql = rtrim( substr( $this->file_content, 0, $sql_end ) );

						$this->file_content = substr( $this->file_content, strpos( $this->file_content, $sql ) + strlen( $sql ) + 1 );
						$rows ++;

						// Write file content to array for security check (150 characters is sufficient to check DML and table name).
						$dml_check = explode( ' ', substr( trim( $sql ), 0, 150 ) );

						if ( ! isset( $dml_check[0] ) || ! isset( $dml_check[1] ) ) {
							// No content.
							$rows_failed ++;
						} else {
							// Check first two words (must be insert into, no other statements allowed).
							if ( strtolower( $dml_check[0] . $dml_check[1] ) !== 'insertinto' ) {
								// Only insert into is allowed.
								$rows_failed ++;
							} else {
								// Check table name (using stristr should cover backticks and schema_names as well).
								if ( ! stristr( $dml_check[2], $table_name ) ) {
									$rows_failed ++;
								} else {
									// Insert row.
									if ( false === $wpdadb->query( $sql ) ) {
										$rows_failed ++;
									}
								}

							}
						}

						// Find next SQL statement.
						$sql_end_unix    = strpos( $this->file_content, ";\n" );
						$sql_end_windows = strpos( $this->file_content, ";\r\n" );
					}
				}
			}

			$wpdadb->suppress_errors( $suppress );

			$msg = "Imported " . ( $rows - $rows_failed ) . " rows";
			if ( $rows_failed > 0 ) {
				$msg .= " ($rows_failed failed).";
			} else {
				$msg .= ".";
			}
			$msg = new WPDA_Message_Box(
				[
					'message_text' => $msg,
				]
			);
			$msg->box();

		}

	}

}
