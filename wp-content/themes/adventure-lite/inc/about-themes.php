<?php
//about theme info
add_action( 'admin_menu', 'adventure_lite_abouttheme' );
function adventure_lite_abouttheme() {    	
	add_theme_page( esc_html__('About Theme', 'adventure-lite'), esc_html__('About Theme', 'adventure-lite'), 'edit_theme_options', 'adventure_lite_guide', 'adventure_lite_mostrar_guide');   
} 

//guidline for about theme
function adventure_lite_mostrar_guide() { 
	//custom function about theme customizer
	$return = add_query_arg( array()) ;
?>
<div class="wrapper-info">
	<div class="col-left">
   		   <div class="col-left-area">
			  <?php esc_attr_e('Theme Information', 'adventure-lite'); ?>
		   </div>
          <p><?php esc_html_e('SKT Adventure Lite WordPress theme can be used for adventure, sports, hiking, trekking, railing, rafting, games, fun, elking, hunting, military, mountain climbing, skiing, surfing and other such adventure sports. Also can be used for tours and travels, camping, hotel, students, summer camps, skating, motels, service industry, and other corporate, business, photography and personal portfolio websites. It is multipurpose template and comes with a ready to import Elementor template plugin as add on which allows to import 63+ design templates for making use in home and other inner pages. Use it to create any type of business, personal, blog and eCommerce website. It is fast, flexible, simple and fully customizable. WooCommerce ready designs.','adventure-lite'); ?></p>
		  <a href="<?php echo esc_url(ADVENTURE_LITE_SKTTHEMES_PRO_THEME_URL); ?>"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/free-vs-pro.png" alt="" /></a>
	</div><!-- .col-left -->
	<div class="col-right">			
			<div class="centerbold">
				<hr />
				<a href="<?php echo esc_url(ADVENTURE_LITE_SKTTHEMES_LIVE_DEMO); ?>" target="_blank"><?php esc_html_e('Live Demo', 'adventure-lite'); ?></a> | 
				<a href="<?php echo esc_url(ADVENTURE_LITE_SKTTHEMES_PRO_THEME_URL); ?>"><?php esc_html_e('Buy Pro', 'adventure-lite'); ?></a> | 
				<a href="<?php echo esc_url(ADVENTURE_LITE_SKTTHEMES_THEME_DOC); ?>" target="_blank"><?php esc_html_e('Documentation', 'adventure-lite'); ?></a>
                <div class="space5"></div>
				<hr />                
                <a href="<?php echo esc_url(ADVENTURE_LITE_SKTTHEMES_THEMES); ?>" target="_blank"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/sktskill.jpg" alt="" /></a>
			</div>		
	</div><!-- .col-right -->
</div><!-- .wrapper-info -->
<?php } ?>