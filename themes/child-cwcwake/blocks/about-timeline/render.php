<?php
/**
 * About — Legacy Timeline block.
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$milestones = array(
	array(
		'year'  => '2006',
		'title' => 'Official Opening',
		'desc'  => "CWC was inaugurated in 2006 under then-governor Luis Villafuerte.\n• The goal: establish Camarines Sur into global watersports destination.\n• Utilized an advanced European cable-pull wakeboarding system, making it one of the first in Asia to feature one.",
		'image' => '/wp-content/uploads/2026/04/official-opening-2006.webp',
	),
	array(
		'year'  => "2010's",
		'title' => 'International Recognition',
		'desc'  => "CWC hosted international wakeboarding competitions, including:\n• World Wakeboard Association (WWA) events\n• Asian wakeboard championships\n• Accommodations expansion: villas, container rooms\n• Amenities: lifestyle destination — nature & sports park",
		'image' => '/wp-content/uploads/2026/04/international-recognition-2010.webp',
	),
	array(
		'year'  => '2023–Present',
		'title' => '',
		'desc'  => "CWC remains a premier watersports destination in the Philippines with:\n• Open to all skill levels\n• Accessible to families and locals\n• Affordable options\n• Continues to attract both tourists and casual visitors, helping boost tourism in Camarines Sur.",
		'image' => '/wp-content/uploads/2026/04/present-2023.webp',
	),
);

$wrapper = get_block_wrapper_attributes( array( 'class' => 'cwc-timeline' ) );
?>

<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<header class="cwc-timeline__header">
		<h2 class="cwc-timeline__title">
			<span class="cwc-timeline__accent">LEGACY</span> of FIRSTS
		</h2>
		<p class="cwc-timeline__desc">
			From 2006 to today, see how we transformed Camarines Sur into the wakeboarding capital of the Philippines and beyond.
		</p>
	</header>

	<div class="cwc-timeline__track">
		<div class="cwc-timeline__line" aria-hidden="true"></div>

		<?php
		foreach ( $milestones as $i => $milestone ) :
			$even  = 0 === $i % 2;
			$align = $even ? 'left' : 'right';
			?>
			<div class="cwc-timeline__row cwc-timeline__row--<?php echo esc_attr( $align ); ?>">
				<div class="cwc-timeline__dot" aria-hidden="true"></div>

				<div class="cwc-timeline__card">
					<div class="cwc-timeline__card-bar" aria-hidden="true"></div>
					<span class="cwc-timeline__year"><?php echo esc_html( $milestone['year'] ); ?></span>
					<?php if ( ! empty( $milestone['title'] ) ) : ?>
						<span class="cwc-timeline__milestone"> – <?php echo esc_html( $milestone['title'] ); ?></span>
					<?php endif; ?>
					<div class="cwc-timeline__card-body">
						<?php echo nl2br( esc_html( $milestone['desc'] ) ); ?>
					</div>
				</div>

				<div class="cwc-timeline__image">
					<img src="<?php echo esc_url( $milestone['image'] ); ?>" alt="<?php echo esc_attr( $milestone['year'] . ' ' . $milestone['title'] ); ?>" loading="lazy">
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
