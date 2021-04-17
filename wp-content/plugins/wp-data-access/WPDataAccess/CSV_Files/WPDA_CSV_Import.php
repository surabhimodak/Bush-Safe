<?php

namespace WPDataAccess\CSV_Files {

	use WPDataAccess\Connection\WPDADB;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;
	use WPDataAccess\Plugin_Table_Models\WPDA_CSV_Uploads_Model;
	use WPDataAccess\Utilities\WPDA_Message_Box;
	use WPDataAccess\List_Table\WPDA_List_View;
	use WPDataAccess\WPDA;

	class WPDA_CSV_Import {

		protected $schema_name = null;
		protected $action      = null;

		public function __construct() {
			global $wpdb;
			$this->schema_name = $wpdb->dbname;

			$this->action =
				isset( $_REQUEST['action'] ) ?
					sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : null; // input var okay.
		}

		public function show() {
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline">
					<span><?php echo __( 'Import CSV ' ); ?></span>
					<a href="https://wpdataaccess.com/docs/documentation/data-explorer/import-csv-file/"
					   target="_blank"
					   class="wpda_tooltip"
					   title="Plugin Help - open a new tab or window">
						<span class="dashicons dashicons-editor-help"
							  style="text-decoration:none;vertical-align:top;font-size:30px;">
						</span>
					</a>
				</h1>&nbsp;
				<form method="post"
					  action="?page=wpda&page_action=wpda_import_csv"
					  style="display: inline-block; vertical-align: baseline;">
					<button type="submit" class="page-title-action">
						<span class="material-icons wpda_icon_on_button">cloud_upload</span>
						<?php echo __( 'Upload new CSV file' ); ?>
					</button>
					<input type="hidden"
						   name="action"
						   value="upload"
					/>
				</form>
				<?php
				if ( 'mapping' === $this->action || 'import_start' === $this->action || 'import' === $this->action || 'reload' === $this->action ) {
					?>
					<form method="post"
						  action="?page=wpda&page_action=wpda_import_csv"
						  style="display: inline-block; vertical-align: baseline;">
						<button type="submit" class="page-title-action">
							<span class="material-icons wpda_icon_on_button">arrow_back</span>
							<?php echo __( 'CSV file list' ); ?>
						</button>
					</form>
					<?php
				}
				?>
				<form method="post"
					  action="?page=wpda"
					  style="display: inline-block; vertical-align: baseline;">
					<button type="submit" class="page-title-action">
						<span class="material-icons wpda_icon_on_button">arrow_back</span>
						<?php echo __( 'Data Explorer' ); ?>
					</button>
				</form>
				<br/>
				<?php $this->show_body(); ?>
			</div>
			<?php
		}

		protected function show_body() {
			if ( 'upload' === $this->action ) {
				if ( isset( $_REQUEST['action2'] ) && 'save' === $_REQUEST['action2'] ) {
					$this->upload_file();
				} else {
					$this->show_body_upload();
				}
			} elseif ( 'mapping' === $this->action ) {
				$this->show_body_mapping();
			} elseif ( 'import_start' === $this->action ) {
				$this->show_body_import_start();
			} elseif ( 'import' === $this->action ) {
				$this->show_body_import();
			} elseif ( 'reload' === $this->action ) {
				$this->show_body_reload();
			} else {
				$this->show_body_main();
			}
		}

		protected function show_body_mapping() {
			$csv_mapping = new WPDA_CSV_Mapping();
			$csv_mapping->show();
		}

		protected function show_body_main() {
			$csv_list_view = new WPDA_List_View(
				[
					'page_hook_suffix'     => 'CSV',
					'table_name'           => WPDA_CSV_Uploads_Model::get_base_table_name(),
					'list_table_class'     => 'WPDataAccess\\CSV_Files\\WPDA_CSV_List_Table',
					'edit_form_class'      => 'WPDataAccess\\Simple_Form\\WPDA_Simple_Form',
					'subtitle'             => '',
				]
			);
			$csv_list_view->show();
		}

