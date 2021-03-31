/* global ajaxurl, fusionBuilderConfig */
jQuery( document ).ready( function() {

	jQuery( '.fusion-builder-admin-toggle-heading' ).on( 'click', function() {
		jQuery( this ).parent().find( '.fusion-builder-admin-toggle-content' ).slideToggle( 300 );

		if ( jQuery( this ).find( '.fusion-builder-admin-toggle-icon' ).hasClass( 'fusion-plus' ) ) {
			jQuery( this ).find( '.fusion-builder-admin-toggle-icon' ).removeClass( 'fusion-plus' ).addClass( 'fusion-minus' );
		} else {
			jQuery( this ).find( '.fusion-builder-admin-toggle-icon' ).removeClass( 'fusion-minus' ).addClass( 'fusion-plus' );
		}

	} );

	jQuery( '.enable-builder-ui .ui-button' ).on( 'click', function( e ) {
		e.preventDefault();

		jQuery( this ).parent().find( '#enable_builder_ui_by_default' ).val( jQuery( this ).data( 'value' ) );
		jQuery( this ).parent().find( '#enable_builder_sticky_publish_buttons' ).val( jQuery( this ).data( 'value' ) );
		jQuery( this ).parent().find( '.ui-button' ).removeClass( 'ui-state-active' );
		jQuery( this ).addClass( 'ui-state-active' );
	} );

	jQuery( '.fusion-check-all' ).click( function( e ) {
		e.preventDefault();
		jQuery( this ).parents( '.fusion-builder-option' ).find( '.fusion-builder-option-field input' ).prop( 'checked', true );
	} );

	jQuery( '.fusion-uncheck-all' ).click( function( e ) {
		e.preventDefault();
		jQuery( this ).parents( '.fusion-builder-option' ).find( '.fusion-builder-option-field input' ).prop( 'checked', false );
	} );

	jQuery( '.fusion-runcheck' ).click( function( e ) {
		var $button = jQuery( this );

		e.preventDefault();

		if ( $button.hasClass( 'disabled' ) ) {
			return;
		}

		$button.addClass( 'disabled' );

		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {
				action: 'fusion_check_elements',
				fusion_import_nonce: fusionBuilderConfig.fusion_import_nonce
			}
		} )
		.done( function( elements ) {
			var $checkboxes = jQuery( '.fusion-builder-element-checkboxes' );
			if ( 'object' === typeof elements && 'object' === typeof elements.data ) {
				jQuery.each( elements.data, function( index, element ) {
					var $checkbox = $checkboxes.find( 'input[value="' + element + '"]' );
					if ( ! $checkbox.closest( 'li' ).hasClass( 'hidden' ) ) {
						$checkbox.prop( 'checked', false );
					}
				} );
			}
			$button.removeClass( 'disabled' );
		} )
		.fail( function() {
			$button.removeClass( 'disabled' );
		} );
	} );

	jQuery( '.enable-builder-ui .ui-button' ).on( 'click', function( e ) {
		e.preventDefault();

		jQuery( this ).parent().find( '#enable_builder_ui_by_default' ).val( jQuery( this ).data( 'value' ) );
		jQuery( this ).parent().find( '#enable_builder_sticky_publish_buttons' ).val( jQuery( this ).data( 'value' ) );
		jQuery( this ).parent().find( '.ui-button' ).removeClass( 'ui-state-active' );
		jQuery( this ).addClass( 'ui-state-active' );
	} );


	jQuery( '#fusion-library-type' ).on( 'change', function( event ) {
		if ( 'templates' === jQuery( event.target ).val() ) {
			jQuery( '#fusion-global-field' ).css( { display: 'none' } );
		} else {
			jQuery( '#fusion-global-field' ).css( { display: 'flex' } );
		}
	} );

	// Dimiss notice on templates page.
	jQuery( '.fusion-builder-template-notification button.notice-dismiss' ).on( 'click', function( event ) {
		var $this = jQuery( this ),
			data  = $this.parent().data();

		event.preventDefault();

		// Make ajax request.
		jQuery.post( ajaxurl, {
			data: data,
			action: 'fusion_dismiss_admin_notice',
			nonce: data.nonce
		} );

		$this.closest( '.fusion-builder-important-notice-wrapper' ).removeClass( 'fusion-has-notification' );
		$this.parent().css( 'display', 'none' );
	} );

	jQuery( '.avada-db-more-info' ).on( 'click', function() {
		jQuery( this ).closest( '.fusion-builder-important-notice-wrapper' ).addClass( 'fusion-has-notification' ).find( '.fusion-builder-template-notification' ).css( 'display', 'block' );
	} );

	// Prevent form being submitted multiple times.
	jQuery( '#fusion-create-layout-form, #fusion-create-template-form' ).on( 'submit', function() {
		jQuery( this ).find( 'input[type="submit"]' ).prop( 'disabled', true );
	} );

} );
