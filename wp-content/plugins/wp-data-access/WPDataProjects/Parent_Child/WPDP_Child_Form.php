<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Parent_Child
 */

namespace WPDataProjects\Parent_Child {

	use WPDataProjects\Simple_Form\WPDP_Simple_Form;
	use WPDataAccess\Utilities\WPDA_Message_Box;

	/**
	 * Class WPDP_Child_Form extends WPDP_Simple_Form
	 *
	 * @see WPDP_Simple_Form
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Child_Form extends WPDP_Simple_Form {

		/**
		 * Possible values: edit and view
		 *
		 * @var string
		 */
		protected $mode;

		/**
		 * Saved initially requested action
		 *
		 * Action is changed during processing
		 *
		 * @var string
		 */
		protected $initial_action;

		/**
		 * Parent key values
		 *
		 * @var array
		 */
		protected $parent;

		/**
		 * Child relationships (for actual parent)
		 *
		 * @var array
		 */
		protected $child;

		/**
		 * WPDP_Child_Form constructor
		 *
		 * @param       $schema_name
		 * @param       $table_name
		 * @param       $wpda_list_columns
		 * @param array $args
		 */
		public function __construct( $schema_name, $table_name, $wpda_list_columns, $args = [] ) {
			if ( isset( $args['mode'] ) ) {
				$this->mode = $args['mode'];
			} else {
				wp_die( __( 'ERROR: Wrong arguments [missing mode]', 'wp-data-access' ) );
			}

			if ( isset( $args['parent'] ) ) {
				$this->parent = $args['parent'];
			} else {
				wp_die( __( 'ERROR: Wrong arguments [missing parent]', 'wp-data-access' ) );
			}

			if ( isset( $args['child'] ) ) {
				$this->child = $args['child'];
			} else {
				wp_die( __( 'ERROR: Wrong arguments [missing child]', 'wp-data-access' ) );
			}

			$this->page_number_item_name = 'child_page_number';

			parent::__construct( $schema_name, $table_name, $wpda_list_columns, $args );

			$this->initial_action = $this->action;
		}

		/**
		 * Overwrite method to add parent argument to get back to parent child relationship
		 */
		protected function add_parent_args() {
			foreach ( $this->parent['parent_key'] as $parent_key ) {
				?>
				<input type='hidden'
					   name='WPDA_PARENT_KEY*<?php echo( esc_attr( $parent_key ) ); ?>'
					   value='<?php echo( esc_attr( $this->parent['parent_key_value'][ $parent_key ] ) ); ?>'
				/>
				<?php
			}
			$child_tab = $this->get_child_tab();
			?>
			<input type='hidden' name='mode' value='<?php echo esc_attr( $this->mode ); ?>'>
			<input type='hidden' name='child_request' value='TRUE'/>
			<input type='hidden' name='child_tab' value='<?php echo esc_attr( $child_tab ); ?>'/>
			<?php
		}

		/**
		 * Get actual child tab
		 *
		 * @return string
		 */
		protected function get_child_tab() {
			if ( isset( $_REQUEST['child_tab'] ) ) {
				return sanitize_text_field( wp_unslash( $_REQUEST['child_tab'] ) ); // input var okay.
			} else {
				return '';
			}
		}

		/**
		 * Overwrites method add_parent_args_to_back_button to add parent arguments
		 *
		 * @return string|void
		 */
		protected function add_parent_args_to_back_button() {
			$child_tab = $this->get_child_tab();
			$args      = "&action=list&mode={$this->mode}&child_request=TRUE&child_tab=$child_tab";
			foreach ( $this->parent['parent_key'] as $parent_key ) {
				$args .= '&' . esc_attr( $parent_key ) . '=' . esc_attr( $this->parent['parent_key_value'][ $parent_key ] );
			}

			return $args;
		}

		/**
		 * Overwrites method prepare_row to prepare child insert
		 */
		protected function prepare_row() {
			parent::prepare_row();

			if ( 'new' === $this->initial_action && 'edit' === $this->action && 'save' === $this->action2 ) {
				if ( isset( $this->child['relation_nm'] ) ) {
					// Create relationship (n:m).
					// Prepare values.
					if ( - 1 !== $this->auto_increment_value ) {
						$child_columns = [
							$this->child['relation_nm']['child_table_select'][0] =>
								$this->auto_increment_value,
							$this->child['relation_nm']['child_table_where'][0]  =>
								$this->parent['parent_key_value'][ $this->parent['parent_key'][0] ],
						];
					} else {
						$index = 0;
						foreach ( $this->child['relation_nm']['parent_key'] as $parent_key ) {
							$child_columns[ $this->child['relation_nm']['child_table_select'][ $index ] ] =
								$this->form_items_new_values[ $this->child['relation_nm']['parent_key'][ $parent_key ] ];
							$child_columns[ $this->child['relation_nm']['child_table_where'][ $index ] ]  =
								$this->parent['parent_key_value'][ $this->parent['parent_key'][ $index ] ];
							$index ++;
						}
					}

					// Perform insert.
					global $wpdb;
					$result = $wpdb->insert( $this->child['relation_nm']['child_table'], $child_columns );

					// Error handling.
					if ( false === $result ) {
						$this->relationship_insert_failed();
					} elseif ( 1 !== $result ) {
						$this->relationship_insert_failed();
					}
				}
			}
		}

		/**
		 * Message shown on insert error
		 */
		protected function relationship_insert_failed() {
			$msg = new WPDA_Message_Box(
				[
					'message_text'           => __( 'Error create relationship (insert failed)', 'wp-data-access' ),
					'message_type'           => 'error',
					'message_is_dismissible' => false,
				]
			);
			$msg->box();
		}

		/**
		 * Overwrites method prepare_items to add parent key values
		 *
		 * @param bool $set_back_form_values
		 */
		protected function prepare_items( $set_back_form_values = false ) {
			parent::prepare_items( $set_back_form_values );

			if ( isset( $this->child['relation_1n'] ) ) {
				foreach ( $this->child['relation_1n']['child_key'] as $child_key ) {
					$child_index = $this->get_item_index( $child_key );
					$child_item  = $this->form_items[ $child_index ];
					if ( $child_item->get_item_name() === $child_key ) {
						foreach ( $this->child['relation_1n']['parent_key'] as $parent_key ) {
							foreach ( $this->parent['parent_key'] as $pkey ) {
								if ( $parent_key === $pkey ) {
									$child_item->set_item_default_value( $this->parent['parent_key_value'][ $parent_key ] );
									$child_item->set_hide_item( true );
									$child_item->set_foreign_key_column( true );
								}
							}
						}
					}
				}
			}
		}

	}

}
