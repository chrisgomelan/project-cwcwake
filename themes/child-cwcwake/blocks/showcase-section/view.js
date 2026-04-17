( () => {
	document.querySelectorAll( '.cwc-showcase--videos' ).forEach( ( section ) => {
		const grid = section.querySelector( '.cwc-showcase__grid' );
		const cards = grid ? Array.from( grid.querySelectorAll( '.cwc-showcase__card--video' ) ) : [];
		const dots = Array.from( section.querySelectorAll( '.cwc-showcase__dot' ) );
		const prev = section.querySelector( '.cwc-showcase__arrow--prev' );
		const next = section.querySelector( '.cwc-showcase__arrow--next' );

		if ( cards.length < 2 ) return;

		let current = 0;

		const goTo = ( index ) => {
			if ( index < 0 ) index = cards.length - 1;
			if ( index >= cards.length ) index = 0;

			cards[ current ].querySelectorAll( 'video' ).forEach( ( v ) => v.pause() );

			const offset = cards[ index ].offsetLeft - grid.offsetLeft;
			grid.scrollTo( { left: offset, behavior: 'smooth' } );

			dots.forEach( ( d, i ) => {
				d.classList.toggle( 'cwc-showcase__dot--active', i === index );
			} );

			current = index;
		};

		prev?.addEventListener( 'click', () => goTo( current - 1 ) );
		next?.addEventListener( 'click', () => goTo( current + 1 ) );

		dots.forEach( ( dot, i ) => {
			dot.style.cursor = 'pointer';
			dot.addEventListener( 'click', () => goTo( i ) );
		} );
	} );
} )();
