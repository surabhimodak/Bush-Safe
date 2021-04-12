<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'RtTpgNotice' ) ):

	class RtTpgNotice {
		public function __construct() {
		    /*
			add_action( 'admin_init', function () {
				$current = time();
				if ( mktime( 0, 0, 0, 11, 26, 2020 ) <= $current && $current <= mktime( 0, 0, 0, 12, 5, 2020 ) ) {
					if ( get_option( 'rttpg_bf_2020' ) != '1' ) {
						if ( ! isset( $GLOBALS['rt_bf_2020_notice'] ) ) {
							$GLOBALS['rt_bf_2020_notice'] = 'rttpg_bf_2020';
							self::notice();
						}
					}
				}
			} );
		    */
		}

		static function notice() {

			add_action( 'admin_enqueue_scripts', function () {
				wp_enqueue_script( 'jquery' );
			} );

			add_action( 'admin_notices', function () {
				$plugin_name   = "The Post Grid Pro";
				$download_link = 'https://www.radiustheme.com/downloads/the-post-grid-pro-for-wordpress/?ref=wporg';
				?>
                <div class="notice notice-info is-dismissible" data-rttpgdismissable="rttpg_bf_2020"
                     style="display:grid;grid-template-columns: 100px auto;padding-top: 25px; padding-bottom: 22px;">
                    <img alt="<?php echo esc_attr( $plugin_name ) ?>"
                         src="<?php echo rtTPG()->assetsUrl . 'images/notice.png'; ?>" width="74px"
                         height="74px" style="grid-row: 1 / 4; align-self: center;justify-self: center"/>
                    <h3 style="margin:0;"><?php echo sprintf( '%s Black Friday Deal!!', $plugin_name ) ?></h3>
                    <p style="margin:0 0 2px;">Don't miss out on our biggest sale of the year! Get your
                        <b><?php echo $plugin_name; ?> plan</b> with <b>50% OFF</b>! Limited time offer expires on
                        December 5.
                    </p>
                    <p style="margin:0;">
                        <a class="button button-primary"
                           href="<?php echo esc_url( $download_link ) ?>"
                           target="_blank">Buy Now</a>
                        <a class="button button-dismiss" href="#">Dismiss</a>
                    </p>
                </div>
				<?php
			} );

			add_action( 'admin_footer', function () {
				?>
                <script type="text/javascript">
                    (function ($) {
                        $(function () {
                            setTimeout(function () {
                                $('div[data-rttpgdismissable] .notice-dismiss, div[data-rttpgdismissable] .button-dismiss')
                                    .on('click', function (e) {
                                        e.preventDefault();
                                        $.post(ajaxurl, {
                                            'action': 'rttpg_dismiss_admin_notice',
                                            'nonce': <?php echo json_encode( wp_create_nonce( 'rttpg-dismissible-notice' ) ); ?>
                                        });
                                        $(e.target).closest('.is-dismissible').remove();
                                    });
                            }, 1000);
                        });
                    })(jQuery);
                </script>
				<?php
			} );

			add_action( 'wp_ajax_rttpg_dismiss_admin_notice', function () {
				check_ajax_referer( 'rttpg-dismissible-notice', 'nonce' );

				update_option( 'rttpg_bf_2020', '1' );
				wp_die();
			} );
		}
	}

endif;
