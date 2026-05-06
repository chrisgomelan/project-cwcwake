( () => {
	document.querySelectorAll( '.cwc-reviews__slider' ).forEach( ( slider ) => {
		const slides = slider.querySelectorAll( '.cwc-reviews__slide' );
		const currentDisplay = slider.querySelector( '.cwc-reviews__counter-current' );
		const total = slides.length;
		if ( total < 2 ) return;

		let current = 0;
		let timer = null;

		const show = ( index, direction ) => {
			if ( direction ) {
				slider.setAttribute( 'data-dir', direction );
			}

			slides.forEach( ( s, i ) => {
				if ( s.classList.contains( 'cwc-reviews__slide--active' ) ) {
					s.classList.remove( 'cwc-reviews__slide--active' );
					s.classList.add( 'cwc-reviews__slide--outgoing' );
				} else if ( i !== index ) {
					s.classList.remove( 'cwc-reviews__slide--outgoing' );
				}

				const prevBtn = s.querySelector( '.cwc-reviews__arrow--prev' ) || slider.querySelector( '.cwc-reviews__arrow--prev' );
				const nextBtn = s.querySelector( '.cwc-reviews__arrow--next' ) || slider.querySelector( '.cwc-reviews__arrow--next' );

				if ( prevBtn ) prevBtn.disabled = ( index === 0 );
				if ( nextBtn ) nextBtn.disabled = ( index === total - 1 );
			} );

			slides[ index ].classList.remove( 'cwc-reviews__slide--outgoing' );
			slides[ index ].classList.add( 'cwc-reviews__slide--active' );

			// Update counter text 
			if ( currentDisplay ) {
				currentDisplay.textContent = String( index + 1 ).padStart( 2, '0' );
			}

			clearTimeout( timer );
			timer = setTimeout( () => {
				slides.forEach( ( s ) => s.classList.remove( 'cwc-reviews__slide--outgoing' ) );
			}, 800 );
		};

		show( 0, '' );

		slider.querySelectorAll( '.cwc-reviews__arrow' ).forEach( ( btn ) => {
			btn.addEventListener( 'click', () => {
				if ( btn.disabled ) return;

				const dir = btn.getAttribute( 'data-dir' );
				if ( dir === 'next' && current < total - 1 ) {
					current++;
					show( current, 'next' );
				} else if ( dir === 'prev' && current > 0 ) {
					current--;
					show( current, 'prev' );
				}
			} );
		} );
	} );
} )();
