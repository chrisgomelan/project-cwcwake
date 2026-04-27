<?php
/**
 * About — Empowering the Region (configurable).
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme_uri = get_stylesheet_directory_uri();

$heading_accent = isset( $attributes['headingAccent'] ) ? (string) $attributes['headingAccent'] : 'EMPOWERING';
$heading_after  = isset( $attributes['headingAfterAccent'] ) ? (string) $attributes['headingAfterAccent'] : ' the REGION.';
$description    = isset( $attributes['description'] ) ? (string) $attributes['description'] : '';
$image          = isset( $attributes['image'] ) ? trim( (string) $attributes['image'] ) : '/wp-content/uploads/2026/04/empowering.webp';
$image_alt      = isset( $attributes['imageAlt'] ) ? (string) $attributes['imageAlt'] : '';
$bracket_color  = isset( $attributes['bracketColor'] ) ? trim( (string) $attributes['bracketColor'] ) : '';
$icon_circle    = isset( $attributes['iconCircleBg'] ) ? trim( (string) $attributes['iconCircleBg'] ) : '';
$cards          = isset( $attributes['cards'] ) && is_array( $attributes['cards'] ) ? $attributes['cards'] : array();

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
if ( '' !== $icon_circle ) {
	$inline .= '--cwc-empower-icon-bg:' . esc_attr( $icon_circle ) . ';';
}

$wrapper_args = array( 'class' => 'cwc-empower' );
if ( '' !== $inline ) {
	$wrapper_args['style'] = $inline;
}
$wrapper = get_block_wrapper_attributes( $wrapper_args );
?>

<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<header class="cwc-empower__header">
		<h2 class="cwc-empower__title">
			<?php if ( '' !== $heading_accent ) : ?>
				<span class="cwc-empower__accent"><?php echo esc_html( $heading_accent ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $heading_after ) : ?>
				<?php echo esc_html( ( '' !== $heading_accent ? ' ' : '' ) . $heading_after ); ?>
			<?php endif; ?>
		</h2>
		<?php if ( '' !== $description ) : ?>
			<p class="cwc-empower__desc"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	</header>

	<div class="cwc-empower__body">
		<div class="cwc-empower__media">
			<div class="cwc-empower__bracket cwc-empower__bracket--tl" aria-hidden="true"></div>
			<div class="cwc-empower__bracket cwc-empower__bracket--br" aria-hidden="true"></div>
			<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" loading="lazy" class="cwc-empower__image">
		</div>

		<div class="cwc-empower__cards">
			<?php foreach ( $cards as $card ) : ?>
				<?php
				if ( ! is_array( $card ) ) {
					continue;
				}
				$icon = isset( $card['icon'] ) ? (string) $card['icon'] : '';
				$ct   = isset( $card['title'] ) ? (string) $card['title'] : '';
				$cd   = isset( $card['desc'] ) ? (string) $card['desc'] : '';
				$icon_url = ( strpos( $icon, 'http' ) === 0 ) ? $icon : ( $theme_uri . '/assets/images/' . $icon );
				?>
				<div class="cwc-empower__card">
					<div class="cwc-empower__icon-wrap">
						<?php if ( '' !== $icon ) : ?>
							<img src="<?php echo esc_url( $icon_url ); ?>" alt="" loading="lazy" aria-hidden="true">
						<?php endif; ?>
					</div>
					<div class="cwc-empower__card-text">
						<?php if ( '' !== $ct ) : ?>
							<h3 class="cwc-empower__card-title"><?php echo esc_html( $ct ); ?></h3>
						<?php endif; ?>
						<?php if ( '' !== $cd ) : ?>
							<p class="cwc-empower__card-desc"><?php echo esc_html( $cd ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
