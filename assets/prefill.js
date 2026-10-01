( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var data = window.siquisRecessoPrefill;
		if ( ! data || typeof data !== 'object' ) {
			return;
		}
		Object.keys( data ).forEach( function ( name ) {
			var field = document.querySelector( '[name="' + name + '"]' );
			if ( field && ! field.value ) {
				field.value = data[ name ];
			}
		} );
	} );
}() );
