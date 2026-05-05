<?php
/**
 * CWC Accommodations — Booking Dashboard.
 *
 * Admin page with tabs: Bookings, Guests, Availability, Analytics.
 *
 * @package CWC_Accommodations
 * @since   1.1.0
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Register the Dashboard submenu under Accommodations.
 */
function cwc_register_dashboard_menu()
{
	add_submenu_page(
		'edit.php?post_type=accommodation',
		__('Dashboard', 'cwc-accommodations'),
		__('Dashboard', 'cwc-accommodations'),
		'manage_options',
		'cwc-booking-dashboard',
		'cwc_render_booking_dashboard'
	);
}
add_action('admin_menu', 'cwc_register_dashboard_menu');

/**
 * Enqueue dashboard assets only on our page.
 */
function cwc_dashboard_admin_assets($hook)
{
	if ('accommodation_page_cwc-booking-dashboard' !== $hook) {
		return;
	}
	wp_enqueue_style(
		'cwc-dashboard-css',
		CWC_ACC_URL . 'includes/dashboard-assets/dashboard.css',
		[],
		CWC_ACC_VERSION
	);
	wp_enqueue_script(
		'cwc-dashboard-js',
		CWC_ACC_URL . 'includes/dashboard-assets/dashboard.js',
		[],
		CWC_ACC_VERSION,
		true
	);
	wp_localize_script('cwc-dashboard-js', 'cwcDash', [
		'ajaxUrl' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('cwc_dash_nonce'),
	]);
}
add_action('admin_enqueue_scripts', 'cwc_dashboard_admin_assets');

/* ────────────────────────────────────────────
   Helper: fetch all bookings with meta
   ──────────────────────────────────────────── */
function cwc_get_all_bookings($args = [])
{
	$defaults = [
		'post_type' => 'cwc_booking',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'orderby' => 'date',
		'order' => 'DESC',
	];
	$query = new WP_Query(array_merge($defaults, $args));
	$bookings = [];

	foreach ($query->posts as $post) {
		$nights = (int) get_post_meta($post->ID, '_cwc_bk_nights', true);
		$checkin_raw = get_post_meta($post->ID, '_cwc_bk_checkin', true);
		$checkout_raw = get_post_meta($post->ID, '_cwc_bk_checkout', true);

		// Calculate nights from dates if not stored
		if (!$nights && $checkin_raw && $checkout_raw) {
			$ci_ts = strtotime($checkin_raw);
			$co_ts = strtotime($checkout_raw);
			if ($ci_ts && $co_ts && $co_ts > $ci_ts) {
				$nights = (int) (($co_ts - $ci_ts) / DAY_IN_SECONDS);
			}
		}

		$bookings[] = [
			'id' => $post->ID,
			'ref' => get_post_meta($post->ID, '_cwc_bk_ref', true) ?: '#' . $post->ID,
			'date' => $post->post_date,
			'name' => get_post_meta($post->ID, '_cwc_bk_name', true),
			'email' => get_post_meta($post->ID, '_cwc_bk_email', true),
			'phone' => get_post_meta($post->ID, '_cwc_bk_phone', true),
			'checkin' => $checkin_raw,
			'checkout' => $checkout_raw,
			'nights' => $nights,
			'room' => get_post_meta($post->ID, '_cwc_bk_room', true),
			'assigned_room' => get_post_meta($post->ID, '_cwc_bk_assigned_room', true),
			'price' => get_post_meta($post->ID, '_cwc_bk_price', true),
			'price_num' => (float) get_post_meta($post->ID, '_cwc_bk_price_num', true),
			'payment' => get_post_meta($post->ID, '_cwc_bk_payment', true),
			'status' => get_post_meta($post->ID, '_cwc_bk_status', true) ?: 'pending',
			'payment_status' => get_post_meta($post->ID, '_cwc_bk_payment_status', true) ?: 'unpaid',
			'transaction_id' => get_post_meta($post->ID, '_cwc_bk_transaction_id', true),
			'guests' => json_decode(get_post_meta($post->ID, '_cwc_bk_guests', true) ?: '[]', true),
			'audit_log' => json_decode(get_post_meta($post->ID, '_cwc_bk_audit_log', true) ?: '[]', true),
			'email_log' => json_decode(get_post_meta($post->ID, '_cwc_bk_email_log', true) ?: '[]', true),
		];
	}
	return $bookings;
}

/* ────────────────────────────────────────────
   Render: main dashboard page
   ──────────────────────────────────────────── */
