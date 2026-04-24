<?php
/**
 * Land Feature Split — Two-column: text + image.
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme_uri = get_stylesheet_directory_uri();

$title_start  = $attributes['titleStart'] ?? 'PROFESSIONAL';
$title_accent = $attributes['titleAccent'] ?? 'SKATE';
$title_end    = $attributes['titleEnd'] ?? '& BMX';
$description  = $attributes['description'] ?? '';
$icon_path    = $attributes['iconPath'] ?? '/assets/images/skate-park.svg';
$image        = $attributes['image'] ?? '';
$reversed     = ! empty( $attributes['reversed'] );

$modifier = $reversed ? ' cwc-land-feature-split--reversed' : '';

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'class' => 'cwc-land-feature-split' . $modifier,
	)
);
?>

<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="cwc-land-feature-split__inner">

		<!-- Text column -->
		<div class="cwc-land-feature-split__text">
			<div class="cwc-land-feature-split__text-bg" style="background-image: url('/wp-content/uploads/2026/04/doodle-bg.webp');"></div>
			<div class="cwc-land-feature-split__text-content">
				<h2 class="cwc-land-feature-split__title">
					<?php echo esc_html( $title_start ); ?>
					<em class="cwc-land-feature-split__accent"><?php echo esc_html( $title_accent ); ?></em>
					<?php echo esc_html( $title_end ); ?>
				</h2>

				<?php if ( '' !== $description ) : ?>
					<p class="cwc-land-feature-split__desc"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>

				<?php if ( '' !== $icon_path ) : ?>
					<img
						class="cwc-land-feature-split__icon"
						src="<?php echo esc_url( $theme_uri . $icon_path ); ?>"
						alt=""
						width="64"
						height="64"
						loading="lazy"
						aria-hidden="true"
					/>
				<?php endif; ?>
			</div>
		</div>

		<!-- Image column -->
		<div class="cwc-land-feature-split__image-col">
			<?php if ( '' !== $image ) : ?>
				<img
					class="cwc-land-feature-split__img"
					src="<?php echo esc_url( $image ); ?>"
					alt=""
					loading="lazy"
					decoding="async"
				/>
			<?php endif; ?>
		</div>

	</div>
</section>
