jQuery.fn.checkWeatherType = function(locationType) {

	var tides = [2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,19],
		swell = [2,5,6,7,8,9,10,11,12,13,24,25];

	if (jQuery.inArray(locationType,tides) >= 0) {
		jQuery(this).find('.ww-widget-weather-types [type="checkbox"][value="6"]')
			.prop('disabled',false)
			.closest('label')
				.removeClass('disabled')
				.removeAttr('title');
	} else {
		jQuery(this).find('.ww-widget-weather-types [type="checkbox"][value="6"]')
			.prop('checked',false)
			.prop('disabled',true)
			.closest('label')
				.addClass('disabled')
				.attr('title','This location does not have Tides');
		if (!jQuery(this).find('.ww-widget-weather-types [type="checkbox"]:checked').length)
			jQuery(this).find('.ww-widget-weather-types [type="checkbox"][value="1"]').prop('checked',true);
	}
	
	if (jQuery.inArray(locationType,swell) >= 0) {
		jQuery(this).find('.ww-widget-weather-types [type="checkbox"][value="5"]')
			.prop('disabled',false)
			.closest('label')
				.removeClass('disabled')
				.removeAttr('title');
	} else {
		jQuery(this).find('.ww-widget-weather-types [type="checkbox"][value="5"]')
			.prop('checked',false)
			.prop('disabled',true)
			.closest('label')
				.addClass('disabled')
				.attr('title','This location does not have Swell');
		if (!jQuery(this).find('.ww-widget-weather-types [type="checkbox"]:checked').length)
			jQuery(this).find('.ww-widget-weather-types [type="checkbox"][value="1"]').prop('checked',true);
	}

}

jQuery.fn.checkTabCount = function(form,callback) {

	var tabs = form.find('.ww-widget-weather-types [type="checkbox"]:checked').length,
		tabWidth = 45,
		frame = parseFloat(form.find('[name="widget[width]"]').val());

	if (tabs * tabWidth > frame) {
		alert('You\'ve selected too many Weather Types. A '+frame+'px wide widget can only display up to '+Math.floor((frame-10)/tabWidth)+' Weather Types.');
		if (typeof callback == 'function')
	        callback.call(this);
	}

}

jQuery.fn.getWillyWeatherWidget = function(form,widget,tabOrder) {

// set widget type

	form.find('[name="widget[widgetType]"] option[value="'+widget.widgetType.id+'"]').prop('selected',true);

// reset weather types if type is not sidebar or full page	

	if (widget.widgetType.id != 6 && widget.widgetType.id != 9) {
		form.find('.ww-widget-weather-types [type="checkbox"][value="1"]').prop('checked',true);
		form.find('.ww-widget-weather-types [type="checkbox"]').not('[value="1"]').prop('checked',false);
	}

// only show weather types if sidebar or full page is selected

	if (widget.widgetType.id == 6 || widget.widgetType.id == 9)
		form.find('.ww-widget-weather-types').show();
	else
		form.find('.ww-widget-weather-types').hide();

// reset header type if thin or highbar

	if (widget.widgetType.id == 1 || widget.widgetType.id == 4) {
		form.find('[name="widget[widgetHeaderType]"]').val(1);
		form.find('[name="ww-allow-search"]').not('[value="1"]').prop('checked',false);
		form.find('[name="ww-allow-search"]').closest('label').hide();
	} else {
		form.find('[name="ww-allow-search"]').closest('label').show();
	}

// set header type

	if (widget.widgetHeaderType.id == 3) {
		form.find('[name="ww-allow-search"]').prop('checked',true);
		form.find('[name="widget[widgetHeaderType]"]').val(widget.widgetHeaderType.id);
	}

// set weather types

	form.find('.ww-widget-weather-types [type="checkbox"]').prop('checked',false);

	for (var i = 0; i < widget.weatherTypes.length; i++)
		form.find('.ww-widget-weather-types [type="checkbox"][value="'+widget.weatherTypes[i]+'"]').prop('checked',true);

	var tabs = tabOrder.split(','),
		weatherTypeList = form.find('ul.ww-widget-weather-types');

	for (var i = 0; i < tabs.length; i++)
		form.find('ul.ww-widget-weather-types [data-id="'+tabs[i]+'"]').appendTo(weatherTypeList).next().remove();

// set location
    //form.find('[name="ww-widget-location"]').attr('placeholder',widget.locations[0].displayName);
    form.find('[name="ww-widget-location"]').val(widget.locations[0].displayName);
	form.find('[name="widget[locations][0][id]"]').val(widget.locations[0].id);
    form.find('[name="widget[locations][0][name]"]').val(widget.locations[0].name);
    form.find('[name="widget[locations][0][displayName]"]').val(widget.locations[0].displayName);
    form.find('[name="widget[locations][0][typeId]"]').val(widget.locations[0].typeId);

    form.checkWeatherType(widget.locations[0].typeId);
	
// set remaining data
	
	form.find('[name="widget[width]"]').val(widget.width).attr('placeholder',widget.width);
	form.find('[name="widget[height]"]').val(widget.height);
	form.find('[name="widget[colour]"]').val(widget.colour);

	var width = parseFloat(form.find('[name="widget[width]"]').val()),
		min = parseFloat(form.find('[name="widget[widgetType]"] :selected').attr('data-min')),
		max = parseFloat(form.find('[name="widget[widgetType]"] :selected').attr('data-max'));

	jQuery.fn.buildWidthTool(form,min,max,width);

}

