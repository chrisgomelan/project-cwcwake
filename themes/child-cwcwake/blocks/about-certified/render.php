<?php
/**
 * About — Certified Safe / Rental Equipment / Host overlay cards.
 *
 * Modified to be fully reusable via attributes.
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme_uri = get_stylesheet_directory_uri();

$title_l1      = $attributes['titleLine1'] ?? 'CERTIFIED';
$title_a1      = $attributes['titleAccent1'] ?? 'SAFE';
$title_l2      = $attributes['titleLine2'] ?? 'BUILT for';
$title_a2      = $attributes['titleAccent2'] ?? 'PERFORMANCE';
$description   = $attributes['description'] ?? '';
$variant       = $attributes['variant'] ?? 'cards';
$items         = $attributes['items'] ?? array();
$accent_color  = isset( $attributes['accentColor'] ) ? trim( (string) $attributes['accentColor'] ) : '';

// Default cards for About page if no items provided.
if ( empty( $items ) && 'cards' === $variant ) {
	$items = array(
		array(
			'icon'  => 'camsur-pass-protocol.svg',
			'title' => 'CamSurpass Protocol',
			'desc'  => 'A comprehensive safety and sanitary protocol developed by the provincial government, ensuring all leisure facilities meet the highest standards of cleanliness and risk prevention.',
		),
		array(
			'icon'  => 'certified-coaching.svg',
			'title' => 'Certified Coaching',
			'desc'  => 'All instructors are professionally certified in cable wakeboarding instruction, water rescue, and first aid — providing safe, guided experiences for every skill level.',
		),
		array(
			'icon'  => 'precise-maintain.svg',
			'title' => 'Precise Maintain',
			'desc'  => 'All wakeboarding systems, cables, pulleys, and safety gear undergo scheduled daily and monthly inspections following European cable park standards.',
		),
	);
}

$wrapper_style = '';
if ( '' !== $accent_color ) {
	$wrapper_style = '--cwc-certified-accent:' . esc_attr( $accent_color ) . ';';
}

$wrapper_args = array(
	'class' => 'cwc-certified cwc-certified--' . sanitize_html_class( $variant ),
);
if ( '' !== $wrapper_style ) {
	$wrapper_args['style'] = $wrapper_style;
}

$wrapper = get_block_wrapper_attributes( $wrapper_args );
?>

<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="cwc-certified__inner">
		<header class="cwc-certified__header">
			<h2 class="cwc-certified__title">
				<?php echo esc_html( $title_l1 ); ?> <span class="cwc-certified__accent"><?php echo esc_html( $title_a1 ); ?></span>.<?php if ( '' !== $title_l2 || '' !== $title_a2 ) : ?><br>
				<?php echo esc_html( $title_l2 ); ?> <span class="cwc-certified__accent"><?php echo esc_html( $title_a2 ); ?></span>.<?php endif; ?>
			</h2>
			<?php if ( '' !== $description ) : ?>
				<p class="cwc-certified__desc"><?php echo esc_html( $description ); ?></p>
			<?php elseif ( 'overlay-cards' !== $variant ) : ?>
				<p class="cwc-certified__desc">
					We uphold the CAMSURPASS standards, featuring certified instructors and regularly inspected
					infrastructure for maximum safety.
				</p>
			<?php endif; ?>
		</header>

		<?php if ( 'cards' === $variant ) : ?>
			<div class="cwc-certified__grid">
				<?php foreach ( $items as $card ) : ?>
					<?php
					if ( ! is_array( $card ) ) {
						continue;
					}
					?>
					<div class="cwc-certified__card">
						<div class="cwc-certified__icon-wrap">
							<?php
								$icon_url = ( isset( $card['icon'] ) && strpos( $card['icon'], 'http' ) === 0 ) ? $card['icon'] : ( $theme_uri . '/assets/images/' . ( $card['icon'] ?? '' ) );
							?>
							<img src="<?php echo esc_url( $icon_url ); ?>" alt=""
								loading="lazy" aria-hidden="true">
						</div>
						<h3 class="cwc-certified__card-title"><?php echo esc_html( $card['title'] ?? '' ); ?></h3>
						<p class="cwc-certified__card-desc"><?php echo esc_html( $card['desc'] ?? '' ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		<?php elseif ( 'overlay-cards' === $variant ) : ?>
			<div class="cwc-certified__overlay-grid">
				<?php foreach ( $items as $card ) : ?>
					<?php
					if ( ! is_array( $card ) ) {
						continue;
					}
					$c_title = $card['title'] ?? '';
					$c_desc  = $card['description'] ?? ( $card['desc'] ?? '' );
					$c_img   = isset( $card['image'] ) ? trim( (string) $card['image'] ) : '';
					?>
					<div class="cwc-certified__overlay-card">
						<?php if ( '' !== $c_img ) : ?>
							<img class="cwc-certified__overlay-card-img" src="<?php echo esc_url( $c_img ); ?>" alt="" loading="lazy" aria-hidden="true">
						<?php endif; ?>
						<div class="cwc-certified__overlay-card-shade" aria-hidden="true"></div>
						<div class="cwc-certified__overlay-card-text">
							<?php if ( '' !== $c_title ) : ?>
								<h3 class="cwc-certified__overlay-card-title"><?php echo esc_html( $c_title ); ?></h3>
							<?php endif; ?>
							<?php if ( '' !== $c_desc ) : ?>
								<p class="cwc-certified__overlay-card-desc"><?php echo esc_html( $c_desc ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<!-- Rental variant: 3-image staggered layout -->
			<div class="cwc-certified__images">
				<?php foreach ( $items as $idx => $img_url ) : ?>
					<div class="cwc-certified__img-wrap cwc-certified__img-wrap--<?php echo esc_attr( (string) ( $idx + 1 ) ); ?>">
						<img src="<?php echo esc_url( is_string( $img_url ) ? $img_url : '' ); ?>" alt="" loading="lazy">
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
