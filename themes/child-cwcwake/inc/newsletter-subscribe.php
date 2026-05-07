<?php
/**
 * Footer newsletter subscribe handler.
 *
 * Receives AJAX POSTs from the footer subscribe form, validates the email,
 * stores it in the `cwc_newsletter_subscribers` option (deduplicated),
 * and emails the admin a notification through `wp_mail()`.
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle a `cwc_newsletter_subscribe` AJAX POST.
 *
 * @since 1.0.0
 * @return void
 */
function cwc_handle_newsletter_subscribe() {
	$nonce = isset( $_POST['cwc_newsletter_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['cwc_newsletter_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'cwc_newsletter_subscribe' ) ) {
		wp_send_json_error( [ 'message' => 'Invalid security token.', 'status' => 'invalid' ] );
	}

	/* Honeypot: silently swallow bot submissions as "success". */
	$honeypot = isset( $_POST['cwc_website'] ) ? sanitize_text_field( wp_unslash( $_POST['cwc_website'] ) ) : '';
	if ( '' !== $honeypot ) {
		wp_send_json_success( [ 'message' => 'Thanks for subscribing!', 'status' => 'success' ] );
	}

	$email = isset( $_POST['cwc_newsletter_email'] ) ? sanitize_email( wp_unslash( $_POST['cwc_newsletter_email'] ) ) : '';
	if ( '' === $email || ! is_email( $email ) ) {
		wp_send_json_error( [ 'message' => 'Please enter a valid email address.', 'status' => 'invalid' ] );
	}

	$subscribers = get_option( 'cwc_newsletter_subscribers', array() );
	if ( ! is_array( $subscribers ) ) {
		$subscribers = array();
	}

	$normalized = strtolower( $email );
	$existing   = array_map( 'strtolower', wp_list_pluck( $subscribers, 'email' ) );
	if ( in_array( $normalized, $existing, true ) ) {
		wp_send_json_error( [ 'message' => "You're already subscribed — thanks for sticking with us.", 'status' => 'duplicate' ] );
	}

	$subscribers[] = array(
		'email'      => $email,
		'subscribed' => current_time( 'mysql' ),
		'ip'         => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
	);

	update_option( 'cwc_newsletter_subscribers', $subscribers, false );

	$recipient = apply_filters( 'cwc_newsletter_admin_recipient', get_option( 'admin_email' ) );

	/* translators: %s: Subscriber email address. */
	$subject = sprintf( __( 'New newsletter subscription: %s', 'child-cwcwake' ), $email );

	ob_start();
	?>
	<p>You have a new subscriber to the CWC Wake newsletter. Here are the details:</p>

	<div class="details-title">Subscriber Details</div>
	<table class="details-table">
		<tr>
			<td>Email</td>
			<td><?php echo esc_html( $email ); ?></td>
		</tr>
		<tr>
			<td>Total Subscribers</td>
			<td><?php echo esc_html( count( $subscribers ) ); ?></td>
		</tr>
	</table>
	<?php
	$email_content = ob_get_clean();

	$body = cwc_get_email_template( 'New Newsletter Subscriber', $email_content, [
		'banner_title'    => 'New Subscriber',
		'banner_subtitle' => esc_html( $email ) . ' has subscribed.'
	] );

	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	wp_mail( $recipient, $subject, $body, $headers );

	wp_send_json_success( [ 'message' => "Thanks for subscribing! We'll keep you in the loop.", 'status' => 'success' ] );
}
add_action( 'wp_ajax_cwc_newsletter_subscribe', 'cwc_handle_newsletter_subscribe' );
add_action( 'wp_ajax_nopriv_cwc_newsletter_subscribe', 'cwc_handle_newsletter_subscribe' );

/**
 * Inject the newsletter banner + dynamic form fields into the static
 * footer template.
 *
 * The footer is a static block-template HTML file, so we can't call
 * PHP from inside it. This filter walks the rendered template-part
 * markup and replaces two well-known sentinel comments.
 *
 * @since 1.0.0
 *
 * @param string $html Rendered template part HTML.
 * @return string Filtered HTML.
 */
function cwc_newsletter_inject_into_footer( $html ) {
	if ( false === strpos( $html, '<!-- CWC_NEWSLETTER_FORM_FIELDS -->' ) ) {
		return $html;
	}

	$action_url = esc_url( admin_url( 'admin-ajax.php' ) );
	$nonce      = wp_nonce_field( 'cwc_newsletter_subscribe', 'cwc_newsletter_nonce', true, false );

	$fields_replacement = sprintf(
		'<input type="hidden" name="action" value="cwc_newsletter_subscribe" />
		%1$s
		<div class="cwc-site-footer__honeypot" aria-hidden="true" style="display:none;">
			<label>Leave this empty<input type="text" name="cwc_website" tabindex="-1" autocomplete="off" /></label>
		</div>',
		$nonce
	);

	$banner_placeholder = '<div id="cwc-newsletter-msg" class="cwc-site-footer__banner" style="display:none; margin-bottom: 16px;"></div>';

	$html = str_replace( '<!-- CWC_NEWSLETTER_FORM_FIELDS -->', $fields_replacement, $html );
	$html = str_replace( 'CWC_NEWSLETTER_FORM_ACTION', $action_url, $html );
	$html = str_replace( '<!-- CWC_NEWSLETTER_BANNER -->', $banner_placeholder, $html );

	// Inject JS to handle AJAX form submission
	$js = "
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		const form = document.querySelector('[data-cwc-newsletter-form]');
		const msgDiv = document.getElementById('cwc-newsletter-msg');
		if (!form) return;
		form.addEventListener('submit', function(e) {
			e.preventDefault();
			const btn = form.querySelector('button[type=\"submit\"]');
			const originalText = btn.innerHTML;
			btn.innerHTML = 'Subscribing...';
			btn.disabled = true;
			msgDiv.style.display = 'none';

			const formData = new FormData(form);

			fetch(form.getAttribute('action'), {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(res => {
				btn.innerHTML = originalText;
				btn.disabled = false;
				msgDiv.style.display = 'block';
				msgDiv.innerHTML = res.data ? res.data.message : 'Error processing request.';
				if (res.success) {
					msgDiv.style.backgroundColor = '#d1fae5'; // Light green
					msgDiv.style.color = '#065f46'; // Dark green
					msgDiv.style.borderColor = 'rgba(22, 163, 74, 0.3)';
					form.reset();
				} else {
					if (res.data && res.data.status === 'duplicate') {
						msgDiv.style.backgroundColor = '#e0e7ff'; // Light blue
						msgDiv.style.color = '#3730a3'; // Dark blue
						msgDiv.style.borderColor = 'rgba(0, 150, 199, 0.3)';
					} else {
						msgDiv.style.backgroundColor = '#fee2e2'; // Light red
						msgDiv.style.color = '#991b1b'; // Dark red
						msgDiv.style.borderColor = 'rgba(220, 38, 38, 0.3)';
					}
				}
			})
			.catch(err => {
				btn.innerHTML = originalText;
				btn.disabled = false;
				msgDiv.style.display = 'block';
				msgDiv.style.backgroundColor = '#fee2e2';
				msgDiv.style.color = '#991b1b';
				msgDiv.style.borderColor = 'rgba(220, 38, 38, 0.3)';
				msgDiv.innerHTML = 'Network error. Please try again.';
			});
		});
	});
	</script>
	";

	$html .= $js;

	return $html;
}
add_filter( 'render_block_core/template-part', 'cwc_newsletter_inject_into_footer' );
