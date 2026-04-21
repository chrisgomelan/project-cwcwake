<?php
/**
 * Render template for the cwc/room-info block.
 *
 * Renders the main room detail card: blue accent bar, room title,
 * description, amenity chip cloud, inline pricing/booking box, and
 * a tabular policies list. Layout matches the Figma room landing
 * spec; see designs/room-landing-page-design.md for the source of truth.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner block markup (unused).
 * @var WP_Block $block      Block instance.
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if (!defined('ABSPATH')) {
	exit;
}

$title = isset($attributes['title']) ? (string) $attributes['title'] : '';
$description_label = isset($attributes['descriptionLabel']) ? (string) $attributes['descriptionLabel'] : '';
$description = isset($attributes['description']) ? (string) $attributes['description'] : '';
$amenities_label = isset($attributes['amenitiesLabel']) ? (string) $attributes['amenitiesLabel'] : '';
$amenities = isset($attributes['amenities']) && is_array($attributes['amenities']) ? $attributes['amenities'] : [];
$price = isset($attributes['price']) ? (string) $attributes['price'] : '';
$price_sub_label = isset($attributes['priceSubLabel']) ? (string) $attributes['priceSubLabel'] : '';
$book_button_label = isset($attributes['bookButtonLabel']) ? (string) $attributes['bookButtonLabel'] : '';
$book_button_url = isset($attributes['bookButtonUrl']) ? (string) $attributes['bookButtonUrl'] : '';
$policies_label = isset($attributes['policiesLabel']) ? (string) $attributes['policiesLabel'] : '';
$policies_intro = isset($attributes['policiesIntro']) ? (string) $attributes['policiesIntro'] : '';
$policies = isset($attributes['policies']) && is_array($attributes['policies']) ? $attributes['policies'] : [];

if ($title === '' && $description === '' && empty($amenities) && empty($policies)) {
	return;
}

$wrapper_attrs = get_block_wrapper_attributes(['class' => 'cwc-room-info']);

/**
 * Build an `<img>` tag for an amenity / policy icon.
 *
 * Maps short slugs to the bundled SVG files in `assets/images/`. The
 * SVG assets ship with their own brand color baked in, so we avoid the
 * extra cost of loading them inline. Returns an empty string for an
 * unknown slug so the editor cannot break layout by typing a typo.
 *
 * @since 1.0.0
 *
 * @param string $slug Icon slug (e.g. `wifi`, `check-in`, `children`).
 * @return string Image tag markup, or empty string when the slug is unknown.
 */
