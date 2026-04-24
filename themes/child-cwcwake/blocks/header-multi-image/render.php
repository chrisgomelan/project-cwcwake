<?php
/**
 * Header Multi Image Block
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title_accent = $attributes['titleAccent'] ?? 'PICKLEBALL';
$title_end    = $attributes['titleEnd'] ?? '& COURT SPORTS';
$description  = $attributes['description'] ?? '';
$image1       = $attributes['image1'] ?? '';
$image2       = $attributes['image2'] ?? '';

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'class' => 'cwc-header-multi-image',
	)
);
?>

<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="cwc-header-multi-image__inner">
		
		<div class="cwc-header-multi-image__header">
			<h2 class="cwc-header-multi-image__title">
				<em class="cwc-header-multi-image__accent"><?php echo esc_html( $title_accent ); ?></em>
				<?php echo esc_html( $title_end ); ?>
			</h2>
			<?php if ( '' !== $description ) : ?>
				<p class="cwc-header-multi-image__desc"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>

		<div class="cwc-header-multi-image__media">
			<?php if ( '' !== $image1 ) : ?>
				<img class="cwc-header-multi-image__img" src="<?php echo esc_url( $image1 ); ?>" alt="" loading="lazy" decoding="async" />
			<?php endif; ?>
			<?php if ( '' !== $image2 ) : ?>
				<img class="cwc-header-multi-image__img" src="<?php echo esc_url( $image2 ); ?>" alt="" loading="lazy" decoding="async" />
			<?php endif; ?>
		</div>

	</div>
</section>
