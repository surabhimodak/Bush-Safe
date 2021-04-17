<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\List_Table
 */

namespace WPDataProjects\List_Table {

	use WPDataAccess\List_Table\WPDA_List_View;
	use WPDataAccess\Utilities\WPDA_Repository;
	use WPDataProjects\Project\WPDP_Project;
	use WPDataProjects\Data_Dictionary\WPDP_List_Columns_Cache;

	/**
	 * Class WPDP_List_View extends WPDA_List_View
	 *
	 * Data Projects uses WPDP_List_View instead of WPDA_List_View to handle column labels correctly. If the where
	 * clause contains the $$USER$$ variable insert, delete and import are disabled.
	 *
	 * @see WPDA_List_View
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_List_View extends WPDA_List_View {

		/**
		 * Project ID
		 *
		 * @var null|string
		 */
		protected $project_id = null;

		/**
		 * Page ID
		 *
		 * @var null
		 */
		protected $page_id = null;

		/**
		 * Page title
		 *
		 * @var null
		 */
		protected $title;

		/**
		 * Page subtitle
		 *
		 * @var null
		 */
		protected $subtitle;

		/**
		 * Possible values for mode are: view and edit
		 *
		 * @var
		 */
		protected $mode;

		/**
		 * SQL where clause
		 *
		 * @var null
		 */
		protected $where_clause = null;

		/**
		 * Order by
		 *
		 * @var null
		 */
		protected $orderby_clause = null;

		/**
		 * Possible values for label type are: listtable and tableform
		 * @var string
		 */
		protected $label_type = 'listtable';

		/**
		 * Allow insert?
		 *
		 * @var string|null
		 */
		protected $allow_insert = null;

		/**
		 * Allow delete?
		 *
		 * @var string|null
		 */
		protected $allow_delete = null;

		/**
		 * Allow import?
		 *
		 * @var string|null
		 */
		protected $allow_import = null;

		/**
		 * Project info
		 *
		 * @var WPDP_Project
		 */
		protected $project = null;

		/**
		 * Options set name
		 *
		 * @var string
		 */
		protected $setname = 'default';

		/**
		 * Overwrite constructor
		 *
		 * @param array $args
		 */
		public function __construct( array $args = [] ) {
			if ( isset( $args['project_id'] ) ) {
				$this->project_id = sanitize_text_field( wp_unslash( $args['project_id'] ) );
			} elseif ( isset( $_REQUEST['tab'] ) && 'tables' === $_REQUEST['tab'] ) {
				$this->project_id = 'wpda_sys_tables';
			}
			if ( isset( $args['page_id'] ) ) {
				$this->page_id = sanitize_text_field( wp_unslash( $args['page_id'] ) );
			}

			$this->project = new WPDP_Project( $this->project_id, $this->page_id );
			if ( null === $this->project->get_project() ) {
				wp_die( __( 'Data Project page not found [need a valid project_id and page_id]', 'wp-data-access' ) );
			}
			$this->title    = $this->project->get_title();
			$this->subtitle = $this->project->get_subtitle();
			$this->mode     = $this->project->get_mode();
			$this->setname  = null===$this->project->get_setname() ? 'default' : $this->project->get_setname();

			global $wpda_project_mode;
			$wpda_project_mode = [
				'project_id' => $this->project_id,
				'page_id'    => $this->page_id,
				'setname'    => $this->setname,
				'mode'       => $this->mode,
			];

			$args['title']    = ( null === $this->title || '' === $this->title ) ? null : $this->title;
			$args['subtitle'] = $this->subtitle;

			parent::__construct( $args );

			if (
				'edit' === $this->action ||
				'new' === $this->action ||
				'view' === $this->action
			) {
				$this->label_type = 'tableform';
			}

			// Overwrite column header text.
			$this->column_headers = isset( $args['column_headers'] ) ? $args['column_headers'] : '';

			if ( isset( $args['where_clause'] ) && '' !== $args['where_clause'] ) {
				$this->where_clause = $args['where_clause'];
			}

			if ( isset( $args['orderby_clause'] ) && '' !== $args['orderby_clause'] ) {
				$this->orderby_clause = $args['orderby_clause'];
			}

			if ( isset( $args['allow_insert'] ) ) {
				$this->allow_insert = sanitize_text_field( wp_unslash( $args['allow_insert'] ) );
			}
			if ( isset( $args['allow_delete'] ) ) {
				$this->allow_delete = sanitize_text_field( wp_unslash( $args['allow_delete'] ) );
			}
			if ( isset( $args['allow_import'] ) ) {
				$this->allow_import = sanitize_text_field( wp_unslash( $args['allow_import'] ) );
			}
			if ( isset( $args['bulk_actions_enabled'] ) ) {
				$this->bulk_actions_enabled = $args['bulk_actions_enabled'];
			}
		}

