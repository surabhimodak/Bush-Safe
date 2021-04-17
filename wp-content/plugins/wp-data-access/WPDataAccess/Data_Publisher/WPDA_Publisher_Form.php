<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Data_Publisher
 */

namespace WPDataAccess\Data_Publisher {

	use WPDataAccess\Data_Dictionary\WPDA_Dictionary_Lists;
	use WPDataAccess\Data_Dictionary\WPDA_List_Columns_Cache;
	use WPDataAccess\Data_Tables\WPDA_Data_Tables;
	use WPDataAccess\Plugin_Table_Models\WPDA_Table_Settings_Model;
	use WPDataAccess\Simple_Form\WPDA_Simple_Form;
	use WPDataAccess\Simple_Form\WPDA_Simple_Form_Item_Boolean;
	use WPDataAccess\Simple_Form\WPDA_Simple_Form_Item_Enum;
	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Publisher_Form extends WPDA_Simple_Form
	 *
	 * Data entry form which allows users to create, update and test publications. A publication consists of a database
	 * table, a number of columns and some options. A shortcode can be generated for a publication. The shortcode can
	 * be copied to the clipboard and from there pasted in a WordPress post or page. The shortcode is used to add a
	 * dynamic HTML table to a post or page that supports searching, pagination and sorting. Tables are created with
	 * jQuery DataTables.
	 *
	 * @author  Peter Schulz
	 * @since   2.0.15
	 */
	class WPDA_Publisher_Form extends WPDA_Simple_Form {

		protected $hyperlinks = [];

		/**
		 * WPDA_Publisher_Form constructor.
		 *
		 * @param string $schema_name Database schema name
		 * @param string $table_name Database table name
		 * @param object $wpda_list_columns Handle to instance of WPDA_List_Columns
		 * @param array  $args
		 */
		public function __construct( $schema_name, $table_name, &$wpda_list_columns, $args = [] ) {
			// Add column labels.
			$args['column_headers'] = [
				'pub_id'                          => __( 'Pub ID', 'wp-data-accesss' ),
				'pub_name'                        => __( 'Publication Name', 'wp-data-accesss' ),
				'pub_schema_name'                 => __( 'Database', 'wp-data-access' ),
				'pub_table_name'                  => __( 'Table Name', 'wp-data-accesss' ),
				'pub_column_names'                => __( 'Column Names (* = all)', 'wp-data-accesss' ),
				'pub_format'                      => __( 'Column Labels', 'wp-data-accesss' ),
				'pub_responsive'                  => __( 'Output', 'wp-data-accesss' ),
				'pub_responsive_popup_title'      => __( 'Popup Title', 'wp-data-accesss' ),
				'pub_responsive_cols'             => __( 'Number Of Columns', 'wp-data-accesss' ),
				'pub_responsive_type'             => __( 'Type', 'wp-data-accesss' ),
				'pub_responsive_modal_hyperlinks' => __( 'Hyperlinks On Modal', 'wp-data-access' ),
				'pub_responsive_icon'             => __( 'Show Icon', 'wp-data-accesss' ),
				'pub_default_where'               => __( 'WHERE Clause', 'wp-data-access' ),
				'pub_default_orderby'             => __( 'Default order/by', 'wp-data-access' ),
				'pub_show_advanced_settings'      => __( 'Show Advanced Settings', 'wp-data-access' ),
				'pub_table_options_searching'     => __( 'Allow searching?', 'wp-data-access' ),
				'pub_table_options_ordering'      => __( 'Allow ordering?', 'wp-data-access' ),
				'pub_table_options_paging'        => __( 'Allow paging?', 'wp-data-access' ),
				'pub_table_options_nl2br'         => __( 'NL > BR', 'wp-data-access' ),
				'pub_sort_icons'                  => __( 'Sort icons', 'wp-data-access' ),
				'pub_table_options_advanced'      => __( 'Table options (advanced)', 'wp-data-access' ),
			];

			$this->check_table_type = false;
			$this->title            = __( 'Data Publisher', 'wp-data-access' );
			$args['help_url']       = 'https://wpdataaccess.com/docs/documentation/data-publisher/';
			if ( wpda_fremius()->is_premium() ) {
				$this->title = __( 'Premium', 'wp-data-access' ) . ' ' . $this->title;
			}

			parent::__construct( $schema_name, $table_name, $wpda_list_columns, $args );
		}

