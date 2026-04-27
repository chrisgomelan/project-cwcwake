<?php
/**
 * Before Footer CTA — "Start Your Own Story" / custom variant.
 *
 * Supports an optional second button, custom background image,
 * and custom overlay gradient for per-page variations.
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Debug: output attributes as a comment for verification
echo '<!-- Block Attributes: ' . esc_html( json_encode( $attributes ) ) . ' -->';

$cta_title     = $attributes['title'] ?? 'START YOUR OWN';
$title_accent  = $attributes['titleAccent'] ?? 'STORY';
$accent_first  = $attributes['accentFirst'] ?? false;
$description   = $attributes['description'] ?? "Whether you're a first-timer or a pro, the water is waiting. Plan your visit to the world's premier cable park today.";
$button_label  = $attributes['buttonLabel'] ?? 'BOOK NOW';
$button_url    = $attributes['buttonUrl'] ?? '#book';
$sec_btn_label = $attributes['secondaryBtnLabel'] ?? '';
$sec_btn_url   = $attributes['secondaryBtnUrl'] ?? '';
$bg_image      = $attributes['backgroundImage'] ?? '';
$overlay_grad  = $attributes['overlayGradient'] ?? '';

/* Inline styles for custom background + overlay. */
$inline_bg   = '';
$inline_over = '';
$extra_class = '';

if ( ! empty( $bg_image ) ) {
	$inline_bg   = sprintf( 'background:url(%s) center / cover no-repeat !important;', esc_url( $bg_image ) );
	$extra_class = ' cwc-cta-footer--custom-bg';
}

if ( ! empty( $overlay_grad ) ) {
	$inline_over = sprintf( 'background:%s !important;', esc_attr( $overlay_grad ) );
}

$wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'cwc-cta-footer' . $extra_class,
	)
);
?>

<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo $inline_bg ? 'style="' . esc_attr( $inline_bg ) . '"' : ''; ?>>
	<div class="cwc-cta-footer__overlay" aria-hidden="true"
	<?php
	if ( $inline_over ) {
		echo ' style="' . esc_attr( $inline_over ) . '"'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>
	></div>
	<div class="cwc-cta-footer__content">
		<?php if ( $cta_title || $title_accent ) : ?>
			<h2 class="cwc-cta-footer__title">
				<?php if ( $accent_first && $title_accent ) : ?>
					<em class="cwc-cta-footer__title-accent"><?php echo esc_html( $title_accent ); ?></em> 
				<?php endif; ?>

				<?php if ( $cta_title ) : ?>
					<span class="cwc-cta-footer__title-main"><?php echo esc_html( $cta_title ); ?></span>
				<?php endif; ?>

				<?php if ( ! $accent_first && $title_accent ) : ?>
					<em class="cwc-cta-footer__title-accent"><?php echo esc_html( $title_accent ); ?></em>
				<?php endif; ?>
			</h2>
		<?php endif; ?>
		
		<?php if ( $description ) : ?>
			<p class="cwc-cta-footer__desc"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
		
		<div class="cwc-cta-footer__actions">
			<?php if ( $button_label ) : ?>
				<a href="<?php echo esc_url( $button_url ); ?>" class="cwc-cta-footer__btn">
					<?php echo esc_html( $button_label ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $sec_btn_label ) : ?>
				<a href="<?php echo esc_url( $sec_btn_url ); ?>" class="cwc-cta-footer__btn cwc-cta-footer__btn--outline">
					<?php echo esc_html( $sec_btn_label ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>
