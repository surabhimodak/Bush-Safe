<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Parent_Child
 */

namespace WPDataProjects\Parent_Child {

	use WPDataAccess\List_Table\WPDA_List_View;
	use WPDataAccess\Utilities\WPDA_Repository;
	use WPDataProjects\Project\WPDP_Project;
	use WPDataProjects\Data_Dictionary\WPDP_List_Columns_Cache;
	use WPDataProjects\List_Table\WPDP_List_View;

	/**
	 * Class WPDP_Parent_List_View extends WPDA_List_View
	 *
	 * Adds parent-child functionality to WPDA_List_View
	 *
	 * @see WPDA_List_View
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Parent_List_View extends WPDP_List_View {

		/**
		 * Project ID
		 *
		 * @var string
		 */
		protected $project_id = null;

		/**
		 * Page ID
		 *
		 * @var string
		 */
		protected $page_id = null;

		/**
		 * Instance of class WPDP_Project for $this->project_id
		 *
		 * @var WPDP_Project
		 */
		protected $project;

		/**
		 * Parent info
		 *
		 * @var array
		 */
		protected $parent;

		/**
		 * Child relationships
		 *
		 * @var array
		 */
		protected $children;

		/**
		 * Page title
		 *
		 * @var string
		 */
		protected $title;

		/**
		 * Page sub title
		 *
		 * @var string
		 */
		protected $subtitle;

		/**
		 * Possible values: TRUE and null
		 *
		 * TRUE = request is a child request
		 * All other values (including null) = request is a parent request
		 *
		 * @var mixed
		 */
		protected $child_request;

		/**
		 * Possible values: edit and view
		 *
		 * @var string
		 */
		protected $mode = null;

		/**
		 * Class to be instantiated for data entry form
		 *
		 * @var string|null
		 */
		protected $parent_edit_form_class = null;

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
		 * WPDP_Parent_List_View constructor
		 *
		 * Adds parent-child functionality
		 *
		 * @param array $args
		 */
		public function __construct( $args = [] ) {
			if ( isset( $args['project_id'] ) ) {
				$this->project_id = sanitize_text_field( wp_unslash( $args['project_id'] ) );
			} elseif ( isset( $_REQUEST['tab'] ) && 'tables' === $_REQUEST['tab'] ) {
				$this->project_id = 'wpda_sys_tables';
			}
			if ( isset( $args['page_id'] ) ) {
				$this->page_id = sanitize_text_field( wp_unslash( $args['page_id'] ) );
			}

			$args['title']       = ( null === $this->title || '' === $this->title ) ? null : $this->title;
			$args['subtitle']    = $this->subtitle;

			parent::__construct( $args );

			$this->child_request = (
				isset( $_REQUEST['child_request'] ) &&
				'TRUE' === sanitize_text_field( wp_unslash( $_REQUEST['child_request'] ) )
			);

			if ( isset( $_REQUEST['mode'] ) ) {
				$this->mode = sanitize_text_field( wp_unslash( $_REQUEST['mode'] ) ); // input var okay.
			}

			if ( isset( $args['parent_edit_form_class'] ) ) {
				$this->parent_edit_form_class = $args['parent_edit_form_class']; // input var okay.
			}

			if ( isset( $args['where_clause'] ) && '' !== $args['where_clause'] ) {
				$this->where_clause = $args['where_clause'];
			}

			if ( isset( $args['orderby_clause'] ) && '' !== $args['orderby_clause'] ) {
				$this->orderby_clause = $args['orderby_clause'];
			}
		}

