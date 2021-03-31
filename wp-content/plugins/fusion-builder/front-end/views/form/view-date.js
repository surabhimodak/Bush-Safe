var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion Form Date View.
		FusionPageBuilder.fusion_form_date = FusionPageBuilder.FormComponentView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 3.1
			 * @param {Object} atts - The attributes object.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Create attribute objects;
				attributes.styles = this.buildStyles( atts.values );
				attributes.html   = this.generateFormFieldHtml( this.generateDateFieldHtml( atts.values ) );

				return attributes;
			},

			generateDateFieldHtml: function( values ) {
				var elementData,
					elementHtml,
					html = '';

				elementData = this.elementData( values );

				this.generateTooltipHtml( values, elementData );

				elementHtml = '<input id="date-' + this.model.get( 'cid' ) + '" type="date" data-type="' + values.picker + '" name="' + values.name + '" ' + elementData.holds_private_data + elementData[ 'class' ] + elementData.required + elementData.placeholder + '/>';

				elementHtml = this.generateIconHtml( values, elementHtml );

				html = this.generateLabelHtml( html, elementHtml, elementData.label );

				return html;
			},

			onRender: function() {
				this.afterPatch();
			},

			beforePatch: function() {
				var picker;
				if ( 'function' === typeof jQuery( '#fb-preview' )[ 0 ].contentWindow.flatpickr ) {
					picker = jQuery( '#fb-preview' )[ 0 ].contentWindow.flatpickr( '#date-' + this.model.get( 'cid' ), {} );
					if ( picker && 'function' === typeof picker.destroy ) {
						picker.destroy();
					}
				}
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 3.1
			 * @return {void}
			 */
			afterPatch: function() {
				var self = this;
				setTimeout( function() {
					var $item 	  = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( self.$el.find( 'input[type="date"]' ) ),
						type      = $item.attr( 'data-type' ),
						useMobile = 'custom' === type;

					// Native, do not init.
					if ( 'native' === type || 'function' !== typeof $item.flatpickr ) {
						return;
					}
					$item.flatpickr( {
						defaultDate: new Date().getTime(),
						disableMobile: useMobile
					} );
				}, 200 );
			}

		} );
	} );
}( jQuery ) );