		/**
		 * Overwrites method add_buttons
		 */
		public function add_buttons() {
			$index       = $this->get_item_index( 'pub_id' );
			$pub_id_item = $this->form_items[ $index ];
			$pub_id      = $pub_id_item->get_item_value();
			$disabled    = 'new' === $this->action ? 'disabled' : '';
			?>
			<a href="javascript:void(0)"
			   onclick="jQuery('#data_publisher_test_container_<?php echo esc_html( $pub_id ); ?>').toggle()"
			   class="button wpda_tooltip <?php echo $disabled; ?>"
			   title="Test publication"
			>
				<span class="material-icons wpda_icon_on_button">bug_report</span><?php echo __( 'Test', 'wp-data-access' ); ?>
			</a>
			<?php
			$this->show_shortcode( $pub_id );
		}

		/**
		 * Overwrites method prepare_items
		 *
		 * @param bool $set_back_form_values
		 */
		public function prepare_items( $set_back_form_values = false ) {
			parent::prepare_items( $set_back_form_values );

			global $wpdb;

			// Get available databases
			$schema_names = WPDA_Dictionary_Lists::get_db_schemas();
			$databases    = [];
			foreach ( $schema_names as $schema_name ) {
				array_push( $databases, $schema_name['schema_name'] );
			}

			$i = 0;
			foreach ( $this->form_items as $form_item ) {
				// Prepare listbox for column pub_schema_name
				if ( $form_item->get_item_name() === 'pub_schema_name' ) {
					if ( '' === $form_item->get_item_value() || null === $form_item->get_item_value() ) {
						$form_item->set_item_value( WPDA::get_user_default_scheme() );
					}
					$form_item->set_enum( $databases );
					$this->form_items[ $i ] = new WPDA_Simple_Form_Item_Enum( $form_item );
				}

				// Prepare listbox for column pub_table_name
				if ( $form_item->get_item_name() === 'pub_table_name' ) {
					$this->form_items[ $i ] = new WPDA_Simple_Form_Item_Enum( $form_item );
				}

				// Set default value for popup title
				if ( $form_item->get_item_name() === 'pub_responsive_popup_title' ) {
					$form_item->set_item_default_value( __( 'Row details', 'wp-data-access' ) );
				}

				// Prepare listbox for column pub_responsive
				if ( $form_item->get_item_name() === 'pub_responsive' ) {
					$form_item->set_enum( [ 'Responsive', 'Flat' ] );
					$form_item->set_enum_options( [ 'Yes', 'No' ] );
				}

				// Prepare selection for column pub_column_names
				if ( $form_item->get_item_name() === 'pub_column_names' ) {
					$title = __( 'Select columns shown in publication', 'wp-data-access' );
					$form_item->set_item_hide_icon( true );
					$form_item->set_item_js(
						'jQuery("#pub_column_names").parent().parent().find("td.icon").append("<a id=\'select_columns\' class=\'button wpda_tooltip\' href=\'javascript:void(0)\' title=\'' . $title . '\' onclick=\'select_columns()\'>' .
						'<span class=\'material-icons wpda_icon_on_button\'>view_list</span>' . __( 'Select', 'wp-data-access' ) .
						'</a>");'
					);
				}

				// Prepare column label settings
				if ( $form_item->get_item_name() === 'pub_format' ) {
					$title = __( 'Define columns for publication (not necessary if already defined in Data Explorer table settings)', 'wp-data-access' );
					$form_item->set_item_hide_icon( true );
					$form_item->set_item_class( 'hide_item' );
					$form_item->set_item_js(
						'jQuery("#pub_format").parent().parent().find("td.data").append("<a id=\'format_columns\' class=\'button wpda_tooltip\' href=\'javascript:void(0)\' title=\'' . $title . '\' onclick=\'format_columns()\'>' .
						'<span class=\'material-icons wpda_icon_on_button\'>label</span>' . __( 'Click to define column labels', 'wp-data-access' ) .
						'</a>");'
					);
				}

				if (
					'pub_responsive_popup_title' === $form_item->get_item_name() ||
					'pub_responsive_cols' === $form_item->get_item_name() ||
					'pub_responsive_type' === $form_item->get_item_name() ||
					'pub_responsive_modal_hyperlinks' === $form_item->get_item_name() ||
					'pub_responsive_icon' === $form_item->get_item_name() ||
					'pub_default_where' === $form_item->get_item_name() ||
					'pub_default_orderby' === $form_item->get_item_name() ||
					'pub_table_options_advanced' === $form_item->get_item_name() ||
					'pub_sort_icons' === $form_item->get_item_name()
				) {
					$form_item->set_hide_item_init( true );
				}

				if ( 'pub_table_options_advanced' === $form_item->get_item_name() ) {
					if ( '' === $form_item->get_item_value() || null === $form_item->get_item_value() ) {
						$form_item->set_item_value('{}');
					}
				}

				if (
					'pub_table_options_searching' === $form_item->get_item_name() ||
					'pub_table_options_ordering' === $form_item->get_item_name() ||
					'pub_table_options_paging' === $form_item->get_item_name()
				) {
					$form_item->set_hide_item_init( true );
					$form_item->checkbox_value_on = 'on';
					if ( 'new' === $this->action ) {
						$form_item->set_item_value( 'on' );
					}
					$this->form_items[ $i ] = new WPDA_Simple_Form_Item_Boolean( $form_item );
				}

				if ( 'pub_table_options_nl2br' === $form_item->get_item_name() ) {
					$form_item->set_hide_item_init( true );
					$form_item->checkbox_value_on = 'on';
					$this->form_items[ $i ] = new WPDA_Simple_Form_Item_Boolean( $form_item );
				}

				$i ++;
			}
		}

