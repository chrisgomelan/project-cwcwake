( () => {
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
