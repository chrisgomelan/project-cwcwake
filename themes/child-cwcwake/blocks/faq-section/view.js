/*
 * CWC Wake — FAQ Section block (view script)
 *
 * Handles:
 *   1. Category tab switching.
 *   2. Accordion open/close with max-height animation.
 *   3. Live search filtering across all categories.
 */

( () => {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', () => {
		const root = document.querySelector( '[data-cwc-faq]' );
		if ( ! root ) return;

		const tabs       = root.querySelectorAll( '[data-cwc-faq-tab]' );
		const panels     = root.querySelectorAll( '[data-cwc-faq-category]' );
		const searchBox  = root.querySelector( '[data-cwc-faq-search]' );
		const noResults  = root.querySelector( '[data-cwc-faq-no-results]' );

		/* ── 1. Accordion Helpers ──────────────────────────────────── */

		const openItem = ( item ) => {
			item.classList.add( 'is-open' );
			const btn    = item.querySelector( '.cwc-faq__question' );
			const answer = item.querySelector( '.cwc-faq__answer' );
			
			if ( btn ) btn.setAttribute( 'aria-expanded', 'true' );
			if ( answer ) {
				/* Measure the natural height for smooth animation */
				answer.style.maxHeight = `${answer.scrollHeight}px`;
			}
		};

		const closeItem = ( item ) => {
			item.classList.remove( 'is-open' );
			const btn    = item.querySelector( '.cwc-faq__question' );
			const answer = item.querySelector( '.cwc-faq__answer' );
			
			if ( btn ) btn.setAttribute( 'aria-expanded', 'false' );
			if ( answer ) answer.style.maxHeight = '';
		};

		/* ── 2. Tab switching ──────────────────────────────────────── */

		const switchTab = ( slug ) => {
			tabs.forEach( t => t.classList.toggle( 'is-active', t.dataset.cwcFaqTab === slug ) );
			panels.forEach( p => p.classList.toggle( 'is-active', p.dataset.cwcFaqCategory === slug ) );

			/* Collapse all open accordions when switching */
			root.querySelectorAll( '.cwc-faq__item.is-open' ).forEach( item => closeItem( item ) );
		};

		tabs.forEach( tab => {
			tab.addEventListener( 'click', () => {
				/* Clear search when switching tabs */
				if ( searchBox && searchBox.value ) {
					searchBox.value = '';
					resetSearch();
				}
				switchTab( tab.dataset.cwcFaqTab );
			} );
		} );

		/* ── 3. Accordion Toggle ───────────────────────────────────── */

		root.addEventListener( 'click', ( e ) => {
			const btn = e.target.closest( '.cwc-faq__question' );
			if ( ! btn ) return;

			const item = btn.closest( '.cwc-faq__item' );
			if ( ! item ) return;

			if ( item.classList.contains( 'is-open' ) ) {
				closeItem( item );
			} else {
				/* Close siblings in the same category first */
				const siblings = item.parentNode.querySelectorAll( '.cwc-faq__item.is-open' );
				siblings.forEach( sib => closeItem( sib ) );
				openItem( item );
			}
		} );

		/* ── 4. Live search ────────────────────────────────────────── */

		const resetSearch = () => {
			panels.forEach( p => {
				p.querySelectorAll( '.cwc-faq__item' ).forEach( item => {
					item.hidden = false;
				} );
			} );
			
			if ( noResults ) noResults.hidden = true;

			/* Restore normal tab view */
			let hasActive = false;
			panels.forEach( p => {
				if ( p.classList.contains( 'is-active' ) ) hasActive = true;
			} );

			if ( ! hasActive && panels.length ) {
				panels[0].classList.add( 'is-active' );
				if ( tabs.length ) tabs[0].classList.add( 'is-active' );
			}
		};

		if ( searchBox ) {
			let debounceTimer;

			searchBox.addEventListener( 'input', () => {
				clearTimeout( debounceTimer );
				debounceTimer = setTimeout( () => {
					const query = searchBox.value.trim().toLowerCase();

					if ( ! query ) {
						resetSearch();
						return;
					}

					let totalVisible = 0;

					/* Show ALL category panels during a search so results from
					   every category are visible simultaneously. */
					panels.forEach( panel => {
						panel.classList.add( 'is-active' );
						const items = panel.querySelectorAll( '.cwc-faq__item' );
						let panelHasMatch = false;

						items.forEach( item => {
							const qText = item.querySelector( '.cwc-faq__question-text' );
							const aText = item.querySelector( '.cwc-faq__answer-inner' );
							let text  = '';
							if ( qText ) text += qText.textContent.toLowerCase();
							if ( aText ) text += ` ${aText.textContent.toLowerCase()}`;

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
					tabs.forEach( t => t.classList.remove( 'is-active' ) );

					if ( noResults ) {
						noResults.hidden = totalVisible > 0;
					}
				}, 200 );
			} );
		}

		/* Initial run — ensure a tab is selected */
		resetSearch();
	} );
} )();
