<?php

/**
 * Suppress "error - 0 - No summary was found for this file" on phpdoc generation
 *
 * @package WPDataProjects\Project
 */
namespace WPDataProjects\Project;

use  WPDataAccess\WPDA ;
use  WPDataProjects\Parent_Child\WPDP_Child_List_Table ;
/**
 * Class WPDP_Project_Page_List extends WPDP_Child_List_Table
 *
 * @see WPDP_Child_List_Table
 *
 * @author  Peter Schulz
 * @since   2.0.0
 */
class WPDP_Project_Page_List extends WPDP_Child_List_Table
{
    /**
     * WPDP_Project_Page_List constructor
     *
     * Add specific column headers to list table
     *
     * @param array $args
     */
    public function __construct( array $args = array() )
    {
        // Add column labels.
        $args['column_headers'] = self::column_headers_labels();
        // Show action links in column page_name
        $this->first_display_column = 'page_id';
        parent::__construct( $args );
    }
    
    /**
     * Overwrites method column_default to support static pages
     *
     * @param array  $item
     * @param string $column_name
     *
     * @return mixed|string
     */
    public function column_default( $item, $column_name )
    {
        
        if ( 'static' === $item['page_type'] && ('page_table_name' === $column_name || 'page_mode' === $column_name || 'page_allow_insert' === $column_name || 'page_allow_delete' === $column_name) ) {
            return '';
        } else {
            
            if ( 'static' !== $item['page_type'] && 'page_content' === $column_name ) {
                return '';
            } else {
                return parent::column_default( $item, $column_name );
            }
        
        }
    
    }
    
    /**
     * Add action "show shortcode"
     *
     * @param array  $item
     * @param string $column_name
     * @param array  $actions
     */
    protected function column_default_add_action( $item, $column_name, &$actions )
    {
        $link_title = 'Get shortcode';
        $title = 'Shortcode';
        ?>
			<div id="wpda_project_<?php 
        echo  esc_attr( $item['page_id'] ) ;
        ?>"
				 title="<?php 
        echo  __( 'Shortcodes', 'wp-data-access' ) ;
        ?>"
				 style="display:none"
			>
				<p>
					Copy shortcode into your post or page to make this Data Project page available on your website.
				</p>

				<?php 
        ?>

				<p class="wpda_shortcode_text">
					<strong>
						[wpdadiehard project_id="<?php 
        echo  esc_attr( $item['project_id'] ) ;
        ?>" page_id="<?php 
        echo  esc_attr( $item['page_id'] ) ;
        ?>"]
					</strong>
				</p>
				<p class="wpda_shortcode_buttons">
					<button class="button wpda_shortcode_clipboard wpda_shortcode_button"
							type="button"
							data-clipboard-text='[wpdadiehard project_id="<?php 
        echo  esc_attr( $item['project_id'] ) ;
        ?>" page_id="<?php 
        echo  esc_attr( $item['page_id'] ) ;
        ?>"]'
							onclick="jQuery.notify('<?php 
        echo  __( 'Shortcode successfully copied to clipboard!' ) ;
        ?>','info')"
					>
						<?php 
        echo  __( 'Copy', 'wp-data-access' ) ;
        ?>
					</button>
					<button class="button button-primary wpda_shortcode_button"
							type="button"
							onclick="jQuery('.ui-dialog-content').dialog('close')"
					>
						<?php 
        echo  __( 'Close', 'wp-data-access' ) ;
        ?>
					</button>
				</p>
				<?php 
        $shortcode_enabled = 'on' === WPDA::get_option( WPDA::OPTION_PLUGIN_WPDADIEHARD_POST ) && 'on' === WPDA::get_option( WPDA::OPTION_PLUGIN_WPDADIEHARD_PAGE );
        if ( !$shortcode_enabled ) {
            ?>
					<p>
						Shortcode wpdadiehard is not enabled for all output types.<br/>
						<a href="options-general.php?page=wpdataaccess" class="wpda_shortcode_link">&raquo; Manage settings</a>
					</p>
					<?php 
        }
        ?>
			</div>
			<?php 
        $actions['shortcode2'] = sprintf(
            '<a href="javascript:void(0)" 
						class="view wpda_tooltip"  
						title="%s"
						onclick="jQuery(\'#wpda_project_%s\').dialog({width:400})"
						<span style="white-space:nowrap">
							<span class="material-icons wpda_icon_on_button">code</span>
							%s
						</span>
					</a>
					',
            $link_title,
            esc_attr( $item['page_id'] ),
            $title
        );
    }
    
    public static function column_headers_labels()
    {
        return [
            'project_id'        => __( 'Project ID', 'wp-data-access' ),
            'page_id'           => __( 'Page ID', 'wp-data-access' ),
            'page_name'         => __( 'Menu Name', 'wp-data-access' ),
            'add_to_menu'       => __( 'Add To Menu', 'wp-data-access' ),
            'page_type'         => __( 'Page Type', 'wp-data-access' ),
            'page_schema_name'  => __( 'Database', 'wp-data-access' ),
            'page_table_name'   => __( 'Table Name', 'wp-data-access' ),
            'page_setname'      => __( 'Template Set Name', 'wp-data-access' ),
            'page_mode'         => __( 'Mode', 'wp-data-access' ),
            'page_allow_insert' => __( 'Allow insert?', 'wp-data-access' ),
            'page_allow_delete' => __( 'Allow delete?', 'wp-data-access' ),
            'page_content'      => __( 'Post', 'wp-data-access' ),
            'page_title'        => __( 'Title', 'wp-data-access' ),
            'page_subtitle'     => __( 'Subtitle', 'wp-data-access' ),
            'page_role'         => __( 'Role', 'wp-data-access' ),
            'page_where'        => __( 'Default WHERE', 'wp-data-access' ),
            'page_orderby'      => __( 'Default ORDER BY', 'wp-data-access' ),
            'page_sequence'     => __( 'Seq#', 'wp-data-access' ),
        ];
    }
    
    // Overwrite method
    public function show()
    {
        parent::show();
        WPDA::shortcode_popup();
    }

}