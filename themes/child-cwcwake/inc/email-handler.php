<?php
/**
 * Reusable email templates and handlers.
 *
 * @package ChildCwcwake
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Wraps content in a reusable email design.
 *
 * @param string $title   The email title/heading.
 * @param string $content The main HTML content of the email.
 * @return string The full HTML email payload.
 */
function cwc_get_email_template($title, $content)
{
	$logo_url = home_url('/wp-content/themes/child-cwcwake/assets/images/logo.svg'); // Try SVG first if possible, but fallback to text if broken

	ob_start();
	?>
	<!DOCTYPE html>
	<html>

	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php echo esc_html($title); ?></title>
		<style>
			body {
				font-family: 'Inter', 'Roboto', 'Helvetica Neue', Helvetica, Arial, sans-serif;
				background-color: #F8FAFC;
				margin: 0;
				padding: 0;
				color: #334155;
			}

			.email-wrapper {
				width: 100%;
				background-color: #F8FAFC;
				padding: 40px 20px;
			}

			.email-container {
				max-width: 640px;
				margin: 0 auto;
				background-color: #ffffff;
				border-radius: 12px;
				overflow: hidden;
				box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
			}

			.email-header {
				background-color: #0096C7;
				padding: 40px 30px;
				text-align: center;
			}

			.email-header img {
				max-height: 48px;
				width: auto;
				display: block;
				margin: 0 auto;
			}

			.email-header h1 {
				color: #ffffff;
				margin: 0;
				font-size: 24px;
				letter-spacing: 3px;
				font-weight: 800;
				text-transform: uppercase;
			}

			.email-body {
				padding: 40px 40px 30px 40px;
			}

			.email-title {
				font-size: 22px;
				font-weight: 800;
				margin-top: 0;
				margin-bottom: 24px;
				color: #0F172A;
			}

			.email-content {
				font-size: 15px;
				line-height: 1.6;
				color: #475569;
			}

			.details-table {
				width: 100%;
				border-collapse: separate;
				border-spacing: 0;
				margin: 32px 0;
				border-radius: 8px;
				overflow: hidden;
				border: 1px solid #E2E8F0;
			}

			.details-table th,
			.details-table td {
				padding: 16px 24px;
				border-bottom: 1px solid #E2E8F0;
				text-align: left;
			}

			.details-table tr:last-child td {
				border-bottom: none;
			}

			.details-table tr:nth-child(even) {
				background-color: #F8FAFC;
			}

			.details-label {
				font-weight: 600;
				color: #64748B;
				width: 40%;
				font-size: 13px;
				text-transform: uppercase;
				letter-spacing: 0.5px;
			}

			.details-value {
				font-weight: 600;
				color: #0F172A;
				font-size: 15px;
			}

			.details-highlight {
				color: #0096C7;
				font-weight: 800;
			}

			.admin-note {
				background-color: #E5F4FA;
				border-left: 4px solid #0096C7;
				padding: 20px 24px;
				margin: 32px 0;
				border-radius: 0 8px 8px 0;
			}

			.admin-note p {
				margin: 0;
				color: #334155;
				line-height: 1.6;
			}

			.admin-note .note-title {
				font-weight: 800;
				color: #0F172A;
				margin-bottom: 8px;
				font-size: 13px;
				text-transform: uppercase;
				letter-spacing: 0.5px;
			}

			.email-footer {
				background-color: #F1F5F9;
				padding: 32px 40px;
				text-align: center;
				font-size: 14px;
				color: #64748B;
			}

			.email-footer p {
				margin: 8px 0;
			}

			.email-footer a {
				color: #0096C7;
				text-decoration: none;
				font-weight: 600;
			}

			.email-footer a:hover {
				text-decoration: underline;
			}
		</style>
	</head>

	<body>
		<div class="email-wrapper">
			<div class="email-container">
				<div class="email-header">
					<h1>CWC WAKE PARK</h1>
				</div>
				<div class="email-body">
					<h2 class="email-title"><?php echo esc_html($title); ?></h2>
					<div class="email-content">
						<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				</div>
				<div class="email-footer">
					<p>&copy; <?php echo date('Y'); ?> CWC Wake Park. All rights reserved.</p>
					<p><a href="<?php echo esc_url(home_url()); ?>">Visit our website</a> &nbsp;&bull;&nbsp; <a
							href="mailto:info@cwcwake.com">Contact Support</a></p>
				</div>
			</div>
		</div>
	</body>

	</html>
	<?php
	return ob_get_clean();
}

