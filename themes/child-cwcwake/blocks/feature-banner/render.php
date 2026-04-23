<?php
/**
 * Feature Banner — Full-width image card with overlay + title.
 *
 * Reusable across pages for feature highlights
 * (Inflatable Aqua Adventure, etc.)
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bg_image     = $attributes['backgroundImage'] ?? '';
$title_line1  = $attributes['titleLine1'] ?? '';
$title_accent = $attributes['titleAccent'] ?? '';
$description  = $attributes['description'] ?? '';

$bg_style = '';
if ( '' !== $bg_image ) {
	$bg_style = sprintf( 'background-image:url(%s);', esc_url( $bg_image ) );
}

$wrapper_attrs = get_block_wrapper_attributes( [
	'class' => 'cwc-feature-banner',
	'style' => $bg_style,
] );
?>

<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="cwc-feature-banner__overlay" aria-hidden="true"></div>
	<div class="cwc-feature-banner__content">
		<?php if ( '' !== $title_line1 || '' !== $title_accent ) : ?>
			<h2 class="cwc-feature-banner__title">
				<?php if ( '' !== $title_line1 ) : ?>
					<?php echo esc_html( $title_line1 ); ?>
				<?php endif; ?>
				<?php if ( '' !== $title_accent ) : ?>
					<em class="cwc-feature-banner__accent"><?php echo esc_html( $title_accent ); ?></em>
				<?php endif; ?>
			</h2>
		<?php endif; ?>

		<?php if ( '' !== $description ) : ?>
			<p class="cwc-feature-banner__desc"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	</div>
</section>
