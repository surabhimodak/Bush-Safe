<?php
/**
 * Builds our admin page.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'lalita_create_menu' ) ) {
	add_action( 'admin_menu', 'lalita_create_menu' );
	/**
	 * Adds our "Lalita" dashboard menu item
	 *
	 */
	function lalita_create_menu() {
		$lalita_page = add_theme_page( 'Lalita', 'Lalita', apply_filters( 'lalita_dashboard_page_capability', 'edit_theme_options' ), 'lalita-options', 'lalita_settings_page' );
		add_action( "admin_print_styles-$lalita_page", 'lalita_options_styles' );
	}
}

if ( ! function_exists( 'lalita_options_styles' ) ) {
	/**
	 * Adds any necessary scripts to the Lalita dashboard page
	 *
	 */
	function lalita_options_styles() {
		wp_enqueue_style( 'lalita-options', get_template_directory_uri() . '/css/admin/admin-style.css', array(), LALITA_VERSION );
	}
}

if ( ! function_exists( 'lalita_settings_page' ) ) {
	/**
	 * Builds the content of our Lalita dashboard page
	 *
	 */
	function lalita_settings_page() {
		?>
		<div class="wrap">
			<div class="metabox-holder">
				<div class="lalita-masthead clearfix">
					<div class="lalita-container">
						<div class="lalita-title">
							<a href="<?php echo esc_url(lalita_theme_uri_link()); ?>" target="_blank"><?php esc_html_e( 'Lalita', 'lalita' ); ?></a> <span class="lalita-version"><?php echo esc_html( LALITA_VERSION ); ?></span>
						</div>
						<div class="lalita-masthead-links">
							<?php if ( ! defined( 'LALITA_PREMIUM_VERSION' ) ) : ?>
								<a class="lalita-masthead-links-bold" href="<?php echo esc_url(lalita_theme_uri_link()); ?>" target="_blank"><?php esc_html_e( 'Premium', 'lalita' );?></a>
							<?php endif; ?>
							<a href="<?php echo esc_url(LALITA_WPKOI_AUTHOR_URL); ?>" target="_blank"><?php esc_html_e( 'WPKoi', 'lalita' ); ?></a>
                            <a href="<?php echo esc_url(LALITA_DOCUMENTATION); ?>" target="_blank"><?php esc_html_e( 'Documentation', 'lalita' ); ?></a>
						</div>
					</div>
				</div>

				<?php
				/**
				 * lalita_dashboard_after_header hook.
				 *
				 */
				 do_action( 'lalita_dashboard_after_header' );
				 ?>

				<div class="lalita-container">
					<div class="postbox-container clearfix" style="float: none;">
						<div class="grid-container grid-parent">

							<?php
							/**
							 * lalita_dashboard_inside_container hook.
							 *
							 */
							 do_action( 'lalita_dashboard_inside_container' );
							 ?>

							<div class="form-metabox grid-70" style="padding-left: 0;">
								<h2 style="height:0;margin:0;"><!-- admin notices below this element --></h2>
								<form method="post" action="options.php">
									<?php settings_fields( 'lalita-settings-group' ); ?>
									<?php do_settings_sections( 'lalita-settings-group' ); ?>
									<div class="customize-button hide-on-desktop">
										<?php
										printf( '<a id="lalita_customize_button" class="button button-primary" href="%1$s">%2$s</a>',
											esc_url( admin_url( 'customize.php' ) ),
											esc_html__( 'Customize', 'lalita' )
										);
										?>
									</div>

									<?php
									/**
									 * lalita_inside_options_form hook.
									 *
									 */
									 do_action( 'lalita_inside_options_form' );
									 ?>
								</form>

								<?php
								$modules = array(
									'Backgrounds' => array(
											'url' => lalita_theme_uri_link(),
									),
									'Blog' => array(
											'url' => lalita_theme_uri_link(),
									),
									'Colors' => array(
											'url' => lalita_theme_uri_link(),
									),
									'Copyright' => array(
											'url' => lalita_theme_uri_link(),
									),
									'Disable Elements' => array(
											'url' => lalita_theme_uri_link(),
									),
									'Demo Import' => array(
											'url' => lalita_theme_uri_link(),
									),
									'Hooks' => array(
											'url' => lalita_theme_uri_link(),
									),
									'Import / Export' => array(
											'url' => lalita_theme_uri_link(),
									),
									'Menu Plus' => array(
											'url' => lalita_theme_uri_link(),
									),
									'Page Header' => array(
											'url' => lalita_theme_uri_link(),
									),
									'Secondary Nav' => array(
											'url' => lalita_theme_uri_link(),
									),
									'Spacing' => array(
											'url' => lalita_theme_uri_link(),
									),
									'Typography' => array(
											'url' => lalita_theme_uri_link(),
									),
									'Elementor Addon' => array(
											'url' => lalita_theme_uri_link(),
									)
								);

								if ( ! defined( 'LALITA_PREMIUM_VERSION' ) ) : ?>
									<div class="postbox lalita-metabox">
										<h3 class="hndle"><?php esc_html_e( 'Premium Modules', 'lalita' ); ?></h3>
										<div class="inside" style="margin:0;padding:0;">
											<div class="premium-addons">
												<?php foreach( $modules as $module => $info ) { ?>
												<div class="add-on activated lalita-clear addon-container grid-parent">
													<div class="addon-name column-addon-name" style="">
														<a href="<?php echo esc_url( $info[ 'url' ] ); ?>" target="_blank"><?php echo esc_html( $module ); ?></a>
													</div>
													<div class="addon-action addon-addon-action" style="text-align:right;">
														<a href="<?php echo esc_url( $info[ 'url' ] ); ?>" target="_blank"><?php esc_html_e( 'More info', 'lalita' ); ?></a>
													</div>
												</div>
												<div class="lalita-clear"></div>
												<?php } ?>
											</div>
										</div>
									</div>
								<?php
								endif;

								/**
								 * lalita_options_items hook.
								 *
								 */
								do_action( 'lalita_options_items' );
								?>
							</div>

							<div class="lalita-right-sidebar grid-30" style="padding-right: 0;">
								<div class="customize-button hide-on-mobile">
									<?php
									printf( '<a id="lalita_customize_button" class="button button-primary" href="%1$s">%2$s</a>',
										esc_url( admin_url( 'customize.php' ) ),
										esc_html__( 'Customize', 'lalita' )
									);
									?>
								</div>

								<?php
								/**
								 * lalita_admin_right_panel hook.
								 *
								 */
								 do_action( 'lalita_admin_right_panel' );

								  ?>
                                
                                <div class="wpkoi-doc">
                                	<h3><?php esc_html_e( 'Lalita documentation', 'lalita' ); ?></h3>
                                	<p><?php esc_html_e( 'If You`ve stuck, the documentation may help on WPKoi.com', 'lalita' ); ?></p>
                                    <a href="<?php echo esc_url(LALITA_DOCUMENTATION); ?>" class="wpkoi-admin-button" target="_blank"><?php esc_html_e( 'Lalita documentation', 'lalita' ); ?></a>
                                </div>
                                
                                <div class="wpkoi-social">
                                	<h3><?php esc_html_e( 'WPKoi on Facebook', 'lalita' ); ?></h3>
                                	<p><?php esc_html_e( 'If You want to get useful info about WordPress and the theme, follow WPKoi on Facebook.', 'lalita' ); ?></p>
                                    <a href="<?php echo esc_url(LALITA_WPKOI_SOCIAL_URL); ?>" class="wpkoi-admin-button" target="_blank"><?php esc_html_e( 'Go to Facebook', 'lalita' ); ?></a>
                                </div>
                                
                                <div class="wpkoi-review">
                                	<h3><?php esc_html_e( 'Help with You review', 'lalita' ); ?></h3>
                                	<p><?php esc_html_e( 'If You like Lalita theme, show it to the world with Your review. Your feedback helps a lot.', 'lalita' ); ?></p>
                                    <a href="<?php echo esc_url(LALITA_WORDPRESS_REVIEW); ?>" class="wpkoi-admin-button" target="_blank"><?php esc_html_e( 'Add my review', 'lalita' ); ?></a>
                                </div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'lalita_admin_errors' ) ) {
	add_action( 'admin_notices', 'lalita_admin_errors' );
	/**
	 * Add our admin notices
	 *
	 */
	function lalita_admin_errors() {
		$screen = get_current_screen();

		if ( 'appearance_page_lalita-options' !== $screen->base ) {
			return;
		}

		if ( isset( $_GET['settings-updated'] ) && 'true' == $_GET['settings-updated'] ) {
			 add_settings_error( 'lalita-notices', 'true', esc_html__( 'Settings saved.', 'lalita' ), 'updated' );
		}

		if ( isset( $_GET['status'] ) && 'imported' == $_GET['status'] ) {
			 add_settings_error( 'lalita-notices', 'imported', esc_html__( 'Import successful.', 'lalita' ), 'updated' );
		}

		if ( isset( $_GET['status'] ) && 'reset' == $_GET['status'] ) {
			 add_settings_error( 'lalita-notices', 'reset', esc_html__( 'Settings removed.', 'lalita' ), 'updated' );
		}

		settings_errors( 'lalita-notices' );
	}
}
