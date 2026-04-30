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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ────────────────────────────────────────────
   CPT Registration
   ──────────────────────────────────────────── */

function cwc_register_booking_cpt() {
	register_post_type( 'cwc_booking', [
		'labels' => [
			'name'          => __( 'Bookings', 'cwc-accommodations' ),
			'singular_name' => __( 'Booking', 'cwc-accommodations' ),
		],
		'public'              => false,
		'show_ui'             => false,
		'show_in_menu'        => false,
		'show_in_rest'        => false,
		'supports'            => [ 'title' ],
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
	] );
}
add_action( 'init', 'cwc_register_booking_cpt', 8 );

/* ────────────────────────────────────────────
   Booking Reference Generator
   ──────────────────────────────────────────── */

/**
 * Generate a unique booking reference like CWC-A7B9X2.
 *
 * @return string Unique reference ID.
 */
function cwc_generate_booking_ref() {
	$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // No 0/O/1/I to avoid confusion
	$attempts = 0;

	do {
		$ref = 'CWC-';
		for ( $i = 0; $i < 6; $i++ ) {
			$ref .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ];
		}
		// Check uniqueness
		$existing = get_posts( [
			'post_type'   => 'cwc_booking',
			'meta_key'    => '_cwc_bk_ref',
			'meta_value'  => $ref,
			'numberposts' => 1,
			'fields'      => 'ids',
		] );
		$attempts++;
	} while ( ! empty( $existing ) && $attempts < 10 );

	return $ref;
}

/**
 * Auto-generate a reference for bookings that don't have one yet.
 * Called on post save for the cwc_booking type.
 */
function cwc_maybe_assign_booking_ref( $post_id, $post, $update ) {
	if ( 'cwc_booking' !== $post->post_type ) {
		return;
	}

	$existing_ref = get_post_meta( $post_id, '_cwc_bk_ref', true );
	if ( empty( $existing_ref ) ) {
		$ref = cwc_generate_booking_ref();
		update_post_meta( $post_id, '_cwc_bk_ref', $ref );
	}

	// Also ensure payment_status exists
	$payment_status = get_post_meta( $post_id, '_cwc_bk_payment_status', true );
	if ( empty( $payment_status ) ) {
		update_post_meta( $post_id, '_cwc_bk_payment_status', 'unpaid' );
	}
}
add_action( 'wp_insert_post', 'cwc_maybe_assign_booking_ref', 10, 3 );

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
function cwc_add_audit_log( $booking_id, $action, $details = [] ) {
	$log = json_decode( get_post_meta( $booking_id, '_cwc_bk_audit_log', true ) ?: '[]', true );

	$current_user = wp_get_current_user();

	$log[] = array_merge( [
		'action'    => $action,
		'admin'     => $current_user->user_login ?? 'system',
		'timestamp' => current_time( 'mysql' ),
	], $details );

	update_post_meta( $booking_id, '_cwc_bk_audit_log', wp_json_encode( $log ) );
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
function cwc_log_email( $booking_id, $type, $to, $sent, $admin_note = '' ) {
	$log = json_decode( get_post_meta( $booking_id, '_cwc_bk_email_log', true ) ?: '[]', true );

	$log[] = [
		'type'       => $type,
		'to'         => $to,
		'sent'       => $sent,
		'admin_note' => $admin_note,
		'timestamp'  => current_time( 'mysql' ),
	];

	update_post_meta( $booking_id, '_cwc_bk_email_log', wp_json_encode( $log ) );
}

/* ────────────────────────────────────────────
   AJAX: Update Booking Status (with modal)
   ──────────────────────────────────────────── */

function cwc_update_booking_status() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => 'Unauthorized' ] );
	}

	$booking_id = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0;
	$new_status = isset( $_POST['new_status'] ) ? sanitize_key( $_POST['new_status'] ) : '';
	$send_email = isset( $_POST['send_email'] ) && $_POST['send_email'] === 'true';
	$admin_note = isset( $_POST['admin_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['admin_note'] ) ) : '';

	$valid_statuses = [ 'pending', 'confirmed', 'cancelled', 'completed' ];

	if ( ! $booking_id || ! in_array( $new_status, $valid_statuses, true ) ) {
		wp_send_json_error( [ 'message' => 'Invalid data.' ] );
	}

	$old_status = get_post_meta( $booking_id, '_cwc_bk_status', true );

	// Update status
	update_post_meta( $booking_id, '_cwc_bk_status', $new_status );

	// Audit log
	cwc_add_audit_log( $booking_id, 'status_changed', [
		'from'       => $old_status,
		'to'         => $new_status,
		'note'       => $admin_note,
		'email_sent' => $send_email,
	] );

	// Send email if requested
	$email_result = false;
	if ( $send_email && $old_status !== $new_status ) {
		$email_result = cwc_send_booking_status_email( $booking_id, $new_status, $admin_note );
	}

	wp_send_json_success( [
		'message'    => 'Status updated.',
		'email_sent' => $email_result,
		'new_status' => $new_status,
	] );
}
add_action( 'wp_ajax_cwc_update_booking_status', 'cwc_update_booking_status' );

/* ────────────────────────────────────────────
   AJAX: Update Payment Status
   ──────────────────────────────────────────── */

function cwc_update_payment_status() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => 'Unauthorized' ] );
	}

	$booking_id = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0;
	$new_status = isset( $_POST['payment_status'] ) ? sanitize_key( $_POST['payment_status'] ) : '';

	$valid = [ 'unpaid', 'paid', 'failed', 'refunded' ];

	if ( ! $booking_id || ! in_array( $new_status, $valid, true ) ) {
		wp_send_json_error( [ 'message' => 'Invalid data.' ] );
	}

	$old_status = get_post_meta( $booking_id, '_cwc_bk_payment_status', true );
	update_post_meta( $booking_id, '_cwc_bk_payment_status', $new_status );

	cwc_add_audit_log( $booking_id, 'payment_status_changed', [
		'from' => $old_status,
		'to'   => $new_status,
	] );

	wp_send_json_success( [
		'message'    => 'Payment status updated.',
		'new_status' => $new_status,
	] );
}
add_action( 'wp_ajax_cwc_update_payment_status', 'cwc_update_payment_status' );

