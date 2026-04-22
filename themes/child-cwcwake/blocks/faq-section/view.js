/*
 * CWC Wake — FAQ Section block (view script)
 *
 * Handles:
 *   1. Category tab switching.
 *   2. Accordion open/close with max-height animation.
 *   3. Live search filtering across all categories.
 */

( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		const root = document.querySelector( '[data-cwc-faq]' );
		if ( ! root ) return;

		const tabs       = root.querySelectorAll( '[data-cwc-faq-tab]' );
		const panels     = root.querySelectorAll( '[data-cwc-faq-category]' );
		const searchBox  = root.querySelector( '[data-cwc-faq-search]' );
		const noResults  = root.querySelector( '[data-cwc-faq-no-results]' );

		/* ── 1. Tab switching ──────────────────────────────────────── */

		function switchTab( slug ) {
			tabs.forEach( function ( t ) {
				t.classList.toggle( 'is-active', t.dataset.cwcFaqTab === slug );
			} );
			panels.forEach( function ( p ) {
				p.classList.toggle( 'is-active', p.dataset.cwcFaqCategory === slug );
			} );

			/* Collapse all open accordions when switching */
			root.querySelectorAll( '.cwc-faq__item.is-open' ).forEach( function ( item ) {
				closeItem( item );
			} );
		}

		tabs.forEach( function ( tab ) {
			tab.addEventListener( 'click', function () {
				/* Clear search when switching tabs */
				if ( searchBox ) {
					searchBox.value = '';
					resetSearch();
				}
				switchTab( tab.dataset.cwcFaqTab );
			} );
		} );

		/* ── 2. Accordion ──────────────────────────────────────────── */

		function openItem( item ) {
			item.classList.add( 'is-open' );
			var btn    = item.querySelector( '.cwc-faq__question' );
			var answer = item.querySelector( '.cwc-faq__answer' );
			if ( btn ) btn.setAttribute( 'aria-expanded', 'true' );
			if ( answer ) {
				/* Measure the natural height for smooth animation */
				answer.style.maxHeight = answer.scrollHeight + 'px';
			}
		}

		function closeItem( item ) {
			item.classList.remove( 'is-open' );
			var btn    = item.querySelector( '.cwc-faq__question' );
			var answer = item.querySelector( '.cwc-faq__answer' );
			if ( btn ) btn.setAttribute( 'aria-expanded', 'false' );
			if ( answer ) answer.style.maxHeight = '';
		}

		root.addEventListener( 'click', function ( e ) {
			var btn = e.target.closest( '.cwc-faq__question' );
			if ( ! btn ) return;

			var item = btn.closest( '.cwc-faq__item' );
			if ( ! item ) return;

			if ( item.classList.contains( 'is-open' ) ) {
				closeItem( item );
			} else {
				/* Close siblings in the same category first */
				var siblings = item.parentNode.querySelectorAll( '.cwc-faq__item.is-open' );
				siblings.forEach( function ( sib ) { closeItem( sib ); } );
				openItem( item );
			}
		} );

		/* ── 3. Live search ────────────────────────────────────────── */

		function resetSearch() {
			panels.forEach( function ( p ) {
				p.querySelectorAll( '.cwc-faq__item' ).forEach( function ( item ) {
					item.hidden = false;
				} );
			} );
			if ( noResults ) noResults.hidden = true;

			/* Restore normal tab view */
			var hasActive = false;
			panels.forEach( function ( p ) {
				if ( p.classList.contains( 'is-active' ) ) hasActive = true;
			} );
			if ( ! hasActive && panels.length ) {
				panels[0].classList.add( 'is-active' );
				if ( tabs.length ) tabs[0].classList.add( 'is-active' );
			}
		}

		if ( searchBox ) {
			var debounceTimer;

			searchBox.addEventListener( 'input', function () {
				clearTimeout( debounceTimer );
				debounceTimer = setTimeout( function () {
					var query = searchBox.value.trim().toLowerCase();

					if ( ! query ) {
						resetSearch();
						return;
					}

					var totalVisible = 0;

					/* Show ALL category panels during a search so results from
					   every category are visible simultaneously. */
					panels.forEach( function ( panel ) {
						panel.classList.add( 'is-active' );
						var items = panel.querySelectorAll( '.cwc-faq__item' );
						var panelHasMatch = false;

						items.forEach( function ( item ) {
							var qText = item.querySelector( '.cwc-faq__question-text' );
							var aText = item.querySelector( '.cwc-faq__answer-inner' );
							var text  = '';
							if ( qText ) text += qText.textContent.toLowerCase();
							if ( aText ) text += ' ' + aText.textContent.toLowerCase();

							if ( text.indexOf( query ) !== -1 ) {
								item.hidden = false;
								panelHasMatch = true;
								totalVisible++;
							} else {
								item.hidden = true;
								closeItem( item );
							}
						} );

						if ( ! panelHasMatch ) {
							panel.classList.remove( 'is-active' );
						}
					} );

					/* Deactivate tab highlights during search */
					tabs.forEach( function ( t ) { t.classList.remove( 'is-active' ); } );

					if ( noResults ) {
						noResults.hidden = totalVisible > 0;
					}
				}, 200 );
			} );
		}
	} );
} )();
