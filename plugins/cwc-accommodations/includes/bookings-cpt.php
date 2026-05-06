<?php
/**
 * CWC Booking CPT — hidden post type for storing booking records.
 *
 * Handles:
 *   - CPT registration
 *   - Booking reference ID generation
 *   - AJAX status updates with optional email + admin note
 *   - Audit logging
 *   - Status-specific email templates
 *
 * @package CWC_Accommodations
 * @since   2.0.0
 */

if (!defined('ABSPATH')) {
	exit;
}

/* ────────────────────────────────────────────
   CPT Registration
   ──────────────────────────────────────────── */

function cwc_register_booking_cpt()
{
	register_post_type('cwc_booking', [
		'labels' => [
			'name' => __('Bookings', 'cwc-accommodations'),
			'singular_name' => __('Booking', 'cwc-accommodations'),
		],
		'public' => false,
		'show_ui' => false,
		'show_in_menu' => false,
		'show_in_rest' => false,
		'supports' => ['title'],
		'exclude_from_search' => true,
		'publicly_queryable' => false,
		'capability_type' => 'post',
		'map_meta_cap' => true,
	]);
}
add_action('init', 'cwc_register_booking_cpt', 8);

/* ────────────────────────────────────────────
   Booking Reference Generator
   ──────────────────────────────────────────── */

/**
 * Generate a unique booking reference like CWC-A7B9X2.
 *
 * @return string Unique reference ID.
 */
function cwc_generate_booking_ref()
{
	$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // No 0/O/1/I to avoid confusion
	$attempts = 0;

	do {
		$ref = 'CWC-';
		for ($i = 0; $i < 6; $i++) {
			$ref .= $chars[wp_rand(0, strlen($chars) - 1)];
		}
		// Check uniqueness
		$existing = get_posts([
			'post_type' => 'cwc_booking',
			'meta_key' => '_cwc_bk_ref',
			'meta_value' => $ref,
			'numberposts' => 1,
			'fields' => 'ids',
		]);
		$attempts++;
	} while (!empty($existing) && $attempts < 10);

	return $ref;
}

/**
 * Generate a sequential transaction ID like TX-00001.
 *
 * @return string Sequential transaction ID.
 */
function cwc_generate_transaction_id()
{
	$last_id = (int) get_option('cwc_last_tx_id', 0);
	$next_id = $last_id + 1;
	update_option('cwc_last_tx_id', $next_id);
	return 'TX-' . str_pad($next_id, 5, '0', STR_PAD_LEFT);
}

/**
 * Auto-generate a reference for bookings that don't have one yet.
 * Called on post save for the cwc_booking type.
 */
function cwc_maybe_assign_booking_ref($post_id, $post, $update)
{
	if ('cwc_booking' !== $post->post_type) {
		return;
	}

	$existing_ref = get_post_meta($post_id, '_cwc_bk_ref', true);
	if (empty($existing_ref)) {
		$ref = cwc_generate_booking_ref();
		update_post_meta($post_id, '_cwc_bk_ref', $ref);
	}

	// Also ensure payment_status exists
	$payment_status = get_post_meta($post_id, '_cwc_bk_payment_status', true);
	if (empty($payment_status)) {
		update_post_meta($post_id, '_cwc_bk_payment_status', 'unpaid');
	}

	// Generate sequential Transaction ID if missing
	$existing_tx = get_post_meta($post_id, '_cwc_bk_transaction_id', true);
	if (empty($existing_tx)) {
		$tx_id = cwc_generate_transaction_id();
		update_post_meta($post_id, '_cwc_bk_transaction_id', $tx_id);
	}
}
add_action('wp_insert_post', 'cwc_maybe_assign_booking_ref', 10, 3);

/* ────────────────────────────────────────────
   Audit Logging
   ──────────────────────────────────────────── */

/**
 * Append an entry to the booking's audit log.
 *
 * @param int    $booking_id  The booking post ID.
 * @param string $action      The action type (e.g. 'status_changed').
 * @param array  $details     Additional details to log.
 */
