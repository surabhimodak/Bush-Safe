<?php
/**
 * Footer elements.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'lalita_construct_footer' ) ) {
	add_action( 'lalita_footer', 'lalita_construct_footer' );
	/**
	 * Build our footer.
	 *
	 */
	function lalita_construct_footer() {
		?>
		<footer class="site-info" itemtype="https://schema.org/WPFooter" itemscope="itemscope">
			<div class="inside-site-info <?php if ( 'full-width' !== lalita_get_setting( 'footer_inner_width' ) ) : ?>grid-container grid-parent<?php endif; ?>">
				<?php
				/**
				 * lalita_before_copyright hook.
				 *
				 *
				 * @hooked lalita_footer_bar - 15
				 */
				do_action( 'lalita_before_copyright' );
				?>
				<div class="copyright-bar">
					<?php
					/**
					 * lalita_credits hook.
					 *
					 *
					 * @hooked lalita_add_footer_info - 10
					 */
					do_action( 'lalita_credits' );
					?>
				</div>
			</div>
		</footer><!-- .site-info -->
		<?php
	}
}

if ( ! function_exists( 'lalita_footer_bar' ) ) {
	add_action( 'lalita_before_copyright', 'lalita_footer_bar', 15 );
	/**
	 * Build our footer bar
	 *
	 */
	function lalita_footer_bar() {
		if ( ! is_active_sidebar( 'footer-bar' ) ) {
			return;
		}
		?>
		<div class="footer-bar">
			<?php dynamic_sidebar( 'footer-bar' ); ?>
		</div>
		<?php
	}
}

if ( ! function_exists( 'lalita_add_footer_info' ) ) {
	add_action( 'lalita_credits', 'lalita_add_footer_info' );
	/**
	 * Add the copyright to the footer
	 *
	 */
	function lalita_add_footer_info() {
		echo '<span class="copyright">&copy; ' . esc_html( date( 'Y' ) ) . ' ' . esc_html( get_bloginfo( 'name' ) ) . '</span> &bull; ' . esc_html__( 'Powered by', 'lalita' ) . ' <a href="' . esc_url( lalita_theme_uri_link() ) . '" itemprop="url">' . esc_html__( 'WPKoi', 'lalita' ) . '</a>';
	}
}

/**
 * Build our individual footer widgets.
 * Displays a sample widget if no widget is found in the area.
 *
 *
 * @param int $widget_width The width class of our widget.
 * @param int $widget The ID of our widget.
 */
function lalita_do_footer_widget( $widget_width, $widget ) {
	$widget_width = apply_filters( "lalita_footer_widget_{$widget}_width", $widget_width );
	$tablet_widget_width = apply_filters( "lalita_footer_widget_{$widget}_tablet_width", '50' );
	?>
	<div class="footer-widget-<?php echo absint( $widget ); ?> grid-parent grid-<?php echo absint( $widget_width ); ?> tablet-grid-<?php echo absint( $tablet_widget_width ); ?> mobile-grid-100">
		<?php if ( ! dynamic_sidebar( 'footer-' . absint( $widget ) ) ) :
	        $current_user = wp_get_current_user();
	        if (user_can( $current_user, 'administrator' )) { ?>
			<aside class="widget inner-padding widget_text">
				<h4 class="widget-title"><?php esc_html_e( 'Footer Widget', 'lalita' );?></h4>
				<div class="textwidget">
					<p>
						<?php esc_html_e( 'Replace this widget content by going to ', 'lalita' ); ?><a href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>"><strong><?php esc_html_e('Appearance / Widgets', 'lalita' ); ?></strong></a><?php esc_html_e( ' and dragging widgets into this widget area.', 'lalita' ); ?>
					</p>
					<p>
						<?php esc_html_e( 'To remove or choose the number of footer widgets, go to ', 'lalita' ); ?><a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><strong><?php esc_html_e('Appearance / Customize / Layout / Footer Widgets', 'lalita' ); ?></strong></a><?php esc_html_e( '.', 'lalita' ); ?>
					</p>
				</div>
			</aside>
		<?php } endif; ?>
	</div>
	<?php
}

