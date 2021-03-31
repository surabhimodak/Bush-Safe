/* global fusionBuilderConfig */
( function( jQuery ) {

	'use strict';

	jQuery( document ).ready( function() {

		jQuery( '.fusion-remove-form-entry' ).on( 'click', function( event ) {
			var self = this;

			event.preventDefault();

			if ( window.confirm( fusionBuilderConfig.remove_entry_message ) ) { // eslint-disable-line no-alert
				jQuery.ajax( {
					type: 'POST',
					url: fusionBuilderConfig.ajaxurl,
					data: {
						action: 'fusion_remove_form_entry',
						fusion_entry_nonce: fusionBuilderConfig.fusion_entry_nonce,
						entry: jQuery( this ).data( 'key' )
					},
					complete: function() {
						jQuery( self ).closest( 'tr' ).remove();
					}
				} );
			}
		} );
	} );
}( jQuery ) );
