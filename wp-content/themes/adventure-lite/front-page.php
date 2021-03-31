<?php
/**
 * The template for displaying home page.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package Adventure Lite
 */
get_header(); ?>
<?php
$hideslide = get_theme_mod('hide_slides', 1);
$hide_pagethreeboxes = get_theme_mod('hide_pagethreeboxes', 1);
$hide_infoboxes = get_theme_mod('hide_infobox', 1);
?>
<?php if (!is_home() && is_front_page()) { ?>
<?php if( $hideslide == '') { ?>
<!-- Slider Section -->
<?php for($sld=7; $sld<10; $sld++) { ?>
<?php if( get_theme_mod('page-setting'.$sld)) { ?>
<?php $slidequery = new WP_query('page_id='.get_theme_mod('page-setting'.$sld,true)); ?>
<?php while( $slidequery->have_posts() ) : $slidequery->the_post();
        $image = wp_get_attachment_url( get_post_thumbnail_id($post->ID));
        $img_arr[] = $image;
        $id_arr[] = $post->ID;
        endwhile;
  	  }
	  wp_reset_postdata();
    }
?>
<?php if(!empty($id_arr)){ ?>

<section id="home_slider">
  <div class="slider-wrapper theme-default">
    <div id="slider" class="nivoSlider">
      <?php 
	$i=1;
	foreach($img_arr as $url){ ?>
      <?php if(!empty($url)){?>	
      <img src="<?php echo esc_url($url); ?>" title="#slidecaption<?php echo esc_attr($i); ?>" />
      <?php }else{?>
      <img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/no_slide.jpg" title="#slidecaption<?php echo esc_attr($i); ?>" />
      <?php } ?>
	  <?php $i++; }  ?>
    </div>
    <?php 
        $i=1;
        foreach($id_arr as $id){ 
        $title = get_the_title( $id ); 
        $post = get_post($id); 
        $content = esc_html(wp_trim_words( $post->post_content, 35, '' ) );
        ?>
    <div id="slidecaption<?php echo esc_attr($i); ?>" class="nivo-html-caption">
      <div class="slide_info">
        <h2><?php echo wp_kses_post($title); ?></h2>
        <p><?php echo wp_kses_post($content); ?></p>
        <div class="clear"></div>
        <a class="slide_more" href="<?php the_permalink(); ?>">
        <?php esc_html_e('Read More', 'adventure-lite');?>
        </a> </div>
    </div>
    <?php $i++; } ?>
  </div>
  <div class="clear"></div>
</section>
<?php } } } ?>
<?php if (!is_home() && is_front_page()) { ?>
<?php if( $hide_infoboxes == '') { ?>
<section class="home1_section_area">
  <div class="center">
    <div class="home_section1_content">
      <?php for($ibox=12; $ibox<19; $ibox++) { ?>
      <?php if( get_theme_mod('pageinfobox-column'.$ibox,false)) { ?>
      <?php $infoboxquery = new WP_query('page_id='.get_theme_mod('pageinfobox-column'.$ibox,true)); ?>
      <?php while( $infoboxquery->have_posts() ) : $infoboxquery->the_post(); ?>
      <a href="<?php the_permalink(); ?>">
      <div class="squarebox">
        <div class="squarebox-content">
          <?php if( has_post_thumbnail() ) { ?>
          <div class="squareicon">
            <?php the_post_thumbnail();?>
          </div>
          <?php } ?>
          <div class="squaretitle">
            <?php the_title(); ?>
          </div>
          <div class="clear"></div>
        </div>
      </div>
      </a>
      <?php endwhile;
           wp_reset_postdata(); ?>
      <?php }} ?>
    </div>
  </div>
</section>
<?php } } ?>
<?php if (!is_home() && is_front_page()) { ?>
<?php if( $hide_pagethreeboxes == '') { ?>
<section id="pagearea">
  <div class="container">
    <?php $sectitle = get_theme_mod('section2_title'); ?>
    <?php if(!empty($sectitle)){?>
    <div class="center-title">
      <h2><?php echo esc_html($sectitle);?></h2>
    </div>
    <?php } ?>
    <div class="clear"></div>
    <div class="area_wrapper">
      <?php for($p=1; $p<4; $p++) { ?>
      <?php if( get_theme_mod('page-column'.$p,false)) { ?>
      <?php $querypagethreeboxes = new WP_query('page_id='.get_theme_mod('page-column'.$p,true)); ?>
      <?php while( $querypagethreeboxes->have_posts() ) : $querypagethreeboxes->the_post(); ?>
      <div class="blocksbox"> <a href="<?php the_permalink(); ?>">
        <?php if( has_post_thumbnail() ) { ?>
        <div class="blockthumb">
          <?php the_post_thumbnail();?>
        </div>
        <?php } ?>
        </a>
        <div class="blocktitle"> <a href="<?php the_permalink(); ?>">
          <h5>
            <?php the_title(); ?>
          </h5>
          </a> </div>
        <div class="blockdesc">
          <?php the_excerpt(); ?>
        </div>
        <div class="blockmore"><a href="<?php the_permalink(); ?>">
          <?php esc_html_e('READ MORE...', 'adventure-lite');?>
          </a></div>
      </div>
      <?php /*?><div class="threebox <?php if($p % 3 == 0) { echo "last_column"; } ?>">
     	<a href="<?php echo esc_url( get_permalink() ); ?>">
		 <?php if( has_post_thumbnail() ) { ?>
			<div class="thumbbx"><?php the_post_thumbnail();?></div>
          <?php } else { ?>
           <div class="thumbbx"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/images/img_404.jpg" alt="" /></div>
          <?php } ?>  
            <h3><?php the_title(); ?></h3>
        </a> 
		<?php the_excerpt(); ?>
        <a class="ReadMore" href="<?php the_permalink(); ?>">
          <?php esc_html_e('Read More', 'adventure-lite');?>
          </a>
      </div><?php */?>
      <?php endwhile;
       wp_reset_postdata(); ?>
      <?php }} ?>
      <div class="clear"></div>
    </div>
  </div>
  <!-- container --> 
</section>
<!-- #pagearea -->
<div class="clear"></div>
<?php } } ?>
<div class="container">
  <div class="page_content">
    <?php 
	if ( 'posts' == get_option( 'show_on_front' ) ) {
    ?>
    <section class="site-main">
      <div class="blog-post">
        <?php
                    if ( have_posts() ) :
                        // Start the Loop.
                        while ( have_posts() ) : the_post();
                            /*
                             * Include the post format-specific template for the content. If you want to
                             * use this in a child theme, then include a file called called content-___.php
                             * (where ___ is the post format) and that will be used instead.
                             */
                            get_template_part( 'content', get_post_format() );
                    
                        endwhile;
                        // Previous/next post navigation.
						the_posts_pagination( array(
							'mid_size' => 2,
							'prev_text' => esc_html__( 'Back', 'adventure-lite' ),
							'next_text' => esc_html__( 'Next', 'adventure-lite' ),
						) );
                    
                    else :
                        // If no content, include the "No posts found" template.
                         get_template_part( 'no-results', 'index' );
                    
                    endif;
                    ?>
      </div>
      <!-- blog-post --> 
    </section>
    <?php
} else {
    ?>
    <section class="site-main">
      <div class="blog-post">
        <?php
                    if ( have_posts() ) :
                        // Start the Loop.
                        while ( have_posts() ) : the_post();
                            /*
                             * Include the post format-specific template for the content. If you want to
                             * use this in a child theme, then include a file called called content-___.php
                             * (where ___ is the post format) and that will be used instead.
                             */
							 ?>
        <header class="entry-header">
          <h1>
            <?php the_title(); ?>
          </h1>
        </header>
        <?php
                            the_content();
                    
                        endwhile;
                        // Previous/next post navigation.
						the_posts_pagination( array(
							'mid_size' => 2,
							'prev_text' => esc_html__( 'Back', 'adventure-lite' ),
							'next_text' => esc_html__( 'Next', 'adventure-lite' ),
						) );
                    
                    else :
                        // If no content, include the "No posts found" template.
                         get_template_part( 'no-results', 'index' );
                    
                    endif;
                    ?>
      </div>
      <!-- blog-post --> 
    </section>
    <?php
}
	?>
    <?php get_sidebar();?>
    <div class="clear"></div>
  </div>
  <!-- site-aligner --> 
</div>
<!-- content -->
<?php get_footer(); ?>