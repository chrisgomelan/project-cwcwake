<?php
/**
 * Before Footer CTA — "Start Your Own Story".
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$wrapper = get_block_wrapper_attributes( [ 'class' => 'cwc-cta-footer' ] );
?>

<section <?php echo $wrapper; ?>>
	<div class="cwc-cta-footer__overlay" aria-hidden="true"></div>
	<div class="cwc-cta-footer__content">
		<h2 class="cwc-cta-footer__title">START YOUR OWN STORY</h2>
		<p class="cwc-cta-footer__desc">
			Whether you're a first-timer or a pro, the water is waiting. Plan your visit to the world's premier cable park today.
		</p>
		<a href="#book" class="cwc-cta-footer__btn">BOOK NOW</a>
	</div>
</section>
