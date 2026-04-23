/**
 * CWC Wake — Search Overlay Logic
 *
 * Implements a "Hybrid priority" search system:
 * 1. Semantic Search (requires API Key on server)
 * 2. Local Smart Search (Vanilla Fuzzy Matching as fallback)
 */
(() => {
	const body = document.body;
	const toggle = document.querySelector('.cwc-header__search-toggle');
	if (!toggle) return;

	// Injected via cwc-search-overlay-html
	let overlay, input, resultsList, statusText, closeBtn;
	let isAiEnabled = false;
	let suggestionsData = [];

	const init = () => {
		overlay = document.querySelector('.cwc-search-overlay');
		if (!overlay) return;

		input = overlay.querySelector('.cwc-search-overlay__input');
		resultsList = overlay.querySelector('.cwc-search-overlay__results');
		statusText = overlay.querySelector('.cwc-search-overlay__status');
		closeBtn = overlay.querySelector('.cwc-search-overlay__close');

		// Check if AI is available (passed from WordPress via wp_localize_script)
		isAiEnabled = typeof cwcSearchConfig !== 'undefined' && cwcSearchConfig.hasAi;

		toggle.addEventListener('click', openSearch);
		closeBtn.addEventListener('click', closeSearch);
		input.addEventListener('input', debounce(handleSearch, 300));
		
		// Show suggestions on focus if empty
		input.addEventListener('focus', () => {
			if (input.value.trim().length === 0) renderSuggestions();
		});

		// Close on Escape
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && overlay.classList.contains('is-active')) closeSearch();
		});

		// Pre-fetch recommendations
		fetchSuggestions();
	};

	const openSearch = (e) => {
		e.preventDefault();
		overlay.classList.add('is-active');
		body.style.overflow = 'hidden';
		
		// Clear and show recommendations on open
		if (input.value.trim().length === 0) {
			renderSuggestions();
		}

		setTimeout(() => input.focus(), 100);
	};

	const closeSearch = () => {
		overlay.classList.remove('is-active');
		body.style.overflow = '';
		input.value = '';
		resultsList.innerHTML = '';
		updateStatus('');
	};

	const fetchLocalSearchData = async () => {
		try {
			const res = await fetch('/wp-json/cwc/v1/search-data');
			if (res.ok) {
				searchData = await res.json();
			}
		} catch (err) {
			console.warn('Search data fetch failed, using runtime fallback.');
		}
	};

	const fetchSuggestions = async () => {
		try {
			const res = await fetch('/wp-json/cwc/v1/search-suggestions');
			if (res.ok) {
				suggestionsData = await res.json();
			}
		} catch (err) {
			console.warn('Suggestions fetch failed.');
		}
	};

	const renderSuggestions = () => {
		if (suggestionsData.length === 0) return;
		updateStatus('Popular Recommendations');
		renderResults(suggestionsData, true);
	};

	const handleSearch = async () => {
		const query = input.value.trim().toLowerCase();
		if (query.length === 0) {
			renderSuggestions();
			return;
		}
		if (query.length < 2) {
			resultsList.innerHTML = '';
			updateStatus('');
			return;
		}

		updateStatus('Thinking...');
		await performSemanticSearch(query);
	};

	const performSemanticSearch = async (query) => {
		try {
			const res = await fetch('/wp-json/cwc/v1/semantic-search', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ query })
			});
			if (res.ok) {
				const results = await res.json();
				renderResults(results);
				updateStatus('AI Results');
			} else {
				throw new Error();
			}
		} catch (err) {
			updateStatus('Search unavailable');
			resultsList.innerHTML = '<div style="color:white; grid-column: 1/-1; text-align:center;">Could not reach AI engine.</div>';
		}
	};

	const renderResults = (results, isSuggestion = false) => {
		if (results.length === 0) {
			resultsList.innerHTML = '<div style="color:white; grid-column: 1/-1; text-align:center;">No results found.</div>';
			return;
		}

		const prefix = isSuggestion ? '<div class="cwc-search-overlay__suggestion-header">Suggested for you:</div>' : '';

		resultsList.innerHTML = prefix + results.map(item => `
			<a href="${item.url}" class="cwc-search-result">
				<span class="cwc-search-result__type">${item.type}</span>
				<h3 class="cwc-search-result__title">${item.title}</h3>
				<p class="cwc-search-result__excerpt">${item.excerpt}</p>
			</a>
		`).join('');
	};

	const updateStatus = (text) => {
		statusText.textContent = text;
	};

	const debounce = (func, wait) => {
		let timeout;
		return (...args) => {
			clearTimeout(timeout);
			timeout = setTimeout(() => func.apply(this, args), wait);
		};
	};

	// Start when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
