<?php
/**
 * Render template for the cwc/contact-form block.
 *
 * Renders the "Send Us A Message" panel: a card with name, email and
 * message inputs (all required) plus a primary submit button. Submits
 * via POST to `admin-post.php` so the server-side handler in
 * `inc/contact-form-handler.php` can validate, send mail through
 * `wp_mail` (intercepted by WP Mail SMTP when active), and redirect
 * back here using the Post/Redirect/Get pattern.
 *
 * Status query args read on render:
 *   - cwc_contact=success            → render success banner
 *   - cwc_contact=error&cwc_err=...  → render error banner + per-field
 *                                      `aria-invalid` markers
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner block markup (unused).
 * @var WP_Block $block      Block instance.
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$form_title          = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
$name_label          = isset( $attributes['nameLabel'] ) ? (string) $attributes['nameLabel'] : 'Name';
$name_placeholder    = isset( $attributes['namePlaceholder'] ) ? (string) $attributes['namePlaceholder'] : '';
$email_label         = isset( $attributes['emailLabel'] ) ? (string) $attributes['emailLabel'] : 'Email';
$email_placeholder   = isset( $attributes['emailPlaceholder'] ) ? (string) $attributes['emailPlaceholder'] : '';
$message_label       = isset( $attributes['messageLabel'] ) ? (string) $attributes['messageLabel'] : 'Message';
$message_placeholder = isset( $attributes['messagePlaceholder'] ) ? (string) $attributes['messagePlaceholder'] : '';
$submit_label        = isset( $attributes['submitLabel'] ) ? (string) $attributes['submitLabel'] : 'Send Message';
$success_message     = isset( $attributes['successMessage'] ) ? (string) $attributes['successMessage'] : '';
$error_message       = isset( $attributes['errorMessage'] ) ? (string) $attributes['errorMessage'] : '';
$recipient_email     = isset( $attributes['recipientEmail'] ) ? (string) $attributes['recipientEmail'] : '';

/*
 * Status feedback comes from a POST → redirect cycle, so we read it from
 * the query string. We also surface per-field error keys (sent by the
 * handler in `cwc_err`) so we can mark the failing inputs with
 * `aria-invalid` and a visible style.
 */
