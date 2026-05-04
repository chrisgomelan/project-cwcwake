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
		const summaryRoomDesc   = block.querySelector( '#bf-summary-room-desc' );
		const summaryAmenities  = block.querySelector( '#bf-summary-amenities' );
		const summaryCapacity   = block.querySelector( '#bf-summary-capacity' );
		const summaryPrice      = block.querySelector( '#bf-summary-price' );
		const summaryRoomImg    = block.querySelector( '.bf-summary__room-img' );

		const summaryMobileRoom     = block.querySelector( '#bf-summary-mobile-room' );
		const summaryMobileCheckin  = block.querySelector( '#bf-summary-mobile-checkin' );
		const summaryMobileCheckout = block.querySelector( '#bf-summary-mobile-checkout' );
		const summaryMobilePrice    = block.querySelector( '#bf-summary-mobile-price' );

		/* Mobile Summary Toggle */
		const summaryToggleBtn = block.querySelector( '#bf-summary-toggle' );
		const summaryAside     = block.querySelector( '#bf-summary' );
		if ( summaryToggleBtn && summaryAside ) {
			summaryToggleBtn.addEventListener( 'click', () => {
				summaryAside.classList.toggle( 'is-expanded' );
			} );
		}

		/* Form inputs */
		const fullNameInput = block.querySelector( '#bf-fullname' );
		const emailInput    = block.querySelector( '#bf-email' );
		const phoneInput    = block.querySelector( '#bf-phone' );

		/* ─── Country Code Selector ─── */
		const countrySelector = block.querySelector( '#bf-country-selector' );
		const countryDropdown = block.querySelector( '#bf-country-dropdown' );
		const selectedFlag    = block.querySelector( '#bf-selected-flag' );
		const selectedCode    = block.querySelector( '#bf-selected-code' );
		const dialCodeInput   = block.querySelector( '#bf-dial-code' );
		const countryFlagInput = block.querySelector( '#bf-country-flag' );

		let countries = [];

		async function initCountrySelector() {
			try {
				const themeUrl = block.dataset.themeUrl;
				const response = await fetch( themeUrl + '/blocks/booking-flow/countries.json' );
				if ( response.ok ) {
					countries = await response.json();
					renderCountryDropdown();
				}
			} catch ( e ) {
				console.error( 'Failed to load countries', e );
			}
		}

		function renderCountryDropdown() {
			if ( ! countryDropdown ) return;
			countryDropdown.innerHTML = countries.map( country => `
				<div class="bf-country-item" data-code="${country.code}" data-iso="${country.iso}" data-placeholder="${country.placeholder}">
					<img src="https://flagcdn.com/w20/${country.iso}.png" width="20">
					<span>${country.name} (${country.code})</span>
				</div>
			` ).join( '' );

			countryDropdown.querySelectorAll( '.bf-country-item' ).forEach( item => {
				item.addEventListener( 'click', () => {
					const code = item.dataset.code;
					const iso  = item.dataset.iso;
					const placeholder = item.dataset.placeholder;

					if ( selectedFlag ) selectedFlag.innerHTML = `<img src="https://flagcdn.com/w20/${iso}.png" width="20" style="border-radius: 2px;">`;
					if ( selectedCode ) selectedCode.textContent = code;
					if ( dialCodeInput ) dialCodeInput.value = code;
					if ( countryFlagInput ) countryFlagInput.value = iso;
					if ( phoneInput ) {
						phoneInput.placeholder = placeholder;
						const maxDigits = placeholder.replace(/\D/g, '').length;
						phoneInput.maxLength = maxDigits;
						
						// Truncate existing value if it exceeds new limit
						let val = phoneInput.value.replace(/\D/g, '');
						if ( val.length > maxDigits ) {
							phoneInput.value = val.substring( 0, maxDigits );
						}
					}

					countryDropdown.classList.remove( 'is-open' );
				} );
			} );
		}

		if ( countrySelector ) {
			const selBtn = countrySelector.querySelector( '.bf-field__country-code' );
			selBtn?.addEventListener( 'click', ( e ) => {
				e.preventDefault();
				e.stopPropagation();
				countryDropdown?.classList.toggle( 'is-open' );
			} );

			document.addEventListener( 'click', () => {
				countryDropdown?.classList.remove( 'is-open' );
			} );
		}

		initCountrySelector();

		/* ─── Phone Input Restriction ─── */
		phoneInput?.addEventListener( 'input', ( e ) => {
			const placeholder = phoneInput.placeholder || '912 345 6789';
			const maxDigits = placeholder.replace(/\D/g, '').length;
			
			// Sync maxlength attribute
			phoneInput.maxLength = maxDigits;
			
			// Remove non-digits
			let val = e.target.value.replace(/\D/g, '');
			
			// Truncate if over limit
			if ( val.length > maxDigits ) {
				val = val.substring( 0, maxDigits );
			}
			
			e.target.value = val;
		} );

		function showError( el, message ) {
			const field = el.closest( '.bf-field, .bf-guest-row__inner' );
			if ( ! field ) return;
			field.classList.add( 'is-invalid' );
			let err = field.querySelector( '.bf-field-error' );
			if ( ! err ) {
				err = document.createElement( 'div' );
				err.className = 'bf-field-error';
				field.appendChild( err );
			}
			err.textContent = message;
		}

		function clearErrors() {
			block.querySelectorAll( '.is-invalid' ).forEach( el => el.classList.remove( 'is-invalid' ) );
			block.querySelectorAll( '.bf-field-error' ).forEach( el => el.textContent = '' );
		}

		/* ─── Data Loading ─── */
		const loadBookingData = () => {
			let data = null;
			
			// Try sessionStorage first
			const sessionData = sessionStorage.getItem('cwc_booking_data');
			if (sessionData) {
				try {
					data = JSON.parse(sessionData);
				} catch (e) {
					console.error('Failed to parse session data', e);
				}
			}

			// Fallback to URL params for backward compatibility or direct links
			if (!data) {
				const urlParams = new URLSearchParams(window.location.search);
				if (urlParams.get('room')) {
					data = {
						room: urlParams.get('room'),
						checkin: urlParams.get('checkin'),
						checkout: urlParams.get('checkout'),
						guests: urlParams.get('guests') || '1 Adult, 0 Kids'
					};
				}
			}

			return data;
		};

		const bookingData = loadBookingData();
		const mainContent = block.querySelector('#bf-main-content');
		const incompleteMsg = block.querySelector('#bf-incomplete-message');

		if (!bookingData) {
			if (mainContent) mainContent.style.display = 'none';
			if (incompleteMsg) incompleteMsg.style.display = 'block';
			return; // Stop initialization
		}

		// If we have data, ensure content is visible
		if (mainContent) mainContent.style.display = 'block';
		if (incompleteMsg) incompleteMsg.style.display = 'none';

		/* ─── Initial State ─── */
		const initialGuestsStr = bookingData.guests;
		let initialAdults = 1;
		let initialKids = 0;
		const guestsMatch = initialGuestsStr.match(/(\d+)\s+Adults?,\s+(\d+)\s+Kids?/i);
		if (guestsMatch) {
			initialAdults = parseInt(guestsMatch[1], 10);
			initialKids = parseInt(guestsMatch[2], 10);
		}
		const totalInitialGuests = initialAdults + initialKids;

		/* Additional guests */
		const addGuestBtn  = block.querySelector( '#bf-add-guest' );
		const guestContainer = block.querySelector( '#bf-additional-guests' );
		const capacityCount  = block.querySelector( '#bf-capacity-count' );
		
		// Initialize additional guests based on initial counts
		let additionalGuests = [];
		if ( totalInitialGuests > 1 ) {
			// Skip the first adult (primary guest)
			let adultsLeft = initialAdults - 1;
			let kidsLeft = initialKids;
			
			for ( let i = 0; i < totalInitialGuests - 1; i++ ) {
				if ( adultsLeft > 0 ) {
					additionalGuests.push( { name: '', type: 'adult' } );
					adultsLeft--;
				} else {
					additionalGuests.push( { name: '', type: 'kid' } );
					kidsLeft--;
				}
			}
		}
		
		// Initialize maxCapacity from the PHP-rendered summary sidebar
		let maxCapacity = 4;
		const capacityEl = block.querySelector( '#bf-summary-capacity' );
		if ( capacityEl ) {
			maxCapacity = parseInt( capacityEl.textContent, 10 ) || 4;
		}

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
				block.classList.remove( 'bf-step-payment' );

				/* Progress: step 1 done, step 2 active, step 3 upcoming */
				updateProgress( 2 );
			} else if ( step === 3 ) {
				stepGuest.classList.remove( 'is-active' );
				stepPayment.classList.add( 'is-active' );
				summaryDetails?.classList.add( 'is-visible' );
				block.classList.add( 'bf-step-payment' );

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
			clearErrors();
			let hasError = false;

			if ( ! fullNameInput?.value?.trim() ) {
				showError( fullNameInput, 'Full name is required.' );
				hasError = true;
			}
			if ( ! emailInput?.value?.trim() ) {
				showError( emailInput, 'Email address is required.' );
				hasError = true;
			} else if ( ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( emailInput.value.trim() ) ) {
				showError( emailInput, 'Please enter a valid email address.' );
				hasError = true;
			}

			// Phone validation based on digits length in placeholder
			const phoneVal = phoneInput?.value.trim() || '';
			const placeholder = phoneInput?.placeholder || '912 345 6789';
			const expectedDigits = placeholder.replace(/\D/g, '').length;
			const digitsOnly = phoneVal.replace(/\D/g, '');

			if ( ! phoneVal ) {
				showError( phoneInput, 'Phone number is required.' );
				hasError = true;
			} else if ( digitsOnly.length !== expectedDigits ) {
				showError( phoneInput, `Phone number must be exactly ${expectedDigits} digits.` );
				hasError = true;
			}

			// Validate additional guests
			block.querySelectorAll( '.bf-guest-name' ).forEach( ( input ) => {
				if ( ! input.value.trim() ) {
					showError( input, 'Guest full name is required.' );
					hasError = true;
				}
			} );

			if ( hasError ) {
				const firstError = block.querySelector( '.is-invalid' );
				if ( firstError ) {
					firstError.scrollIntoView( { behavior: 'smooth', block: 'center' } );
				}
				return;
			}

			showStep( 3 );
		} );

		btnConfirmPay?.addEventListener( 'click', () => {
			const agree = block.querySelector( '#bf-agree-terms' );
			if ( agree && ! agree.checked ) {
				if ( window.cwcToast ) {
					window.cwcToast.show( 'Please accept the Terms of Use and Privacy Policy to proceed.', 'warning' );
				} else {
					alert( 'Please accept the Terms of Use and Privacy Policy to proceed.' );
				}
				return;
			}

			/* Disable button to prevent double clicks */
			btnConfirmPay.disabled = true;
			btnConfirmPay.textContent = 'Processing...';

			const formData = new URLSearchParams();
			formData.append( 'action', 'cwc_submit_booking' );
			formData.append( 'name', fullNameInput?.value || '' );
			formData.append( 'email', emailInput?.value || '' );
			formData.append( 'phone', phoneInput?.value || '' );
			formData.append( 'checkin', summaryCheckin?.textContent || '' );
			formData.append( 'checkout', summaryCheckout?.textContent || '' );
			formData.append( 'room', summaryRoomName?.textContent || '' );
			formData.append( 'price', summaryPrice?.textContent || '' );
			formData.append( 'nights', String( calculateNights() ) );
			
			const paymentMethod = block.querySelector( 'input[name="bf_payment_method"]:checked' )?.value || '';
			formData.append( 'payment_method', paymentMethod );
			formData.append( 'guests', JSON.stringify( additionalGuests ) );

			fetch( '/wp-admin/admin-ajax.php', {
				method: 'POST',
				body: formData
			} )
			.then( response => response.json() )
			.then( result => {
				if ( result.success ) {
					openModal( 'success' );
					setTimeout( () => {
						window.location.href = '/';
					}, 5000 );
				} else {
					btnConfirmPay.disabled = false;
					btnConfirmPay.textContent = 'Confirm and Pay';
					const msg = result.data?.message || 'There was an issue processing your booking. Please try again.';
					if ( result.data?.fully_booked ) {
						showAvailabilityStatus( { fully_booked: true, available_units: 0 } );
					}
					if ( window.cwcToast ) {
						window.cwcToast.show( msg, 'error' );
					} else {
						alert( msg );
					}
				}
			} )
			.catch( error => {
				console.error( 'Booking Error:', error );
				btnConfirmPay.disabled = false;
				btnConfirmPay.textContent = 'Confirm and Pay';
				if ( window.cwcToast ) {
					window.cwcToast.show( 'There was an issue processing your booking. Please try again.', 'error' );
				} else {
					alert( 'There was an issue processing your booking. Please try again.' );
				}
			} );
		} );

		/* ═══════════════════════════════════════
		   Additional Guests
		   ═══════════════════════════════════════ */

		const updateSummaryGuests = () => {
			if ( ! summaryGuests ) return;
			
			let adults = 1; // Primary guest
			let kids = 0;
			
			additionalGuests.forEach( g => {
				if ( g.type === 'adult' ) adults++;
				else kids++;
			} );
			
			summaryGuests.textContent = `${adults} Adult${adults !== 1 ? 's' : ''}, ${kids} Kid${kids !== 1 ? 's' : ''}`;
		};

		const updateCapacityUI = () => {
			const count = 1 + additionalGuests.length;
			if ( capacityCount ) {
				capacityCount.textContent = `${ count }/${ maxCapacity }`;
			}
			updateSummaryGuests();
		};

		const renderGuestRows = () => {
			if ( ! guestContainer ) return;
			guestContainer.innerHTML = '';

			if ( additionalGuests.length > 0 ) {
				const header = document.createElement( 'h2' );
				header.className = 'bf-panel__title';
				header.style.marginBottom = '24px';
				header.style.marginTop = '40px';
				header.textContent = 'Additional Guest/s';
				guestContainer.appendChild( header );
			}

			additionalGuests.forEach( ( guest, idx ) => {
				const row = document.createElement( 'div' );
				row.className = 'bf-guest-row';
				row.innerHTML = `
					<div class="bf-guest-row__inner">
						<label class="bf-field__label">Full Name <span class="bf-field__req">*</span></label>
						<div class="bf-guest-row__field-group">
							<button class="bf-guest-row__remove" data-idx="${ idx }" type="button">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<circle cx="12" cy="12" r="11" stroke="#0056FF" stroke-width="2"/>
									<path d="M7 12H17" stroke="#0056FF" stroke-width="2" stroke-linecap="round"/>
								</svg>
							</button>
							<div class="bf-guest-row__input-wrap">
								<input type="text" class="bf-field__input bf-guest-name" data-idx="${ idx }" placeholder="Last Name, First Name" value="${ guest.name }">
								<button class="bf-guest-type-toggle bf-guest-type-toggle--${ guest.type }" data-idx="${ idx }" type="button">
									<span class="bf-toggle-text">${ guest.type === 'adult' ? 'Adult' : 'Kid' }</span>
									<span class="bf-toggle-thumb">
										<img src="/wp-content/themes/child-cwcwake/assets/images/${ guest.type }.svg" width="20" height="20">
									</span>
								</button>
							</div>
						</div>
					</div>
				`;
				guestContainer.appendChild( row );
			} );

			/* Bind events */
			guestContainer.querySelectorAll( '.bf-guest-name' ).forEach( ( input ) => {
				input.addEventListener( 'input', ( e ) => {
					const i = parseInt( e.target.dataset.idx, 10 );
					additionalGuests[ i ].name = e.target.value;
				} );
			} );

			guestContainer.querySelectorAll( '.bf-guest-type-toggle' ).forEach( ( btn ) => {
				btn.addEventListener( 'click', ( e ) => {
					const i = parseInt( e.currentTarget.dataset.idx, 10 );
					additionalGuests[ i ].type = additionalGuests[ i ].type === 'adult' ? 'kid' : 'adult';
					renderGuestRows();
					updateSummaryGuests();
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
			if ( 1 + additionalGuests.length >= maxCapacity ) {
				if ( window.cwcToast ) {
					window.cwcToast.show( `Maximum capacity for this room is ${ maxCapacity } persons.`, 'warning' );
				} else {
					alert( `Maximum capacity for this room is ${ maxCapacity } persons.` );
				}
				return;
			}
			additionalGuests.push( { name: '', type: 'adult' } );
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

		/* ─── Nights Calculation & Price ─── */
		let nightlyRate = 0;
		const priceText = summaryPrice?.textContent || '';
		const priceMatch = priceText.replace( /,/g, '' ).match( /[\d.]+/ );
		if ( priceMatch ) {
			nightlyRate = parseFloat( priceMatch[0] );
		}

		let nightsDisplay = block.querySelector( '#bf-summary-nights' );
		if ( ! nightsDisplay ) {
			nightsDisplay = document.createElement( 'div' );
			nightsDisplay.className = 'bf-summary__nights-row';
			nightsDisplay.id = 'bf-summary-nights';
			nightsDisplay.style.cssText = 'display:flex;justify-content:space-between;align-items:center;padding:8px 0;font-size:14px;color:#475569;';

			const totalPriceSection = block.querySelector( '.bf-summary__total' );
			if ( totalPriceSection ) {
				totalPriceSection.parentNode.insertBefore( nightsDisplay, totalPriceSection );
			}
		}

		const calculateNights = () => {
			if ( ! selectedStart || ! selectedEnd ) return 0;
			const diffMs = selectedEnd.getTime() - selectedStart.getTime();
			return Math.max( 0, Math.round( diffMs / ( 1000 * 60 * 60 * 24 ) ) );
		};

		const updateNightsAndPrice = () => {
			const nights = calculateNights();
			if ( nightsDisplay ) {
				nightsDisplay.innerHTML = nights > 0
					? `<span>Duration</span><strong>${ nights } night${ nights !== 1 ? 's' : '' }</strong>`
					: '';
			}
			if ( nights > 0 && nightlyRate > 0 && summaryPrice ) {
				const total = nightlyRate * nights;
				const formatted = `₱ ${ total.toLocaleString( 'en-PH', { minimumFractionDigits: 2 } ) }`;
				summaryPrice.textContent = formatted;
				if ( summaryMobilePrice ) summaryMobilePrice.textContent = formatted;
			}
		};

		/* ─── Availability Check ─── */
		let roomAvailabilityCache = {};

		const checkRoomAvailability = async ( roomName, checkinDate, checkoutDate ) => {
			const cacheKey = `${ roomName }|${ checkinDate }|${ checkoutDate }`;
			if ( roomAvailabilityCache[ cacheKey ] !== undefined ) {
				return roomAvailabilityCache[ cacheKey ];
			}

			try {
				const formData = new URLSearchParams();
				formData.append( 'action', 'cwc_check_room_availability' );
				formData.append( 'room', roomName );
				formData.append( 'checkin', checkinDate );
				formData.append( 'checkout', checkoutDate );

				const response = await fetch( '/wp-admin/admin-ajax.php', {
					method: 'POST',
					body: formData,
				} );
				const result = await response.json();

				if ( result.success ) {
					roomAvailabilityCache[ cacheKey ] = result.data;
					return result.data;
				}
			} catch ( e ) {
				console.error( 'Availability check failed', e );
			}
			return null;
		};

		const showAvailabilityStatus = ( data ) => {
			let indicator = block.querySelector( '#bf-availability-indicator' );
			if ( ! indicator ) {
				indicator = document.createElement( 'div' );
				indicator.id = 'bf-availability-indicator';
				indicator.style.cssText = 'padding:10px 16px;border-radius:8px;font-size:13px;font-weight:600;margin-top:8px;text-align:center;';
				const priceEl = block.querySelector( '.bf-summary__total' );
				if ( priceEl ) {
					priceEl.parentNode.insertBefore( indicator, priceEl );
				}
			}

			if ( ! data ) {
				indicator.style.display = 'none';
				return;
			}

			if ( data.fully_booked ) {
				indicator.style.display = 'block';
				indicator.style.background = '#fef2f2';
				indicator.style.color = '#dc2626';
				indicator.style.border = '1px solid #fecaca';
				indicator.innerHTML = 'This room type is fully booked for your selected dates.';
				if ( btnConfirmPay ) {
					btnConfirmPay.disabled = true;
					btnConfirmPay.style.opacity = '0.5';
					btnConfirmPay.style.cursor = 'not-allowed';
				}
			} else {
				indicator.style.display = 'none';
				if ( btnConfirmPay ) {
					btnConfirmPay.disabled = false;
					btnConfirmPay.style.opacity = '1';
					btnConfirmPay.style.cursor = 'pointer';
				}
			}
		};

		const runAvailabilityCheck = async () => {
			if ( ! selectedStart || ! selectedEnd ) return;
			const roomName = summaryRoomName?.textContent?.replace( / Room$/i, '' ).trim() || '';
			if ( ! roomName ) return;

			const ciStr = selectedStart.toISOString().split( 'T' )[0];
			const coStr = selectedEnd.toISOString().split( 'T' )[0];

			const data = await checkRoomAvailability( roomName, ciStr, coStr );
			showAvailabilityStatus( data );
		};

		/* ─── Calendar Logic ─── */
		let selectedStart = bookingData.checkin ? new Date( bookingData.checkin ) : new Date( 2026, 7, 14 );
		let selectedEnd = bookingData.checkout ? new Date( bookingData.checkout ) : new Date( 2026, 7, 19 );
		let currentMonth = new Date( selectedStart.getTime() );

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
								<div class="bf-modal__guest-row-inner">
									<div class="bf-modal__guest-row-header">
										<span class="bf-modal__guest-row-label">Full Name</span>
										<button class="bf-modal__guest-edit" type="button">
											<img src="/wp-content/uploads/2026/04/pencil-edit.svg" alt="" width="14" height="14">
											Edit
										</button>
									</div>
									<input type="text" class="bf-field__input bf-modal-guest-name" data-idx="${ idx }" value="${ name || `Additional Guest ${ idx + 1 }` }">
								</div>
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
						const dateStr = formatDate( selectedStart.toISOString().split( 'T' )[ 0 ] );
						summaryCheckin.textContent = dateStr;
						if ( summaryMobileCheckin ) summaryMobileCheckin.textContent = dateStr;
					}
					if ( selectedEnd && summaryCheckout ) {
						const dateStr = formatDate( selectedEnd.toISOString().split( 'T' )[ 0 ] );
						summaryCheckout.textContent = dateStr;
						if ( summaryMobileCheckout ) summaryMobileCheckout.textContent = dateStr;
					}
					updateNightsAndPrice();
					runAvailabilityCheck();
				}

				/* Selected Room save */
				if ( modal.id === 'bf-modal-selected-room' ) {
					const checked = modal.querySelector( 'input[name="bf_modal_room"]:checked' );
					if ( checked ) {
						const roomName = checked.value;
						const price    = checked.dataset.price || '';
						const cap      = checked.dataset.capacity || '4';
						const excerpt  = checked.dataset.excerpt || '';
						const bedsData = checked.dataset.beds ? JSON.parse( checked.dataset.beds ) : [];

						const newMaxCapacity = parseInt( cap, 10 ) || 4;
						const currentTotalGuests = 1 + additionalGuests.length;
						
						if ( currentTotalGuests > newMaxCapacity ) {
							if ( window.cwcToast ) {
								window.cwcToast.show( `Cannot select ${roomName} Room. It accommodates up to ${newMaxCapacity} people, but you have ${currentTotalGuests} guests selected.`, 'warning' );
							} else {
								alert( `Cannot select ${roomName} Room. It accommodates up to ${newMaxCapacity} people, but you have ${currentTotalGuests} guests selected.` );
							}
							return;
						}

						if ( summaryRoomName ) summaryRoomName.textContent = `${ roomName } Room`;
						if ( summaryRoomType ) summaryRoomType.textContent = `${ roomName } Room`;
						if ( summaryRoomDesc ) summaryRoomDesc.textContent = excerpt;
						if ( summaryMobileRoom ) summaryMobileRoom.textContent = `${ roomName } Room`;
						
						if ( summaryRoomImg ) {
							summaryRoomImg.src = checked.dataset.image || '';
							summaryRoomImg.alt = `${ roomName } Room`;
						}
						
						if ( summaryAmenities ) {
							let amenitiesHtml = '';
							bedsData.forEach( ( bed ) => {
								amenitiesHtml += `
									<span class="bf-summary__amenity">
										<img src="${ bed.icon_url }" alt="" width="16" height="16">
										${ bed.label }
									</span>
									<span class="bf-summary__amenity-divider">|</span>
								`;
							} );
							amenitiesHtml += `
								<span class="bf-summary__amenity">
									<img src="/wp-content/uploads/2026/04/max-people-icon.svg" alt="" width="16" height="16">
									Max <span id="bf-summary-capacity">${ cap }</span> People
								</span>
							`;

							summaryAmenities.innerHTML = amenitiesHtml;
						}

						if ( price ) {
							nightlyRate = parseFloat( price.replace( /[^0-9.]/g, '' ) ) || 0;
						}
						maxCapacity = newMaxCapacity;
						updateCapacityUI();
						updateNightsAndPrice();
						roomAvailabilityCache = {};
						runAvailabilityCheck();
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
						additionalGuests[ i ].name = input.value;
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
		const syncInitialUI = () => {
			if (summaryRoomName) {
				const roomName = bookingData.room.includes('Room') ? bookingData.room : `${bookingData.room} Room`;
				summaryRoomName.textContent = roomName;
				if (summaryRoomType) summaryRoomType.textContent = roomName;
				if (summaryMobileRoom) summaryMobileRoom.textContent = roomName;
				
				const roomRadio = block.querySelector(`input[name="bf_modal_room"][value="${bookingData.room}"]`);
				if (roomRadio) {
					roomRadio.checked = true;
					const beds = JSON.parse(roomRadio.dataset.beds || '[]');
					if (summaryRoomDesc) summaryRoomDesc.textContent = roomRadio.dataset.excerpt || '';
					if (summaryRoomImg) {
						summaryRoomImg.src = roomRadio.dataset.image || '';
						summaryRoomImg.alt = roomName;
					}
					if (summaryAmenities) {
						summaryAmenities.innerHTML = beds.map(bed => `
							<span class="bf-summary__amenity">
								<img src="${bed.icon_url}" alt="" width="16" height="16">
								${bed.label}
							</span>
							<span class="bf-summary__amenity-divider">|</span>
						`).join('') + `
							<span class="bf-summary__amenity">
								<img src="/wp-content/uploads/2026/04/max-people-icon.svg" alt="" width="16" height="16">
								Max <span id="bf-summary-capacity">${roomRadio.dataset.capacity}</span> People
							</span>
						`;
					}
					if (roomRadio.dataset.price) {
						nightlyRate = parseFloat(roomRadio.dataset.price.replace(/[^0-9.]/g, ''));
					}
				}
			}

			if (summaryCheckin) {
				summaryCheckin.textContent = bookingData.checkin;
				if (summaryMobileCheckin) summaryMobileCheckin.textContent = bookingData.checkin;
			}
			if (summaryCheckout) {
				summaryCheckout.textContent = bookingData.checkout;
				if (summaryMobileCheckout) summaryMobileCheckout.textContent = bookingData.checkout;
			}
			if (summaryGuests) {
				summaryGuests.textContent = bookingData.guests;
			}

			updateModalPills();
			updateNightsAndPrice();
			runAvailabilityCheck();
		};

		syncInitialUI();
		updateCapacityUI();
		renderGuestRows();
		showStep( 2 );
	} );
} )();