		/**
		 * Overwrites method show
		 *
		 * @param bool   $allow_save
		 * @param string $add_param
		 */
		public function show( $allow_save = true, $add_param = '' ) {
			parent::show( $allow_save, $add_param );

			$index       = $this->get_item_index( 'pub_id' );
			$pub_id_item = $this->form_items[ $index ];
			$pub_id      = $pub_id_item->get_item_value();

			$index            = $this->get_item_index( 'pub_schema_name' );
			$schema_name_item = $this->form_items[ $index ];
			$schema_name      = $schema_name_item->get_item_value();

			$index           = $this->get_item_index( 'pub_table_name' );
			$table_name_item = $this->form_items[ $index ];
			$table_name      = $table_name_item->get_item_value();

			$table_columns = WPDA_List_Columns_Cache::get_list_columns( $schema_name, $table_name );
			$columns       = [];
			foreach ( $table_columns->get_table_columns() as $table_column ) {
				array_push( $columns, $table_column['column_name'] );
			}

			$column_labels = $table_columns->get_table_column_headers();

			$json_editing = WPDA::get_option( WPDA::OPTION_DP_JSON_EDITING );

			$wpda_table_settings_db = WPDA_Table_Settings_Model::query( $table_name, $schema_name );
			if ( isset( $wpda_table_settings_db[0]['wpda_table_settings'] ) ) {
				$wpda_table_settings = json_decode( $wpda_table_settings_db[0]['wpda_table_settings'] );
				if ( isset( $wpda_table_settings->hyperlinks ) ) {
					foreach ( $wpda_table_settings->hyperlinks as $hyperlink ) {
						$hyperlink_label = isset( $hyperlink->hyperlink_label ) ? $hyperlink->hyperlink_label : '';
						$hyperlink_html  = isset( $hyperlink->hyperlink_html ) ? $hyperlink->hyperlink_html : '';
						if ( $hyperlink_label !== '' && $hyperlink_html !== '' ) {
							array_push( $this->hyperlinks, $hyperlink_label );
						}
					}
				}
			}
			?>
			<script type='text/javascript'>
				function set_responsive_columns() {
					if (jQuery('#pub_responsive').val() == 'Yes') {
						// Show responsive settings
						jQuery('#pub_responsive_popup_title').parent().parent().show();
						jQuery('#pub_responsive_cols').parent().parent().show();
						jQuery('#pub_responsive_type').parent().parent().show();
						jQuery('#pub_responsive_modal_hyperlinks').parent().parent().show();
						jQuery('#pub_responsive_icon').parent().parent().show();
					} else {
						// Hide responsive settings
						jQuery('#pub_responsive_popup_title').parent().parent().hide();
						jQuery('#pub_responsive_cols').parent().parent().hide();
						jQuery('#pub_responsive_type').parent().parent().hide();
						jQuery('#pub_responsive_modal_hyperlinks').parent().parent().hide();
						jQuery('#pub_responsive_icon').parent().parent().hide();
					}
				}

				function set_advanced_settings() {
					if (jQuery('#pub_show_advanced_settings_chk').is(':checked')) {
						// Show advanced settings
						jQuery('#pub_default_where').parent().parent().show();
						jQuery('#pub_default_orderby').parent().parent().show();
						jQuery('#pub_table_options_searching').parent().parent().parent().show();
						jQuery('#pub_table_options_advanced').parent().parent().show();
						jQuery('#pub_sort_icons').parent().parent().show();
					} else {
						// Hide advanced settings
						jQuery('#pub_default_where').parent().parent().hide();
						jQuery('#pub_default_orderby').parent().parent().hide();
						jQuery('#pub_table_options_searching').parent().parent().parent().hide();
						jQuery('#pub_table_options_advanced').parent().parent().hide();
						jQuery('#pub_sort_icons').parent().parent().hide();
					}
				}

				function update_table_list(table_name = '') {
					var url = location.pathname + '?action=wpda_get_tables';
					var data = {
						wpdaschema_name: jQuery('[name="pub_schema_name"]').val()
					};
					jQuery.post(
						url,
						data,
						function (data) {
							jQuery('[name="pub_table_name"]').empty();
							var tables = JSON.parse(data);
							for (var i = 0; i < tables.length; i++) {
								jQuery('<option/>', {
									value: tables[i].table_name,
									html: tables[i].table_name
								}).appendTo('[name="pub_table_name"]');
							}
							if (table_name!=='') {
								jQuery('[name="pub_table_name"]').val(table_name);
							} else {
								jQuery('#pub_column_names').val('*');
								jQuery('#pub_format').val('');
								table_columns = [];
							}
						}
					);
				}

				jQuery(function () {
					pub_table_options_searching = jQuery('#pub_table_options_searching').parent().parent();
					pub_table_options_ordering = jQuery('#pub_table_options_ordering').parent().parent().children();
					pub_table_options_ordering_tr = jQuery(pub_table_options_ordering).parent().parent();
					pub_table_options_paging = jQuery('#pub_table_options_paging').parent().parent().children();
					pub_table_options_paging_tr = jQuery(pub_table_options_paging).parent().parent();
					pub_table_options_nl2br = jQuery('#pub_table_options_nl2br').parent().parent().children();
					pub_table_options_nl2br_tr = jQuery(pub_table_options_nl2br).parent().parent();

					jQuery('<span style="width:10px;display:inline-block;"></span>').appendTo(pub_table_options_searching);
					pub_table_options_ordering.appendTo(pub_table_options_searching);
					jQuery('<span style="width:10px;display:inline-block;"></span>').appendTo(pub_table_options_searching);
					pub_table_options_paging.appendTo(pub_table_options_searching);
					jQuery('<span style="width:10px;display:inline-block;"></span>').appendTo(pub_table_options_searching);
					pub_table_options_nl2br.appendTo(pub_table_options_searching);

					pub_table_options_ordering_tr.remove();
					pub_table_options_paging_tr.remove();
					pub_table_options_nl2br_tr.remove();

					set_responsive_columns();
					set_advanced_settings();

					<?php if ( WPDA::OPTION_DP_JSON_EDITING[1] === $json_editing ) { ?>
					var cm = wp.codeEditor.initialize(jQuery('#pub_table_options_advanced'), cm_settings);
					<?php } ?>

					jQuery('[name="pub_schema_name"]').on('change', function () {
						update_table_list();
					});
					update_table_list('<?php echo esc_attr( $table_name ); ?>');

					jQuery('[name="pub_table_name"]').on('change', function () {
						jQuery('#pub_column_names').val('*');
						jQuery('#pub_format').val('');
						table_columns = [];
					});

					jQuery('#pub_default_where').parent().parent().find('.icon').empty().append('<span title="Enter a valid sql where clause, for example:\nfirst_name like \'Peter%\'" class="material-icons pointer wpda_tooltip">help</span>');
					jQuery('#pub_default_orderby').parent().parent().find('.icon').empty().append('<span title="Format: column number, direction | ...\nExample: 3,desc|5,asc" class="material-icons pointer wpda_tooltip">help</span>');
					jQuery('#pub_table_options_searching').parent().parent().parent().find('.icon').empty().append('<span title="When paging is disabled, all rows are fetch on page load (this implicitly disables server side processing).\n\nEnable NL > BR to automatically convert New Lines to <BR> tags." class="material-icons pointer wpda_tooltip">help</span>');
					jQuery('#pub_table_options_advanced').parent().parent().find('.icon').empty().append('<span title=\'Must be valid JSON like:\n{"option":"value","option2","value2"}\' class="material-icons pointer wpda_tooltip">help</span>');
					jQuery('#pub_table_options_advanced').parent().parent().find('.icon').append('<br/><a href="https://datatables.net/reference/option/" target="_blank" title="Click to check jQuery DataTables website for available\noptions (opens in a new tab or window)" class="dashicons dashicons-external wpda_tooltip" style="margin-top:5px;"></a>');
					jQuery('#pub_sort_icons').parent().parent().find('.icon').empty().append('<span title="default: show default jQuery DataTables sort icons\nplugin: show plugin (material ui) sort icons\nnone: hide sort icons" class="material-icons pointer wpda_tooltip">help</span>');

					jQuery( '.wpda_tooltip' ).tooltip();

					<?php if ( 'view' === $this->action ) { ?>
					jQuery('#format_columns').prop("readonly", true).prop("disabled", true).addClass("disabled");
					jQuery('#select_columns').prop("readonly", true).prop("disabled", true).addClass("disabled");
					<?php } ?>

					jQuery('#pub_responsive').on('change', function () {
						set_responsive_columns();
					});

					jQuery('#pub_show_advanced_settings_chk').on('change', function () {
						set_advanced_settings();
					});
				});

				var no_cols_selected = '* (= show all columns)';

				var table_columns = [];
				<?php
				foreach ( $columns as $column ) {
				?>
				table_columns.push('<?php echo $column; ?>');
				<?php
				}
				?>

				var hyperlinks = [];
				<?php
				if ( null !== $this->hyperlinks && is_array( $this->hyperlinks ) ) {
					foreach ( $this->hyperlinks as $hyperlink ) {
						echo "hyperlinks.push('{$hyperlink}');";
					}
				}
				?>

				function select_available(e) {
					var option = jQuery("#columns_available option:selected");
					var add_to = jQuery("#columns_selected");

					option.remove();
					new_option = add_to.append(option);

					if (jQuery("#columns_selected option[value='*']").length > 0) {
						// Remove ALL from selected list.
						jQuery("#columns_selected option[value='*']").remove();
					}

					jQuery('select#columns_selected option').prop("selected", false);
				}

				function select_selected(e) {
					var option = jQuery("#columns_selected option:selected");
					if (option[0].value === '*') {
						// Cannot remove ALL.
						return;
					}

					var add_to = jQuery("#columns_available");

					option.remove();
					add_to.append(option);

					if (jQuery('select#columns_selected option').length === 0) {
						jQuery("#columns_selected").append(jQuery('<option></option>').attr('value', '*').text(no_cols_selected));
					}

					jQuery('select#columns_available option').prop("selected", false);
				}

				function select_columns(e) {
					if (!(Array.isArray(table_columns) && table_columns.length)) {
						alert("<?php echo __( 'To select columns you need to save your publication first', 'wp-data-access' ); ?>");
						return;
					}

					var columns_available = jQuery(
						'<select id="columns_available" name="columns_available[]" multiple size="8" style="width:200px" onchange="select_available()">' +
						'</select>'
					);
					jQuery.each(table_columns, function (i, val) {
						columns_available.append(jQuery('<option></option>').attr('value', val).text(val));
					});
					for (i=0; i<hyperlinks.length;i++) {
						columns_available.append(jQuery('<option></option>').attr('value', 'wpda_hyperlink_' + i).text('Hyperlink: ' + hyperlinks[i]));
					}

					var currently_select_option = '';
					var currently_select_values = jQuery('#pub_column_names').val();
					if (currently_select_values == '*') {
						currently_select_values = [];
					} else {
						currently_select_values = currently_select_values.split(',');
					}
					if (currently_select_values.length === 0) {
						currently_select_option = '<option value="*">' + no_cols_selected + '</option>';
					} else {
						for (var i = 0; i < currently_select_values.length; i++) {
							if (currently_select_values[i].substr(0,15)==='wpda_hyperlink_') {
								hyperlink_no = currently_select_values[i].substr(15);
								if (hyperlink_no<hyperlinks.length) {
									option_text = 'Hyperlink: ' + hyperlinks[hyperlink_no];
									currently_select_option += '<option value="' + currently_select_values[i] + '">' + option_text + '</option>';
								}
							} else {
								option_text = currently_select_values[i];
								currently_select_option += '<option value="' + currently_select_values[i] + '">' + option_text + '</option>';
							}
						}
					}

					var columns_selected = jQuery(
						'<select id="columns_selected" name="columns_selected[]" multiple size="8" style="width:200px" onchange="select_selected()">' +
						currently_select_option +
						'</select>'
					);

					var dialog_table = jQuery('<table style="width:410px"></table>');
					var dialog_table_row = dialog_table.append(jQuery('<tr></tr>'));
					dialog_table_row.append(jQuery('<td width="50%"></td>').append(columns_available));
					dialog_table_row.append(jQuery('<td width="50%"></td>').append(columns_selected));

					var dialog_text = jQuery('<div style="width:410px"></div>');
					var dialog = jQuery('<div></div>');

					dialog.append(dialog_text);
					dialog.append(dialog_table);

					jQuery(dialog).dialog(
						{
							dialogClass: 'wp-dialog no-close',
							title: 'Add column(s) to publication',
							modal: true,
							autoOpen: true,
							closeOnEscape: false,
							resizable: false,
							width: 'auto',
							buttons: {
								"OK": function () {
									var selected_columns = '';
									jQuery("#columns_selected option").each(
										function () {
											selected_columns += jQuery(this).val() + ',';
										}
									);
									if (selected_columns !== '') {
										selected_columns = selected_columns.slice(0, -1);
									}
									jQuery('#pub_column_names').val(selected_columns);
									jQuery(this).dialog('destroy').remove();
								},
								"Cancel": function () {
									jQuery(this).dialog('destroy').remove();
								}
							}
						}
					);

					// Remove selected columns from available columns
					for (var i = 0; i < currently_select_values.length; i++) {
						jQuery("#columns_available option[value='" + currently_select_values[i] + "']").remove();
					}
				}

				function format_columns() {
					if (!(Array.isArray(table_columns) && table_columns.length)) {
						alert("<?php echo __( 'To format columns you need to save your publication first', 'wp-data-access' ); ?>");
						return;
					}

					var pub_format_json_string = jQuery('#pub_format').val();

					var columns_labels = [];

					if (pub_format_json_string !== '') {
						// Use previously defined formatting
						var pub_format = JSON.parse(pub_format_json_string);
						if (typeof pub_format['pub_format']['column_labels'] !== 'undefined') {
							columns_labels = pub_format['pub_format']['column_labels'];
						}
					} else {
						// Get column labels from table settings
						columns_labels = <?php echo json_encode( $column_labels ); ?>;
					}

					var dialog_table = jQuery('<table></table>');
					dialog_table.append(
						jQuery('<tr></tr>').append(
							jQuery('<th style="text-align:left;"><?php echo __( 'Column Name', 'wp-data-access' ); ?></th>'),
							jQuery('<th style="text-align:left;"><?php echo __( 'Column Label', 'wp-data-access' ); ?></th>'),
						)
					);

					<?php
					foreach ( $table_columns->get_table_columns() as $table_column ) {
						?>
						columns_label = '<?php echo esc_attr( $table_column['column_name'] ); ?>';
						if (typeof columns_labels !== 'undefined') {
							if (columns_label in columns_labels) {
								columns_label = columns_labels[columns_label];
							}
						}
						dialog_table.append(
							jQuery('<tr></tr>').append(
								jQuery('<td style="text-align:left;"><?php echo esc_attr( $table_column['column_name'] ); ?></td>'),
								jQuery('<td style="text-align:left;"><input type="text" class="column_label" name="<?php echo esc_attr( $table_column['column_name'] ); ?>" value="' + columns_label + '"></td>'),
							)
						);
						<?php
					}
					?>

					var dialog_text = jQuery('<div></div>');
					var dialog = jQuery('<div id="define_column_labels"></div>');

					dialog.append(dialog_text);
					dialog.append(dialog_table);

					jQuery(dialog).dialog(
						{
							dialogClass: 'wp-dialog no-close',
							title: 'Define column labels',
							modal: true,
							autoOpen: true,
							closeOnEscape: false,
							resizable: false,
							width: 'auto',
							buttons: {
								"OK": function () {
									// Create JSON from defined column labels
									var column_labels = {};
									jQuery('.column_label').each(
										function () {
											column_labels[jQuery(this).attr('name')] = jQuery(this).val();
										}
									);

									// Write JSON to column pub_format
									pub_format = {
										"pub_format": {
											"column_labels": column_labels
										}
									};
									jQuery('#pub_format').val(JSON.stringify(pub_format));
									jQuery(this).dialog('destroy').remove();
								},
								"Cancel": function () {
									jQuery(this).dialog('destroy').remove();
								}
							}
						}
					);
				}
			</script>
			<?php
			self::show_publication( $pub_id, $table_name );
		}