function cwc_add_audit_log($booking_id, $action, $details = [])
{
	$log = json_decode(get_post_meta($booking_id, '_cwc_bk_audit_log', true) ?: '[]', true);

	$current_user = wp_get_current_user();

	$log[] = array_merge([
		'action' => $action,
		'admin' => $current_user->user_login ?? 'system',
		'timestamp' => current_time('mysql'),
	], $details);

	update_post_meta($booking_id, '_cwc_bk_audit_log', wp_json_encode($log));
}

/* ────────────────────────────────────────────
   Email Logging
   ──────────────────────────────────────────── */

/**
 * Log an email send attempt.
 *
 * @param int    $booking_id  The booking post ID.
 * @param string $type        Email type (e.g. 'status_confirmed').
 * @param string $to          Recipient email.
 * @param bool   $sent        Whether wp_mail returned true.
 * @param string $admin_note  Optional admin note included.
 */
function cwc_log_email($booking_id, $type, $to, $sent, $admin_note = '')
{
	$log = json_decode(get_post_meta($booking_id, '_cwc_bk_email_log', true) ?: '[]', true);

	$log[] = [
		'type' => $type,
		'to' => $to,
		'sent' => $sent,
		'admin_note' => $admin_note,
		'timestamp' => current_time('mysql'),
	];

	update_post_meta($booking_id, '_cwc_bk_email_log', wp_json_encode($log));
}

function cwc_sync_booking_room_link($booking_id, $room_post_id = 0)
{
	$booking_id = (int) $booking_id;
	$room_post_id = (int) $room_post_id;

	if ($booking_id <= 0) {
		return 0;
	}

	if ($room_post_id <= 0 && function_exists('cwc_get_booking_room_post_id')) {
		$room_post_id = cwc_get_booking_room_post_id($booking_id);
	}

	if ($room_post_id <= 0 || 'accommodation' !== get_post_type($room_post_id)) {
		return 0;
	}

	update_post_meta($booking_id, '_cwc_bk_room_post_id', $room_post_id);

	if ((int) get_post_field('post_parent', $booking_id) !== $room_post_id) {
		wp_update_post([
			'ID' => $booking_id,
			'post_parent' => $room_post_id,
		]);
	}

	return $room_post_id;
}

function cwc_assign_available_unit_to_booking($booking_id, $room_post_id = 0, $checkin = '', $checkout = '')
{
	$booking_id = (int) $booking_id;
	$room_post_id = cwc_sync_booking_room_link($booking_id, $room_post_id);

	if ($room_post_id <= 0 || !function_exists('cwc_find_available_unit_for_booking')) {
		return null;
	}

	$checkin = $checkin ?: (string) get_post_meta($booking_id, '_cwc_bk_checkin', true);
	$checkout = $checkout ?: (string) get_post_meta($booking_id, '_cwc_bk_checkout', true);

	if (!$checkin || !$checkout) {
		return null;
	}

	$unit = cwc_find_available_unit_for_booking(
		$room_post_id,
		date('Y-m-d', strtotime($checkin)),
		date('Y-m-d', strtotime($checkout)),
		$booking_id
	);

	if (!$unit || empty($unit['id'])) {
		return null;
	}

	update_post_meta($booking_id, '_cwc_bk_assigned_unit_id', $unit['id']);
	update_post_meta($booking_id, '_cwc_bk_assigned_room', $unit['name'] ?? '');

	return $unit;
}

function cwc_release_legacy_booked_unit_for_booking($booking_id)
{
	if (!function_exists('cwc_find_physical_room_by_booking')) {
		return;
	}

	$match = cwc_find_physical_room_by_booking($booking_id);
	if (!$match || !is_array($match)) {
		return;
	}

	$room = $match['room'] ?? [];
	if (($room['status'] ?? 'available') !== 'booked') {
		return;
	}

	$room_post_id = (int) ($match['post_id'] ?? 0);
	$checkin = (string) get_post_meta($booking_id, '_cwc_bk_checkin', true);
	$checkout = (string) get_post_meta($booking_id, '_cwc_bk_checkout', true);
	if ($room_post_id <= 0 || !$checkin || !$checkout) {
		return;
	}

	$unit_id = (string) ($room['id'] ?? '');
	foreach (cwc_get_overlapping_booking_ids_for_room_post($room_post_id, date('Y-m-d', strtotime($checkin)), date('Y-m-d', strtotime($checkout)), $booking_id) as $other_booking_id) {
		if ($unit_id && $unit_id === cwc_get_booking_assigned_unit_id($other_booking_id)) {
			return;
		}
	}

	$physical_rooms = cwc_get_physical_rooms($room_post_id);
	$index = (int) ($match['index'] ?? -1);
	if ($index < 0 || !isset($physical_rooms[$index])) {
		return;
	}

	$physical_rooms[$index]['status'] = 'available';
	cwc_update_physical_rooms($room_post_id, $physical_rooms);
}

