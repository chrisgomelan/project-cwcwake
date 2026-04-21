<?php
if (!defined('ABSPATH')) {
	exit;
}

$heading = $attributes['heading'] ?? 'STAY With Us';
$subtitle = $attributes['subtitle'] ?? '';
$cta_label = $attributes['ctaLabel'] ?? 'View Accommodations';
$cta_url = $attributes['ctaUrl'] ?? '/accommodations/';
$bg_image = $attributes['backgroundImage'] ?? '';
$items = $attributes['items'] ?? [];

if (empty($items)) {
	$items = [
		['title' => 'Villas', 'image' => '', 'price' => 'PHP 19,500/ night', 'capacity' => 'Maximum 4 persons', 'buttonLabel' => 'View Details', 'buttonUrl' => '/accommodations/villas/'],
		['title' => 'Cabin', 'image' => '', 'price' => 'PHP 8,500/ night', 'capacity' => 'Maximum 2 persons', 'buttonLabel' => 'View Details', 'buttonUrl' => '/accommodations/cabin/'],
		['title' => 'Dwell', 'image' => '', 'price' => 'PHP 7,500/ night', 'capacity' => 'Maximum 2 persons', 'buttonLabel' => 'View Details', 'buttonUrl' => '/accommodations/dwell/'],
		['title' => 'Cabana', 'image' => '', 'price' => 'PHP 6,500/ night', 'capacity' => 'Maximum 2 persons', 'buttonLabel' => 'View Details', 'buttonUrl' => '/accommodations/cabana/'],
	];
}

$bg_style = '';
if (!empty($bg_image)) {
	$bg_style = sprintf('background-image:url(%s);', esc_url($bg_image));
}

$wrapper_attrs = get_block_wrapper_attributes([
	'class' => 'cwc-accommodations',
	'style' => $bg_style,
]);
?>

<section <?php echo $wrapper_attrs; ?>>
	<div class="cwc-accommodations__inner">

		<!-- Header row -->
		<div class="cwc-accommodations__header">
			<div class="cwc-accommodations__header-text">
				<h2 class="cwc-accommodations__heading"><?php echo esc_html($heading); ?></h2>

				<?php if (!empty($subtitle)): ?>
					<p class="cwc-accommodations__subtitle"><?php echo esc_html($subtitle); ?></p>
				<?php endif; ?>
			</div>

			<?php if (!empty($cta_label)): ?>
				<a href="<?php echo esc_url($cta_url); ?>" class="cwc-accommodations__cta cwc-btn-arrow">
					<?php echo esc_html($cta_label); ?>
					<img src="/wp-content/uploads/2026/04/arrow-up.svg" alt="" width="20" height="20"
						class="cwc-accommodations__cta-icon">
				</a>
			<?php endif; ?>
		</div>

		<!-- Cards grid -->
		<div class="cwc-accommodations__grid">
			<?php foreach ($items as $index => $item):
				$title = $item['title'] ?? '';
				$image = $item['image'] ?? '';
				$price = $item['price'] ?? '';
				$capacity = $item['capacity'] ?? '';
				$btn_label = $item['buttonLabel'] ?? 'View Details';
				$btn_url = $item['buttonUrl'] ?? '#';
				$stagger = ($index % 2 === 0) ? 'cwc-accommodations__card--even' : 'cwc-accommodations__card--odd';
				?>
				<div class="cwc-accommodations__card <?php echo esc_attr($stagger); ?>">
					<?php if (!empty($image)): ?>
						<div class="cwc-accommodations__card-img">
							<img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
						</div>
					<?php else: ?>
						<div class="cwc-accommodations__card-img cwc-accommodations__card-img--empty"></div>
					<?php endif; ?>

					<div class="cwc-accommodations__card-body">
						<?php if (!empty($title)): ?>
							<h3 class="cwc-accommodations__card-title"><?php echo esc_html($title); ?></h3>
						<?php endif; ?>

						<?php if (!empty($price)): ?>
							<p class="cwc-accommodations__card-price"><?php echo esc_html($price); ?></p>
						<?php endif; ?>

						<?php if (!empty($capacity)): ?>
							<p class="cwc-accommodations__card-capacity">
								<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
									fill="currentColor">
									<path
										d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
								</svg>
								<?php echo esc_html($capacity); ?>
							</p>
						<?php endif; ?>

						<a href="<?php echo esc_url($btn_url); ?>" class="cwc-accommodations__card-btn">
							<?php echo esc_html($btn_label); ?>
						</a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>