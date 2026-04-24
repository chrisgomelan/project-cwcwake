<?php
/**
 * Render template for the cwc/reviews-section block.
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading_start  = $attributes['headingStart'] ?? 'Client';
$heading_emp    = $attributes['headingEmphasis'] ?? 'REVIEWS';
$emphasis_color = $attributes['emphasisColor'] ?? 'primary';
$items          = $attributes['items'] ?? array();

if ( empty( $items ) ) {
	$items = array(
		array(
			'quote'   => 'Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum',
			'name'    => 'First Name, Last Name',
			'country' => 'Country',
			'rating'  => 5,
			'image'   => '',
		),
		array(
			'quote'   => 'An incredible experience! The facilities are top-notch and the staff is amazing.',
			'name'    => 'Jane Doe',
			'country' => 'Australia',
			'rating'  => 5,
			'image'   => '',
		),
		array(
			'quote'   => 'Best wakepark I have ever visited. Will definitely come back!',
			'name'    => 'John Smith',
			'country' => 'USA',
			'rating'  => 5,
			'image'   => '',
		),
	);
}

$total = count( $items );

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'class' => 'cwc-reviews',
	)
);
?>

<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="cwc-reviews__inner">

		<div class="cwc-reviews__slider" data-total="<?php echo esc_attr( $total ); ?>">

			<!-- Slides -->
			<?php
			foreach ( $items as $index => $item ) :
				$quote   = $item['quote'] ?? '';
				$name    = $item['name'] ?? '';
				$country = $item['country'] ?? '';
				$rating  = (int) ( $item['rating'] ?? 5 );
				$image   = $item['image'] ?? '';
				$active  = ( 0 === $index ) ? ' cwc-reviews__slide--active' : '';
				?>
				<div class="cwc-reviews__slide<?php echo esc_attr( $active ); ?>" data-index="<?php echo esc_attr( $index ); ?>">

					<!-- Left: quote -->
					<div class="cwc-reviews__quote-col">
						<h2 class="cwc-reviews__heading">
							<?php echo esc_html( $heading_start ); ?>
							<em style="color:var(--wp--preset--color--<?php echo esc_attr( $emphasis_color ); ?>)">
								<?php echo esc_html( $heading_emp ); ?>
							</em>
						</h2>

						<div class="cwc-reviews__quote-wrap">
							<img class="cwc-reviews__quote-mark" src="/wp-content/uploads/2026/04/quote.svg" alt="" aria-hidden="true">

							<blockquote class="cwc-reviews__quote">
								<?php echo esc_html( $quote ); ?>
							</blockquote>
						</div>

						<div class="cwc-reviews__reviewer">
							<span class="cwc-reviews__reviewer-name">
								<?php echo esc_html( $name ); ?>
								<?php
								if ( ! empty( $country ) ) :
									?>
									- <?php echo esc_html( $country ); ?><?php endif; ?>
							</span>
							<span class="cwc-reviews__stars">
								<?php for ( $i = 0; $i < $rating; $i++ ) : ?>
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="var(--wp--preset--color--primary)"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
								<?php endfor; ?>
							</span>
						</div>
					</div>

					<!-- Right: image + nav arrows -->
					<div class="cwc-reviews__image-col">
						<div class="cwc-reviews__image-wrap">
							<?php if ( ! empty( $image ) ) : ?>
								<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
							<?php else : ?>
								<div class="cwc-reviews__image-placeholder"></div>
							<?php endif; ?>

							<!-- Arrows & Counter -->
							<?php if ( $total > 1 ) : ?>
								<div class="cwc-reviews__nav-wrap">
									<div class="cwc-reviews__arrows">
										<button class="cwc-reviews__arrow cwc-reviews__arrow--next" aria-label="Next review" data-dir="next">
											<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
										</button>
										<button class="cwc-reviews__arrow cwc-reviews__arrow--prev" aria-label="Previous review" data-dir="prev">
											<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
										</button>
									</div>
									<div class="cwc-reviews__counter-container">
										<span class="cwc-reviews__counter">
											<span class="cwc-reviews__counter-current"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>/<span class="cwc-reviews__counter-total"><?php echo esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ); ?></span>
										</span>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</div>

				</div>
			<?php endforeach; ?>

		</div>

	</div>
</section>
