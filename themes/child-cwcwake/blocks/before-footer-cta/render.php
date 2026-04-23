<?php
/**
 * Before Footer CTA — "Start Your Own Story".
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$title        = $attributes['title'] ?? 'START YOUR OWN';
$title_accent = $attributes['titleAccent'] ?? 'STORY';
$accent_first = $attributes['accentFirst'] ?? false;
$description  = $attributes['description'] ?? "Whether you're a first-timer or a pro, the water is waiting. Plan your visit to the world's premier cable park today.";
$button_label = $attributes['buttonLabel'] ?? 'BOOK NOW';
$button_url   = $attributes['buttonUrl'] ?? '#book';

$wrapper = get_block_wrapper_attributes( [ 'class' => 'cwc-cta-footer' ] );
?>

<section <?php echo $wrapper; ?>>
	<div class="cwc-cta-footer__overlay" aria-hidden="true"></div>
	<div class="cwc-cta-footer__content">
		<?php if ( $title || $title_accent ) : ?>
			<h2 class="cwc-cta-footer__title">
				<?php if ( $accent_first && $title_accent ) : ?>
					<em class="cwc-cta-footer__title-accent"><?php echo esc_html( $title_accent ); ?></em> 
				<?php endif; ?>

				<?php if ( $title ) : ?>
					<span class="cwc-cta-footer__title-main"><?php echo esc_html( $title ); ?></span>
				<?php endif; ?>

				<?php if ( ! $accent_first && $title_accent ) : ?>
					 <em class="cwc-cta-footer__title-accent"><?php echo esc_html( $title_accent ); ?></em>
				<?php endif; ?>
			</h2>
		<?php endif; ?>
		
		<?php if ( $description ) : ?>
			<p class="cwc-cta-footer__desc"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
		
		<?php if ( $button_label ) : ?>
			<a href="<?php echo esc_url( $button_url ); ?>" class="cwc-cta-footer__btn">
				<?php echo esc_html( $button_label ); ?>
			</a>
		<?php endif; ?>
	</div>
</section>