jQuery.fn.buildWidthTool = function(form,min,max,width) {

	if (form.find('.ww-widget-width-slider').hasClass('ui-slider'))
		form.find('.ww-widget-width-slider').slider('destroy');

	form.find('.ww-widget-width-slider').slider({
		range: 'min',
		min: min,
		max: max,
		value: width,
		step: 1,
		create: function() {
			form.find('.ww-widget-width label strong').text(width + 'px');
			form.find('[name="widget[width]"]').val(width);
		},
		slide: function(event,ui) {
			form.find('[name="widget[width]"]').val(ui.value);
			form.find('.ww-widget-width label strong').text(ui.value + 'px');
		},
		change: function(event,ui) {
			form.find('[name="widget[width]"]').val(ui.value);
			form.find('.ww-widget-width label strong').text(ui.value + 'px');
	
			jQuery.fn.checkTabCount(form,function(){
				form.find('[name="widget[width]"]').val(300);
				form.find('.ww-widget-width-slider').slider('value',300);
			});
			
		}
	});

}

jQuery(document).ready(function($){

	// hide wp update button so we can take control of it
	
	$('[data-ww-name="ww-widget-id"]').each(function(){
	
		var form = $(this).closest('div.widget .widget-inside form'),
			width = form.find('[name="widget[widgetType]"] :selected').attr('data-width'),
			min = form.find('[name="widget[widgetType]"] :selected').attr('data-min'),
			max = form.find('[name="widget[widgetType]"] :selected').attr('data-max');

		form.find('[name="savewidget"]').hide();
		//$.fn.buildWidthTool(form,min,max,width);

	});

	// event listeners

	$(document)
	
		.on('click','.ww-update-widget',function(){
	
			var form = $(this).closest('div.widget .widget-inside form');
	
	// validate color
	
			var colour = form.find('[name="widget[colour]"]').val();
	
			if (!/(^#[0-9A-F]{6}$)|(^#[0-9A-F]{3}$)/i.test(colour))
				form.find('[name="widget[colour]"]').val('#eeeeee')
	
	// validate weather types
	
			if (!form.find('.ww-widget-weather-types [type="checkbox"]:checked').length)
				form.find('.ww-widget-weather-types [type="checkbox"][value="1"]').prop('checked',true);
	
	// disable all non ww inputs
	
			form.find('input').prop('disabled',true);
			form.find('[name^="widget["]').prop('disabled',false);

	// prepare data and select api service

			if (form.find('[data-ww-name="ww-widget-id"]').val().length) {
				var url = '//www.willyweather.com.au/widget/plugin/update.html';
				form.find('[name="widget[id]"]').prop('disabled',false);
				form.find('[name="widget[code]"]').prop('disabled',false);
			} else {
				var url = '//www.willyweather.com.au/widget/plugin/create.html';
				form.find('[name="widget[id]"]').prop('disabled',true);
				form.find('[name="widget[code]"]').prop('disabled',true);
			}
	
			var data = form.serializeArray();

	// send ww form data

			$.ajax({
				type: 'POST',
				url: url,
				data: data,
				dataType: 'jsonp',
				success: function(response) {
				
					form.find('input').prop('disabled',false);
				
					if (response.success) {

						form.find('[data-ww-name="ww-widget-id"]').val(response.id);
						form.find('[data-ww-name="ww-widget-code"]').val(response.code);
						form.find('[data-ww-name="ww-widget-html"]').val(response.html);
	
						wpWidgets.save(form.closest('div.widget'),0,1,0); // send to wp database
	
					} else {

						alert('error #'+response.error.code+': '+response.error.description);

					}
			
				}
			});
	
		})
		
		.on('change','.ww-widget-style [name="widget[widgetType]"]',function(){
		
			var form = $(this).closest('div.widget .widget-inside form'),
				value = parseFloat($(this).val()),
				width = parseFloat($(this).find(':selected').attr('data-width')),
				height = parseFloat($(this).find(':selected').attr('data-height')),
				min = parseFloat($(this).find(':selected').attr('data-min')),
				max = parseFloat($(this).find(':selected').attr('data-max'));
		
	// set the width and height defaults as user cycles through widget styles
		
			form.find('[name="widget[height]"]').val(height);
			form.find('[name="widget[width]"]').val(width);
			
			form.find('.ww-widget-width-slider').slider('destroy');
			
			$.fn.buildWidthTool(form,min,max,width);

	// reset weather types if type is not sidebar or full page	
			
			if (value != 6 && value != 9) {
				form.find('.ww-widget-weather-types [type="checkbox"][value="1"]').prop('checked',true);
				form.find('.ww-widget-weather-types [type="checkbox"]').not('[value="1"]').prop('checked',false);
			}

	// only show weather types if sidebar or full page is selected
		
			if (value == 6 || value == 9)
				form.find('.ww-widget-weather-types').show();
			else
				form.find('.ww-widget-weather-types').hide();

	// reset header type if thin or highbar
			if (value == 1 || value == 4) {
				form.find('[name="widget[widgetHeaderType]"]').val(1);
				form.find('[name="ww-allow-search"]').not('[value="1"]').prop('checked',false);
				form.find('[name="ww-allow-search"]').closest('label').hide();
			} else {
				form.find('[name="ww-allow-search"]').closest('label').show();
			}

			$.fn.checkTabCount(form,function(){
				form.find('[name="widget[widgetType]"] option[value="9"]').prop('selected',true);
				form.find('[name="widget[height]"]').val(520);
				form.find('[name="widget[width]"]').val(500);
				form.find('.ww-widget-width-slider').slider('destroy');
				$.fn.buildWidthTool(form,400,1000,500);
			});
	
		})
	
	// location autocomplete
		.on('blur','[name="ww-widget-location"]',function(){
		
			var form = $(this).closest('div.widget .widget-inside form');
			form.find('.ww-widget-location-results').remove();

		})

		.on('focus keyup','[name="ww-widget-location"]',function(){

			var form = $(this).closest('div.widget .widget-inside form'),
				data = $(this).val();

			if (data.length >= 3) {

				$.ajax({
					type: 'GET',
					url: '//www.willyweather.com.au/search/autocomplete.html?query='+data,
					dataType: 'jsonp',
					success: function(response) {

						form.find('.ww-widget-location-results').remove();
						
						if (response.length >= 1)
							form.find('.ww-widget-location').append('<ul class="ww-widget-location-results"/>');
					
						for (var i = 0; i < response.length; i++) {
							if (i < 25) // limit to 25
								form.find('.ww-widget-location-results').append('<li><a data-id="'+response[i].id+'" data-location-type="'+response[i].typeId+'">'+response[i].name+'</a></li>');
						}
				
					}
				});
				
			}

		})

	// location selection
	
		.on('mousedown','.ww-widget-location-results a',function(){
		
			var form = $(this).closest('div.widget .widget-inside form'),
				id = $(this).attr('data-id'),
				locationType = parseFloat($(this).attr('data-location-type')),
				name = $(this).text();

			form.find('[name="widget[locations][0][id]"]').val(id);
            form.find('[name="widget[locations][0][typeId]"]').val(locationType);
            form.find('[name="widget[locations][0][name]"]').val(name);
            form.find('[name="widget[locations][0][displayName]"]').val(name);
            form.find('[name="ww-widget-location"]').attr('placeholder', name);
			form.find('[name="ww-widget-location"]').val(name);
			form.find('.ww-widget-location-results').remove();

			form.checkWeatherType(locationType);

		})

    // on location box click clear existing data

        .on('mousedown', '.ww-widget-location', function() {
            var form = $(this).closest('div.widget .widget-inside form');

            form.find('[name="ww-widget-location"]').val('');
        })
		
	// weather type selection
		
		.on('mouseenter','ul.ww-widget-weather-types',function(){
		
			var form = $(this).closest('div.widget .widget-inside form');

			if (!$(this).hasClass('ui-sortable')) {
			
				$(this).sortable({
					axis: 'y',
					update: function(event,ui) {
						var order = [];
						form.find('.ww-widget-weather-types [type="checkbox"]').each(function(){
							order.push($(this).val());
						});
						form.find('[data-ww-name="ww-widget-tabOrder"]').val(order.join(','));
					}
				});
						
			}

		})
		
		.on('change','.ww-widget-weather-types [type="checkbox"]',function(){
		
			var form = $(this).closest('div.widget .widget-inside form');
		
			$.fn.checkTabCount(form,function(){
				form.find('.ww-widget-weather-types [type="checkbox"]:checked:last').prop('checked',false);
			});

		})
		
	// header type toggling
		
		.on('change','[name="ww-allow-search"]',function(){
		
			var form = $(this).closest('div.widget .widget-inside form');

			if ($(this).is(':checked'))
				form.find('[name="widget[widgetHeaderType]"]').val(3);
			else
				form.find('[name="widget[widgetHeaderType]"]').val(1);

		})
	
	// build colour picker

		.on('focus','[name="widget[colour]"]',function(){
		
			if (!$(this).next('.iris-picker').length)
				$(this).iris({
					hide: false,
					palettes: ['#eee','#555','#222','#58217c','#184da4','#40ce40','#f6f274','#fb940b','#f33','#ff98bf']
				});

		})

	// check tab width when changing width

		.on('change','[name="widget[width]"]',function(){
		
			var form = $(this).closest('div.widget .widget-inside form'),
				max = $(this).attr('max');
		
			$.fn.checkTabCount(form,function(){
				form.find('[name="widget[width]"]').val(max);
			});

		})
		
	;

});