$submission_status = isset( $_GET['cwc_contact'] ) ? sanitize_key( wp_unslash( $_GET['cwc_contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$error_fields      = array();
if ( 'error' === $submission_status && isset( $_GET['cwc_err'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$raw          = sanitize_text_field( wp_unslash( $_GET['cwc_err'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$error_fields = array_filter( array_map( 'sanitize_key', explode( ',', $raw ) ) );
}

/*
 * Repopulate the inputs after a validation failure so the user does
 * not have to retype everything.
 */
$old_values = array(
	'name'    => '',
	'email'   => '',
	'message' => '',
);
if ( 'error' === $submission_status ) {
	$old = get_transient( 'cwc_contact_old_' . cwc_contact_form_session_key() );
	if ( is_array( $old ) ) {
		$old_values = wp_parse_args( $old, $old_values );
		delete_transient( 'cwc_contact_old_' . cwc_contact_form_session_key() );
	}
}

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'class' => 'cwc-contact-form',

		/*
		 * Stable id used as the redirect-back anchor target so users
		 * always land on the form (not at the top of the page) after
		 * a successful submit or a validation error.
		 */
		'id'    => 'contact-form',
	)
);
$action_url    = esc_url( admin_url( 'admin-post.php' ) );
$is_invalid    = static function ( string $field ) use ( $error_fields ): string {
	return in_array( $field, $error_fields, true ) ? ' aria-invalid="true"' : '';
};
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="cwc-contact-form__panel">

		<?php if ( '' !== $form_title ) : ?>
			<header class="cwc-contact-form__header">
				<span class="cwc-contact-form__icon-wrap" aria-hidden="true">
					<img
						class="cwc-contact-form__icon"
						src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/send-us-message.svg' ); ?>"
						alt=""
						width="36"
						height="36"
						loading="lazy"
						decoding="async"
					/>
				</span>
				<h2 class="cwc-contact-form__title"><?php echo esc_html( $form_title ); ?></h2>
			</header>
		<?php endif; ?>

		<?php if ( 'success' === $submission_status && '' !== $success_message ) : ?>
			<div
				class="cwc-contact-form__banner cwc-contact-form__banner--success"
				role="status"
				tabindex="-1"
				data-cwc-contact-banner
			>
				<?php echo esc_html( $success_message ); ?>
			</div>
		<?php elseif ( 'error' === $submission_status && '' !== $error_message ) : ?>
			<div
				class="cwc-contact-form__banner cwc-contact-form__banner--error"
				role="alert"
				tabindex="-1"
				data-cwc-contact-banner
			>
				<?php echo esc_html( $error_message ); ?>
			</div>
		<?php endif; ?>

		<form
			class="cwc-contact-form__form"
			action="<?php echo $action_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
			method="post"
			novalidate
			data-cwc-contact-form
		>
			<input type="hidden" name="action" value="cwc_contact_submit" />
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( get_permalink() ); ?>" />
			<?php if ( '' !== $recipient_email ) : ?>
				<input type="hidden" name="recipient_hash" value="<?php echo esc_attr( wp_hash( $recipient_email ) ); ?>" />
			<?php endif; ?>
			<?php wp_nonce_field( 'cwc_contact_submit', 'cwc_contact_nonce' ); ?>

			<?php /* Honeypot — real users leave this blank; bots fill every input. */ ?>
			<div class="cwc-contact-form__honeypot" aria-hidden="true">
				<label>
					Leave this field empty
					<input type="text" name="cwc_company" tabindex="-1" autocomplete="off" />
				</label>
			</div>

			<div class="cwc-contact-form__field">
				<label class="cwc-contact-form__label" for="cwc-contact-name">
					<?php echo esc_html( $name_label ); ?>
					<span class="cwc-contact-form__required" aria-hidden="true">*</span>
				</label>
				<input
					class="cwc-contact-form__input"
					type="text"
					id="cwc-contact-name"
					name="cwc_name"
					placeholder="<?php echo esc_attr( $name_placeholder ); ?>"
					value="<?php echo esc_attr( $old_values['name'] ); ?>"
					autocomplete="name"
					required
					<?php echo $is_invalid( 'name' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				/>
			</div>

			<div class="cwc-contact-form__field">
				<label class="cwc-contact-form__label" for="cwc-contact-email">
					<?php echo esc_html( $email_label ); ?>
					<span class="cwc-contact-form__required" aria-hidden="true">*</span>
				</label>
				<input
					class="cwc-contact-form__input"
					type="email"
					id="cwc-contact-email"
					name="cwc_email"
					placeholder="<?php echo esc_attr( $email_placeholder ); ?>"
					value="<?php echo esc_attr( $old_values['email'] ); ?>"
					autocomplete="email"
					required
					<?php echo $is_invalid( 'email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				/>
			</div>

			<div class="cwc-contact-form__field">
				<label class="cwc-contact-form__label" for="cwc-contact-message">
					<?php echo esc_html( $message_label ); ?>
					<span class="cwc-contact-form__required" aria-hidden="true">*</span>
				</label>
				<textarea
					class="cwc-contact-form__input cwc-contact-form__input--textarea"
					id="cwc-contact-message"
					name="cwc_message"
					placeholder="<?php echo esc_attr( $message_placeholder ); ?>"
					rows="6"
					required
					<?php echo $is_invalid( 'message' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				><?php echo esc_textarea( $old_values['message'] ); ?></textarea>
			</div>

			<button class="cwc-contact-form__submit" type="submit">
				<?php echo esc_html( $submit_label ); ?>
			</button>
		</form>
	</div>
</section>
