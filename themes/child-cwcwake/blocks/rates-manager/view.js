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

				// --- Form Submission Logic ---
				const form = modal.querySelector('.cwc-inquiry-form');
				const submitBtn = form?.querySelector('.cwc-inquiry-form__submit');

				if (form && submitBtn) {
					form.addEventListener('submit', async (e) => {
						e.preventDefault();

						const email = form.querySelector('input[type="email"]').value;
						const subject = form.querySelector('select').value;
						const message = form.querySelector('textarea').value;

						// Loading state
						const originalText = submitBtn.textContent;
						submitBtn.textContent = 'Sending...';
						submitBtn.disabled = true;
						submitBtn.style.opacity = '0.7';

						try {
							const formData = new FormData();
							formData.append('action', 'cwc_submit_inquiry');
							formData.append('email', email);
							formData.append('subject', subject);
							formData.append('message', message);

							const response = await fetch(cwcVars.ajaxUrl, {
								method: 'POST',
								body: formData
							});

							const result = await response.json();

							if (result.success) {
								if (window.cwcToast) {
									window.cwcToast.show(result.data.message, 'success');
								} else {
									alert(result.data.message);
								}
								form.reset();
								setTimeout(closeModal, 1500);
							} else {
								throw new Error(result.data.message || 'Submission failed');
							}
						} catch (error) {
							console.error('Inquiry Error:', error);
							if (window.cwcToast) {
								window.cwcToast.show(error.message || 'Something went wrong. Please try again.', 'error');
							} else {
								alert(error.message || 'Something went wrong. Please try again.');
							}
						} finally {
							submitBtn.textContent = originalText;
							submitBtn.disabled = false;
							submitBtn.style.opacity = '';
						}
					});
				}
			}

			const dropdown = manager.querySelector('.cwc-rates-manager__dropdown');
			if (!dropdown) return;

			const sidebar = manager.querySelector('.cwc-rates-manager__sidebar');
			const toggle = dropdown.querySelector('.cwc-rates-manager__dropdown-toggle');
			const menu = dropdown.querySelector('.cwc-rates-manager__dropdown-menu');
			const current = dropdown.querySelector('.cwc-rates-manager__dropdown-current');
			const items = dropdown.querySelectorAll('.cwc-rates-manager__dropdown-item');
			const panels = manager.querySelectorAll('.cwc-rates-manager__panel');

			if (!toggle || !menu || !items.length || !panels.length) return;

			// Handle Mobile Layout Shift
			const handleLayout = () => {
				const isMobile = window.innerWidth <= 900;
				const activePanel = manager.querySelector('.cwc-rates-manager__panel.is-active');

				if (isMobile && activePanel) {
					const header = activePanel.querySelector('.cwc-rates-manager__panel-header');
					if (header && !header.contains(dropdown)) {
						header.appendChild(dropdown);
					}
				} else if (!isMobile && sidebar && !sidebar.contains(dropdown)) {
					sidebar.appendChild(dropdown);
				}
			};

			window.addEventListener('resize', handleLayout);
			handleLayout();

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

					// Re-handle layout to move dropdown to new active panel
					handleLayout();
				});
			});
		});
	};

	document.addEventListener('DOMContentLoaded', initRatesManager);
})();
