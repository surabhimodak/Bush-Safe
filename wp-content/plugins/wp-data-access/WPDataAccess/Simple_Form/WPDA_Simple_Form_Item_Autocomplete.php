<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Simple_Form
 */

namespace WPDataAccess\Simple_Form {

	use WPDataAccess\Utilities\WPDA_Autocomplete;

	/**
	 * Class WPDA_Simple_Form_Item_Autocomplete
	 *
	 * Adds autocomplete support to input field
	 *
	 * @author  Peter Schulz
	 * @since   4.1.3
	 */
	class WPDA_Simple_Form_Item_Autocomplete extends WPDA_Simple_Form_Item {

		const AUTOCOMPLE_NONCE_ACTION = 'WPDA-AUTO-COMPLETE*';

		protected $autocomplete_def   = null;
		protected $tableform_settings = null;

		protected function show_item() {
			parent::show_item();

			if ( null === $this->autocomplete_def ) {
				wp_die( '<span style="font-weight: bold">' . __( 'ERROR: Invalid autocomplete lookup usage', 'wp-data-access' ) . '</span>' );
			}

			$lookup_value = '';

			if ( null !== $this->item_value && '' !== $this->item_value ) {
				// Get lookup value
				$schema_name         = $this->autocomplete_def->target_schema_name;
				$table_name          = $this->autocomplete_def->target_table_name;
				$column_name         = $this->autocomplete_def->target_column_name[0];
				$lookup_column_name  = '';
				$lookup_column_value = $this->item_value;

				foreach ( $this->tableform_settings as $tableform_setting ) {
					if ( $tableform_setting->column_name === $column_name ) {
						$lookup_column_name = $tableform_setting->lookup;
					}
				}
				if ( '' === $lookup_column_name ) {
					wp_die( '<span style="font-weight: bold">' . __( 'ERROR: Invalid autocomplete lookup usage', 'wp-data-access' ) . '</span>' );
				}

				$wpda_autocomplete = new WPDA_Autocomplete();
				$lookup_value = $wpda_autocomplete->autocomplete_lookup(
					$schema_name,
					$table_name,
					$column_name,
					$lookup_column_name,
					$lookup_column_value
				);

				if ( false === $lookup_value ) {
					echo '<div>Lookup value not found!</div>';
				}
			}

			$placeholder = __( 'Start typing', 'wp-data-access' ) . " " . strtolower( $this->get_item_label() ) . '...';
			echo "<input type='text' id='wpda_autocomplete_{$this->get_item_name()}' class='wpda_autocomplete' placeholder='{$placeholder}' value='{$lookup_value}'/>";

			$this->add_js();
		}

		protected function add_js() {
			$wpnonce = wp_create_nonce( self::AUTOCOMPLE_NONCE_ACTION .  $this->autocomplete_def->target_table_name );
			?>
			<script type="text/javascript">
				jQuery(function() {
					var wpda_path = '<?php echo admin_url() . 'admin-ajax.php'; ?>';
					var item_name = '<?php echo esc_attr( $this->get_item_name() ); ?>';

					var autocomplete_def = '<?php echo json_encode( $this->autocomplete_def );?>';
					var tableform_settings = '<?php echo json_encode( $this->tableform_settings );?>';

					autocomplete_def_json = jQuery.parseJSON(autocomplete_def);
					tableform_settings_json = jQuery.parseJSON(tableform_settings);
					lookup_label_column = '';

					for (i=0; i<tableform_settings_json.length; i++) {
						if (tableform_settings_json[i]['column_name']===item_name) {
							lookup_label_column = tableform_settings_json[i].lookup;
						}
					}

					if (lookup_label_column==='') {
						alert('ERROR: Cannot call autocomplete service [error in definition]');
					} else {
						jQuery('#wpda_autocomplete_' + item_name).autocomplete({
							autoFocus: true,
							source: function (name, response) {
								jQuery.ajax({
									method: 'POST',
									url: wpda_path + '?action=wpda_autocomplete',
									data: {
										wpda_wpnonce: '<?php echo esc_attr( $wpnonce );?>',
										wpda_source_column_value: jQuery('#wpda_autocomplete_' + item_name).val(),
										wpda_source_column_name: autocomplete_def_json.source_column_name[0],
										wpda_target_schema_name: autocomplete_def_json.target_schema_name,
										wpda_target_table_name: autocomplete_def_json.target_table_name,
										wpda_target_column_name: autocomplete_def_json.target_column_name[0],
										wpda_lookup_label_column: lookup_label_column
									}
								}).done(
									function (msg) {
										if (msg.status === "error") {
											alert(msg.message);
										} else {
											response(msg.rows);
										}
									}
								).error(
									function (msg) {
										alert('ERROR: Autocomplete service call return an invalid response');
									}
								);
							},
							select: function(e,ui) {
								jQuery('#' + item_name).val(ui.item.lookup);
							},
							change: function(event){
								if (jQuery('#wpda_autocomplete_' + item_name).val()=='') {
									jQuery('#' + item_name).val('');
								}
							},
							classes: {
								"ui-autocomplete": "wpda-autocomplete",
							}
						});
					}
				});
			</script>
			<style type="text/css">
                ul.wpda-autocomplete.ui-widget-content {
					background: white;
                    color: inherit;
				}
				ul.wpda-autocomplete li.ui-menu-item .ui-menu-item-wrapper.ui-state-active {
                    background: #efefef;
                    color: unset;
                    border: none;
                }
			</style>
			<?php
		}

		public function set_autocomplete( $autocomplete_def, $tableform_settings ) {
			$this->autocomplete_def   = $autocomplete_def;
			$this->tableform_settings = $tableform_settings;
		}

	}

}