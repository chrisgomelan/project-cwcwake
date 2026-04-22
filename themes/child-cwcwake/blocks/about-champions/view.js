/*
 * CWC Wake — About: Champions carousel + phrase cycling
 *
 * Creates a curved 3D carousel from the slide elements and
 * auto-advances every 3s, cycling the phrase overlay in sync.
 */

( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var root = document.querySelector( '[data-cwc-champions]' );
		if ( ! root ) return;

		var slides  = Array.from( root.querySelectorAll( '.cwc-champions__slide' ) );
		var phrases = Array.from( root.querySelectorAll( '[data-cwc-phrase]' ) );
		var total   = slides.length;
		if ( total === 0 ) return;

		var current = 0;
		var INTERVAL = 3000;

		function layout() {
			var angleStep = 360 / total;
			var radius    = Math.min( 360, window.innerWidth * 0.35 );

			slides.forEach( function ( slide, i ) {
				var offset = ( ( i - current ) % total + total ) % total;
				var angle  = offset * angleStep;
				var rad    = ( angle * Math.PI ) / 180;

				var x     = Math.sin( rad ) * radius;
				var z     = Math.cos( rad ) * radius - radius;
				var scale = 0.6 + 0.4 * ( ( z + radius ) / radius );
				scale = Math.max( 0.5, Math.min( 1, scale ) );

				slide.style.transform =
					'translate(-50%, -50%) translateX(' + x + 'px) translateZ(' + z + 'px) scale(' + scale + ')';
				slide.style.zIndex    = Math.round( scale * 10 );
				slide.style.opacity   = scale < 0.65 ? 0.35 : scale;

				slide.classList.toggle( 'is-active', i === current );
			} );

			/* Cycle phrase */
			if ( phrases.length > 0 ) {
				var phraseIdx = current % phrases.length;
				phrases.forEach( function ( p, pi ) {
					p.classList.toggle( 'is-active', pi === phraseIdx );
				} );
			}
		}

		function next() {
			current = ( current + 1 ) % total;
			layout();
		}

		layout();
		setInterval( next, INTERVAL );
	} );
} )();
