<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataAccess\Utilities
 */

namespace WPDataAccess\Utilities {

	use WPDataAccess\Connection\WPDADB;
	use WPDataAccess\Simple_Form\WPDA_Simple_Form_Item_Autocomplete;

	class WPDA_Autocomplete {

		public function autocomplete() {
			$status    = 'ok';
			$message   = '';
			$rows      = [];

			if (
				! isset(
					$_POST['wpda_wpnonce'],
					$_POST['wpda_source_column_value'],
					$_POST['wpda_source_column_name'],
					$_POST['wpda_target_schema_name'],
					$_POST['wpda_target_table_name'],
					$_POST['wpda_target_column_name'],
					$_POST['wpda_lookup_label_column']
				)
			) {
				$status  = 'error';
				$message = __( 'Wrong arguments', 'wp-data-access' );
			}else {
				$wpda_wpnonce             = sanitize_text_field( wp_unslash( $_POST['wpda_wpnonce'] ) ); // input var okay.
				$wpda_source_column_value = sanitize_text_field( wp_unslash( $_POST['wpda_source_column_value'] ) ); // input var okay.
				$wpda_source_column_name  = sanitize_text_field( wp_unslash( $_POST['wpda_source_column_name'] ) ); // input var okay.
				$wpda_target_schema_name  = sanitize_text_field( wp_unslash( $_POST['wpda_target_schema_name'] ) ); // input var okay.
				$wpda_target_table_name   = sanitize_text_field( wp_unslash( $_POST['wpda_target_table_name'] ) ); // input var okay.
				$wpda_target_column_name  = sanitize_text_field( wp_unslash( $_POST['wpda_target_column_name'] ) ); // input var okay.
				$wpda_lookup_label_column = sanitize_text_field( wp_unslash( $_POST['wpda_lookup_label_column'] ) ); // input var okay.

				if ( ! wp_verify_nonce( $wpda_wpnonce, WPDA_Simple_Form_Item_Autocomplete::AUTOCOMPLE_NONCE_ACTION . $wpda_target_table_name ) ) {
					$status  = 'error';
					$message = __( 'Not authorized', 'wp-data-access' );
				} else {
					$rows = $this->autocomplete_query(
						$wpda_target_schema_name,
						$wpda_target_table_name,
						$wpda_target_column_name,
						$wpda_lookup_label_column,
						$wpda_source_column_value
					);
				}
			}

			$response = [
				'status'  => $status,
				'message' => $message,
				'rows'    => $rows,
			];

			header('Content-type: application/json');
			header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
			header('Cache-Control: post-check=0, pre-check=0', false);
			header('Pragma: no-cache');
			header('Expires: 0');

			echo json_encode( $response, JSON_NUMERIC_CHECK );

			die();
		}

		public function autocomplete_query(
			$wpda_target_schema_name,
			$wpda_target_table_name,
			$wpda_target_column_name,
			$wpda_lookup_label_column,
			$wpda_source_column_value
		) {
			$wpdadb = WPDADB::get_db_connection( $wpda_target_schema_name );
			if ( null === $wpdadb ) {
				return [];
			}

			$query  = "
						select `{$wpda_lookup_label_column}` as value,
						       `{$wpda_lookup_label_column}` as label,
						       `{$wpda_target_column_name}` as lookup
						from `{$wpda_target_table_name}`
						where `{$wpda_lookup_label_column}` like %s
					";

			return $wpdadb->get_results(
				$wpdadb->prepare(
					$query,
					"$wpda_source_column_value%"
				),
				'ARRAY_A'
			);
		}

		public function autocomplete_anonymous() {
			$this->autocomplete();
		}

		public function autocomplete_lookup(
			$schema_name,
			$table_name,
			$column_name,
			$lookup_column_name,
			$lookup_column_value
		) {
			$wpdadb = WPDADB::get_db_connection( $schema_name );
			if ( null === $wpdadb ) {
				return false;
			}

			$query  = "
				select `{$lookup_column_name}` as lookup
				from `{$table_name}`
				where `{$column_name}` = %s
			";

			$rows = $wpdadb->get_results(
				$wpdadb->prepare(
					$query,
					$lookup_column_value
				)
				, 'ARRAY_A' );

			if ( 1 === $wpdadb->num_rows ) {
				return $rows[0]['lookup'];
			} else {
				return false;
			}
		}

	}

}