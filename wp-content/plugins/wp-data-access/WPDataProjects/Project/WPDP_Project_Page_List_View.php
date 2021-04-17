<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Project
 */

namespace WPDataProjects\Project {

	use WPDataProjects\Parent_Child\WPDP_Child_List_Table_View;

	/**
	 * Class WPDP_Project_Page_List_View extends WPDP_Child_List_Table_View
	 *
	 * @see WPDP_Child_List_Table_View
	 *
	 * @author  Peter Schulz
	 * @since   2.0.0
	 */
	class WPDP_Project_Page_List_View extends WPDP_Child_List_Table_View {

		/**
		 * WPDP_Project_Page_List_View constructor
		 *
		 * @param array $args
		 */
		public function __construct( array $args = [] ) {
			// Add column labels.
			$args['column_headers'] = [
				'project_id'        => __( 'Project ID', 'wp-data-access' ),
				'page_id'           => __( 'Page ID', 'wp-data-access' ),
				'page_name'         => __( 'Menu Name', 'wp-data-access' ),
				'page_type'         => __( 'Type', 'wp-data-access' ),
				'page_table_name'   => __( 'Table Name', 'wp-data-access' ),
				'page_mode'         => __( 'Mode', 'wp-data-access' ),
				'page_allow_insert' => __( 'Allow insert?', 'wp-data-access' ),
				'page_allow_delete' => __( 'Allow delete?', 'wp-data-access' ),
				'page_content'      => __( 'Content', 'wp-data-access' ),
				'page_title'        => __( 'Title', 'wp-data-access' ),
				'page_subtitle'     => __( 'Subtitle', 'wp-data-access' ),
				'page_sequence'     => __( 'Seq#', 'wp-data-access' ),
			];

			parent::__construct( $args );
		}

	}

}