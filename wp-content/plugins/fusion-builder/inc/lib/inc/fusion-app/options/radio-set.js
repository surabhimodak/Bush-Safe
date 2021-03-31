var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.radioButtonSet = {
	optionRadioButtonSet: function( $element ) {
		var $radiobuttonsets, $radiobuttonset, $radiosetcontainer, optionId, $subgroupWrapper,
			self = this;

		$element         = $element || this.$el;
		$radiobuttonsets = $element.find( '.fusion-form-radio-button-set' );

		if ( $radiobuttonsets.length ) {
			$radiobuttonsets.each( function() {
				$radiobuttonset = jQuery( this );
				optionId        = $radiobuttonset.closest( '.fusion-builder-option' ).attr( 'data-option-id' );

				if ( 'color_scheme' !== optionId && 'scheme_type' !== optionId ) {
					$radiobuttonset.find( 'a' ).on( 'click', function( event ) {
						event.preventDefault();
						$radiosetcontainer = jQuery( this ).closest( '.fusion-form-radio-button-set' );
						$subgroupWrapper   = $radiosetcontainer.closest( '.fusion-builder-option.subgroup' ).parent();

						$radiosetcontainer.find( '.ui-state-active' ).removeClass( 'ui-state-active' );
						jQuery( this ).addClass( 'ui-state-active' );
						$radiosetcontainer.find( '.button-set-value' ).val( $radiosetcontainer.find( '.ui-state-active' ).data( 'value' ) ).trigger( 'change' );
						jQuery( this ).blur();

						if ( $radiosetcontainer.closest( '.fusion-builder-option.subgroup' ).length ) {
							$subgroupWrapper.find( '.fusion-subgroup-content' ).removeClass( 'active' );
							$subgroupWrapper.find( '.fusion-subgroup-' + $radiosetcontainer.find( '.ui-state-active' ).data( 'value' ) ).addClass( 'active' );
						}
					} );
				} else {
					$radiobuttonset.find( 'a' ).on( 'click', function( event ) {
						event.preventDefault();
						if ( 'function' === typeof self.colorSchemeImport ) {
							self.colorSchemeImport( jQuery( event.currentTarget ), jQuery( event.currentTarget ).closest( '.fusion-builder-option' ) );
						}
					} );
				}
			} );
		}
	}
};