		protected function show_shortcode( $pub_id ) {
			// Show publication shortcode directly from Data Publisher main page
			$shortcode_enabled =
				'on' === WPDA::get_option( WPDA::OPTION_PLUGIN_WPDATAACCESS_POST ) &&
				'on' === WPDA::get_option( WPDA::OPTION_PLUGIN_WPDATAACCESS_PAGE );

			?>
			<div id="wpda_publication_<?php echo esc_attr( $pub_id ); ?>"
				 title="<?php echo __( 'Publication shortcode', 'wp-data-access' ); ?>"
				 style="display:none"
			>
				<p>
					Copy the shortcode below into your post or page to make this publications available on your website.
				</p>
				<p class="wpda_shortcode_text">
					<strong>
						[wpdataaccess pub_id="<?php echo esc_attr( $pub_id ); ?>"]
					</strong>
				</p>
				<p class="wpda_shortcode_buttons">
					<button class="button wpda_shortcode_clipboard wpda_shortcode_button"
							type="button"
							data-clipboard-text='[wpdataaccess pub_id="<?php echo esc_attr( $pub_id ); ?>"]'
							onclick="jQuery.notify('<?php echo __( 'Shortcode successfully copied to clipboard!' ); ?>','info')"
					>
						<?php echo __( 'Copy', 'wp-data-access' ); ?>
					</button>
					<button class="button button-primary wpda_shortcode_button"
							type="button"
							onclick="jQuery('.ui-dialog-content').dialog('close')"
					>
						<?php echo __( 'Close', 'wp-data-access' ); ?>
					</button>
				</p>
				<?php
				if ( ! $shortcode_enabled ) {
					?>
					<p>
						Shortcode wpdataaccess is not enabled for all output types.
						<a href="options-general.php?page=wpdataaccess" class="wpda_shortcode_link">&raquo; Manage settings</a>
					</p>
					<?php
				}
				?>
			</div>
			<a href="javascript:void(0)"
			   class="button view wpda_tooltip"
			   title="<?php echo __( 'Get publication shortcode', 'wp-data-access' ); ?>"
			   onclick="jQuery('#wpda_publication_<?php echo esc_attr( $pub_id ); ?>').dialog()"
		    >
				<span style="white-space:nowrap">
					<span class="material-icons wpda_icon_on_button">code</span>
					<?php echo __( 'Shortcode', 'wp-data-access' ); ?>
				</span>
			</a>
			<?php
			WPDA::shortcode_popup();
		}

