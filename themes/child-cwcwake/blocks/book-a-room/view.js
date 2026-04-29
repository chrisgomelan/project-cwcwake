( () => {
	document.addEventListener( 'DOMContentLoaded', () => {
		const backdrop = document.getElementById( 'cwc-modal-backdrop' );
		if ( ! backdrop ) {
			return;
		}

		const modals = document.querySelectorAll( '.cwc-booking-modal' );
		
		// Move backdrop to body
		document.body.appendChild( backdrop );
		
		// Prevent clicks inside modal from bubbling up and re-triggering the open event
		modals.forEach( modal => {
			modal.addEventListener( 'click', ( e ) => e.stopPropagation() );
		} );

		const triggers = document.querySelectorAll( '.cwc-booking-bar__field[data-modal-target]' );
		
		const closeModal = () => {
			backdrop.classList.remove( 'is-active' );
			modals.forEach( ( modal ) => modal.classList.remove( 'is-active' ) );
		};

		backdrop.addEventListener( 'click', closeModal );

		triggers.forEach( ( trigger ) => {
			trigger.addEventListener( 'click', () => {
				const targetId = `cwc-modal-${ trigger.getAttribute( 'data-modal-target' ) }`;
				const modal = document.getElementById( targetId );
				if ( modal ) {
					closeModal();
					backdrop.classList.add( 'is-active' );
					
					// Append modal to the clicked field so it stays perfectly attached
					trigger.appendChild( modal );
					modal.classList.add( 'is-active' );
					
					// Clear any inline styles from previous iterations
					modal.style.position = '';
					modal.style.top = '';
					modal.style.left = '';
					modal.style.transform = '';
				} else if ( trigger.getAttribute( 'data-modal-target' ) === 'date' ) {
					// Date modal could be implemented with a library like flatpickr.
					// For now we'll just simulate selecting a date.
					const checkin = document.getElementById( 'cwc-val-checkin' );
					const checkout = document.getElementById( 'cwc-val-checkout' );
					if ( checkin ) checkin.textContent = 'Nov 1, 2026';
					if ( checkout ) checkout.textContent = 'Nov 5, 2026';
				}
			} );
		} );

		// Confirm buttons in modals
		document.querySelectorAll( '.cwc-booking-modal__confirm' ).forEach( ( btn ) => {
			btn.addEventListener( 'click', () => {
				const activeModal = btn.closest( '.cwc-booking-modal' );
				if ( ! activeModal ) return;

				if ( activeModal.id === 'cwc-modal-room' ) {
					const selectedRadio = activeModal.querySelector( 'input[type="radio"]:checked' );
					if ( selectedRadio ) {
						const roomVal = document.getElementById( 'cwc-val-room' );
						if ( roomVal ) roomVal.textContent = selectedRadio.value;
						
						// Enforce capacity on existing guest selection
						const maxCap = parseInt( selectedRadio.dataset.capacity || 4, 10 );
						const adultsEl = document.getElementById( 'cwc-val-modal-adults' );
						const kidsEl = document.getElementById( 'cwc-val-modal-kids' );
						if ( adultsEl && kidsEl ) {
							let adults = parseInt( adultsEl.textContent, 10 );
							let kids = parseInt( kidsEl.textContent, 10 );
							if ( adults + kids > maxCap ) {
								adultsEl.textContent = Math.min( adults, maxCap );
								kidsEl.textContent = Math.max( 0, maxCap - parseInt( adultsEl.textContent, 10 ) );
								
								// Update summary
								const guestsVal = document.getElementById( 'cwc-val-guests' );
								if ( guestsVal ) {
									guestsVal.textContent = `${ adultsEl.textContent } Adult, ${ kidsEl.textContent } Kids`;
								}
							}
						}
					}
				} else if ( activeModal.id === 'cwc-modal-guests' ) {
					const adults = parseInt( document.getElementById( 'cwc-val-modal-adults' )?.textContent || '0', 10 );
					const kids = parseInt( document.getElementById( 'cwc-val-modal-kids' )?.textContent || '0', 10 );
					const guestsVal = document.getElementById( 'cwc-val-guests' );
					if ( guestsVal ) {
						guestsVal.textContent = `${ adults } Adult, ${ kids } Kids`;
					}
				} else if ( activeModal.id === 'cwc-modal-date' ) {
					if ( selectedStart && selectedEnd ) {
						const options = { month: 'short', day: 'numeric', year: 'numeric' };
						const checkinVal = document.getElementById( 'cwc-val-checkin' );
						const checkoutVal = document.getElementById( 'cwc-val-checkout' );
						if ( checkinVal ) checkinVal.textContent = selectedStart.toLocaleDateString( 'en-US', options );
						if ( checkoutVal ) checkoutVal.textContent = selectedEnd.toLocaleDateString( 'en-US', options );
					}
				}
				
				closeModal();
			} );
		} );

		// ─── Calendar Logic ───
		let currentMonth = new Date();
		let selectedStart = null;
		let selectedEnd = null;

		const calendarGrid = document.getElementById( 'cwc-calendar-grid' );
		const monthYearEl = document.querySelector( '.cwc-calendar__month-year' );
		const prevMonthBtn = document.querySelector( '.cwc-calendar__prev' );
		const nextMonthBtn = document.querySelector( '.cwc-calendar__next' );

		const renderCalendar = () => {
			if ( ! calendarGrid || ! monthYearEl ) return;

			calendarGrid.innerHTML = '';
			const year = currentMonth.getFullYear();
			const month = currentMonth.getMonth();

			monthYearEl.textContent = new Intl.DateTimeFormat( 'en-US', { month: 'long', year: 'numeric' } ).format( currentMonth );

			const firstDayOfMonth = new Date( year, month, 1 ).getDay();
			const daysInMonth = new Date( year, month + 1, 0 ).getDate();

			// Monday start adjustment (M=1, ..., Sat=6, Sun=0)
			let startOffset = firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1;

			for ( let i = 0; i < startOffset; i++ ) {
				const emptyDiv = document.createElement( 'div' );
				emptyDiv.className = 'cwc-calendar__day cwc-calendar__day--empty';
				calendarGrid.appendChild( emptyDiv );
			}

			const today = new Date();
			today.setHours( 0, 0, 0, 0 );

			for ( let day = 1; day <= daysInMonth; day++ ) {
				const dateObj = new Date( year, month, day );
				const dayDiv = document.createElement( 'div' );
				dayDiv.className = 'cwc-calendar__day';
				dayDiv.textContent = day;

				if ( dateObj < today ) {
					dayDiv.classList.add( 'cwc-calendar__day--disabled' );
				}

				if ( dateObj.getTime() === today.getTime() ) {
					dayDiv.classList.add( 'cwc-calendar__day--today' );
				}

				if ( selectedStart && dateObj.getTime() === selectedStart.getTime() ) {
					dayDiv.classList.add( 'cwc-calendar__day--selected' );
					dayDiv.classList.add( 'cwc-calendar__day--range-start' );
				}
				if ( selectedEnd && dateObj.getTime() === selectedEnd.getTime() ) {
					dayDiv.classList.add( 'cwc-calendar__day--selected' );
					dayDiv.classList.add( 'cwc-calendar__day--range-end' );
				}
				if ( selectedStart && selectedEnd && dateObj > selectedStart && dateObj < selectedEnd ) {
					dayDiv.classList.add( 'cwc-calendar__day--in-range' );
				}

				dayDiv.addEventListener( 'click', () => {
					if ( dayDiv.classList.contains( 'cwc-calendar__day--disabled' ) ) return;

					if ( ! selectedStart || ( selectedStart && selectedEnd ) ) {
						selectedStart = dateObj;
						selectedEnd = null;
					} else if ( dateObj < selectedStart ) {
						selectedStart = dateObj;
					} else if ( dateObj.getTime() === selectedStart.getTime() ) {
						selectedStart = null;
					} else {
						selectedEnd = dateObj;
					}
					renderCalendar();
				} );

				calendarGrid.appendChild( dayDiv );
			}
		};

		prevMonthBtn?.addEventListener( 'click', ( e ) => {
			e.stopPropagation();
			currentMonth.setMonth( currentMonth.getMonth() - 1 );
			renderCalendar();
		} );

		nextMonthBtn?.addEventListener( 'click', ( e ) => {
			e.stopPropagation();
			currentMonth.setMonth( currentMonth.getMonth() + 1 );
			renderCalendar();
		} );

		renderCalendar();

		// Counter functionality for Guests Modal
		const updateCounter = ( target, delta ) => {
			const el = document.getElementById( `cwc-val-modal-${ target }` );
			const adultsEl = document.getElementById( 'cwc-val-modal-adults' );
			const kidsEl = document.getElementById( 'cwc-val-modal-kids' );
			
			if ( el && adultsEl && kidsEl ) {
				let maxCapacity = 10;
				const selectedRoom = document.querySelector( 'input[name="cwc_room_type"]:checked' );
				
				if ( selectedRoom && selectedRoom.dataset.capacity ) {
					maxCapacity = parseInt( selectedRoom.dataset.capacity, 10 );
				} else {
					const allRooms = Array.from( document.querySelectorAll( 'input[name="cwc_room_type"]' ) );
					if ( allRooms.length > 0 ) {
						maxCapacity = Math.max( ...allRooms.map( r => parseInt( r.dataset.capacity || 4, 10 ) ) );
					}
				}

				let adults = parseInt( adultsEl.textContent, 10 );
				let kids = parseInt( kidsEl.textContent, 10 );
				
				let val = parseInt( el.textContent, 10 ) + delta;
				if ( val < 0 ) val = 0;
				
				const newTotal = ( target === 'adults' ? val : adults ) + ( target === 'kids' ? val : kids );
				
				if ( newTotal <= maxCapacity ) {
					el.textContent = val;
				}
			}
		};

		document.querySelectorAll( '.cwc-booking-modal__btn-inc' ).forEach( ( btn ) => {
			btn.addEventListener( 'click', ( e ) => {
				e.preventDefault();
				updateCounter( btn.getAttribute( 'data-target' ), 1 );
			} );
		} );

		document.querySelectorAll( '.cwc-booking-modal__btn-dec' ).forEach( ( btn ) => {
			btn.addEventListener( 'click', ( e ) => {
				e.preventDefault();
				updateCounter( btn.getAttribute( 'data-target' ), -1 );
			} );
		} );
		// Proceed button → navigate to booking flow
		const proceedBtn = document.querySelector( '.cwc-booking-bar__proceed' );
		proceedBtn?.addEventListener( 'click', () => {
			const room     = document.getElementById( 'cwc-val-room' )?.textContent || '';
			const checkin  = document.getElementById( 'cwc-val-checkin' )?.textContent || '';
			const checkout = document.getElementById( 'cwc-val-checkout' )?.textContent || '';
			const guests   = document.getElementById( 'cwc-val-guests' )?.textContent || '';

			const params = new URLSearchParams( {
				room,
				checkin,
				checkout,
				guests,
			} );

			window.location.href = `/booking/?${ params.toString() }`;
		} );

		// ─── Sticky Booking Bar Logic ───
		const bookingBar = document.querySelector( '.cwc-booking-bar' );
		const footer = document.querySelector( '.cwc-footer' ) || document.querySelector( 'footer' );
		const barWrap = document.querySelector( '.cwc-book-hero__images-wrap' );

		if ( bookingBar && barWrap ) {
			const handleScroll = () => {
				const scrollY = window.scrollY;
				const barRect = barWrap.getBoundingClientRect();
				const barWrapBottom = barRect.bottom + scrollY;
				
				const triggerPoint = barWrapBottom - 77; 
				const headerHeight = parseInt( getComputedStyle( document.documentElement ).getPropertyValue( '--cwc-header-h' ) ) || 115;
				
				const footerTop = footer ? footer.getBoundingClientRect().top + scrollY : document.body.scrollHeight;
				const barHeight = bookingBar.offsetHeight;

				if ( scrollY + headerHeight > triggerPoint ) {
					bookingBar.classList.add( 'is-sticky' );
					
					const stickyTop = headerHeight;
					if ( scrollY + stickyTop + barHeight + 40 > footerTop ) {
						const offset = ( scrollY + stickyTop + barHeight + 40 ) - footerTop;
						bookingBar.style.top = `${ stickyTop - offset }px`;
					} else {
						bookingBar.style.top = `${ stickyTop }px`;
					}
				} else {
					bookingBar.classList.remove( 'is-sticky' );
					bookingBar.style.top = '';
				}
			};

			window.addEventListener( 'scroll', handleScroll );
			window.addEventListener( 'resize', handleScroll );
			handleScroll();
		}
	} );
} )();
