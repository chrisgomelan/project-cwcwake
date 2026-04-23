<?php
/**
 * About — Certified Safe / Rental Equipment.
 *
 * Modified to be fully reusable via attributes.
 *
 * @package CWC_Wake
 */

if (!defined('ABSPATH'))
	exit;

$theme_uri = get_stylesheet_directory_uri();

$title_l1     = $attributes['titleLine1'] ?? 'CERTIFIED';
$title_a1     = $attributes['titleAccent1'] ?? 'SAFE';
$title_l2     = $attributes['titleLine2'] ?? 'BUILT for';
$title_a2     = $attributes['titleAccent2'] ?? 'PERFORMANCE';
$description  = $attributes['description'] ?? '';
$variant      = $attributes['variant'] ?? 'cards';
$items        = $attributes['items'] ?? [];

// Default cards for About page if no items provided
if ( empty( $items ) && 'cards' === $variant ) {
	$items = [
		[
			'icon' => 'camsur-pass-protocol.svg',
			'title' => 'CamSurpass Protocol',
			'desc' => 'A comprehensive safety and sanitary protocol developed by the provincial government, ensuring all leisure facilities meet the highest standards of cleanliness and risk prevention.',
		],
		[
			'icon' => 'certified-coaching.svg',
			'title' => 'Certified Coaching',
			'desc' => 'All instructors are professionally certified in cable wakeboarding instruction, water rescue, and first aid — providing safe, guided experiences for every skill level.',
		],
		[
			'icon' => 'precise-maintain.svg',
			'title' => 'Precise Maintain',
			'desc' => 'All wakeboarding systems, cables, pulleys, and safety gear undergo scheduled daily and monthly inspections following European cable park standards.',
		],
	];
}

$wrapper = get_block_wrapper_attributes(['class' => 'cwc-certified cwc-certified--' . $variant]);
?>

<section <?php echo $wrapper; ?>>
	<div class="cwc-certified__inner">
		<header class="cwc-certified__header">
			<h2 class="cwc-certified__title">
				<?php echo esc_html( $title_l1 ); ?> <span class="cwc-certified__accent"><?php echo esc_html( $title_a1 ); ?></span>.<br>
				<?php echo esc_html( $title_l2 ); ?> <span class="cwc-certified__accent"><?php echo esc_html( $title_a2 ); ?></span>.
			</h2>
			<?php if ( '' !== $description ) : ?>
				<p class="cwc-certified__desc"><?php echo esc_html( $description ); ?></p>
			<?php else : ?>
				<p class="cwc-certified__desc">
					We uphold the CAMSURPASS standards, featuring certified instructors and regularly inspected
					infrastructure for maximum safety.
				</p>
			<?php endif; ?>
		</header>

		<?php if ( 'cards' === $variant ) : ?>
			<div class="cwc-certified__grid">
				<?php foreach ($items as $card): ?>
					<div class="cwc-certified__card">
						<div class="cwc-certified__icon-wrap">
							<?php 
								$icon_url = (strpos($card['icon'], 'http') === 0) ? $card['icon'] : ($theme_uri . '/assets/images/' . $card['icon']);
							?>
							<img src="<?php echo esc_url($icon_url); ?>" alt=""
								loading="lazy" aria-hidden="true">
						</div>
						<h3 class="cwc-certified__card-title"><?php echo esc_html($card['title']); ?></h3>
						<p class="cwc-certified__card-desc"><?php echo esc_html($card['desc']); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<!-- Rental variant: 3-image staggered layout -->
			<div class="cwc-certified__images">
				<?php foreach ( $items as $idx => $img_url ) : ?>
					<div class="cwc-certified__img-wrap cwc-certified__img-wrap--<?php echo ($idx + 1); ?>">
						<img src="<?php echo esc_url( $img_url ); ?>" alt="" loading="lazy">
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>