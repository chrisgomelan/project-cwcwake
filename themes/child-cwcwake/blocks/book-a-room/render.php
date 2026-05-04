<?php
/**
 * Render template for the cwc/book-a-room block.
 *
 * @package ChildCwcwake
 */

if (!defined('ABSPATH')) {
	exit;
}

$class_name = 'wp-block-cwc-book-a-room';
if (!empty($attributes['className'])) {
	$class_name .= ' ' . esc_attr($attributes['className']);
}

$wrapper_attrs = get_block_wrapper_attributes(array('class' => $class_name));

$query = new WP_Query(
	array(
		'post_type' => 'accommodation',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'orderby' => 'menu_order title',
		'order' => 'ASC',
	)
);

$rooms = array();
if ($query->have_posts()) {
	while ($query->have_posts()) {
		$query->the_post();
		$current_post_id = get_the_ID();

		$capacity = get_post_meta($current_post_id, '_cwc_capacity', true);
		if (empty($capacity)) {
			$capacity = 'Maximum 4 persons';
		} else {
			$capacity = sprintf('Maximum %s %s', $capacity, 1 === (int) $capacity ? 'person' : 'persons');
		}

		$room_availability = 'available';
		if (function_exists('cwc_accommodation_availability')) {
			$room_availability = cwc_accommodation_availability($current_post_id);
		}

		$rooms[] = array(
			'title' => get_the_title(),
			'image' => get_the_post_thumbnail_url($current_post_id, 'large'),
			'price' => get_post_meta($current_post_id, '_cwc_price', true),
			'url' => get_permalink(),
			'capacity' => $capacity,
			'raw_capacity' => empty(get_post_meta($current_post_id, '_cwc_capacity', true)) ? 4 : (int) get_post_meta($current_post_id, '_cwc_capacity', true),
			'inclusions' => function_exists('cwc_accommodation_inclusions') ? cwc_accommodation_inclusions($current_post_id) : [],
			'availability' => $room_availability,
		);
	}
	wp_reset_postdata();
}
?>

