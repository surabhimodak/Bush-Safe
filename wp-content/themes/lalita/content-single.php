<?php
/**
 * The template for displaying single posts.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> <?php lalita_article_schema( 'CreativeWork' ); ?>>
	<div class="inside-article">
		<?php
		/**
		 * lalita_before_content hook.
		 *
		 *
		 * @hooked lalita_featured_page_header_inside_single - 10
		 */
		do_action( 'lalita_before_content' );
		?>

		<header class="entry-header">
			<?php
			/**
			 * lalita_before_entry_title hook.
			 *
			 */
			do_action( 'lalita_before_entry_title' );

			if ( lalita_show_title() ) {
				the_title( '<h1 class="entry-title" itemprop="headline">', '</h1>' );
			}

			/**
			 * lalita_after_entry_title hook.
			 *
			 *
			 * @hooked lalita_post_meta - 10
			 */
			do_action( 'lalita_after_entry_title' );
			?>
		</header><!-- .entry-header -->

		<?php
		/**
		 * lalita_after_entry_header hook.
		 *
		 *
		 * @hooked lalita_post_image - 10
		 */
		do_action( 'lalita_after_entry_header' );
		?>

		<div class="entry-content" itemprop="text">
			<?php
			the_content();

			wp_link_pages( array(
				'before' => '<div class="page-links">' . __( 'Pages:', 'lalita' ),
				'after'  => '</div>',
			) );
			?>
		</div><!-- .entry-content -->

		<?php
		/**
		 * lalita_after_entry_content hook.
		 *
		 *
		 * @hooked lalita_footer_meta - 10
		 */
		do_action( 'lalita_after_entry_content' );

		/**
		 * lalita_after_content hook.
		 *
		 */
		do_action( 'lalita_after_content' );
		?>
	</div><!-- .inside-article -->
</article><!-- #post-## -->
