<?php
/**
 * The template for displaying Archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Adventure Lite
 */

get_header(); ?>

<div class="container">
  <div class="page_content">
    <section class="site-main">
			<?php if ( have_posts() ) : ?>
                <header class="page-header">
                   <?php
					the_archive_title( '<h1 class="entry-title">', '</h1>' );
					the_archive_description( '<div class="taxonomy-description">', '</div>' );
				  ?>
                </header><!-- .page-header -->
				<div class="blog-post">
					<?php /* Start the Loop */ ?>
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php get_template_part( 'content', get_post_format() ); ?>
                    <?php endwhile; ?>
                </div>
                <?php  
				// Previous/next post navigation.
				the_posts_pagination( array(
							'mid_size' => 2,
							'prev_text' => esc_html__( 'Back', 'adventure-lite' ),
							'next_text' => esc_html__( 'Next', 'adventure-lite' ),
							'screen_reader_text' => esc_html__( 'Posts navigation', 'adventure-lite' )
				) );
			    ?>
            <?php else : ?>
                <?php get_template_part( 'no-results'); ?>
            <?php endif; ?>
        </section>
    <?php get_sidebar();?>
    <div class="clear"></div>
  </div>
  <!-- site-aligner --> 
</div>
<!-- container -->

<?php get_footer(); ?>