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
function cwc_get_email_template($title, $content, $args = [])
{
	$logo_url = home_url('/wp-content/themes/child-cwcwake/assets/images/logo.png'); // Assuming a logo exists, or fallback to text
	$ref_code = $args['ref'] ?? '';
	$banner_title = $args['banner_title'] ?? "You're all set!";
	$banner_subtitle = $args['banner_subtitle'] ?? "Your booking has been received and is being processed.";

	ob_start();
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title><?php echo esc_html($title); ?></title>
		<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Archivo:wght@400;500;600&display=swap" rel="stylesheet" />
		<style>
			* { margin: 0; padding: 0; box-sizing: border-box; }
			body {
				background-color: #EBEBE6;
				font-family: 'Archivo', sans-serif;
				color: #1A1A1A;
				-webkit-font-smoothing: antialiased;
				margin: 0;
				padding: 0;
			}

			.email-wrapper {
				max-width: 600px;
				margin: 40px auto;
				background: #F5F1E8;
				box-shadow: 0 20px 50px rgba(0,0,0,0.05);
			}

			/* Header */
			.header {
				background: #1A1A1A;
				padding: 24px 36px;
				display: flex;
				align-items: center;
				justify-content: space-between;
			}
			.logo {
				font-family: 'Sora', sans-serif;
				font-size: 14px;
				font-weight: 700;
				letter-spacing: 0.2em;
				color: #F5F1E8;
				text-transform: uppercase;
				border: 1.5px solid rgba(255,255,255,0.3);
				padding: 5px 10px;
				text-decoration: none;
				display: inline-block;
			}
			.header-ref {
				font-family: 'Archivo', sans-serif;
				font-size: 11px;
				letter-spacing: 0.1em;
				color: rgba(255,255,255,0.35);
				text-transform: uppercase;
			}
			.header-ref span {
				color: #0096C7;
				font-weight: 600;
			}

			/* Confirmation Banner */
			.confirm-banner {
				background: #0096C7;
				padding: 36px 36px 32px;
				display: flex;
				align-items: flex-start;
				gap: 20px;
			}
			.confirm-icon {
				width: 48px;
				height: 48px;
				background: rgba(255,255,255,0.15);
				border-radius: 50%;
				display: flex;
				align-items: center;
				justify-content: center;
				flex-shrink: 0;
				font-size: 22px;
				color: #fff;
			}
			.confirm-text {}
			.confirm-label {
				font-family: 'Archivo', sans-serif;
				font-size: 11px;
				letter-spacing: 0.18em;
				text-transform: uppercase;
				color: rgba(255,255,255,0.7);
				margin-bottom: 6px;
			}
			.confirm-title {
				font-family: 'Sora', sans-serif;
				font-size: 24px;
				font-weight: 700;
				color: #FFFFFF;
				line-height: 1.2;
			}
			.confirm-subtitle {
				font-family: 'Archivo', sans-serif;
				font-size: 14px;
				color: rgba(255,255,255,0.75);
				margin-top: 6px;
				line-height: 1.5;
			}

			/* Content Body */
			.email-body {
				padding: 36px;
			}
			.email-body p {
				font-family: 'Archivo', sans-serif;
				font-size: 15px;
				line-height: 1.7;
				color: #444;
				margin-bottom: 20px;
			}
			.email-body p strong {
				color: #1A1A1A;
				font-weight: 600;
			}

			/* Trip Summary Card (Used in content) */
			.trip-card {
				margin: 20px 0;
				border: 1.5px solid rgba(0,0,0,0.1);
				background: #fff;
			}
			.trip-card-header {
				background: #1A1A1A;
				padding: 14px 22px;
				display: flex;
				align-items: center;
				justify-content: space-between;
			}
			.trip-card-header-label {
				font-family: 'Sora', sans-serif;
				font-size: 12px;
				font-weight: 600;
				letter-spacing: 0.12em;
				text-transform: uppercase;
				color: rgba(255,255,255,0.6);
			}
			.trip-card-header-status {
				font-family: 'Archivo', sans-serif;
				font-size: 11px;
				font-weight: 600;
				letter-spacing: 0.1em;
				text-transform: uppercase;
				background: #0096C7;
				color: #fff;
				padding: 3px 10px;
			}

			/* Details Tables */
			.details-title {
				font-family: 'Sora', sans-serif;
				font-size: 13px;
				font-weight: 700;
				letter-spacing: 0.1em;
				text-transform: uppercase;
				color: #1A1A1A;
				margin-top: 32px;
				margin-bottom: 12px;
			}
			.details-table {
				width: 100%;
				border-collapse: collapse;
				margin-bottom: 24px;
			}
			.details-table tr {
				border-bottom: 1px solid rgba(0,0,0,0.06);
			}
			.details-table tr:last-child { border-bottom: none; }
			.details-table td {
				padding: 12px 0;
				font-family: 'Archivo', sans-serif;
				font-size: 14px;
				vertical-align: top;
			}
			.details-table td:first-child {
				color: #888;
				width: 40%;
				font-size: 13px;
			}
			.details-table td:last-child {
				color: #1A1A1A;
				font-weight: 500;
				text-align: right;
			}
			.details-highlight {
				color: #0096C7 !important;
				font-weight: 700 !important;
			}

			/* Admin Note / Payment Note */
			.admin-note, .payment-note {
				background: rgba(0,150,199,0.07);
				border-left: 3px solid #0096C7;
				padding: 14px 16px;
				margin: 24px 0;
				font-family: 'Archivo', sans-serif;
				font-size: 13px;
				color: #555;
				line-height: 1.6;
			}
			.admin-note strong, .payment-note strong { color: #1A1A1A; }

			/* Divider */
			.divider {
				margin: 28px 0;
				border: none;
				border-top: 1px solid rgba(0,0,0,0.08);
			}

			/* Footer */
			.footer {
				background: #1A1A1A;
				padding: 28px 36px;
			}
			.footer-top {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 20px;
			}
			.footer-logo {
				font-family: 'Sora', sans-serif;
				font-size: 11px;
				font-weight: 700;
				letter-spacing: 0.2em;
				color: #F5F1E8;
				text-transform: uppercase;
				border: 1.5px solid rgba(255,255,255,0.2);
				padding: 4px 9px;
				text-decoration: none;
				display: inline-block;
			}
			.footer-support {
				font-family: 'Archivo', sans-serif;
				font-size: 12px;
				color: rgba(255,255,255,0.35);
				text-align: right;
			}
			.footer-support a {
				color: #0096C7;
				text-decoration: none;
			}
			.footer-divider {
				border: none;
				border-top: 1px solid rgba(255,255,255,0.07);
				margin-bottom: 16px;
			}
			.footer-legal {
				font-family: 'Archivo', sans-serif;
				font-size: 11px;
				color: rgba(255,255,255,0.2);
				line-height: 1.7;
			}
			.footer-legal a { color: rgba(255,255,255,0.3); text-decoration: underline; }

			@media (max-width: 520px) {
				.email-wrapper { margin: 0; width: 100%; }
				.header, .confirm-banner, .email-body, .footer {
					padding-left: 20px; padding-right: 20px;
				}
				.confirm-title { font-size: 20px; }
				.footer-top { flex-direction: column; align-items: flex-start; gap: 16px; }
				.footer-support { text-align: left; }
			}
		</style>
	</head>
	<body>
		<div class="email-wrapper">
			<!-- Header -->
			<div class="header">
				<a href="<?php echo esc_url(home_url()); ?>" class="logo">CWC WAKE PARK</a>
				<?php if ($ref_code): ?>
					<div class="header-ref">Ref: <span>#<?php echo esc_html($ref_code); ?></span></div>
				<?php endif; ?>
			</div>

			<!-- Confirmation Banner -->
			<div class="confirm-banner">
				<div class="confirm-icon">✓</div>
				<div class="confirm-text">
					<div class="confirm-label"><?php echo esc_html($title); ?></div>
					<div class="confirm-title"><?php echo esc_html($banner_title); ?></div>
					<div class="confirm-subtitle"><?php echo $banner_subtitle; ?></div>
				</div>
			</div>

			<!-- Main Body -->
			<div class="email-body">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

			<!-- Footer -->
			<div class="footer">
				<div class="footer-top">
					<a href="<?php echo esc_url(home_url()); ?>" class="footer-logo">CWC</a>
					<div class="footer-support">
						Need help?<br/>
						<a href="mailto:info@cwcwake.com">info@cwcwake.com</a> · +63 2 8888 0000
					</div>
				</div>
				<hr class="footer-divider" />
				<div class="footer-legal">
					&copy; <?php echo date('Y'); ?> CWC World Travel Co. · All rights reserved.<br/>
					This is a transactional email regarding your inquiry or booking.<br/>
					<a href="<?php echo esc_url(home_url('/privacy-policy')); ?>">Privacy Policy</a> · <a href="<?php echo esc_url(home_url('/terms-and-conditions')); ?>">Terms & Conditions</a>
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
		$room_post_id = 0;
		if (function_exists('cwc_find_accommodation_post_by_room_name')) {
			$room_post = cwc_find_accommodation_post_by_room_name($room);
			$room_post_id = $room_post instanceof WP_Post ? (int) $room_post->ID : 0;
		}

		if ($room_post_id) {
			$overlapping = cwc_count_overlapping_bookings($room, $checkin_date, $checkout_date);
			if ($overlapping > 0) {
				wp_send_json_error([
					'message' => 'Sorry, this room is already reserved for your selected dates. Please choose different dates.',
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

		if (!empty($room_post_id) && function_exists('cwc_sync_booking_room_link')) {
			cwc_sync_booking_room_link($booking_id, $room_post_id);
		}

		if (!empty($room_post_id) && function_exists('cwc_assign_available_unit_to_booking')) {
			cwc_assign_available_unit_to_booking($booking_id, $room_post_id, $checkin_date, $checkout_date);
		}

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

	}

	// Retrieve auto-generated reference from the insert hook
	$ref = get_post_meta($booking_id, '_cwc_bk_ref', true);
	$assigned_room = get_post_meta($booking_id, '_cwc_bk_assigned_room', true);

	// Build the email content
	ob_start();
	?>
	<p>Hi <strong><?php echo esc_html($name); ?></strong>,</p>
	<p>Thank you for choosing CWC Wake Park! Your booking has been confirmed and we've reserved your space. Please review your itinerary details below.</p>

	<!-- Stay Summary Card -->
	<div class="trip-card">
		<div class="trip-card-header">
			<span class="trip-card-header-label">Stay Summary</span>
			<span class="trip-card-header-status">Confirmed</span>
		</div>
		<div style="padding: 24px 22px;">
			<div style="display: flex; justify-content: space-between; align-items: flex-end;">
				<div>
					<div style="font-family: 'Sora', sans-serif; font-size: 24px; font-weight: 800; color: #1A1A1A; line-height: 1;"><?php echo esc_html($room); ?></div>
					<div style="font-family: 'Archivo', sans-serif; font-size: 12px; color: #888; margin-top: 4px;">CWC Wake Park · Camarines Sur</div>
				</div>
				<?php if ($assigned_room): ?>
				<div style="text-align: right;">
					<div style="font-family: 'Archivo', sans-serif; font-size: 10px; letter-spacing: 0.14em; text-transform: uppercase; color: #aaa; margin-bottom: 2px;">Assigned Unit</div>
					<div style="font-family: 'Sora', sans-serif; font-size: 16px; font-weight: 700; color: #0096C7;"><?php echo esc_html($assigned_room); ?></div>
				</div>
				<?php endif; ?>
			</div>

			<hr style="margin: 20px 0; border: none; border-top: 1px solid rgba(0,0,0,0.07);" />

			<div style="display: flex; gap: 20px;">
				<div style="flex: 1;">
					<div style="font-family: 'Archivo', sans-serif; font-size: 10px; letter-spacing: 0.14em; text-transform: uppercase; color: #aaa; margin-bottom: 5px;">Check-in</div>
					<div style="font-family: 'Sora', sans-serif; font-size: 14px; font-weight: 600; color: #1A1A1A;"><?php echo esc_html(date('M j, Y', strtotime($checkin))); ?></div>
					<div style="font-family: 'Archivo', sans-serif; font-size: 12px; color: #888; margin-top: 2px;">from 14:00</div>
				</div>
				<div style="flex: 1;">
					<div style="font-family: 'Archivo', sans-serif; font-size: 10px; letter-spacing: 0.14em; text-transform: uppercase; color: #aaa; margin-bottom: 5px;">Check-out</div>
					<div style="font-family: 'Sora', sans-serif; font-size: 14px; font-weight: 600; color: #1A1A1A;"><?php echo esc_html(date('M j, Y', strtotime($checkout))); ?></div>
					<div style="font-family: 'Archivo', sans-serif; font-size: 12px; color: #888; margin-top: 2px;">by 12:00</div>
				</div>
				<div style="flex: 1;">
					<div style="font-family: 'Archivo', sans-serif; font-size: 10px; letter-spacing: 0.14em; text-transform: uppercase; color: #aaa; margin-bottom: 5px;">Duration</div>
					<div style="font-family: 'Sora', sans-serif; font-size: 14px; font-weight: 600; color: #1A1A1A;"><?php echo esc_html($nights); ?> Night<?php echo $nights > 1 ? 's' : ''; ?></div>
				</div>
			</div>
		</div>
	</div>

	<div class="details-title">Guest Details</div>
	<table class="details-table">
		<tr>
			<td>Primary Guest</td>
			<td><?php echo esc_html($name); ?></td>
		</tr>
		<tr>
			<td>Phone Number</td>
			<td><?php echo esc_html($phone); ?></td>
		</tr>
		<tr>
			<td>Email Address</td>
			<td><?php echo esc_html($email); ?></td>
		</tr>
		<?php if (!empty($guests) && is_array($guests)): ?>
			<?php foreach ($guests as $index => $guest): ?>
				<tr>
					<td>Additional Guest <?php echo $index + 1; ?></td>
					<td><?php echo esc_html($guest['name']); ?> (<?php echo esc_html(ucfirst($guest['type'])); ?>)</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</table>

	<div class="details-title">Payment Summary</div>
	<table class="details-table">
		<tr>
			<td>Payment Method</td>
			<td style="text-transform: uppercase;"><?php echo esc_html($payment_method); ?></td>
		</tr>
		<tr>
			<td>Total Charged</td>
			<td class="details-highlight" style="font-size: 18px;"><?php echo esc_html($price); ?></td>
		</tr>
	</table>

	<div class="payment-note">
		<strong>Booking Status: Pending Confirmation</strong> — Our team will verify your payment and details. You will receive a final confirmation once processed. If you have any questions, please contact us at info@cwcwake.com.
	</div>

	<p style="margin-top: 32px;">We look forward to hosting you at CWC Wake Park!</p>
	<p>Best regards,<br><strong>The CWC Team</strong></p>
	<?php
	$email_content = ob_get_clean();

	$full_html = cwc_get_email_template('Booking Confirmation', $email_content, [
		'ref' => $ref,
		'banner_title' => "You're all set, " . explode(' ', $name)[0] . "!",
		'banner_subtitle' => "Your reservation at CWC Wake Park is confirmed.<br/>A summary has been sent to " . esc_html($email)
	]);

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
	<p>You have received a new inquiry from the <strong>Rates & Park Hours</strong> page. Please find the details below:</p>

	<div class="details-title">Inquiry Details</div>
	<table class="details-table">
		<tr>
			<td>From</td>
			<td><?php echo esc_html($email); ?></td>
		</tr>
		<tr>
			<td>Subject</td>
			<td><?php echo esc_html($subject); ?></td>
		</tr>
	</table>

	<div class="details-title">Message</div>
	<div style="background: #fff; border: 1.5px solid rgba(0,0,0,0.07); padding: 20px; font-family: 'Archivo', sans-serif; font-size: 14px; line-height: 1.6; color: #1A1A1A;">
		<?php echo nl2br(esc_html($message)); ?>
	</div>

	<div class="payment-note" style="margin-top: 32px;">
		<strong>Note:</strong> You can reply directly to this email to contact the visitor. This inquiry was sent via the global contact system.
	</div>
	<?php
	$email_content = ob_get_clean();

	$full_html = cwc_get_email_template('New Inquiry', $email_content, [
		'banner_title' => $subject,
		'banner_subtitle' => "A visitor has sent a message regarding: " . esc_html($subject)
	]);

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
