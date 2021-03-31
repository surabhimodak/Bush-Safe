<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_upsells' ) ) {

	if ( ! class_exists( 'FusionTB_Woo_Upsells' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.2
		 */
		class FusionTB_Woo_Upsells extends Fusion_Woo_Products_Component {

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.2
			 */
			public function __construct() {
				$shortcode                 = 'fusion_tb_woo_upsells';
				$this->shortcode_classname = 'fusion-woo-upsells-tb';
				parent::__construct( $shortcode );
			}


			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.2
			 * @return array
			 */
			public function attr() {
				$attr = parent::attr();

				$attr['class'] .= ' up-sells upsells products';

				return $attr;
			}

			/**
			 * Get 'no related products' placeholder.
			 *
			 * @since 3.2
			 * @return string
			 */
			protected function get_placeholder() {
				return '<div class="fusion-builder-placeholder">' . esc_html__( 'There are no Upsells for this product.', 'fusion-builder' ) . '</div>';
			}

			/**
			 * Define heading text.
			 *
			 * @access public
			 * @since 3.2
			 * @return string
			 */
			public function get_main_heading() {
				return apply_filters( 'woocommerce_product_upsells_products_heading', __( 'You may also like&hellip;', 'fusion-builder' ) );
			}

			/**
			 * Get product query.
			 *
			 * @access public
			 * @since 3.2
			 * @return array
			 */
			public function get_query() {
				global $product;

				$args = [
					'posts_per_page' => $this->args['number_products'],
					'columns'        => $this->args['products_columns'],
					'orderby'        => 'rand', // @codingStandardsIgnoreLine.
				];
				$args = apply_filters( 'woocommerce_upsell_display_args', $args );

				$defaults = [
					'posts_per_page' => '-1',
					'columns'        => 4,
					'orderby'        => 'rand', // @codingStandardsIgnoreLine.
					'order'          => 'desc',
				];

				$args = wp_parse_args( $args, $defaults );

				// Get visible related products then sort them at random.
				$args['products'] = wc_products_array_orderby( array_filter( array_map( 'wc_get_product', $product->get_upsell_ids() ), 'wc_products_array_filter_visible' ), $args['orderby'], $args['order'] );
				$args['products'] = $args['posts_per_page'] > 0 ? array_slice( $args['products'], 0, $args['posts_per_page'] ) : $args['products'];

				return $args;
			}

			/**
			 * Set wc loop props.
			 *
			 * @access public
			 * @since 3.2
			 * @param array $args The arguments.
			 * @return void
			 */
			public function set_loop_props( $args ) {
				wc_set_loop_prop( 'name', 'up-sells' );
				wc_set_loop_prop( 'columns', apply_filters( 'woocommerce_upsells_columns', $args['columns'] ) );
			}
		}
	}

	new FusionTB_Woo_Upsells();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.2
 */
function fusion_component_woo_upsells() {

	global $fusion_settings;

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Woo_Upsells',
			[
				'name'      => esc_attr__( 'Woo Upsells', 'fusion-builder' ),
				'shortcode' => 'fusion_tb_woo_upsells',
				'icon'      => 'fusiona-woo-upsell-products',
				'component' => true,
				'templates' => [ 'content' ],
				'params'    => fusion_get_woo_product_params(
					[
						'ajax_action'                => 'get_fusion_tb_woo_upsells',
						'animation_preview_selector' => '.fusion-woo-upsells-tb',
					]
				),
				'callback'  => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_woo_upsells',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_upsells' );