/* ────────────────────────────────────────────
   AJAX: Update Booking Status (with modal)
   ──────────────────────────────────────────── */

function cwc_update_booking_status()
{
	if (!current_user_can('manage_options')) {
		wp_send_json_error(['message' => 'Unauthorized']);
	}

	$booking_id = isset($_POST['booking_id']) ? absint($_POST['booking_id']) : 0;
	$new_status = isset($_POST['new_status']) ? sanitize_key($_POST['new_status']) : '';
	$send_email = isset($_POST['send_email']) && $_POST['send_email'] === 'true';
	$admin_note = isset($_POST['admin_note']) ? sanitize_textarea_field(wp_unslash($_POST['admin_note'])) : '';

	$valid_statuses = ['pending', 'confirmed', 'cancelled', 'completed'];

	if (!$booking_id || !in_array($new_status, $valid_statuses, true)) {
		wp_send_json_error(['message' => 'Invalid data.']);
	}

	$old_status = get_post_meta($booking_id, '_cwc_bk_status', true);

	// Update status
	update_post_meta($booking_id, '_cwc_bk_status', $new_status);

	// Audit log
	cwc_add_audit_log($booking_id, 'status_changed', [
		'from' => $old_status,
		'to' => $new_status,
		'note' => $admin_note,
		'email_sent' => $send_email,
	]);

	// Auto-release legacy unit locks for older bookings that still marked the unit itself as booked.
	if (in_array($new_status, ['completed', 'cancelled'], true) && !in_array($old_status, ['completed', 'cancelled'], true)) {
		cwc_release_legacy_booked_unit_for_booking($booking_id);
	}

	// Send email if requested
	$email_result = false;
	if ($send_email && $old_status !== $new_status) {
		$email_result = cwc_send_booking_status_email($booking_id, $new_status, $admin_note);
	}

	wp_send_json_success([
		'message' => 'Status updated.',
		'email_sent' => $email_result,
		'new_status' => $new_status,
	]);
}
add_action('wp_ajax_cwc_update_booking_status', 'cwc_update_booking_status');

/* ────────────────────────────────────────────
   AJAX: Update Payment Status
   ──────────────────────────────────────────── */

function cwc_update_payment_status()
{
	if (!current_user_can('manage_options')) {
		wp_send_json_error(['message' => 'Unauthorized']);
	}

	$booking_id = isset($_POST['booking_id']) ? absint($_POST['booking_id']) : 0;
	$new_status = isset($_POST['payment_status']) ? sanitize_key($_POST['payment_status']) : '';

	$valid = ['unpaid', 'paid', 'failed', 'refunded'];

	if (!$booking_id || !in_array($new_status, $valid, true)) {
		wp_send_json_error(['message' => 'Invalid data.']);
	}

	$old_status = get_post_meta($booking_id, '_cwc_bk_payment_status', true);
	update_post_meta($booking_id, '_cwc_bk_payment_status', $new_status);

	cwc_add_audit_log($booking_id, 'payment_status_changed', [
		'from' => $old_status,
		'to' => $new_status,
	]);

	wp_send_json_success([
		'message' => 'Payment status updated.',
		'new_status' => $new_status,
	]);
}
add_action('wp_ajax_cwc_update_payment_status', 'cwc_update_payment_status');

/* ────────────────────────────────────────────
   AJAX: Resend Last Email
   ──────────────────────────────────────────── */

