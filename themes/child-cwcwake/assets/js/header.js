/*
 * CWC Wake — Header behavior
 *
 * 1. Home-page wave / opaque-on-scroll toggle.
 *    Only the home page uses the transparent-at-top + opaque-on-scroll
 *    behavior. Every other page renders the header in the opaque
 *    (scrolled) state at all times — handled entirely in CSS via
 *    `body:not(.cwc-home)` selectors.
 *
 * 2. Off-canvas mobile / tablet navigation drawer.
 *    Toggled by the .cwc-header__burger button. Closes on:
 *      • burger / close button click
 *      • backdrop click
 *      • Escape key
 *      • viewport widening past the desktop breakpoint
 *      • clicking a leaf nav link inside the drawer
 *    Locks body scroll while open.
 */

( () => {
	const header = document.querySelector( '.cwc-header' );
	if ( ! header ) return;

	/* ---------- 1. Wave / scrolled state (home only) ---------- */

	if ( document.body.classList.contains( 'cwc-home' ) ) {
		const scrollClass = 'cwc-header--scrolled';
		const threshold = 10;

		const updateScroll = () => {
			header.classList.toggle( scrollClass, window.scrollY > threshold );
		};

		window.addEventListener( 'scroll', updateScroll, { passive: true } );
		updateScroll();
	}

	/* ---------- 2. Off-canvas drawer ---------- */

	const burger = header.querySelector( '.cwc-header__burger' );
	const drawer = header.querySelector( '.cwc-header__nav-center' );
	const backdrop = header.querySelector( '.cwc-header__backdrop' );
	const closeBtn = header.querySelector( '.cwc-header__nav-close' );

	if ( ! burger || ! drawer ) return;

	const DESKTOP_QUERY = window.matchMedia( '(min-width: 1200px)' );
	const OPEN_BODY_CLASS = 'cwc-nav-open';

	const setBackdropVisible = ( visible ) => {
		if ( ! backdrop ) return;
		if ( visible ) {
			backdrop.hidden = false;
			// Force reflow so the transition runs on first open.
			void backdrop.offsetWidth;
			backdrop.classList.add( 'is-visible' );
		} else {
			backdrop.classList.remove( 'is-visible' );
			// Hide after the fade-out transition finishes.
			setTimeout( () => {
				if ( ! backdrop.classList.contains( 'is-visible' ) ) {
					backdrop.hidden = true;
				}
			}, 300 );
		}
	};

	const openDrawer = () => {
		drawer.classList.add( 'is-open' );
		burger.setAttribute( 'aria-expanded', 'true' );
		burger.setAttribute( 'aria-label', 'Close menu' );
		document.body.classList.add( OPEN_BODY_CLASS );
		setBackdropVisible( true );
	};

	const closeDrawer = () => {
		drawer.classList.remove( 'is-open' );
		burger.setAttribute( 'aria-expanded', 'false' );
		burger.setAttribute( 'aria-label', 'Open menu' );
		document.body.classList.remove( OPEN_BODY_CLASS );
		setBackdropVisible( false );

		// Collapse any open accordion sub-menus.
		drawer.querySelectorAll( '.wp-block-navigation-item.is-submenu-open' )
			.forEach( ( item ) => {
				item.classList.remove( 'is-submenu-open' );
				const ic = item.querySelector( ':scope > .wp-block-navigation__submenu-icon' );
				if ( ic ) ic.setAttribute( 'aria-expanded', 'false' );
			} );
	};

	const toggleDrawer = () => {
		if ( drawer.classList.contains( 'is-open' ) ) {
			closeDrawer();
		} else {
			openDrawer();
		}
	};

	burger.addEventListener( 'click', ( event ) => {
		event.preventDefault();
		toggleDrawer();
	} );

	if ( backdrop ) {
		backdrop.addEventListener( 'click', closeDrawer );
	}

	if ( closeBtn ) {
		closeBtn.addEventListener( 'click', ( event ) => {
			event.preventDefault();
			closeDrawer();
			burger.focus();
		} );
	}

	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' && drawer.classList.contains( 'is-open' ) ) {
			closeDrawer();
			burger.focus();
		}
	} );

	/*
	 * In-drawer behavior:
	 *
	 *   • Click on a top-level row's chevron → toggle its sub-menu.
	 *   • Click on a parent link with no real destination (href="#")
	 *     also toggles its sub-menu (the click is intercepted).
	 *   • Click on any leaf link (or a parent link that has a real
	 *     destination) → close the drawer and let the navigation
	 *     happen naturally.
	 */
	drawer.addEventListener( 'click', ( event ) => {
		const chevron = event.target.closest( '.wp-block-navigation__submenu-icon' );
		if ( chevron ) {
			const parent = chevron.closest( '.wp-block-navigation-item.has-child' );
			if ( parent ) {
				event.preventDefault();
				event.stopPropagation();
				parent.classList.toggle( 'is-submenu-open' );
				chevron.setAttribute(
					'aria-expanded',
					parent.classList.contains( 'is-submenu-open' ) ? 'true' : 'false'
				);
			}
			return;
		}

		const link = event.target.closest( 'a' );
		if ( ! link ) return;

		const item = link.closest( '.wp-block-navigation-item' );
		const isPlaceholder = ! link.getAttribute( 'href' ) ||
			link.getAttribute( 'href' ) === '#' ||
			link.getAttribute( 'href' ) === '';

		// Parent placeholder link → behave like the chevron.
		if ( item && item.classList.contains( 'has-child' ) && isPlaceholder ) {
			event.preventDefault();
			item.classList.toggle( 'is-submenu-open' );
			const ic = item.querySelector( ':scope > .wp-block-navigation__submenu-icon' );
			if ( ic ) {
				ic.setAttribute(
					'aria-expanded',
					item.classList.contains( 'is-submenu-open' ) ? 'true' : 'false'
				);
			}
			return;
		}

		// Real navigation → close the drawer.
		closeDrawer();
	} );

	/*
	 * If the viewport widens past the breakpoint while the drawer is
	 * open, force-close so we don't leave the body locked or the
	 * backdrop visible behind the desktop layout.
	 */
	const handleViewportChange = ( event ) => {
		if ( event.matches && drawer.classList.contains( 'is-open' ) ) {
			closeDrawer();
		}
	};

	if ( typeof DESKTOP_QUERY.addEventListener === 'function' ) {
		DESKTOP_QUERY.addEventListener( 'change', handleViewportChange );
	} else if ( typeof DESKTOP_QUERY.addListener === 'function' ) {
		// Safari < 14 fallback.
		DESKTOP_QUERY.addListener( handleViewportChange );
	}
} )();
