<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities {

	/**
	 * Class WPDA_Export_Xml
	 *
	 * @author  Peter Schulz
	 * @since   2.0.13
	 */
	class WPDA_Export_Xml extends WPDA_Export_Formatted {

		/**
		 * File header for XML export
		 *
		 * @since   2.0.13
		 */
		protected function header() {
			header( "Content-type: text/xml; charset=utf-8" );
			header( "Content-Disposition: attachment; filename={$this->table_names}.xml" );
			header( "Pragma: no-cache" );
			header( "Expires: 0" );

			echo "<?xml version=\"1.0\" ?>";
			echo "<resultset statement=\"{$this->statement}>\"";
			echo " time=\"" . gmdate( "Y-m-d\TH:i:s\Z" ) . "\"";
			echo " xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">";
		}

		/**
		 * Process one row to be export in XML format
		 *
		 * @param array $row
		 *
		 * @since   2.0.13
		 */
		protected function row( $row ) {
			echo "<row>";
			foreach ( $row as $column_name => $column_value ) {
				echo "<field name=\"$column_name\">" . esc_html( $column_value ) . '</field>';
			}
			echo "</row>";
		}

		/**
		 * File footer for XML export
		 *
		 * @since   2.0.13
		 */
		protected function footer() {
			echo "</resultset>";
		}

	}

}