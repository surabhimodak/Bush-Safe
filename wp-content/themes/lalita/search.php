<?php
/**
 * The template for displaying Search Results pages.
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

			if ( have_posts() ) : ?>

				<header class="page-header">
					<h1 class="page-title">
						<?php
						printf( 
							/* translators: 1: Search query name */
							esc_html__( 'Search Results for: %s', 'lalita' ),
							'<span>' . get_search_query() . '</span>'
						);
						?>
					</h1>
				</header><!-- .page-header -->

				<?php while ( have_posts() ) : the_post();

					get_template_part( 'content', 'search' );

				endwhile;

				lalita_content_nav( 'nav-below' );

			else :

				get_template_part( 'no-results', 'search' );

			endif;

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
