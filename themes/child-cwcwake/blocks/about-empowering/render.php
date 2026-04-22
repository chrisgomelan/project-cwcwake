<?php
/**
 * About — Empowering the Region.
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$theme_uri = get_stylesheet_directory_uri();

$cards = [
	[
		'icon'  => 'sustainable-tourism.svg',
		'title' => 'Sustainable Tourism',
		'desc'  => 'By promoting eco-friendly practices, maintaining natural green spaces, and supporting sustainable recreational benefit from increased tourism in the region.',
	],
	[
		'icon'  => 'local-employment.svg',
		'title' => 'Local Employment',
		'desc'  => 'The complex provides stable jobs to local residents — from administration and hospitality to instructors and groundskeeping — helping uplift and empower the local community.',
	],
	[
		'icon'  => 'youth-sports-dev.svg',
		'title' => 'Youth & Sports Development',
		'desc'  => "By providing national-standard facilities for aspiring athletes, CWC nurtures young talents and puts Camarines Sur on the map as a competitive sports hub.",
	],
];

$wrapper = get_block_wrapper_attributes( [ 'class' => 'cwc-empower' ] );
?>

<section <?php echo $wrapper; ?>>
	<header class="cwc-empower__header">
		<h2 class="cwc-empower__title">
			<span class="cwc-empower__accent">EMPOWERING</span> the REGION.
		</h2>
		<p class="cwc-empower__desc">
			Learn how CWC supports the Bicolano community through sustainable tourism, local employment, and youth sports development.
		</p>
	</header>

	<div class="cwc-empower__body">
		<!-- Left — image with bracket wrapper (mirrors intro-section pattern) -->
		<div class="cwc-empower__media">
			<div class="cwc-empower__bracket cwc-empower__bracket--tl" aria-hidden="true"></div>
			<div class="cwc-empower__bracket cwc-empower__bracket--br" aria-hidden="true"></div>
			<img src="/wp-content/uploads/2026/04/empowering.webp" alt="Empowering the Region" loading="lazy" class="cwc-empower__image">
		</div>

		<!-- Right — info cards stack -->
		<div class="cwc-empower__cards">
			<?php foreach ( $cards as $card ) : ?>
				<div class="cwc-empower__card">
					<div class="cwc-empower__icon-wrap">
						<img src="<?php echo esc_url( $theme_uri . '/assets/images/' . $card['icon'] ); ?>" alt="" loading="lazy" aria-hidden="true">
					</div>
					<div class="cwc-empower__card-text">
						<h3 class="cwc-empower__card-title"><?php echo esc_html( $card['title'] ); ?></h3>
						<p class="cwc-empower__card-desc"><?php echo esc_html( $card['desc'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