$icon = static function (string $slug): string {
	static $map = [
	'wifi' => 'free-wifi.svg',
	'parking' => 'free-parking.svg',
	'pool' => 'swimming-pool.svg',
	'air' => 'air-conditioning.svg',
	'garden' => 'garden&terrace.svg',
	'bar' => 'bar.svg',
	'coffee' => 'coffee-shop.svg',
	'smoke-free' => 'smoke-free.svg',
	'smoking' => 'no-smoking.svg',
	'check-in' => 'check-in.svg',
	'check-out' => 'checkout.svg',
	'breakfast' => 'breakfast.svg',
	'reception' => 'reception-hours.svg',
	'children' => 'children-beds.svg',
	'no-age' => 'age-restriction.svg',
	];

	if (!isset($map[$slug])) {
		return '';
	}

	/*
	 * `rawurlencode` keeps filesystem-safe characters intact while
	 * escaping the `&` in `garden&terrace.svg` so it survives the
	 * trip from HTML attribute → HTTP request without being parsed
	 * as a separate query string segment.
	 */
	$src = get_stylesheet_directory_uri() . '/assets/images/' . rawurlencode($map[$slug]);

	return sprintf(
		'<img class="cwc-room-info__icon" src="%s" alt="" width="20" height="20" loading="lazy" decoding="async" aria-hidden="true" />',
		esc_url($src)
	);
};
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="cwc-room-info__panel">
		<span class="cwc-room-info__accent" aria-hidden="true"></span>

		<div class="cwc-room-info__body">
			<?php if ($title !== ''): ?>
				<h2 class="cwc-room-info__title"><?php echo esc_html($title); ?></h2>
			<?php endif; ?>

			<div class="cwc-room-info__layout">
				<div class="cwc-room-info__main">
					<?php if ($description !== ''): ?>
						<div class="cwc-room-info__section">
							<?php if ($description_label !== ''): ?>
								<h3 class="cwc-room-info__label"><?php echo esc_html($description_label); ?></h3>
							<?php endif; ?>
							<p class="cwc-room-info__description"><?php echo esc_html($description); ?></p>
						</div>
					<?php endif; ?>

					<?php if (!empty($amenities)): ?>
						<div class="cwc-room-info__section">
							<?php if ($amenities_label !== ''): ?>
								<h3 class="cwc-room-info__label"><?php echo esc_html($amenities_label); ?></h3>
							<?php endif; ?>
							<ul class="cwc-room-info__amenities">
								<?php
								foreach ($amenities as $amenity) {
									if (!is_array($amenity)) {
										$amenity = ['label' => (string) $amenity];
									}
									$label = isset($amenity['label']) ? (string) $amenity['label'] : '';
									$icon_key = isset($amenity['icon']) ? (string) $amenity['icon'] : '';
									if ($label === '') {
										continue;
									}
									?>
									<li class="cwc-room-info__chip">
										<?php
										if ($icon_key !== '') {
											echo $icon($icon_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										}
										?>
										<span><?php echo esc_html($label); ?></span>
									</li>
									<?php
								}
								?>
							</ul>
						</div>
					<?php endif; ?>
				</div>

				<?php if ($price !== '' || $book_button_label !== ''): ?>
					<aside class="cwc-room-info__pricing">
						<?php if ($price !== ''): ?>
							<p class="cwc-room-info__price"><?php echo esc_html($price); ?></p>
						<?php endif; ?>
						<?php if ($price_sub_label !== ''): ?>
							<p class="cwc-room-info__price-sub"><?php echo esc_html($price_sub_label); ?></p>
						<?php endif; ?>
						<?php if ($book_button_label !== ''): ?>
							<a class="cwc-room-info__book-button"
								href="<?php echo esc_url($book_button_url !== '' ? $book_button_url : '#book'); ?>">
								<?php echo esc_html($book_button_label); ?>
							</a>
						<?php endif; ?>
					</aside>
				<?php endif; ?>
			</div>

			<?php if (!empty($policies)): ?>
				<div class="cwc-room-info__section cwc-room-info__section--policies">
					<?php if ($policies_label !== ''): ?>
						<h3 class="cwc-room-info__label"><?php echo esc_html($policies_label); ?></h3>
					<?php endif; ?>
					<?php if ($policies_intro !== ''): ?>
						<p class="cwc-room-info__policies-intro"><?php echo esc_html($policies_intro); ?></p>
					<?php endif; ?>
					<div class="cwc-room-info__policies" role="table">
						<?php
						foreach ($policies as $policy) {
							if (!is_array($policy)) {
								continue;
							}
							$name = isset($policy['name']) ? (string) $policy['name'] : '';
							$desc = isset($policy['description']) ? (string) $policy['description'] : '';
							$icon_key = isset($policy['icon']) ? (string) $policy['icon'] : '';
							if ($name === '' && $desc === '') {
								continue;
							}
							?>
							<div class="cwc-room-info__policy" role="row">
								<div class="cwc-room-info__policy-name" role="cell">
									<?php
									if ($icon_key !== '') {
										echo $icon($icon_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									}
									?>
									<span><?php echo esc_html($name); ?></span>
								</div>
								<div class="cwc-room-info__policy-desc" role="cell">
									<?php echo wp_kses_post(wpautop($desc)); ?>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>