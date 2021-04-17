<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Parent_Child
 */

namespace WPDataProjects\Parent_Child {

	use WPDataAccess\Connection\WPDADB;
	use WPDataAccess\Utilities\WPDA_Message_Box;

	/**
	 * Class WPDP_Child_List_Table_Selection extends WPDP_Child_List_Table
	 *
	 * Provides a list table with lookup selection functionality. Rows can be selected from the list to be added to a
	 * relationship. This list table shows only rows that do not (yet) belong to the relationship.
	 *
	 * @see WPDP_Child_List_Table
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Child_List_Table_Selection extends WPDP_Child_List_Table {

		/**
		 * Overwrites WPDP_Child_List_Table_Selection constructor
		 *
		 * Turns off all standard links (all links are overwritten in this class).
		 *
		 * @param array $args
		 */
		public function __construct( $args = [] ) {
			$this->page_number_item_name = 'child_selection_page_number';
			$this->search_item_name      = 'slov';

			parent::__construct( $args );

			$this->bulk_actions_enabled = true;
			$this->show_view_link       = 'off';
			$this->allow_insert         = 'off';
			$this->allow_update         = 'off';
			$this->allow_delete         = 'off';
			$this->where_in             = 'not in';
		}

		/**
		 * Overwrites method show to add a back button
		 */
		public function show() {
			parent::show();

			// Prepare url back button
			if ( is_admin() ) {
				$url = '?page=' . esc_attr( $this->page );
			} else {
				$url = '';
			}
			global $wpdb;
			if ( '' !== $this->schema_name && $wpdb->dbname !== $this->schema_name ) {
				$url .= $url === '' ? '?' : '&';
				$url .= 'wpdaschema_name=' . esc_attr( $this->schema_name );
			}
			$url .= $url === '' ? '?' : '&';
			$url .= 'table_name=' . esc_attr( $this->table_name ) . esc_attr( $this->add_parent_args_to_back_button() ) . $this->page_number_link;
			?>
			<div style="padding-top:5px;padding-left:3px;">
				<button type="button"
					   	onclick="javascript:window.location.href='<?php echo $url; ?>'"
					   	class="button button-secondary">
					<span class="material-icons wpda_icon_on_button">arrow_back</span>
					<?php echo __( 'Child List', 'wp-data-access' ); ?>
				</button>
			</div>
			<?php
		}

		/**
		 * Overwrites method add_parent_args to add argument list_table_selection
		 */
		protected function add_parent_args() {
			parent::add_parent_args();
			?>
			<input type='hidden' name='list_table_selection' value='TRUE'>
			<?php
		}

		/**
		 * Add parent args to back button as url argument string
		 *
		 * @return string
		 */
		protected function add_parent_args_to_back_button() {
			if ( isset( $_REQUEST['child_tab'] ) ) {
				$child_tab = sanitize_text_field( wp_unslash( $_REQUEST['child_tab'] ) ); // input var okay.
			} else {
				$child_tab = '';
			}
			$args = "&action=list&mode=edit&child_request=TRUE&child_tab=$child_tab";
			foreach ( $this->parent['parent_key'] as $parent_key ) {
				$args .= '&' . esc_attr( $parent_key ) . '=' . esc_attr( $this->parent['parent_key_value'][ $parent_key ] );
			}

			return $args;
		}

		/**
		 * Overwrite method get_bulk_actions to add 'Add selected rows' to bulk selection
		 *
		 * @return array
		 */
		public function get_bulk_actions() {
			$actions = [
				'bulk-add' => __( 'Add selected rows', 'wp-data-access' ),
			];

			return $actions;
		}

