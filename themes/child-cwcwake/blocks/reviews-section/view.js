(function () {
	document.querySelectorAll('.cwc-reviews__slider').forEach(function (slider) {
		var slides = slider.querySelectorAll('.cwc-reviews__slide');
		var total = slides.length;
		if (total < 2) return;

		var current = 0;
		var timer = null;

		function show(index, direction) {
			if (direction) {
				slider.setAttribute('data-dir', direction);
			}

			slides.forEach(function (s, i) {
				if (s.classList.contains('cwc-reviews__slide--active')) {
					s.classList.remove('cwc-reviews__slide--active');
					s.classList.add('cwc-reviews__slide--outgoing');
				} else if (i !== index) {
					s.classList.remove('cwc-reviews__slide--outgoing');
				}

				// Disable appropriate button depending on limits
				var prevBtn = s.querySelector('.cwc-reviews__arrow--prev');
				var nextBtn = s.querySelector('.cwc-reviews__arrow--next');
				
				if (prevBtn) {
					prevBtn.disabled = (index === 0);
				}
				if (nextBtn) {
					nextBtn.disabled = (index === total - 1);
				}
			});

			slides[index].classList.remove('cwc-reviews__slide--outgoing');
			slides[index].classList.add('cwc-reviews__slide--active');

			clearTimeout(timer);
			timer = setTimeout(function () {
				slides.forEach(function (s) {
					s.classList.remove('cwc-reviews__slide--outgoing');
				});
			}, 800);
		}

		// Initialize state without direction animation
		show(0, '');

		slider.querySelectorAll('.cwc-reviews__arrow').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (btn.disabled) return;

				var dir = btn.getAttribute('data-dir');
				if (dir === 'next' && current < total - 1) {
					current++;
					show(current, 'next');
				} else if (dir === 'prev' && current > 0) {
					current--;
					show(current, 'prev');
				}
			});
		});
	});
})();