function cwc_resend_booking_email()
{
	if (!current_user_can('manage_options')) {
		wp_send_json_error(['message' => 'Unauthorized']);
	}

	$booking_id = isset($_POST['booking_id']) ? absint($_POST['booking_id']) : 0;
	if (!$booking_id) {
		wp_send_json_error(['message' => 'Invalid booking.']);
	}

	$status = get_post_meta($booking_id, '_cwc_bk_status', true);
	$result = cwc_send_booking_status_email($booking_id, $status, '');

	cwc_add_audit_log($booking_id, 'email_resent', [
		'status' => $status,
		'result' => $result,
	]);

	if ($result) {
		wp_send_json_success(['message' => 'Email resent successfully.']);
	} else {
		wp_send_json_error(['message' => 'Failed to send email.']);
	}
}
add_action('wp_ajax_cwc_resend_booking_email', 'cwc_resend_booking_email');

/* ────────────────────────────────────────────
   AJAX: Toggle Physical Room Status
   ──────────────────────────────────────────── */
function cwc_toggle_physical_room_status()
{
	if (!current_user_can('manage_options')) {
		wp_send_json_error(['message' => 'Unauthorized']);
	}

	$room_id = isset($_POST['room_id']) ? absint($_POST['room_id']) : 0;
	$unit_id = isset($_POST['unit_id']) ? preg_replace('/[^a-z0-9_-]/i', '', (string) wp_unslash($_POST['unit_id'])) : '';
	$unit_name = isset($_POST['unit_name']) ? sanitize_text_field(wp_unslash($_POST['unit_name'])) : '';
	$new_status = isset($_POST['new_status']) ? sanitize_key($_POST['new_status']) : '';

	if (!$room_id || (!$unit_id && !$unit_name) || !in_array($new_status, ['available', 'booked'], true)) {
		wp_send_json_error(['message' => 'Invalid data.']);
	}

	if (function_exists('cwc_get_physical_rooms')) {
		$physical_rooms = cwc_get_physical_rooms($room_id);
		$updated = false;
		foreach ($physical_rooms as &$p_room) {
			$matches_id = $unit_id && ($p_room['id'] ?? '') === strtolower($unit_id);
			$matches_name = !$unit_id && ($p_room['name'] ?? '') === $unit_name;
			if ($matches_id || $matches_name) {
				$p_room['status'] = $new_status;
				$updated = true;
				break;
			}
		}

		if ($updated) {
			cwc_update_physical_rooms($room_id, $physical_rooms);
			wp_send_json_success(['message' => 'Unit status updated.']);
		}
	}

	wp_send_json_error(['message' => 'Failed to update unit status.']);
}
add_action('wp_ajax_cwc_toggle_physical_room_status', 'cwc_toggle_physical_room_status');


/* ────────────────────────────────────────────
   AJAX: Check Room Availability by Dates
   ──────────────────────────────────────────── */

/**
 * Check if a room should be treated as available for the given date range.
 *
 * Current booking UX treats any existing reservation for the same room and
 * date range as unavailable on the public booking calendars, even when the
 * room type has multiple physical units configured in the dashboard.
 */
function cwc_check_room_availability()
{
	$room_name = isset($_POST['room']) ? sanitize_text_field(wp_unslash($_POST['room'])) : '';
	$checkin = isset($_POST['checkin']) ? sanitize_text_field(wp_unslash($_POST['checkin'])) : '';
	$checkout = isset($_POST['checkout']) ? sanitize_text_field(wp_unslash($_POST['checkout'])) : '';

	if (empty($room_name) || 'Choose Room' === $room_name || empty($checkin) || empty($checkout)) {
		wp_send_json_error(['message' => 'Please select a room and specify check-in/check-out dates.']);
	}

	$checkin_date = date('Y-m-d', strtotime($checkin));
	$checkout_date = date('Y-m-d', strtotime($checkout));

	if (!$checkin_date || !$checkout_date || $checkout_date <= $checkin_date) {
		wp_send_json_error(['message' => 'Invalid date range.']);
	}

	$room_post = function_exists('cwc_find_accommodation_post_by_room_name')
		? cwc_find_accommodation_post_by_room_name($room_name)
		: null;
	$room_post_id = $room_post instanceof WP_Post ? (int) $room_post->ID : 0;
	$total_units = $room_post_id ? cwc_get_room_inventory($room_post_id) : 1;
	$overlapping = cwc_count_overlapping_bookings($room_name, $checkin_date, $checkout_date);
	$available_units = $overlapping > 0 ? 0 : $total_units;

	if ($room_post_id && function_exists('cwc_get_room_unit_allocation')) {
		$allocation = cwc_get_room_unit_allocation($room_post_id, $checkin_date, $checkout_date);
		$total_units = (int) ($allocation['total_units'] ?? $total_units);
		$overlapping = (int) ($allocation['occupied_count'] ?? 0) + count($allocation['overflow_booking_ids'] ?? []);
		$available_units = $overlapping > 0 ? 0 : $total_units;
	}

	$is_fully_booked = ($overlapping > 0);

	wp_send_json_success([
		'room' => $room_name,
		'checkin' => $checkin_date,
		'checkout' => $checkout_date,
		'total_units' => $total_units,
		'booked_units' => $overlapping,
		'available_units' => $available_units,
		'fully_booked' => $is_fully_booked,
	]);
}
add_action('wp_ajax_cwc_check_room_availability', 'cwc_check_room_availability');
add_action('wp_ajax_nopriv_cwc_check_room_availability', 'cwc_check_room_availability');

