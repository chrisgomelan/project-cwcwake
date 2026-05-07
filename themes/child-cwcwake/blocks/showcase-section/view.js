( () => {
	// Smart Loading for Hover Videos
	const hoverVideos = document.querySelectorAll( 'video[data-hover-video]' );
	hoverVideos.forEach( ( video ) => {
		if ( window.innerWidth > 1024 ) {
			if ( video.dataset.src ) {
				video.src = video.dataset.src;
				video.load();
			}
		}
	} );

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
	// Lite Embed Handler
	document.querySelectorAll( '.cwc-showcase__lite-embed' ).forEach( ( embed ) => {
		embed.addEventListener( 'click', () => {
			const url = embed.dataset.embedUrl;
			if ( ! url ) return;

			// Add autoplay to the URL if not present
			const finalUrl = url.includes( '?' ) ? `${ url }&autoplay=1` : `${ url }?autoplay=1`;

			const iframe = document.createElement( 'iframe' );
			iframe.className = 'cwc-showcase__video cwc-showcase__video--iframe';
			iframe.src = finalUrl;
			iframe.frameBorder = '0';
			iframe.allow = 'autoplay; fullscreen; picture-in-picture';
			iframe.allowFullscreen = true;

			// Clear the poster and play button, then append iframe
			embed.innerHTML = '';
			embed.appendChild( iframe );
			embed.classList.add( 'is-loaded' );
		} );
	} );
} )();