		protected function show_body_upload() {
			$csv_id =
				isset( $_REQUEST['csv_id'] ) ?
					sanitize_text_field( wp_unslash( $_REQUEST['csv_id'] ) ) : ''; // input var okay.
			$csv_name =
				isset( $_REQUEST['csv_name'] ) ?
					sanitize_text_field( wp_unslash( $_REQUEST['csv_name'] ) ) : ''; // input var okay.
			?>
			<br/>
			<fieldset class="wpda_fieldset">
				<legend>
					<?php echo __( 'Select a file and click upload', 'wp-data-access' ); ?>
				</legend>
				<form id="form_import_table"
					  method="post"
					  action="?page=wpda&page_action=wpda_import_csv"
					  enctype="multipart/form-data">
					<label for="csv_name">
						Import name
						<input id="csv_name"
							   name="csv_name"
							   type="text"
							   value="<?php echo esc_attr( $csv_name ); ?>"
							   <?php if ( '' !== $csv_name ) { echo 'disabled'; } ?>
						/>
						<?php if ( '' !== $csv_name ) { ?>
							<input id="csv_name"
								   name="csv_name"
								   type="hidden"
								   value="<?php echo esc_attr( $csv_name ); ?>"
							/>
						<?php } ?>
					</label>
					<br/><br/>
					<input type="file" name="filename" id="filename" accept=".csv">
					<button type="submit"
						   	class="button button-secondary"
						   	onclick="if (jQuery('#csv_name').val()===''||jQuery('#filename').val()==='') { alert('Please enter an import name and select a file'); return false; }"
					>
						<span class="material-icons wpda_icon_on_button">check</span>
						<?php echo __( 'Upload', 'wp-data-access' ); ?>
					</button>
					<button type="button"
						   	onclick="window.location.href='?page=wpda&page_action=wpda_import_csv'"
						   	class="button button-secondary"
					>
						<span class="material-icons wpda_icon_on_button">cancel</span>
						<?php echo __( 'Cancel', 'wp-data-access' ); ?>
					</button>
					<input type="hidden"
						   name="action"
						   value="upload"
					/>
					<input type="hidden"
						   name="action2"
						   value="save"
					/>
					<input type="hidden"
						   name="csv_id"
						   value="<?php echo  esc_attr( $csv_id ); ?>"
				   />
					<?php wp_nonce_field( "wpda-import-csv-{$this->schema_name}", '_wpnonce', false ); ?>
				</form>
			</fieldset>
			<?php
		}

