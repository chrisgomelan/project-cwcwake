(function () {
	var header = document.querySelector('.cwc-header');
	if (!header) return;

	var scrollClass = 'cwc-header--scrolled';
	var threshold = 10;

	function update() {
		if (window.scrollY > threshold) {
			header.classList.add(scrollClass);
		} else {
			header.classList.remove(scrollClass);
		}
	}

	window.addEventListener('scroll', update, { passive: true });
	update();
})();
