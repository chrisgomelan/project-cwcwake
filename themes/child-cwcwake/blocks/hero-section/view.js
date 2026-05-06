( () => {
	// Smart Loading for Background Video
	const heroVideos = document.querySelectorAll( 'video[data-hero-video]' );
	heroVideos.forEach( ( video ) => {
		if ( window.innerWidth > 1024 ) {
			const source = video.querySelector( 'source' );
			if ( source && source.dataset.src ) {
				source.src = source.dataset.src;
				video.load();
				// Autoplay is handled by the video attributes, but load() + play() ensures it starts
				video.play().catch( () => {
					// Autoplay might be blocked by browser policy until interaction
				} );
			}
		}
	} );

	document.querySelectorAll( '.cwc-hero__video-toggle' ).forEach( ( btn ) => {
		const hero = btn.closest( '.cwc-hero' );
		const video = hero?.querySelector( '.cwc-hero__video' );

		if ( ! video ) return;

		btn.addEventListener( 'click', () => {
			if ( video.paused ) {
				video.play();
				btn.setAttribute( 'data-playing', 'true' );
				btn.setAttribute( 'aria-label', 'Pause background video' );
			} else {
				video.pause();
				btn.setAttribute( 'data-playing', 'false' );
				btn.setAttribute( 'aria-label', 'Play background video' );
			}
		} );
	} );
} )();
