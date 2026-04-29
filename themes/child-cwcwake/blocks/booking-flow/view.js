/**
 * Booking Flow — view.js
 *
 * Drives the multi-step booking flow:
 *   Step 2 → Guest Details
 *   Step 3 → Payment Details
 *
 * Manages: step navigation, additional guest rows, payment method
 * toggling, modal open/close and save actions, and Booking Summary
 * live updates.
 *
 * @package ChildCwcwake
 */

( () => {
	document.addEventListener( 'DOMContentLoaded', () => {
		/* ─── DOM refs ─── */
		const block = document.querySelector( '.wp-block-cwc-booking-flow' );
		if ( ! block ) {
			return;
		}

		const stepGuest   = block.querySelector( '#bf-step-guest' );
		const stepPayment = block.querySelector( '#bf-step-payment' );
		const btnToPayment = block.querySelector( '#bf-to-payment' );
		const btnConfirmPay = block.querySelector( '#bf-confirm-pay' );

		/* Progress */
		const progressSteps = block.querySelectorAll( '.bf-progress__step' );
		const progressLines = block.querySelectorAll( '.bf-progress__line' );

		/* Summary refs */
		const summaryDetails    = block.querySelector( '#bf-summary-details' );
		const summaryName       = block.querySelector( '#bf-summary-name' );
		const summaryPhone      = block.querySelector( '#bf-summary-phone' );
		const summaryEmail      = block.querySelector( '#bf-summary-email' );
		const summaryCheckin    = block.querySelector( '#bf-summary-checkin' );
		const summaryCheckout   = block.querySelector( '#bf-summary-checkout' );
		const summaryGuests     = block.querySelector( '#bf-summary-guests' );
		const summaryRoomName   = block.querySelector( '#bf-summary-room-name' );
		const summaryRoomType   = block.querySelector( '#bf-summary-room-type' );
		const summaryPrice      = block.querySelector( '#bf-summary-price' );

		/* Form inputs */
		const fullNameInput = block.querySelector( '#bf-fullname' );
		const emailInput    = block.querySelector( '#bf-email' );
		const phoneInput    = block.querySelector( '#bf-phone' );

		/* Additional guests */
		const addGuestBtn  = block.querySelector( '#bf-add-guest' );
		const guestContainer = block.querySelector( '#bf-additional-guests' );
		const capacityCount  = block.querySelector( '#bf-capacity-count' );
		let additionalGuests = [];
		let maxCapacity = 4;

		/* Modals */
		const backdrop = block.querySelector( '#bf-modal-backdrop' );

		/* Payment method toggle */
		const cardForm  = block.querySelector( '#bf-card-form' );
		const gcashQr   = block.querySelector( '#bf-gcash-qr' );
		const paymentRadios = block.querySelectorAll( 'input[name="bf_payment_method"]' );

		/* ═══════════════════════════════════════
		   Step Navigation
		   ═══════════════════════════════════════ */

		const showStep = ( step ) => {
			if ( step === 2 ) {
				stepGuest.classList.add( 'is-active' );
				stepPayment.classList.remove( 'is-active' );
				summaryDetails?.classList.remove( 'is-visible' );

				/* Progress: step 1 done, step 2 active, step 3 upcoming */
				updateProgress( 2 );
			} else if ( step === 3 ) {
				stepGuest.classList.remove( 'is-active' );
				stepPayment.classList.add( 'is-active' );
				summaryDetails?.classList.add( 'is-visible' );

				/* Populate summary details */
				if ( summaryName )  summaryName.textContent  = fullNameInput?.value || '—';
				if ( summaryPhone ) summaryPhone.textContent = `+63${ phoneInput?.value || '' }`;
				if ( summaryEmail ) summaryEmail.textContent = emailInput?.value || '—';

				updateProgress( 3 );
			}
			window.scrollTo( { top: 0, behavior: 'smooth' } );
		};

		const updateProgress = ( activeStep ) => {
			progressSteps.forEach( ( el ) => {
				const s = parseInt( el.dataset.step, 10 );
				const circle = el.querySelector( '.bf-progress__circle' );

				el.classList.remove( 'bf-progress__step--done', 'bf-progress__step--active', 'bf-progress__step--upcoming' );
				circle.classList.remove( 'bf-progress__circle--done', 'bf-progress__circle--active', 'bf-progress__circle--upcoming' );

				if ( s < activeStep ) {
					el.classList.add( 'bf-progress__step--done' );
					circle.classList.add( 'bf-progress__circle--done' );
					circle.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" fill="#fff"/></svg>';
				} else if ( s === activeStep ) {
					el.classList.add( 'bf-progress__step--active' );
					circle.classList.add( 'bf-progress__circle--active' );
					circle.textContent = String( s );
				} else {
					el.classList.add( 'bf-progress__step--upcoming' );
					circle.classList.add( 'bf-progress__circle--upcoming' );
					circle.textContent = String( s );
				}
			} );

			progressLines.forEach( ( line, i ) => {
				line.classList.remove( 'bf-progress__line--done', 'bf-progress__line--upcoming' );
				if ( i < activeStep - 1 ) {
					line.classList.add( 'bf-progress__line--done' );
				} else {
					line.classList.add( 'bf-progress__line--upcoming' );
				}
			} );
		};

		btnToPayment?.addEventListener( 'click', () => {
			/* Basic validation */
			if ( ! fullNameInput?.value?.trim() ) {
				fullNameInput?.focus();
				return;
			}
			if ( ! emailInput?.value?.trim() ) {
				emailInput?.focus();
				return;
			}
			showStep( 3 );
		} );

		btnConfirmPay?.addEventListener( 'click', () => {
			/* eslint-disable-next-line no-alert */
			alert( 'Booking confirmed! You will receive a confirmation email shortly.' );
		} );

		/* ═══════════════════════════════════════
		   Additional Guests
		   ═══════════════════════════════════════ */

		const updateCapacityUI = () => {
			const count = 1 + additionalGuests.length;
			if ( capacityCount ) {
				capacityCount.textContent = `${ count }/${ maxCapacity }`;
			}
		};

		const renderGuestRows = () => {
			if ( ! guestContainer ) return;
			guestContainer.innerHTML = '';

			if ( additionalGuests.length > 0 ) {
				const header = document.createElement( 'h3' );
				header.className = 'bf-panel__sub-label';
				header.style.marginBottom = '16px';
				header.textContent = 'Additional Guest';
				guestContainer.appendChild( header );
			}

			additionalGuests.forEach( ( name, idx ) => {
				const row = document.createElement( 'div' );
				row.className = 'bf-guest-row';
				row.innerHTML = `
					<div class="bf-field">
						<label class="bf-field__label">Full Name <span class="bf-field__req">*</span></label>
						<input type="text" class="bf-field__input bf-guest-name" data-idx="${ idx }" placeholder="Last Name, First Name" value="${ name }">
					</div>
					<button class="bf-guest-row__remove" data-idx="${ idx }" type="button">
						<img src="/wp-content/uploads/2026/04/remove-guest.svg" alt="Remove" width="24" height="24">
					</button>
				`;
				guestContainer.appendChild( row );
			} );

			/* Bind events */
			guestContainer.querySelectorAll( '.bf-guest-name' ).forEach( ( input ) => {
				input.addEventListener( 'input', ( e ) => {
					const i = parseInt( e.target.dataset.idx, 10 );
					additionalGuests[ i ] = e.target.value;
				} );
			} );

			guestContainer.querySelectorAll( '.bf-guest-row__remove' ).forEach( ( btn ) => {
				btn.addEventListener( 'click', ( e ) => {
					const i = parseInt( e.currentTarget.dataset.idx, 10 );
					additionalGuests.splice( i, 1 );
					renderGuestRows();
					updateCapacityUI();
				} );
			} );
		};

		addGuestBtn?.addEventListener( 'click', () => {
			if ( 1 + additionalGuests.length >= maxCapacity ) return;
			additionalGuests.push( '' );
			renderGuestRows();
			updateCapacityUI();
		} );

		/* ═══════════════════════════════════════
		   Payment Method Toggle
		   ═══════════════════════════════════════ */

		const togglePaymentUI = () => {
			const selected = block.querySelector( 'input[name="bf_payment_method"]:checked' )?.value;
			if ( cardForm ) {
				cardForm.classList.toggle( 'is-visible', selected === 'visa' || selected === 'mastercard' );
			}
			if ( gcashQr ) {
				gcashQr.classList.toggle( 'is-visible', selected === 'gcash' );
			}
		};

		paymentRadios.forEach( ( radio ) => {
			radio.addEventListener( 'change', togglePaymentUI );
		} );
		togglePaymentUI();

		/* ─── Calendar Logic ─── */
		let currentMonth = new Date( 2026, 7, 1 ); // Start at August 2026
		let selectedStart = new Date( 2026, 7, 14 );
		let selectedEnd = new Date( 2026, 7, 19 );

		const calendarGrid = block.querySelector( '#bf-calendar-grid' );
		const monthYearEl  = block.querySelector( '.bf-calendar__month-year' );
		const prevMonthBtn = block.querySelector( '.bf-calendar__prev' );
		const nextMonthBtn = block.querySelector( '.bf-calendar__next' );

		const calendarEl   = block.querySelector( '#bf-modal-calendar' );
		const gridCheckin  = block.querySelector( '#bf-modal-trigger-checkin' );
		const gridCheckout = block.querySelector( '#bf-modal-trigger-checkout' );

		const renderCalendar = () => {
			if ( ! calendarGrid || ! monthYearEl ) return;

			calendarGrid.innerHTML = '';
			const year = currentMonth.getFullYear();
			const month = currentMonth.getMonth();

			monthYearEl.textContent = new Intl.DateTimeFormat( 'en-US', { month: 'long', year: 'numeric' } ).format( currentMonth );

			const firstDayOfMonth = new Date( year, month, 1 ).getDay();
			const daysInMonth = new Date( year, month + 1, 0 ).getDate();

			// Monday start adjustment
			let startOffset = firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1;

			for ( let i = 0; i < startOffset; i++ ) {
				const emptyDiv = document.createElement( 'div' );
				emptyDiv.className = 'bf-calendar__day bf-calendar__day--empty';
				calendarGrid.appendChild( emptyDiv );
			}

			const today = new Date();
			today.setHours( 0, 0, 0, 0 );

			for ( let day = 1; day <= daysInMonth; day++ ) {
				const dateObj = new Date( year, month, day );
				const dayDiv = document.createElement( 'div' );
				dayDiv.className = 'bf-calendar__day';
				dayDiv.textContent = day;

				if ( dateObj < today ) {
					dayDiv.classList.add( 'bf-calendar__day--disabled' );
				}

				if ( selectedStart && dateObj.getTime() === selectedStart.getTime() ) {
					dayDiv.classList.add( 'bf-calendar__day--selected' );
					dayDiv.classList.add( 'bf-calendar__day--range-start' );
				}
				if ( selectedEnd && dateObj.getTime() === selectedEnd.getTime() ) {
					dayDiv.classList.add( 'bf-calendar__day--selected' );
					dayDiv.classList.add( 'bf-calendar__day--range-end' );
				}
				if ( selectedStart && selectedEnd && dateObj > selectedStart && dateObj < selectedEnd ) {
					dayDiv.classList.add( 'bf-calendar__day--in-range' );
				}

				dayDiv.addEventListener( 'click', () => {
					if ( dayDiv.classList.contains( 'bf-calendar__day--disabled' ) ) return;

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
					
					/* Update modal pills immediately */
					updateModalPills();
					renderCalendar();
				} );

				calendarGrid.appendChild( dayDiv );
			}
		};

		gridCheckin?.addEventListener( 'click', () => {
			calendarEl?.classList.add( 'is-open' );
			renderCalendar();
		} );

		gridCheckout?.addEventListener( 'click', () => {
			calendarEl?.classList.add( 'is-open' );
			renderCalendar();
		} );

		const updateModalPills = () => {
			const valCheckin = block.querySelector( '#bf-modal-val-checkin' );
			const valCheckout = block.querySelector( '#bf-modal-val-checkout' );
			if ( valCheckin ) valCheckin.textContent = selectedStart ? formatDate( selectedStart.toISOString().split( 'T' )[ 0 ] ) : 'Select Date';
			if ( valCheckout ) valCheckout.textContent = selectedEnd ? formatDate( selectedEnd.toISOString().split( 'T' )[ 0 ] ) : 'Select Date';
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

		/* ═══════════════════════════════════════
		   Modals
		   ═══════════════════════════════════════ */

		const openModal = ( id ) => {
			backdrop?.classList.add( 'is-active' );
			block.querySelector( `#bf-modal-${ id }` )?.classList.add( 'is-active' );
			if ( id === 'trip-summary' ) {
				calendarEl?.classList.remove( 'is-open' );
				updateModalPills();
			}
		};

		const closeAllModals = () => {
			backdrop?.classList.remove( 'is-active' );
			block.querySelectorAll( '.bf-modal' ).forEach( ( m ) => m.classList.remove( 'is-active' ) );
		};

		backdrop?.addEventListener( 'click', closeAllModals );

		block.querySelectorAll( '.bf-modal__close' ).forEach( ( btn ) => {
			btn.addEventListener( 'click', closeAllModals );
		} );

		/* Edit links in summary → open modals */
		block.querySelectorAll( '.bf-summary__edit-link[data-modal]' ).forEach( ( btn ) => {
			btn.addEventListener( 'click', () => {
				const modalId = btn.dataset.modal;
				/* Pre-fill Personal Info modal */
				if ( modalId === 'personal-info' ) {
					const piName  = block.querySelector( '#bf-modal-pi-name' );
					const piEmail = block.querySelector( '#bf-modal-pi-email' );
					const piPhone = block.querySelector( '#bf-modal-pi-phone' );
					if ( piName )  piName.value  = fullNameInput?.value || '';
					if ( piEmail ) piEmail.value = emailInput?.value || '';
					if ( piPhone ) piPhone.value = phoneInput?.value || '';
				}
				/* Pre-fill Additional Guests modal */
				if ( modalId === 'additional-guests' ) {
					const list = block.querySelector( '#bf-modal-guests-list' );
					if ( list ) {
						list.innerHTML = '';
						additionalGuests.forEach( ( name, idx ) => {
							const row = document.createElement( 'div' );
							row.className = 'bf-modal__guest-row';
							row.innerHTML = `
								<div class="bf-modal__guest-row-header">
									<span class="bf-modal__guest-row-label">Full Name</span>
									<button class="bf-modal__guest-edit" type="button">
										<img src="/wp-content/uploads/2026/04/pencil-edit.svg" alt="" width="14" height="14">
										Edit
									</button>
								</div>
								<input type="text" class="bf-field__input bf-modal-guest-name" data-idx="${ idx }" value="${ name || `Additional Guest ${ idx + 1 }` }">
							`;
							list.appendChild( row );
						} );
					}
				}
				openModal( modalId );
			} );
		} );

		/* Modal save handlers */
		block.querySelectorAll( '.bf-modal__save' ).forEach( ( btn ) => {
			btn.addEventListener( 'click', () => {
				const modal = btn.closest( '.bf-modal' );
				if ( ! modal ) {
					closeAllModals();
					return;
				}

				/* Trip Summary save */
				if ( modal.id === 'bf-modal-trip-summary' ) {
					if ( selectedStart && summaryCheckin ) {
						summaryCheckin.textContent = formatDate( selectedStart.toISOString().split( 'T' )[ 0 ] );
					}
					if ( selectedEnd && summaryCheckout ) {
						summaryCheckout.textContent = formatDate( selectedEnd.toISOString().split( 'T' )[ 0 ] );
					}
				}

				/* Selected Room save */
				if ( modal.id === 'bf-modal-selected-room' ) {
					const checked = modal.querySelector( 'input[name="bf_modal_room"]:checked' );
					if ( checked ) {
						const roomName = checked.value;
						const price    = checked.dataset.price || '';
						const cap      = checked.dataset.capacity || '4';
						if ( summaryRoomName ) summaryRoomName.textContent = `${ roomName } Room`;
						if ( summaryRoomType ) summaryRoomType.textContent = `${ roomName } Room`;
						if ( summaryPrice )    summaryPrice.textContent    = `₱ ${ price.replace( /[^0-9,.\s]/g, '' ).trim() }.00`;
						maxCapacity = parseInt( cap, 10 ) || 4;
						updateCapacityUI();
					}
				}

				/* Personal Information save */
				if ( modal.id === 'bf-modal-personal-info' ) {
					const n = block.querySelector( '#bf-modal-pi-name' )?.value || '';
					const e = block.querySelector( '#bf-modal-pi-email' )?.value || '';
					const p = block.querySelector( '#bf-modal-pi-phone' )?.value || '';
					if ( fullNameInput ) fullNameInput.value = n;
					if ( emailInput )    emailInput.value    = e;
					if ( phoneInput )    phoneInput.value    = p;
					if ( summaryName )   summaryName.textContent  = n || '—';
					if ( summaryEmail )  summaryEmail.textContent = e || '—';
					if ( summaryPhone )  summaryPhone.textContent = p ? `+63${ p }` : '—';
				}

				/* Additional Guests save */
				if ( modal.id === 'bf-modal-additional-guests' ) {
					modal.querySelectorAll( '.bf-modal-guest-name' ).forEach( ( input ) => {
						const i = parseInt( input.dataset.idx, 10 );
						additionalGuests[ i ] = input.value;
					} );
					renderGuestRows();
				}

				closeAllModals();
			} );
		} );

		/* ─── Helpers ─── */
		const formatDate = ( isoDate ) => {
			if ( ! isoDate ) return '';
			const d = new Date( isoDate + 'T00:00:00' );
			const mm = String( d.getMonth() + 1 ).padStart( 2, '0' );
			const dd = String( d.getDate() ).padStart( 2, '0' );
			return `${ mm }/${ dd }/${ d.getFullYear() }`;
		};

		/* ─── Init ─── */
		showStep( 2 );
		updateCapacityUI();
	} );
} )();
