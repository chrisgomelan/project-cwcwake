/**
 * CWC Wake — Global Header Search
 *
 * Handles live search with recommendations, FAQ-based suggestions,
 * and intent-based smart fallback so a "no results" state never appears.
 */

( ( $ ) => {
	'use strict';

	const CWC_RECENT_SEARCH_KEY  = 'cwc_header_recent_searches_v1';
	const CWC_RECENT_SEARCH_MAX  = 8;
	const CWC_RECENT_QUERY_MAX   = 120;

	const cwcRecentSearches = {
		get() {
			try {
				const raw = localStorage.getItem( CWC_RECENT_SEARCH_KEY );
				if ( ! raw ) {
					return [];
				}
				const parsed = JSON.parse( raw );
				if ( ! Array.isArray( parsed ) ) {
					return [];
				}
				return parsed
					.filter( ( s ) => typeof s === 'string' && s.trim().length >= 2 )
					.map( ( s ) => s.trim().substring( 0, CWC_RECENT_QUERY_MAX ) );
			} catch ( err ) {
				return [];
			}
		},

		save( query ) {
			let q = String( query || '' ).trim();
			if ( q.length < 2 ) {
				return;
			}
			if ( q.length > CWC_RECENT_QUERY_MAX ) {
				q = q.substring( 0, CWC_RECENT_QUERY_MAX );
			}
			let list = this.get();
			const lower = q.toLowerCase();
			list = list.filter( ( item ) => item.toLowerCase() !== lower );
			list.unshift( q );
			list = list.slice( 0, CWC_RECENT_SEARCH_MAX );
			try {
				localStorage.setItem( CWC_RECENT_SEARCH_KEY, JSON.stringify( list ) );
			} catch ( err ) {
				/* quota / private mode */
			}
		},

		clear() {
			try {
				localStorage.removeItem( CWC_RECENT_SEARCH_KEY );
			} catch ( err ) {
				/* ignore */
			}
		},

		/**
		 * @param {JQuery} $parent Results container.
		 * @param {{ onClear: () => void, onPick: (q: string) => void }} handlers
		 */
		renderSection( $parent, handlers ) {
			const recent = this.get();
			if ( ! recent.length ) {
				return;
			}
			const $section = $( '<div class="cwc-header__search-section cwc-header__search-section--recent"></div>' );
			const $header  = $( '<div class="cwc-header__search-section-header"></div>' );
			$header.append( '<span class="cwc-header__search-section-label">Recent searches</span>' );
			const $clearBtn = $( '<button type="button" class="cwc-header__search-recent-clear"></button>' ).text( 'Clear' );
			$clearBtn.on( 'click', ( e ) => {
				e.preventDefault();
				e.stopPropagation();
				this.clear();
				handlers.onClear();
			} );
			$header.append( $clearBtn );
			$section.append( $header );

			const $list = $( '<div class="cwc-header__search-recommendations"></div>' );
			recent.forEach( ( text ) => {
				const $btn = $( '<button type="button" class="cwc-header__search-rec-item"></button>' );
				$btn.text( text );
				$btn.on( 'click', ( e ) => {
					e.preventDefault();
					handlers.onPick( text );
				} );
				$list.append( $btn );
			} );
			$section.append( $list );
			$parent.append( $section );
		}
	};

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
						cwcRecentSearches.save( query );
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

			const hasResults = ( data.faqs && data.faqs.length )
				|| ( data.posts && data.posts.length )
				|| ( data.accommodations && data.accommodations.length );

			const hasSuggestions = data.suggestions && data.suggestions.length;

			// Show intent hint banner when the backend resolved user intent.
			if ( data.intent_hint ) {
				$results.append( `
					<div class="cwc-header__search-intent-hint">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
						<span>${data.intent_hint}</span>
					</div>
				` );
			}

			// 1. FAQs (Answers to questions)
			if ( data.faqs && data.faqs.length ) {
				const $section = $( '<div class="cwc-header__search-section"></div>' );
				$section.append( '<div class="cwc-header__search-section-label">Suggested Answers</div>' );

				data.faqs.forEach( faq => {
					$section.append( createItemHtml( faq, 'faq' ) );
				} );

				$results.append( $section );
			}

			// 2. Accommodations
			if ( data.accommodations && data.accommodations.length ) {
				const $section = $( '<div class="cwc-header__search-section"></div>' );
				$section.append( '<div class="cwc-header__search-section-label">Accommodations</div>' );

				data.accommodations.forEach( acc => {
					$section.append( createItemHtml( acc, 'accommodation' ) );
				} );

				$results.append( $section );
			}

			// 3. Posts / Pages
			if ( data.posts && data.posts.length ) {
				const $section = $( '<div class="cwc-header__search-section"></div>' );
				$section.append( '<div class="cwc-header__search-section-label">Other Content</div>' );

				data.posts.forEach( post => {
					$section.append( createItemHtml( post, post.type ) );
				} );

				$results.append( $section );
			}

			// 4. Curated page suggestions (from intent mapping or fallback)
			if ( hasSuggestions ) {
				const $section = $( '<div class="cwc-header__search-section cwc-header__search-section--suggestions"></div>' );
				$section.append( '<div class="cwc-header__search-section-label">Explore These Pages</div>' );

				const $grid = $( '<div class="cwc-header__search-suggestions-grid"></div>' );
				data.suggestions.forEach( item => {
					$grid.append( `
						<a href="${item.url}" class="cwc-header__search-suggestion-card">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
							<span>${item.title}</span>
						</a>
					` );
				} );

				$section.append( $grid );
				$results.append( $section );
			}

			// 5. If absolutely nothing to show (shouldn't happen now)
			if ( ! hasResults && ! hasSuggestions && ! data.intent_hint ) {
				const $msg = $( '<div class="cwc-header__search-status"></div>' );
				$msg.text( 'No results found for "' + query + '".' );
				$results.html( $msg );
			}
		};

		const renderRecommendations = ( data ) => {
			$results.empty();

			cwcRecentSearches.renderSection( $results, {
				onClear: () => loadRecommendations(),
				onPick: ( text ) => {
					$input.val( text ).trigger( 'input' );
				}
			} );

			const recs = data.recommendations || [];
			const $section = $( '<div class="cwc-header__search-section"></div>' );
			$section.append( '<div class="cwc-header__search-section-label">Popular Searches</div>' );

			const $recList = $( '<div class="cwc-header__search-recommendations"></div>' );
			recs.forEach( ( rec ) => {
				const $item = $( '<button type="button" class="cwc-header__search-rec-item"></button>' ).text( rec );
				$item.on( 'click', ( e ) => {
					e.preventDefault();
					$input.val( rec ).trigger( 'input' );
				} );
				$recList.append( $item );
			} );

			$section.append( $recList );
			$results.append( $section );

			// Also show some top FAQs or Rooms as suggestions
			const suggestions = data.suggestions || [];
			if ( suggestions.length ) {
				const $sSection = $( '<div class="cwc-header__search-section"></div>' );
				$sSection.append( '<div class="cwc-header__search-section-label">Recommended for you</div>' );

				suggestions.forEach( item => {
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

	/* ══════════════════════════════════════════════════════════════
	   404 Page — Inline Search (reuses same AJAX + rendering)
	   ══════════════════════════════════════════════════════════════ */

	$( document ).ready( () => {
		const $widget  = $( '[data-cwc-404-search]' );
		if ( ! $widget.length ) return;

		const $input   = $widget.find( '.cwc-404__search-input' );
		const $results = $widget.find( '.cwc-404__search-results' );
		const $clear   = $widget.find( '.cwc-404__search-clear' );
		const ajaxUrl  = window.cwcVars?.ajaxUrl || '/wp-admin/admin-ajax.php';
		let timer;

		/* ── Input handling ─────────────────────────────────────── */

		$input.on( 'input', () => {
			const q = $input.val().trim();
			$clear.toggleClass( 'is-visible', q.length > 0 );
			clearTimeout( timer );

			if ( q.length < 2 ) {
				if ( q.length === 0 ) loadRecs();
				else $results.empty();
				return;
			}

			timer = setTimeout( () => doSearch( q ), 300 );
		} );

		$clear.on( 'click', () => {
			$input.val( '' ).focus();
			$clear.removeClass( 'is-visible' );
			loadRecs();
		} );

		/* ── AJAX ───────────────────────────────────────────────── */

		const doSearch = ( q ) => {
			$results.html( spinnerHtml( 'Searching...' ) );

			$.ajax( {
				url: ajaxUrl,
				type: 'GET',
				data: { action: 'cwc_global_search', q },
				success: ( r ) => {
					if ( r.success ) {
						cwcRecentSearches.save( q );
						render404Results( r.data, q );
					} else {
						$results.html( statusHtml( 'Something went wrong.' ) );
					}
				},
				error: () => $results.html( statusHtml( 'Connection error.' ) )
			} );
		};

		const loadRecs = () => {
			$results.html( spinnerHtml( 'Loading suggestions...' ) );

			$.ajax( {
				url: ajaxUrl,
				type: 'GET',
				data: { action: 'cwc_search_recommendations' },
				success: ( r ) => {
					if ( r.success ) render404Recs( r.data );
				}
			} );
		};

		/* ── Rendering ──────────────────────────────────────────── */

		const render404Results = ( data, query ) => {
			$results.empty();

			const hasResults = ( data.faqs?.length ) || ( data.posts?.length ) || ( data.accommodations?.length );
			const hasSuggestions = data.suggestions?.length;

			if ( data.intent_hint ) {
				$results.append( `
					<div class="cwc-header__search-intent-hint">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
						<span>${data.intent_hint}</span>
					</div>
				` );
			}

			if ( data.faqs?.length ) {
				$results.append( sectionHtml( 'Suggested Answers', data.faqs, 'faq' ) );
			}
			if ( data.accommodations?.length ) {
				$results.append( sectionHtml( 'Accommodations', data.accommodations, 'accommodation' ) );
			}
			if ( data.posts?.length ) {
				$results.append( sectionHtml( 'Pages & Posts', data.posts ) );
			}

			if ( hasSuggestions ) {
				const $sec = $( '<div class="cwc-header__search-section cwc-header__search-section--suggestions"></div>' );
				$sec.append( '<div class="cwc-header__search-section-label">Explore These Pages</div>' );
				const $grid = $( '<div class="cwc-header__search-suggestions-grid"></div>' );
				data.suggestions.forEach( s => {
					$grid.append( `
						<a href="${s.url}" class="cwc-header__search-suggestion-card">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
							<span>${s.title}</span>
						</a>
					` );
				} );
				$sec.append( $grid );
				$results.append( $sec );
			}

			if ( ! hasResults && ! hasSuggestions && ! data.intent_hint ) {
				const $msg = $( '<div class="cwc-header__search-status"></div>' );
				$msg.text( 'No results found for "' + query + '".' );
				$results.html( $msg );
			}
		};

		const render404Recs = ( data ) => {
			$results.empty();

			cwcRecentSearches.renderSection( $results, {
				onClear: () => loadRecs(),
				onPick: ( text ) => {
					$input.val( text ).trigger( 'input' );
				}
			} );

			const $sec = $( '<div class="cwc-header__search-section"></div>' );
			$sec.append( '<div class="cwc-header__search-section-label">Popular Searches</div>' );

			const $list = $( '<div class="cwc-header__search-recommendations"></div>' );
			( data.recommendations || [] ).forEach( ( rec ) => {
				const $item = $( '<button type="button" class="cwc-header__search-rec-item"></button>' ).text( rec );
				$item.on( 'click', ( e ) => {
					e.preventDefault();
					$input.val( rec ).trigger( 'input' );
				} );
				$list.append( $item );
			} );
			$sec.append( $list );
			$results.append( $sec );

			if ( data.suggestions?.length ) {
				const $sSec = $( '<div class="cwc-header__search-section"></div>' );
				$sSec.append( '<div class="cwc-header__search-section-label">Recommended for you</div>' );
				data.suggestions.forEach( item => {
					$sSec.append( itemHtml( item, item.type ) );
				} );
				$results.append( $sSec );
			}
		};

		/* ── Shared helpers ─────────────────────────────────────── */

		const sectionHtml = ( label, items, forceType ) => {
			const $sec = $( '<div class="cwc-header__search-section"></div>' );
			$sec.append( `<div class="cwc-header__search-section-label">${label}</div>` );
			items.forEach( i => $sec.append( itemHtml( i, forceType || i.type ) ) );
			return $sec;
		};

		const itemHtml = ( item, type ) => {
			const icons = {
				faq:           '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
				accommodation: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>'
			};
			const icon = icons[ type ] || '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>';

			return `
				<a href="${item.url}" class="cwc-header__search-item">
					<div class="cwc-header__search-item-icon">${icon}</div>
					<div class="cwc-header__search-item-info">
						<span class="cwc-header__search-item-title">${item.title}</span>
						<span class="cwc-header__search-item-type">${(type || '').toUpperCase()}</span>
						${item.excerpt ? `<span class="cwc-header__search-item-excerpt">${item.excerpt}</span>` : ''}
					</div>
				</a>
			`;
		};

		const spinnerHtml = ( text ) => `
			<div class="cwc-header__search-status">
				<div class="cwc-header__search-loading">
					<span class="cwc-header__search-spinner"></span>
					${text}
				</div>
			</div>
		`;

		const statusHtml = ( text ) => `<div class="cwc-header__search-status">${text}</div>`;

		/* ── Auto-load recommendations on page load ──────────────── */
		loadRecs();
	} );

} )( jQuery );