		/**
		 * Overwrites method show to add parent-child functionality
		 */
		public function show() {
			// Add datetimepicker
			wp_enqueue_style( 'datetimepicker' );
			wp_enqueue_script( 'datetimepicker' );

			$this->project = new WPDP_Project( $this->project_id, $this->page_id );
			if ( null === $this->project->get_project() ) {
				wp_die( __( 'Data Project page not found [need a valid project_id and page_id]', 'wp-data-access' ) );
			}
			$this->title = $this->project->get_title();
			if ( null === $this->mode ) {
				$this->mode = $this->project->get_mode();
			}
			$this->subtitle = $this->project->get_subtitle();
			$this->parent   = $this->project->get_parent();
			$this->children = $this->project->get_children();

			$wpda_repository = new WPDA_Repository();
			$wpda_repository->inform_user();

			// DO NOT REMOVE!!!
			// Yes, this is double code! But for some completely unclear reason this member seems to lose tis value...
			$this->child_request = (
				isset( $_REQUEST['child_request'] ) &&
				'TRUE' === sanitize_text_field( wp_unslash( $_REQUEST['child_request'] ) )
			);

			if ( $this->child_request ) {
				$this->display_edit_form();
			} else {
				switch ( $this->action ) {
					case 'new':
					case 'view':
					case 'edit':
						$this->display_edit_form();
						break;
					default:
						$this->display_list_table();
				}
			}
		}

		/**
		 * Overwrites method display_edit_form to add parent-child functionality
		 */
		protected function display_edit_form() {
			if (
				'view' === $this->mode &&
				'only' !== $this->allow_insert ||
				( ! $this->child_request && 'view' === $this->action )
			) {
				$edit_form_class = 'WPDataProjects\\Parent_Child\\WPDP_Parent_Form_View';
				$mode            = 'view';
			} else {
				$edit_form_class = 'WPDataProjects\\Parent_Child\\WPDP_Parent_Form';
				$mode            = 'edit';
			}

			if ( null !== $this->parent_edit_form_class ) {
				$edit_form_class = $this->parent_edit_form_class;
			}

			$args = [
				'title'               => null === $this->title ? __( 'Back', 'wp-data-access' ) : $this->title,
				'subtitle'            => $this->subtitle,
				'add_action_to_title' => 'FALSE',
				'mode'                => $mode,
				'child_request'       => $this->child_request,
			];
			if ( 'off' === $this->allow_insert || 'only' === $this->allow_insert ) {
				$args['hide_add_new'] = true;
			}
			if ( 'only' === $this->allow_insert ) {
				$args['action'] = 'new';
			}

			$this->wpda_list_columns = WPDP_List_Columns_Cache::get_list_columns( $this->schema_name, $this->table_name, 'tableform', $this->setname );
			$form                    = new $edit_form_class(
				$this->schema_name,
				$this->table_name,
				$this->wpda_list_columns,
				$args,
				[
					'parent'   => $this->parent,
					'children' => $this->children,
				]
			);

			$form->show();
		}

		/**
		 * Overwrites method display_list_table to add parent-child functionality
		 */
		protected function display_list_table() {
			$this->wpda_list_columns = WPDP_List_Columns_Cache::get_list_columns( $this->schema_name, $this->table_name, 'listtable', $this->setname );
			$this->wpda_list_table   = new $this->list_table_class(
				[
					'wpdaschema_name'   => $this->schema_name,
					'table_name'        => $this->table_name,
					'wpda_list_columns' => $this->wpda_list_columns,
					'column_headers'    => $this->column_headers,
					'project'           => $this->project,
					'where_clause'      => $this->where_clause,
					'orderby_clause'    => $this->orderby_clause,
					'default_where'     => $this->default_where,
					'allow_delete'      => $this->allow_delete,
					'allow_import'      => $this->allow_import,
					'allow_update'      => $this->allow_update,
					'allow_insert'      => $this->allow_insert,
				]
			);

			$this->wpda_list_table->set_bulk_actions_enabled( $this->bulk_actions_enabled );
			$this->wpda_list_table->set_search_box_enabled( $this->search_box_enabled );

			if ( null !== $this->title ) {
				$this->wpda_list_table->set_title( $this->title );
			}
			if ( null !== $this->subtitle ) {
				$this->wpda_list_table->set_subtitle( $this->subtitle );
			}

			$this->wpda_list_table->show();
		}

	}

}