<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<div class="cwc-book-hero">
		<h1 class="cwc-book-hero__title">BOOK A ROOM</h1>
		<p class="cwc-book-hero__desc">Stay in the heart of CWC and experience the perfect balance of comfort and
			adventure. Our accommodations are crafted to give you a relaxing retreat after a day of activities. Reserve
			your room today and make every moment of your trip count.</p>

		<div class="cwc-book-hero__images-wrap">
			<div class="cwc-book-hero__images">
				<?php
				$hero_images = array(
					'/wp-content/uploads/2026/04/pro-level-6-2.webp',
					'/wp-content/uploads/2026/04/elite-facilities-before-footer-bg.webp',
					'/wp-content/uploads/2026/04/book-3.webp',
					'/wp-content/uploads/2026/04/book-4.webp'
				);
				foreach ($hero_images as $img):
					?>
					<div class="cwc-book-hero__image-tile">
						<div class="cwc-book-hero__image" style="background-image:url('<?php echo esc_url($img); ?>');">
						</div>
						<div class="cwc-book-hero__image-overlay"></div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="cwc-booking-bar">
				<div class="cwc-booking-bar__field cwc-booking-bar__field--checkin" data-modal-target="date">
					<img src="/wp-content/uploads/2026/04/book-check-in.svg" alt="" class="cwc-booking-bar__icon">
					<div class="cwc-booking-bar__content">
						<span class="cwc-booking-bar__label">Check In</span>
						<span class="cwc-booking-bar__value" id="cwc-val-checkin">Add date</span>
					</div>
				</div>

				<div class="cwc-booking-bar__field cwc-booking-bar__field--checkout" data-modal-target="date">
					<img src="/wp-content/uploads/2026/04/book-check-in.svg" alt="" class="cwc-booking-bar__icon">
					<div class="cwc-booking-bar__content">
						<span class="cwc-booking-bar__label">Check Out</span>
						<span class="cwc-booking-bar__value" id="cwc-val-checkout">Add date</span>
					</div>
				</div>

				<div class="cwc-booking-bar__field cwc-booking-bar__field--room" data-modal-target="room">
					<img src="/wp-content/uploads/2026/04/room-type.svg" alt="" class="cwc-booking-bar__icon">
					<div class="cwc-booking-bar__content">
						<span class="cwc-booking-bar__label">Room Type</span>
						<span class="cwc-booking-bar__value" id="cwc-val-room">Choose Room</span>
					</div>
				</div>

				<div class="cwc-booking-bar__field cwc-booking-bar__field--guests" data-modal-target="guests">
					<img src="/wp-content/uploads/2026/04/guest-type.svg" alt="" class="cwc-booking-bar__icon">
					<div class="cwc-booking-bar__content">
						<span class="cwc-booking-bar__label">Guests</span>
						<span class="cwc-booking-bar__value" id="cwc-val-guests">0 Adult, 0 Kids</span>
					</div>
				</div>

				<button class="cwc-booking-bar__proceed">Proceed</button>
			</div>
		</div>

		<p class="cwc-book-notice">Guests will receive confirmation within 24 hours via e-mail or telephone call by
			frontdesk staff.</p>
	</div>

	<div class="cwc-room-cards">
		<?php foreach ($rooms as $room): ?>
			<article class="cwc-room-card">
				<div class="cwc-room-card__image" style="background-image:url('<?php echo esc_url($room['image']); ?>');">
				</div>
				<div class="cwc-room-card__content">
					<div class="cwc-room-card__header">
						<h3 class="cwc-room-card__title"><?php echo esc_html(strtoupper($room['title'])); ?></h3>
						<div class="cwc-room-card__price"><?php echo esc_html($room['price']); ?></div>
					</div>
					<ul class="cwc-room-card__inclusions">
						<?php
						$inclusions_list = is_array($room['inclusions']) ? $room['inclusions'] : explode(',', (string)$room['inclusions']);
						$inclusions_list = array_filter(array_map('trim', $inclusions_list));

						if (!empty($inclusions_list)) {
							foreach ($inclusions_list as $inc) {
								printf('<li>%s</li>', esc_html($inc));
							}
						} else {
							echo '<li class="cwc-room-card__inclusion-fallback">';
							echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px; opacity:0.6;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
							echo '<span>Standard inclusions apply</span>';
							echo '</li>';
						}
						?>
					</ul>
					<a href="<?php echo esc_url($room['url']); ?>" class="cwc-room-card__btn">View Full Details</a>
				</div>
			</article>
		<?php endforeach; ?>
	</div>

	<!-- Modals Overlay -->
	<div class="cwc-modal-backdrop" id="cwc-modal-backdrop"></div>

	<!-- Date Modal -->
	<div class="cwc-booking-modal" id="cwc-modal-date">
		<div class="cwc-booking-modal__content cwc-booking-modal__content--calendar">
			<div class="cwc-calendar">
				<div class="cwc-calendar__header">
					<button class="cwc-calendar__prev" type="button" aria-label="Previous Month">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
							<path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
								stroke-linejoin="round" />
						</svg>
					</button>
					<h3 class="cwc-calendar__month-year"></h3>
					<button class="cwc-calendar__next" type="button" aria-label="Next Month">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
							<path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
								stroke-linejoin="round" />
						</svg>
					</button>
				</div>
				<div class="cwc-calendar__days-labels">
					<span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span><span>S</span>
				</div>
				<div class="cwc-calendar__grid" id="cwc-calendar-grid">
					<!-- JS will populate this -->
				</div>
			</div>
			<button class="cwc-booking-modal__confirm">Confirm Selection</button>
		</div>
	</div>

	<!-- Room Type Modal -->
	<div class="cwc-booking-modal" id="cwc-modal-room">
		<div class="cwc-booking-modal__content">
			<h3 class="cwc-booking-modal__title">Room Type</h3>
			<div class="cwc-booking-modal__options">
				<?php foreach ($rooms as $room): 
					$is_room_booked = ( $room['availability'] === 'fully-booked' );
				?>
					<label class="cwc-booking-modal__option<?php echo $is_room_booked ? ' cwc-booking-modal__option--booked' : ''; ?>" style="<?php echo $is_room_booked ? 'opacity: 0.6; position: relative; cursor: not-allowed; pointer-events: none; filter: grayscale(100%);' : ''; ?>">
						<div class="cwc-booking-modal__option-text">
							<span class="cwc-booking-modal__label"><?php echo esc_html($room['title']); ?></span>
							<span class="cwc-booking-modal__sublabel"><?php echo esc_html($room['capacity']); ?></span>
							<?php if ($is_room_booked): ?>
								<span style="display:inline-block;margin-top:4px;padding:2px 8px;border-radius:4px;background:#fef2f2;color:#dc2626;font-size:11px;font-weight:600;">Fully Booked</span>
							<?php endif; ?>
						</div>
						<input type="radio" name="cwc_room_type" class="cwc-booking-modal__radio"
							value="<?php echo esc_attr($room['title']); ?>"
							data-capacity="<?php echo esc_attr($room['raw_capacity']); ?>"
							<?php echo $is_room_booked ? ' disabled' : ''; ?>>
					</label>
				<?php endforeach; ?>
			</div>
			<button class="cwc-booking-modal__confirm">Confirm Selection</button>
		</div>
	</div>

	<!-- Guests Modal -->
	<div class="cwc-booking-modal" id="cwc-modal-guests">
		<div class="cwc-booking-modal__content">
			<h3 class="cwc-booking-modal__title">Guests</h3>

			<div class="cwc-booking-modal__counter">
				<div class="cwc-booking-modal__counter-info">
					<span class="cwc-booking-modal__label">Adults</span>
					<span class="cwc-booking-modal__sublabel">Age +17</span>
				</div>
				<div class="cwc-booking-modal__counter-actions">
					<button class="cwc-booking-modal__btn-dec" data-target="adults"
						aria-label="Decrease adults">-</button>
					<span class="cwc-booking-modal__val" id="cwc-val-modal-adults">0</span>
					<button class="cwc-booking-modal__btn-inc" data-target="adults"
						aria-label="Increase adults">+</button>
				</div>
			</div>

			<div class="cwc-booking-modal__counter">
				<div class="cwc-booking-modal__counter-info">
					<span class="cwc-booking-modal__label">Kids</span>
					<span class="cwc-booking-modal__sublabel">Age 0 to 17</span>
				</div>
				<div class="cwc-booking-modal__counter-actions">
					<button class="cwc-booking-modal__btn-dec" data-target="kids" aria-label="Decrease kids">-</button>
					<span class="cwc-booking-modal__val" id="cwc-val-modal-kids">0</span>
					<button class="cwc-booking-modal__btn-inc" data-target="kids" aria-label="Increase kids">+</button>
				</div>
			</div>

			<button class="cwc-booking-modal__confirm">Confirm Selection</button>
		</div>
	</div>

</div>