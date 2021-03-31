<?php
/**
 * Form Builder Component Class.
 *
 * @package fusion-builder
 * @since 3.1
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}


/**
 * Form Builder Component Class.
 *
 * @since 3.1
 */
abstract class Fusion_Form_Component extends Fusion_Element {

	/**
	 * Global form options.
	 *
	 * @access public
	 * @since 3.1
	 * @var array
	 */
	public $params = [];

	/**
	 * Shortcode handle.
	 *
	 * @access public
	 * @since 3.1
	 * @var string
	 */
	public $shortcode_handle = '';

	/**
	 * Constructor.
	 *
	 * @since 3.1
	 * @param string $shortcode_handle Shortcode Handle.
	 */
	public function __construct( $shortcode_handle ) {
		parent::__construct();
		$this->shortcode_handle = $shortcode_handle;
		add_shortcode( $this->shortcode_handle, [ $this, 'render' ] );
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
		$this->counter++;

		$defaults = FusionBuilder::set_shortcode_defaults( $this->get_element_defaults(), $args, $this->shortcode_handle );
		$content  = apply_filters( 'fusion_shortcode_content', $content, $this->shortcode_handle, $args );

		$this->args = $defaults;

		$html = $this->get_form_field( $content );

		$this->on_render();

		return apply_filters( 'fusion_form_component_' . $this->shortcode_handle . '_content', $html, $args );
	}

	/**
	 * Creates all meta data for the form elements.
	 *
	 * @since 3.1
	 * @access public
	 * @param array $args All needed values for the form field.
	 * @return string The meta data of a form element.
	 */
	public function create_element_data( $args ) {
		$data = [
			'checked'              => '',
			'required'             => '',
			'required_label'       => '',
			'required_placeholder' => '',
			'placeholder'          => '',
			'label'                => '',
			'label_class'          => '',
			'style'                => '',
			'holds_private_data'   => 'no',
			'upload_size'          => '',
			'pattern'              => '',
		];

		$is_hidden = isset( $args['hidden'] ) && $args['hidden'];

		if ( ! $args ) {
			return $data;
		}

		// TODO: move input specific code to own file.
		if ( 'fusion_form_checkbox' === $this->shortcode_handle && isset( $args['checked'] ) && $args['checked'] ) {
			$data['checked'] = ' checked="checked"';
		}

		if ( 'fusion_form_phone_number' === $this->shortcode_handle ) {
			$data['pattern'] = ' pattern="[0-9()#&+*-=.]+" title="' . esc_attr( __( 'Only numbers and phone characters are accepted.', 'fusion-builder' ) ) . '"';
		}

		if ( 'fusion_form_upload' === $this->shortcode_handle && isset( $args['upload_size'] ) && $args['upload_size'] ) {
			$data['upload_size'] = ' data-size="' . esc_attr( $args['upload_size'] ) . '"';
		}

		if ( isset( $args['required'] ) && 'yes' === $args['required'] ) {
			$data['required']             = ' required="true" aria-required="true"';
			$data['required_label']       = ' <abbr class="fusion-form-element-required" title="' . esc_attr( __( 'required', 'fusion-builder' ) ) . '">*</abbr>';
			$data['required_placeholder'] = '*';
		}

		$data['class'] = ' class="fusion-form-input"';

		if ( isset( $args['placeholder'] ) && '' !== $args['placeholder'] ) {
			if ( 'fusion_form_select' === $this->shortcode_handle ) {
				$data['placeholder'] = [ $args['placeholder'] . $data['required_placeholder'] ];
			} else {
				$data['placeholder'] = ' placeholder="' . esc_attr( $args['placeholder'] . $data['required_placeholder'] ) . '"';
			}
		}

		if ( isset( $args['label'] ) && '' !== $args['label'] && ! $is_hidden ) {
			$non_label_elements = [ 'fusion_form_radio', 'fusion_form_checkbox', 'fusion_form_image_select', 'fusion_form_rating' ];

			if ( 'fusion_form_checkbox' === $this->shortcode_handle ) {
				$data['label_class'] = ' class="fusion-form-checkbox-label"';
			}

			$tag_open  = '<label for="' . esc_attr( $args['name'] ) . '"' . esc_attr( $data['label_class'] ) . '>';
			$tag_close = '</label>';

			if ( in_array( $this->shortcode_handle, $non_label_elements, true ) ) {
				$tag_open  = '<span class="label">';
				$tag_close = '</span>';
			}

			$data['label'] = $tag_open . esc_html( $args['label'] ) . $data['required_label'] . $tag_close;
		}

		$data['holds_private_data'] = ' data-holds-private-data="false"';
		if ( isset( $args['holds_private_data'] ) && $args['holds_private_data'] ) {
			$data['holds_private_data'] = ' data-holds-private-data="true"';
		}

		$data['input_attributes'] = '';

		// Number field min and max.
		if ( isset( $args['min'] ) && ! empty( $args['min'] ) ) {
			$data['input_attributes'] .= ' min="' . $args['min'] . '"';
		}
		if ( isset( $args['max'] ) && ! empty( $args['max'] ) ) {
			$data['input_attributes'] .= ' max="' . $args['max'] . '"';
		}

		// Text field minlength and maxlength.
		if ( isset( $args['minlength'] ) && ! empty( $args['minlength'] ) ) {
			$data['input_attributes'] .= ' minlength="' . $args['minlength'] . '"';
		}
		if ( isset( $args['maxlength'] ) && ! empty( $args['maxlength'] ) ) {
			$data['input_attributes'] .= ' maxlength="' . $args['maxlength'] . '"';
		}

		return $data;
	}

