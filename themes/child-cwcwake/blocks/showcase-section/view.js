( () => {
	document.querySelectorAll( '.cwc-showcase--videos' ).forEach( ( section ) => {
		const grid = section.querySelector( '.cwc-showcase__grid' );
		const cards = grid ? Array.from( grid.querySelectorAll( '.cwc-showcase__card--video' ) ) : [];
		const dots = Array.from( section.querySelectorAll( '.cwc-showcase__dot' ) );
		const prev = section.querySelector( '.cwc-showcase__arrow--prev' );
		const next = section.querySelector( '.cwc-showcase__arrow--next' );

		if ( cards.length <= 3 ) return;

		let currentPage = 0;
		const itemsPerPage = 3;
		const totalPages = Math.ceil( cards.length / itemsPerPage );

		const goToPage = ( pageIndex ) => {
			if ( pageIndex < 0 ) pageIndex = totalPages - 1;
			if ( pageIndex >= totalPages ) pageIndex = 0;

			// Pause videos in previous slide
			section.querySelectorAll( 'video' ).forEach( ( v ) => v.pause() );
			section.querySelectorAll( 'iframe' ).forEach( ( f ) => {
				const src = f.src;
				f.src = src;
			} );

			// Calculate card index to scroll to
			let cardIndex = pageIndex * itemsPerPage;
			if ( cardIndex >= cards.length ) cardIndex = cards.length - 1;

			const offset = cards[ cardIndex ].offsetLeft - grid.offsetLeft;
			grid.scrollTo( { left: offset, behavior: 'smooth' } );

			dots.forEach( ( d, i ) => {
				d.classList.toggle( 'cwc-showcase__dot--active', i === pageIndex );
			} );

			currentPage = pageIndex;
		};

		prev?.addEventListener( 'click', () => goToPage( currentPage - 1 ) );
		next?.addEventListener( 'click', () => goToPage( currentPage + 1 ) );

		dots.forEach( ( dot, i ) => {
			dot.style.cursor = 'pointer';
			dot.addEventListener( 'click', () => goToPage( i ) );
		} );
	} );
} )();