/**
 * Count overlapping active bookings for a room within a date range.
 *
 * @param string $room_name    Room title to match.
 * @param string $checkin_date  Y-m-d format.
 * @param string $checkout_date Y-m-d format.
 * @param int    $exclude_id    Optional booking ID to exclude (for edits).
 * @return int Number of overlapping bookings.
 */
function cwc_count_overlapping_bookings($room_name, $checkin_date, $checkout_date, $exclude_id = 0)
{
	$bookings = get_posts([
		'post_type' => 'cwc_booking',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'fields' => 'ids',
	]);

	$room_post = function_exists('cwc_find_accommodation_post_by_room_name')
		? cwc_find_accommodation_post_by_room_name($room_name)
		: null;
	$room_post_id = $room_post instanceof WP_Post ? (int) $room_post->ID : 0;
	$room_name_clean = strtolower(preg_replace('/\s+Room$/i', '', trim($room_name)));

	$count = 0;
	foreach ($bookings as $booking_id) {
		if ($exclude_id && $booking_id === $exclude_id) {
			continue;
		}

		$bk_status = get_post_meta($booking_id, '_cwc_bk_status', true);
		if (!cwc_booking_status_is_active($bk_status)) {
			continue;
		}

		if ($room_post_id > 0 && function_exists('cwc_get_booking_room_post_id')) {
			if (cwc_get_booking_room_post_id($booking_id) !== $room_post_id) {
				continue;
			}
		} else {
			$bk_room = get_post_meta($booking_id, '_cwc_bk_room', true);
			$bk_room_clean = strtolower(preg_replace('/\s+Room$/i', '', trim($bk_room)));
			if ($bk_room_clean !== $room_name_clean) {
				continue;
			}
		}

		$bk_checkin = get_post_meta($booking_id, '_cwc_bk_checkin', true);
		$bk_checkout = get_post_meta($booking_id, '_cwc_bk_checkout', true);

		if (empty($bk_checkin) || empty($bk_checkout)) {
			continue;
		}

		$bk_ci = date('Y-m-d', strtotime($bk_checkin));
		$bk_co = date('Y-m-d', strtotime($bk_checkout));

		// Overlap check: booking overlaps if it starts before our checkout
		// AND ends after our checkin
		if ($bk_ci < $checkout_date && $bk_co > $checkin_date) {
			$count++;
		}
	}

	return $count;
}

/**
 * AJAX: Get all unavailable dates for a specific room.
 *
 * Returns an array of Y-m-d strings where the room already has an existing
 * reservation and should be disabled in the public booking calendars.
 */
