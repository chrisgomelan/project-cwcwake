/**
 * CWC Wake — All Blogs block view script.
 *
 * Implements AJAX pagination and a custom dropdown filter component
 * to match premium design specifications (rounded menus, blue dots, etc).
 *
 * @since 1.0.0
 */

(function() {
	/**
	 * Initialize the AJAX and custom dropdown functionality.
	 */
	const init = () => {
		const blocks = document.querySelectorAll('.cwc-all-blogs');

		blocks.forEach((block) => {
			const target = block.querySelector('.js-all-blogs-target');
			const filter = block.querySelector('.js-all-blogs-filter');

			if (!target || !filter) return;

			const trigger = filter.querySelector('.js-filter-trigger');
			const dropdown = filter.querySelector('.js-filter-dropdown');
			const options = filter.querySelectorAll('.cwc-all-blogs__filter-option');
			const currentText = filter.querySelector('.js-filter-current');

			/**
			 * Fetch new content from a URL and update the grid container.
			 *
			 * @param {string} url The target URL.
			 */
			const updateContent = async (url) => {
				target.style.opacity = '0.5';
				target.style.pointerEvents = 'none';

				try {
					const response = await fetch(url);
					if (!response.ok) throw new Error('Network response failed');

					const html = await response.text();
					const parser = new DOMParser();
					const doc = parser.parseFromString(html, 'text/html');
					const newContent = doc.querySelector('.js-all-blogs-target');

					if (newContent) {
						target.innerHTML = newContent.innerHTML;
						window.history.pushState({ path: url }, '', url);
						bindPagination();

						const rect = block.getBoundingClientRect();
						const offset = window.pageYOffset + rect.top - 100;
						window.scrollTo({ top: offset, behavior: 'smooth' });
					}
				} catch (err) {
					console.warn('AJAX failed, fallback to reload:', err);
					window.location.href = url;
				} finally {
					target.style.opacity = '1';
					target.style.pointerEvents = 'auto';
				}
			};

			/**
			 * Intercept pagination links.
			 */
			const bindPagination = () => {
				const links = target.querySelectorAll('a.cwc-all-blogs__page:not(.cwc-all-blogs__page--disabled)');
				links.forEach((link) => {
					link.addEventListener('click', (e) => {
						e.preventDefault();
						updateContent(link.href);
					});
				});
			};

			// Initial pagination binding
			bindPagination();

			// --- Custom Dropdown Logic ---

			// Toggle dropdown
			trigger.addEventListener('click', (e) => {
				e.stopPropagation();
				filter.classList.toggle('is-open');
				trigger.setAttribute('aria-expanded', filter.classList.contains('is-open'));
			});

			// Select an option
			options.forEach((opt) => {
				opt.addEventListener('click', (e) => {
					e.stopPropagation();
					const val = opt.getAttribute('data-value');
					const label = opt.textContent.trim();

					// UI Updates
					options.forEach(o => o.classList.remove('is-active'));
					opt.classList.add('is-active');
					currentText.textContent = label;
					filter.classList.remove('is-open');
					trigger.setAttribute('aria-expanded', 'false');

					// Trigger AJAX Load
					const currentUrl = new URL(window.location.href);
					if (val) {
						currentUrl.searchParams.set('blog_cat', val);
					} else {
						currentUrl.searchParams.delete('blog_cat');
					}
					currentUrl.searchParams.delete('blog_page');
					updateContent(currentUrl.toString());
				});
			});

			// Close dropdown when clicking outside
			document.addEventListener('click', () => {
				filter.classList.remove('is-open');
				trigger.setAttribute('aria-expanded', 'false');
			});
		});
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	window.addEventListener('popstate', () => {
		if (document.querySelectorAll('.cwc-all-blogs').length) {
			window.location.reload();
		}
	});
})();
