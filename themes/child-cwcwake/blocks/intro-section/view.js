( () => {
	document.querySelectorAll( '.cwc-intro__lite-embed' ).forEach( ( embed ) => {
		embed.addEventListener( 'click', () => {
			const url = embed.dataset.embedUrl;
			if ( ! url ) return;

			const iframe = document.createElement( 'iframe' );
			iframe.className = 'cwc-intro__video';
			iframe.src = url;
			iframe.frameBorder = '0';
			iframe.allow = 'autoplay; fullscreen; picture-in-picture';
			iframe.allowFullscreen = true;

			embed.innerHTML = '';
			embed.appendChild( iframe );
			embed.classList.add( 'is-loaded' );
		} );
	} );
} )();