function cwc_get_booked_dates()
{
	$room_name = isset($_POST['room']) ? sanitize_text_field(wp_unslash($_POST['room'])) : '';
	if (empty($room_name) || 'Choose Room' === $room_name) {
		wp_send_json_error(['message' => 'Please select a room first.']);
	}

	$room_post = function_exists('cwc_find_accommodation_post_by_room_name')
		? cwc_find_accommodation_post_by_room_name($room_name)
		: null;
	$room_post_id = $room_post instanceof WP_Post ? (int) $room_post->ID : 0;

	if (!$room_post_id) {
		wp_send_json_success([]);
	}

	$fully_booked_dates = [];
	$today = new DateTimeImmutable(current_time('Y-m-d'));
	for ($offset = 0; $offset < 365; $offset++) {
		$date = $today->modify('+' . $offset . ' days');
		$next_date = $date->modify('+1 day');
		$overlapping = cwc_count_overlapping_bookings($room_name, $date->format('Y-m-d'), $next_date->format('Y-m-d'));
		if ($overlapping > 0) {
			$date_str = $date->format('Y-m-d');
			$fully_booked_dates[] = $date_str;
		}
	}

	wp_send_json_success($fully_booked_dates);
}
add_action('wp_ajax_cwc_get_booked_dates', 'cwc_get_booked_dates');
add_action('wp_ajax_nopriv_cwc_get_booked_dates', 'cwc_get_booked_dates');

/* ────────────────────────────────────────────
   Status Email Templates
   ──────────────────────────────────────────── */

/**
 * Send a status-specific email to the guest.
 *
 * @param int    $booking_id  The booking post ID.
 * @param string $status      The new booking status.
 * @param string $admin_note  Optional note from the admin.
 * @return bool Whether the email was sent.
 */
function cwc_send_booking_status_email($booking_id, $status, $admin_note = '')
{
	$email = get_post_meta($booking_id, '_cwc_bk_email', true);
	$name = get_post_meta($booking_id, '_cwc_bk_name', true);
	$room = get_post_meta($booking_id, '_cwc_bk_room', true);
	$assigned_room = get_post_meta($booking_id, '_cwc_bk_assigned_room', true);
	$checkin = get_post_meta($booking_id, '_cwc_bk_checkin', true);
	$checkout = get_post_meta($booking_id, '_cwc_bk_checkout', true);
	$price = get_post_meta($booking_id, '_cwc_bk_price', true);
	$ref = get_post_meta($booking_id, '_cwc_bk_ref', true);
	$nights = (int) get_post_meta($booking_id, '_cwc_bk_nights', true);
	$pay_status = get_post_meta($booking_id, '_cwc_bk_payment_status', true) ?: 'unpaid';
	$pay_method = get_post_meta($booking_id, '_cwc_bk_payment', true);

	if (!$nights && $checkin && $checkout) {
		$ci_ts = strtotime($checkin);
		$co_ts = strtotime($checkout);
		if ($ci_ts && $co_ts && $co_ts > $ci_ts) {
			$nights = (int) (($co_ts - $ci_ts) / DAY_IN_SECONDS);
		}
	}

	if (!$email) {
		return false;
	}

	// Status-specific content
	$templates = [
		'pending' => [
			'subject' => 'Booking Received — CWC Wake Park',
			'heading' => 'Booking Received',
			'message' => 'Thank you for your reservation! Your booking is currently being reviewed by our team. We will notify you once it has been confirmed.',
		],
		'confirmed' => [
			'subject' => 'Booking Confirmed! — CWC Wake Park',
			'heading' => 'Your Booking is Confirmed!',
			'message' => 'Great news! Your booking has been confirmed. We look forward to welcoming you at CWC Wake Park!',
		],
		'cancelled' => [
			'subject' => 'Booking Cancelled — CWC Wake Park',
			'heading' => 'Booking Cancellation Notice',
			'message' => 'We regret to inform you that your booking has been cancelled. If you have any questions or believe this is an error, please don\'t hesitate to contact us.',
		],
		'completed' => [
			'subject' => 'Thank You for Staying! — CWC Wake Park',
			'heading' => 'Thank You for Your Visit!',
			'message' => 'We hope you had an amazing time at CWC Wake Park! We would love to see you again soon.',
		],
	];

	if (!isset($templates[$status])) {
		return false;
	}

	$tpl = $templates[$status];

	// Build the email body
	ob_start();
	?>
	<p>Hi <strong><?php echo esc_html($name); ?></strong>,</p>
	<p><?php echo esc_html($tpl['message']); ?></p>

	<table class="details-table">
		<?php if ($ref): ?>
			<tr>
				<td class="details-label">Booking Reference</td>
				<td class="details-value details-highlight"><?php echo esc_html($ref); ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td class="details-label">Room</td>
			<td class="details-value"><?php echo esc_html($room); ?></td>
		</tr>
		<?php if ($assigned_room): ?>
			<tr>
				<td class="details-label">Room Number</td>
				<td class="details-value details-highlight"><?php echo esc_html($assigned_room); ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td class="details-label">Check-in</td>
			<td class="details-value"><?php echo esc_html($checkin); ?></td>
		</tr>
		<tr>
			<td class="details-label">Check-out</td>
			<td class="details-value"><?php echo esc_html($checkout); ?></td>
		</tr>
		<?php if ($nights > 0): ?>
			<tr>
				<td class="details-label">Duration</td>
				<td class="details-value"><?php echo esc_html($nights); ?> night<?php echo $nights > 1 ? 's' : ''; ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td class="details-label">Amount</td>
			<td class="details-value details-highlight"><?php echo esc_html($price); ?></td>
		</tr>
		<tr>
			<td class="details-label">Payment</td>
			<td class="details-value" style="text-transform: capitalize;"><?php echo esc_html($pay_status); ?>
				(<?php echo esc_html(strtoupper($pay_method)); ?>)</td>
		</tr>
		<tr>
			<td class="details-label">Booking Status</td>
			<td class="details-value details-highlight" style="text-transform: capitalize;">
				<?php echo esc_html($status); ?>
			</td>
		</tr>
	</table>

	<?php if (!empty($admin_note)): ?>
		<div class="admin-note">
			<p class="note-title">Note from CWC Team:</p>
			<p><?php echo nl2br(esc_html($admin_note)); ?></p>
		</div>
	<?php endif; ?>

	<p style="margin-top: 24px; color: #64748b; font-size: 14px;">If you have any questions, feel free to reply to this
		email or contact us at <a href="mailto:info@cwcwake.com" style="color: #0096C7;">info@cwcwake.com</a>.</p>
	<?php
	$body = ob_get_clean();

	// Wrap in premium template
	if (function_exists('cwc_get_email_template')) {
		$full_html = cwc_get_email_template($tpl['heading'], $body);
	} else {
		$full_html = $body;
	}

	$headers = ['Content-Type: text/html; charset=UTF-8'];
	$sent = wp_mail($email, $tpl['subject'], $full_html, $headers);

	// Log the email
	cwc_log_email($booking_id, 'status_' . $status, $email, $sent, $admin_note);

	return $sent;
}