	/**
	 * Generate and returns tooltip html for the input field.
	 *
	 * @since 3.1
	 * @access public
	 * @param array $args All needed values for the form field.
	 * @return string $html HTML for tooltip.
	 */
	public function get_field_tooltip( $args ) {
		global $fusion_form;
		$html = '';

		if ( '' !== $args['tooltip'] ) {
			$html .= '<div class="fusion-form-tooltip">';
			$html .= '<i class="fusion-icon-question-circle"></i>';
			$html .= '<span class="fusion-form-tooltip-content">' . $args['tooltip'] . '</span>';
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Renders normal text inputs.
	 *
	 * @since 3.1
	 * @access private
	 * @param array  $args All needed values for the form field.
	 * @param string $type The type of the input, e.g. text, number, email.
	 * @return string      The text input element.
	 */
	public function generate_input_field( $args, $type = 'text' ) {
		global $fusion_form;

		$html         = '';
		$element_data = $this->create_element_data( $args );

		if ( '' !== $args['tooltip'] ) {
			$element_data['label'] .= $this->get_field_tooltip( $args );
		}

		$args['value'] = isset( $args['value'] ) ? $args['value'] : '';

		$element_html = '<input type="' . $type . '" ';

		if ( 'hidden' !== $type && '' !== $this->args['tab_index'] ) {
			$element_html .= 'tabindex="' . $this->args['tab_index'] . '" ';
		}

		$element_html .= 'name="' . $args['name'] . '" value="' . $args['value'] . '" ' . $element_data['class'] . $element_data['required'] . $element_data['placeholder'] . $element_data['style'] . $element_data['holds_private_data'] . $element_data['input_attributes'] . $element_data['pattern'] . '/>';

		if ( isset( $args['input_field_icon'] ) && '' !== $args['input_field_icon'] ) {
			$icon_html     = '<div class="fusion-form-input-with-icon">';
			$icon_html    .= '<i class="' . fusion_font_awesome_name_handler( $args['input_field_icon'] ) . '"></i>';
			$element_html  = $icon_html . $element_html;
			$element_html .= '</div>';
		}

		if ( '' !== $element_data['label'] ) {
			$element_data['label'] = '<div class="fusion-form-label-wrapper">' . $element_data['label'] . '</div>';
		}

		if ( 'above' === $fusion_form['form_meta']['label_position'] ) {
			$html .= $element_data['label'] . $element_html;
		} else {
			$html .= $element_html . $element_data['label'];
		}

		return $html;
	}

	/**
	 * Renders checkbox inputs.
	 *
	 * @since 3.1
	 * @access private
	 * @param array  $args All needed values for the form field.
	 * @param string $type The type of the input, e.g. radio, checkbox.
	 * @return string The checkbox input element.
	 */
	public function checkbox( $args, $type = 'checkbox' ) {
		global $fusion_form;

		$options = '';
		$html    = '';

		if ( empty( $args['options'] ) ) {
			return $html;
		} else {
			$args['options'] = json_decode( fusion_decode_if_needed( $args['options'] ), true );
		}

		$element_data = $this->create_element_data( $args );

		foreach ( $args['options'] as $key => $option ) {
			$checked = $option[0] ? ' checked ' : '';
			$label   = trim( $option[1] );
			$value   = ! empty( $option[2] ) ? trim( $option[2] ) : $label;

			$name         = empty( $args['name'] ) ? $args['label'] : $args['name'];
			$element_name = ( 'checkbox' === $type ) ? $name . '[]' : $name;

			$checkbox_class = ( 'floated' === $args['form_field_layout'] ) ? 'fusion-form-' . $type . ' option-inline' : 'fusion-form-' . $type;
			$label_id       = $type . '-' . str_replace( ' ', '-', strtolower( $name ) ) . '-' . $this->counter . '-' . $key;
			$options       .= '<div class="' . $checkbox_class . '">';
			$options       .= '<input tabindex="' . $args['tab_index'] . '" id="' . $label_id . '" type="' . $type . '" value="' . $value . '" name="' . $element_name . '"' . $element_data['class'] . $element_data['required'] . $checked . $element_data['holds_private_data'] . '/>';
			$options       .= '<label for="' . $label_id . '">';
			$options       .= $label . '</label>';
			$options       .= '</div>';
		}

		$element_html  = '<fieldset>';
		$element_html .= $options;
		$element_html .= '</fieldset>';

		if ( '' !== $args['tooltip'] ) {
			$element_data['label'] .= $this->get_field_tooltip( $args );
		}

		if ( '' !== $element_data['label'] ) {
			$element_data['label'] = '<div class="fusion-form-label-wrapper">' . $element_data['label'] . '</div>';
		}

		if ( 'above' === $fusion_form['form_meta']['label_position'] ) {
			$html .= $element_data['label'] . $element_html;
		} else {
			$html .= $element_html . $element_data['label'];
		}

		return $html;
	}

	/**
	 * Adds field data to the form.
	 *
	 * @access public
	 * @since 3.1
	 * @return void
	 */
	public function add_field_data_to_form() {
		global $fusion_form;

		if ( ! isset( $fusion_form['form_fields'] ) ) {
			$fusion_form['form_fields'] = [];
		}

		$fusion_form['form_fields'][] = $this->shortcode_handle;

		if ( isset( $this->args['label'] ) ) {
			$fusion_form['field_labels'][ $this->args['name'] ] = $this->args['label'];
		}
	}

	/**
	 * Adds field wrapper html.
	 *
	 * @access public
	 * @since 3.1
	 * @return string
	 */
	public function add_field_wrapper_html() {
		$label_position = 'above';

		if ( $this->params['form_meta']['label_position'] ) {
			$label_position = $this->params['form_meta']['label_position'];
		}

		$html = '<div ';

		// Add custom ID if it's there.
		if ( isset( $this->args['id'] ) && '' !== $this->args['id'] ) {
			$html .= 'id="' . esc_attr( $this->args['id'] ) . '" ';
		}

		// Start building class.
		$html .= 'class="fusion-form-field ' . str_replace( '_', '-', $this->shortcode_handle ) . '-field fusion-form-label-' . $label_position;

		// Add custom class if it's there.
		if ( isset( $this->args['class'] ) && '' !== $this->args['class'] ) {
			$html .= ' ' . esc_attr( $this->args['class'] );
		}

		// Close class quotes.
		$html .= '"';

		$html .= ' data-form-id="' . $this->params['form_number'] . '">';

		return $html;
	}

	/**
	 * Get the global $fusion_form value.
	 *
	 * @access public
	 * @since 3.1
	 * @return array
	 */
	public function get_form_data() {
		global $fusion_form;

		if ( ! isset( $fusion_form['id'] ) ) {
			if ( fusion_doing_ajax() && isset( $_POST['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.PHP.StrictComparisons.LooseComparison
				$fusion_form = Fusion_Builder_Form_Helper::fusion_form_set_form_data( (int) sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.PHP.StrictComparisons.LooseComparison
			} elseif ( ! fusion_doing_ajax() && isset( $_POST['post_ID'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$fusion_form = Fusion_Builder_Form_Helper::fusion_form_set_form_data( (int) sanitize_text_field( wp_unslash( $_POST['post_ID'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.PHP.StrictComparisons.LooseComparison
			}
		}

		return $fusion_form;
	}

	/**
	 * Render form field html.
	 *
	 * @access public
	 * @since 3.1
	 * @param string $content The content.
	 * @return string
	 */
	public function get_form_field( $content ) {
		global $fusion_form;

		// Add form data to form element.
		if ( empty( $this->params ) || $this->params['id'] !== $fusion_form['id'] ) {
			$this->params = $this->get_form_data();
		}

		// Add form element data to a form.
		$this->add_field_data_to_form();

		// Get form input html.
		$html  = $this->add_field_wrapper_html();
		$html .= $this->render_input_field( $content );
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render form input field html.
	 *
	 * @access public
	 * @since 3.1
	 * @param string $content The content.
	 * @return false
	 */
	public function render_input_field( $content ) {
		return false;
	}
}
