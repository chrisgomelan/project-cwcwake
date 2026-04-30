<?php
/**
 * Reusable email templates and handlers.
 *
 * @package ChildCwcwake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wraps content in a reusable email design.
 *
 * @param string $title   The email title/heading.
 * @param string $content The main HTML content of the email.
 * @return string The full HTML email payload.
 */
function cwc_get_email_template( $title, $content ) {
	$logo_url = home_url( '/wp-content/themes/child-cwcwake/assets/images/logo.png' ); // Assuming a logo exists

	ob_start();
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php echo esc_html( $title ); ?></title>
		<style>
			body {
				font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
				background-color: #f4f4f5;
				margin: 0;
				padding: 0;
				color: #18181b;
			}
			.email-wrapper {
				width: 100%;
				background-color: #f4f4f5;
				padding: 40px 20px;
			}
			.email-container {
				max-width: 600px;
				margin: 0 auto;
				background-color: #ffffff;
				border-radius: 12px;
				overflow: hidden;
				box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
			}
			.email-header {
				background-color: #0056FF;
				padding: 30px;
				text-align: center;
			}
			.email-header img {
				max-width: 150px;
				height: auto;
			}
			.email-body {
				padding: 40px 30px;
			}
			.email-title {
				font-size: 24px;
				font-weight: 700;
				margin-top: 0;
				margin-bottom: 24px;
				color: #18181b;
			}
			.email-content {
				font-size: 16px;
				line-height: 1.6;
				color: #3f3f46;
			}
			.email-footer {
				background-color: #fafafa;
				padding: 24px 30px;
				text-align: center;
				font-size: 14px;
				color: #71717a;
				border-top: 1px solid #e4e4e7;
			}
			.email-footer a {
				color: #0056FF;
				text-decoration: none;
			}
		</style>
	</head>
	<body>
		<div class="email-wrapper">
			<div class="email-container">
				<div class="email-header">
					<h1 style="color: #ffffff; margin: 0; font-size: 28px; letter-spacing: 2px;">CWC WAKE PARK</h1>
				</div>
				<div class="email-body">
					<h2 class="email-title"><?php echo esc_html( $title ); ?></h2>
					<div class="email-content">
						<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				</div>
				<div class="email-footer">
					<p>&copy; <?php echo date('Y'); ?> CWC Wake Park. All rights reserved.</p>
					<p><a href="<?php echo esc_url( home_url() ); ?>">Visit our website</a> | <a href="mailto:info@cwcwake.com">Contact Support</a></p>
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
function cwc_submit_booking() {
	$name           = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email          = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone          = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$checkin        = isset( $_POST['checkin'] ) ? sanitize_text_field( wp_unslash( $_POST['checkin'] ) ) : '';
	$checkout       = isset( $_POST['checkout'] ) ? sanitize_text_field( wp_unslash( $_POST['checkout'] ) ) : '';
	$room           = isset( $_POST['room'] ) ? sanitize_text_field( wp_unslash( $_POST['room'] ) ) : '';
	$price          = isset( $_POST['price'] ) ? sanitize_text_field( wp_unslash( $_POST['price'] ) ) : '';
	$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : '';
	$guests         = isset( $_POST['guests'] ) ? json_decode( wp_unslash( $_POST['guests'] ), true ) : [];

	if ( empty( $name ) || empty( $email ) ) {
		wp_send_json_error( [ 'message' => 'Name and email are required.' ] );
	}

	/* ── Save booking record ── */
	$booking_id = wp_insert_post( [
		'post_type'   => 'cwc_booking',
		'post_title'  => $name . ' — ' . $room,
		'post_status' => 'publish',
		'post_date'   => current_time( 'mysql' ),
	] );

	if ( $booking_id && ! is_wp_error( $booking_id ) ) {
		update_post_meta( $booking_id, '_cwc_bk_name',      $name );
		update_post_meta( $booking_id, '_cwc_bk_email',     $email );
		update_post_meta( $booking_id, '_cwc_bk_phone',     $phone );
		update_post_meta( $booking_id, '_cwc_bk_checkin',   $checkin );
		update_post_meta( $booking_id, '_cwc_bk_checkout',  $checkout );
		update_post_meta( $booking_id, '_cwc_bk_room',      $room );
		update_post_meta( $booking_id, '_cwc_bk_price',     $price );
		update_post_meta( $booking_id, '_cwc_bk_payment',   $payment_method );
		update_post_meta( $booking_id, '_cwc_bk_status',    'pending' );
		update_post_meta( $booking_id, '_cwc_bk_guests',    wp_json_encode( $guests ) );
		$price_num = (float) preg_replace( '/[^0-9.]/', '', $price );
		update_post_meta( $booking_id, '_cwc_bk_price_num', $price_num );
	}

	// Retrieve auto-generated reference from the insert hook
	$ref = get_post_meta( $booking_id, '_cwc_bk_ref', true );

	// Build the email content
	ob_start();
	?>
	<p>Hi <strong><?php echo esc_html( $name ); ?></strong>,</p>
	<p>Thank you for choosing CWC Wake Park! We have received your booking request. Here are the details of your reservation:</p>
	
	<table style="width: 100%; border-collapse: collapse; margin: 24px 0; background: #fafafa; border: 1px solid #e4e4e7; border-radius: 8px; overflow: hidden;">
		<?php if ( $ref ) : ?>
		<tr>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; font-weight: 600; width: 40%; color: #18181b;">Booking Reference</td>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; color: #0056FF; font-weight: 700;"><?php echo esc_html( $ref ); ?></td>
		</tr>
		<?php endif; ?>
		<tr>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; font-weight: 600; width: 40%; color: #18181b;">Room Type</td>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; color: #3f3f46;"><?php echo esc_html( $room ); ?></td>
		</tr>
		<tr>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; font-weight: 600; color: #18181b;">Check-in Date</td>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; color: #3f3f46;"><?php echo esc_html( $checkin ); ?></td>
		</tr>
		<tr>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; font-weight: 600; color: #18181b;">Check-out Date</td>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; color: #3f3f46;"><?php echo esc_html( $checkout ); ?></td>
		</tr>
		<tr>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; font-weight: 600; color: #18181b;">Phone Number</td>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; color: #3f3f46;"><?php echo esc_html( $phone ); ?></td>
		</tr>
		<tr>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; font-weight: 600; color: #18181b;">Payment Method</td>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; color: #3f3f46; text-transform: uppercase;"><?php echo esc_html( $payment_method ); ?></td>
		</tr>
		<tr>
			<td style="padding: 12px 16px; font-weight: 600; color: #18181b;">Total Price</td>
			<td style="padding: 12px 16px; color: #3f3f46; font-weight: 600; color: #0056FF;"><?php echo esc_html( $price ); ?></td>
		</tr>
	</table>

	<?php if ( ! empty( $guests ) && is_array( $guests ) ) : ?>
		<h3 style="font-size: 18px; margin-top: 32px; margin-bottom: 16px; color: #18181b;">Additional Guests</h3>
		<ul style="padding-left: 20px; color: #3f3f46; line-height: 1.6;">
			<?php foreach ( $guests as $guest ) : ?>
				<li><?php echo esc_html( $guest['name'] ); ?> (<?php echo esc_html( ucfirst( $guest['type'] ) ); ?>)</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<p style="margin-top: 32px;">If you have any questions or need to make changes to your reservation, please don't hesitate to reply to this email.</p>
	<p>We look forward to hosting you!</p>
	<p>Best regards,<br><strong>The CWC Wake Park Team</strong></p>
	<?php
	$email_content = ob_get_clean();

	$full_html = cwc_get_email_template( 'Your Booking Confirmation', $email_content );

	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	$sent = wp_mail( $email, 'Your Booking Confirmation - CWC Wake Park', $full_html, $headers );

	if ( $sent ) {
		wp_send_json_success( [ 'message' => 'Booking received and email sent.' ] );
	} else {
		wp_send_json_error( [ 'message' => 'Booking processed but email failed to send.' ] );
	}
}
add_action( 'wp_ajax_cwc_submit_booking', 'cwc_submit_booking' );
add_action( 'wp_ajax_nopriv_cwc_submit_booking', 'cwc_submit_booking' );

/**
 * Handle inquiry submission via AJAX from the Rates Manager.
 */
function cwc_submit_inquiry() {
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	if ( empty( $email ) || empty( $subject ) || empty( $message ) ) {
		wp_send_json_error( [ 'message' => 'All fields are required.' ] );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error( [ 'message' => 'Please provide a valid email address.' ] );
	}

	$recipient = get_option( 'admin_email' );
	
	// Build the email content
	ob_start();
	?>
	<p>You have received a new inquiry from the <strong>Rates & Park Hours</strong> page.</p>
	
	<table style="width: 100%; border-collapse: collapse; margin: 24px 0; background: #fafafa; border: 1px solid #e4e4e7; border-radius: 8px; overflow: hidden;">
		<tr>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; font-weight: 600; width: 30%; color: #18181b;">From</td>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; color: #3f3f46;"><?php echo esc_html( $email ); ?></td>
		</tr>
		<tr>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; font-weight: 600; color: #18181b;">Subject</td>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; color: #3f3f46;"><?php echo esc_html( $subject ); ?></td>
		</tr>
		<tr>
			<td style="padding: 12px 16px; font-weight: 600; color: #18181b; vertical-align: top;">Message</td>
			<td style="padding: 12px 16px; color: #3f3f46; line-height: 1.6;"><?php echo nl2br( esc_html( $message ) ); ?></td>
		</tr>
	</table>

	<p style="font-size: 14px; color: #71717a; margin-top: 24px;">
		<em>Note: You can reply directly to this email to contact the visitor.</em>
	</p>
	<?php
	$email_content = ob_get_clean();

	$full_html = cwc_get_email_template( 'New Inquiry: ' . $subject, $email_content );

	$headers = array( 
		'Content-Type: text/html; charset=UTF-8',
		'Reply-To: ' . $email
	);

	$sent = wp_mail( $recipient, 'CWC Inquiry: ' . $subject, $full_html, $headers );

	if ( $sent ) {
		wp_send_json_success( [ 'message' => 'Thank you! Your inquiry has been sent.' ] );
	} else {
		wp_send_json_error( [ 'message' => 'Sorry, there was an error sending your email. Please try again later.' ] );
	}
}
add_action( 'wp_ajax_cwc_submit_inquiry', 'cwc_submit_inquiry' );
add_action( 'wp_ajax_nopriv_cwc_submit_inquiry', 'cwc_submit_inquiry' );
