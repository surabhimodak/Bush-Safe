<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Simple_Form
 */

namespace WPDataAccess\Simple_Form {

	use WPDataAccess\WPDA;

	/**
	 * Class WPDA_Simple_Form_Item_DateTime
	 *
	 * Adds a date/time picker to fields of type: date, datetime, timestamp and time
	 *
	 * @author  Peter Schulz
	 * @since   2.5.1
	 */
	class WPDA_Simple_Form_Item_DateTime extends WPDA_Simple_Form_Item {

		protected $date_format = 'Y-m-d';
		protected $time_format = 'H:i';

		protected $date_placeholder = 'yyyy-mm-dd';
		protected $time_placeholder = 'hh:mi';

		protected $date_picker = 'true';
		protected $time_picker = 'false';

		/**
		 * WPDA_Simple_Form_Item_DateTime constructor.
		 *
		 * @param array $args
		 */
		public function __construct( $args = [] ) {
			parent::__construct( $args );

			// Get date and time formats
			$this->date_format = WPDA::get_option( WPDA::OPTION_PLUGIN_DATE_FORMAT );
			$this->time_format = WPDA::get_option( WPDA::OPTION_PLUGIN_TIME_FORMAT );

			// Get date and time placeholders
			$this->date_placeholder = WPDA::get_option( WPDA::OPTION_PLUGIN_DATE_PLACEHOLDER );
			$this->time_placeholder = WPDA::get_option( WPDA::OPTION_PLUGIN_TIME_PLACEHOLDER );

			switch ( $this->column_type ) {
				case 'time':
					$this->date_format      = $this->time_format;
					$this->date_picker      = 'false';
					$this->time_picker      = 'true';
					$this->item_placeholder = $this->time_placeholder;
					$db_format              = WPDA::DB_TIME_FORMAT;
					break;
				case 'date':
					$this->item_placeholder = $this->date_placeholder;
					$db_format              = WPDA::DB_DATE_FORMAT;
					break;
				default:
					$this->date_format      = $this->date_format . ' ' . $this->time_format;
					$this->time_picker      = 'true';
					$this->item_placeholder = $this->date_placeholder . ' ' . $this->time_placeholder;
					$db_format              = WPDA::DB_DATETIME_FORMAT;
			}

			if ( ! $args[ 'user_update' ] && null !== $this->item_value && '' !== $this->item_value ) {
				$convert_date     = \DateTime::createFromFormat( $db_format, $this->item_value );
				$this->item_value = $convert_date->format( $this->date_format );
			}
		}

		/**
		 * Overwrite method show_item: add date/time picker
		 */
		public function show_item() {
			parent::show_item();
			?>
			<script type='text/javascript'>
				jQuery(function () {
					jQuery.datetimepicker.setLocale('<?php echo substr( get_locale(), 0, 2 ); ?>');
					jQuery('#<?php echo esc_attr( $this->item_name ); ?>').datetimepicker({
						format: '<?php echo $this->date_format; ?>',
						datepicker: <?php echo $this->date_picker; ?>,
						timepicker: <?php echo $this->time_picker; ?>,
						scrollMonth: false,
						scrollInput: false
					});
					jQuery('#<?php echo esc_attr( $this->item_name ); ?>').attr('autocomplete', 'off');
				});
			</script>
			<?php
		}

	}

}