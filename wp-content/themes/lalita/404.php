<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header(); ?>

	<div id="primary" <?php lalita_content_class(); ?>>
		<main id="main" <?php lalita_main_class(); ?>>
			<?php
			/**
			 * lalita_before_main_content hook.
			 *
			 */
			do_action( 'lalita_before_main_content' );
			?>

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
					<h1 class="entry-title" itemprop="headline"><?php echo esc_html( apply_filters( 'lalita_404_title', __( 'Oops! That page can&rsquo;t be found.', 'lalita' ) ) ); ?></h1>
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
					echo '<p>' . esc_html( apply_filters( 'lalita_404_text', __( 'It looks like nothing was found at this location. Maybe try searching?', 'lalita' ) ) ) . '</p>';

					get_search_form();
					?>
				</div><!-- .entry-content -->

				<?php
				/**
				 * lalita_after_content hook.
				 *
				 */
				do_action( 'lalita_after_content' );
				?>

			</div><!-- .inside-article -->

			<?php
			/**
			 * lalita_after_main_content hook.
			 *
			 */
			do_action( 'lalita_after_main_content' );
			?>
		</main><!-- #main -->
	</div><!-- #primary -->

	<?php
	/**
	 * lalita_after_primary_content_area hook.
	 *
	 */
	 do_action( 'lalita_after_primary_content_area' );

	 lalita_construct_sidebars();

get_footer();
