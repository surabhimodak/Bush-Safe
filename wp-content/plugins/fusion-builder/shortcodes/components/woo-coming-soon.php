<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.2
 */

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.2
 */
function fusion_component_woo_coming_soon() {

	$tooltip = esc_attr__( 'More WooCommerce elements are coming soon.', 'fusion-builder' );
	fusion_builder_map(
		[
			'name'                    => esc_attr__( 'Woo Account', 'fusion-builder' ),
			'template_tooltip'        => $tooltip,
			'shortcode'               => 'fusion_tb_woo_account',
			'icon'                    => 'fusiona-woo-my-account',
			'class'                   => 'hidden',
			'component'               => true,
			'templates'               => [ 'content' ],
			'components_per_template' => 0,
			'params'                  => [],
			'defaults'                => [],
			'extras'                  => [],
		]
	);
	fusion_builder_map(
		[
			'name'                    => esc_attr__( 'Woo Checkout', 'fusion-builder' ),
			'shortcode'               => 'fusion_tb_woo_checkout',
			'template_tooltip'        => $tooltip,
			'icon'                    => 'fusiona-woo-checkout',
			'class'                   => 'hidden',
			'component'               => true,
			'templates'               => [ 'content' ],
			'components_per_template' => 0,
			'params'                  => [],
			'defaults'                => [],
			'extras'                  => [],
		]
	);
	fusion_builder_map(
		[
			'name'                    => esc_attr__( 'Woo Archives', 'fusion-builder' ),
			'shortcode'               => 'fusion_tb_woo_archives',
			'template_tooltip'        => $tooltip,
			'icon'                    => 'fusiona-woo-archive',
			'class'                   => 'hidden',
			'component'               => true,
			'templates'               => [ 'content' ],
			'components_per_template' => 0,
			'params'                  => [],
			'defaults'                => [],
			'extras'                  => [],
		]
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_coming_soon' );
