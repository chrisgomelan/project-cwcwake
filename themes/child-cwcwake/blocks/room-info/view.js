(() => {
	if (window.cwcRoomInfoInitialized) return;
	window.cwcRoomInfoInitialized = true;

	document.addEventListener('DOMContentLoaded', () => {

		// ─── Only run on pages with the room-info pricing widget ───
		const pricingWidget = document.querySelector('.cwc-room-info__pricing');
		if (!pricingWidget) return;

		// ─── Inject Styles ───
		const style = document.createElement('style');
		style.textContent = `
			.cwc-ri-backdrop {
				position: fixed;
				inset: 0;
				background: transparent;
				z-index: 9990;
				display: none;
				opacity: 0;
				visibility: hidden;
				transition: opacity 0.3s, visibility 0.3s;
			}
			.cwc-ri-backdrop.cwc-ri-backdrop--active {
				opacity: 1;
				visibility: visible;
			}
			.cwc-ri-modal {
				z-index: 9999;
				display: none;
				width: max-content;
				min-width: 320px;
				max-width: 460px;
			}
			@media (min-width: 992px) {
				.cwc-ri-modal {
					position: absolute;
					top: 100%;
					right: 0;
				}
			}
			.cwc-ri-modal.is-active {
				display: block;
			}
			.cwc-ri-modal__content {
				background: #F7F9FB;
				border-radius: 12px;
				padding: clamp(20px, 4vw, 40px);
				box-shadow: 0 8px 32px rgba(0,0,0,0.18);
				position: relative;
			}
			.cwc-ri-modal__close {
				position: absolute;
				top: clamp(12px, 3vw, 10px);
				right: clamp(12px, 3vw, 10px);
				background: none;
				border: none;
				font-size: clamp(18px, 5vw, 24px);
				line-height: 1;
				cursor: pointer;
				color: #1A1A1A;
				opacity: 0.5;
				transition: opacity 0.2s;
				padding: 0;
				width: clamp(24px, 6vw, 32px);
				height: clamp(24px, 6vw, 32px);
				display: flex;
				align-items: center;
				justify-content: center;
				outline:none;
			}
			.cwc-ri-modal__close:hover {
				opacity: 1;
			}
			.cwc-ri-modal__content--calendar {
				padding: clamp(16px, 4vw, 24px);
			}
			.cwc-ri-modal__title {
				font-family: var(--wp--preset--font-family--heading, 'Sora', sans-serif);
				font-weight: 600;
				font-size: clamp(24px, 6vw, 36px);
				color: #0096C7;
				margin: 0 0 clamp(16px, 3vw, 24px);
			}
			/* Calendar */
			.cwc-ri-cal__header {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: clamp(16px, 3vw, 24px);
				padding: 0 clamp(5px, 2vw, 10px);
			}
			.cwc-ri-cal__month-year {
				font-family: var(--wp--preset--font-family--heading, 'Sora', sans-serif);
				font-size: clamp(18px, 4vw, 24px);
				font-weight: 600;
				color: #1A1A1A;
				margin: 0;
			}
			.cwc-ri-cal__nav {
				background: none;
				border: none;
				cursor: pointer;
				color: #1A1A1A;
				padding: 5px;
				display: flex;
				align-items: center;
				transition: opacity 0.2s;
				outline:none;
			}
			.cwc-ri-cal__nav:hover { opacity: 0.6; }
			.cwc-ri-cal__labels {
				display: grid;
				grid-template-columns: repeat(7, 1fr);
				text-align: center;
				margin-bottom: clamp(8px, 2vw, 12px);
			}
			.cwc-ri-cal__labels span {
				font-family: var(--wp--preset--font-family--body, 'Archivo', sans-serif);
				font-weight: 600;
				font-size: clamp(14px, 3vw, 18px);
				color: #454C58;
			}
			.cwc-ri-cal__grid {
				display: grid;
				grid-template-columns: repeat(7, 1fr);
				gap: 2px 0;
				margin-bottom: clamp(20px, 4vw, 30px);
			}
			.cwc-ri-cal__day {
				aspect-ratio: 1;
				display: flex;
				align-items: center;
				justify-content: center;
				cursor: pointer;
				font-family: var(--wp--preset--font-family--body, 'Archivo', sans-serif);
				font-size: clamp(14px, 2.5vw, 18px);
				color: #1A1A1A;
				transition: all 0.2s;
				border-radius: 0;
			}
			.cwc-ri-cal__day--empty { cursor: default; }
			.cwc-ri-cal__day--disabled { opacity: 0.2; cursor: not-allowed; }
			.cwc-ri-cal__day--selected { background: #0096C7 !important; color: #fff !important; border-radius: 4px; }
			.cwc-ri-cal__day--in-range { background: #89D1E8; color: #1A1A1A; }
			.cwc-ri-cal__day--range-start { border-top-left-radius: 4px; border-bottom-left-radius: 4px; }
			.cwc-ri-cal__day--range-end { border-top-right-radius: 4px; border-bottom-right-radius: 4px; }
			.cwc-ri-cal__day:hover:not(.cwc-ri-cal__day--empty):not(.cwc-ri-cal__day--disabled):not(.cwc-ri-cal__day--selected) {
				background: #E8EEF2;
				border-radius: 4px;
			}
			/* Counter */
			.cwc-ri-modal__counter {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: clamp(16px, 3vw, 24px);
				padding-bottom: clamp(12px, 2vw, 16px);
				border-bottom: 1px solid rgba(0,0,0,0.05);
			}
			.cwc-ri-modal__counter-label {
				font-family: var(--wp--preset--font-family--heading, 'Sora', sans-serif);
				font-weight: 600;
				font-size: clamp(16px, 3.5vw, 20px);
				color: #2B3037;
				display: block;
			}
			.cwc-ri-modal__counter-sub {
				font-family: var(--wp--preset--font-family--body, 'Archivo', sans-serif);
				font-weight: 400;
				font-size: clamp(13px, 2.5vw, 16px);
				color: rgba(26,26,26,0.5);
			}
			.cwc-ri-modal__counter-actions {
				display: flex;
				align-items: center;
				gap: clamp(12px, 2vw, 16px);
			}
			.cwc-ri-modal__counter-btn {
				width: clamp(28px, 7vw, 32px);
				height: clamp(28px, 7vw, 32px);
				border-radius: 50%;
				border: 1px solid #CCC;
				background: transparent;
				color: #1A1A1A;
				font-size: clamp(16px, 3vw, 20px);
				display: flex;
				justify-content: center;
				align-items: center;
				cursor: pointer;
				padding: 0;
				line-height: 1;
			}
			.cwc-ri-modal__counter-btn--inc {
				border-color: #0096C7;
				color: #0096C7;
			}
			.cwc-ri-modal__counter-val {
				font-family: var(--wp--preset--font-family--heading, 'Sora', sans-serif);
				font-weight: 600;
				font-size: clamp(16px, 3.5vw, 20px);
				color: #2B3037;
				min-width: 20px;
				text-align: center;
			}
			/* Confirm button */
			.cwc-ri-modal__confirm {
				width: 100%;
				height: clamp(48px, 10vw, 61px);
				border-radius: 12px;
				background: #F7F9FB;
				border: 1px solid #0081A7;
				color: #0096C7;
				font-family: var(--wp--preset--font-family--heading, 'Sora', sans-serif);
				font-weight: 600;
				font-size: clamp(14px, 3vw, 18px);
				cursor: pointer;
				transition: background-color 0.3s, color 0.3s;
			}
			.cwc-ri-modal__confirm:hover {
				background: #0096C7;
				color: #fff;
			}
			@media (max-width: 991px) {
				.cwc-ri-backdrop.cwc-ri-backdrop--active {
					display: flex;
					align-items: center;
					justify-content: center;
					padding: 20px;
					background: rgba(0, 0, 0, 0.5);
					backdrop-filter: blur(2px);
				}

				.cwc-ri-modal {
					position: relative;
					width: min(460px, calc(100vw - 40px));
					min-width: 280px;
					max-width: 460px;
					max-height: calc(100vh - 40px);
					overflow-y: auto;
					top: auto;
					left: auto;
					right: auto;
				}
			}
		`;
		document.head.appendChild(style);

		// ─── Build Backdrop ───
		const backdrop = document.createElement('div');
		backdrop.className = 'cwc-ri-backdrop';
		document.body.appendChild(backdrop);
		console.log('✓ Room-info modal system initialized', { backdrop: backdrop.tagName, inDOM: document.body.contains(backdrop) });

		// ─── Calendar state ───
		let currentMonth = new Date();
		let selectedStart = null;
		let selectedEnd = null;
		let calendarGrid, monthYearEl;
		let fullyBookedDates = [];

		const fetchBookedDates = async () => {
			const bookBtn = document.getElementById('cwc-book-btn');
			if (!bookBtn || !bookBtn.dataset.roomName) return;

			try {
				const formData = new URLSearchParams();
				formData.append('action', 'cwc_get_booked_dates');
				formData.append('room', bookBtn.dataset.roomName);

				const response = await fetch('/wp-admin/admin-ajax.php', { method: 'POST', body: formData });
				const result = await response.json();
				if (result.success) {
					fullyBookedDates = result.data;
					renderCalendar();
				}
			} catch (err) {
				console.error('Failed to fetch booked dates', err);
			}
		};
		fetchBookedDates();

		// ─── Build Date Modal ───
		const dateModal = document.createElement('div');
		dateModal.className = 'cwc-ri-modal';
		dateModal.id = 'cwc-ri-modal-date';
		dateModal.innerHTML = `
			<div class="cwc-ri-modal__content cwc-ri-modal__content--calendar">
				<button class="cwc-ri-modal__close" type="button" aria-label="Close modal">&times;</button>
				<div>
					<div class="cwc-ri-cal__header">
						<button class="cwc-ri-cal__nav cwc-ri-cal__prev" type="button" aria-label="Previous Month">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</button>
						<h3 class="cwc-ri-cal__month-year"></h3>
						<button class="cwc-ri-cal__nav cwc-ri-cal__next" type="button" aria-label="Next Month">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</button>
					</div>
					<div class="cwc-ri-cal__labels">
						<span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span><span>S</span>
					</div>
					<div class="cwc-ri-cal__grid"></div>
				</div>
				<button class="cwc-ri-modal__confirm" id="cwc-ri-date-confirm">Confirm Selection</button>
			</div>
		`;
		document.body.appendChild(dateModal);

		calendarGrid = dateModal.querySelector('.cwc-ri-cal__grid');
		monthYearEl = dateModal.querySelector('.cwc-ri-cal__month-year');

		// ─── Build Guests Modal ───
		const guestsModal = document.createElement('div');
		guestsModal.className = 'cwc-ri-modal';
		guestsModal.id = 'cwc-ri-modal-guests';
		guestsModal.innerHTML = `
			<div class="cwc-ri-modal__content">
				<button class="cwc-ri-modal__close" type="button" aria-label="Close modal">&times;</button>
				<h3 class="cwc-ri-modal__title">Guests</h3>
				<div class="cwc-ri-modal__counter">
					<div>
						<span class="cwc-ri-modal__counter-label">Adults</span>
						<span class="cwc-ri-modal__counter-sub">Age +17</span>
					</div>
					<div class="cwc-ri-modal__counter-actions">
						<button class="cwc-ri-modal__counter-btn" data-target="adults" data-delta="-1" aria-label="Decrease adults">-</button>
						<span class="cwc-ri-modal__counter-val" id="cwc-ri-adults">0</span>
						<button class="cwc-ri-modal__counter-btn cwc-ri-modal__counter-btn--inc" data-target="adults" data-delta="1" aria-label="Increase adults">+</button>
					</div>
				</div>
				<div class="cwc-ri-modal__counter">
					<div>
						<span class="cwc-ri-modal__counter-label">Kids</span>
						<span class="cwc-ri-modal__counter-sub">Age 0 to 17</span>
					</div>
					<div class="cwc-ri-modal__counter-actions">
						<button class="cwc-ri-modal__counter-btn" data-target="kids" data-delta="-1" aria-label="Decrease kids">-</button>
						<span class="cwc-ri-modal__counter-val" id="cwc-ri-kids">0</span>
						<button class="cwc-ri-modal__counter-btn cwc-ri-modal__counter-btn--inc" data-target="kids" data-delta="1" aria-label="Increase kids">+</button>
					</div>
				</div>
				<button class="cwc-ri-modal__confirm" id="cwc-ri-guests-confirm">Confirm Selection</button>
			</div>
		`;
		document.body.appendChild(guestsModal);

		// ─── Helpers ───
		const isOverlayMode = () => {
			const result = window.matchMedia('(max-width: 991px)').matches;
			console.log('→ isOverlayMode()', { overlayMode: result, windowWidth: window.innerWidth });
			return result;
		};

		const closeAll = () => {
			console.log('→ closeAll CALLED');
			backdrop.classList.remove('cwc-ri-backdrop--active');

			// Reset all modals
			document.querySelectorAll('.cwc-ri-modal').forEach(m => {
				m.classList.remove('is-active');
				m.style.display = ''; // Clear inline display
			});

			backdrop.style.display = '';
			backdrop.style.alignItems = '';
			backdrop.style.justifyContent = '';
			backdrop.style.padding = '';
			backdrop.style.background = '';
			backdrop.style.backdropFilter = '';
			backdrop.style.opacity = '';
			backdrop.style.visibility = '';
			console.log('→ closeAll COMPLETE');
		};

		const openModal = (trigger, modal) => {
			closeAll();
			const overlay = isOverlayMode();
			console.log('→ openModal START', { overlay, backdropParent: backdrop.parentElement?.tagName, modalId: modal.id });

			if (overlay) {
				console.log('→ OVERLAY MODE detected');
				if (modal.parentElement !== backdrop) {
					console.log('→ Moving modal into backdrop');
					backdrop.appendChild(modal);
				}
				backdrop.style.display = 'flex';
				backdrop.style.alignItems = 'center';
				backdrop.style.justifyContent = 'center';
				backdrop.style.padding = '20px';
				backdrop.style.background = 'rgba(0, 0, 0, 0.5)';
				backdrop.style.backdropFilter = 'blur(2px)';
				backdrop.style.opacity = '1';
				backdrop.style.visibility = 'visible';
				backdrop.style.zIndex = '9990';
				backdrop.classList.add('cwc-ri-backdrop--active');
				modal.style.display = 'block';
				modal.style.position = '';
				modal.style.top = '';
				modal.style.left = '';
				modal.style.transform = '';

				console.log('→ OVERLAY STYLES APPLIED', {
					backdropDisplay: backdrop.style.display,
					backdropVisibility: backdrop.style.visibility,
					modalDisplay: modal.style.display,
					computed: {
						backdropDisplay: window.getComputedStyle(backdrop).display,
						modalDisplay: window.getComputedStyle(modal).display
					}
				});
			} else {
				console.log('→ DROPDOWN MODE detected');
				trigger.appendChild(modal);
				modal.style.display = 'block';
				modal.style.position = '';
				modal.style.top = '';
				modal.style.left = '';
				modal.style.transform = '';
			}

			modal.classList.add('is-active');
			console.log('→ openModal COMPLETE');
		};

		backdrop.addEventListener('click', closeAll);

		const setupModalEvents = (modal) => {
			modal.addEventListener('click', e => {
				if (e.target.closest('.cwc-ri-modal__close')) {
					console.log('→ Modal Close Button Clicked');
					closeAll();
				}
				e.stopPropagation();
			});
		};

		setupModalEvents(dateModal);
		setupModalEvents(guestsModal);

		// Formats date to YYYY-MM-DD in local time (ignoring timezone offset issues)
		const formatLocalDate = (d) => {
			const year = d.getFullYear();
			const month = String(d.getMonth() + 1).padStart(2, '0');
			const day = String(d.getDate()).padStart(2, '0');
			return `${year}-${month}-${day}`;
		};

		// ─── Calendar render ───
		const renderCalendar = () => {
			if (!calendarGrid || !monthYearEl) return;
			calendarGrid.innerHTML = '';
			const year = currentMonth.getFullYear();
			const month = currentMonth.getMonth();

			monthYearEl.textContent = new Intl.DateTimeFormat('en-US', { month: 'long', year: 'numeric' }).format(currentMonth);

			const firstDay = new Date(year, month, 1).getDay();
			const daysInMonth = new Date(year, month + 1, 0).getDate();
			const startOffset = firstDay === 0 ? 6 : firstDay - 1;
			const today = new Date(); today.setHours(0, 0, 0, 0);

			for (let i = 0; i < startOffset; i++) {
				const el = document.createElement('div');
				el.className = 'cwc-ri-cal__day cwc-ri-cal__day--empty';
				calendarGrid.appendChild(el);
			}

			for (let day = 1; day <= daysInMonth; day++) {
				const dateObj = new Date(year, month, day);
				const dateStr = formatLocalDate(dateObj);
				const el = document.createElement('div');
				el.className = 'cwc-ri-cal__day';
				el.textContent = day;

				let isDisabled = false;
				if (dateObj < today || fullyBookedDates.includes(dateStr)) {
					isDisabled = true;
					el.classList.add('cwc-ri-cal__day--disabled');
				}

				if (selectedStart && dateObj.getTime() === selectedStart.getTime()) {
					el.classList.add('cwc-ri-cal__day--selected', 'cwc-ri-cal__day--range-start');
				}
				if (selectedEnd && dateObj.getTime() === selectedEnd.getTime()) {
					el.classList.add('cwc-ri-cal__day--selected', 'cwc-ri-cal__day--range-end');
				}
				if (selectedStart && selectedEnd && dateObj > selectedStart && dateObj < selectedEnd) {
					el.classList.add('cwc-ri-cal__day--in-range');
				}

				el.addEventListener('click', () => {
					if (isDisabled) return;
					if (!selectedStart || (selectedStart && selectedEnd)) {
						selectedStart = dateObj; selectedEnd = null;
					} else if (dateObj < selectedStart) {
						selectedStart = dateObj;
					} else if (dateObj.getTime() === selectedStart.getTime()) {
						selectedStart = null;
					} else {
						// Check if range contains any disabled date
						let hasDisabled = false;
						let tempDate = new Date(selectedStart);
						tempDate.setDate(tempDate.getDate() + 1);
						while (tempDate < dateObj) {
							if (fullyBookedDates.includes(formatLocalDate(tempDate))) {
								hasDisabled = true;
								break;
							}
							tempDate.setDate(tempDate.getDate() + 1);
						}

						if (hasDisabled) {
							// If selection spans a disabled date, reset selection to just the new end date
							selectedStart = dateObj;
							selectedEnd = null;
						} else {
							selectedEnd = dateObj;
						}
					}
					renderCalendar();
				});

				calendarGrid.appendChild(el);
			}
		};

		dateModal.querySelector('.cwc-ri-cal__prev').addEventListener('click', e => {
			e.stopPropagation();
			currentMonth.setMonth(currentMonth.getMonth() - 1);
			renderCalendar();
		});
		dateModal.querySelector('.cwc-ri-cal__next').addEventListener('click', e => {
			e.stopPropagation();
			currentMonth.setMonth(currentMonth.getMonth() + 1);
			renderCalendar();
		});

		// ─── Trigger fields ───
		document.querySelectorAll('.cwc-room-info__pricing-field[data-modal-target]').forEach(trigger => {
			trigger.addEventListener('click', e => {
				e.stopPropagation();
				const target = trigger.getAttribute('data-modal-target');
				console.log('✓ TRIGGER CLICKED', { target, overlayMode: isOverlayMode() });
				if (target === 'date') {
					renderCalendar();
					openModal(trigger, dateModal);
				} else if (target === 'guests') {
					openModal(trigger, guestsModal);
				}
			});
		});

		// ─── Date confirm ───
		document.getElementById('cwc-ri-date-confirm').addEventListener('click', () => {
			if (selectedStart && selectedEnd) {
				const opts = { month: 'short', day: 'numeric', year: 'numeric' };
				const checkinEl = document.getElementById('cwc-ri-val-checkin');
				const checkoutEl = document.getElementById('cwc-ri-val-checkout');
				if (checkinEl) checkinEl.textContent = selectedStart.toLocaleDateString('en-US', opts);
				if (checkoutEl) checkoutEl.textContent = selectedEnd.toLocaleDateString('en-US', opts);
			}
			closeAll();
		});

		// ─── Guest counter ───
		const adultsEl = document.getElementById('cwc-ri-adults');
		const kidsEl = document.getElementById('cwc-ri-kids');

		guestsModal.querySelectorAll('.cwc-ri-modal__counter-btn').forEach(btn => {
			btn.addEventListener('click', () => {
				const target = btn.getAttribute('data-target');
				const delta = parseInt(btn.getAttribute('data-delta'), 10);
				const el = target === 'adults' ? adultsEl : kidsEl;
				const maxCap = parseInt(document.getElementById('cwc-book-btn')?.dataset.maxCapacity || '10', 10);

				let adults = parseInt(adultsEl.textContent, 10);
				let kids = parseInt(kidsEl.textContent, 10);
				let val = (target === 'adults' ? adults : kids) + delta;
				if (val < 0) val = 0;

				const newTotal = (target === 'adults' ? val : adults) + (target === 'kids' ? val : kids);
				if (newTotal > maxCap) {
					if (window.cwcToast) window.cwcToast.show(`Maximum capacity is ${maxCap} guests.`, 'warning');
					else alert(`Maximum capacity is ${maxCap} guests.`);
					return;
				}
				el.textContent = val;
			});
		});

		// ─── Guest confirm ───
		document.getElementById('cwc-ri-guests-confirm').addEventListener('click', () => {
			const adults = parseInt(adultsEl?.textContent || '0', 10);
			const kids = parseInt(kidsEl?.textContent || '0', 10);
			if (adults < 1) {
				if (window.cwcToast) window.cwcToast.show('At least one adult is required.', 'warning');
				else alert('At least one adult is required.');
				return;
			}
			const guestsValEl = document.getElementById('cwc-ri-val-guests');
			if (guestsValEl) guestsValEl.textContent = `${adults} Adult, ${kids} Kids`;
			closeAll();
		});

		// ─── Book button ───
		const bookBtn = document.getElementById('cwc-book-btn');
		if (bookBtn) {
			bookBtn.addEventListener('click', async (e) => {
				if (bookBtn.getAttribute('href') && bookBtn.getAttribute('href').includes('/contact/')) {
					return; // Let the default link navigation proceed for Inquire
				}

				e.preventDefault();

				const checkinEl = document.getElementById('cwc-ri-val-checkin');
				const checkoutEl = document.getElementById('cwc-ri-val-checkout');
				const guestsEl = document.getElementById('cwc-ri-val-guests');

				const checkin = checkinEl?.textContent.trim();
				const checkout = checkoutEl?.textContent.trim();
				const guests = guestsEl?.textContent.trim();
				const room = bookBtn.dataset.roomName || '';

				// Validation
				let errorMsg = '';
				if (checkin === 'Add date' || checkout === 'Add date') {
					errorMsg = 'Please select check-in and check-out dates.';
				} else if (!guests || guests === '0 Adult, 0 Kids') {
					errorMsg = 'Please select the number of guests.';
				} else {
					const adultsMatch = guests.match(/(\d+)\s+Adult/i);
					const adultsCount = adultsMatch ? parseInt(adultsMatch[1], 10) : 0;
					if (adultsCount < 1) {
						errorMsg = 'At least one adult is required for booking.';
					}
				}

				if (errorMsg) {
					if (window.cwcToast) {
						window.cwcToast.show(errorMsg, 'warning');
					} else {
						alert(errorMsg);
					}
					return;
				}

				// Check availability before proceeding
				bookBtn.style.opacity = '0.6';
				bookBtn.style.pointerEvents = 'none';
				const origText = bookBtn.textContent;
				bookBtn.textContent = 'Checking availability...';

				try {
					const formData = new URLSearchParams();
					formData.append('action', 'cwc_check_room_availability');
					formData.append('room', room);
					formData.append('checkin', checkin);
					formData.append('checkout', checkout);

					const response = await fetch('/wp-admin/admin-ajax.php', { method: 'POST', body: formData });
					const result = await response.json();

					if (result.success && result.data.fully_booked) {
						bookBtn.style.opacity = '1';
						bookBtn.style.pointerEvents = 'auto';
						bookBtn.textContent = origText;
						if (window.cwcToast) {
							window.cwcToast.show('Sorry, this room is already reserved for your selected dates. Please choose different dates.', 'error');
						} else {
							alert('Sorry, this room is already reserved for your selected dates. Please choose different dates.');
						}
						return;
					}
				} catch (err) {
					console.error('Availability check failed', err);
				}

				bookBtn.style.opacity = '1';
				bookBtn.style.pointerEvents = 'auto';
				bookBtn.textContent = origText;

				let targetUrl = bookBtn.getAttribute('href');
				if (!targetUrl || targetUrl === '#book') targetUrl = '/booking/';

				const bookingData = {
					room: room,
					checkin: checkin,
					checkout: checkout,
					guests: guests
				};
				sessionStorage.setItem('cwc_booking_data', JSON.stringify(bookingData));

				window.location.href = targetUrl;
			});
		}

	});
})();
