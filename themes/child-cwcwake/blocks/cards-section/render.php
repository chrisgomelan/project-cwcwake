<?php
/**
 * Render template for the cwc/cards-section block.
 *
 * Renders an italic two-tone section heading followed by a 12-column grid
 * of image cards. Two variants are supported:
 *   - detailed: full-bleed image with a dark gradient panel containing
 *               title, price, capacity and a CTA button (Rooms section).
 *   - overlay:  full-bleed image with a 30% dim overlay and a single
 *               overlaid title (Leisure Services section).
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner block markup (unused).
 * @var WP_Block $block      Block instance.
 *
 * @package ChildCwcwake
 */

if (!defined('ABSPATH')) {
	exit;
}

$variant = (isset($attributes['variant']) && in_array($attributes['variant'], ['overlay', 'static'])) ? $attributes['variant'] : 'detailed';
$heading_primary = isset($attributes['headingPrimary']) ? trim((string) $attributes['headingPrimary']) : '';
$heading_secondary = isset($attributes['headingSecondary']) ? trim((string) $attributes['headingSecondary']) : '';
$section_description = isset($attributes['sectionDescription']) ? trim((string) $attributes['sectionDescription']) : '';
$items = isset($attributes['items']) && is_array($attributes['items']) ? $attributes['items'] : [];

if (empty($items)) {
	return;
}

$variant_class = 'overlay' === $variant || 'static' === $variant ? 'overlay' : 'detailed';
$static_class = 'static' === $variant ? ' cwc-cards-section--static' : '';

$wrapper_attrs = get_block_wrapper_attributes([
	'class' => 'cwc-cards-section cwc-cards-section--' . $variant_class . $static_class,
]);

$person_icon = '<svg class="cwc-cards-section__icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>';
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ($heading_primary !== '' || $heading_secondary !== '' || $section_description !== ''): ?>
		<div class="cwc-cards-section__heading-wrap">
			<?php if ($heading_primary !== '' || $heading_secondary !== ''): ?>
				<h2 class="cwc-cards-section__heading">
					<?php if ($heading_primary !== ''): ?>
						<span class="cwc-cards-section__heading-primary"><?php echo esc_html($heading_primary); ?></span>
					<?php endif; ?>
					<?php if ($heading_secondary !== ''): ?>
						<span class="cwc-cards-section__heading-secondary"> <?php echo esc_html($heading_secondary); ?></span>
					<?php endif; ?>
				</h2>
			<?php endif; ?>
			
			<?php if ($section_description !== ''): ?>
				<p class="cwc-cards-section__section-desc"><?php echo esc_html($section_description); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<ul class="cwc-cards-section__list">
		<?php
		foreach ($items as $item) {
			if (!is_array($item)) {
				continue;
			}

			$title = isset($item['title']) ? (string) $item['title'] : '';
			$image = isset($item['image']) ? (string) $item['image'] : '';
			$default_span = ($variant === 'detailed') ? 3 : 6;
			$span_raw = isset($item['span']) ? (int) $item['span'] : $default_span;
			$span = max(1, min(12, $span_raw));
			$url = isset($item['url']) ? (string) $item['url'] : '';
			$price = isset($item['price']) ? (string) $item['price'] : '';
			$capacity = isset($item['capacity']) ? (string) $item['capacity'] : '';
			$btn_label = isset($item['buttonLabel']) ? (string) $item['buttonLabel'] : '';
			$btn_url = isset($item['buttonUrl']) ? (string) $item['buttonUrl'] : '';

			$description = isset($item['description']) ? (string) $item['description'] : '';
			$item_style = sprintf('--cwc-card-span:%d', $span);
			?>
			<li class="cwc-cards-section__item" style="<?php echo esc_attr($item_style); ?>">
				<?php if ($variant === 'detailed'): ?>
					<article class="cwc-cards-section__card cwc-cards-section__card--detailed">
						<?php if ($image !== ''): ?>
							<div class="cwc-cards-section__card-media" role="img" <?php echo $title !== '' ? ' aria-label="' . esc_attr($title) . '"' : ''; ?> style="background-image:url('<?php echo esc_url($image); ?>');">
							</div>
						<?php endif; ?>
						<div class="cwc-cards-section__card-body">
							<?php if ($title !== ''): ?>
								<h3 class="cwc-cards-section__card-title"><?php echo esc_html($title); ?></h3>
							<?php endif; ?>
							<?php if ($price !== ''): ?>
								<p class="cwc-cards-section__card-price"><?php echo esc_html($price); ?></p>
							<?php endif; ?>
							<?php if ($capacity !== ''): ?>
								<p class="cwc-cards-section__card-capacity">
									<?php echo $person_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<span><?php echo esc_html($capacity); ?></span>
								</p>
							<?php endif; ?>
							<?php
							if ($btn_label !== '') {
								if ($btn_url !== '') {
									printf(
										'<a class="cwc-cards-section__card-button" href="%1$s">%2$s</a>',
										esc_url($btn_url),
										esc_html($btn_label)
									);
								} else {
									printf(
										'<span class="cwc-cards-section__card-button">%s</span>',
										esc_html($btn_label)
									);
								}
							}
							?>
						</div>
					</article>
				<?php else: ?>
					<div class="cwc-cards-section__card cwc-cards-section__card--overlay">
						<?php if ($image !== ''): ?>
							<div class="cwc-cards-section__card-media" role="img" <?php echo $title !== '' ? ' aria-label="' . esc_attr($title) . '"' : ''; ?> style="background-image:url('<?php echo esc_url($image); ?>');">
							</div>
						<?php endif; ?>
						<span class="cwc-cards-section__card-dim" aria-hidden="true"></span>
						<div class="cwc-cards-section__card-content">
							<?php if ($title !== ''): ?>
								<h3 class="cwc-cards-section__card-title"><?php echo esc_html($title); ?></h3>
							<?php endif; ?>
							<?php if ($description !== ''): ?>
								<p class="cwc-cards-section__card-description"><?php echo esc_html($description); ?></p>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			</li>
			<?php
		}
		?>
	</ul>
</section>