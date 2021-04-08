<?php

/*
	Plugin Name: Australian Weather Widget
	Plugin URI: http://willyweather.com.au/
	Description: The most accurate Australian weather widgets, with data provided by the Bureau of Meteorology (BoM). Willyweather weather widgets come in many different shapes and sizes, you can choose your own colours and select from multiple weather types such as Weather, Wind, Rain, Swell, Tides, UV, Sun and Moon.
	Author: WillyWeather
	Author URI: http://willyweather.com.au/
	Version: 1.5
*/

function ww_load() {

	if (is_admin()) {
	
		wp_enqueue_style('jquery');
		wp_enqueue_style('jquery-ui-sortable');
		wp_enqueue_style('jquery-ui-slider');
		wp_enqueue_style('iris');

		wp_enqueue_style('self',plugins_url('willyweather.css',__FILE__));
		wp_enqueue_script('self',plugins_url('willyweather.js',__FILE__),array(
			'jquery',
			'jquery-ui-sortable',
			'jquery-ui-slider',
			'wp-color-picker'
		),false,true);
	
	}
	
	register_widget('WW_Widget');

}

class WW_Widget extends WP_Widget {

	function __construct() {

		parent::__construct(
			'ww_widget',
			'WillyWeather',
			array(
				'description' => __('Highly detailed weather widgets with thousands of Australian locations','text_domain')
			)
		);

	}

	public function widget($args,$instance) {

		echo $args['before_widget'];
		echo $instance['html']; // echos the outframe
		echo $args['after_widget'];

	}

