/**
 * CWC Wake — Single Blog Post helpers
 *
 * 1. Populate the hero section with the featured image + metadata.
 * 2. Scrollspy: highlights TOC items, moves the dot indicator and
 *    fills the progress rail as the user scrolls through sections.
 * 3. Smooth-scroll on TOC link clicks.
 */

( () => {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', () => {

		/* ── 1. Populate hero section ──────────────────────────────── */

		const hero = document.querySelector( '.cwc-single-post__hero' );
		if ( hero && typeof cwcSinglePost !== 'undefined' ) {
			const d = cwcSinglePost;
			let html = '';

			if ( d.image ) {
				html += `<img class="cwc-single-post__featured-img" src="${d.image}" alt="" loading="eager">`;
			}

			html += '<div class="cwc-single-post__meta">';
			if ( d.date ) {
				html += `
					<span class="cwc-single-post__meta-item">
						<img class="cwc-single-post__meta-icon" src="${d.themeUri}/assets/images/published-date-icon.svg" alt="" aria-hidden="true">
						${d.date}
					</span>`;
			}
			if ( d.readTime ) {
				html += `
					<span class="cwc-single-post__meta-item">
						<img class="cwc-single-post__meta-icon" src="${d.themeUri}/assets/images/eye-read.svg" alt="" aria-hidden="true">
						${d.readTime}
					</span>`;
			}
			html += '</div>';

			hero.innerHTML = html;
		}

		/* ── 2. Scrollspy ──────────────────────────────────────────── */

		const contentBox = document.querySelector( '.cwc-single-post__content-box' );
		const tocLinks   = document.querySelectorAll( '.cwc-toc__link' );
		const tocDot     = document.querySelector( '.cwc-toc__dot' );
		const tocRail    = document.querySelector( '.cwc-toc__rail' );

		if ( ! contentBox || tocLinks.length === 0 ) return;

		const headings     = contentBox.querySelectorAll( 'h2, h3' );
		const stickyOffset = 160;

		/* Assign IDs to headings so the TOC href anchors resolve. */
		tocLinks.forEach( ( link, index ) => {
			const anchor = link.getAttribute( 'href' ).substring( 1 );
			if ( headings[ index ] ) {
				headings[ index ].id = anchor;
			}
		} );

		const onScroll = () => {
			let currentId  = '';
			const scrollPos  = window.scrollY + stickyOffset;

			headings.forEach( ( h ) => {
				if ( h.getBoundingClientRect().top + window.scrollY <= scrollPos ) {
					currentId = h.id;
					h.classList.add( 'passed' );
				} else {
					h.classList.remove( 'passed' );
				}
			} );

			let activeIdx = -1;
			tocLinks.forEach( ( link, i ) => {
				link.classList.remove( 'active', 'passed' );
				if ( link.getAttribute( 'href' ) === `#${currentId}` ) {
					link.classList.add( 'active' );
					activeIdx = i;
				}
			} );

			/* Mark all preceding items as "passed" and move the dot */
			if ( activeIdx !== -1 ) {
				for ( let i = 0; i <= activeIdx; i++ ) {
					tocLinks[ i ].classList.add( 'passed' );
				}

				const active = tocLinks[ activeIdx ];
				if ( active && tocDot && tocRail ) {
					const railRect = tocRail.getBoundingClientRect();
					const linkRect = active.getBoundingClientRect();
					const dotTop   = ( linkRect.top - railRect.top ) + ( linkRect.height / 2 );
					tocDot.style.top     = `${dotTop}px`;
					tocDot.style.opacity = '1';
				}
			} else if ( tocDot ) {
				tocDot.style.opacity = '0';
			}

			/* Progress rail */
			const progress = document.querySelector( '.cwc-toc__rail-progress' );
			if ( progress && contentBox ) {
				const contentRect = contentBox.getBoundingClientRect();
				const total       = contentBox.scrollHeight;
				const scrollOff   = window.scrollY + ( window.innerHeight / 2 );
				const contentTop  = contentRect.top + window.scrollY;
				let pct         = ( scrollOff - contentTop ) / total;
				pct = Math.max( 0, Math.min( 1, pct ) );
				progress.style.height = `${pct * 100}%`;
			}
		};

		/* ── 3. Smooth scroll on TOC click ────────────────────────── */

		tocLinks.forEach( ( link ) => {
			link.addEventListener( 'click', ( e ) => {
				e.preventDefault();
				const id     = link.getAttribute( 'href' ).substring( 1 );
				const target = document.getElementById( id );
				if ( ! target ) return;

				const headerH  = 160; // Clear fixed header (+ breathing room)
				const elemPos  = target.getBoundingClientRect().top;
				const scrollTo = elemPos + window.pageYOffset - headerH;

				window.scrollTo( { top: scrollTo, behavior: 'smooth' } );
			} );
		} );

		/* Bind scroll handler */
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();

		/* ── Rail glow while scrolling ────────────────────────────── */
		let scrollTimer;
		window.addEventListener( 'scroll', () => {
			if ( tocRail ) {
				tocRail.classList.add( 'is-scrolling' );
				clearTimeout( scrollTimer );
				scrollTimer = setTimeout( () => {
					tocRail.classList.remove( 'is-scrolling' );
				}, 300 );
			}
		}, { passive: true } );

	} );
} )();
