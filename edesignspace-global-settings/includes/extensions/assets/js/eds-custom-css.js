/**
 * EDS Custom CSS — Live Editor Preview
 * eDesign Space · https://edesignspace.com/
 */
( function() {
	'use strict';

	function injectCSS( el, settings ) {
		var raw = settings._eds_custom_css || '';
		var id  = 'eds-custom-css-' + el.getAttribute( 'data-id' );

		// Remove existing style tag for this element
		var existing = document.getElementById( id );
		if ( existing ) existing.remove();

		if ( ! raw.trim() ) return;

		var selector = '.elementor-element.elementor-element-' + el.getAttribute( 'data-id' );
		var css = raw.replace( /selector/g, selector );

		var style = document.createElement( 'style' );
		style.id = id;
		style.textContent = css;
		document.head.appendChild( style );
	}

	jQuery( window ).on( 'elementor/frontend/init', function() {

		var CSSHandler = elementorModules.frontend.handlers.Base.extend( {
			onInit: function() {
				elementorModules.frontend.handlers.Base.prototype.onInit.apply( this, arguments );
				injectCSS( this.$element[0], this.getElementSettings() );
			},
			onElementChange: function() {
				injectCSS( this.$element[0], this.getElementSettings() );
			}
		} );

		elementorFrontend.hooks.addAction( 'frontend/element_ready/global', function( $element ) {
			elementorFrontend.elementsHandler.addHandler( CSSHandler, { $element: $element } );
		} );

	} );

}() );