/**
 * Handle booking submission via AJAX.
 */
function cwc_submit_booking()
{
	$name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
	$email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
	$phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
	$checkin = isset($_POST['checkin']) ? sanitize_text_field(wp_unslash($_POST['checkin'])) : '';
	$checkout = isset($_POST['checkout']) ? sanitize_text_field(wp_unslash($_POST['checkout'])) : '';
	$room = isset($_POST['room']) ? sanitize_text_field(wp_unslash($_POST['room'])) : '';
	$price = isset($_POST['price']) ? sanitize_text_field(wp_unslash($_POST['price'])) : '';
	$payment_method = isset($_POST['payment_method']) ? sanitize_text_field(wp_unslash($_POST['payment_method'])) : '';
	$guests = isset($_POST['guests']) ? json_decode(wp_unslash($_POST['guests']), true) : [];
	$nights = isset($_POST['nights']) ? absint($_POST['nights']) : 0;

	if (empty($name) || empty($email)) {
		wp_send_json_error(['message' => 'Name and email are required.']);
	}

	if (empty($checkin) || empty($checkout)) {
		wp_send_json_error(['message' => 'Check-in and check-out dates are required.']);
	}

	if (empty($room)) {
		wp_send_json_error(['message' => 'Room selection is required.']);
	}

	// Validate date range
	$checkin_date = date('Y-m-d', strtotime($checkin));
	$checkout_date = date('Y-m-d', strtotime($checkout));

	if ($checkout_date <= $checkin_date) {
		wp_send_json_error(['message' => 'Check-out date must be after check-in date.']);
	}

	// Calculate nights if not provided
	if (!$nights) {
		$nights = (int) ((strtotime($checkout_date) - strtotime($checkin_date)) / DAY_IN_SECONDS);
	}

	// Check room availability for the selected dates
	if (function_exists('cwc_count_overlapping_bookings') && function_exists('cwc_get_room_inventory')) {
		$room_clean = preg_replace('/\s+Room$/i', '', trim($room));
		$room_posts = get_posts([
			'post_type' => 'accommodation',
			'post_status' => 'publish',
			'posts_per_page' => -1,
		]);
		$room_post_id = 0;
		foreach ($room_posts as $rp) {
			if (strtolower(trim($rp->post_title)) === strtolower($room_clean)) {
				$room_post_id = $rp->ID;
				break;
			}
		}

		if ($room_post_id) {
			$total_units = cwc_get_room_inventory($room_post_id);
			$overlapping = cwc_count_overlapping_bookings($room, $checkin_date, $checkout_date);
			$available_units = max(0, $total_units - $overlapping);

			if ($available_units <= 0) {
				wp_send_json_error([
					'message' => 'Sorry, this room type is fully booked for your selected dates. Please choose different dates or another room.',
					'fully_booked' => true,
				]);
			}
		}
	}

	/* ── Save booking record ── */
	$booking_id = wp_insert_post([
		'post_type' => 'cwc_booking',
		'post_title' => $name . ' — ' . $room,
		'post_status' => 'publish',
		'post_date' => current_time('mysql'),
	]);

	if ($booking_id && !is_wp_error($booking_id)) {
		update_post_meta($booking_id, '_cwc_bk_name', $name);
		update_post_meta($booking_id, '_cwc_bk_email', $email);
		update_post_meta($booking_id, '_cwc_bk_phone', $phone);
		update_post_meta($booking_id, '_cwc_bk_checkin', $checkin);
		update_post_meta($booking_id, '_cwc_bk_checkout', $checkout);
		update_post_meta($booking_id, '_cwc_bk_room', $room);
		update_post_meta($booking_id, '_cwc_bk_price', $price);
		update_post_meta($booking_id, '_cwc_bk_payment', $payment_method);
		update_post_meta($booking_id, '_cwc_bk_status', 'pending');
		update_post_meta($booking_id, '_cwc_bk_guests', wp_json_encode($guests));
		update_post_meta($booking_id, '_cwc_bk_nights', $nights);
		$price_num = (float) preg_replace('/[^0-9.]/', '', $price);
		update_post_meta($booking_id, '_cwc_bk_price_num', $price_num);

		/* ── PayMongo Integration ── */
		$paymongo_methods = ['paymaya'];
		if (in_array($payment_method, $paymongo_methods) && defined('PAYMONGO_SECRET_KEY')) {
			$amount = (int) ($price_num * 100); // Centavos
			$current_url = isset($_POST['current_url']) ? esc_url_raw(wp_unslash($_POST['current_url'])) : home_url('/');
			$ref = get_post_meta($booking_id, '_cwc_bk_ref', true);
			$success_url = add_query_arg(['booking_success' => '1', 'ref' => $ref], $current_url);

			$payload = [
				'data' => [
					'attributes' => [
						'line_items' => [
							[
								'amount'      => $amount,
								'currency'    => 'PHP',
								'description' => 'Booking for ' . $room . ' (' . $nights . ' nights)',
								'name'        => $room,
								'quantity'    => 1,
							]
						],
						'payment_method_types' => ['card', 'gcash', 'paymaya', 'grab_pay'],
						'success_url'          => $success_url,
						'cancel_url'           => $current_url,
						'description'          => 'CWC Wake Park Booking - ' . $ref,
					]
				]
			];

			$response = wp_remote_post('https://api.paymongo.com/v1/checkout_sessions', [
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':'),
					'Content-Type'  => 'application/json',
				],
				'body' => wp_json_encode($payload),
			]);

			if (!is_wp_error($response)) {
				$body = json_decode(wp_remote_retrieve_body($response), true);
				if (isset($body['data']['attributes']['checkout_url'])) {
					$checkout_url = $body['data']['attributes']['checkout_url'];
					if (isset($body['data']['id'])) {
						update_post_meta($booking_id, '_cwc_paymongo_session_id', $body['data']['id']);
					}
					wp_send_json_success([
						'message'      => 'Redirecting to payment gateway...',
						'redirect_url' => $checkout_url
					]);
				}
			}
			error_log('PayMongo Error: ' . print_r($response, true));
		}

		// Automatically mark an available physical room as booked
		if (isset($room_post_id) && $room_post_id && function_exists('cwc_get_physical_rooms')) {
			$physical_rooms = cwc_get_physical_rooms($room_post_id);
			$updated = false;
			foreach ($physical_rooms as &$p_room) {
				if (($p_room['status'] ?? 'available') === 'available') {
					$p_room['status'] = 'booked';
					$updated = true;
					update_post_meta($booking_id, '_cwc_bk_assigned_room', $p_room['name'] ?? '');
					break;
				}
			}
			if ($updated) {
				update_post_meta($room_post_id, '_cwc_physical_rooms', wp_json_encode($physical_rooms));
			}
		}
	}

	// Retrieve auto-generated reference from the insert hook
	$ref = get_post_meta($booking_id, '_cwc_bk_ref', true);
	$assigned_room = get_post_meta($booking_id, '_cwc_bk_assigned_room', true);

	// Build the email content
	ob_start();
	?>
	<p>Hi <strong><?php echo esc_html($name); ?></strong>,</p>
	<p>Thank you for choosing CWC Wake Park! We have received your booking request. Here are the details of your
		reservation:</p>

	<table class="details-table">
		<?php if ($ref): ?>
			<tr>
				<td class="details-label">Booking Reference</td>
				<td class="details-value details-highlight"><?php echo esc_html($ref); ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td class="details-label">Room Type</td>
			<td class="details-value"><?php echo esc_html($room); ?></td>
		</tr>
		<?php if ($assigned_room): ?>
			<tr>
				<td class="details-label">Room Number</td>
				<td class="details-value details-highlight"><?php echo esc_html($assigned_room); ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td class="details-label">Check-in Date</td>
			<td class="details-value"><?php echo esc_html($checkin); ?></td>
		</tr>
		<tr>
			<td class="details-label">Check-out Date</td>
			<td class="details-value"><?php echo esc_html($checkout); ?></td>
		</tr>
		<tr>
			<td class="details-label">Duration</td>
			<td class="details-value"><?php echo esc_html($nights); ?> night<?php echo $nights > 1 ? 's' : ''; ?></td>
		</tr>
		<tr>
			<td class="details-label">Phone Number</td>
			<td class="details-value"><?php echo esc_html($phone); ?></td>
		</tr>
		<tr>
			<td class="details-label">Payment Method</td>
			<td class="details-value" style="text-transform: uppercase;"><?php echo esc_html($payment_method); ?></td>
		</tr>
		<tr>
			<td class="details-label">Total Price</td>
			<td class="details-value details-highlight"><?php echo esc_html($price); ?></td>
		</tr>
	</table>

	<?php if (!empty($guests) && is_array($guests)): ?>
		<h3 class="email-title" style="font-size: 18px; margin-top: 32px; margin-bottom: 16px;">Additional Guests</h3>
		<ul style="padding-left: 20px; color: #475569; line-height: 1.6;">
			<?php foreach ($guests as $guest): ?>
				<li><strong><?php echo esc_html($guest['name']); ?></strong>
					(<?php echo esc_html(ucfirst($guest['type'])); ?>)</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<p style="margin-top: 32px;">If you have any questions or need to make changes to your reservation, please don't
		hesitate to reply to this email.</p>
	<p>We look forward to hosting you!</p>
	<p>Best regards,<br><strong>The CWC Wake Park Team</strong></p>
	<?php
	$email_content = ob_get_clean();

	$full_html = cwc_get_email_template('Your Booking Confirmation', $email_content);

	$headers = array('Content-Type: text/html; charset=UTF-8');

	$sent = wp_mail($email, 'Your Booking Confirmation - CWC Wake Park', $full_html, $headers);

	if ($sent) {
		wp_send_json_success(['message' => 'Booking received and email sent.']);
	} else {
		wp_send_json_error(['message' => 'Booking processed but email failed to send.']);
	}
}
add_action('wp_ajax_cwc_submit_booking', 'cwc_submit_booking');
add_action('wp_ajax_nopriv_cwc_submit_booking', 'cwc_submit_booking');

