<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Project
 */
namespace WPDataProjects\Project;

use  WPDataAccess\WPDA ;
use  WPDataProjects\Parent_Child\WPDP_Parent_List_Table ;
use  WPDataAccess\Utilities\WPDA_Import_Multi ;
/**
 * Class WPDP_Project_Project_List extends WPDP_Parent_List_Table
 *
 * @see WPDP_Parent_List_Table
 *
 * @author  Peter Schulz
 * @since   2.0.0
 */
class WPDP_Project_Project_List extends WPDP_Parent_List_Table
{
    /**
     * WPDP_Project_Project_List constructor
     *
     * @param array $args
     */
    public function __construct( array $args = array() )
    {
        $args['column_headers'] = self::column_headers_labels();
        $args['title'] = '';
        $args['allow_import'] = 'off';
        $args['allow_insert'] = 'on';
        parent::__construct( $args );
        try {
            // Instantiate WPDA_Import.
            $this->wpda_import = new WPDA_Import_Multi( "?page={$this->page}", $this->schema_name, [ __( 'IMPORT DATA PROJECTS', 'wp-data-access' ), '' ] );
        } catch ( \Exception $e ) {
            // If import is turned off instantition will fail. Handle is set to null (check in future calls).
            $this->wpda_import = null;
        }
    }
    
    /**
     * Overwrites method add_header_button to add arguments to insert button
     *
     * @param string $add_param
     */
    protected function add_header_button( $add_param = '' )
    {
        ?>
			<form
					method="post"
					action="?page=<?php 
        echo  esc_attr( $this->page ) ;
        ?>"
					style="display: inline-block; vertical-align: unset;"
			>
				<div>
					<input type="hidden" name="action" value="new">
					<input type="hidden" name="mode" value="edit">
					<input type="hidden" name="table_name" value="<?php 
        echo  esc_attr( $this->table_name ) ;
        ?>">
					<button type="submit" class="page-title-action wpda_tooltip"
							title="Add new project to repository"
					>
						<span class="material-icons wpda_icon_on_button">add_circle</span>
						<?php 
        echo  __( 'Add New', 'wp-data-access' ) ;
        ?>
					</button>
				</div>
			</form>
			<?php 
        if ( null !== $this->wpda_import ) {
            $this->wpda_import->add_button( __( 'Import', 'wp-data-access' ) );
        }
    }
    
    /**
     * Overwrites method column_default_add_action to rewrite export action
     *
     * Default export supports single table export only. Project export support exporting child rows as well.
     *
     * @param array  $item
     * @param string $column_name
     * @param array  $actions
     */
    protected function column_default_add_action( $item, $column_name, &$actions )
    {
        parent::column_default_add_action( $item, $column_name, $actions );
        $wp_nonce_action = 'wpdp-export-project-' . $item['project_id'];
        $wp_nonce = wp_create_nonce( $wp_nonce_action );
        $src = '?action=wpda_export_project&project_id=' . $item['project_id'] . '&wpnonce=' . $wp_nonce;
        $actions['export'] = sprintf( '
					<a href="%s" target="_blank" title="Export project" class="wpda_tooltip">
						<span style="white-space:nowrap">
							<span class="material-icons wpda_icon_on_button">cloud_download</span>
							%s
						</span>
					</a>
				', $src, __( 'Export', 'wp-data-access' ) );
    }
    
    public static function column_headers_labels()
    {
        return [
            'project_id'          => __( 'Project ID', 'wp-data-access' ),
            'project_name'        => __( 'Project Name', 'wp-data-access' ),
            'project_description' => __( 'Project Description', 'wp-data-access' ),
            'add_to_menu'         => __( 'Add To Menu', 'wp-data-access' ),
            'menu_name'           => __( 'Menu Name', 'wp-data-access' ),
            'project_sequence'    => __( 'Seq#', 'wp-data-access' ),
        ];
    }
    
    // Overwrite method
    public function show()
    {
        parent::show();
        WPDA::shortcode_popup();
    }
    
    public function get_bulk_actions()
    {
        $actions = parent::get_bulk_actions();
        unset(
            $actions['bulk-export'],
            $actions['bulk-export-xml'],
            $actions['bulk-export-json'],
            $actions['bulk-export-excel'],
            $actions['bulk-export-csv']
        );
        return $actions;
    }

}