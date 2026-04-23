<?php
/**
 * Coaching Section — Two-column: image + text/cards.
 *
 * Reusable for coaching/experience pages.
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title_start  = $attributes['titleStart'] ?? '';
$title_accent = $attributes['titleAccent'] ?? '';
$description  = $attributes['description'] ?? '';
$sub_heading  = $attributes['subHeading'] ?? '';
$image1 = isset($attributes['image1']) ? trim((string) $attributes['image1']) : '';
$image2 = isset($attributes['image2']) ? trim((string) $attributes['image2']) : '';
$cards = isset($attributes['cards']) && is_array($attributes['cards']) ? $attributes['cards'] : [];
$reversed = isset($attributes['reversed']) ? (bool) $attributes['reversed'] : false;

$wrapper_attrs = get_block_wrapper_attributes([
	'class' => 'cwc-coaching' . ($reversed ? ' cwc-coaching--reversed' : ''),
]);
?>

<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="cwc-coaching__inner">

		<!-- Image column -->
		<div class="cwc-coaching__media">
			<?php if ($image1 !== ''): ?>
				<img class="cwc-coaching__img cwc-coaching__img--1" src="<?php echo esc_url($image1); ?>" alt="<?php echo esc_attr($title_start . ' ' . $title_accent); ?>" loading="lazy">
			<?php endif; ?>
			
			<?php if ($image2 !== ''): ?>
				<img class="cwc-coaching__img cwc-coaching__img--2" src="<?php echo esc_url($image2); ?>" alt="<?php echo esc_attr($title_start . ' ' . $title_accent); ?>" loading="lazy">
			<?php elseif ($image1 === ''): ?>
				<div class="cwc-coaching__image-placeholder"></div>
			<?php endif; ?>
		</div>

		<!-- Content column -->
		<div class="cwc-coaching__content">
			<?php if ( '' !== $title_start || '' !== $title_accent ) : ?>
				<h2 class="cwc-coaching__title">
					<?php if ( '' !== $title_accent ) : ?>
						<em class="cwc-coaching__accent"><?php echo esc_html( $title_accent ); ?></em>
					<?php endif; ?>
					<?php if ( '' !== $title_start ) : ?>
						<span class="cwc-coaching__title-main"><?php echo esc_html( $title_start ); ?></span>
					<?php endif; ?>
				</h2>
			<?php endif; ?>

			<?php if ( '' !== $description ) : ?>
				<p class="cwc-coaching__desc"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== $sub_heading ) : ?>
				<h3 class="cwc-coaching__subheading"><?php echo esc_html( $sub_heading ); ?></h3>
			<?php endif; ?>

			<?php if ( ! empty( $cards ) ) : ?>
				<div class="cwc-coaching__cards">
					<?php foreach ( $cards as $card ) :
						$card_title = $card['title'] ?? '';
						$card_desc  = $card['description'] ?? '';
					?>
						<div class="cwc-coaching__card">
							<?php if ( '' !== $card_title ) : ?>
								<h4 class="cwc-coaching__card-title"><?php echo esc_html( $card_title ); ?></h4>
							<?php endif; ?>
							<?php if ( '' !== $card_desc ) : ?>
								<p class="cwc-coaching__card-desc"><?php echo esc_html( $card_desc ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

	</div>
</section>
