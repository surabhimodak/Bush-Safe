<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 2.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_content' ) ) {

	if ( ! class_exists( 'FusionTB_Content' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 2.2
		 */
		class FusionTB_Content extends Fusion_Component {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 2.2
			 * @var array
			 */
			protected $args;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 2.2
			 * @var int
			 */
			private $counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 2.2
			 */
			public function __construct() {
				parent::__construct( 'fusion_tb_content' );
				add_filter( 'fusion_attr_fusion_tb_content-shortcode', [ $this, 'attr' ] );
			}


			/**
			 * Check if component should render
			 *
			 * @access public
			 * @since 2.2
			 * @return boolean
			 */
			public function should_render() {
				return is_singular();
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = fusion_get_fusion_settings();
				return [
					'margin_bottom'       => '',
					'margin_left'         => '',
					'margin_right'        => '',
					'margin_top'          => '',
					'hide_on_mobile'      => fusion_builder_default_visibility( 'string' ),
					'class'               => '',
					'id'                  => '',
					'animation_type'      => '',
					'animation_direction' => 'down',
					'animation_speed'     => '0.1',
					'animation_offset'    => $fusion_settings->get( 'animation_offset' ),
				];
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 2.2
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				global $global_column_array, $global_column_inner_array, $global_container_count;

				$this->args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_tb_content' );

				$target_post = $this->get_target_post();

				// No recursion.
				if ( $target_post && get_the_ID() === $target_post->ID ) {
					$dummy_post = Fusion_Dummy_Post::get_dummy_post();
					return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $dummy_post->post_content, $args );
				}

				// Don't render on archive pages.
				if ( ! $target_post && ! $this->should_render() ) {
					return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', '', $args );
				}

				// Save backups of the globals and then reset.
				$template_global_column_array       = $global_column_array;
				$template_global_column_inner_array = $global_column_inner_array;

				$global_column_array       = [];
				$global_column_inner_array = [];

				// Backup global container count, will rerun scoped for nested.
				$template_global_container_count = $global_container_count;
				$global_container_count          = false;

				if ( ! $target_post ) {
					do_action( 'fusion_resume_live_editor_filter' );
				} else {
					do_action( 'fusion_pause_live_editor_filter' );
				}

				do_action( 'fusion_content_pre_render' );

				$content = $target_post ? $target_post->post_content : get_the_content();
				$content = apply_filters( 'the_content', $content );

				do_action( 'fusion_content_post_render' );

				if ( ! $target_post ) {
					do_action( 'fusion_pause_live_editor_filter' );
				} else {
					do_action( 'fusion_resume_live_editor_filter' );
				}

				$content = str_replace( ']]>', ']]&gt;', $content );

				$global_column_array       = $template_global_column_array;
				$global_column_inner_array = $template_global_column_inner_array;
				$global_container_count    = $template_global_container_count;

				$content = '<div ' . FusionBuilder::attributes( 'fusion_tb_content-shortcode' ) . '>' . $content . '</div>';

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $content, $args );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'fusion-content-tb fusion-content-tb-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;
			}
		}
	}

	new FusionTB_Content();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 2.2
 */
function fusion_component_content() {

	global $fusion_settings;

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Content',
			[
				'name'                    => esc_attr__( 'Content', 'fusion-builder' ),
				'shortcode'               => 'fusion_tb_content',
				'icon'                    => 'fusiona-content',
				'component'               => true,
				'templates'               => [ 'content' ],
				'components_per_template' => 1,
				'params'                  => [
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'margin',
						'value'            => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-content-tb',
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_content' );
