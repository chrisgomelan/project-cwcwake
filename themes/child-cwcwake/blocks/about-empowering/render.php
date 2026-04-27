<?php
/**
 * Render template for the cwc/about-empowering block.
 *
 * @package ChildCwcwake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme_uri = get_stylesheet_directory_uri();

$heading_before = isset( $attributes['headingBeforeAccent'] ) ? (string) $attributes['headingBeforeAccent'] : '';
$heading_accent = isset( $attributes['headingAccent'] ) ? (string) $attributes['headingAccent'] : '';
$heading_after  = isset( $attributes['headingAfterAccent'] ) ? (string) $attributes['headingAfterAccent'] : '';
$description    = isset( $attributes['description'] ) ? (string) $attributes['description'] : '';
$image          = isset( $attributes['image'] ) ? trim( (string) $attributes['image'] ) : '';
$image_alt      = isset( $attributes['imageAlt'] ) ? (string) $attributes['imageAlt'] : '';
$bracket_color  = isset( $attributes['bracketColor'] ) ? trim( (string) $attributes['bracketColor'] ) : '';
$icon_circle_bg = isset( $attributes['iconCircleBg'] ) ? trim( (string) $attributes['iconCircleBg'] ) : '';
$accent_color   = isset( $attributes['accentColor'] ) ? trim( (string) $attributes['accentColor'] ) : '';
$cards          = isset( $attributes['cards'] ) && is_array( $attributes['cards'] ) ? $attributes['cards'] : array();

// Fallback defaults for pages that don't set these
if ( '' === $heading_accent && '' === $heading_before && '' === $heading_after ) {
	$heading_accent = 'EMPOWERING';
	$heading_after  = ' the REGION.';
}

if ( '' === $image ) {
	$image = '/wp-content/uploads/2026/04/empowering.webp';
}

if ( '' === $image_alt ) {
	$image_alt = __( 'Empowering the Region', 'child-cwcwake' );
}

if ( empty( $cards ) ) {
	$cards = array(
		array(
			'icon'  => 'sustainable-tourism.svg',
			'title' => 'Sustainable Tourism',
			'desc'  => 'By promoting eco-friendly practices, maintaining natural green spaces, and supporting sustainable recreational benefit from increased tourism in the region.',
		),
		array(
			'icon'  => 'local-employment.svg',
			'title' => 'Local Employment',
			'desc'  => 'The complex provides stable jobs to local residents — from administration and hospitality to instructors and groundskeeping — helping uplift and empower the local community.',
		),
		array(
			'icon'  => 'youth-sports-dev.svg',
			'title' => 'Youth & Sports Development',
			'desc'  => 'By providing national-standard facilities for aspiring athletes, CWC nurtures young talents and puts Camarines Sur on the map as a competitive sports hub.',
		),
	);
}

$inline = '';
if ( '' !== $bracket_color ) {
	$inline .= '--cwc-empower-bracket:' . esc_attr( $bracket_color ) . ';';
}
if ( '' !== $icon_circle_bg ) {
	$inline .= '--cwc-empower-icon-bg:' . esc_attr( $icon_circle_bg ) . ';';
}
if ( '' !== $accent_color ) {
	$inline .= '--cwc-empower-accent:' . esc_attr( $accent_color ) . ';';
}

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'class' => 'cwc-empower',
		'style' => $inline,
	)
);
?>

<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<!-- Heading + description above the two-column body -->
	<div class="cwc-empower__header">
		<h2 class="cwc-empower__title">
			<?php if ( '' !== $heading_before ) : ?>
				<span><?php echo esc_html( $heading_before ); ?></span>
			<?php endif; ?>

			<?php if ( '' !== $heading_accent ) : ?>
				<em class="cwc-empower__accent"<?php echo '' !== $accent_color ? ' style="color:' . esc_attr( $accent_color ) . '"' : ''; ?>><?php echo esc_html( $heading_accent ); ?></em>
			<?php endif; ?>

			<?php if ( '' !== $heading_after ) : ?>
				<span><?php echo esc_html( $heading_after ); ?></span>
			<?php endif; ?>
		</h2>

		<?php if ( '' !== $description ) : ?>
			<p class="cwc-empower__desc"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	</div>

	<!-- Two-column body: image | cards -->
	<div class="cwc-empower__body">

		<!-- Left — image with bracket accents -->
		<div class="cwc-empower__media">
			<span class="cwc-empower__bracket cwc-empower__bracket--tl" aria-hidden="true"></span>
			<div class="cwc-empower__image-wrap">
				<?php if ( '' !== $image ) : ?>
					<img class="cwc-empower__image" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" loading="lazy">
				<?php endif; ?>
			</div>
			<span class="cwc-empower__bracket cwc-empower__bracket--br" aria-hidden="true"></span>
		</div>

		<!-- Right — info cards -->
		<div class="cwc-empower__cards">
			<?php foreach ( $cards as $card ) : ?>
				<?php
				$card_icon  = $card['icon'] ?? '';
				$card_title = $card['title'] ?? '';
				$card_desc  = $card['desc'] ?? '';

				// Support both /wp-content/uploads/ full paths and bare filename in /assets/images/.
				$icon_src = '';
				if ( '' !== $card_icon ) {
					if ( str_starts_with( $card_icon, '/' ) || str_starts_with( $card_icon, 'http' ) ) {
						$icon_src = $card_icon;
					} else {
						$icon_src = $theme_uri . '/assets/images/' . $card_icon;
					}
				}
				?>
				<div class="cwc-empower__card">
					<div class="cwc-empower__icon-wrap">
						<?php if ( '' !== $icon_src ) : ?>
							<img src="<?php echo esc_url( $icon_src ); ?>" alt="<?php echo esc_attr( $card_title ); ?>" class="cwc-empower__icon-img">
						<?php endif; ?>
					</div>
					<div class="cwc-empower__card-text">
						<?php if ( '' !== $card_title ) : ?>
							<h3 class="cwc-empower__card-title"><?php echo esc_html( $card_title ); ?></h3>
						<?php endif; ?>
						<?php if ( '' !== $card_desc ) : ?>
							<p class="cwc-empower__card-desc"><?php echo esc_html( $card_desc ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
