<?php
/**
 * About — Home of World Champions block.
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$images = [
	'/wp-content/uploads/2026/04/champion-1.webp',
	'/wp-content/uploads/2026/04/champion-2.webp',
	'/wp-content/uploads/2026/04/champion-3.webp',
	'/wp-content/uploads/2026/04/champion-4.webp',
	'/wp-content/uploads/2026/04/champion-5.webp',
];

$phrases = [
	'WWA World Series',
	'WWA Wake Park World Championships',
	'Asian Wakeboard Championships',
	'Philippine Wakeboard Nationals',
];

$wrapper = get_block_wrapper_attributes( [ 'class' => 'cwc-champions' ] );
?>

<section <?php echo $wrapper; ?> data-cwc-champions>
	<header class="cwc-champions__header">
		<h2 class="cwc-champions__title">
			HOME of WORLD <span class="cwc-champions__accent">CHAMPIONS</span>
		</h2>
		<p class="cwc-champions__desc">
			Official host of WWA World Series and the preferred training ground for the planet's most elite watersports athletes.
		</p>
	</header>

	<div class="cwc-champions__carousel" data-cwc-carousel>
		<div class="cwc-champions__track">
			<?php foreach ( $images as $idx => $src ) : ?>
				<div class="cwc-champions__slide" data-index="<?php echo $idx; ?>">
					<img src="<?php echo esc_url( $src ); ?>" alt="CWC Champion <?php echo $idx + 1; ?>" loading="lazy">
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="cwc-champions__phrase-overlay" aria-live="polite">
		<?php foreach ( $phrases as $p ) : ?>
			<span class="cwc-champions__phrase" data-cwc-phrase><?php echo esc_html( $p ); ?></span>
		<?php endforeach; ?>
	</div>
</section>
