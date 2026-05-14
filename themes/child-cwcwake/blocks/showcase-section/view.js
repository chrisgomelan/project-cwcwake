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
		const nav = section.querySelector( '.cwc-showcase__carousel-nav' );
		const dotsWrap = section.querySelector( '.cwc-showcase__dots' );
		const prev = section.querySelector( '.cwc-showcase__arrow--prev' );
		const next = section.querySelector( '.cwc-showcase__arrow--next' );
		const cards = grid ? Array.from( grid.querySelectorAll( '.cwc-showcase__card--video' ) ) : [];

		if ( ! grid || ! dotsWrap || cards.length === 0 ) {
			return;
		}

		/* Match showcase-section/style.css video card flex basis */
		const getItemsPerPage = () => {
			const w = window.innerWidth;
			if ( w <= 600 ) {
				return 1;
			}
			if ( w <= 900 ) {
				return 2;
			}
			return 3;
		};

		let currentPage = 0;
		let dots = [];

		const getTotalPages = () => {
			const ipp = getItemsPerPage();
			return Math.max( 1, Math.ceil( cards.length / ipp ) );
		};

		const rebuildDots = ( totalPages ) => {
			dotsWrap.innerHTML = '';
			dots = [];
			for ( let i = 0; i < totalPages; i++ ) {
				const btn = document.createElement( 'button' );
				btn.type = 'button';
				btn.className = 'cwc-showcase__dot' + ( i === currentPage ? ' cwc-showcase__dot--active' : '' );
				btn.setAttribute( 'aria-label', `Slide ${ i + 1 } of ${ totalPages }` );
				btn.addEventListener( 'click', () => goToPage( i ) );
				dotsWrap.appendChild( btn );
				dots.push( btn );
			}
		};

		const syncDots = () => {
			dots.forEach( ( d, i ) => d.classList.toggle( 'cwc-showcase__dot--active', i === currentPage ) );
		};

		const scrollGridToPage = ( pageIndex, smooth ) => {
			const ipp = getItemsPerPage();
			let cardIndex = pageIndex * ipp;
			if ( cardIndex >= cards.length ) {
				cardIndex = Math.max( 0, cards.length - 1 );
			}
			const offset = cards[ cardIndex ].offsetLeft - grid.offsetLeft;
			grid.scrollTo( { left: offset, behavior: smooth ? 'smooth' : 'auto' } );
		};

		const goToPage = ( pageIndex ) => {
			const totalPages = getTotalPages();

			if ( pageIndex < 0 ) {
				pageIndex = totalPages - 1;
			}
			if ( pageIndex >= totalPages ) {
				pageIndex = 0;
			}

			section.querySelectorAll( 'video' ).forEach( ( v ) => v.pause() );
			section.querySelectorAll( 'iframe' ).forEach( ( f ) => {
				const src = f.src;
				f.src = src;
			} );

			currentPage = pageIndex;
			scrollGridToPage( currentPage, true );
			syncDots();
		};

		const refreshCarousel = () => {
			const ipp = getItemsPerPage();
			if ( ! nav ) {
				return;
			}

			if ( cards.length <= ipp ) {
				nav.classList.add( 'cwc-showcase__carousel-nav--hidden' );
				dotsWrap.innerHTML = '';
				dots = [];
				currentPage = 0;
				return;
			}

			nav.classList.remove( 'cwc-showcase__carousel-nav--hidden' );

			const totalPages = getTotalPages();
			if ( currentPage >= totalPages ) {
				currentPage = totalPages - 1;
			}
			if ( currentPage < 0 ) {
				currentPage = 0;
			}

			rebuildDots( totalPages );
			syncDots();
			scrollGridToPage( currentPage, false );
		};

		prev?.addEventListener( 'click', () => goToPage( currentPage - 1 ) );
		next?.addEventListener( 'click', () => goToPage( currentPage + 1 ) );

		refreshCarousel();

		let resizeTimer;
		window.addEventListener( 'resize', () => {
			clearTimeout( resizeTimer );
			resizeTimer = setTimeout( () => {
				refreshCarousel();
			}, 150 );
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
