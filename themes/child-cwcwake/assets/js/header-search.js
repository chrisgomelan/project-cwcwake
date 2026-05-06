/**
 * CWC Wake — Global Header Search
 * 
 * Handles live search with recommendations and FAQ-based suggestions.
 */

( ( $ ) => {
	'use strict';

	$( document ).ready( () => {
		const $container = $( '.cwc-header__search-container' );
		const $toggle    = $( '[data-cwc-search-toggle]' );
		const $input     = $( '.cwc-header__search-input' );
		const $results   = $( '.cwc-header__search-results' );
		const $clear     = $( '.cwc-header__search-clear' );

		if ( ! $toggle.length ) return;

		let searchTimeout;
		const ajaxUrl = window.cwcVars?.ajaxUrl || '/wp-admin/admin-ajax.php';

		/* ── 1. Toggle Search Panel ────────────────────────────────── */

		$toggle.on( 'click', ( e ) => {
			e.preventDefault();
			const isActive = $container.hasClass( 'is-active' );

			if ( ! isActive ) {
				$container.addClass( 'is-active' );
				setTimeout( () => $input.focus(), 100 );
				
				// Load recommendations if input is empty
				if ( ! $input.val().trim() ) {
					loadRecommendations();
				}
			} else {
				closeSearch();
			}
		} );

		const closeSearch = () => {
			$container.removeClass( 'is-active' );
		};

		// Close on click outside
		$( document ).on( 'click', ( e ) => {
			if ( ! $container.is( e.target ) && $container.has( e.target ).length === 0 ) {
				closeSearch();
			}
		} );

		// Close on Escape
		$( document ).on( 'keydown', ( e ) => {
			if ( e.key === 'Escape' ) closeSearch();
		} );

		/* ── 2. Search Input Handling ──────────────────────────────── */

		$input.on( 'input', () => {
			const query = $input.val().trim();
			
			$clear.toggleClass( 'is-visible', query.length > 0 );

			clearTimeout( searchTimeout );

			if ( query.length < 2 ) {
				if ( query.length === 0 ) {
					loadRecommendations();
				} else {
					$results.empty();
				}
				return;
			}

			searchTimeout = setTimeout( () => {
				performSearch( query );
			}, 300 );
		} );

		$clear.on( 'click', () => {
			$input.val( '' ).focus();
			$clear.removeClass( 'is-visible' );
			loadRecommendations();
		} );

		/* ── 3. AJAX Functions ─────────────────────────────────────── */

		const performSearch = ( query ) => {
			$results.html( `
				<div class="cwc-header__search-status">
					<div class="cwc-header__search-loading">
						<span class="cwc-header__search-spinner"></span>
						Searching...
					</div>
				</div>
			` );

			$.ajax( {
				url: ajaxUrl,
				type: 'GET',
				data: {
					action: 'cwc_global_search',
					q: query,
					nonce: window.cwcVars?.nonce
				},
				success: ( response ) => {
					if ( response.success ) {
						renderResults( response.data, query );
					} else {
						$results.html( '<div class="cwc-header__search-status">Something went wrong.</div>' );
					}
				},
				error: () => {
					$results.html( '<div class="cwc-header__search-status">Connection error.</div>' );
				}
			} );
		};

		const loadRecommendations = () => {
			$results.html( `
				<div class="cwc-header__search-status">
					<div class="cwc-header__search-loading">
						<span class="cwc-header__search-spinner"></span>
						Loading suggestions...
					</div>
				</div>
			` );

			$.ajax( {
				url: ajaxUrl,
				type: 'GET',
				data: {
					action: 'cwc_search_recommendations',
					nonce: window.cwcVars?.nonce
				},
				success: ( response ) => {
					if ( response.success ) {
						renderRecommendations( response.data );
					}
				}
			} );
		};

		/* ── 4. Rendering ──────────────────────────────────────────── */

		const renderResults = ( data, query ) => {
			$results.empty();

			if ( ! data.faqs.length && ! data.posts.length && ! data.accommodations.length ) {
				$results.html( '<div class="cwc-header__search-status">No results found for "' + query + '".</div>' );
				return;
			}

			// 1. FAQs (Answers to questions)
			if ( data.faqs.length ) {
				const $section = $( '<div class="cwc-header__search-section"></div>' );
				$section.append( '<div class="cwc-header__search-section-label">Suggested Answers</div>' );
				
				data.faqs.forEach( faq => {
					$section.append( createItemHtml( faq, 'faq' ) );
				} );
				
				$results.append( $section );
			}

			// 2. Accommodations
			if ( data.accommodations.length ) {
				const $section = $( '<div class="cwc-header__search-section"></div>' );
				$section.append( '<div class="cwc-header__search-section-label">Accommodations</div>' );
				
				data.accommodations.forEach( acc => {
					$section.append( createItemHtml( acc, 'accommodation' ) );
				} );
				
				$results.append( $section );
			}

			// 3. Posts / Pages
			if ( data.posts.length ) {
				const $section = $( '<div class="cwc-header__search-section"></div>' );
				$section.append( '<div class="cwc-header__search-section-label">Other Content</div>' );
				
				data.posts.forEach( post => {
					$section.append( createItemHtml( post, post.type ) );
				} );
				
				$results.append( $section );
			}
		};

		const renderRecommendations = ( data ) => {
			$results.empty();
			
			const $section = $( '<div class="cwc-header__search-section"></div>' );
			$section.append( '<div class="cwc-header__search-section-label">Popular Searches</div>' );
			
			const $recList = $( '<div class="cwc-header__search-recommendations"></div>' );
			data.recommendations.forEach( rec => {
				const $item = $( `<a href="#" class="cwc-header__search-rec-item">${rec}</a>` );
				$item.on( 'click', ( e ) => {
					e.preventDefault();
					$input.val( rec ).trigger( 'input' );
				} );
				$recList.append( $item );
			} );

			$section.append( $recList );
			$results.append( $section );

			// Also show some top FAQs or Rooms as suggestions
			if ( data.suggestions.length ) {
				const $sSection = $( '<div class="cwc-header__search-section"></div>' );
				$sSection.append( '<div class="cwc-header__search-section-label">Recommended for you</div>' );
				
				data.suggestions.forEach( item => {
					$sSection.append( createItemHtml( item, item.type ) );
				} );
				
				$results.append( $sSection );
			}
		};

		const createItemHtml = ( item, type ) => {
			let iconSvg = '';
			
			if ( type === 'faq' ) {
				iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`;
			} else if ( type === 'accommodation' ) {
				iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>`;
			} else {
				// Default page/post icon
				iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>`;
			}

			return `
				<a href="${item.url}" class="cwc-header__search-item">
					<div class="cwc-header__search-item-icon">${iconSvg}</div>
					<div class="cwc-header__search-item-info">
						<span class="cwc-header__search-item-title">${item.title}</span>
						<span class="cwc-header__search-item-type">${type.toUpperCase()}</span>
						${item.excerpt ? `<span class="cwc-header__search-item-excerpt">${item.excerpt}</span>` : ''}
					</div>
				</a>
			`;
		};
	} );

} )( jQuery );
