<?php
/**
 * CWC Wake — Page Banner block render template.
 *
 * Renders an inner-page hero banner with background image, title,
 * optional subtitle, and decorative corner brackets. Two height
 * variants are supported via the `size` attribute.
 *
 * Breadcrumbs are not rendered inside the banner; they live in the
 * tropical-bg section that wraps the main page content (see the
 * Gallery / Accommodations / Contact / Terms / Privacy templates).
 *
 * @package CWC_Wake
 * @since   1.0.0
 *
 * @var array $attributes Block attributes passed in by WordPress.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bg_image           = $attributes['backgroundImage'] ?? '';
$overlay_opacity    = $attributes['overlayOpacity'] ?? 40;
$title              = $attributes['title'] ?? '';
$subtitle           = $attributes['subtitle'] ?? '';
$size               = $attributes['size'] ?? 'default';
$show_corners       = ! empty( $attributes['showCorners'] );
$use_featured_image = ! empty( $attributes['useFeaturedImage'] );

/*
 * Resolve the background image. Priority:
 *   1. Explicit `backgroundImage` attribute (template author's choice).
 *   2. The current post's featured image, if `useFeaturedImage` is on.
 *   3. The bundled fallback image inside the theme.
 */
if ( empty( $bg_image ) && $use_featured_image && has_post_thumbnail() ) {
	$bg_image = get_the_post_thumbnail_url( null, 'full' );
}

if ( empty( $bg_image ) ) {
	$bg_image = get_stylesheet_directory_uri() . '/assets/images/hero-fallback.jpg';
}

$size_class = 'cwc-page-banner--' . ( 'compact' === $size ? 'compact' : 'default' );

$wrapper_attrs = get_block_wrapper_attributes(
	[
		'class' => 'cwc-page-banner ' . $size_class,
		'style' => sprintf(
			'--cwc-banner-bg:url(%s);--cwc-banner-overlay:%s;',
			esc_url( $bg_image ),
			esc_attr( $overlay_opacity / 100 )
		),
	]
);
?>

<section <?php echo $wrapper_attrs; ?>>
	<div class="cwc-page-banner__overlay" aria-hidden="true"></div>

	<?php if ( $show_corners ) : ?>
		<span class="cwc-page-banner__corner cwc-page-banner__corner--tl" aria-hidden="true"></span>
		<span class="cwc-page-banner__corner cwc-page-banner__corner--tr" aria-hidden="true"></span>
		<span class="cwc-page-banner__corner cwc-page-banner__corner--bl" aria-hidden="true"></span>
		<span class="cwc-page-banner__corner cwc-page-banner__corner--br" aria-hidden="true"></span>
	<?php endif; ?>

	<div class="cwc-page-banner__content">
		<?php if ( ! empty( $title ) ) : ?>
			<h1 class="cwc-page-banner__title"><?php echo esc_html( $title ); ?></h1>
		<?php endif; ?>

		<?php if ( ! empty( $subtitle ) ) : ?>
			<p class="cwc-page-banner__subtitle"><?php echo esc_html( $subtitle ); ?></p>
		<?php endif; ?>
	</div>
</section>
