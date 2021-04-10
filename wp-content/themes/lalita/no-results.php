<?php
/**
 * The template part for displaying a message that posts cannot be found.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div class="no-results not-found">
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
			<h1 class="entry-title"><?php esc_html_e( 'Nothing Found', 'lalita' ); // WPCS: XSS OK. ?></h1>
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

		<div class="entry-content">

				<?php if ( is_search() ) : ?>

					<p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'lalita' ); // WPCS: XSS OK. ?></p>
					<?php get_search_form(); ?>

				<?php else : ?>

					<p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'lalita' ); // WPCS: XSS OK. ?></p>
					<?php get_search_form(); ?>

				<?php endif; ?>

		</div><!-- .entry-content -->

		<?php
		/**
		 * lalita_after_content hook.
		 *
		 */
		do_action( 'lalita_after_content' );
		?>
	</div><!-- .inside-article -->
</div><!-- .no-results -->