/* ────────────────────────────────────────────
   Automated Checkout Processing
   ──────────────────────────────────────────── */

if (!wp_next_scheduled('cwc_daily_checkout_processor')) {
	wp_schedule_event(time(), 'daily', 'cwc_daily_checkout_processor');
}

add_action('cwc_daily_checkout_processor', 'cwc_process_past_checkouts');

/**
 * Automatically set bookings to 'completed' and release physical rooms
 * if the check-out date has passed.
 */
function cwc_process_past_checkouts()
{
	$today = date('Y-m-d');

	// Find bookings that have checked out
	$bookings = get_posts([
		'post_type' => 'cwc_booking',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'meta_query' => [
			'relation' => 'AND',
			[
				'key' => '_cwc_bk_status',
				'value' => ['pending', 'confirmed'],
				'compare' => 'IN',
			],
			[
				'key' => '_cwc_bk_checkout',
				'value' => $today,
				'compare' => '<',
				'type' => 'DATE'
			]
		]
	]);

	foreach ($bookings as $booking) {
		$booking_id = $booking->ID;
		$old_status = get_post_meta($booking_id, '_cwc_bk_status', true);
		$new_status = 'completed';

		update_post_meta($booking_id, '_cwc_bk_status', $new_status);
		cwc_add_audit_log($booking_id, 'status_changed', [
			'from' => $old_status,
			'to' => $new_status,
			'note' => 'Auto-completed via daily checkout processor',
			'email_sent' => false,
		]);

		cwc_release_legacy_booked_unit_for_booking($booking_id);
	}
}
