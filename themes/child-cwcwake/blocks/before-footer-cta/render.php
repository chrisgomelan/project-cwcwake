<?php
/**
 * Before Footer CTA — "Start Your Own Story" / custom variant.
 *
 * Supports an optional second button, custom background image,
 * and custom overlay gradient for per-page variations.
 *
 * Custom `overlayGradient` values from templates usually use opaque colour
 * stops. Painting that on a full-size overlay hides the background image.
 * Inline gradients therefore include a slight `opacity` so the section
 * background (default or custom photo) remains visible underneath.
 *
 * @package CWC_Wake
 */

if (!defined('ABSPATH')) {
	exit;
}

// Debug: output attributes as a comment for verification
echo '<!-- Block Attributes: ' . esc_html(json_encode($attributes)) . ' -->';

$cta_title = $attributes['title'] ?? 'START YOUR OWN';
$title_accent = $attributes['titleAccent'] ?? 'STORY';
$accent_first = $attributes['accentFirst'] ?? false;
$description = $attributes['description'] ?? "Whether you're a first-timer or a pro, the water is waiting. Plan your visit to the world's premier cable park today.";
$button_label = $attributes['buttonLabel'] ?? 'BOOK NOW';
$button_url = $attributes['buttonUrl'] ?? '#book';
$sec_btn_label = $attributes['secondaryBtnLabel'] ?? '';
$sec_btn_url = $attributes['secondaryBtnUrl'] ?? '';
$bg_image = $attributes['backgroundImage'] ?? '';
$overlay_grad = $attributes['overlayGradient'] ?? '';

$extra_class = '';
$section_style = '';
$overlay_style = '';
$has_custom_bg = !empty($bg_image);
$has_overlay = !empty($overlay_grad);

if ($has_custom_bg) {
	$extra_class = ' cwc-cta-footer--custom-bg';
	$section_style = sprintf('background:url(%s) center / cover no-repeat !important;', esc_url($bg_image));
}

if ($has_overlay) {
	/*
	 * Template gradients often use opaque hex stops.
	 * We apply the gradient and a slight opacity so the photo remains visible.
	 */
	$overlay_style = sprintf('background:%s !important; opacity: 0.85;', $overlay_grad);
}

$wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'cwc-cta-footer' . $extra_class,
	)
);
?>

<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo $section_style ? 'style="' . esc_attr($section_style) . '"' : ''; ?>>
	<div class="cwc-cta-footer__overlay" aria-hidden="true" <?php echo $overlay_style ? ' style="' . esc_attr($overlay_style) . '"' : ''; ?>></div>
	<div class="cwc-cta-footer__content">
		<?php if ($cta_title || $title_accent): ?>
			<h2 class="cwc-cta-footer__title">
				<?php if ($accent_first && $title_accent): ?>
					<em class="cwc-cta-footer__title-accent"><?php echo esc_html($title_accent); ?></em>
				<?php endif; ?>

				<?php if ($cta_title): ?>
					<span class="cwc-cta-footer__title-main"><?php echo esc_html($cta_title); ?></span>
				<?php endif; ?>

				<?php if (!$accent_first && $title_accent): ?>
					<em class="cwc-cta-footer__title-accent"><?php echo esc_html($title_accent); ?></em>
				<?php endif; ?>
			</h2>
		<?php endif; ?>

		<?php if ($description): ?>
			<p class="cwc-cta-footer__desc"><?php echo esc_html($description); ?></p>
		<?php endif; ?>

		<div class="cwc-cta-footer__actions">
			<?php if ($button_label): ?>
				<a href="<?php echo esc_url($button_url); ?>" class="cwc-cta-footer__btn">
					<?php echo esc_html($button_label); ?>
				</a>
			<?php endif; ?>

			<?php if ($sec_btn_label): ?>
				<a href="<?php echo esc_url($sec_btn_url); ?>" class="cwc-cta-footer__btn cwc-cta-footer__btn--outline">
					<?php echo esc_html($sec_btn_label); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>