		protected function show_body_import_start() {
			$csv_id   = isset( $_REQUEST['csv_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['csv_id'] ) ) : '';
			$csv_name = isset( $_REQUEST['csv_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['csv_name'] ) ) : '';
			$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
			$page     = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
			?>
			<style type="text/css">
				.wpda-label {
                    font-weight: bold;
					display: inline-block;
					width: 110px;
				}
				.wpda-import-form {
                    margin-left: 115px;
                    width: 110px;
				}
			</style>
			<br/>
			<form action="?page=<?php echo esc_attr( $page ); ?>&page_action=wpda_import_csv" method="post">
			<fieldset class="wpda_fieldset">
				<legend>
					Start Import
				</legend>
				<p>
					<label class="wpda-label">Import Name</label>
					<input type="text" value="<?php echo esc_attr( $csv_name ); ?>" readonly />
				</p>
				<p>
					<label class="wpda-label"></label>
					<label style="padding-left:5px">
						<input type="checkbox" name="truncate_table" />
						<strong>Truncate table before import?</strong> (This will remove all rows from your table and cannot be undone!)
					</label>
				</p>
				<p class="wpda-import-form">
					<input type='hidden' name='action' value='import' />
					<input type='hidden' name='csv_id' value='<?php echo esc_attr( $csv_id ); ?>' />
					<input type='hidden' name='_wpnonce' value='<?php echo esc_attr( $wp_nonce ); ?>'>
					<input type="submit" class="button" value="Start Import" />
				</p>
			</fieldset>
			</form>
			<?php
		}

		protected function show_body_import() {
			$csv_id =
				isset( $_REQUEST['csv_id'] ) ?
					sanitize_text_field( wp_unslash( $_REQUEST['csv_id'] ) ) : ''; // input var okay.

			// Security check
			$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '?'; // input var okay.
			if ( ! wp_verify_nonce( $wp_nonce, "wpda-import-csv-{$csv_id}" ) ) {
				wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
			}

			$truncate_table =
				isset( $_REQUEST['truncate_table'] ) ?
					sanitize_text_field( wp_unslash( $_REQUEST['truncate_table'] ) ) : 'off'; // input var okay.

			echo '<p>Reading CSV file info...</p>';
			$dbrow = WPDA_CSV_Uploads_Model::query( $csv_id );

			global $wpdb;
			if ( 1 === $wpdb->num_rows ) {
				if ( ! isset( $dbrow[0]->csv_real_file_name ) ) {
					echo '<p style="font-weight: bold">ERROR: No CSV file found for this import</p>';
					return;
				}

				if ( ! isset( $dbrow[0]->csv_mapping ) ) {
					echo '<p style="font-weight: bold">ERROR: Cannot import CSV file without column mapping</p>';
					return;
				}

				echo '<p>Validating column mapping...</p>';
				$upload_dir         = WPDA_CSV_Import::get_plugin_upload_dir();
				$file_name          = $upload_dir . $dbrow[0]->csv_real_file_name;
				$mapping            = json_decode( $dbrow[0]->csv_mapping, true );
				$delimiter          = isset( $mapping['settings']['delimiter'] ) ? $mapping['settings']['delimiter'] : ',';
				$date_format        = isset( $mapping['settings']['date_format'] ) ? $mapping['settings']['date_format'] : '%Y-%m-%d';
				$has_header_columns = isset( $mapping['settings']['has_header_columns'] ) ? $mapping['settings']['has_header_columns'] : true;
				$schema_name        = isset( $mapping['database']['schema_name'] ) ? esc_attr( $mapping['database']['schema_name'] ) : '';
				$table_name         = isset( $mapping['database']['table_name'] ) ? esc_attr( $mapping['database']['table_name'] ) : '';
				$table_columns      = isset( $mapping['columns'] ) ? $mapping['columns'] : [];

				if ( 'on' === $truncate_table ) {
					// Truncate table
					$wpdadb = WPDADB::get_db_connection( $schema_name );
					$wpdadb->query( "truncate table `$table_name`" );
					echo "<p><strong>Table `{$table_name}` truncated...</strong></p>";
				}

				$columns_inserted = '';
				foreach ( $table_columns as $table_column ) {
					$columns_inserted .= $table_column . ',';
				}
				$columns_inserted = substr( $columns_inserted, 0, strlen( $columns_inserted) - 1 );

				$data_type         = [];
				$data_type_before  = [];
				$data_type_after   = [];
				$wpda_list_columns = WPDA_List_Columns_Cache::get_list_columns( $schema_name, $table_name );
				$column_data_types = $wpda_list_columns->get_table_columns();
				foreach ( $column_data_types as $column ) {
					$data_type[ $column['column_name'] ] = WPDA::get_type( $column['data_type'] );
					switch( $data_type[ $column['column_name'] ] ) {
						case 'number':
							$data_type_before[ $column['column_name'] ] = '';
							$data_type_after[ $column['column_name'] ] = '';
							break;
						case 'date':
							$data_type_before[ $column['column_name'] ] = "str_to_date('";
							$data_type_after[ $column['column_name'] ] = "','$date_format')";
							break;
						default:
							$data_type_before[ $column['column_name'] ] = "'";
							$data_type_after[ $column['column_name'] ] = "'";
					}
				}

				echo '<p>Enabling buffering...</p>';
				set_time_limit( 0 );
				@ini_set( 'zlib.output_compression', false );
				@ini_set( 'implicit_flush', true );
				@ini_set( 'output_buffering', true );
				@ini_set( 'display_errors', false );
				ob_implicit_flush( true );

				echo '<p>Reading CSV file...</p>';
				@ini_set( 'auto_detect_line_endings', true );
				if ( false !== ( $fp = fopen( $file_name, 'rb' ) ) ) {
					$wpdadb = WPDADB::get_db_connection( $schema_name );
					if ( null !== $wpdadb ) {
						$row      = 0;
						$inserted = 0;
						$errors   = 0;
						echo '<p>Connecting to database...</p>';
						$suppress_errors = $wpdadb->suppress_errors( true );
						while ( false !== ( $data = fgetcsv( $fp, 0, $delimiter, '"' ) ) ) {
							if ( 0 === $row && 'true' === $has_header_columns ) {
								// Skip first row
							} else {
								// Prepare insert array
								$wpda_insert_column_values = [];
								$row_values_valid          = true;
								for ( $column = 0; $column < sizeof( $data ); $column++ ) {
									if ( isset( $table_columns[ $column ] ) ) {
										// Add column to array
										if ( isset( $table_columns[ $column ] ) ) {
											if (
												'number' === $data_type[ $table_columns[ $column ] ] ||
												'date' === $data_type[ $table_columns[ $column ] ]
											) {
												if ( null === $data[ $column ] || 'null' === $data[ $column ] || '' === $data[ $column ] ) {
													$wpda_insert_column_values[ $table_columns[ $column ] ] = null;
												} else {
													if ( 'number' === $data_type[ $table_columns[ $column ] ] ) {
														$wpda_insert_column_values[ $table_columns[ $column ] ] = $data[ $column ];
													} else {
														try {
															$date_value   = substr( $data[ $column ], 0, 10 );
															$convert_date = \DateTime::createFromFormat( str_replace( '%', '', $date_format ), $date_value );
															if ( false === $convert_date ) {
																echo "<div>ERROR: Cannot convert {$date_value} to date (using format {$date_format})</div>";
																$row_values_valid = false;
															} else {
																$wpda_insert_column_values[ $table_columns[ $column ] ] = $convert_date->format( 'Y-m-d' );
															}
														} catch ( \Exception $e ) {
															echo "<div>ERROR: Cannot convert {$date_value} to date (using format {$date_format})</div>";
															$row_values_valid = false;
														}
													}
												}
											} else {
												$wpda_insert_column_values[ $table_columns[ $column ] ] = $data[ $column ];
											}
										}
									}
								}
								if ( $row_values_valid ) {
									// var_dump( $wpda_insert_column_values );
									// Insert row
									$result = $wpdadb->insert(
										$table_name,
										$wpda_insert_column_values
									); // db call ok; no-cache ok.
									if ( $result === 1 ) {
										$inserted++;
									} else {
										// Try to insert with plain sql
										$insert = "insert into `{$wpdadb->dbname}`.`{$table_name}` ";
										$insert .= "({$columns_inserted}) values (";
										for ( $column = 0; $column < sizeof( $data ); $column++ ) {
											if ( isset( $table_columns[ $column ] ) ) {
												if (
													'' === $data[ $column ] &&
													(
														'number' === $data_type[ $table_columns[ $column ] ] ||
														'date' === $data_type[ $table_columns[ $column ] ]
													)
												) {
													$insert .=
														'null,';
												} else {
													$insert .=
														$data_type_before[ $table_columns[ $column ] ] .
														str_replace( "'", "\'", $data[ $column ] ) .
														$data_type_after[ $table_columns[ $column ] ] .
														',';
												}
											}
										}
										$insert = substr( $insert, 0, strlen( $insert ) - 1 );
										$insert .= ")";
										// var_dump( $insert );
										if ( false === $wpdadb->query( $insert ) ) {
											if ( '' === $wpdadb->last_error ) {
												echo "Error: {$insert}<br/>";
											} else {
												echo "Error: {$wpdadb->last_error}<br/>";
											}
											$errors++;
										} else {
											$inserted++;
										}
									}
								} else {
									$errors++;
								}
							}
							$row++;
						}
						$wpdadb->suppress_errors( $suppress_errors );
					}
					fclose( $fp );

					$row--;
					echo "<p style='font-weight: bold'>Import ready!</p>";
					echo "{$row} rows processed<br/>";
					echo "{$inserted} rows inserted<br/>";
					echo "{$errors} rows with errors<br/>";
				} else {
					echo '<p style="font-weight: bold">ERROR: File not found</p>';
				}
			}
		}

		protected function show_body_reload() {
			$csv_id =
				isset( $_REQUEST['csv_id'] ) ?
					sanitize_text_field( wp_unslash( $_REQUEST['csv_id'] ) ) : ''; // input var okay.

			// Security check
			$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '?'; // input var okay.
			if ( ! wp_verify_nonce( $wp_nonce, "wpda-reload-csv-{$csv_id}" ) ) {
				wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
			}

			$this->show_body_upload();
		}

		protected function upload_file() {
			// Security check
			$wp_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '?'; // input var okay.
			if ( ! wp_verify_nonce( $wp_nonce, "wpda-import-csv-{$this->schema_name}" ) ) {
				wp_die( __( 'ERROR: Not authorized', 'wp-data-access' ) );
			}

			if ( isset( $_FILES['filename'] ) && isset( $_REQUEST['csv_name'] ) ) {

				if ( UPLOAD_ERR_OK === $_FILES['filename']['error']
				     && is_uploaded_file( $_FILES['filename']['tmp_name'] )
				) {
					echo '<br/>';
					echo __( 'Uploading file', 'wp-data-access' ) . " <strong>{$_FILES['filename']['name']}</strong>...";
					echo '<br/><br/>';

					$upload_dir     = self::get_plugin_upload_dir();
					$temp_file_name = $_FILES['filename']['tmp_name'];
					$real_file_name = 'wpda_csv_upload_' . date( 'YmdHis' ) . '.csv';
					$orig_file_name = $_FILES['filename']['name'];

					// Process file and save a local copy
					$fp = $this->file_pointer = fopen( $temp_file_name, 'rb' );
					if ( false !== $this->file_pointer ) {
						if ( ! is_dir( $upload_dir ) ) {
							mkdir( $upload_dir, 0755, true );
						}

						$fw = fopen( $upload_dir . "{$real_file_name}", 'w' );
						while ( ! feof( $this->file_pointer ) ) {
							$file_content = fread( $this->file_pointer, 1024 );
							fwrite( $fw, $file_content );
						}
					}
					fclose( $fp );
					fclose( $fw );

					echo __( 'Saving file info...', 'wp-data-access' );
					echo '<br/><br/>';

					$csv_id =
						isset( $_REQUEST['csv_id'] ) ?
							sanitize_text_field( wp_unslash( $_REQUEST['csv_id'] ) ) : ''; // input var okay.
					$csv_name = sanitize_text_field( wp_unslash( $_REQUEST['csv_name'] ) ); // input var okay.

					if ( '' === $csv_id ) {
						// New CSV import
						$result = WPDA_CSV_Uploads_Model::insert( $csv_name, $real_file_name, $orig_file_name );
					} else {
						// Reload CSV
						$oldrow = WPDA_CSV_Uploads_Model::query( $csv_id );
						$result = WPDA_CSV_Uploads_Model::update( $csv_id, $real_file_name, $orig_file_name );
						if ( $result ) {
							// Remove old file
							if ( isset( $oldrow[0]->csv_real_file_name ) ) {
								unlink( self::get_plugin_upload_dir() . $oldrow[0]->csv_real_file_name );
							}
						}
					}
					if ( false === $result ) {
						$msg = new WPDA_Message_Box(
							[
								'message_text'           => __( 'Processing CSV file failed', 'wp-data-access' ),
								'message_type'           => 'error',
								'message_is_dismissible' => false,
							]
						);
						$msg->box();
					} else {
						$wp_nonce_csv_id = $csv_id==='' ? $result : $csv_id;
						$wp_nonce_action = "wpda-mapping-{$wp_nonce_csv_id}";
						$wp_nonce        = esc_attr( wp_create_nonce( $wp_nonce_action ) );
						?>
						<strong>Upload successful</strong>
						<br/><br/>
						<form method="post"
							  action="?page=wpda&page_action=wpda_import_csv"
							  style="display: inline-block; vertical-align: baseline;">
							<input type="hidden"
								   name="csv_id"
								   value="<?php echo $csv_id==='' ? $result : $csv_id; ?>"
							>
							<input type="hidden"
								   name="action"
								   value="mapping"
							>
							<input type="submit"
								   class="page-title-action"
								   style="margin-left: 0;"
								   value="<?php echo __( 'Column mapping' ); ?>"
							/>
							<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $wp_nonce ); ?>" />
						</form>&nbsp;
						<form method="post"
							  action="?page=wpda&page_action=wpda_import_csv"
							  style="display: inline-block; vertical-align: baseline;">
							<input type="submit"
								   class="page-title-action"
								   style="margin-left: 0;"
								   value="<?php echo __( 'CSV file list' ); ?>"
							/>
						</form>
						<?php
					}
				}
			} else {
				// File upload failed: inform user
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

		public static function get_plugin_upload_dir() {
			$uploads = wp_upload_dir();

			return $uploads['basedir'] . DIRECTORY_SEPARATOR . 'wp-data-access' . DIRECTORY_SEPARATOR;
		}

	}

}