if ( ! function_exists( 'lalita_construct_footer_widgets' ) ) {
	add_action( 'lalita_footer', 'lalita_construct_footer_widgets', 5 );
	/**
	 * Build our footer widgets.
	 *
	 */
	function lalita_construct_footer_widgets() {
		// Get how many widgets to show.
		$widgets = lalita_get_footer_widgets();

		if ( ! empty( $widgets ) && 0 !== $widgets ) :

			// Set up the widget width.
			$widget_width = '';
			if ( $widgets == 1 ) {
				$widget_width = '100';
			}

			if ( $widgets == 2 ) {
				$widget_width = '50';
			}

			if ( $widgets == 3 ) {
				$widget_width = '33';
			}

			if ( $widgets == 4 ) {
				$widget_width = '25';
			}

			if ( $widgets == 5 ) {
				$widget_width = '20';
			}
			?>
			<div id="footer-widgets" class="site footer-widgets">
				<div <?php lalita_inside_footer_class(); ?>>
					<div class="inside-footer-widgets">
						<?php
						if ( $widgets >= 1 ) {
							lalita_do_footer_widget( $widget_width, 1 );
						}

						if ( $widgets >= 2 ) {
							lalita_do_footer_widget( $widget_width, 2 );
						}

						if ( $widgets >= 3 ) {
							lalita_do_footer_widget( $widget_width, 3 );
						}

						if ( $widgets >= 4 ) {
							lalita_do_footer_widget( $widget_width, 4 );
						}

						if ( $widgets >= 5 ) {
							lalita_do_footer_widget( $widget_width, 5 );
						}
						?>
					</div>
				</div>
			</div>
		<?php
		endif;

		/**
		 * lalita_after_footer_widgets hook.
		 *
		 */
		do_action( 'lalita_after_footer_widgets' );
	}
}

if ( ! function_exists( 'lalita_back_to_top' ) ) {
	add_action( 'lalita_after_footer', 'lalita_back_to_top', 2 );
	/**
	 * Build the back to top button
	 *
	 */
	function lalita_back_to_top() {
		$lalita_settings = wp_parse_args(
			get_option( 'lalita_settings', array() ),
			lalita_get_defaults()
		);

		if ( 'enable' !== $lalita_settings[ 'back_to_top' ] ) {
			return;
		}

		echo '<a title="' . esc_attr__( 'Scroll back to top', 'lalita' ) . '" rel="nofollow" href="#" class="lalita-back-to-top" style="opacity:0;visibility:hidden;" data-scroll-speed="' . absint( apply_filters( 'lalita_back_to_top_scroll_speed', 400 ) ) . '" data-start-scroll="' . absint( apply_filters( 'lalita_back_to_top_start_scroll', 300 ) ) . '">
				<span class="screen-reader-text">' . esc_html__( 'Scroll back to top', 'lalita' ) . '</span>
			</a>';
	}
}

add_action( 'lalita_after_footer', 'lalita_side_padding_footer', 5 );
/**
 * Add holder div if sidebar padding is enabled
 *
 */
function lalita_side_padding_footer() { 
	$lalita_settings = wp_parse_args(
		get_option( 'lalita_spacing_settings', array() ),
		lalita_spacing_get_defaults()
	);
	
	$fixed_side_content   =  lalita_get_setting( 'fixed_side_content' ); 
	$socials_display_side =  lalita_get_setting( 'socials_display_side' ); 
	
	if ( ( $lalita_settings[ 'side_top' ] != 0 ) || ( $lalita_settings[ 'side_right' ] != 0 ) || ( $lalita_settings[ 'side_bottom' ] != 0 ) || ( $lalita_settings[ 'side_left' ] != 0 ) ) { ?>
    	<div class="lalita-side-left-cover"></div>
    	<div class="lalita-side-right-cover"></div>
	</div>
	<?php } 
	if ( ( $fixed_side_content != '' ) || ( $socials_display_side == true ) ) { ?>
    <div class="lalita-side-left-content">
        <?php if ( $socials_display_side == true ) { ?>
        <div class="lalita-side-left-socials">
        <?php do_action( 'lalita_social_bar_action' ); ?>
        </div>
        <?php } ?>
        <?php if ( $fixed_side_content != '' ) { ?>
    	<div class="lalita-side-left-text">
        <?php echo wp_kses_post( $fixed_side_content ); ?>
        </div>
        <?php } ?>
    </div>
    <?php
	}
}
