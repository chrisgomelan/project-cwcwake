(() => {
	document.addEventListener('DOMContentLoaded', () => {
		const backdrop = document.getElementById('cwc-modal-backdrop');
		if (!backdrop) {
			return;
		}

		const modals = document.querySelectorAll('.cwc-booking-modal');

		// Move backdrop to body
		document.body.appendChild(backdrop);

		// Prevent clicks inside modal from bubbling up and re-triggering the open event
		modals.forEach(modal => {
			modal.addEventListener('click', (e) => e.stopPropagation());
		});

		const triggers = document.querySelectorAll('.cwc-booking-bar__field[data-modal-target]');

		// ─── Shared Calendar State ───
		let fullyBookedDates = [];
		let renderCalendarFn = null; // Will be defined later
		const fetchBookedDates = async (roomName) => {
			if (!roomName || roomName === 'Choose Room') return;
			try {
				const formData = new URLSearchParams();
				formData.append('action', 'cwc_get_booked_dates');
				formData.append('room', roomName);

				const response = await fetch('/wp-admin/admin-ajax.php', { method: 'POST', body: formData });
				const result = await response.json();
				if (result.success) {
					fullyBookedDates = result.data;
					if (renderCalendarFn) renderCalendarFn();
				}
			} catch (err) {
				console.error('Failed to fetch booked dates', err);
			}
		};

		const closeModal = () => {
			backdrop.classList.remove('is-active');
			modals.forEach((modal) => modal.classList.remove('is-active'));
		};

		backdrop.addEventListener('click', closeModal);

		triggers.forEach((trigger) => {
			trigger.addEventListener('click', () => {
				const targetModal = trigger.getAttribute('data-modal-target');

				if (targetModal === 'guests') {
					const roomVal = document.getElementById('cwc-val-room');
					if (!roomVal || roomVal.textContent.trim() === 'Choose Room' || roomVal.textContent.trim() === '') {
						if (window.cwcToast) {
							window.cwcToast.show('Please select a room first.', 'warning');
						} else {
							alert('Please select a room first.');
						}
						return;
					}
				}

				const targetId = `cwc-modal-${targetModal}`;
				const modal = document.getElementById(targetId);
				if (modal) {
					closeModal();
					backdrop.classList.add('is-active');

					// Append modal to the clicked field so it stays perfectly attached
					trigger.appendChild(modal);
					modal.classList.add('is-active');

					// Clear any inline styles from previous iterations
					modal.style.position = '';
					modal.style.top = '';
					modal.style.left = '';
					modal.style.transform = '';
				} else if (targetModal === 'date') {
					// Date modal could be implemented with a library like flatpickr.
					// For now we'll just simulate selecting a date.
					const checkin = document.getElementById('cwc-val-checkin');
					const checkout = document.getElementById('cwc-val-checkout');
					if (checkin) checkin.textContent = 'Nov 1, 2026';
					if (checkout) checkout.textContent = 'Nov 5, 2026';
				}
			});
		});

		// Confirm buttons in modals
		document.querySelectorAll('.cwc-booking-modal__confirm').forEach((btn) => {
			btn.addEventListener('click', () => {
				const activeModal = btn.closest('.cwc-booking-modal');
				if (!activeModal) return;

				if (activeModal.id === 'cwc-modal-room') {
					const selectedRadio = activeModal.querySelector('input[type="radio"]:checked');
					if (selectedRadio) {
						const roomVal = document.getElementById('cwc-val-room');
						if (roomVal) {
							if (roomVal.textContent !== selectedRadio.value) {
								roomVal.textContent = selectedRadio.value;
								fetchBookedDates(selectedRadio.value);
							} else {
								roomVal.textContent = selectedRadio.value;
							}
						}

						// Enforce capacity on existing guest selection
						const maxCap = parseInt(selectedRadio.dataset.capacity || 4, 10);
						const adultsEl = document.getElementById('cwc-val-modal-adults');
						const kidsEl = document.getElementById('cwc-val-modal-kids');
						if (adultsEl && kidsEl) {
							let adults = parseInt(adultsEl.textContent, 10);
							let kids = parseInt(kidsEl.textContent, 10);
							if (adults + kids > maxCap) {
								adultsEl.textContent = Math.min(adults, maxCap);
								kidsEl.textContent = Math.max(0, maxCap - parseInt(adultsEl.textContent, 10));

								// Update summary
								const guestsVal = document.getElementById('cwc-val-guests');
								if (guestsVal) {
									guestsVal.textContent = `${adultsEl.textContent} Adult, ${kidsEl.textContent} Kids`;
								}
							}
						}
					}
				} else if (activeModal.id === 'cwc-modal-guests') {
					const adults = parseInt(document.getElementById('cwc-val-modal-adults')?.textContent || '0', 10);
					const kids = parseInt(document.getElementById('cwc-val-modal-kids')?.textContent || '0', 10);

					if (adults < 1) {
						if (window.cwcToast) {
							window.cwcToast.show('At least one adult is required for every booking.', 'warning');
						} else {
							alert('At least one adult is required for every booking.');
						}
						return; // Prevent closing and saving
					}

					const guestsVal = document.getElementById('cwc-val-guests');
					if (guestsVal) {
						guestsVal.textContent = `${adults} Adult, ${kids} Kids`;
					}
				} else if (activeModal.id === 'cwc-modal-date') {
					if (selectedStart && selectedEnd) {
						const options = { month: 'short', day: 'numeric', year: 'numeric' };
						const checkinVal = document.getElementById('cwc-val-checkin');
						const checkoutVal = document.getElementById('cwc-val-checkout');
						if (checkinVal) checkinVal.textContent = selectedStart.toLocaleDateString('en-US', options);
						if (checkoutVal) checkoutVal.textContent = selectedEnd.toLocaleDateString('en-US', options);
					}
				}

				closeModal();
			});
		});

		// ─── Calendar Logic ───
		let currentMonth = new Date();
		let selectedStart = null;
		let selectedEnd = null;

		// Formats date to YYYY-MM-DD in local time
		const formatLocalDate = (d) => {
			const year = d.getFullYear();
			const month = String(d.getMonth() + 1).padStart(2, '0');
			const day = String(d.getDate()).padStart(2, '0');
			return `${year}-${month}-${day}`;
		};

		const calendarGrid = document.getElementById('cwc-calendar-grid');
		const monthYearEl = document.querySelector('.cwc-calendar__month-year');
		const prevMonthBtn = document.querySelector('.cwc-calendar__prev');
		const nextMonthBtn = document.querySelector('.cwc-calendar__next');

		const renderCalendar = () => {
			if (!calendarGrid || !monthYearEl) return;

			calendarGrid.innerHTML = '';
			const year = currentMonth.getFullYear();
			const month = currentMonth.getMonth();

			monthYearEl.textContent = new Intl.DateTimeFormat('en-US', { month: 'long', year: 'numeric' }).format(currentMonth);

			const firstDayOfMonth = new Date(year, month, 1).getDay();
			const daysInMonth = new Date(year, month + 1, 0).getDate();

			// Monday start adjustment (M=1, ..., Sat=6, Sun=0)
			let startOffset = firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1;

			for (let i = 0; i < startOffset; i++) {
				const emptyDiv = document.createElement('div');
				emptyDiv.className = 'cwc-calendar__day cwc-calendar__day--empty';
				calendarGrid.appendChild(emptyDiv);
			}

			const today = new Date();
			today.setHours(0, 0, 0, 0);

			for (let day = 1; day <= daysInMonth; day++) {
				const dateObj = new Date(year, month, day);
				const dateStr = formatLocalDate(dateObj);
				const dayDiv = document.createElement('div');
				dayDiv.className = 'cwc-calendar__day';
				dayDiv.textContent = day;

				let isDisabled = false;
				if (dateObj < today || fullyBookedDates.includes(dateStr)) {
					isDisabled = true;
					dayDiv.classList.add('cwc-calendar__day--disabled');
				}

				if (dateObj.getTime() === today.getTime()) {
					dayDiv.classList.add('cwc-calendar__day--today');
				}

				if (selectedStart && dateObj.getTime() === selectedStart.getTime()) {
					dayDiv.classList.add('cwc-calendar__day--selected');
					dayDiv.classList.add('cwc-calendar__day--range-start');
				}
				if (selectedEnd && dateObj.getTime() === selectedEnd.getTime()) {
					dayDiv.classList.add('cwc-calendar__day--selected');
					dayDiv.classList.add('cwc-calendar__day--range-end');
				}
				if (selectedStart && selectedEnd && dateObj > selectedStart && dateObj < selectedEnd) {
					dayDiv.classList.add('cwc-calendar__day--in-range');
				}

				dayDiv.addEventListener('click', () => {
					if (isDisabled) return;

					if (!selectedStart || (selectedStart && selectedEnd)) {
						selectedStart = dateObj;
						selectedEnd = null;
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
							selectedStart = dateObj;
							selectedEnd = null;
						} else {
							selectedEnd = dateObj;
						}
					}
					renderCalendarFn();
				});

				calendarGrid.appendChild(dayDiv);
			}
		};
		renderCalendarFn = renderCalendar;

		prevMonthBtn?.addEventListener('click', (e) => {
			e.stopPropagation();
			currentMonth.setMonth(currentMonth.getMonth() - 1);
			renderCalendar();
		});

		nextMonthBtn?.addEventListener('click', (e) => {
			e.stopPropagation();
			currentMonth.setMonth(currentMonth.getMonth() + 1);
			renderCalendar();
		});

		renderCalendar();

		// Counter functionality for Guests Modal
		const updateCounter = (target, delta) => {
			const el = document.getElementById(`cwc-val-modal-${target}`);
			const adultsEl = document.getElementById('cwc-val-modal-adults');
			const kidsEl = document.getElementById('cwc-val-modal-kids');

			if (el && adultsEl && kidsEl) {
				let maxCapacity = 10;
				const selectedRoom = document.querySelector('input[name="cwc_room_type"]:checked');

				if (selectedRoom && selectedRoom.dataset.capacity) {
					maxCapacity = parseInt(selectedRoom.dataset.capacity, 10);
				} else {
					const allRooms = Array.from(document.querySelectorAll('input[name="cwc_room_type"]'));
					if (allRooms.length > 0) {
						maxCapacity = Math.max(...allRooms.map(r => parseInt(r.dataset.capacity || 4, 10)));
					}
				}

				let adults = parseInt(adultsEl.textContent, 10);
				let kids = parseInt(kidsEl.textContent, 10);

				let val = parseInt(el.textContent, 10) + delta;
				if (val < 0) val = 0;

				const newTotal = (target === 'adults' ? val : adults) + (target === 'kids' ? val : kids);

				if (newTotal <= maxCapacity) {
					el.textContent = val;
				} else {
					if (window.cwcToast) {
						window.cwcToast.show(`Maximum capacity is ${maxCapacity} persons${selectedRoom ? ' for the selected room' : ''}.`, 'warning');
					}
				}
			}
		};

		document.querySelectorAll('.cwc-booking-modal__btn-inc').forEach((btn) => {
			btn.addEventListener('click', (e) => {
				e.preventDefault();
				updateCounter(btn.getAttribute('data-target'), 1);
			});
		});

		document.querySelectorAll('.cwc-booking-modal__btn-dec').forEach((btn) => {
			btn.addEventListener('click', (e) => {
				e.preventDefault();
				updateCounter(btn.getAttribute('data-target'), -1);
			});
		});
		// Proceed button → navigate to booking flow
		const proceedBtn = document.querySelector('.cwc-booking-bar__proceed');
		proceedBtn?.addEventListener('click', async () => {
			const room = document.getElementById('cwc-val-room')?.textContent.trim() || '';
			const checkin = document.getElementById('cwc-val-checkin')?.textContent.trim() || '';
			const checkout = document.getElementById('cwc-val-checkout')?.textContent.trim() || '';
			const guests = document.getElementById('cwc-val-guests')?.textContent.trim() || '';

			const guestsText = document.getElementById('cwc-val-guests')?.textContent.trim() || '';
			const adultsMatch = guestsText.match(/(\d+)\s+Adult/i);
			const adultsSelected = adultsMatch ? parseInt(adultsMatch[1], 10) : 0;

			if (!room || room === 'Choose Room' || !checkin || checkin === 'Add date' || !checkout || checkout === 'Add date' || !guests || guests === '0 Adult, 0 Kids') {
				if (window.cwcToast) {
					window.cwcToast.show('Please complete your booking selection (dates, room, and guests).', 'warning');
				} else {
					alert('Please complete your booking selection (dates, room, and guests).');
				}
				return;
			}

			if (adultsSelected < 1) {
				if (window.cwcToast) {
					window.cwcToast.show('At least one adult is required to proceed with the booking.', 'warning');
				} else {
					alert('At least one adult is required to proceed with the booking.');
				}
				return;
			}

			// Check room availability before proceeding
			const origText = proceedBtn.textContent;
			proceedBtn.textContent = 'Checking...';
			proceedBtn.disabled = true;

			try {
				const formData = new URLSearchParams();
				formData.append('action', 'cwc_check_room_availability');
				formData.append('room', room);
				formData.append('checkin', checkin);
				formData.append('checkout', checkout);

				const response = await fetch('/wp-admin/admin-ajax.php', { method: 'POST', body: formData });
				const result = await response.json();

				if (result.success && result.data.fully_booked) {
					proceedBtn.textContent = origText;
					proceedBtn.disabled = false;
					if (window.cwcToast) {
						window.cwcToast.show('Sorry, this room is fully booked for your selected dates. Please choose different dates or another room.', 'error');
					} else {
						alert('Sorry, this room is fully booked for your selected dates. Please choose different dates or another room.');
					}
					return;
				}
			} catch (err) {
				console.error('Availability check failed', err);
			}

			proceedBtn.textContent = origText;
			proceedBtn.disabled = false;

			const bookingData = {
				room,
				checkin,
				checkout,
				guests,
			};
			sessionStorage.setItem('cwc_booking_data', JSON.stringify(bookingData));

			window.location.href = '/booking/';
		});

		// ─── Sticky Booking Bar Logic ───
		const bookingBar = document.querySelector('.cwc-booking-bar');
		const footer = document.querySelector('.cwc-footer') || document.querySelector('footer');
		const barWrap = document.querySelector('.cwc-book-hero__images-wrap');

		if (bookingBar && barWrap) {
			const handleScroll = () => {
				if (window.innerWidth <= 1024) {
					bookingBar.classList.remove('is-sticky');
					bookingBar.style.top = '';
					return;
				}

				const scrollY = window.scrollY;
				const barRect = barWrap.getBoundingClientRect();
				const barWrapBottom = barRect.bottom + scrollY;

				const triggerPoint = barWrapBottom - 77;
				const headerHeight = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--cwc-header-h')) || 115;

				const footerTop = footer ? footer.getBoundingClientRect().top + scrollY : document.body.scrollHeight;
				const barHeight = bookingBar.offsetHeight;

				if (scrollY + headerHeight > triggerPoint) {
					bookingBar.classList.add('is-sticky');

					const stickyTop = headerHeight;
					if (scrollY + stickyTop + barHeight + 40 > footerTop) {
						const offset = (scrollY + stickyTop + barHeight + 40) - footerTop;
						bookingBar.style.top = `${stickyTop - offset}px`;
					} else {
						bookingBar.style.top = `${stickyTop}px`;
					}
				} else {
					bookingBar.classList.remove('is-sticky');
					bookingBar.style.top = '';
				}
			};

			window.addEventListener('scroll', handleScroll);
			window.addEventListener('resize', handleScroll);
			handleScroll();
		}
	});
})();
