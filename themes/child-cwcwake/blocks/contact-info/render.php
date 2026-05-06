<?php
/**
 * Render template for the cwc/contact-info block.
 *
 * Renders the upper Contact-page panel: a card containing the
 * "Get In Touch" details (email + phone), the "Visit Us" address +
 * Google Maps embed, and a tall right-side photograph. Layout matches
 * the Figma contact-page spec (`designs/contact-page-design.md`).
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

$get_in_touch_title       = isset( $attributes['getInTouchTitle'] ) ? (string) $attributes['getInTouchTitle'] : '';
$get_in_touch_description = isset( $attributes['getInTouchDescription'] ) ? (string) $attributes['getInTouchDescription'] : '';
$email_label              = isset( $attributes['emailLabel'] ) ? (string) $attributes['emailLabel'] : '';
$email                    = isset( $attributes['email'] ) ? (string) $attributes['email'] : '';
$phone_label              = isset( $attributes['phoneLabel'] ) ? (string) $attributes['phoneLabel'] : '';
$phone                    = isset( $attributes['phone'] ) ? (string) $attributes['phone'] : '';
$visit_us_title           = isset( $attributes['visitUsTitle'] ) ? (string) $attributes['visitUsTitle'] : '';
$address                  = isset( $attributes['address'] ) ? (string) $attributes['address'] : '';
$map_embed_url            = isset( $attributes['mapEmbedUrl'] ) ? (string) $attributes['mapEmbedUrl'] : '';
$side_image_url           = isset( $attributes['sideImageUrl'] ) ? (string) $attributes['sideImageUrl'] : '';
$side_image_alt           = isset( $attributes['sideImageAlt'] ) ? (string) $attributes['sideImageAlt'] : '';

/*
 * Fall back to the bundled photo asset when the editor leaves the
 * `sideImageUrl` attribute empty so the layout still has a right
 * column even on a freshly seeded page.
 */
if ( '' === $side_image_url ) {
	$side_image_url = get_stylesheet_directory_uri() . '/assets/images/get-in-touch-image.webp';
}

$assets_uri = get_stylesheet_directory_uri() . '/assets/images/';
$phone_icon = $assets_uri . 'get-in-touch-phone-icon.svg';
$email_icon = $assets_uri . 'email-icon.svg';
$maps_icon  = $assets_uri . 'maps.svg';

/*
 * Strip any framing markup (e.g. a full `<iframe ...></iframe>` pasted
 * by an editor) and keep just the embed URL we use as the iframe `src`.
 */
$map_src = trim( $map_embed_url );
if ( '' !== $map_src && false !== stripos( $map_src, '<iframe' ) ) {
	if ( preg_match( '/src\s*=\s*"([^"]+)"/i', $map_src, $matches ) ) {
		$map_src = $matches[1];
	} else {
		$map_src = '';
	}
}

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'cwc-contact-info' ) );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="cwc-contact-info__panel">
		<div class="cwc-contact-info__content">

			<?php if ( '' !== $get_in_touch_title ) : ?>
				<header class="cwc-contact-info__header">
					<span class="cwc-contact-info__icon-wrap" aria-hidden="true">
						<img class="cwc-contact-info__icon" src="<?php echo esc_url( $phone_icon ); ?>" alt="" width="36" height="36" loading="lazy" decoding="async" />
					</span>
					<h2 class="cwc-contact-info__title"><?php echo esc_html( $get_in_touch_title ); ?></h2>
				</header>
			<?php endif; ?>

			<?php if ( '' !== $get_in_touch_description ) : ?>
				<p class="cwc-contact-info__description"><?php echo esc_html( $get_in_touch_description ); ?></p>
			<?php endif; ?>

			<ul class="cwc-contact-info__list">
				<?php if ( '' !== $email ) : ?>
					<li class="cwc-contact-info__list-item">
						<span class="cwc-contact-info__list-icon" aria-hidden="true">
							<img src="<?php echo esc_url( $email_icon ); ?>" alt="" width="22" height="22" loading="lazy" decoding="async" />
						</span>
						<div class="cwc-contact-info__list-text">
							<?php if ( '' !== $email_label ) : ?>
								<span class="cwc-contact-info__list-label"><?php echo esc_html( $email_label ); ?></span>
							<?php endif; ?>
							<a class="cwc-contact-info__list-value" href="<?php echo esc_url( 'mailto:' . $email ); ?>">
								<?php echo esc_html( $email ); ?>
							</a>
						</div>
					</li>
				<?php endif; ?>

				<?php if ( '' !== $phone ) : ?>
					<li class="cwc-contact-info__list-item">
						<span class="cwc-contact-info__list-icon" aria-hidden="true">
							<img src="<?php echo esc_url( $phone_icon ); ?>" alt="" width="22" height="22" loading="lazy" decoding="async" />
						</span>
						<div class="cwc-contact-info__list-text">
							<?php if ( '' !== $phone_label ) : ?>
								<span class="cwc-contact-info__list-label"><?php echo esc_html( $phone_label ); ?></span>
							<?php endif; ?>
							<?php
							/*
							 * `tel:` URIs strip everything except `+` and digits.
							 * Keep the display value pretty while making the link tap-friendly.
							 */
							$tel_href = preg_replace( '/[^0-9+]/', '', $phone );
							?>
							<a class="cwc-contact-info__list-value" href="<?php echo esc_url( 'tel:' . $tel_href ); ?>">
								<?php echo esc_html( $phone ); ?>
							</a>
						</div>
					</li>
				<?php endif; ?>
			</ul>

			<?php if ( '' !== $visit_us_title ) : ?>
				<header class="cwc-contact-info__header cwc-contact-info__header--visit">
					<span class="cwc-contact-info__icon-wrap" aria-hidden="true">
						<img class="cwc-contact-info__icon" src="<?php echo esc_url( $maps_icon ); ?>" alt="" width="36" height="36" loading="lazy" decoding="async" />
					</span>
					<h2 class="cwc-contact-info__title"><?php echo esc_html( $visit_us_title ); ?></h2>
				</header>
			<?php endif; ?>

			<?php if ( '' !== $address ) : ?>
				<address class="cwc-contact-info__address">
					<?php echo nl2br( esc_html( $address ) ); ?>
				</address>
			<?php endif; ?>

			<?php if ( '' !== $map_src ) : ?>
				<div class="cwc-contact-info__map">
					<iframe
						src="<?php echo esc_url( $map_src ); ?>"
						title="<?php echo esc_attr__( 'Map showing CamSur Watersports Complex location', 'child-cwcwake' ); ?>"
						loading="lazy"
						allowfullscreen
						referrerpolicy="no-referrer-when-downgrade"
					></iframe>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( '' !== $side_image_url ) : ?>
			<div class="cwc-contact-info__image">
				<img
					src="<?php echo esc_url( $side_image_url ); ?>"
					alt="<?php echo esc_attr( $side_image_alt ); ?>"
					loading="lazy"
					decoding="async"
				/>
			</div>
		<?php endif; ?>
	</div>
</section>
