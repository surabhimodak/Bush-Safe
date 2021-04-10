<?php
/**
 * The template for displaying the footer.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

	</div><!-- #content -->
</div><!-- #page -->

<?php
/**
 * lalita_before_footer hook.
 *
 */
do_action( 'lalita_before_footer' );
?>

<div <?php lalita_footer_class(); ?>>
	<?php
	/**
	 * lalita_before_footer_content hook.
	 *
	 */
	do_action( 'lalita_before_footer_content' );

	/**
	 * lalita_footer hook.
	 *
	 *
	 * @hooked lalita_construct_footer_widgets - 5
	 * @hooked lalita_construct_footer - 10
	 */
	do_action( 'lalita_footer' );

	/**
	 * lalita_after_footer_content hook.
	 *
	 */
	do_action( 'lalita_after_footer_content' );
	?>
</div><!-- .site-footer -->

<?php
/**
 * lalita_after_footer hook.
 *
 */
do_action( 'lalita_after_footer' );

wp_footer();
?>

</body>
</html>
