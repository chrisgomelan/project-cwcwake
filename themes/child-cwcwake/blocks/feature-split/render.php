<?php
/**
 * Feature Split — Two-column: text + overlapping images.
 *
 * Reusable across pages (Water Sports Pro-Level section, etc.)
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme_uri = get_stylesheet_directory_uri();

$title_start     = $attributes['titleStart'] ?? 'PRO-LEVEL 6-';
$title_accent    = $attributes['titleAccent'] ?? 'POINT SYSTEM';
$description     = $attributes['description'] ?? '';
$checklist_items = $attributes['checklistItems'] ?? [];
$image1          = $attributes['image1'] ?? '';
$image2          = $attributes['image2'] ?? '';
$reversed        = ! empty( $attributes['reversed'] );

$modifier = $reversed ? ' cwc-feature-split--reversed' : '';

$wrapper_attrs = get_block_wrapper_attributes( [
	'class' => 'cwc-feature-split' . $modifier,
] );
?>

<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="cwc-feature-split__inner">

		<!-- Text column -->
		<div class="cwc-feature-split__text">
			<h2 class="cwc-feature-split__title">
				<?php echo esc_html( $title_start ); ?>
				<em class="cwc-feature-split__accent"><?php echo esc_html( $title_accent ); ?></em>
			</h2>

			<?php if ( '' !== $description ) : ?>
				<p class="cwc-feature-split__desc"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $checklist_items ) ) : ?>
				<ul class="cwc-feature-split__checklist">
					<?php foreach ( $checklist_items as $item ) : ?>
						<li class="cwc-feature-split__check-item">
							<img
								class="cwc-feature-split__check-icon"
								src="<?php echo esc_url( $theme_uri . '/assets/images/check-mark.svg' ); ?>"
								alt=""
								width="30"
								height="30"
								loading="lazy"
								aria-hidden="true"
							/>
							<span><?php echo esc_html( $item ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<!-- Images column — overlapping composition -->
		<div class="cwc-feature-split__images">
			<?php if ( '' !== $image1 ) : ?>
				<img
					class="cwc-feature-split__img cwc-feature-split__img--1"
					src="<?php echo esc_url( $image1 ); ?>"
					alt=""
					loading="lazy"
					decoding="async"
				/>
			<?php endif; ?>
			<?php if ( '' !== $image2 ) : ?>
				<img
					class="cwc-feature-split__img cwc-feature-split__img--2"
					src="<?php echo esc_url( $image2 ); ?>"
					alt=""
					loading="lazy"
					decoding="async"
				/>
			<?php endif; ?>
		</div>

	</div>
</section>
