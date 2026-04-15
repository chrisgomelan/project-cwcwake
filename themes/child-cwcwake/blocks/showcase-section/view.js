( function () {
	document.querySelectorAll( '.cwc-showcase--videos' ).forEach( function ( section ) {
		var grid  = section.querySelector( '.cwc-showcase__grid' );
		var cards = grid ? Array.from( grid.querySelectorAll( '.cwc-showcase__card--video' ) ) : [];
		var dots  = Array.from( section.querySelectorAll( '.cwc-showcase__dot' ) );
		var prev  = section.querySelector( '.cwc-showcase__arrow--prev' );
		var next  = section.querySelector( '.cwc-showcase__arrow--next' );

		if ( cards.length < 2 ) return;

		var current = 0;

		function goTo( index ) {
			if ( index < 0 ) index = cards.length - 1;
			if ( index >= cards.length ) index = 0;

			cards[ current ].querySelectorAll( 'video' ).forEach( function ( v ) {
				v.pause();
			} );

			var offset = cards[ index ].offsetLeft - grid.offsetLeft;
			grid.scrollTo( { left: offset, behavior: 'smooth' } );

			dots.forEach( function ( d, i ) {
				d.classList.toggle( 'cwc-showcase__dot--active', i === index );
			} );

			current = index;
		}

		if ( prev ) {
			prev.addEventListener( 'click', function () {
				goTo( current - 1 );
			} );
		}

		if ( next ) {
			next.addEventListener( 'click', function () {
				goTo( current + 1 );
			} );
		}

		dots.forEach( function ( dot, i ) {
			dot.style.cursor = 'pointer';
			dot.addEventListener( 'click', function () {
				goTo( i );
			} );
		} );
	} );
} )();
