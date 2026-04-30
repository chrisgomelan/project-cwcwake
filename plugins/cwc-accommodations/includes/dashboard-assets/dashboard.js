/**
 * CWC Booking Dashboard — Admin JS
 */
( () => {
	document.addEventListener( 'DOMContentLoaded', () => {
		
		/* ─── Search & Filters ─── */
		const filterTable = ( searchInputId, tableId, filterSelectors = [] ) => {
			const searchInput = document.getElementById( searchInputId );
			const table = document.getElementById( tableId );
			if ( ! searchInput || ! table ) return;

			const rows = table.querySelectorAll( 'tbody .cwc-dash__row-item' );
			const filters = filterSelectors.map( id => document.getElementById( id ) ).filter( Boolean );

			const applyFilters = () => {
				const query = searchInput.value.toLowerCase().trim();
				const filterVals = filters.map( f => f.value );

				rows.forEach( row => {
					let matchSearch = true;
					if ( query ) {
						const textMatch = [
							row.dataset.ref || '',
							row.dataset.name || '',
							row.dataset.email || '',
							row.dataset.phone || '',
							row.dataset.tx || ''
						].some( val => val.includes( query ) );
						matchSearch = textMatch;
					}

					let matchFilters = true;
					if ( filters.length ) {
						// Assuming filter 0 is booking status, filter 1 is payment status
						if ( filters[0] && filters[0].value !== 'all' && row.dataset.status !== filters[0].value ) matchFilters = false;
						if ( filters[1] && filters[1].value !== 'all' && row.dataset.paymentStatus !== filters[1].value ) matchFilters = false;
					}

					if ( matchSearch && matchFilters ) {
						row.classList.remove( 'cwc-dash__hidden' );
					} else {
						row.classList.add( 'cwc-dash__hidden' );
					}
				} );
			};

			searchInput.addEventListener( 'input', applyFilters );
			filters.forEach( f => f.addEventListener( 'change', applyFilters ) );
		};

		filterTable( 'cwc-booking-search', 'cwc-bookings-table', ['cwc-filter-status', 'cwc-filter-payment'] );
		filterTable( 'cwc-guest-search', 'cwc-guests-table' );
		filterTable( 'cwc-payment-search', 'cwc-payments-table' );

		/* ─── Actions Dropdown ─── */
		document.addEventListener( 'click', ( e ) => {
			// Close all dropdowns if clicking outside
			if ( ! e.target.closest( '.cwc-dash__actions-menu' ) ) {
				document.querySelectorAll( '.cwc-dash__actions-dropdown.is-open' ).forEach( el => el.classList.remove( 'is-open' ) );
			}
		} );

		document.querySelectorAll( '.js-actions-toggle' ).forEach( btn => {
			btn.addEventListener( 'click', ( e ) => {
				e.stopPropagation();
				const dropdown = btn.nextElementSibling;
				const isOpen = dropdown.classList.contains( 'is-open' );
				
				// Close others
				document.querySelectorAll( '.cwc-dash__actions-dropdown.is-open' ).forEach( el => el.classList.remove( 'is-open' ) );
				
				if ( ! isOpen ) dropdown.classList.add( 'is-open' );
			} );
		} );

		/* ─── Status Modal ─── */
		const modal = document.getElementById( 'cwc-status-modal' );
		const modalCloseBtns = document.querySelectorAll( '.js-close-status-modal' );
		const submitStatusBtn = document.getElementById( 'modal-submit-status' );

		if ( modal ) {
			// Open Modal
			document.querySelectorAll( '.js-action-change-status' ).forEach( btn => {
				btn.addEventListener( 'click', () => {
					document.getElementById( 'modal-booking-id' ).value = btn.dataset.id;
					document.getElementById( 'modal-booking-ref' ).textContent = btn.dataset.ref;
					document.getElementById( 'modal-guest-name' ).textContent = btn.dataset.name;
					document.getElementById( 'modal-guest-email' ).textContent = btn.dataset.email;
					document.getElementById( 'modal-new-status' ).value = btn.dataset.status;
					document.getElementById( 'modal-admin-note' ).value = '';
					
					modal.style.display = 'flex';
					document.querySelectorAll( '.cwc-dash__actions-dropdown.is-open' ).forEach( el => el.classList.remove( 'is-open' ) );
				} );
			} );

			// Close Modal
			modalCloseBtns.forEach( btn => {
				btn.addEventListener( 'click', () => {
					modal.style.display = 'none';
				} );
			} );

			// Submit Modal AJAX
			if ( submitStatusBtn ) {
				submitStatusBtn.addEventListener( 'click', async () => {
					const btnOriginalText = submitStatusBtn.textContent;
					submitStatusBtn.textContent = 'Updating...';
					submitStatusBtn.disabled = true;

					const bookingId = document.getElementById( 'modal-booking-id' ).value;
					const newStatus = document.getElementById( 'modal-new-status' ).value;
					const sendEmail = document.getElementById( 'modal-send-email' ).checked;
					const adminNote = document.getElementById( 'modal-admin-note' ).value;

					try {
						const formData = new URLSearchParams();
						formData.append( 'action', 'cwc_update_booking_status' );
						formData.append( 'booking_id', bookingId );
						formData.append( 'new_status', newStatus );
						formData.append( 'send_email', sendEmail );
						formData.append( 'admin_note', adminNote );

						const response = await fetch( cwcDash.ajaxUrl, { method: 'POST', body: formData } );
						const result = await response.json();

						if ( result.success ) {
							location.reload(); // Reload to show new statuses and badges
						} else {
							alert( 'Error: ' + result.data.message );
							submitStatusBtn.textContent = btnOriginalText;
							submitStatusBtn.disabled = false;
						}
					} catch ( err ) {
						console.error( err );
						alert( 'A server error occurred.' );
						submitStatusBtn.textContent = btnOriginalText;
						submitStatusBtn.disabled = false;
					}
				} );
			}
		}

		/* ─── Update Payment Status AJAX ─── */
		document.querySelectorAll( '.js-action-update-payment' ).forEach( btn => {
			btn.addEventListener( 'click', async () => {
				if ( ! confirm( `Are you sure you want to mark this as ${btn.dataset.status.toUpperCase()}?` ) ) return;

				const bookingId = btn.dataset.id;
				const paymentStatus = btn.dataset.status;

				try {
					const formData = new URLSearchParams();
					formData.append( 'action', 'cwc_update_payment_status' );
					formData.append( 'booking_id', bookingId );
					formData.append( 'payment_status', paymentStatus );

					const response = await fetch( cwcDash.ajaxUrl, { method: 'POST', body: formData } );
					const result = await response.json();

					if ( result.success ) {
						location.reload();
					} else {
						alert( 'Error: ' + result.data.message );
					}
				} catch ( err ) {
					console.error( err );
					alert( 'A server error occurred.' );
				}
			} );
		} );

		/* ─── Resend Email AJAX ─── */
		document.querySelectorAll( '.js-action-resend-email' ).forEach( btn => {
			btn.addEventListener( 'click', async () => {
				if ( ! confirm( 'Are you sure you want to resend the latest status email to this guest?' ) ) return;

				const bookingId = btn.dataset.id;
				const originalText = btn.textContent;
				btn.textContent = 'Sending...';

				try {
					const formData = new URLSearchParams();
					formData.append( 'action', 'cwc_resend_booking_email' );
					formData.append( 'booking_id', bookingId );

					const response = await fetch( cwcDash.ajaxUrl, { method: 'POST', body: formData } );
					const result = await response.json();

					if ( result.success ) {
						alert( 'Email resent successfully!' );
					} else {
						alert( 'Error: ' + result.data.message );
					}
				} catch ( err ) {
					console.error( err );
					alert( 'A server error occurred.' );
				} finally {
					btn.textContent = originalText;
					document.querySelectorAll( '.cwc-dash__actions-dropdown.is-open' ).forEach( el => el.classList.remove( 'is-open' ) );
				}
			} );
		} );

		/* ─── Companion Toggle Logic ─── */
		const companionToggles = document.querySelectorAll( '.js-toggle-companions' );
		companionToggles.forEach( ( toggle ) => {
			toggle.addEventListener( 'click', ( e ) => {
				e.preventDefault();
				e.stopPropagation();
				const parentId = toggle.dataset.id;
				const parentRow = toggle.closest( '.cwc-dash__tr--primary' );
				const companionRows = document.querySelectorAll( `.cwc-dash__companion-row[data-parent-id="${parentId}"]` );

				toggle.classList.toggle( 'is-active' );
				parentRow.classList.toggle( 'is-expanded' );

				companionRows.forEach( ( row ) => {
					if ( row.style.display === 'none' ) {
						row.style.display = 'table-row';
					} else {
						row.style.display = 'none';
					}
				} );
			} );
		} );
	} );
} )();
