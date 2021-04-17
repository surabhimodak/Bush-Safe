<?php

namespace WPDataAccess\Utilities {

	class WPDA_Restore_Repository {

		const BACKUP_TABLE_EXTENSION = '_BACKUP_';

		protected $repository_tables = [];

		public function restore( $restore_date ) {
			global $wpdb;
			$this->repository_tables = [];

			$suppress = $wpdb->suppress_errors( true );
			foreach ( WPDA_Repository::CREATE_TABLE as $key => $value ) {
				if ( isset( $_REQUEST[ $key ] ) ) {
					if ( 'replace' === $_REQUEST[ $key ] || 'add' === $_REQUEST[ $key ] ) {
						$this->restore_table(
							$wpdb->prefix . $key,
							$wpdb->prefix . $key . self::BACKUP_TABLE_EXTENSION . $restore_date,
							$_REQUEST[ $key ]
						);
					}
				}
			}
			$wpdb->suppress_errors( $suppress );

			return $this->repository_tables;
		}

		protected function restore_table( $arg_table_name, $arg_bck_table_name, $action ) {
			global $wpdb;

			// Use backticks to prevent SQL injection
			$table_name     = str_replace( '`', '', $arg_table_name );
			$bck_table_name = str_replace( '`', '', $arg_bck_table_name );

			$sql_check_table = "
				select c1.column_name as column_name
				from information_schema.columns c1
				where c1.table_schema = '{$wpdb->dbname}'
				  and c1.table_name   = '$table_name'
				  and c1.column_name in (
				      select c2.column_name
					  from   information_schema.columns c2
				      where  c2.table_schema = '{$wpdb->dbname}'
				        and  c2.table_name   = '{$bck_table_name}'
				      )
			";
			$same_cols = $wpdb->get_results( $sql_check_table, 'ARRAY_A' );

			// Get selected columns
			$selected_columns = '';
			foreach ( $same_cols as $same_col ) {
				$selected_columns .= $same_col['column_name'] . ',';
			}
			$selected_columns = substr( $selected_columns, 0, strlen( $selected_columns ) - 1 );

			if ( 'replace' === $action ) {
				// Truncate table before restoring backup data
				$wpdb->query("
					truncate table `{$table_name}`
				");
			}

			// Restore data
			$result = $wpdb->query("
				insert into `{$table_name}` ({$selected_columns})
				select {$selected_columns} from `{$bck_table_name}`
			");

			$this->repository_tables[ $table_name ] = [
				'rows'   => $result,
				'errors' => $wpdb->last_error
			];
		}

	}

}