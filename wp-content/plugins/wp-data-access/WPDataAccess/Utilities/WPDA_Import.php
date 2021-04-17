<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Exist;
	use WPDataAccess\List_Table\WPDA_List_Table;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Import
	 *
	 * Imports a script file that contains exactly one insert into statement (can insert multiple records). Only
	 * insert statements are allowed. Insert is only allowed into the table name provided in constructor. Subqueries
	 * are not allowed (checked with explain).
	 *
	 * @author  Peter Schulz
	 * @since   1.0.0
	 */
	class WPDA_Import {

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
		 * Database table name
		 *
		 * @var string
		 */
		protected $table_name;

		/**
		 * Indicates where imports are allowed
		 *
		 * @var string 'on' or 'off'
		 */
		protected $allow_imports;

		/**
		 * WPDA_Import constructor
		 *
		 * Checks if imports are allowed. Throws an exception if imports are not allowed.
		 *
		 * @param string $page Page where to post data (url).
		 * @param string $schema_name Database schema name.
		 * @param string $table_name Database table name.
		 *
		 * @throws \Exception Throws exception if export is disabled.
		 * @since   1.0.0
		 *
		 */
		public function __construct( $page, $schema_name, $table_name ) {

			if ( ! WPDA::is_wpda_table( $table_name ) ) {
				// Check access rights for non WPDA tables.
				if ( 'on' !== WPDA::get_option( WPDA::OPTION_BE_ALLOW_IMPORTS ) ) {
					// Prevent import object being created: exception must be handled in calling method.
					throw new \Exception( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}
				// Disable import for views.
				$wpda_dictionary_exists = new WPDA_Dictionary_Exist( $schema_name, $table_name );
				if ( $wpda_dictionary_exists->is_view() ) {
					// Prevent import object being created: exception must be handled in calling method.
					throw new \Exception( __( 'ERROR: Import not possible on views', 'wp-data-access' ) );
				}
			}

			$this->url         = $page;
			$this->schema_name = $schema_name;
			$this->table_name  = $table_name;

		}

		/**
		 * Checks if request is valid and allowed
		 *
		 * If the requested import is valid and allowed, the import file is loaded and its content imported.
		 *
		 * @since   1.0.0
		 */
		public function check_post() {

			// Check if import was requested.
			// Import is not possible for WPDA_List_Table::LIST_BASE_TABLE (view in mysql information_schema).
			if ( WPDA_List_Table::LIST_BASE_TABLE !== $this->table_name &&
			     isset( $_REQUEST['action'] ) && 'import' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) // input var okay.
			) {
				// Security check.
				$wp_nonce = isset( $_REQUEST['_wpnonceimport'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonceimport'] ) ) : '?'; // input var okay.
				if ( ! wp_verify_nonce( $wp_nonce, "wpda-import-{$this->table_name}" ) ) {
					wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
				}

				if ( isset( $_FILES['filename'] ) ) {

					if ( UPLOAD_ERR_OK === $_FILES['filename']['error']
					     && is_uploaded_file( $_FILES['filename']['tmp_name'] )
					) {
						// Get file content.
						$wpda_import = new WPDA_Import_File( $_FILES['filename']['tmp_name'] );

						// Check if errors should be shown.
						$hide_errors = isset( $_REQUEST['hide_errors'] ) ?
							sanitize_text_field( wp_unslash( $_REQUEST['hide_errors'] ) ) : 'off'; // input var okay.

						// Process file content.
						$wpda_import->import( $this->schema_name, $this->table_name, $hide_errors );
					}
				} else {
					// File upload failed: inform user.
					$msg = new WPDA_Message_Box(
						[
							'message_text'           => __( 'File upload failed', 'wp-data-access' ),
							'message_type'           => 'error',
							'message_is_dismissible' => false,
						]
					);
					$msg->box();
				}
			}

		}

		/**
		 * Adds an import button
		 *
		 * @param string $label Button label.
		 * @param string $class Button CSS class.
		 *
		 * @since   1.0.0
		 *
		 */
		public function add_button( $label = '', $class = 'page-title-action' ) {
			$storage_type =
				WPDA::is_wpda_table( $this->table_name ) ?
					__( 'current respository table', 'wp-data-access' ) :
					__( 'table', 'wp-data-access' ) . " {$this->table_name}" ;
			$title        = sprintf( __( 'Allows only imports into %s', 'wp-data-access' ), $storage_type );
			?>
			<button type="button"
				   	onclick="jQuery('#upload_file_container').show()"
				   	class="wpda_tooltip <?php echo esc_attr( $class ); ?>"
					title="<?php echo $title; ?>">
				<span class="material-icons wpda_icon_on_button">cloud_upload</span>
				<?php echo '' === $label ? __( 'Import', 'wp-data-access' ) : $label; ?>
			</button>
			<?php
		}

		/**
		 * Adds an import container
		 *
		 * The container contains an upload form. The container is hidden by default. When the button created in
		 * {@see WPDA_Import::add_button()} is clicked, the container is shown.
		 *
		 * @since   1.0.0
		 */
		public function add_container() {

			$file_uploads_enabled = @ini_get( 'file_uploads' );

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

			<div id="upload_file_container" style="display: none">
				<div>&nbsp;</div>
				<div>
					<?php if ( $file_uploads_enabled ) { ?>
						<form id="form_import_table" method="post" action="<?php echo esc_attr( $this->url ); ?>"
							  enctype="multipart/form-data">
							<fieldset class="wpda_fieldset">
								<legend>
								<span>
									<?php echo __( sprintf( 'SUPPORTS ONLY DATA IMPORTS FOR TABLE `%s`', esc_attr( $this->table_name ) ), 'wp-data-access' ); ?>
								</span>
								</legend>
								<p class="wpda_list_indent">
									<?php
									echo __( 'Supports only file type', 'wp-data-access' ) . ' <strong>sql</strong>. ' . __( 'Maximum supported file size is', 'wp-data-access' ) . ' <strong>' . @ini_get( 'upload_max_filesize' ) . '</strong>.';
									?>
								</p>
								<input type="file" name="filename" id="filename" class="wpda_tooltip" accept=".sql">
								<button type="submit"
									   	class="button button-secondary"
									   	onclick="return before_submit_upload()">
									<span class="material-icons wpda_icon_on_button">code</span>
									<?php echo __( 'Import file', 'wp-data-access' ); ?>
								</button>
								<button type="button"
									   	onclick="jQuery('#upload_file_container').hide()"
									   	class="button button-secondary">
									<span class="material-icons wpda_icon_on_button">cancel</span>
									<?php echo __( 'Cancel', 'wp-data-access' ); ?>
								</button>
								<label style="vertical-align:baseline;">
									<input type="checkbox" name="hide_errors" style="vertical-align:sub;" checked>
									<?php echo __( 'Hide errors', 'wp-data-access' ); ?>
								</label>
								<input type="hidden" name="action" value="import">
								<?php wp_nonce_field( "wpda-import-{$this->table_name}", '_wpnonceimport', false ); ?>
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