	public function form($instance) {

		if (isset($instance['id']))
			$id = $instance['id'];
		else
			$id = __('','text_domain');

		echo '<input data-ww-name="ww-widget-id" type="hidden" id="'.$this->get_field_id('id').'" name="'.$this->get_field_name('id').'" value="'.esc_attr($id).'">';

		if (isset($instance['code']))
			$code = $instance['code'];
		else
			$code = __('','text_domain');

		echo '<input data-ww-name="ww-widget-code" type="hidden" id="'.$this->get_field_id('code').'" name="'.$this->get_field_name('code').'" value="'.esc_attr($code).'">';

		if (isset($instance['html']))
			$html = $instance['html'];
		else
			$html = __('','text_domain');

		echo '<input data-ww-name="ww-widget-html" type="hidden" id="'.$this->get_field_id('html').'" name="'.$this->get_field_name('html').'" value="'.esc_attr($html).'">';

		if (isset($instance['tabOrder']))
			$tabOrder = $instance['tabOrder'];
		else
			$tabOrder = __('1,2,4,7,3,8,5,6','text_domain');

		echo '<input data-ww-name="ww-widget-tabOrder" type="hidden" id="'.$this->get_field_id('tabOrder').'" name="'.$this->get_field_name('tabOrder').'" value="'.esc_attr($tabOrder).'">';

	?>

		<script>
		
			jQuery(document).ready(function(){

				if (0<?php echo esc_attr($id); ?>) {
				
					var form = jQuery('[name="widget[id]"][value="<?php echo esc_attr($id); ?>"]').closest('form');
				
					form.find('.widget-content').prepend('<i class="ww-activity-indicator"></i>');
				
					jQuery.ajax({
						type: 'POST',
						url: '//www.willyweather.com.au/widget/plugin/get.html?id=<?php echo esc_attr($id); ?>&code=<?php echo esc_attr($code); ?>',
						dataType: 'jsonp',
						timeout: 20000,
						success: function(response) {
							jQuery.fn.getWillyWeatherWidget(form,response.widget,'<?php echo esc_attr($tabOrder); ?>');
							form.find('.widget-content .ww-activity-indicator').remove();
						}
					});
					
				}
				
				jQuery('[name="widget[id]"]').each(function(){
					if(jQuery(this).closest('#widgets-right').length && !jQuery(this).closest('form').find('.ui-slider').length && !jQuery(this).val())
						jQuery.fn.buildWidthTool(jQuery(this).closest('form'),120,300,300);
				});
				
			});
		
		</script>

		<input disabled type="hidden" name="widget[id]" value="<?php echo esc_attr($id); ?>">
		<input disabled type="hidden" name="widget[code]" value="<?php echo esc_attr($code); ?>">
		
		<input type="hidden" name="widget[width]" value="300"><!--default-->
		<input type="hidden" name="widget[fontFamily]" value="sans-serif"><!--default-->
		<input type="hidden" name="widget[widgetHeaderType]" value="1"><!--default-->
		<input type="hidden" name="widget[height]" value="228"><!--default-->
		<input type="hidden" name="widget[locations][0][id]" value="4988"><!--default-->
        <input type="hidden" name="widget[locations][0][typeId]" value="12"><!--default -->
        <input type="hidden" name="widget[locations][0][name]" value="Bondi Beach"><!--default -->
        <input type="hidden" name="widget[locations][0][displayName]" value="Bondi Beach, NSW 2026"><!--default -->

		<p class="ww-widget-style">
			<label>Style:</label>
			<select class="widefat" name="widget[widgetType]">
				<option value="1" data-width="260" data-min="220" data-max="300" data-height="63">Thin Bar <em>220 - 300px &times; 26px</em></option><br>
				<option value="4" data-width="199" data-min="188" data-max="210" data-height="62">High Bar <em>188 - 210px &times; 62px</em></option><br>
				<option selected value="6" data-width="300" data-min="120" data-max="300" data-height="228">Sidebar <em>120 - 300px &times; 228px</em></option><br><!--default-->
				<option value="8" data-width="800" data-min="450" data-max="850" data-height="92">Leaderboard <em>450 - 850px &times; 92px</em></option><br>
				<option value="9" data-width="500" data-min="400" data-max="1000" data-height="520">Full Page <em>400 - 1000px &times; 520px</em></option><br>
			</select>
		</p>

		<div class="ww-widget-location">
			<p>
				<label>Location:</label>
				<input class="widefat" type="search" name="ww-widget-location" placeholder="Bondi Beach, NSW 2026" autocomplete="off"><!--default-->
				<label><input type="checkbox" class="checkbox" name="ww-allow-search"> Allow users to search for locations</label>
			</p>
		</div>

		<p class="ww-widget-weather-types">
			<label>Weather Types:</label>
		</p>
		<ul class="ww-widget-weather-types">
			<li data-id="1"><label><input checked type="checkbox" class="checkbox" name="widget[weatherTypes][]" value="1"> Weather</label></li><!--default-->
			<li data-id="2"><label><input type="checkbox" class="checkbox" name="widget[weatherTypes][]" value="2"> Wind</label></li>
			<li data-id="4"><label><input type="checkbox" class="checkbox" name="widget[weatherTypes][]" value="4"> Rainfall</label></li>
			<li data-id="7"><label><input type="checkbox" class="checkbox" name="widget[weatherTypes][]" value="7"> Sunrise / Sunset</label></li>
			<li data-id="3"><label><input type="checkbox" class="checkbox" name="widget[weatherTypes][]" value="3"> Moon Phases</label></li>
			<li data-id="8"><label><input type="checkbox" class="checkbox" name="widget[weatherTypes][]" value="8"> UV</label></li>
			<li data-id="5"><label><input type="checkbox" class="checkbox" name="widget[weatherTypes][]" value="5"> Swell</label></li>
			<li data-id="6"><label><input type="checkbox" class="checkbox" name="widget[weatherTypes][]" value="6"> Tides</label></li>
		</ul>

		<p class="ww-widget-colour">
			<label>Colour:</label>
			<input class="widefat" type="text" name="widget[colour]" value="#eeeeee"><!--default-->
		</p>

		<p class="ww-widget-width">
			<label>Width: <strong>300px</strong></label>
			<div class="ww-widget-width-slider"></div>
		</p>

		<a class="ww-update-widget button button-primary right">Save</a>

	<?php

	}

	public function update($new_instance,$old_instance) {

		$instance = array();
		$instance['id'] = (!empty($new_instance['id'])) ? strip_tags($new_instance['id']) : '';
		$instance['code'] = (!empty($new_instance['code'])) ? strip_tags($new_instance['code']) : '';
		$instance['html'] = (!empty($new_instance['html'])) ? $new_instance['html'] : '';
		$instance['tabOrder'] = (!empty($new_instance['tabOrder'])) ? $new_instance['tabOrder'] : '';

		return $instance;

	}

}

// load widget

add_action('widgets_init','ww_load');
