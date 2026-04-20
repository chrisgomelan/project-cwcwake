<?php
/**
 * Footer newsletter subscribe handler.
 *
 * Receives POSTs from the footer subscribe form, validates the email,
 * stores it in the `cwc_newsletter_subscribers` option (deduplicated),
 * and emails the admin a notification through `wp_mail()` so it
 * routes through the WP Mail SMTP plugin when active. Uses the
 * Post/Redirect/Get pattern with two query args:
 *
 *   - `cwc_newsletter=success`         — banner: thanks for subscribing.
 *   - `cwc_newsletter=invalid`         — banner: please enter a valid email.
 *   - `cwc_newsletter=duplicate`       — banner: already subscribed.
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve a safe same-host redirect target sent with the form.
 *
 * Falls back to the site home URL when the supplied value is missing
 * or points to a different host (defensive against open-redirect abuse).
 *
 * @since 1.0.0
 *
 * @return string Safe absolute URL.
 */
function cwc_newsletter_resolve_redirect()
{
	$candidate = '';
	if ( isset( $_POST['redirect_to'] ) ) {
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
 * Redirect back to the originating page with status context.
 *
 * Strips any prior `cwc_newsletter` arg before adding a fresh one so
 * reloads never carry stale state. Always exits.
 *
 * @since 1.0.0
 *
 * @param string $status One of `success`, `invalid`, `duplicate`, `error`.
 * @return void
 */
function cwc_newsletter_redirect_back( $status )
{
	$base = remove_query_arg( [ 'cwc_newsletter' ], cwc_newsletter_resolve_redirect() );
	$url  = add_query_arg( [ 'cwc_newsletter' => $status ], $base ) . '#newsletter';

	wp_safe_redirect( $url );
	exit;
}

/**
 * Handle a `cwc_newsletter_subscribe` POST.
 *
 * Wired to both `admin_post_*` and `admin_post_nopriv_*` so logged-out
 * visitors can subscribe.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_handle_newsletter_subscribe()
{
	$nonce = isset( $_POST['cwc_newsletter_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['cwc_newsletter_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'cwc_newsletter_subscribe' ) ) {
		cwc_newsletter_redirect_back( 'invalid' );
	}

	/* Honeypot: silently swallow bot submissions as "success". */
	$honeypot = isset( $_POST['cwc_website'] ) ? trim( (string) wp_unslash( $_POST['cwc_website'] ) ) : '';
	if ( '' !== $honeypot ) {
		cwc_newsletter_redirect_back( 'success' );
	}

	$email = isset( $_POST['cwc_newsletter_email'] ) ? sanitize_email( wp_unslash( $_POST['cwc_newsletter_email'] ) ) : '';
	if ( '' === $email || ! is_email( $email ) ) {
		cwc_newsletter_redirect_back( 'invalid' );
	}

	$subscribers = get_option( 'cwc_newsletter_subscribers', [] );
	if ( ! is_array( $subscribers ) ) {
		$subscribers = [];
	}

	/*
	 * Email comparison is intentionally case-insensitive on the local
	 * part too — most providers treat addresses as case-insensitive,
	 * and we'd rather be a little permissive than ask the same person
	 * to subscribe twice.
	 */
	$normalized = strtolower( $email );
	$existing   = array_map( 'strtolower', wp_list_pluck( $subscribers, 'email' ) );
	if ( in_array( $normalized, $existing, true ) ) {
		cwc_newsletter_redirect_back( 'duplicate' );
	}

	$subscribers[] = [
		'email'      => $email,
		'subscribed' => current_time( 'mysql' ),
		'ip'         => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
	];

	update_option( 'cwc_newsletter_subscribers', $subscribers, false );

	$recipient = apply_filters( 'cwc_newsletter_admin_recipient', get_option( 'admin_email' ) );

	/* translators: %s: Subscriber email address. */
	$subject = sprintf( __( 'New newsletter subscription: %s', 'child-cwcwake' ), $email );

	/* translators: 1: Subscriber email. 2: Total subscriber count. */
	$body = sprintf(
		__(
			"A visitor just subscribed to the CWC Wake newsletter:\n\nEmail: %1\$s\nTotal subscribers: %2\$d\n",
			'child-cwcwake'
		),
		$email,
		count( $subscribers )
	);

	/*
	 * The send result isn't fatal for the user-facing flow — they're
	 * still subscribed even if the admin notice can't be delivered —
	 * so we don't surface a separate error state for the visitor.
	 */
	wp_mail( $recipient, $subject, $body );

	cwc_newsletter_redirect_back( 'success' );
}
add_action( 'admin_post_cwc_newsletter_subscribe', 'cwc_handle_newsletter_subscribe' );
add_action( 'admin_post_nopriv_cwc_newsletter_subscribe', 'cwc_handle_newsletter_subscribe' );

/**
 * Render the visible status banner that the footer template injects.
 *
 * Kept in PHP (not in the static `footer.html` markup) because the
 * banner depends on the `cwc_newsletter` query arg. The footer
 * template prints `<?php cwc_newsletter_render_banner(); ?>` (via the
 * filter below) on the frontend.
 *
 * @since 1.0.0
 *
 * @return string Banner HTML, or empty string when no status is set.
 */
function cwc_newsletter_get_banner_html()
{
	if ( ! isset( $_GET['cwc_newsletter'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return '';
	}

	$status = sanitize_key( wp_unslash( $_GET['cwc_newsletter'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$messages = [
		'success'   => [
			'class' => 'cwc-site-footer__banner--success',
			'text'  => __( 'Thanks for subscribing! We\'ll keep you in the loop.', 'child-cwcwake' ),
		],
		'duplicate' => [
			'class' => 'cwc-site-footer__banner--info',
			'text'  => __( 'You\'re already subscribed — thanks for sticking with us.', 'child-cwcwake' ),
		],
		'invalid'   => [
			'class' => 'cwc-site-footer__banner--error',
			'text'  => __( 'Please enter a valid email address.', 'child-cwcwake' ),
		],
	];

	if ( ! isset( $messages[ $status ] ) ) {
		return '';
	}

	$role = ( 'invalid' === $status ) ? 'alert' : 'status';

	return sprintf(
		'<div class="cwc-site-footer__banner %1$s" role="%2$s" tabindex="-1" data-cwc-newsletter-banner>%3$s</div>',
		esc_attr( $messages[ $status ]['class'] ),
		esc_attr( $role ),
		esc_html( $messages[ $status ]['text'] )
	);
}

/**
 * Inject the newsletter banner + dynamic form fields into the static
 * footer template.
 *
 * The footer is a static block-template HTML file, so we can't call
 * PHP from inside it. This filter walks the rendered template-part
 * markup and replaces two well-known sentinel comments — set in
 * `parts/footer.html` — with the dynamic banner + nonce/action/honeypot
 * inputs the subscribe handler expects.
 *
 * @since 1.0.0
 *
 * @param string $html Rendered template part HTML.
 * @return string Filtered HTML.
 */
function cwc_newsletter_inject_into_footer( $html )
{
	if ( false === strpos( $html, '<!-- CWC_NEWSLETTER_FORM_FIELDS -->' ) ) {
		return $html;
	}

	$action_url = esc_url( admin_url( 'admin-post.php' ) );
	$redirect   = esc_url( ( is_singular() || is_page() ) ? get_permalink() : home_url( add_query_arg( null, null ) ) );
	$nonce      = wp_nonce_field( 'cwc_newsletter_subscribe', 'cwc_newsletter_nonce', true, false );

	/*
	 * Sentinel-style placeholders are HTML comments that the template
	 * editor sees as inert markup, but we know to replace at render time
	 * to keep the form in sync with the server-side handler.
	 */
	$fields_replacement = sprintf(
		'<input type="hidden" name="action" value="cwc_newsletter_subscribe" />
		<input type="hidden" name="redirect_to" value="%1$s" />
		%2$s
		<div class="cwc-site-footer__honeypot" aria-hidden="true">
			<label>Leave this empty<input type="text" name="cwc_website" tabindex="-1" autocomplete="off" /></label>
		</div>',
		$redirect,
		$nonce
	);

	$html = str_replace( '<!-- CWC_NEWSLETTER_FORM_FIELDS -->', $fields_replacement, $html );
	$html = str_replace( 'CWC_NEWSLETTER_FORM_ACTION', $action_url, $html );
	$html = str_replace( '<!-- CWC_NEWSLETTER_BANNER -->', cwc_newsletter_get_banner_html(), $html );

	return $html;
}
add_filter( 'render_block_core/template-part', 'cwc_newsletter_inject_into_footer' );
