/**
 * CWC Wake — Rates Manager frontend logic
 */

(() => {
	'use strict';

	const initRatesManager = () => {
		const managers = document.querySelectorAll('.cwc-rates-manager');

		managers.forEach((manager) => {
			// --- Inquiry Modal Logic ---
			const modal = manager.querySelector('.cwc-inquiry-modal');
			const openBtn = manager.querySelector('.js-open-inquiry-modal');
			const closeBtns = manager.querySelectorAll('.js-close-modal');

			if (modal && openBtn) {
				const openModal = (e) => {
					if (e) e.preventDefault();
					modal.classList.add('is-open');
					document.body.style.overflow = 'hidden';
				};

				const closeModal = () => {
					modal.classList.remove('is-open');
					document.body.style.overflow = '';
				};

				openBtn.addEventListener('click', openModal);

				closeBtns.forEach(btn => {
					btn.addEventListener('click', closeModal);
				});

				// Close on ESC
				document.addEventListener('keydown', (e) => {
					if (e.key === 'Escape' && modal.classList.contains('is-open')) {
						closeModal();
					}
				});
			}

			const dropdown = manager.querySelector('.cwc-rates-manager__dropdown');
			if (!dropdown) return;

			const toggle = dropdown.querySelector('.cwc-rates-manager__dropdown-toggle');
			const menu = dropdown.querySelector('.cwc-rates-manager__dropdown-menu');
			const current = dropdown.querySelector('.cwc-rates-manager__dropdown-current');
			const items = dropdown.querySelectorAll('.cwc-rates-manager__dropdown-item');
			const panels = manager.querySelectorAll('.cwc-rates-manager__panel');

			if (!toggle || !menu || !items.length || !panels.length) return;

			// Toggle Menu
			toggle.addEventListener('click', (e) => {
				e.stopPropagation();
				const expanded = toggle.getAttribute('aria-expanded') === 'true';
				toggle.setAttribute('aria-expanded', !expanded);
				menu.classList.toggle('is-open');
			});

			// Close menu when clicking outside
			document.addEventListener('click', () => {
				toggle.setAttribute('aria-expanded', 'false');
				menu.classList.remove('is-open');
			});

			// Handle Item Selection
			items.forEach((item) => {
				item.addEventListener('click', () => {
					const targetId = item.getAttribute('data-target');
					const targetPanel = manager.querySelector(`#cat-${targetId}`);

					if (!targetPanel) return;

					// Update UI
					current.textContent = item.textContent.trim();
					items.forEach(i => {
						i.classList.remove('is-active');
						i.setAttribute('aria-selected', 'false');
					});
					item.classList.add('is-active');
					item.setAttribute('aria-selected', 'true');

					// Update Panels
					panels.forEach(p => p.classList.remove('is-active'));
					targetPanel.classList.add('is-active');

					// Close Menu
					toggle.setAttribute('aria-expanded', 'false');
					menu.classList.remove('is-open');
				});
			});
		});
	};

	document.addEventListener('DOMContentLoaded', initRatesManager);
})();