function cwc_render_booking_dashboard()
{
	$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'bookings';
	$bookings = cwc_get_all_bookings();

	// Stats
	$total_bookings = count($bookings);
	$total_revenue = array_sum(array_column($bookings, 'price_num'));
	$pending_count = count(array_filter($bookings, fn($b) => $b['status'] === 'pending'));
	$confirmed_count = count(array_filter($bookings, fn($b) => $b['status'] === 'confirmed'));
	$cancelled_count = count(array_filter($bookings, fn($b) => $b['status'] === 'cancelled'));
	$paid_count = count(array_filter($bookings, fn($b) => $b['payment_status'] === 'paid'));

	// Room breakdown
	$room_counts = [];
	$room_revenue = [];
	foreach ($bookings as $b) {
		$r = $b['room'] ?: 'Unknown';
		$room_counts[$r] = ($room_counts[$r] ?? 0) + 1;
		$room_revenue[$r] = ($room_revenue[$r] ?? 0) + $b['price_num'];
	}

	// Guest count
	$total_guests = 0;
	foreach ($bookings as $b) {
		$total_guests += 1 + count($b['guests']);
	}
	?>
	<div class="wrap cwc-dash">
		<h1 class="cwc-dash__title">
			<span class="dashicons dashicons-performance"
				style="font-size: 32px; width: 32px; height: 32px; margin-right: 8px;"></span>
			Booking Dashboard
		</h1>

		<!-- Tabs -->
		<nav class="cwc-dash__tabs">
			<?php
			$tabs = [
				'bookings' => 'Bookings',
				'guests' => 'Guests',
				'payments' => 'Payments',
				'room-tracking' => 'Room Units Tracking',
				'availability' => 'Availability',
				'analytics' => 'Analytics',
			];
			$base_url = admin_url('edit.php?post_type=accommodation&page=cwc-booking-dashboard');
			foreach ($tabs as $slug => $label):
				$is_active = ($slug === $active_tab) ? ' cwc-dash__tab--active' : '';
				?>
				<a href="<?php echo esc_url(add_query_arg('tab', $slug, $base_url)); ?>"
					class="cwc-dash__tab<?php echo esc_attr($is_active); ?>">
					<?php echo esc_html($label); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<!-- Stat Cards -->
		<div class="cwc-dash__stats">
			<div class="cwc-dash__stat-card cwc-dash__stat-card--blue">
				<div class="cwc-dash__stat-value"><?php echo esc_html($total_bookings); ?></div>
				<div class="cwc-dash__stat-label">Total Bookings</div>
			</div>
			<div class="cwc-dash__stat-card cwc-dash__stat-card--green">
				<div class="cwc-dash__stat-value">₱<?php echo esc_html(number_format($total_revenue, 2)); ?></div>
				<div class="cwc-dash__stat-label">Total Revenue</div>
			</div>
			<div class="cwc-dash__stat-card cwc-dash__stat-card--amber">
				<div class="cwc-dash__stat-value"><?php echo esc_html($pending_count); ?></div>
				<div class="cwc-dash__stat-label">Pending</div>
			</div>
			<div class="cwc-dash__stat-card cwc-dash__stat-card--teal">
				<div class="cwc-dash__stat-value"><?php echo esc_html($paid_count); ?></div>
				<div class="cwc-dash__stat-label">Paid</div>
			</div>
		</div>

		<!-- Tab Content -->
		<div class="cwc-dash__content">
			<?php
			switch ($active_tab) {
				case 'guests':
					cwc_render_dash_guests($bookings);
					break;
				case 'payments':
					cwc_render_dash_payments($bookings);
					break;
				case 'room-tracking':
					cwc_render_dash_room_tracking($bookings);
					break;
				case 'availability':
					cwc_render_dash_availability($bookings);
					break;
				case 'analytics':
					cwc_render_dash_analytics($bookings, $room_counts, $room_revenue, $total_revenue);
					break;
				default:
					cwc_render_dash_bookings($bookings);
					break;
			}
			?>
		</div>

		<!-- Status Change Modal -->
		<div class="cwc-dash__modal" id="cwc-status-modal" style="display:none;">
			<div class="cwc-dash__modal-overlay js-close-status-modal"></div>
			<div class="cwc-dash__modal-container">
				<div class="cwc-dash__modal-header">
					<h3>Update Booking Status</h3>
					<button class="cwc-dash__modal-close js-close-status-modal">&times;</button>
				</div>
				<div class="cwc-dash__modal-body">
					<div class="cwc-dash__modal-info">
						<p><strong>Booking:</strong> <span id="modal-booking-ref"></span></p>
						<p><strong>Guest:</strong> <span id="modal-guest-name"></span> (<span
								id="modal-guest-email"></span>)</p>
					</div>
					<div class="cwc-dash__modal-field">
						<label for="modal-new-status">New Status</label>
						<select id="modal-new-status" class="cwc-dash__modal-select">
							<option value="pending">Pending</option>
							<option value="confirmed">Confirmed</option>
							<option value="cancelled">Cancelled</option>
							<option value="completed">Completed</option>
						</select>
					</div>
					<div class="cwc-dash__modal-field">
						<label class="cwc-dash__modal-checkbox-label">
							<input type="checkbox" id="modal-send-email" checked>
							Send email notification to guest
						</label>
					</div>
					<div class="cwc-dash__modal-field" id="modal-note-wrap">
						<label for="modal-admin-note">Admin Note <small>(optional — included in email)</small></label>
						<textarea id="modal-admin-note" rows="3"
							placeholder="e.g. Your room has been verified and is ready for your arrival."></textarea>
					</div>
					<input type="hidden" id="modal-booking-id" value="">
				</div>
				<div class="cwc-dash__modal-footer">
					<button class="button js-close-status-modal">Cancel</button>
					<button class="button button-primary" id="modal-submit-status">Update Status</button>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render standardized pagination controls.
 */
function cwc_render_dash_pagination($id_prefix)
{
	?>
	<div class="cwc-dash__pagination-wrap" id="<?php echo esc_attr($id_prefix); ?>-pagination">
		<div class="cwc-dash__pagination-per-page">
			<label for="<?php echo esc_attr($id_prefix); ?>-per-page">Show</label>
			<select id="<?php echo esc_attr($id_prefix); ?>-per-page"
				class="cwc-dash__pagination-select js-pagination-per-page">
				<option value="10" selected>10</option>
				<option value="20">20</option>
				<option value="30">30</option>
				<option value="50">50</option>
			</select>
			<span>entries</span>
		</div>
		<div class="cwc-dash__pagination-controls">
			<span class="cwc-dash__pagination-info js-pagination-info">Showing 0 to 0 of 0 entries</span>
			<div class="cwc-dash__pagination-btns">
				<button class="button button-secondary js-pagination-prev" disabled>Previous</button>
				<button class="button button-secondary js-pagination-next" disabled>Next</button>
			</div>
		</div>
	</div>
	<?php
}

/* ─── TAB: Bookings ─── */
function cwc_render_dash_bookings($bookings)
{
	?>
	<div class="cwc-dash__card">
		<div class="cwc-dash__card-header cwc-dash__card-header--with-actions">
			<div class="cwc-dash__header-title">
				<h2>All Bookings</h2>
				<span class="cwc-dash__badge"><?php echo count($bookings); ?> records</span>
			</div>

			<!-- Search & Filter Bar -->
			<div class="cwc-dash__filters">
				<div class="cwc-dash__search-wrap">
					<span class="dashicons dashicons-search cwc-dash__search-icon"></span>
					<input type="text" id="cwc-booking-search" class="cwc-dash__search-input"
						placeholder="Search ref, name, email, phone..."
						value="<?php echo isset($_GET['s']) ? esc_attr(sanitize_text_field($_GET['s'])) : ''; ?>">
				</div>
				<select id="cwc-filter-status" class="cwc-dash__filter-select">
					<option value="all">All Bookings</option>
					<option value="pending">Pending</option>
					<option value="confirmed">Confirmed</option>
					<option value="cancelled">Cancelled</option>
					<option value="completed">Completed</option>
				</select>
				<select id="cwc-filter-payment" class="cwc-dash__filter-select">
					<option value="all">All Payments</option>
					<option value="unpaid">Unpaid</option>
					<option value="paid">Paid</option>
					<option value="failed">Failed</option>
					<option value="refunded">Refunded</option>
				</select>
			</div>
		</div>
		<?php if (empty($bookings)): ?>
			<div class="cwc-dash__empty">
				<span class="dashicons dashicons-clipboard"
					style="font-size: 48px; width: 48px; height: 48px; color: #cbd5e1; margin-bottom: 16px;"></span>
				<p>No bookings yet. Bookings will appear here once guests start reserving rooms.</p>
			</div>
		<?php else: ?>
			<div class="cwc-dash__table-wrap">
				<table class="cwc-dash__table" id="cwc-bookings-table">
					<thead>
						<tr>
							<th>Ref ID</th>
							<th>Guest</th>
							<th>Room</th>
							<th>Unit</th>
							<th>Check-in / Check-out</th>
							<th>Nights</th>
							<th>Amount</th>
							<th>Booking Status</th>
							<th>Payment Status</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($bookings as $b): ?>
							<tr class="cwc-dash__row-item" data-id="<?php echo esc_attr($b['id']); ?>"
								data-ref="<?php echo esc_attr(strtolower($b['ref'])); ?>"
								data-name="<?php echo esc_attr(strtolower($b['name'])); ?>"
								data-email="<?php echo esc_attr(strtolower($b['email'])); ?>"
								data-phone="<?php echo esc_attr(strtolower($b['phone'])); ?>"
								data-status="<?php echo esc_attr($b['status']); ?>"
								data-payment-status="<?php echo esc_attr($b['payment_status']); ?>">

								<td class="cwc-dash__td-ref"><strong><?php echo esc_html($b['ref']); ?></strong></td>
								<td>
									<div class="cwc-dash__guest-cell">
										<strong><?php echo esc_html($b['name']); ?></strong>
										<small><?php echo esc_html($b['email']); ?></small>
									</div>
								</td>
								<td><span class="cwc-dash__room-pill"><?php echo esc_html($b['room']); ?></span></td>
								<td>
									<?php if (!empty($b['assigned_room'])): ?>
										<span class="cwc-dash__unit-pill"><?php echo esc_html($b['assigned_room']); ?></span>
									<?php else: ?>
										<span style="color: #94a3b8; font-size: 12px;">—</span>
									<?php endif; ?>
								</td>
								<td>
									<div class="cwc-dash__date-cell">
										<span
											class="cwc-dash__date-in"><?php echo esc_html(date('M j', strtotime($b['checkin']))); ?></span>
										<span class="cwc-dash__date-sep">→</span>
										<span
											class="cwc-dash__date-out"><?php echo esc_html(date('M j', strtotime($b['checkout']))); ?></span>
									</div>
								</td>
								<td style="text-align: center; font-weight: 600;">
									<?php echo $b['nights'] ? esc_html($b['nights']) : '—'; ?>
								</td>
								<td class="cwc-dash__td-price"><?php echo esc_html($b['price']); ?></td>

								<td>
									<span class="cwc-dash__status-dot cwc-dash__status-dot--<?php echo esc_attr($b['status']); ?>">
										<?php echo esc_html(ucwords(str_replace('-', ' ', $b['status']))); ?>
									</span>
								</td>

								<td>
									<span
										class="cwc-dash__payment-badge cwc-dash__payment-badge--<?php echo esc_attr($b['payment_status']); ?>">
										<?php echo esc_html(ucwords($b['payment_status'])); ?>
									</span>
								</td>

								<td class="cwc-dash__td-actions">
									<div class="cwc-dash__actions-menu">
										<button class="cwc-dash__actions-btn js-actions-toggle">⋮</button>
										<div class="cwc-dash__actions-dropdown">
											<button class="js-action-change-status" data-id="<?php echo esc_attr($b['id']); ?>"
												data-ref="<?php echo esc_attr($b['ref']); ?>"
												data-name="<?php echo esc_attr($b['name']); ?>"
												data-email="<?php echo esc_attr($b['email']); ?>"
												data-status="<?php echo esc_attr($b['status']); ?>">
												Change Status
											</button>
											<button class="js-action-resend-email" data-id="<?php echo esc_attr($b['id']); ?>">
												Resend Email
											</button>
										</div>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php cwc_render_dash_pagination('cwc-bookings'); ?>
		<?php endif; ?>
	</div>
	<?php
}

/* ─── TAB: Guests ─── */
function cwc_render_dash_guests($bookings)
{
	// Calculate total people
	$total_people = 0;
	foreach ($bookings as $b) {
		$total_people += 1 + (!empty($b['guests']) ? count($b['guests']) : 0);
	}
	?>
	<div class="cwc-dash__card">
		<div class="cwc-dash__card-header cwc-dash__card-header--with-actions">
			<div class="cwc-dash__header-title">
				<h2>Guest Directory</h2>
				<span class="cwc-dash__badge"><?php echo $total_people; ?> people</span>
			</div>

			<!-- Search & Filter Bar -->
			<div class="cwc-dash__filters">
				<div class="cwc-dash__search-wrap">
					<span class="dashicons dashicons-search cwc-dash__search-icon"></span>
					<input type="text" id="cwc-guest-search" class="cwc-dash__search-input"
						placeholder="Search name, ref, email...">
				</div>
			</div>
		</div>
		<?php if (empty($bookings)): ?>
			<div class="cwc-dash__empty">
				<span class="dashicons dashicons-groups"
					style="font-size: 48px; width: 48px; height: 48px; color: #cbd5e1; margin-bottom: 16px;"></span>
				<p>No guest records found.</p>
			</div>
		<?php else: ?>
			<div class="cwc-dash__table-wrap">
				<table class="cwc-dash__table" id="cwc-guests-table">
					<thead>
						<tr>
							<th>Booking Ref</th>
							<th>Name</th>
							<th>Type</th>
							<th>Email</th>
							<th>Phone</th>
							<th>Room</th>
							<th>Booking Status</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($bookings as $b):
							$comp_count = !empty($b['guests']) ? count($b['guests']) : 0;
							$has_companions = $comp_count > 0;
							?>
							<!-- Primary Guest -->
							<tr class="cwc-dash__tr--primary cwc-dash__row-item <?php echo $has_companions ? 'cwc-dash__tr--has-companions' : ''; ?>"
								data-id="<?php echo esc_attr($b['id']); ?>"
								data-ref="<?php echo esc_attr(strtolower($b['ref'])); ?>"
								data-name="<?php echo esc_attr(strtolower($b['name'])); ?>"
								data-email="<?php echo esc_attr(strtolower($b['email'])); ?>">
								<td class="cwc-dash__td-ref"><strong><?php echo esc_html($b['ref']); ?></strong></td>
								<td>
									<div class="cwc-dash__guest-cell">
										<div class="cwc-dash__guest-name-wrap">
											<strong><?php echo esc_html($b['name']); ?></strong>
											<?php if ($has_companions): ?>
												<button class="cwc-dash__companion-toggle js-toggle-companions"
													aria-label="Toggle companions" data-id="<?php echo esc_attr($b['id']); ?>">
													<span class="dashicons dashicons-arrow-down-alt2"></span>
												</button>
											<?php endif; ?>
										</div>
										<?php if ($has_companions): ?>
											<span class="cwc-dash__companion-badge">
												+<?php echo esc_html($comp_count); ?>
												companion<?php echo $comp_count > 1 ? 's' : ''; ?>
											</span>
										<?php endif; ?>
									</div>
								</td>
								<td>
									<span class="cwc-dash__type-badge cwc-dash__type-badge--primary">Primary</span>
								</td>
								<td><?php echo esc_html($b['email']); ?></td>
								<td><?php echo esc_html($b['phone']); ?></td>
								<td><span class="cwc-dash__room-pill"><?php echo esc_html($b['room']); ?></span></td>
								<td>
									<span class="cwc-dash__status-dot cwc-dash__status-dot--<?php echo esc_attr($b['status']); ?>">
										<?php echo esc_html(ucwords(str_replace('-', ' ', $b['status']))); ?>
									</span>
								</td>
							</tr>

							<!-- Companion Rows (Hidden by default) -->
							<?php if ($has_companions): ?>
								<?php foreach ($b['guests'] as $g): ?>
									<tr class="cwc-dash__tr--companion cwc-dash__companion-row cwc-dash__row-item"
										data-parent-id="<?php echo esc_attr($b['id']); ?>"
										data-ref="<?php echo esc_attr(strtolower($b['ref'])); ?>"
										data-name="<?php echo esc_attr(strtolower($g['name'] ?? '')); ?>" style="display: none;">
										<td class="cwc-dash__td-ref"><strong><?php echo esc_html($b['ref']); ?></strong></td>
										<td>
											<div class="cwc-dash__guest-cell">
												<strong><?php echo esc_html($g['name'] ?? ''); ?></strong>
											</div>
										</td>
										<td>
											<span
												class="cwc-dash__type-badge cwc-dash__type-badge--<?php echo esc_attr(strtolower($g['type'] ?? 'adult')); ?>">
												<?php echo esc_html(ucfirst($g['type'] ?? 'adult')); ?>
											</span>
										</td>
										<td>—</td>
										<td>—</td>
										<td><span class="cwc-dash__room-pill"><?php echo esc_html($b['room']); ?></span></td>
										<td>
											<span class="cwc-dash__status-dot cwc-dash__status-dot--<?php echo esc_attr($b['status']); ?>">
												<?php echo esc_html(ucwords(str_replace('-', ' ', $b['status']))); ?>
											</span>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php cwc_render_dash_pagination('cwc-guests'); ?>
		<?php endif; ?>
	</div>
	<?php
}

/* ─── TAB: Payments ─── */
function cwc_render_dash_payments($bookings)
{
	?>
	<div class="cwc-dash__card">
		<div class="cwc-dash__card-header cwc-dash__card-header--with-actions">
			<div class="cwc-dash__header-title">
				<h2>Payment Tracking</h2>
				<span class="cwc-dash__badge"><?php echo count($bookings); ?> records</span>
			</div>

			<div class="cwc-dash__filters">
				<div class="cwc-dash__search-wrap">
					<span class="dashicons dashicons-search cwc-dash__search-icon"></span>
					<input type="text" id="cwc-payment-search" class="cwc-dash__search-input"
						placeholder="Search Transaction ID, Ref, Name...">
				</div>
			</div>
		</div>
		<?php if (empty($bookings)): ?>
			<div class="cwc-dash__empty">
				<span class="dashicons dashicons-money-alt"
					style="font-size: 48px; width: 48px; height: 48px; color: #cbd5e1; margin-bottom: 16px;"></span>
				<p>No payment records found.</p>
			</div>
		<?php else: ?>
			<div class="cwc-dash__table-wrap">
				<table class="cwc-dash__table" id="cwc-payments-table">
					<thead>
						<tr>
							<th>Transaction ID</th>
							<th>Booking Ref</th>
							<th>Guest</th>
							<th>Method</th>
							<th>Amount</th>
							<th>Payment Status</th>
							<th>Date</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($bookings as $b):
							$tx_id = $b['transaction_id'] ?: 'N/A';
							?>
							<tr class="cwc-dash__row-item" data-id="<?php echo esc_attr($b['id']); ?>"
								data-tx="<?php echo esc_attr(strtolower($tx_id)); ?>"
								data-ref="<?php echo esc_attr(strtolower($b['ref'])); ?>"
								data-name="<?php echo esc_attr(strtolower($b['name'])); ?>">

								<td class="cwc-dash__td-tx">
									<?php if ($tx_id !== 'N/A'): ?>
										<code><?php echo esc_html($tx_id); ?></code>
									<?php else: ?>
										<span style="color: #94a3b8;">—</span>
									<?php endif; ?>
								</td>
								<td class="cwc-dash__td-ref"><strong><?php echo esc_html($b['ref']); ?></strong></td>
								<td><?php echo esc_html($b['name']); ?></td>
								<td>
									<span class="cwc-dash__payment-tag"><?php echo esc_html(strtoupper($b['payment'])); ?></span>
								</td>
								<td class="cwc-dash__td-price"><?php echo esc_html($b['price']); ?></td>
								<td>
									<span
										class="cwc-dash__payment-badge cwc-dash__payment-badge--<?php echo esc_attr($b['payment_status']); ?>">
										<?php echo esc_html(ucwords($b['payment_status'])); ?>
									</span>
								</td>
								<td><small><?php echo esc_html(date('M j, Y g:i A', strtotime($b['date']))); ?></small></td>

								<td class="cwc-dash__td-actions">
									<div class="cwc-dash__actions-menu">
										<button class="cwc-dash__actions-btn js-actions-toggle">⋮</button>
										<div class="cwc-dash__actions-dropdown cwc-dash__actions-dropdown--right">
											<button class="js-action-update-payment" data-id="<?php echo esc_attr($b['id']); ?>"
												data-status="paid">
												Mark as Paid
											</button>
											<button class="js-action-update-payment" data-id="<?php echo esc_attr($b['id']); ?>"
												data-status="refunded">
												Mark as Refunded
											</button>
										</div>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php cwc_render_dash_pagination('cwc-payments'); ?>
		<?php endif; ?>
	</div>
	<?php
}

/* ─── TAB: Room Tracking ─── */
function cwc_render_dash_room_tracking($bookings)
{
	$rooms = get_posts([
		'post_type' => 'accommodation',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'orderby' => 'menu_order',
		'order' => 'ASC',
	]);

	$today = date('Y-m-d');
	?>
	<div class="cwc-dash__card">
		<div class="cwc-dash__card-header">
			<div class="cwc-dash__header-title">
				<h2>Room Units Tracking</h2>
				<span class="cwc-dash__badge"><?php echo count($rooms); ?> room types</span>
			</div>
		</div>

		<?php foreach ($rooms as $room_post):
			$physical_rooms = cwc_get_physical_rooms($room_post->ID);
			$capacity = (int) get_post_meta($room_post->ID, '_cwc_capacity', true);
			$total_units = max(count($physical_rooms), 1);

			$active_bookings_for_room = array_filter($bookings, function ($b) use ($room_post, $today) {
				$b_room = strtolower(trim($b['room']));
				$rp_title = strtolower(trim($room_post->post_title));
				$rp_title_clean = preg_replace('/\s+Room$/i', '', $rp_title);

				if ($b_room !== $rp_title && $b_room !== $rp_title_clean && preg_replace('/\s+Room$/i', '', $b_room) !== $rp_title_clean)
					return false;

				if (in_array($b['status'], ['cancelled', 'completed'], true))
					return false;
				$ci = $b['checkin'] ? date('Y-m-d', strtotime($b['checkin'])) : '';
				$co = $b['checkout'] ? date('Y-m-d', strtotime($b['checkout'])) : '';
				if (!$ci || !$co)
					return false;
				return ($ci <= $today && $co >= $today);
			});

			$manual_booked = 0;
			if (!empty($physical_rooms)) {
				foreach ($physical_rooms as $u) {
					if (($u['status'] ?? 'available') === 'booked')
						$manual_booked++;
				}
			}

			$booking_count = count($active_bookings_for_room);
			$total_occupied = $booking_count + $manual_booked;
			$total_units = !empty($physical_rooms) ? count($physical_rooms) : max(1, (int) get_post_meta($room_post->ID, '_cwc_inventory', true));
			$available_count = max(0, $total_units - $total_occupied);
			$is_fully_booked = ($available_count <= 0);
			?>
			<div class="cwc-dash__room-tracking-section"
				style="margin-bottom: 32px; padding: 20px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
					<div>
						<h3 style="margin: 0 0 4px; font-size: 18px; font-weight: 700;">
							<?php echo esc_html($room_post->post_title); ?>
						</h3>
						<span style="font-size: 13px; color: #64748b;">Max <?php echo esc_html($capacity); ?> guests ·
							<?php echo esc_html($total_units); ?> unit<?php echo $total_units > 1 ? 's' : ''; ?></span>
					</div>
					<div style="display: flex; gap: 12px; align-items: center;">
						<span
							style="padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: <?php echo $is_fully_booked ? '#fef2f2' : '#f0fdf4'; ?>; color: <?php echo $is_fully_booked ? '#dc2626' : '#16a34a'; ?>;">
							<?php echo $is_fully_booked ? 'Fully Booked' : $available_count . ' Available'; ?>
						</span>
						<span
							style="padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #eff6ff; color: #2563eb;">
							<?php echo $total_occupied; ?> Occupied Today
						</span>
					</div>
				</div>

				<?php if (!empty($physical_rooms)): ?>
					<div class="cwc-dash__table-wrap">
						<table class="cwc-dash__table" style="margin: 0;">
							<thead>
								<tr>
									<th>Unit Name</th>
									<th>Status</th>
									<th>REF ID</th>
									<th>Check-in</th>
									<th>Check-out</th>
									<th>Guest</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$unassigned_bookings = $active_bookings_for_room;
								foreach ($physical_rooms as $idx => $unit):
									$unit_status = ($unit['status'] ?? 'available');

									// Improved Sync: Check for actual assignments first
									$unit_booking = null;
									foreach ($active_bookings_for_room as $ab) {
										if (($ab['assigned_room'] ?? '') === $unit['name']) {
											$unit_booking = $ab;
											break;
										}
									}

									// Fallback: If not explicitly assigned but we have unassigned bookings, guess (for legacy/missing data)
									if (!$unit_booking && $unit_status !== 'booked') {
										foreach ($unassigned_bookings as $key => $ab) {
											if (empty($ab['assigned_room'])) {
												$unit_booking = $ab;
												unset($unassigned_bookings[$key]);
												break;
											}
										}
									}

									$is_occupied = ($unit_status === 'booked' || $unit_booking);
									?>
									<tr class="cwc-dash__row-item">
										<td><strong><?php echo esc_html($unit['name']); ?></strong></td>
										<td>
											<div style="display: flex; align-items: center; gap: 8px;">
												<span
													class="cwc-dash__status-dot cwc-dash__status-dot--<?php echo $is_occupied ? 'pending' : 'confirmed'; ?>">
													<?php echo $is_occupied ? 'Occupied' : 'Available'; ?>
												</span>
												<button class="button button-small js-toggle-unit-status"
													data-room-id="<?php echo esc_attr($room_post->ID); ?>"
													data-unit-name="<?php echo esc_attr($unit['name']); ?>"
													data-current-status="<?php echo esc_attr($unit_status); ?>"
													title="Manually toggle status">
													<?php echo $unit_status === 'booked' ? 'Release' : 'Block'; ?>
												</button>
											</div>
										</td>
										<td>
											<?php if ($unit_booking): ?>
												<a href="#" class="js-dash-nav-booking"
													data-ref="<?php echo esc_attr($unit_booking['ref']); ?>"
													style="text-decoration: none; font-weight: 700; color: #2563eb;">
													<?php echo esc_html($unit_booking['ref']); ?>
												</a>
											<?php elseif ($is_occupied): ?>
												<span style="color: #64748b; font-style: italic;">Blocked / House Use</span>
											<?php else: ?>
												<span style="color: #94a3b8;">—</span>
											<?php endif; ?>
										</td>
										<td>
											<?php if ($unit_booking): ?>
												<?php echo esc_html(date('M j, Y', strtotime($unit_booking['checkin']))); ?>
											<?php elseif ($is_occupied): ?>
												<span style="color: #94a3b8;">Manual</span>
											<?php else: ?>
												<span style="color: #94a3b8;">—</span>
											<?php endif; ?>
										</td>
										<td>
											<?php if ($unit_booking): ?>
												<?php echo esc_html(date('M j, Y', strtotime($unit_booking['checkout']))); ?>
											<?php elseif ($is_occupied): ?>
												<span style="color: #94a3b8;">Manual</span>
											<?php else: ?>
												<span style="color: #94a3b8;">—</span>
											<?php endif; ?>
										</td>
										<td>
											<?php if ($unit_booking): ?>
												<?php echo esc_html($unit_booking['name']); ?>
											<?php elseif ($is_occupied): ?>
												<span style="color: #64748b;">(Manual Block)</span>
											<?php else: ?>
												<span style="color: #94a3b8;">—</span>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					<?php
					if (count($physical_rooms) > 5) {
						cwc_render_dash_pagination('cwc-room-units-' . $room_post->ID);
					}
					?>
				<?php else: ?>
					<div style="padding: 16px; text-align: center; color: #94a3b8; font-size: 14px;">
						<p>No physical rooms defined. Add units in the room editor to track individual availability.</p>
					</div>
				<?php endif; ?>

				<?php if (!empty($active_bookings_for_room)): ?>
					<div style="margin-top: 16px;">
						<h4 style="font-size: 14px; font-weight: 600; margin: 0 0 8px; color: #475569;">Active Bookings for Today
						</h4>
						<div class="cwc-dash__schedule-list">
							<?php foreach ($active_bookings_for_room as $ab): ?>
								<div class="cwc-dash__schedule-item" style="padding: 8px 12px;">
									<div class="cwc-dash__schedule-info" style="flex: 1;">
										<strong><?php echo esc_html($ab['name']); ?></strong>
										<small><?php echo esc_html($ab['ref']); ?> · <?php echo esc_html($ab['checkin']); ?> →
											<?php echo esc_html($ab['checkout']); ?></small>
									</div>
									<span class="cwc-dash__status-dot cwc-dash__status-dot--<?php echo esc_attr($ab['status']); ?>">
										<?php echo esc_html(ucwords(str_replace('-', ' ', $ab['status']))); ?>
									</span>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/* ─── TAB: Availability ─── */
function cwc_render_dash_availability($bookings)
{
	// Get all accommodation posts for room inventory
	$rooms = get_posts([
		'post_type' => 'accommodation',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'orderby' => 'menu_order',
		'order' => 'ASC',
	]);

	// Count active bookings per room
	$active_per_room = [];
	foreach ($bookings as $b) {
		if (in_array($b['status'], ['confirmed', 'checked-in', 'pending'], true)) {
			$r = $b['room'] ?: 'Unknown';
			$active_per_room[$r] = ($active_per_room[$r] ?? 0) + 1;
		}
	}

	// Upcoming bookings (next 14 days)
	$upcoming = [];
	$now = time();
	$two_weeks = $now + (14 * DAY_IN_SECONDS);
	foreach ($bookings as $b) {
		if (in_array($b['status'], ['cancelled'], true))
			continue;
		$ci = strtotime($b['checkin']);
		if ($ci && $ci >= $now && $ci <= $two_weeks) {
			$upcoming[] = $b;
		}
	}
	usort($upcoming, fn($a, $c) => strtotime($a['checkin']) - strtotime($c['checkin']));
	?>
	<div class="cwc-dash__grid-2">
		<!-- Room Status -->
		<div class="cwc-dash__card">
			<div class="cwc-dash__card-header">
				<h2>Room Units Status Overview</h2>
			</div>
			<div class="cwc-dash__room-grid">
				<?php foreach ($rooms as $room_post):
					$title = $room_post->post_title;
					$capacity = (int) get_post_meta($room_post->ID, '_cwc_capacity', true);

					// Get physical units for more accurate occupancy
					$units = cwc_get_physical_rooms($room_post->ID);
					$total_units = !empty($units) ? count($units) : max(1, (int) get_post_meta($room_post->ID, '_cwc_inventory', true));

					$manual_booked = 0;
					if (!empty($units)) {
						foreach ($units as $u) {
							if (($u['status'] ?? 'available') === 'booked')
								$manual_booked++;
						}
					}

					$confirmed_active = $active_per_room[$title] ?? 0;
					$total_occupied = $confirmed_active + $manual_booked;
					$avail = max(0, $total_units - $total_occupied);
					$pct = $total_units > 0 ? round(($total_occupied / $total_units) * 100) : 0;
					$bar_cls = $pct >= 80 ? 'high' : ($pct >= 50 ? 'mid' : 'low');
					?>
					<div class="cwc-dash__room-card">
						<div class="cwc-dash__room-card-header">
							<h3><?php echo esc_html($title); ?></h3>
							<span class="cwc-dash__room-cap">Max <?php echo esc_html($capacity); ?> guests</span>
						</div>
						<div class="cwc-dash__room-bar-wrap">
							<div class="cwc-dash__room-bar cwc-dash__room-bar--<?php echo esc_attr($bar_cls); ?>"
								style="width: <?php echo esc_attr($pct); ?>%"></div>
						</div>
						<div class="cwc-dash__room-meta">
							<span><?php echo esc_html($total_occupied); ?> occupied</span>
							<span><?php echo esc_html($avail); ?> available</span>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Upcoming Schedule -->
		<div class="cwc-dash__card">
			<div class="cwc-dash__card-header">
				<h2>Upcoming Check-ins (14 days)</h2>
				<span class="cwc-dash__badge"><?php echo count($upcoming); ?></span>
			</div>
			<?php if (empty($upcoming)): ?>
				<div class="cwc-dash__empty">
					<span class="dashicons dashicons-calendar-alt"
						style="font-size: 48px; width: 48px; height: 48px; color: #cbd5e1; margin-bottom: 16px;"></span>
					<p>No upcoming check-ins in the next 14 days.</p>
				</div>
			<?php else: ?>
				<div class="cwc-dash__schedule-list">
					<?php foreach ($upcoming as $u): ?>
						<div class="cwc-dash__schedule-item">
							<div class="cwc-dash__schedule-date">
								<?php
								$ci_ts = strtotime($u['checkin']);
								echo '<span class="cwc-dash__schedule-day">' . esc_html(date('d', $ci_ts)) . '</span>';
								echo '<span class="cwc-dash__schedule-month">' . esc_html(date('M', $ci_ts)) . '</span>';
								?>
							</div>
							<div class="cwc-dash__schedule-info">
								<strong><?php echo esc_html($u['name']); ?></strong>
								<small><?php echo esc_html($u['room']); ?> · <?php echo esc_html($u['checkin']); ?> →
									<?php echo esc_html($u['checkout']); ?></small>
							</div>
							<span class="cwc-dash__status-dot cwc-dash__status-dot--<?php echo esc_attr($u['status']); ?>">
								<?php echo esc_html(ucwords(str_replace('-', ' ', $u['status']))); ?>
							</span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/* ─── TAB: Analytics ─── */
function cwc_render_dash_analytics($bookings, $room_counts, $room_revenue, $total_revenue)
{
	// Monthly revenue data (last 6 months)
	$monthly = [];
	for ($i = 5; $i >= 0; $i--) {
		$month_key = date('Y-m', strtotime("-{$i} months"));
		$month_label = date('M Y', strtotime("-{$i} months"));
		$monthly[$month_key] = ['label' => $month_label, 'revenue' => 0, 'count' => 0];
	}
	foreach ($bookings as $b) {
		$mk = date('Y-m', strtotime($b['date']));
		if (isset($monthly[$mk])) {
			$monthly[$mk]['revenue'] += $b['price_num'];
			$monthly[$mk]['count']++;
		}
	}
	$max_rev = max(array_column($monthly, 'revenue')) ?: 1;

	// Payment method breakdown
	$payment_counts = [];
	foreach ($bookings as $b) {
		$pm = strtoupper($b['payment'] ?: 'OTHER');
		$payment_counts[$pm] = ($payment_counts[$pm] ?? 0) + 1;
	}
	?>
	<div class="cwc-dash__grid-2">
		<!-- Revenue Chart -->
		<div class="cwc-dash__card">
			<div class="cwc-dash__card-header">
				<h2>Revenue (Last 6 Months)</h2>
			</div>
			<div class="cwc-dash__chart">
				<?php foreach ($monthly as $m):
					$pct = $max_rev > 0 ? round(($m['revenue'] / $max_rev) * 100) : 0;
					?>
					<div class="cwc-dash__chart-col">
						<div class="cwc-dash__chart-bar-wrap">
							<div class="cwc-dash__chart-bar" style="height: <?php echo esc_attr($pct); ?>%"
								title="₱<?php echo esc_attr(number_format($m['revenue'], 2)); ?>">
								<?php if ($m['revenue'] > 0): ?>
									<span
										class="cwc-dash__chart-val">₱<?php echo esc_html(number_format($m['revenue'] / 1000, 1)); ?>k</span>
								<?php endif; ?>
							</div>
						</div>
						<span class="cwc-dash__chart-label"><?php echo esc_html($m['label']); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Room Breakdown -->
		<div class="cwc-dash__card">
			<div class="cwc-dash__card-header">
				<h2>Bookings by Room Units</h2>
			</div>
			<?php if (empty($room_counts)): ?>
				<div class="cwc-dash__empty">
					<p>No data yet.</p>
				</div>
			<?php else: ?>
				<div class="cwc-dash__breakdown">
					<?php
					$total_bk = array_sum($room_counts);
					foreach ($room_counts as $room_name => $cnt):
						$pct = $total_bk > 0 ? round(($cnt / $total_bk) * 100) : 0;
						$rev = $room_revenue[$room_name] ?? 0;
						?>
						<div class="cwc-dash__breakdown-row">
							<div class="cwc-dash__breakdown-label">
								<span class="cwc-dash__room-pill"><?php echo esc_html($room_name); ?></span>
								<span class="cwc-dash__breakdown-stats"><?php echo esc_html($cnt); ?> bookings ·
									₱<?php echo esc_html(number_format($rev, 2)); ?></span>
							</div>
							<div class="cwc-dash__breakdown-bar-wrap">
								<div class="cwc-dash__breakdown-bar" style="width: <?php echo esc_attr($pct); ?>%"></div>
							</div>
							<span class="cwc-dash__breakdown-pct"><?php echo esc_html($pct); ?>%</span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- Payment Methods -->
		<div class="cwc-dash__card">
			<div class="cwc-dash__card-header">
				<h2>Payment Methods</h2>
			</div>
			<?php if (empty($payment_counts)): ?>
				<div class="cwc-dash__empty">
					<p>No payment data.</p>
				</div>
			<?php else: ?>
				<div class="cwc-dash__payment-grid">
					<?php foreach ($payment_counts as $pm => $cnt): ?>
						<div class="cwc-dash__payment-card">
							<div class="cwc-dash__payment-name"><?php echo esc_html($pm); ?></div>
							<div class="cwc-dash__payment-count"><?php echo esc_html($cnt); ?></div>
							<div class="cwc-dash__payment-label">transactions</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<!-- Status Summary -->
		<div class="cwc-dash__card">
			<div class="cwc-dash__card-header">
				<h2>Status Summary</h2>
			</div>
			<?php
			$status_map = [
				'pending' => ['label' => 'Pending', 'color' => '#f59e0b'],
				'confirmed' => ['label' => 'Confirmed', 'color' => '#3b82f6'],
				'checked-in' => ['label' => 'Checked In', 'color' => '#10b981'],
				'checked-out' => ['label' => 'Checked Out', 'color' => '#6b7280'],
				'cancelled' => ['label' => 'Cancelled', 'color' => '#ef4444'],
			];
			$status_counts = [];
			foreach ($bookings as $b) {
				$s = $b['status'];
				$status_counts[$s] = ($status_counts[$s] ?? 0) + 1;
			}
			?>
			<div class="cwc-dash__status-grid">
				<?php foreach ($status_map as $key => $meta):
					$cnt = $status_counts[$key] ?? 0;
					?>
					<div class="cwc-dash__status-card" style="border-left: 4px solid <?php echo esc_attr($meta['color']); ?>">
						<div class="cwc-dash__status-count"><?php echo esc_html($cnt); ?></div>
						<div class="cwc-dash__status-label"><?php echo esc_html($meta['label']); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
}
