(function () {
	document.querySelectorAll('.cwc-reviews__slider').forEach(function (slider) {
		var slides = slider.querySelectorAll('.cwc-reviews__slide');
		var total = slides.length;
		if (total < 2) return;

		var current = 0;

		function show(index) {
			slides.forEach(function (s) {
				s.classList.remove('cwc-reviews__slide--active');
			});
			slides[index].classList.add('cwc-reviews__slide--active');

			slider.querySelectorAll('.cwc-reviews__counter-current').forEach(function (el) {
				el.textContent = String(index + 1).padStart(2, '0');
			});
		}

		slider.querySelectorAll('.cwc-reviews__arrow').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var dir = btn.getAttribute('data-dir');
				if (dir === 'next') {
					current = (current + 1) % total;
				} else {
					current = (current - 1 + total) % total;
				}
				show(current);
			});
		});
	});
})();
