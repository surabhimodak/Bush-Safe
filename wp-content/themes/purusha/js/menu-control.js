/**
 * File menu-controls.js.
 *
 * Inspired by twentytwentyone theme
 * https://wordpress.org/themes/twentytwentyone/
 */

( function() {
	/**
	 * Menu Toggle Behaviors
	 *
	 * @param {string} id - The ID.
	 */
	var navMenu = function( id ) {
		var wrapper = document.body, // this is the element to which a CSS class is added when a mobile nav menu is open
			mobileButton = document.querySelector( '.menu-toggle' );

		if ( mobileButton ) {
			mobileButton.onclick = function() {
				wrapper.classList.toggle( id + '-navigation-open' );
				mobileButton.focus();
			};
		}
		/**
		 * Trap keyboard navigation in the menu modal.
		 */
		document.addEventListener( 'keydown', function( event ) {
			var modal, elements, selectors, lastEl, firstEl, activeEl, tabKey, shiftKey, escKey;
			if ( ! wrapper.classList.contains( id + '-navigation-open' ) ) {
				return;
			}

			modal = document.querySelector( '.main-navigation' );
			selectors = 'input, a, button';
			elements = modal.querySelectorAll( selectors );
			elements = Array.prototype.slice.call( elements );
			tabKey = event.keyCode === 9;
			shiftKey = event.shiftKey;
			escKey = event.keyCode === 27;
			activeEl = document.activeElement; // eslint-disable-line @wordpress/no-global-active-element
			lastEl = elements[ elements.length - 1 ];
			firstEl = elements[0];

			if ( ! shiftKey && tabKey && lastEl === activeEl ) {
				event.preventDefault();
				firstEl.focus();
			}

			if ( shiftKey && tabKey && firstEl === activeEl ) {
				event.preventDefault();
				lastEl.focus();
			}

			// If there are no elements in the menu, don't move the focus
			if ( tabKey && firstEl === lastEl ) {
				event.preventDefault();
			}
		} );

	};

	window.addEventListener( 'load', function() {
		new navMenu( 'primary' );
	} );
}() );
