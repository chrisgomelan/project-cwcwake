<?php
/**
 * Hero Section block — render template.
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bg_image         = $attributes['backgroundImage'] ?? '';
$overlay_opacity  = $attributes['overlayOpacity'] ?? 50;
$heading_line1    = $attributes['headingLine1'] ?? 'RIDE THE';
$heading_emphasis = $attributes['headingEmphasis'] ?? 'BEST WAKEPARK';
$heading_line2    = $attributes['headingLine2'] ?? 'IN THE PHILIPPINES';
$subtitle         = $attributes['subtitle'] ?? 'Adventure, Relaxation, and Unforgettable moments - all in one place';
$primary_label    = $attributes['primaryBtnLabel'] ?? 'Book A Ride';
$primary_url      = $attributes['primaryBtnUrl'] ?? '#book';
$secondary_label  = $attributes['secondaryBtnLabel'] ?? 'Explore CWC';
$secondary_url    = $attributes['secondaryBtnUrl'] ?? '#explore';
$bg_video         = $attributes['backgroundVideo'] ?? '';
$video_url        = $attributes['videoUrl'] ?? '';
$min_height       = $attributes['minHeight'] ?? '100vh';
$show_scroll      = $attributes['showScrollToDive'] ?? false;

$fallback_image = get_stylesheet_directory_uri() . '/assets/images/hero-fallback.jpg';
$bg_src         = ! empty( $bg_image ) ? esc_url( $bg_image ) : esc_url( $fallback_image );

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'class' => 'cwc-hero',
		'style' => sprintf( '--cwc-hero-bg:url(%s);--cwc-hero-overlay:%s;min-height:%s;', $bg_src, $overlay_opacity / 100, esc_attr( $min_height ) ),
	)
);
?>

<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( ! empty( $bg_video ) ) : ?>
		<video class="cwc-hero__video" autoplay muted loop playsinline preload="auto"
			<?php
			if ( ! empty( $bg_src ) ) :
				?>
				poster="<?php echo esc_url( $bg_src ); ?>"<?php endif; ?>>
			<source src="<?php echo esc_url( $bg_video ); ?>" type="video/mp4">
		</video>
	<?php endif; ?>

	<div class="cwc-hero__overlay" aria-hidden="true"></div>

	<div class="cwc-hero__content">
		<h1 class="cwc-hero__heading">
			<?php echo esc_html( $heading_line1 ); ?>
			<em><?php echo esc_html( $heading_emphasis ); ?></em><br>
			<?php echo esc_html( $heading_line2 ); ?>
		</h1>

		<p class="cwc-hero__subtitle">
			<?php echo esc_html( $subtitle ); ?>
		</p>

		<div class="cwc-hero__actions">
			<?php if ( ! empty( $primary_label ) ) : ?>
				<a href="<?php echo esc_url( $primary_url ); ?>" class="cwc-hero__btn cwc-hero__btn--primary">
					<?php echo esc_html( $primary_label ); ?>
				</a>
			<?php endif; ?>

			<?php if ( ! empty( $secondary_label ) ) : ?>
				<a href="<?php echo esc_url( $secondary_url ); ?>" class="cwc-hero__btn cwc-hero__btn--secondary">
					<?php echo esc_html( $secondary_label ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( ! empty( $bg_video ) ) : ?>
		<button class="cwc-hero__video-toggle" type="button" aria-label="Pause background video" data-playing="true">
			<svg class="cwc-hero__icon-pause" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><rect x="5" y="3" width="5" height="18"/><rect x="14" y="3" width="5" height="18"/></svg>
			<svg class="cwc-hero__icon-play" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="6,3 20,12 6,21"/></svg>
		</button>
	<?php endif; ?>

	<?php if ( ! empty( $video_url ) ) : ?>
		<a href="<?php echo esc_url( $video_url ); ?>" class="cwc-hero__play" aria-label="Play video" target="_blank" rel="noopener noreferrer">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><polygon points="5,3 19,12 5,21"/></svg>
		</a>
	<?php endif; ?>

	<?php if ( $show_scroll ) : ?>
		<div class="cwc-hero__scroll-dive">
			<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#F5F1EB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
				<path d="M12 5v14M19 12l-7 7-7-7"/>
			</svg>
			<span>SCROLL TO DIVE</span>
		</div>
	<?php endif; ?>
</section>