/**
 * Handle inquiry submission via AJAX from the Rates Manager.
 */
function cwc_submit_inquiry()
{
	$email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
	$subject = isset($_POST['subject']) ? sanitize_text_field(wp_unslash($_POST['subject'])) : '';
	$message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';

	if (empty($email) || empty($subject) || empty($message)) {
		wp_send_json_error(['message' => 'All fields are required.']);
	}

	if (!is_email($email)) {
		wp_send_json_error(['message' => 'Please provide a valid email address.']);
	}

	$recipient = get_option('admin_email');

	// Build the email content
	ob_start();
	?>
	<p>You have received a new inquiry from the <strong>Rates & Park Hours</strong> page.</p>

	<table class="details-table">
		<tr>
			<td class="details-label" style="width: 30%;">From</td>
			<td class="details-value"><?php echo esc_html($email); ?></td>
		</tr>
		<tr>
			<td class="details-label" style="width: 30%;">Subject</td>
			<td class="details-value"><?php echo esc_html($subject); ?></td>
		</tr>
		<tr>
			<td class="details-label" style="width: 30%; vertical-align: top;">Message</td>
			<td class="details-value" style="font-weight: 400; line-height: 1.6;">
				<?php echo nl2br(esc_html($message)); ?></td>
		</tr>
	</table>

	<p style="font-size: 14px; color: #71717a; margin-top: 24px;">
		<em>Note: You can reply directly to this email to contact the visitor.</em>
	</p>
	<?php
	$email_content = ob_get_clean();

	$full_html = cwc_get_email_template('New Inquiry: ' . $subject, $email_content);

	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'Reply-To: ' . $email
	);

	$sent = wp_mail($recipient, 'CWC Inquiry: ' . $subject, $full_html, $headers);

	if ($sent) {
		wp_send_json_success(['message' => 'Thank you! Your inquiry has been sent.']);
	} else {
		wp_send_json_error(['message' => 'Sorry, there was an error sending your email. Please try again later.']);
	}
}
add_action('wp_ajax_cwc_submit_inquiry', 'cwc_submit_inquiry');
add_action('wp_ajax_nopriv_cwc_submit_inquiry', 'cwc_submit_inquiry');
