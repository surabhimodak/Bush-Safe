<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.1
 */

if ( fusion_is_element_enabled( 'fusion_form_upload' ) ) {

	if ( ! class_exists( 'FusionForm_Upload' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.1
		 */
		class FusionForm_Upload extends Fusion_Form_Component {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.1
			 * @var array
			 */
			protected $args;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 3.1
			 * @var int
			 */
			public $counter = 0;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.1
			 */
			public function __construct() {
				parent::__construct( 'fusion_form_upload' );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.1
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = fusion_get_fusion_settings();
				return [
					'label'            => '',
					'name'             => '',
					'required'         => '',
					'placeholder'      => '',
					'input_field_icon' => '',
					'upload_size'      => '',
					'extensions'       => '',
					'class'            => '',
					'id'               => '',
					'tooltip'          => '',
				];
			}

			/**
			 * Render form field html.
			 *
			 * @access public
			 * @since 3.1
			 * @param string $content The content.
			 * @return string
			 */
			public function render_input_field( $content ) {

				$element_data = $this->create_element_data( $this->args );
				$html         = '';

				if ( '' !== $this->args['tooltip'] ) {
					$element_data['label'] .= $this->get_field_tooltip( $this->args );
				}

				$element_data['accept'] = ( isset( $this->args['extensions'] ) && '' !== $this->args['extensions'] ) ? 'accept="' . $this->args['extensions'] . '"' : '';

				$element_html  = '<div class="fusion-form-upload-field-container">';
				$element_html .= '<input type="file" id="' . $this->args['name'] . '" name="' . $this->args['name'] . '" value="' . $content . '" ' . $element_data['class'] . $element_data['accept'] . $element_data['required'] . $element_data['placeholder'] . $element_data['style'] . $element_data['upload_size'] . '/>';
				$element_html .= '<input type="text" disabled value="' . $content . '" class="fusion-form-upload-field" ' . $element_data['required'] . $element_data['placeholder'] . $element_data['style'] . $element_data['holds_private_data'] . '/>';
				$element_html .= do_shortcode( '[fusion_button class="fusion-form-upload-field-button" size="medium" shape="square" link="javascript:void();" target="_self" hide_on_mobile="small-visibility,medium-visibility,large-visibility" color="default"  stretch="default"]' . __( 'Choose File', 'fusion-builder' ) . '[/fusion_button]' );
				$element_html .= '</div>';

				if ( isset( $this->args['input_field_icon'] ) && '' !== $this->args['input_field_icon'] ) {
					$icon_html     = '<div class="fusion-form-input-with-icon">';
					$icon_html    .= '<i class=" ' . $this->args['input_field_icon'] . '"></i>';
					$element_html  = $icon_html . $element_html;
					$element_html .= '</div>';
				}

				if ( 'above' === $this->params['form_meta']['label_position'] ) {
					$html .= $element_data['label'] . $element_html;
				} else {
					$html .= $element_html . $element_data['label'];
				}

				return $html;
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.1
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/form/upload.min.css' );
			}
		}
	}

	new FusionForm_Upload();
}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 3.1
 */
function fusion_form_upload() {

	global $fusion_settings;

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionForm_Upload',
			[
				'name'           => esc_attr__( 'Upload Field', 'fusion-builder' ),
				'shortcode'      => 'fusion_form_upload',
				'icon'           => 'fusiona-af-upload',
				'form_component' => true,
				'preview'        => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-form-element-preview.php',
				'preview_id'     => 'fusion-builder-block-module-form-element-preview-template',
				'params'         => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Field Label', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter the label for the input field. This is how users will identify individual fields.', 'fusion-builder' ),
						'param_name'  => 'label',
						'value'       => '',
						'placeholder' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Field Name', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter the field name. Should be single word without spaces. Underscores and dashes are allowed.', 'fusion-builder' ),
						'param_name'  => 'name',
						'value'       => '',
						'placeholder' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Required Field', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection to ensure that this field is completed before allowing the user to submit the form.', 'fusion-builder' ),
						'param_name'  => 'required',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Placeholder Text', 'fusion-builder' ),
						'param_name'  => 'placeholder',
						'value'       => esc_attr__( 'Click or drag a file to this area to upload.', 'fusion-builder' ),
						'description' => esc_attr__( 'The placeholder text to display as hint for the input type.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Tooltip Text', 'fusion-builder' ),
						'param_name'  => 'tooltip',
						'value'       => '',
						'description' => esc_attr__( 'The text to display as tooltip hint for the input.', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_html__( 'Max File Upload Size', 'fusion-builder' ),
						'description' => esc_html__( 'Maximum size limit for file upload. The default is 2 MB.', 'fusion-builder' ),
						'param_name'  => 'upload_size',
						'value'       => '2',
						'min'         => '1',
						'max'         => '100',
						'step'        => '1',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Allowed Extensions', 'fusion-builder' ),
						'param_name'  => 'extensions',
						'value'       => '',
						'description' => esc_html__( 'Please enter the comma separated extensions that you want to allow. Leave empty to allow all. Example input: .jpg,.png.  Note, WordPress file type permissions still apply.', 'fusion-builder' ),
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Input Field Icon', 'fusion-builder' ),
						'param_name'  => 'input_field_icon',
						'value'       => 'fa-upload fas',
						'description' => esc_attr__( 'Select an icon for the input field, click again to deselect.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'description' => esc_attr__( 'Add a class for the input field.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'description' => esc_attr__( 'Add an ID for the input field.', 'fusion-builder' ),
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_form_upload' );
