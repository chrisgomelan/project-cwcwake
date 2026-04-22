/*
 * CWC Wake — About: Champions carousel + phrase cycling
 *
 * Creates a curved 3D carousel from the slide elements and
 * auto-advances every 3s, cycling the phrase overlay in sync.
 */

( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		const root = document.querySelector( '[data-cwc-champions]' );
		if ( ! root ) return;

		const track   = root.querySelector( '.cwc-champions__track' );
		let slides    = Array.from( root.querySelectorAll( '.cwc-champions__slide' ) );
		const phrases = Array.from( root.querySelectorAll( '[data-cwc-phrase]' ) );
		const originalTotal = slides.length;
		if ( originalTotal === 0 ) return;

		// We need a smooth, wide curve. So we'll fill a large cylinder
		// with cloned slides. Let's aim for ~16-18 slots on the cylinder.
		const TARGET_SLOTS = 15;
		
		// Clone slides so we have enough to form a smooth cylinder
		if ( slides.length < TARGET_SLOTS ) {
			const clonesNeeded = TARGET_SLOTS - slides.length;
			for ( let i = 0; i < clonesNeeded; i++ ) {
				const clone = slides[ i % originalTotal ].cloneNode( true );
				track.appendChild( clone );
			}
			// Update slides array
			slides = Array.from( root.querySelectorAll( '.cwc-champions__slide' ) );
		}

		const total = slides.length;
		const angleStep = 360 / total;
		
		let radius = 0;
		let currentAngle = 0;
		let currentItem = 0;

		function initializeCarousel() {
			const slideWidth = slides[0].offsetWidth; 
			// Trigonometry to define depth. Adding gap to make it look flush but distinct
			radius = Math.round( ( slideWidth / 2 ) / Math.tan( (Math.PI / total) ) ) + 20;

			slides.forEach( ( slide, i ) => {
				const angle = i * angleStep;
				// Placing items on the inside curve (concave)
				slide.style.transform = `rotateY(${-angle}deg) translateZ(-${radius}px)`;
			});

			updateView();
		}

		function updateView() {
			// Rotate the track based on concave direction and pull it forward so the active slide is flush
			track.style.transform = `translate(-50%, -50%) translateZ(${radius}px) rotateY(${-currentAngle}deg)`;
			
			slides.forEach( ( slide, i ) => {
				slide.classList.toggle('is-active', i === currentItem);
			});

			if ( phrases.length > 0 ) {
				const phraseIdx = currentItem % originalTotal;
				phrases.forEach( ( p, pi ) => {
					p.classList.toggle( 'is-active', pi === phraseIdx );
				} );
			}
		}

		let resizeTimer;
		window.addEventListener('resize', () => {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(initializeCarousel, 250);
		});

		function next() {
			currentAngle -= angleStep;
			currentItem = (currentItem + 1) % total;
			updateView();
		}

		// Initial setup
		setTimeout(initializeCarousel, 100);
		setInterval( next, 3000 );
	} );
} )();