		/**
		 * Overwrites method process_bulk_action to implement selection
		 */
		public function process_bulk_action() {
			if ( 'bulk-add' === $this->current_action() ) {
				if ( ! isset( $_REQUEST['bulk-selected'] ) ) { // input var okay.
					$msg = new WPDA_Message_Box(
						[
							'message_text' => __( 'Nothing selected', 'wp-data-access' ),
						]
					);
					$msg->box();

					return;
				}

				$bulk_rows = $_REQUEST['bulk-selected'];
				$no_rows   = count( $bulk_rows ); // # rows to be added.

				$rows_to_be_added = []; // Gonna hold rows to be added.

				for ( $i = 0; $i < $no_rows; $i ++ ) {
					// Write "json" to named array. Need to strip slashes twice. Once for the normal conversion
					// and once extra for the pre-conversion of double quotes in method column_cb().
					$row_object = json_decode( stripslashes( stripslashes( $bulk_rows[ $i ] ) ), true );
					if ( $row_object ) {
						$j = 0; // Index used to build array.

						// Check all key columns.
						foreach ( $this->wpda_list_columns->get_table_primary_key() as $key ) {
							// Check if key is available.
							if ( ! isset( $row_object[ $key ] ) ) {
								wp_die( __( 'ERROR: Invalid URL', 'wp-data-access' ) );
							}

							// Write key value pair to array.
							$rows_to_be_added[ $i ][ $j ]['key']   = $key;
							$rows_to_be_added[ $i ][ $j ]['value'] = $row_object[ $key ];
							$j ++;

						}
					}
				}

				// Looks like eveything is there. Add relationship.
				$no_key_cols            = count( $this->wpda_list_columns->get_table_primary_key() );
				$rows_succesfully_added = 0; // Number of rows succesfully added.
				$rows_with_errors       = 0; // Number of rows that could not be added.
				for ( $i = 0; $i < $no_rows; $i ++ ) {
					// Prepare named array for delete operation.
					$next_row_to_be_added = [];

					$row_found = true;
					for ( $j = 0; $j < $no_key_cols; $j ++ ) {
						if ( isset( $rows_to_be_added[ $i ][ $j ]['key'] ) ) {
							$next_row_to_be_added[ $rows_to_be_added[ $i ][ $j ]['key'] ] = $rows_to_be_added[ $i ][ $j ]['value'];
						} else {
							$row_found = false;
						}
					}

					if ( $row_found ) {
						if ( $this->add_row( $next_row_to_be_added ) ) {
							// Row(s) succesfully added.
							$rows_succesfully_added ++;
						} else {
							// An error occured during the insert operation: increase error count.
							$rows_with_errors ++;
						}
					} else {
						// An error occured during the insert operation: increase error count.
						$rows_with_errors ++;
					}
				}

				// Inform user about the results of the operation.
				$message = '';
				if ( 1 === $rows_succesfully_added ) {
					$message = __( 'Row added', 'wp-data-access' );
				} elseif ( $rows_succesfully_added > 1 ) {
					$message = "$rows_succesfully_added " . __( 'rows added', 'wp-data-access' );
				}
				if ( '' !== $message ) {
					$msg = new WPDA_Message_Box(
						[
							'message_text' => $message,
						]
					);
					$msg->box();
				}

				if ( $rows_with_errors > 0 ) {
					$msg = new WPDA_Message_Box(
						[
							'message_text'           => __( 'Not all rows have been added', 'wp-data-access' ),
							'message_type'           => 'error',
							'message_is_dismissible' => false,
						]
					);
					$msg->box();
				}

				// Records added, redirect to list table
				if ( isset( $this->child['tab_label'] ) ) {
					?>
					<script type='text/javascript'>
						jQuery('#form_tab_<?php echo esc_attr( $this->child['tab_label'] ); ?>').submit();
					</script>
					<?php
				}
			}
		}

		/**
		 * Adds a relationship for every selected row
		 *
		 * @param array $next_row_to_be_added Rows to be added
		 *
		 * @return mixed Return value of SQL insert statement
		 */
		protected function add_row( $next_row_to_be_added ) {
			$wpdadb = WPDADB::get_db_connection( $this->schema_name );
			if ( null === $wpdadb ) {
				if ( is_admin() ) {
					wp_die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				} else {
					die( sprintf( __( 'ERROR - Remote database %s not available', 'wp-data-access' ), $this->schema_name ) );
				}
			}

			$child_columns = [];
			$index         = 0;
			foreach ( $next_row_to_be_added as $row ) {
				$child_columns[ $this->child['relation_nm']['child_table_select'][ $index ] ] =
					$row;
				$child_columns[ $this->child['relation_nm']['child_table_where'][ $index ] ]  =
					$this->parent['parent_key_value'][ $this->parent['parent_key'][ $index ] ];
				$index ++;
			}

			return $wpdadb->insert( $this->child['relation_nm']['child_table'], $child_columns );
		}

		/**
		 * Overwrite method column_default_add_action to prevent parent action being executed
		 *
		 * @param array  $item
		 * @param string $column_name
		 * @param array  $actions
		 */
		protected function column_default_add_action( $item, $column_name, &$actions ) { }

	}

}
