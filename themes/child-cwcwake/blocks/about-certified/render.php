<?php
/**
 * About — Certified Safe, Built for Performance.
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$theme_uri = get_stylesheet_directory_uri();

$cards = [
	[
		'icon'  => 'camsur-pass-protocol.svg',
		'title' => 'CamSurpass Protocol',
		'desc'  => 'A comprehensive safety and sanitary protocol developed by the provincial government, ensuring all leisure facilities meet the highest standards of cleanliness and risk prevention.',
	],
	[
		'icon'  => 'certified-coaching.svg',
		'title' => 'Certified Coaching',
		'desc'  => 'All instructors are professionally certified in cable wakeboarding instruction, water rescue, and first aid — providing safe, guided experiences for every skill level.',
	],
	[
		'icon'  => 'precise-maintain.svg',
		'title' => 'Precise Maintain',
		'desc'  => 'All wakeboarding systems, cables, pulleys, and safety gear undergo scheduled daily and monthly inspections following European cable park standards.',
	],
];

$wrapper = get_block_wrapper_attributes( [ 'class' => 'cwc-certified' ] );
?>

<section <?php echo $wrapper; ?>>
	<div class="cwc-certified__inner">
		<header class="cwc-certified__header">
			<h2 class="cwc-certified__title">
				CERTIFIED <span class="cwc-certified__accent">SAFE</span>.<br>
				BUILT for <span class="cwc-certified__accent">PERFORMANCE</span>.
			</h2>
			<p class="cwc-certified__desc">
				We uphold the CAMSURPASS standards, featuring certified instructors and regularly inspected infrastructure for maximum safety.
			</p>
		</header>

		<div class="cwc-certified__grid">
			<?php foreach ( $cards as $card ) : ?>
				<div class="cwc-certified__card">
					<div class="cwc-certified__icon-wrap">
						<img src="<?php echo esc_url( $theme_uri . '/assets/images/' . $card['icon'] ); ?>" alt="" loading="lazy" aria-hidden="true">
					</div>
					<h3 class="cwc-certified__card-title"><?php echo esc_html( $card['title'] ); ?></h3>
					<p class="cwc-certified__card-desc"><?php echo esc_html( $card['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