		public static function show_publication( $pub_id, $table_name ) {
			$datatables_enabled            = WPDA::get_option( WPDA::OPTION_BE_LOAD_DATATABLES ) === 'on';
			$datatables_responsive_enabled = WPDA::get_option( WPDA::OPTION_BE_LOAD_DATATABLES_RESPONSE ) === 'on';

			if ( ! $datatables_enabled || ! $datatables_responsive_enabled ) {
				$publication =
					'<strong>' . __( 'ERROR: Cannot test publication', 'wp-data-access' ) . '</strong><br/><br/>' .
					__( 'SOLUTION: Load jQuery DataTables: WP Data Access > Manage Plugin > Back-End Settings', 'wp-data-access' );
			} else {
				$wpda_data_tables = new WPDA_Data_Tables();
				$publication      = $wpda_data_tables->show( $pub_id, '', '', '', '', '', '', '', '', '' );
			}
			?>
			<div id="data_publisher_test_container_<?php echo esc_html( $pub_id ); ?>" style="width:95%">
				<style>
					#data_publisher_test_header_<?php echo esc_html( $pub_id); ?> {
						background-color: #ccc;
						padding: 10px;
						margin-bottom: 10px;
					}

					#data_publisher_test_container_<?php echo esc_html( $pub_id); ?> {
						display: none;
						padding: 10px;
						position: absolute;
						top: 30px;
						left: 10px;
						color: black;
						overflow-y: auto;
						background-color: white;
						border: 1px solid #ccc;
						width: max-content;
						z-index: 999;
					}
				</style>
				<div id="data_publisher_test_header_<?php echo esc_html( $pub_id ); ?>">
					<span><strong><?php echo __( 'Test Publication', 'wp-data-access' ); ?> (pub_id=<?php echo $pub_id; ?>)</strong></span>
					<span class="button" style="float:right;"
						  onclick="jQuery('#data_publisher_test_container_<?php echo esc_html( $pub_id ); ?>').hide()">x</span><br/>
					<?php echo __( 'Publication might look different on your website', 'wp-data-access' ); ?>
				</div>
				<?php echo $publication; ?>
			</div>
			<script type='text/javascript'>
				jQuery("#data_publisher_test_container_<?php echo esc_html( $pub_id ); ?>").appendTo("#wpbody-content");
			</script>
			<?php
		}
	}

}