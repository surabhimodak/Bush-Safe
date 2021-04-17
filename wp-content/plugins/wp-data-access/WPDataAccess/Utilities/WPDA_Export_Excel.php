<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities {

	/**
	 * Class WPDA_Export_Excel
	 *
	 * @author  Peter Schulz
	 * @since   2.0.13
	 */
	class WPDA_Export_Excel extends WPDA_Export_Formatted {

		/**
		 * File header for Excel export
		 *
		 * @since   2.0.13
		 */
		protected function header() {
			header( "Content-type: application/vnd.ms-excel; charset=utf-8" );
			header( "Content-Disposition: attachment; filename={$this->table_names}.xml" );
			header( "Pragma: no-cache" );
			header( "Expires: 0" );

			echo "<?xml version='1.0' ?>";
			echo "<?mso-application progid=\"Excel.Sheet\"?>";
			echo "<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"";
			echo " xmlns:o=\"urn:schemas-microsoft-com:office:office\"";
			echo " xmlns:x=\"urn:schemas-microsoft-com:office:excel\"";
			echo " xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"";
			echo " xmlns:html=\"http://www.w3.org/TR/REC-html40\">";
			echo "<DocumentProperties xmlns=\"urn:schemas-microsoft-com:office:office\">";
			echo "<Author>Exported by WP Data Access</Author>";
			echo "</DocumentProperties>";
			echo "<Styles><Style ss:ID=\"s62\"><Font ss:Bold=\"1\"/></Style></Styles>";
			echo "<Worksheet ss:Name=\"Table {$this->table_names} export\">";
			echo "<Table>";
			if ( is_array( $this->rows ) && sizeof( $this->rows ) > 0 ) {
				echo "<Row>";
				foreach ( $this->rows[0] as $column_name => $column_value ) {
					echo "<Cell ss:StyleID=\"s62\"><Data ss:Type=\"String\">" . $this->wpda_list_columns->get_column_label( $column_name ) . "</Data></Cell>";
				}
				echo "</Row>";
			}
		}

		/**
		 * Process one row to be export in Excel format
		 *
		 * @param array $row
		 *
		 * @since   2.0.13
		 */
		protected function row( $row ) {
			echo "<Row>";
			foreach ( $row as $column_name => $column_value ) {
				echo '<Cell><Data ss:Type="String">' . esc_html( $column_value ) .'</Data></Cell>';
			}
			echo "</Row>";
		}

		/**
		 * File footer for Excel export
		 *
		 * @since   2.0.13
		 */
		protected function footer() {
			echo "</Table>";
			echo "</Worksheet>";
			echo "</Workbook>";

		}

	}

}