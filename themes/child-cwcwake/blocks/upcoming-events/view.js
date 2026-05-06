/**
 * Upcoming Events block-level interactivity.
 *
 * Implements the "stack page" switcher logic: clicking a day circle
 * toggles visibility classes on a stack of cards with smooth animations.
 */

(function() {
	/**
	 * Initialize the switcher for a specific block instance.
	 *
	 * @param {HTMLElement} block The block wrapper element.
	 */
	const initBlock = (block) => {
		const railItems = block.querySelectorAll('.cwc-upcoming-events__rail-item');
		const cards = block.querySelectorAll('.cwc-upcoming-events__card');

		if (!railItems.length || !cards.length) return;

		railItems.forEach((item) => {
			const button = item.querySelector('.cwc-upcoming-events__day');
			const targetIndex = item.getAttribute('data-event-index');

			if (!button || targetIndex === null) return;

			button.addEventListener('click', (e) => {
				e.preventDefault();

				// Skip if already active
				if (item.classList.contains('cwc-upcoming-events__rail-item--active')) return;

				// 1. Update Rail UI
				railItems.forEach(ri => {
					ri.classList.remove('cwc-upcoming-events__rail-item--active');
					const btn = ri.querySelector('.cwc-upcoming-events__day');
					if (btn) {
						btn.classList.remove('cwc-upcoming-events__day--active');
						btn.setAttribute('aria-pressed', 'false');
					}
				});

				item.classList.add('cwc-upcoming-events__rail-item--active');
				button.classList.add('cwc-upcoming-events__day--active');
				button.setAttribute('aria-pressed', 'true');

				// 2. Update Card Stack with smooth "stack" effect
				cards.forEach((card) => {
					if (card.getAttribute('data-event-index') === targetIndex) {
						card.classList.add('is-active');
					} else {
						card.classList.remove('is-active');
					}
				});
			});
		});
	};

	// Standard WordPress block initialization hook
	document.addEventListener('DOMContentLoaded', () => {
		const blocks = document.querySelectorAll('.cwc-upcoming-events');
		blocks.forEach(initBlock);
	});
})();
