<?php
/**
 * Contact form server-side processing.
 *
 * Receives POSTs from the `cwc/contact-form` block, validates the
 * payload (nonce, honeypot, required fields, email format), sends an
 * email through `wp_mail()` so it transparently routes through the
 * WP Mail SMTP plugin when active, and then redirects back to the
 * originating page using the Post/Redirect/Get pattern. Status is
 * communicated to the block render via two query args:
 *
 *   - `cwc_contact=success` — banner announces success.
 *   - `cwc_contact=error&cwc_err=name,email` — banner announces error
 *     and per-field invalid markers are restored on render.
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build a stable, per-visitor key used to namespace transients.
 *
 * Combines the IP address (best-effort) with the user agent so that
 * concurrent submissions from different browsers don't overwrite each
 * other's "old values" buffer. Falls back to a constant when neither
 * is available so the site never breaks.
 *
 * @since 1.0.0
 *
 * @return string MD5 hash safe to embed in a transient key.
 */
function cwc_contact_form_session_key() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

	$cache = md5( $ip . '|' . $ua . '|cwc-contact' );
	return $cache;
}

/**
 * Resolve the redirect target sent with the form, falling back to the
 * site home URL when nothing trustworthy is available.
 *
 * Only same-host URLs are accepted so the form cannot be turned into
 * an open redirect by a malicious POST.
 *
 * @since 1.0.0
 *
 * @return string Safe absolute URL to redirect back to.
 */
function cwc_contact_form_resolve_redirect() {
	$candidate = '';

	if ( isset( $_POST['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$candidate = esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
	$cand_host = $candidate ? wp_parse_url( $candidate, PHP_URL_HOST ) : '';

	if ( ! $candidate || $cand_host !== $home_host ) {
		$candidate = home_url( '/' );
	}

	return $candidate;
}

/**
 * Redirect back to the form with success/error context.
 *
 * Strips any existing `cwc_contact*` and `cwc_err` args before adding
 * fresh ones so reloads never carry stale state. Always exits.
 *
 * @since 1.0.0
 *
 * @param string   $status        Either `success` or `error`.
 * @param string[] $error_fields  Optional. List of invalid field keys.
 * @return void
 */
function cwc_contact_form_redirect_back( $status, array $error_fields = array() ) {
	$base = remove_query_arg( array( 'cwc_contact', 'cwc_err' ), cwc_contact_form_resolve_redirect() );
	$args = array( 'cwc_contact' => $status );

	if ( $error_fields ) {
		$args['cwc_err'] = implode( ',', $error_fields );
	}

	$url = add_query_arg( $args, $base ) . '#contact-form';

	wp_safe_redirect( $url );
	exit;
}

/**
 * Handle a `cwc_contact_submit` POST.
 *
 * Validates nonce + honeypot + required fields, then delegates email
 * delivery to `wp_mail()` (which WP Mail SMTP intercepts when active).
 * Repopulates the form on failure by stashing the cleaned values in a
 * short-lived transient keyed to the visitor.
 *
 * Wired to both `admin_post_cwc_contact_submit` and
 * `admin_post_nopriv_cwc_contact_submit` so logged-out visitors can
 * also submit the form.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_handle_contact_submit() {
	$nonce = isset( $_POST['cwc_contact_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['cwc_contact_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'cwc_contact_submit' ) ) {
		cwc_contact_form_redirect_back( 'error' );
	}

	/*
	 * Honeypot: real users see the input as off-screen and ignore it.
	 * Bots indiscriminately fill every input — silently treat their
	 * submission as a "success" so they don't probe for the failure
	 * mode, but never actually send mail.
	 */
	$honeypot = isset( $_POST['cwc_company'] ) ? sanitize_text_field( wp_unslash( $_POST['cwc_company'] ) ) : '';
	if ( '' !== $honeypot ) {
		cwc_contact_form_redirect_back( 'success' );
	}

	$name    = isset( $_POST['cwc_name'] ) ? sanitize_text_field( wp_unslash( $_POST['cwc_name'] ) ) : '';
	$email   = isset( $_POST['cwc_email'] ) ? sanitize_email( wp_unslash( $_POST['cwc_email'] ) ) : '';
	$message = isset( $_POST['cwc_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cwc_message'] ) ) : '';

	$errors = array();
	if ( '' === $name ) {
		$errors[] = 'name';
	}
	if ( '' === $email || ! is_email( $email ) ) {
		$errors[] = 'email';
	}
	if ( '' === $message ) {
		$errors[] = 'message';
	}

	if ( $errors ) {
		set_transient(
			'cwc_contact_old_' . cwc_contact_form_session_key(),
			array(
				'name'    => $name,
				'email'   => $email,
				'message' => $message,
			),
			15 * MINUTE_IN_SECONDS
		);
		cwc_contact_form_redirect_back( 'error', $errors );
	}

	$recipient = apply_filters( 'cwc_contact_form_recipient', get_option( 'admin_email' ) );

	/* translators: %s: Visitor name. */
	$subject = sprintf( __( 'New contact message from %s', 'child-cwcwake' ), $name );

	/* translators: 1: Visitor name. 2: Visitor email. 3: Message body. */
	$body = sprintf(
		__(
			"You have a new message from the CWC Wake website contact form:\n\nName: %1\$s\nEmail: %2\$s\n\nMessage:\n%3\$s\n",
			'child-cwcwake'
		),
		$name,
		$email,
		$message
	);

	/*
	 * Setting Reply-To to the visitor's email lets the admin hit
	 * "Reply" in their inbox and respond directly. From: stays as
	 * the site default so the message passes SPF/DKIM checks
	 * configured in WP Mail SMTP.
	 */
	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );

	$sent = wp_mail( $recipient, $subject, $body, $headers );

	if ( ! $sent ) {
		set_transient(
			'cwc_contact_old_' . cwc_contact_form_session_key(),
			array(
				'name'    => $name,
				'email'   => $email,
				'message' => $message,
			),
			15 * MINUTE_IN_SECONDS
		);
		cwc_contact_form_redirect_back( 'error', array( 'send' ) );
	}

	cwc_contact_form_redirect_back( 'success' );
}
add_action( 'admin_post_cwc_contact_submit', 'cwc_handle_contact_submit' );
add_action( 'admin_post_nopriv_cwc_contact_submit', 'cwc_handle_contact_submit' );
