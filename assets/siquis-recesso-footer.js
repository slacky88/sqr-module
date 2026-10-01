/**
 * Keeps the footer withdrawal link above fixed/sticky bars anchored to the bottom
 * of the viewport (sticky add-to-cart, chat, WhatsApp, cookie banners).
 */
( function () {
	'use strict';

	function bottomCover( link ) {
		var linkBox = link.getBoundingClientRect();
		var vh = window.innerHeight;
		var max = 0;
		var all = document.body.getElementsByTagName( '*' );

		for ( var i = 0; i < all.length; i++ ) {
			var el = all[ i ];
			if ( el === link || el.contains( link ) || link.contains( el ) ) {
				continue;
			}
			var style = window.getComputedStyle( el );
			if ( 'fixed' !== style.position && 'sticky' !== style.position ) {
				continue;
			}
			if ( 'none' === style.display || 'hidden' === style.visibility || '0' === style.opacity ) {
				continue;
			}
			var box = el.getBoundingClientRect();
			var anchoredBottom = box.bottom >= vh - 20 && box.top < vh;
			var overlapsX = box.left < linkBox.right && box.right > linkBox.left;
			if ( anchoredBottom && overlapsX && box.height > 0 && box.height < vh * 0.4 ) {
				max = Math.max( max, vh - box.top );
			}
		}
		return max;
	}

	function adjust() {
		var wrap = document.querySelector( '.siquis-recesso-footer' );
		var link = wrap && wrap.querySelector( 'a' );
		if ( ! link ) {
			return;
		}
		var cover = bottomCover( link );
		wrap.style.paddingBottom = cover > 0 ? ( Math.ceil( cover ) + 12 ) + 'px' : '';
	}

	function schedule() {
		try {
			adjust();
		} catch ( e ) {}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', schedule );
	} else {
		schedule();
	}
	window.addEventListener( 'load', schedule );
	window.addEventListener( 'resize', schedule );
	setTimeout( schedule, 1000 );
	setTimeout( schedule, 3000 );
	// Banners that close on click (e.g. cookie consent) free the space again.
	document.addEventListener( 'click', function () {
		setTimeout( schedule, 600 );
	} );
}() );
