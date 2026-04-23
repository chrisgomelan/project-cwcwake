/*
 * CWC Wake — About: Champions carousel + phrase cycling
 *
 * Creates a curved 3D carousel from the slide elements and
 * auto-advances every 3s, cycling the phrase overlay in sync.
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		const root = document.querySelector('[data-cwc-champions]');
		if (!root) return;

		const track = root.querySelector('.cwc-champions__track');
		let slides = Array.from(root.querySelectorAll('.cwc-champions__slide'));
		const phrases = Array.from(root.querySelectorAll('[data-cwc-phrase]'));
		const originalTotal = slides.length;
		if (originalTotal === 0) return;

		// We need a smooth, wide curve. So we'll fill a large cylinder
		// with cloned slides. Let's aim for ~16-18 slots on the cylinder.
		const TARGET_SLOTS = 15;

		// Clone slides so we have enough to form a smooth cylinder
		if (slides.length < TARGET_SLOTS) {
			const clonesNeeded = TARGET_SLOTS - slides.length;
			for (let i = 0; i < clonesNeeded; i++) {
				const clone = slides[i % originalTotal].cloneNode(true);
				track.appendChild(clone);
			}
			// Update slides array
			slides = Array.from(root.querySelectorAll('.cwc-champions__slide'));
		}

		const total = slides.length;
		const angleStep = 360 / total;

		let radius = 0;
		let currentAngle = 0;
		let targetAngle = 0;

		function initializeCarousel() {
			const slideWidth = slides[0].offsetWidth;
			// Trigonometry to define depth. Add a gap by padding the width used in the radius constraint.
			const gapToApply = 20; // Approx 80px gap total
			radius = Math.round(((slideWidth / 2) + gapToApply) / Math.tan((Math.PI / total)));

			slides.forEach((slide, i) => {
				const angle = i * angleStep;
				// Placing items on the inside curve (concave)
				slide.style.transform = `rotateY(${-angle}deg) translateZ(-${radius}px)`;
			});
			updateView();
		}

		function updateView() {
			// Rotate the track. Let currentAngle drive the rotation.
			track.style.transform = `translate(-50%, -50%) translateZ(${radius}px) rotateY(${-currentAngle}deg)`;
		}
		
		const autoRotateSpeed = 0.08; // Sets continuous drift speed
		function animationLoop() {
			if (!isDragging) {
				targetAngle -= autoRotateSpeed; 
			}
			// Apply lerp for buttery smooth swiping and easing into rotation
			currentAngle += (targetAngle - currentAngle) * 0.1;
			updateView();
			requestAnimationFrame(animationLoop);
		}

		let currentPhraseIdx = 0;
		if (phrases.length > 0) {
			phrases.forEach((p, i) => p.classList.toggle('is-active', i === 0));
			setInterval(() => {
				currentPhraseIdx = (currentPhraseIdx + 1) % phrases.length;
				phrases.forEach((p, i) => p.classList.toggle('is-active', i === currentPhraseIdx));
			}, 3000);
		}

		let resizeTimer;
		window.addEventListener('resize', () => {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(initializeCarousel, 250);
		});

		// Swipe handling
		let isDragging = false;
		let startX = 0;
		let startAngle = 0;

		const swipeArea = root.querySelector('.cwc-champions__carousel');
		if (swipeArea) {
			swipeArea.style.cursor = 'grab';
			swipeArea.style.touchAction = 'pan-y'; // allowing vertical page scroll, capturing horizontal

			swipeArea.addEventListener('dragstart', (e) => e.preventDefault()); // Prevent ghost drag on images

			swipeArea.addEventListener('pointerdown', (e) => {
				isDragging = true;
				startX = e.clientX;
				startAngle = targetAngle;
				swipeArea.style.cursor = 'grabbing';
				// prevent image selection issues
				e.target.setPointerCapture && e.target.setPointerCapture(e.pointerId);
			});

			swipeArea.addEventListener('pointermove', (e) => {
				if (!isDragging) return;
				const draggedX = e.clientX - startX;
				const sensitivity = 0.1; // How much drag distance affects rotational angle
				targetAngle = startAngle + (draggedX * sensitivity);
			});

			const endDrag = (e) => {
				if (!isDragging) return;
				isDragging = false;
				swipeArea.style.cursor = 'grab';
				e.target.releasePointerCapture && e.target.releasePointerCapture(e.pointerId);
			};

			swipeArea.addEventListener('pointerup', endDrag);
			swipeArea.addEventListener('pointercancel', endDrag);
		}

		// Initial setup
		setTimeout(initializeCarousel, 100);
		requestAnimationFrame(animationLoop);
	});
})();
