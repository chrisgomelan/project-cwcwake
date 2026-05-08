/**
 * CWC Booking Dashboard — Admin JS
 */
(() => {
	document.addEventListener('DOMContentLoaded', () => {

		/* ─── Search, Filters & Pagination ─── */
		const filterTable = (searchInputId, tableElementOrId, filterSelectors = []) => {
			const searchInput = typeof searchInputId === 'string' ? document.getElementById(searchInputId) : searchInputId;
			const table = typeof tableElementOrId === 'string' ? document.getElementById(tableElementOrId) : tableElementOrId;
			if (!table) return;

			const card = table.closest('.cwc-dash__card, .cwc-dash__room-tracking-section');
			const paginationWrap = card ? card.querySelector('.cwc-dash__pagination-wrap') : null;
			const perPageSelect = paginationWrap ? paginationWrap.querySelector('.js-pagination-per-page') : null;
			const infoText = paginationWrap ? paginationWrap.querySelector('.js-pagination-info') : null;
			const prevBtn = paginationWrap ? paginationWrap.querySelector('.js-pagination-prev') : null;
			const nextBtn = paginationWrap ? paginationWrap.querySelector('.js-pagination-next') : null;

			let currentPage = 1;
			let perPage = perPageSelect ? parseInt(perPageSelect.value) : 10;

			const rows = table.querySelectorAll('tbody .cwc-dash__row-item:not(.cwc-dash__tr--companion)');
			const filters = filterSelectors.map(id => document.getElementById(id)).filter(Boolean);

			const applyFilters = (resetPage = true) => {
				if (resetPage) currentPage = 1;

				const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
				let visibleRows = [];

				rows.forEach(row => {
					// 1. Filter Logic
					let matchSearch = true;
					if (query) {
						const textMatch = [
							row.dataset.ref || '',
							row.dataset.name || '',
							row.dataset.email || '',
							row.dataset.phone || '',
							row.dataset.tx || ''
						].some(val => val.includes(query));
						matchSearch = textMatch;
					}

					let matchFilters = true;
					if (filters.length) {
						if (filters[0] && filters[0].value !== 'all' && row.dataset.status !== filters[0].value) matchFilters = false;
						if (filters[1] && filters[1].value !== 'all' && row.dataset.paymentStatus !== filters[1].value) matchFilters = false;
					}

					// 2. Apply Filter Class
					if (matchSearch && matchFilters) {
						row.classList.remove('cwc-dash__hidden');
						visibleRows.push(row);
					} else {
						row.classList.add('cwc-dash__hidden');
						// Also hide companions if parent is hidden
						const companions = table.querySelectorAll(`.cwc-dash__companion-row[data-parent-id="${row.dataset.id}"]`);
						companions.forEach(c => c.style.display = 'none');
					}
				});

				// 3. Pagination Logic
				if (paginationWrap) {
					const totalVisible = visibleRows.length;
					const totalPages = Math.ceil(totalVisible / perPage);

					if (currentPage > totalPages) currentPage = Math.max(1, totalPages);

					const start = (currentPage - 1) * perPage;
					const end = start + perPage;

					visibleRows.forEach((row, index) => {
						if (index >= start && index < end) {
							row.classList.remove('cwc-dash__hidden-page');
						} else {
							row.classList.add('cwc-dash__hidden-page');
							// Also hide companions if parent is hidden by page
							const companions = table.querySelectorAll(`.cwc-dash__companion-row[data-parent-id="${row.dataset.id}"]`);
							companions.forEach(c => c.style.display = 'none');
						}
					});

					// Update UI
					if (infoText) {
						const showStart = totalVisible > 0 ? start + 1 : 0;
						const showEnd = Math.min(end, totalVisible);
						infoText.textContent = `Showing ${showStart} to ${showEnd} of ${totalVisible} entries`;
					}
					if (prevBtn) prevBtn.disabled = (currentPage === 1);
					if (nextBtn) nextBtn.disabled = (currentPage === totalPages || totalPages === 0);
				}
			};

			// Listeners
			if (searchInput) searchInput.addEventListener('input', () => applyFilters(true));
			filters.forEach(f => f.addEventListener('change', () => applyFilters(true)));

			if (perPageSelect) {
				perPageSelect.addEventListener('change', () => {
					perPage = parseInt(perPageSelect.value);
					applyFilters(true);
				});
			}
			if (prevBtn) {
				prevBtn.addEventListener('click', (e) => {
					e.preventDefault();
					if (currentPage > 1) {
						currentPage--;
						applyFilters(false);
						paginationWrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
					}
				});
			}
			if (nextBtn) {
				nextBtn.addEventListener('click', (e) => {
					e.preventDefault();
					const totalVisible = table.querySelectorAll('tbody .cwc-dash__row-item:not(.cwc-dash__tr--companion):not(.cwc-dash__hidden)').length;
					const totalPages = Math.ceil(totalVisible / perPage);
					if (currentPage < totalPages) {
						currentPage++;
						applyFilters(false);
						paginationWrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
					}
				});
			}

			// Initial call
			applyFilters();
		};

		filterTable('cwc-booking-search', 'cwc-bookings-table', ['cwc-filter-status', 'cwc-filter-payment']);
		filterTable('cwc-guest-search', 'cwc-guests-table');
		filterTable('cwc-payment-search', 'cwc-payments-table');

		document.querySelectorAll('.cwc-dash__room-tracking-section table').forEach(table => {
			filterTable(null, table);
		});

		/* ─── Actions Dropdown ─── */
		document.addEventListener('click', (e) => {
			// Close all dropdowns if clicking outside
			if (!e.target.closest('.cwc-dash__actions-menu')) {
				document.querySelectorAll('.cwc-dash__actions-dropdown.is-open').forEach(el => el.classList.remove('is-open'));
			}
		});

		document.querySelectorAll('.js-actions-toggle').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.stopPropagation();
				const dropdown = btn.nextElementSibling;
				const isOpen = dropdown.classList.contains('is-open');

				// Close others
				document.querySelectorAll('.cwc-dash__actions-dropdown.is-open').forEach(el => el.classList.remove('is-open'));

				if (!isOpen) dropdown.classList.add('is-open');
			});
		});

		/* ─── Status Modal ─── */
		const modal = document.getElementById('cwc-status-modal');
		const modalCloseBtns = document.querySelectorAll('.js-close-status-modal');
		const submitStatusBtn = document.getElementById('modal-submit-status');

		if (modal) {
			// Open Modal
			document.querySelectorAll('.js-action-change-status').forEach(btn => {
				btn.addEventListener('click', () => {
					document.getElementById('modal-booking-id').value = btn.dataset.id;
					document.getElementById('modal-booking-ref').textContent = btn.dataset.ref;
					document.getElementById('modal-guest-name').textContent = btn.dataset.name;
					document.getElementById('modal-guest-email').textContent = btn.dataset.email;
					document.getElementById('modal-new-status').value = btn.dataset.status;
					document.getElementById('modal-admin-note').value = '';

					modal.style.display = 'flex';
					document.querySelectorAll('.cwc-dash__actions-dropdown.is-open').forEach(el => el.classList.remove('is-open'));
				});
			});

			// Close Modal
			modalCloseBtns.forEach(btn => {
				btn.addEventListener('click', () => {
					modal.style.display = 'none';
				});
			});

			// Submit Modal AJAX
			if (submitStatusBtn) {
				submitStatusBtn.addEventListener('click', async () => {
					const btnOriginalText = submitStatusBtn.textContent;
					submitStatusBtn.textContent = 'Updating...';
					submitStatusBtn.disabled = true;

					const bookingId = document.getElementById('modal-booking-id').value;
					const newStatus = document.getElementById('modal-new-status').value;
					const sendEmail = document.getElementById('modal-send-email').checked;
					const adminNote = document.getElementById('modal-admin-note').value;

					try {
						const formData = new URLSearchParams();
						formData.append('action', 'cwc_update_booking_status');
						formData.append('booking_id', bookingId);
						formData.append('new_status', newStatus);
						formData.append('send_email', sendEmail);
						formData.append('admin_note', adminNote);
					formData.append('nonce', cwcDash.nonce);

					const response = await fetch(cwcDash.ajaxUrl, { method: 'POST', body: formData });
					const result = await response.json();

					if (result.success) {
						location.reload();
					} else {
							alert('Error: ' + result.data.message);
							submitStatusBtn.textContent = btnOriginalText;
							submitStatusBtn.disabled = false;
						}
					} catch (err) {
						console.error(err);
						alert('A server error occurred.');
						submitStatusBtn.textContent = btnOriginalText;
						submitStatusBtn.disabled = false;
					}
				});
			}
		}

		/* ─── Update Payment Status AJAX ─── */
		document.querySelectorAll('.js-action-update-payment').forEach(btn => {
			btn.addEventListener('click', async () => {
				if (!confirm(`Are you sure you want to mark this as ${btn.dataset.status.toUpperCase()}?`)) return;

				const bookingId = btn.dataset.id;
				const paymentStatus = btn.dataset.status;

				try {
					const formData = new URLSearchParams();
					formData.append('action', 'cwc_update_payment_status');
					formData.append('booking_id', bookingId);
					formData.append('payment_status', paymentStatus);
					formData.append('nonce', cwcDash.nonce);

					const response = await fetch(cwcDash.ajaxUrl, { method: 'POST', body: formData });
					const result = await response.json();

					if (result.success) {
						location.reload();
					} else {
						alert('Error: ' + result.data.message);
					}
				} catch (err) {
					console.error(err);
					alert('A server error occurred.');
				}
			});
		});

		/* ─── Resend Email AJAX ─── */
		document.querySelectorAll('.js-action-resend-email').forEach(btn => {
			btn.addEventListener('click', async () => {
				if (!confirm('Are you sure you want to resend the latest status email to this guest?')) return;

				const bookingId = btn.dataset.id;
				const originalText = btn.textContent;
				btn.textContent = 'Sending...';

				try {
					const formData = new URLSearchParams();
					formData.append('action', 'cwc_resend_booking_email');
					formData.append('booking_id', bookingId);
					formData.append('nonce', cwcDash.nonce);

					const response = await fetch(cwcDash.ajaxUrl, { method: 'POST', body: formData });
					const result = await response.json();

					if (result.success) {
						alert('Email resent successfully!');
					} else {
						alert('Error: ' + result.data.message);
					}
				} catch (err) {
					console.error(err);
					alert('A server error occurred.');
				} finally {
					btn.textContent = originalText;
					document.querySelectorAll('.cwc-dash__actions-dropdown.is-open').forEach(el => el.classList.remove('is-open'));
				}
			});
		});

		/* ─── Companion Toggle Logic ─── */
		const companionToggles = document.querySelectorAll('.js-toggle-companions');
		companionToggles.forEach((toggle) => {
			toggle.addEventListener('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				const parentId = toggle.dataset.id;
				const parentRow = toggle.closest('.cwc-dash__tr--primary');
				const companionRows = document.querySelectorAll(`.cwc-dash__companion-row[data-parent-id="${parentId}"]`);

				toggle.classList.toggle('is-active');
				parentRow.classList.toggle('is-expanded');

				companionRows.forEach((row) => {
					if (row.style.display === 'none') {
						row.style.display = 'table-row';
					} else {
						row.style.display = 'none';
					}
				});
			});
		});

		/* ─── Toggle Physical Room Status AJAX ─── */
		document.querySelectorAll('.js-toggle-unit-status').forEach(btn => {
			btn.addEventListener('click', async (e) => {
				e.preventDefault();
				const roomId = btn.dataset.roomId;
				const unitId = btn.dataset.unitId || '';
				const unitName = btn.dataset.unitName;
				const currentStatus = btn.dataset.currentStatus;
				const newStatus = currentStatus === 'booked' ? 'available' : 'booked';

				const confirmMsg = newStatus === 'booked'
					? `Are you sure you want to mark ${unitName} as BOOKED/OCCUPIED?`
					: `Are you sure you want to release ${unitName} and mark it as AVAILABLE?`;

				if (!confirm(confirmMsg)) return;

				const originalText = btn.textContent;
				btn.textContent = '...';
				btn.disabled = true;

				try {
					const formData = new URLSearchParams();
					formData.append('action', 'cwc_toggle_physical_room_status');
					formData.append('room_id', roomId);
					if (unitId) {
						formData.append('unit_id', unitId);
					}
					formData.append('unit_name', unitName);
					formData.append('new_status', newStatus);

					const response = await fetch(cwcDash.ajaxUrl, { method: 'POST', body: formData });
					const result = await response.json();

					if (result.success) {
						location.reload();
					} else {
						alert('Error: ' + result.data.message);
						btn.textContent = originalText;
						btn.disabled = false;
					}
				} catch (err) {
					console.error(err);
					alert('A server error occurred.');
					btn.textContent = originalText;
					btn.disabled = false;
				}
			});
		});

		/* ─── Deep Linking: Tracking -> Bookings ─── */
		document.querySelectorAll('.js-dash-nav-booking').forEach(link => {
			link.addEventListener('click', (e) => {
				e.preventDefault();
				const ref = link.dataset.ref;

				// 1. Find the Bookings tab link and click it
				const bookingsTab = document.querySelector('.cwc-dash__tab[href*="tab=bookings"]');
				if (bookingsTab) {
					// Instead of full reload, we can try to find the search input if already on page
					const searchInput = document.getElementById('cwc-booking-search');
					if (searchInput) {
						searchInput.value = ref;
						searchInput.dispatchEvent(new Event('input'));
						// Smooth scroll to table
						searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
					} else {
						// If tab logic is server-side (which it is), we must redirect with query arg
						const url = new URL(bookingsTab.href);
						url.searchParams.set('s', ref); // We can handle this in PHP
						window.location.href = url.toString();
					}
				}
			});
		});

		/* ─── Rates Management Tab Logic ─── */
		const ratesList = document.getElementById('cwc-rates-categories');
		const addCategoryBtn = document.getElementById('cwc-rates-add-category-btn');
		const categoryTemplate = document.getElementById('cwc-rates-category-template');
		const saveRatesBtn = document.getElementById('cwc-rates-save-btn');
		const ratesForm = document.getElementById('cwc-rates-manager-form');

		if (ratesList && addCategoryBtn && categoryTemplate) {
			
			// Add Category
			addCategoryBtn.addEventListener('click', () => {
				const index = ratesList.querySelectorAll('.cwc-rates-category-item').length;
				let html = categoryTemplate.innerHTML.replace(/__CAT_IDX__/g, index);
				
				const div = document.createElement('div');
				div.innerHTML = html.trim();
				const newItem = div.firstChild;
				
				ratesList.appendChild(newItem);
				newItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
			});

			// Save Button Trigger
			if (saveRatesBtn && ratesForm) {
				saveRatesBtn.addEventListener('click', () => {
					ratesForm.submit();
				});
			}

			// Delegate Events (Remove, Add Row, Add Col)
			ratesList.addEventListener('click', (e) => {
				const target = e.target;

				// Remove Category
				if (target.classList.contains('cwc-rates-remove-category')) {
					if (confirm('Are you sure you want to remove this entire rate category?')) {
						target.closest('.cwc-rates-category-item').remove();
					}
				}

				// Add Row
				if (target.classList.contains('cwc-rates-add-row-btn')) {
					const categoryItem = target.closest('.cwc-rates-category-item');
					const catIdx = categoryItem.dataset.index;
					const tableBody = categoryItem.querySelector('.cwc-rates-editor-table tbody');
					const controlRow = tableBody.querySelector('.cwc-rates-controls-row');
					const colCount = controlRow.querySelectorAll('td').length - 1;
					const rowIdx = tableBody.querySelectorAll('tr:not(.cwc-rates-controls-row)').length;

					const tr = document.createElement('tr');
					let cellsHtml = '';
					for (let i = 0; i < colCount; i++) {
						cellsHtml += `<td><input type="text" name="rates[${catIdx}][table][${rowIdx}][${i}]" value=""></td>`;
					}
					cellsHtml += `<td><button type="button" class="cwc-rates-remove-row-btn">&times;</button></td>`;
					
					tr.innerHTML = cellsHtml;
					tableBody.appendChild(tr);
				}

				// Remove Row
				if (target.classList.contains('cwc-rates-remove-row-btn')) {
					if (confirm('Delete this row?')) {
						target.closest('tr').remove();
					}
				}

				// Add Column
				if (target.classList.contains('cwc-rates-add-col')) {
					const categoryItem = target.closest('.cwc-rates-category-item');
					const catIdx = categoryItem.dataset.index;
					const table = categoryItem.querySelector('.cwc-rates-editor-table');
					const tableBody = table.querySelector('tbody');
					const controlRow = tableBody.querySelector('.cwc-rates-controls-row');
					const dataRows = tableBody.querySelectorAll('tr:not(.cwc-rates-controls-row)');
					const colIdx = controlRow.querySelectorAll('td').length - 1;

					// Add Control Cell
					const tdControl = document.createElement('td');
					tdControl.innerHTML = `<button type="button" class="cwc-rates-remove-col" data-col="${colIdx}" title="Remove Column">&times;</button>`;
					controlRow.insertBefore(tdControl, target.parentElement);

					// Add Body Cells
					dataRows.forEach((row, rIdx) => {
						const td = document.createElement('td');
						td.innerHTML = `<input type="text" name="rates[${catIdx}][table][${rIdx}][${colIdx}]" value="">`;
						row.insertBefore(td, row.lastElementChild);
					});
				}

				// Remove Column
				if (target.classList.contains('cwc-rates-remove-col')) {
					const table = target.closest('table');
					const controlRow = table.querySelector('.cwc-rates-controls-row');
					const totalCols = controlRow.querySelectorAll('td').length - 1; // Exclude add-col cell

					if (totalCols <= 1) {
						alert('You must have at least one column.');
						return;
					}

					if (confirm('Are you sure you want to remove this column? This will delete all data in this column for this category.')) {
						const colIdx = Array.from(target.parentElement.parentElement.children).indexOf(target.parentElement);
						
						// Remove from every row
						table.querySelectorAll('tr').forEach(row => {
							if (row.cells[colIdx]) {
								row.cells[colIdx].remove();
							}
						});

						// Re-index remaining column buttons
						table.querySelectorAll('.cwc-rates-remove-col').forEach((btn, idx) => {
							btn.dataset.col = idx;
						});
					}
				}
			});
		}

		/* ─── Bulk Actions Logic ─── */
		const bulkBar = document.getElementById('cwc-bulk-actions-bar');
		const bulkCount = document.getElementById('cwc-bulk-count');
		const bulkSelectAll = document.getElementById('cwc-bulk-select-all');
		const bulkStatus = document.getElementById('cwc-bulk-status');
		const bulkPayment = document.getElementById('cwc-bulk-payment');
		const bulkApplyBtn = document.getElementById('cwc-bulk-apply');
		const bulkCancelBtn = document.getElementById('cwc-bulk-cancel');
		const rowCheckboxes = () => document.querySelectorAll('.cwc-row-checkbox');

		const updateBulkUI = () => {
			const selected = Array.from(rowCheckboxes()).filter(cb => cb.checked);
			const count = selected.length;
			
			if (count > 0) {
				if (bulkBar) bulkBar.style.display = 'flex';
				if (bulkCount) bulkCount.textContent = count;
			} else {
				if (bulkBar) bulkBar.style.display = 'none';
				if (bulkSelectAll) bulkSelectAll.checked = false;
			}

			// Highlight selected rows
			rowCheckboxes().forEach(cb => {
				const row = cb.closest('tr');
				if (cb.checked) {
					row.classList.add('is-selected');
				} else {
					row.classList.remove('is-selected');
				}
			});
		};

		if (bulkSelectAll) {
			bulkSelectAll.addEventListener('change', () => {
				// Only select visible rows (not hidden by filter/pagination)
				const visibleCheckboxes = Array.from(rowCheckboxes()).filter(cb => {
					const row = cb.closest('tr');
					return !row.classList.contains('cwc-dash__hidden') && !row.classList.contains('cwc-dash__hidden-page');
				});
				
				visibleCheckboxes.forEach(cb => cb.checked = bulkSelectAll.checked);
				updateBulkUI();
			});
		}

		document.addEventListener('change', (e) => {
			if (e.target.classList.contains('cwc-row-checkbox')) {
				updateBulkUI();
			}
		});

		if (bulkCancelBtn) {
			bulkCancelBtn.addEventListener('click', () => {
				rowCheckboxes().forEach(cb => cb.checked = false);
				if (bulkSelectAll) bulkSelectAll.checked = false;
				updateBulkUI();
			});
		}

		if (bulkApplyBtn) {
			bulkApplyBtn.addEventListener('click', async () => {
				const selectedIds = Array.from(rowCheckboxes())
					.filter(cb => cb.checked)
					.map(cb => cb.value);
				
				const status = bulkStatus ? bulkStatus.value : '';
				const payment = bulkPayment ? bulkPayment.value : '';
				const sendEmail = document.getElementById('cwc-bulk-send-email')?.checked;

				if (!status && !payment) {
					alert('Please select an action to apply.');
					return;
				}

				if (!confirm(`Apply changes to ${selectedIds.length} selected bookings?`)) return;

				const originalText = bulkApplyBtn.textContent;
				bulkApplyBtn.textContent = 'Applying...';
				bulkApplyBtn.disabled = true;

				try {
					const formData = new URLSearchParams();
					formData.append('action', 'cwc_bulk_update_bookings');
					formData.append('ids', JSON.stringify(selectedIds));
					if (status) formData.append('status', status);
					if (payment) formData.append('payment_status', payment);
					formData.append('send_email', sendEmail ? '1' : '0');
					formData.append('nonce', cwcDash.nonce);

					const response = await fetch(cwcDash.ajaxUrl, { method: 'POST', body: formData });
					const result = await response.json();

					if (result.success) {
						alert(result.data.message);
						location.reload();
					} else {
						alert('Error: ' + result.data.message);
						bulkApplyBtn.textContent = originalText;
						bulkApplyBtn.disabled = false;
					}
				} catch (err) {
					console.error(err);
					alert('A server error occurred.');
					bulkApplyBtn.textContent = originalText;
					bulkApplyBtn.disabled = false;
				}
			});
		}
	});

})();

