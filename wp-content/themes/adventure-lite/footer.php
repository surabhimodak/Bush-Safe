<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package Adventure Lite
 */
?>

<div id="footer-wrapper">
  <div class="container footer">
    <div class="cols-3 widget-column-1">
      <?php $contact_title = get_theme_mod('contact_title'); ?>
      <?php if (!empty($contact_title)){  ?>
      <h5><?php echo esc_html($contact_title); ?></h5>
      <?php } ?>
      <div class="phone-no">
        <?php $contact_add = get_theme_mod('contact_add');?>
        <?php if (!empty($contact_add)) { ?>
        <p><img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/location-icon.png" alt="" /><?php echo wp_kses_post($contact_add); ?></p>
        <?php } ?>
        <?php $contact_no = get_theme_mod('contact_no'); ?>
        <?php if (!empty($contact_no)) { ?>
        <p> <img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/footer-icon-phone.png" alt="" /> <?php echo esc_html($contact_no); ?></p>
        <?php } ?>
        <?php $contact_mail = get_theme_mod('contact_mail'); ?>
        <?php if(!empty($contact_mail)){ ?>
        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/footer-icon-email.png" alt="" /><a href="mailto:<?php echo esc_attr( antispambot(sanitize_email( $contact_mail ) )); ?>"><?php echo esc_html( antispambot( $contact_mail ) ); ?></a>
        <?php } ?>
      </div>
      <div class="social-icons">
        <?php $fb_link = get_theme_mod('fb_link'); ?>
        <?php if (!empty($fb_link)) { ?>
        <a title="<?php esc_attr__('facebook', 'adventure-lite');?>" class="fb" target="_blank" href="<?php echo esc_url($fb_link); ?>"></a>
        <?php } ?>
        <?php $twitt_link = get_theme_mod('twitt_link');?>
        <?php if (!empty($twitt_link)) { ?>
        <a title="<?php esc_attr__('twitter', 'adventure-lite');?>" class="tw" target="_blank" href="<?php echo esc_url($twitt_link); ?>"></a>
        <?php } ?>
        <?php $gplus_link = get_theme_mod('gplus_link'); ?>
        <?php if (!empty($gplus_link)) { ?>
        <a title="<?php esc_attr__('google-plus', 'adventure-lite');?>" class="gp" target="_blank" href="<?php echo esc_url($gplus_link); ?>"></a>
        <?php } ?>
        <?php $linked_link = get_theme_mod('linked_link'); ?>
        <?php if (!empty($linked_link)) { ?>
        <a title="<?php esc_attr__('linkedin', 'adventure-lite');?>" class="in" target="_blank" href="<?php echo esc_url($linked_link); ?>"></a>
        <?php } ?>
      </div>
    </div>
    <!--end .widget-column-1-->
    <div class="cols-3 widget-column-2">
      <?php $newsfeed_title = get_theme_mod('newsfeed_title'); ?>
      <?php if (!empty($newsfeed_title)) { ?>
      <h5><?php echo esc_html($newsfeed_title); ?></h5>
      <?php } ?>
      <?php $args = array( 'posts_per_page' => 2, 'post__not_in' => get_option('sticky_posts'), 'orderby' => 'date', 'order' => 'desc' );
					$postquery = new WP_Query( $args );
					?>
      <?php while( $postquery->have_posts() ) : $postquery->the_post(); ?>
      <div class="recent-post"> <a href="<?php echo esc_url( get_permalink() ); ?>">
        <?php the_post_thumbnail('thumbnail'); ?>
        </a>
        <p><a href="<?php the_permalink(); ?>">
          <?php the_title(); ?>
          </a><br/>
          <?php the_excerpt(); ?>
        </p>
        <a class="morebtn" href="<?php echo esc_url( get_permalink() ); ?>">
        <?php esc_html_e('Read More','adventure-lite'); ?>
        </a> </div>
      <?php endwhile; ?>
      <?php wp_reset_postdata(); ?>
    </div>
    <!--end .widget-column-3-->
    
    <div class="cols-3 widget-column-3">
      <?php $about_title = get_theme_mod('about_title'); ?>
      <?php if (!empty($about_title)) { ?>
      <h5><?php echo esc_html($about_title);?></h5>
      <?php } ?>
      <?php $about_description = get_theme_mod('about_description'); ?>
      <?php if (!empty($about_description)) { ?>
      <p><?php echo wp_kses_post($about_description);?></p>
      <?php } ?>
    </div>
    <!--end .widget-column-4-->
    
    <div class="clear"></div>
  </div>
  <!--end .container-->
  
  <div class="copyright-wrapper">
    <div class="container">
      <div class="copyright-txt">&nbsp;</div>
      <div class="design-by"><?php echo esc_html('SKT Adventure Lite');?></div>
      <div class="clear"></div>
    </div>
  </div>
</div>
<!--end .footer-wrapper-->
<?php wp_footer(); ?>
</body></html>