/* ────────────────────────────────────────────
   AJAX: Resend Last Email
   ──────────────────────────────────────────── */

function cwc_resend_booking_email() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => 'Unauthorized' ] );
	}

	$booking_id = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0;
	if ( ! $booking_id ) {
		wp_send_json_error( [ 'message' => 'Invalid booking.' ] );
	}

	$status = get_post_meta( $booking_id, '_cwc_bk_status', true );
	$result = cwc_send_booking_status_email( $booking_id, $status, '' );

	cwc_add_audit_log( $booking_id, 'email_resent', [
		'status' => $status,
		'result' => $result,
	] );

	if ( $result ) {
		wp_send_json_success( [ 'message' => 'Email resent successfully.' ] );
	} else {
		wp_send_json_error( [ 'message' => 'Failed to send email.' ] );
	}
}
add_action( 'wp_ajax_cwc_resend_booking_email', 'cwc_resend_booking_email' );

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
function cwc_send_booking_status_email( $booking_id, $status, $admin_note = '' ) {
	$email    = get_post_meta( $booking_id, '_cwc_bk_email', true );
	$name     = get_post_meta( $booking_id, '_cwc_bk_name', true );
	$room     = get_post_meta( $booking_id, '_cwc_bk_room', true );
	$checkin  = get_post_meta( $booking_id, '_cwc_bk_checkin', true );
	$checkout = get_post_meta( $booking_id, '_cwc_bk_checkout', true );
	$price    = get_post_meta( $booking_id, '_cwc_bk_price', true );
	$ref      = get_post_meta( $booking_id, '_cwc_bk_ref', true );
	$pay_status = get_post_meta( $booking_id, '_cwc_bk_payment_status', true ) ?: 'unpaid';
	$pay_method = get_post_meta( $booking_id, '_cwc_bk_payment', true );

	if ( ! $email ) {
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

	if ( ! isset( $templates[ $status ] ) ) {
		return false;
	}

	$tpl = $templates[ $status ];

	// Build the email body
	ob_start();
	?>
	<p>Hi <strong><?php echo esc_html( $name ); ?></strong>,</p>
	<p><?php echo esc_html( $tpl['message'] ); ?></p>

	<table style="width: 100%; border-collapse: collapse; margin: 24px 0; background: #fafafa; border: 1px solid #e4e4e7; border-radius: 8px; overflow: hidden;">
		<?php if ( $ref ) : ?>
		<tr>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; font-weight: 600; width: 40%; color: #18181b;">Booking Reference</td>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; color: #0056FF; font-weight: 700;"><?php echo esc_html( $ref ); ?></td>
		</tr>
		<?php endif; ?>
		<tr>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; font-weight: 600; color: #18181b;">Room</td>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; color: #3f3f46;"><?php echo esc_html( $room ); ?></td>
		</tr>
		<tr>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; font-weight: 600; color: #18181b;">Check-in</td>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; color: #3f3f46;"><?php echo esc_html( $checkin ); ?></td>
		</tr>
		<tr>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; font-weight: 600; color: #18181b;">Check-out</td>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; color: #3f3f46;"><?php echo esc_html( $checkout ); ?></td>
		</tr>
		<tr>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; font-weight: 600; color: #18181b;">Amount</td>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; color: #3f3f46;"><?php echo esc_html( $price ); ?></td>
		</tr>
		<tr>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; font-weight: 600; color: #18181b;">Payment</td>
			<td style="padding: 12px 16px; border-bottom: 1px solid #e4e4e7; color: #3f3f46; text-transform: capitalize;"><?php echo esc_html( $pay_status ); ?> (<?php echo esc_html( strtoupper( $pay_method ) ); ?>)</td>
		</tr>
		<tr>
			<td style="padding: 12px 16px; font-weight: 600; color: #18181b;">Booking Status</td>
			<td style="padding: 12px 16px; color: #0056FF; font-weight: 700; text-transform: capitalize;"><?php echo esc_html( $status ); ?></td>
		</tr>
	</table>

	<?php if ( ! empty( $admin_note ) ) : ?>
	<div style="background: #f0f4ff; border-left: 4px solid #0056FF; padding: 16px 20px; margin: 24px 0; border-radius: 0 8px 8px 0;">
		<p style="margin: 0 0 4px; font-weight: 700; color: #1e293b; font-size: 14px;">Note from CWC Team:</p>
		<p style="margin: 0; color: #334155; line-height: 1.6;"><?php echo nl2br( esc_html( $admin_note ) ); ?></p>
	</div>
	<?php endif; ?>

	<p style="margin-top: 24px; color: #64748b; font-size: 14px;">If you have any questions, feel free to reply to this email or contact us at <a href="mailto:info@cwcwake.com" style="color: #0056FF;">info@cwcwake.com</a>.</p>
	<?php
	$body = ob_get_clean();

	// Wrap in premium template
	if ( function_exists( 'cwc_get_email_template' ) ) {
		$full_html = cwc_get_email_template( $tpl['heading'], $body );
	} else {
		$full_html = $body;
	}

	$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
	$sent = wp_mail( $email, $tpl['subject'], $full_html, $headers );

	// Log the email
	cwc_log_email( $booking_id, 'status_' . $status, $email, $sent, $admin_note );

	return $sent;
}