		/**
		 * Overwrite show method
		 *
		 * @see WPDA_List_View::show()
		 */
		public function show() {
			// Add datetimepicker
			wp_enqueue_style( 'datetimepicker' );
			wp_enqueue_script( 'datetimepicker' );

			// Prepare columns for list table. Needed in get_column_headers() and handed over to list table to prevent
			// processing the same queries multiple times.
			if ( null === $this->wpda_list_columns ) {
				$this->wpda_list_columns =
					WPDP_List_Columns_Cache::get_list_columns( $this->schema_name, $this->table_name, $this->label_type, $this->setname );
			}

			$wpda_repository = new WPDA_Repository();
			$wpda_repository->inform_user();

			if ( 'only' === $this->allow_insert && 'new' !== $this->action ) {
				wp_die( __( 'ERROR: Action not allowed', 'wp-data-access' ) );
			}

			if (
				'view' === $this->mode &&
				(
					'new' === $this->action || 'edit' === $this->action
				)
			) {
				if ( ( 'only' === $this->allow_insert || null === $this->allow_insert ) && 'new' === $this->action ) {
					// Allow these actions (exceptions)
				} else {
					wp_die( __( 'ERROR: Action not allowed', 'wp-data-access' ) );
				}
			}

			switch ( $this->action ) {

				case 'new':  // Show edit form in editing mode to create new records.
				case 'edit': // Show edit form in editing mode to update records.
				case 'view': // Show edit form in view mode to view records.
					$this->display_edit_form();
					break;

				case 'create_table': // Show form to create new table.
					$this->display_design_menu();
					break;

				default: // Show list (default).
					$this->display_list_table();

			}
		}

		/**
		 * Overwrite display_edit_form method
		 *
		 * @see WPDA_List_View::display_edit_form()
		 */
		protected function display_edit_form() {
			$args = [
				'title'               => $this->title,
				'add_action_to_title' => 'FALSE',
				'hide_add_new'        => 'off' === $this->allow_insert || 'only' === $this->allow_insert,
			];

			if ( 'only' === $this->allow_insert ) {
				$args[ 'show_back_button' ] = false;
				$args[ 'show_back_icon' ]   = false;
				$args[ 'action' ]           = 'new';
			}

			$form = new $this->edit_form_class(
				$this->schema_name,
				$this->table_name,
				$this->wpda_list_columns,
				$args
			);
			$form->show();
		}

		/**
		 * Overwrite display_list_table method
		 *
		 * @see WPDA_List_View::display_list_table()
		 */
		protected function display_list_table() {
			$args = [
				'wpdaschema_name'   => $this->schema_name,
				'table_name'        => $this->table_name,
				'wpda_list_columns' => $this->wpda_list_columns,
				'column_headers'    => $this->column_headers,
				'title'             => $this->title,
				'subtitle'          => $this->subtitle,
				'mode'              => $this->mode,
				'where_clause'      => $this->where_clause,
				'orderby_clause'    => $this->orderby_clause,
			];
			if ( null !== $this->allow_insert ) {
				$args['allow_insert'] = $this->allow_insert;
			}
			if ( null !== $this->allow_update ) {
				$args['allow_update'] = $this->allow_update;
			}
			if ( null !== $this->allow_delete ) {
				$args['allow_delete'] = $this->allow_delete;
			}
			if ( null !== $this->allow_import ) {
				$args['allow_import'] = $this->allow_import;
			}
			if ( false === $this->bulk_actions_enabled ) {
				$args['bulk_actions_enabled'] = $this->bulk_actions_enabled;
			}
			$this->wpda_list_table = new WPDP_List_Table( $args );

			$this->wpda_list_table->show();
		}

		/**
		 * Overwrite get_column_headers method
		 *
		 * @see WPDA_List_View::get_column_headers()
		 */
		public function get_column_headers() {
			if ( null === $this->wpda_list_columns ) {
				$this->wpda_list_columns = WPDP_List_Columns_Cache::get_list_columns( $this->schema_name, $this->table_name, $this->label_type, $this->setname );
			}

			return $this->wpda_list_columns->get_table_column_headers();
		}

	}

}