<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities {

	use WPDataAccess\Connection\WPDADB;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Import_Multi
	 *
	 * Imports a script file that might contain multiple SQL statement including create table and insert into
	 * statements.
	 *
	 * @author  Peter Schulz
	 * @since   1.6.0
	 */
	class WPDA_Import_Multi {

		const SOLUTIONS = '(<a href="https://wpdataaccess.com/docs/documentation/getting-started/known-limitations/" target="_blank">see solutions</a>)';

		/**
		 * URL where to post data
		 *
		 * @var string
		 */
		protected $url;

		/**
		 * Database schema name
		 *
		 * @var string
		 */
		protected $schema_name;

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
		 * Text to inform user (line 1).
		 * @var string
		 */
		protected $info_title = '';

		/**
		 * Text to inform user (line 1).
		 * @var string
		 */
		protected $info_text = '';

		/**
		 * Indicicates whether ZipArchive is installed.
		 * @var bool
		 */
		protected $isinstalled_ziparchive = false;

		/**
		 * WPDA_Import constructor
		 *
		 * Checks if ZipArchive is installed to support the import of ZIP files.
		 *
		 * @param string $page Page where to post data (url).
		 * @param string $schema_name Database schema name.
		 * @param array  $args Extra arguments.
		 *
		 * @since   1.6.0
		 *
		 */
		public function __construct( $page, $schema_name, $args = null ) {
			$this->url         = $page;
			$this->schema_name = $schema_name;

			$this->isinstalled_ziparchive = class_exists( '\ZipArchive' );

			if ( ! is_null( $args ) && isset( $args[0] ) && isset( $args[1] ) ) {
				$this->info_title = $args[0];
				$this->info_text  = $args[1];
			} else {
				$this->info_title = __(
					'IMPORT DATA/EXECUTE SCRIPT(S)',
					'wp-data-access' );
				if ( $this->isinstalled_ziparchive ) {
					$this->info_text = __(
						'Supports <strong>sql</strong> and <strong>zip</strong> file types.',
						'wp-data-access'
					);
				} else {
					$this->info_text = __(
						'Supports only file type <strong>sql</strong> (install <strong>ZipArchive</strong> for <strong>zip</strong> file type support).',
						'wp-data-access'
					);
				}
			}
		}

		/**
		 * Checks if request is valid and allowed
		 *
		 * If the requested import is valid and allowed, the import file is loaded and its content imported.
		 *
		 * @since   1.6.0
		 */
		public function check_post() {
			// Check if import was requested.
			if (
				isset( $_REQUEST['action'] ) &&
				'import' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) // input var okay.
			) {
				// Security check.
				$wp_nonce = isset( $_REQUEST['_wpnonceimport'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonceimport'] ) ) : '?'; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, "wpda-import-from-data-explorer" ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				if ( isset( $_FILES['filename'] ) ) {

					if ( 0 === $_FILES['filename']['error']
					     && is_uploaded_file( $_FILES['filename']['tmp_name'] )
					) {
						if (
							'application/zip' === $_FILES['filename']['type'] ||
							'application/x-zip' === $_FILES['filename']['type'] ||
							'application/x-zip-compressed' === $_FILES['filename']['type']
						) {
							// Process ZIP file.
							if ( $this->isinstalled_ziparchive ) {
								$zip = new \ZipArchive;
								if ( $zip->open( $_FILES['filename']['tmp_name'] ) ) {
									for ( $i = 0; $i < $zip->numFiles; $i ++ ) {
										$this->file_pointer = $zip->getStream( $zip->getNameIndex( $i ) );
										$this->import( $zip->getNameIndex( $i ) );
									}
								} else {
									// Error reading ZIP file.
									$this->import_failed( sprintf( __( 'Import failed [error reading ZIP file `%s`]', 'wp-data-access' ), $_FILES['filename']['name'] ) );
								}
							} else {
								// ZipArchive not installed.
								$this->import_failed( sprintf( __( 'Import failed - ZipArchive not installed %s', 'wp-data-access' ), SELF::SOLUTIONS ) );
							}
						} else {
							// Process plain file.
							$this->file_pointer = fopen( $_FILES['filename']['tmp_name'], 'rb' );
							$this->import( $_FILES['filename']['name'] );
						}
					}
				} else {
					$this->upload_failed();
				}
			} elseif ( isset( $_REQUEST['impchk'] ) ) {
				$this->upload_failed();
			}
		}

		/**
		 * Perform import
		 *
		 * Import file is read in chunks to allow imports of large files without resource limitations. An indicator
		 * is maintained to follow the progress of the import. The indicator is used to show a message at the end
		 * whether the import succeeded or failed.
		 *
		 * @param string $file_name Import file name
		 *
		 * @since   1.6.0
		 */
		protected function import( $file_name ) {
			// Check if errors should be shown.
			$hide_errors = isset( $_REQUEST['hide_errors'] ) ? $_REQUEST['hide_errors'] : 'off';

			$result = true;
			global $wpdb;

			$wpdadb   = WPDADB::get_db_connection( $this->schema_name );
			if ( null === $wpdadb ) {
				if ( is_admin() ) {
					wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				} else {
					die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				}
			}

			$suppress = $wpdadb->suppress_errors( 'on' === $hide_errors );

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

						if ( false === $wpdadb->query( $sql ) ) {
							$result = false;
						}

						// Find next SQL statement.
						$sql_end_unix    = strpos( $this->file_content, ";\n" );
						$sql_end_windows = strpos( $this->file_content, ";\r\n" );
					}
				}
			}

			$wpdadb->suppress_errors( $suppress );

			// Process file content.
			if ( ! $result ) {
				$this->import_failed( sprintf( __( 'Import `%s` failed [check import file]', 'wp-data-access' ), $file_name ) );
			} else {
				// Import succeeded.
				$msg = new WPDA_Message_Box(
					[
						'message_text' => sprintf( __( 'Import `%s` completed succesfully', 'wp-data-access' ), $file_name ),
					]
				);
				$msg->box();
			}
		}

		/**
		 * Inform user that an error occured
		 *
		 * @param string $msg Error message text
		 *
		 * @since   1.6.0
		 */
		protected function import_failed( $msg ) {
			$msg = new WPDA_Message_Box(
				[
					'message_text'           => $msg,
					'message_type'           => 'error',
					'message_is_dismissible' => false,
				]
			);
			$msg->box();
		}

		/**
		 * Inform user that the import failed
		 *
		 * @since   1.6.0
		 */
		protected function upload_failed() {
			$msg = new WPDA_Message_Box(
				[
					'message_text'           => sprintf( __( 'File upload failed %s', 'wp-data-access' ), SELF::SOLUTIONS ),
					'message_type'           => 'error',
					'message_is_dismissible' => false,
				]
			);
			$msg->box();
		}

		/**
		 * Adds an import button
		 *
		 * @param string $label Button label.
		 * @param string $class Button CSS class.
		 *
		 * @since   1.6.0
		 *
		 */
		public function add_button( $label = '', $class = 'page-title-action' ) {
			if ( '' === $label ) {
				$label = __( 'Import data/Execute script(s)', 'wp-data-access' );
			}
			?>
			<a href="javascript:void(0)"
			   onclick="jQuery('#upload_file_container_multi').show()"
			   class="wpda_tooltip <?php echo esc_attr( $class ); ?>"
			   title="<?php echo __( 'Allows to import data and execute SQL scripts', 'wp-data-access' ); ?>.
<?php echo __( 'Add a ; and a new line to the end of every SQL statement to run multiple SQL statements', 'wp-data-access' ); ?>."
			>
				<span class="material-icons wpda_icon_on_button">code</span> <?php echo esc_attr( $label ); ?></a>
			<?php
		}

		/**
		 * Adds an import container
		 *
		 * The container contains an upload form. The container is hidden by default. When the button created in
		 * {@see WPDA_Import_Multi::add_button()} is clicked, the container is shown.
		 *
		 * @since   1.6.0
		 */
		public function add_container() {
			$file_uploads_enabled = @ini_get( 'file_uploads' );

			if ( $this->isinstalled_ziparchive ) {
				$file_extentions = '.sql,.zip';
			} else {
				$file_extentions = '.sql';
			}

			?>

			<script type='text/javascript'>
				function before_submit_upload() {
					if (jQuery('#filename').val() == '') {
						alert('<?php echo __( 'No file to import!', 'wp-data-access' ); ?>');
						return false;
					}
					if (!(jQuery('#filename')[0].files[0].size < <?php echo WPDA::convert_memory_to_decimal( @ini_get( 'upload_max_filesize' ) ); ?>)) {
						alert("<?php echo __( 'File exceeds maximum size of', 'wp-data-access' ); ?> <?php echo @ini_get( 'upload_max_filesize' ); ?>!");
						return false;
					}
				}
			</script>

			<div id="upload_file_container_multi" style="display: none">
				<div>&nbsp;</div>
				<div>
					<?php if ( $file_uploads_enabled ) { ?>
						<p>
						</p>
						<form id="form_import_multi_table" method="post"
							  action="<?php echo esc_attr( $this->url ); ?>&impchk"
							  enctype="multipart/form-data">
							<fieldset class="wpda_fieldset">
								<legend>
								<span>
									<strong><?php echo $this->info_title; ?></strong>
									<a href="https://wpdataaccess.com/docs/documentation/data-explorer/exports-and-imports/" target="_blank" class="wpda_tooltip" title="Plugin Help - open a new tab or window">
										<span class="dashicons dashicons-editor-help "style="text-decoration:none;vertical-align:middle;font-size:18px;">
									</span></a>
								</span>
								</legend>
								<p class="wpda_list_indent">
									<?php
									echo $this->info_text . ' ' . __( 'Maximum supported file size is', 'wp-data-access' ) . ' <strong>' . @ini_get( 'upload_max_filesize' ) . '</strong>. ';
									?>
								</p>
								<p class="wpda_list_indent">
									<input type="file" name="filename" id="filename" class="wpda_tooltip"
										   accept="<?php echo esc_attr( $file_extentions ); ?>">
									<button type="submit"
											class="button button-secondary"
											onclick="return before_submit_upload()">
										<span class="material-icons wpda_icon_on_button">code</span>
										<?php echo __( 'Import file/Execute script(s)', 'wp-data-access' ); ?>
									</button>
									<a href="javascript:void(0)"
									   onclick="jQuery('#upload_file_container_multi').hide()"
									   class="button button-secondary">
										<span class="material-icons wpda_icon_on_button">cancel</span>
										<?php echo __( 'Cancel', 'wp-data-access' ); ?>
									</a>
									<label style="vertical-align:baseline;">
										<input type="checkbox" name="hide_errors" style="vertical-align:sub;" checked>
										<?php echo __( 'Hide errors', 'wp-data-access' ); ?>
									</label>
									<input type="hidden" name="action" value="import">
									<?php wp_nonce_field( "wpda-import-from-data-explorer", '_wpnonceimport', false ); ?>
								</p>
								<p class="wpda_list_indent">
									<?php
									echo '<strong>' . __( 'IMPORTANT', 'wp-data-access' ) . '</strong>&nbsp;';
									echo __( 'Add a ; and a new line to the end of every SQL statement to run multiple SQL statements', 'wp-data-access' ) . '.';
									?>
								</p>
							</fieldset>
						</form>
					<?php } else { ?>
						<p>
							<strong><?php echo __( 'ERROR', 'wp-data-access' ); ?></strong>
						</p>
						<p class="wpda_list_indent">
							<?php
							echo __( 'Your configuration does not allow file uploads!', 'wp-data-access' );
							echo ' ';
							echo __( 'Set', 'wp-data-access' );
							echo ' <strong>';
							echo __( 'file_uploads', 'wp-data-access' );
							echo '</strong> ';
							echo __( 'to', 'wp-data-access' );
							echo ' <strong>';
							echo __( 'On', 'wp-data-access' );
							echo '</strong> (<a href="https://wpdataaccess.com/docs/documentation/getting-started/known-limitations/">';
							echo __( 'see documentation', 'wp-data-access' );
							echo '</a>).';
							?>
						</p>
					<?php } ?>
				</div>
				<div>&nbsp;</div>
			</div>

			<?php
		}

	}

}
