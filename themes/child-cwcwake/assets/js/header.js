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

(() => {
	const header = document.querySelector('.cwc-header');
	if (!header) return;

	/* ---------- 1. Wave / scrolled state (home only) ---------- */

	if (document.body.classList.contains('cwc-home')) {
		const scrollClass = 'cwc-header--scrolled';
		const threshold = 10;
		let ticking = false;

		const updateScroll = () => {
			if (!ticking) {
				window.requestAnimationFrame(() => {
					header.classList.toggle(scrollClass, window.scrollY > threshold);
					ticking = false;
				});
				ticking = true;
			}
		};

		window.addEventListener('scroll', updateScroll, { passive: true });
		updateScroll();
	}

	/* ---------- 2. Off-canvas drawer ---------- */

	const burger = header.querySelector('.cwc-header__burger');
	const drawer = header.querySelector('.cwc-header__nav-center');
	const backdrop = header.querySelector('.cwc-header__backdrop');
	const closeBtn = header.querySelector('.cwc-header__nav-close');

	if (!burger || !drawer) return;

	const DESKTOP_QUERY = window.matchMedia('(min-width: 1200px)');
	const OPEN_BODY_CLASS = 'cwc-nav-open';

	const setBackdropVisible = (visible) => {
		if (!backdrop) return;
		if (visible) {
			backdrop.hidden = false;
			// Force reflow so the transition runs on first open.
			void backdrop.offsetWidth;
			backdrop.classList.add('is-visible');
		} else {
			backdrop.classList.remove('is-visible');
			// Hide after the fade-out transition finishes.
			setTimeout(() => {
				if (!backdrop.classList.contains('is-visible')) {
					backdrop.hidden = true;
				}
			}, 300);
		}
	};

	const openDrawer = () => {
		drawer.classList.add('is-open');
		burger.setAttribute('aria-expanded', 'true');
		burger.setAttribute('aria-label', 'Close menu');
		document.body.classList.add(OPEN_BODY_CLASS);
		setBackdropVisible(true);
	};

	const closeDrawer = () => {
		drawer.classList.remove('is-open');
		burger.setAttribute('aria-expanded', 'false');
		burger.setAttribute('aria-label', 'Open menu');
		document.body.classList.remove(OPEN_BODY_CLASS);
		setBackdropVisible(false);

		// Collapse any open sub-menus directly.
		drawer.querySelectorAll('.wp-block-navigation-item.is-submenu-open')
			.forEach((item) => {
				item.classList.remove('is-submenu-open');
				const toggle = item.querySelector('.wp-block-navigation-submenu__toggle, .wp-block-navigation__submenu-icon');
				if (toggle && toggle.tagName === 'BUTTON') {
					toggle.setAttribute('aria-expanded', 'false');
				}
			});
	};

	const toggleDrawer = () => {
		if (drawer.classList.contains('is-open')) {
			closeDrawer();
		} else {
			openDrawer();
		}
	};

	burger.addEventListener('click', (event) => {
		event.preventDefault();
		toggleDrawer();
	});

	if (backdrop) {
		backdrop.addEventListener('click', closeDrawer);
	}

	if (closeBtn) {
		closeBtn.addEventListener('click', (event) => {
			event.preventDefault();
			closeDrawer();
			burger.focus();
		});
	}

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && drawer.classList.contains('is-open')) {
			closeDrawer();
			burger.focus();
		}
	});

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

	const closeOtherSubmenus = (currentParent) => {
		drawer.querySelectorAll('.wp-block-navigation-item.is-submenu-open').forEach((openItem) => {
			if (openItem !== currentParent && !openItem.contains(currentParent)) {
				openItem.classList.remove('is-submenu-open');
				// Also update the toggle button's aria-expanded if it exists
				const toggle = openItem.querySelector('.wp-block-navigation-submenu__toggle, .wp-block-navigation__submenu-icon');
				if (toggle && toggle.tagName === 'BUTTON') {
					toggle.setAttribute('aria-expanded', 'false');
				}
			}
		});
	};

	drawer.addEventListener('click', (event) => {
		const target = event.target;
		const toggle = target.closest('.wp-block-navigation-submenu__toggle, .wp-block-navigation__submenu-icon');
		const link = target.closest('a');

		// Case 1: Clicked a toggle button/icon
		if (toggle) {
			event.preventDefault();
			event.stopPropagation();

			const item = toggle.closest('.wp-block-navigation-item.has-child');
			if (item) {
				const isOpening = !item.classList.contains('is-submenu-open');
				if (isOpening) closeOtherSubmenus(item);
				item.classList.toggle('is-submenu-open');

				const btn = item.querySelector('.wp-block-navigation-submenu__toggle');
				if (btn && btn.tagName === 'BUTTON') {
					btn.setAttribute('aria-expanded', isOpening);
				}
			}
			return;
		}

		// Case 2: Clicked a link
		if (link) {
			const item = link.closest('.wp-block-navigation-item.has-child');
			const href = link.getAttribute('href');
			const isPlaceholder = !href || href === '#' || href === '';

			if (item && isPlaceholder) {
				event.preventDefault();
				event.stopPropagation();

				const isOpening = !item.classList.contains('is-submenu-open');
				if (isOpening) closeOtherSubmenus(item);
				item.classList.toggle('is-submenu-open');

				const btn = item.querySelector('.wp-block-navigation-submenu__toggle');
				if (btn && btn.tagName === 'BUTTON') {
					btn.setAttribute('aria-expanded', isOpening);
				}
				return;
			}

			// Real navigation → close the drawer.
			closeDrawer();
		}
	});

	/*
	 * If the viewport widens past the breakpoint while the drawer is
	 * open, force-close so we don't leave the body locked or the
	 * backdrop visible behind the desktop layout.
	 */
	const handleViewportChange = (event) => {
		if (event.matches && drawer.classList.contains('is-open')) {
			closeDrawer();
		}
	};

	if (typeof DESKTOP_QUERY.addEventListener === 'function') {
		DESKTOP_QUERY.addEventListener('change', handleViewportChange);
	} else if (typeof DESKTOP_QUERY.addListener === 'function') {
		// Safari < 14 fallback.
		DESKTOP_QUERY.addListener(handleViewportChange);
	}

	/* ---------- 3. Robust Active State Logic ---------- */
	const applyActiveMenuStates = () => {
		const currentPath = window.location.pathname;
		const navLinks = header.querySelectorAll('.wp-block-navigation-item > a, .wp-block-navigation-item > .wp-block-navigation-item__content');

		navLinks.forEach(link => {
			const url = link.getAttribute('href');
			if (!url || url.startsWith('#')) return;

			try {
				const linkUrl = new URL(url, window.location.origin);
				const linkPath = linkUrl.pathname;

				// Check for exact path match
				if (linkPath === currentPath || linkPath === currentPath.replace(/\/$/, '') || linkPath === currentPath + '/') {
					const parentItem = link.closest('.wp-block-navigation-item');
					if (parentItem) {
						parentItem.classList.add('current-menu-item');

						const submenuContainer = parentItem.closest('.wp-block-navigation__submenu-container');
						if (submenuContainer) {
							const grandParentItem = submenuContainer.closest('.wp-block-navigation-item');
							if (grandParentItem) {
								grandParentItem.classList.add('current-menu-ancestor');
							}
						}
					}
				}
			} catch (e) {
				// Ignore invalid URLs
			}
		});
	};

	applyActiveMenuStates();

})();
