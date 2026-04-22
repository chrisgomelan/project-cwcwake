/*
 * CWC Wake — Single Blog Post helpers
 *
 * 1. Populate the hero section with the featured image + metadata.
 * 2. Scrollspy: highlights TOC items, moves the dot indicator and
 *    fills the progress rail as the user scrolls through sections.
 * 3. Smooth-scroll on TOC link clicks.
 */

( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {

		/* ── 1. Populate hero section ──────────────────────────────── */

		const hero = document.querySelector( '.cwc-single-post__hero' );
		if ( hero && typeof cwcSinglePost !== 'undefined' ) {
			const d = cwcSinglePost;
			let html = '';

			if ( d.image ) {
				html += '<img class="cwc-single-post__featured-img" src="' + d.image + '" alt="" loading="eager">';
			}

			html += '<div class="cwc-single-post__meta">';
			if ( d.date ) {
				html += '<span class="cwc-single-post__meta-item">'
					+ '<img class="cwc-single-post__meta-icon" src="' + d.themeUri + '/assets/images/published-date-icon.svg" alt="" aria-hidden="true">'
					+ d.date
					+ '</span>';
			}
			if ( d.readTime ) {
				html += '<span class="cwc-single-post__meta-item">'
					+ '<img class="cwc-single-post__meta-icon" src="' + d.themeUri + '/assets/images/eye-read.svg" alt="" aria-hidden="true">'
					+ d.readTime
					+ '</span>';
			}
			html += '</div>';

			hero.innerHTML = html;
		}

		/* ── 2. Scrollspy ──────────────────────────────────────────── */

		const contentBox = document.querySelector( '.cwc-single-post__content-box' );
		const tocLinks   = document.querySelectorAll( '.cwc-toc__link' );
		const tocDot     = document.querySelector( '.cwc-toc__dot' );
		const tocRail    = document.querySelector( '.cwc-toc__rail' );
		const tocTitle   = document.querySelector( '.cwc-toc__title' );

		if ( ! contentBox || tocLinks.length === 0 ) return;

		const headings     = contentBox.querySelectorAll( 'h2, h3' );
		const stickyOffset = 160;

		/* Assign IDs to headings so the TOC href anchors resolve. */
		tocLinks.forEach( function ( link, index ) {
			var anchor = link.getAttribute( 'href' ).substring( 1 );
			if ( headings[ index ] ) {
				headings[ index ].id = anchor;
			}
		} );

		function onScroll() {
			var currentId  = '';
			var scrollPos  = window.scrollY + stickyOffset;

			headings.forEach( function ( h ) {
				if ( h.getBoundingClientRect().top + window.scrollY <= scrollPos ) {
					currentId = h.id;
					h.classList.add( 'passed' );
				} else {
					h.classList.remove( 'passed' );
				}
			} );

			var activeIdx = -1;
			tocLinks.forEach( function ( link, i ) {
				link.classList.remove( 'active', 'passed' );
				if ( link.getAttribute( 'href' ) === '#' + currentId ) {
					link.classList.add( 'active' );
					activeIdx = i;
				}
			} );

			/* Title highlight */
			if ( tocTitle ) {
				tocTitle.style.color = activeIdx !== -1 ? '#96E5FF' : '';
			}

			/* Mark all preceding items as "passed" and move the dot */
			if ( activeIdx !== -1 ) {
				for ( var i = 0; i <= activeIdx; i++ ) {
					tocLinks[ i ].classList.add( 'passed' );
				}

				var active = tocLinks[ activeIdx ];
				if ( active && tocDot && tocRail ) {
					var railRect = tocRail.getBoundingClientRect();
					var linkRect = active.getBoundingClientRect();
					var dotTop   = ( linkRect.top - railRect.top ) + ( linkRect.height / 2 );
					tocDot.style.top     = dotTop + 'px';
					tocDot.style.opacity = '1';
				}
			} else if ( tocDot ) {
				tocDot.style.opacity = '0';
			}

			/* Progress rail */
			var progress = document.querySelector( '.cwc-toc__rail-progress' );
			if ( progress && contentBox ) {
				var contentRect = contentBox.getBoundingClientRect();
				var total       = contentBox.scrollHeight;
				var scrollOff   = window.scrollY + ( window.innerHeight / 2 );
				var contentTop  = contentRect.top + window.scrollY;
				var pct         = ( scrollOff - contentTop ) / total;
				pct = Math.max( 0, Math.min( 1, pct ) );
				progress.style.height = ( pct * 100 ) + '%';
			}
		}

		/* ── 3. Smooth scroll on TOC click ────────────────────────── */

		tocLinks.forEach( function ( link ) {
			link.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				var id     = link.getAttribute( 'href' ).substring( 1 );
				var target = document.getElementById( id );
				if ( ! target ) return;

				var headerH  = 140;
				var elemPos  = target.getBoundingClientRect().top;
				var scrollTo = elemPos + window.pageYOffset - headerH;

				window.scrollTo( { top: scrollTo, behavior: 'smooth' } );
			} );
		} );

		/* Bind scroll handler */
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();

		/* ── Rail glow while scrolling ────────────────────────────── */
		var scrollTimer;
		window.addEventListener( 'scroll', function () {
			if ( tocRail ) {
				tocRail.classList.add( 'is-scrolling' );
				clearTimeout( scrollTimer );
				scrollTimer = setTimeout( function () {
					tocRail.classList.remove( 'is-scrolling' );
				}, 300 );
			}
		}, { passive: true } );

	} );
} )();
