( () => {
	const header = document.querySelector( '.cwc-header' );
	if ( ! header ) return;

	/*
	 * Only the home page uses the transparent-at-top + opaque-on-scroll
	 * behavior. Every other page renders the header in the opaque
	 * (scrolled) state at all times — handled entirely in CSS via
	 * `body:not(.cwc-home)` selectors.
	 */
	if ( ! document.body.classList.contains( 'cwc-home' ) ) return;

	const scrollClass = 'cwc-header--scrolled';
	const threshold = 10;

	const update = () => {
		header.classList.toggle( scrollClass, window.scrollY > threshold );
	};

	window.addEventListener( 'scroll', update, { passive: true } );
	update();
} )();
