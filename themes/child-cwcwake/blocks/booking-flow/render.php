<?php
/**
 * Render template for the cwc/booking-flow block.
 *
 * Multi-step booking flow with Guest Details (step 2) and
 * Payment Details (step 3), plus a persistent Booking Summary sidebar.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner block markup (unused).
 * @var WP_Block $block      Block instance.
 *
 * @package ChildCwcwake
 */

if (!defined('ABSPATH')) {
	exit;
}

$class_name = 'wp-block-cwc-booking-flow';
if (!empty($attributes['className'])) {
	$class_name .= ' ' . esc_attr($attributes['className']);
}

$wrapper_attrs = get_block_wrapper_attributes(array(
	'class' => $class_name,
	'data-theme-url' => get_stylesheet_directory_uri()
));

$booking_data_source = 'session';
$room_val     = isset($_GET['room']) ? sanitize_text_field($_GET['room']) : '';
$checkin_val  = isset($_GET['checkin']) ? sanitize_text_field($_GET['checkin']) : '';
$checkout_val = isset($_GET['checkout']) ? sanitize_text_field($_GET['checkout']) : '';

/* Fetch rooms for the Selected Room modal */
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
			$capacity = '4';
		}

		$room_availability = 'available';
		if (function_exists('cwc_accommodation_availability')) {
			$room_availability = cwc_accommodation_availability($current_post_id);
		}

		$rooms[] = array(
			'title' => get_the_title(),
			'image' => get_the_post_thumbnail_url($current_post_id, 'medium'),
			'price' => get_post_meta($current_post_id, '_cwc_price', true),
			'capacity' => $capacity,
			'excerpt' => get_the_excerpt($current_post_id),
			'beds' => function_exists('cwc_get_room_beds') ? cwc_get_room_beds($current_post_id) : [],
			'availability' => $room_availability,
		);
	}
	wp_reset_postdata();
}

$selected_room = array();
foreach ($rooms as $room) {
	if (strtolower($room['title']) === strtolower($room_val)) {
		$selected_room = $room;
		break;
	}
}

if (empty($selected_room) && !empty($rooms)) {
	$selected_room = $rooms[0];
} else if (empty($selected_room)) {
	$selected_room = array(
		'title' => 'Villa',
		'image' => '',
		'price' => 'PHP 19,500',
		'capacity' => '4',
	);
}

$first_room = $selected_room;
$first_room_price = $first_room['price'];
?>

<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-source="<?php echo esc_attr($booking_data_source); ?>">
	<!-- ─── Incomplete Selection Message (Hidden by default) ─── -->
	<div id="bf-incomplete-message" class="bf-inner-container" style="display: none; text-align: center; padding: 100px 20px;">
		<h2 style="margin-bottom: 16px;">Incomplete Booking Selection</h2>
		<p style="margin-bottom: 24px; color: #666;">Please complete your booking selection (dates, room, and guests) before proceeding.</p>
		<a href="<?php echo esc_url(home_url('/accommodations/')); ?>" class="bf-btn-primary" style="display: inline-block; text-decoration: none; padding: 12px 24px; border-radius: 8px;">Back to Accommodations</a>
	</div>

	<div class="bf-main-content" id="bf-main-content">
		<div class="bf-inner-container">

		<!-- ─── Progress Bar ─── -->
		<div class="bf-progress">
			<div class="bf-progress__step bf-progress__step--done" data-step="1">
				<span class="bf-progress__circle bf-progress__circle--done">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" fill="#fff" />
					</svg>
				</span>
				<span class="bf-progress__label">Your Selection</span>
			</div>
			<span class="bf-progress__line bf-progress__line--done"></span>
			<div class="bf-progress__step bf-progress__step--active" data-step="2">
				<span class="bf-progress__circle bf-progress__circle--active">2</span>
				<span class="bf-progress__label">Your Details</span>
			</div>
			<span class="bf-progress__line bf-progress__line--upcoming"></span>
			<div class="bf-progress__step bf-progress__step--upcoming" data-step="3">
				<span class="bf-progress__circle bf-progress__circle--upcoming">3</span>
				<span class="bf-progress__label">Payment</span>
			</div>
		</div>

		<!-- ─── Main Layout ─── -->
		<div class="bf-layout">

			<!-- ════════ LEFT PANEL ════════ -->
			<div class="bf-panel-wrap">

				<!-- ── STEP 2: Guest Details ── -->
				<div class="bf-panel bf-panel--guest is-active" id="bf-step-guest">
					<h2 class="bf-panel__title">Guest Details</h2>
					<p class="bf-panel__desc">Guest names must match the valid ID which will be used at check-in.</p>

					<div class="bf-panel__section-header">
						<h3 class="bf-panel__sub-label">Primary Guest</h3>
						<div class="bf-panel__section-controls">
							<span class="bf-panel__capacity" id="bf-capacity-count">1/4</span>
							<button class="bf-panel__add-guest" id="bf-add-guest" type="button">
								<img src="/wp-content/uploads/2026/04/add-new-guest.svg" alt="" width="24" height="24">
								Add New Guest
							</button>
						</div>
					</div>

					<div class="bf-field">
						<label class="bf-field__label">Your Full Name <span class="bf-field__req">*</span></label>
						<input type="text" class="bf-field__input" id="bf-fullname" placeholder="Last Name, First Name"
							required>
						<p class="bf-field__note">All booking confirmations, updates, and important travel details will
							be sent to Primary Guest.</p>
					</div>

					<div class="bf-field-row">
						<div class="bf-field bf-field--half">
							<label class="bf-field__label">Your Email <span class="bf-field__req">*</span></label>
							<div class="bf-field__input-wrap bf-field__input-wrap--icon">
								<img src="/wp-content/uploads/2026/04/mail-envelope.svg" alt="" class="bf-field__icon"
									width="20" height="20">
								<input type="email" class="bf-field__input bf-field__input--with-icon" id="bf-email"
									placeholder="Enter Email Address" required>
							</div>
						</div>
						<div class="bf-field bf-field--half">
							<label class="bf-field__label">Your Phone Number <span
									class="bf-field__req">*</span></label>
							<div class="bf-field__phone-row">
								<div class="bf-field__country-selector" id="bf-country-selector">
									<button class="bf-field__country-code" type="button">
										<span class="bf-field__flag" id="bf-selected-flag"><img
												src="https://flagcdn.com/w20/ph.png" width="20"
												style="border-radius: 2px;"></span>
										<span id="bf-selected-code">+63</span>
										<svg width="12" height="12" viewBox="0 0 12 12">
											<path d="M3 5l3 3 3-3" stroke="currentColor" stroke-width="1.5"
												fill="none" />
										</svg>
									</button>
									<div class="bf-country-dropdown" id="bf-country-dropdown">
										<!-- JS populates -->
									</div>
								</div>
								<input type="tel" class="bf-field__input bf-field__input--phone" id="bf-phone"
									placeholder="912 345 6789" required>
								<input type="hidden" id="bf-dial-code" value="+63">
								<input type="hidden" id="bf-country-flag" value="ph">
							</div>
						</div>
					</div>

					<div id="bf-additional-guests">
						<!-- JS will append additional guest rows here -->
					</div>

					<div class="bf-field">
						<label class="bf-field__label">Special Requests</label>
						<textarea class="bf-field__textarea" id="bf-requests"
							placeholder="Let the property know if there's anything they can assist you with."></textarea>
					</div>

					<label class="bf-checkbox">
						<input type="checkbox" class="bf-checkbox__input" id="bf-agree-updates">
						<span class="bf-checkbox__box"></span>
						<span class="bf-checkbox__label">I agree to receive status updates via Email &amp; SMS</span>
					</label>

					<button class="bf-btn-primary" id="bf-to-payment" type="button">Proceed to Payment</button>

					<p class="bf-disclaimer">Deposits are non-refundable; no-shows will be charged the full booking
						amount, and the remaining balance must be paid at the hotel upon arrival.</p>
				</div>

				<!-- ── STEP 3: Payment Details ── -->
				<div class="bf-panel bf-panel--payment" id="bf-step-payment">
					<h2 class="bf-panel__title">Payment Details</h2>

					<h3 class="bf-panel__sub-label">Payment methods</h3>
					<div class="bf-payment-methods">
						<label class="bf-payment-methods__option">
							<input type="radio" name="bf_payment_method" value="visa" checked>
							<img src="/wp-content/uploads/2026/04/visa.webp" alt="Visa" height="40">
						</label>
						<label class="bf-payment-methods__option">
							<input type="radio" name="bf_payment_method" value="mastercard">
							<img src="/wp-content/uploads/2026/04/master-card.webp" alt="Mastercard" height="40">
						</label>
						<label class="bf-payment-methods__option">
							<input type="radio" name="bf_payment_method" value="kind">
							<img src="/wp-content/uploads/2026/04/kind.webp" alt="Kind" height="40">
						</label>
						<label class="bf-payment-methods__option">
							<input type="radio" name="bf_payment_method" value="gcash">
							<img src="/wp-content/uploads/2026/04/gcash-logo.webp" alt="GCash" height="40">
						</label>
						<label class="bf-payment-methods__option">
							<input type="radio" name="bf_payment_method" value="bank">
							<img src="/wp-content/uploads/2026/04/bank-transfer.webp" alt="Bank Transfer" height="40">
						</label>
					</div>

					<!-- Card form (visible for visa/mastercard) -->
					<div class="bf-card-form" id="bf-card-form">
						<h3 class="bf-panel__sub-label">Card Details</h3>

						<div class="bf-field">
							<label class="bf-field__label">Card Holder Name <span class="bf-field__req">*</span></label>
							<input type="text" class="bf-field__input" id="bf-card-name"
								placeholder="Enter Card Holder Name">
						</div>
						<div class="bf-field">
							<label class="bf-field__label">Card Number <span class="bf-field__req">*</span></label>
							<input type="text" class="bf-field__input" id="bf-card-number"
								placeholder="Enter Card Number" maxlength="19">
						</div>
						<div class="bf-field-row">
							<div class="bf-field bf-field--half">
								<label class="bf-field__label">Expiry <span class="bf-field__req">*</span></label>
								<input type="text" class="bf-field__input" id="bf-card-expiry" placeholder="MM/YY"
									maxlength="5">
							</div>
							<div class="bf-field bf-field--half">
								<label class="bf-field__label">CVC <span class="bf-field__req">*</span></label>
								<input type="text" class="bf-field__input" id="bf-card-cvc" placeholder="CVC"
									maxlength="4">
							</div>
						</div>
					</div>

					<!-- GCash QR (visible for gcash-qr) -->
					<div class="bf-gcash-qr" id="bf-gcash-qr">
						<h3 class="bf-panel__sub-label">GCash</h3>
						<p class="bf-panel__desc">Please scan the QR code using your GCash app to complete your payment
							quickly and securely. Once done, kindly keep a screenshot of your transaction for reference.
						</p>
						<div class="bf-gcash-qr__image">
							<img src="/wp-content/uploads/2026/04/gcash-qr-code.webp" alt="GCash QR Code" width="200"
								height="200">
						</div>
					</div>

					<label class="bf-checkbox">
						<input type="checkbox" class="bf-checkbox__input" id="bf-agree-terms">
						<span class="bf-checkbox__box"></span>
						<span class="bf-checkbox__label">I accept the Terms of Use and Privacy Policy</span>
					</label>

					<button class="bf-btn-primary" id="bf-confirm-pay" type="button">Confirm and Pay</button>

					<p class="bf-disclaimer">Deposits are non-refundable; no-shows will be charged the full booking
						amount, and the remaining balance must be paid at the hotel upon arrival.</p>
				</div>

			</div>

			<!-- ════════ RIGHT PANEL: Booking Summary ════════ -->
			<aside class="bf-summary" id="bf-summary">
				<!-- Mobile Collapsed Header -->
				<div class="bf-summary__header">
					<div class="bf-summary__title-wrap">
						<h2 class="bf-summary__title">Booking Summary</h2>
						<button class="bf-summary__toggle" id="bf-summary-toggle" type="button">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M6 9l6 6 6-6"/>
							</svg>
						</button>
					</div>
					<div class="bf-summary__mobile-quick" id="bf-summary-mobile-quick">
						<h3 class="bf-summary__mobile-room" id="bf-summary-mobile-room"><?php echo esc_html($first_room['title']); ?> Room</h3>
						<div class="bf-summary__mobile-dates">
							<span id="bf-summary-mobile-checkin"><?php echo esc_html($checkin_val ?: '08/14/2026'); ?></span> — <span id="bf-summary-mobile-checkout"><?php echo esc_html($checkout_val ?: '08/19/2026'); ?></span>
						</div>
						<div class="bf-summary__mobile-total">
							Total: <span id="bf-summary-mobile-price">₱ <?php echo esc_html(number_format($total_price, 2)); ?></span>
						</div>
					</div>
				</div>

				<div class="bf-summary__content" id="bf-summary-content">
					<div class="bf-summary__divider"></div>

				<!-- Room preview -->
				<div class="bf-summary__room-preview">
					<?php if (!empty($first_room['image'])): ?>
						<img class="bf-summary__room-img" src="<?php echo esc_url($first_room['image']); ?>"
							alt="<?php echo esc_attr($first_room['title']); ?>" width="136" height="136">
					<?php else: ?>
						<div class="bf-summary__room-img bf-summary__room-img--empty"></div>
					<?php endif; ?>
					<div class="bf-summary__room-info">
						<h3 class="bf-summary__room-name" id="bf-summary-room-name">
							<?php echo esc_html($first_room['title']); ?> Room
						</h3>
						<p class="bf-summary__room-desc" id="bf-summary-room-desc">
							<?php echo esc_html($first_room['excerpt']); ?>
						</p>
					</div>
				</div>

				<!-- Your Details (shown on step 3) -->
				<div class="bf-summary__details" id="bf-summary-details">
					<div class="bf-summary__section-header">
						<h3 class="bf-summary__sub-label">Your Details</h3>
						<button class="bf-summary__edit-link" data-modal="personal-info" type="button">
							<img src="/wp-content/uploads/2026/04/pencil-edit.svg" alt="" width="16" height="16">
							Edit
						</button>
					</div>
					<p class="bf-summary__detail-text" id="bf-summary-name">—</p>
					<p class="bf-summary__detail-text" id="bf-summary-phone">—</p>
					<p class="bf-summary__detail-text" id="bf-summary-email">—</p>

					<div class="bf-summary__section-header">
						<h3 class="bf-summary__sub-label bf-summary__additional-label">Additional Guest</h3>
						<button class="bf-summary__edit-link" data-modal="additional-guests" type="button">View
							All</button>
					</div>
					<div class="bf-summary__divider"></div>
				</div>

				<!-- Trip Summary -->
				<div class="bf-summary__trip">
					<div class="bf-summary__section-header">
						<h3 class="bf-summary__sub-label">Trip Summary</h3>
						<button class="bf-summary__edit-link" data-modal="trip-summary" type="button">
							<img src="/wp-content/uploads/2026/04/pencil-edit.svg" alt="" width="16" height="16">
							Edit
						</button>
					</div>
					<div class="bf-summary__dates bf-summary-padded">
						<div class="bf-summary__date-col">
							<div class="bf-summary__date-label">
								<img src="/wp-content/uploads/2026/04/booking-summary-trip-calendar.svg" alt=""
									width="18" height="18">
								Check-in
							</div>
							<span class="bf-summary__date-val"
								id="bf-summary-checkin"><?php echo esc_html($checkin_val ?: '08/14/2026'); ?></span>
						</div>
						<span class="bf-summary__date-divider"></span>
						<div class="bf-summary__date-col">
							<div class="bf-summary__date-label">
								<img src="/wp-content/uploads/2026/04/booking-summary-trip-calendar.svg" alt=""
									width="18" height="18">
								Check-out
							</div>
							<span class="bf-summary__date-val"
								id="bf-summary-checkout"><?php echo esc_html($checkout_val ?: '08/19/2026'); ?></span>
						</div>
					</div>
					<div class="bf-summary__guests-row">
						<span class="bf-summary__guests-label">Guests</span>
						<div class="bf-summary__guests-group bf-summary-padded">
							<img src="/wp-content/uploads/2026/04/guests.svg" alt="" width="18" height="18">
							<span id="bf-summary-guests"
								style="color:#000000B2 ;"><?php echo esc_html($guests_val ?: '4 Adults, 0 Kids'); ?></span>
						</div>
					</div>
				</div>
				<div class="bf-summary__divider"></div>

				<!-- Selected Room -->
				<div class="bf-summary__selected-room">
					<div class="bf-summary__section-header">
						<h3 class="bf-summary__sub-label">Your Selected Room</h3>
						<button class="bf-summary__edit-link" data-modal="selected-room" type="button">
							<img src="/wp-content/uploads/2026/04/pencil-edit.svg" alt="" width="16" height="16">
							Edit
						</button>
					</div>
					<p class="bf-summary__room-type bf-summary-padded" id="bf-summary-room-type">
						<?php echo esc_html($first_room['title']); ?> Room
					</p>
					<div class="bf-summary__amenities" id="bf-summary-amenities">
						<?php foreach ($first_room['beds'] as $bed): ?>
							<span class="bf-summary__amenity">
								<img src="<?php echo esc_url($bed['icon_url']); ?>" alt="" width="16" height="16">
								<?php echo esc_html($bed['label']); ?>
							</span>
							<span class="bf-summary__amenity-divider">|</span>
						<?php endforeach; ?>
						<span class="bf-summary__amenity">
							<img src="/wp-content/uploads/2026/04/max-people-icon.svg" alt="" width="16" height="16">
							Max <span id="bf-summary-capacity"><?php echo esc_html($first_room['capacity']); ?></span>
							People
						</span>
					</div>
				</div>
				<div class="bf-summary__divider"></div>

				<!-- Price Breakdown -->
				<?php
				$nights_count = 0;
				if (!empty($checkin_val) && !empty($checkout_val)) {
					$ci_ts = strtotime($checkin_val);
					$co_ts = strtotime($checkout_val);
					if ($ci_ts && $co_ts && $co_ts > $ci_ts) {
						$nights_count = (int)(($co_ts - $ci_ts) / DAY_IN_SECONDS);
					}
				}
				$nightly_rate = (float) preg_replace('/[^0-9.]/', '', $first_room_price);
				$total_price = $nights_count > 0 ? $nightly_rate * $nights_count : $nightly_rate;
				?>
				<div class="bf-summary__price-breakdown" style="padding: 8px 0; font-size: 14px; color: #475569;">
					<div style="display:flex;justify-content:space-between;margin-bottom:4px;">
						<span>Rate per night</span>
						<span>₱ <?php echo esc_html(number_format($nightly_rate, 2)); ?></span>
					</div>
					<?php if ($nights_count > 0): ?>
					<div id="bf-summary-nights" class="bf-summary__nights-row" style="display:flex;justify-content:space-between;margin-bottom:4px;">
						<span>Duration</span>
						<strong><?php echo esc_html($nights_count); ?> night<?php echo $nights_count > 1 ? 's' : ''; ?></strong>
					</div>
					<?php endif; ?>
				</div>

				<!-- Total Price -->
				<div class="bf-summary__total">
					<div class="bf-summary__total-left">
						<span class="bf-summary__total-label">Total Price</span>
						<span class="bf-summary__total-sub">Taxes &amp; fees included</span>
					</div>
					<span class="bf-summary__total-price" id="bf-summary-price">₱
						<?php echo esc_html(number_format($total_price, 2)); ?></span>
				</div>
				</div><!-- .bf-summary__content -->
			</aside>

		</div><!-- .bf-layout -->

	</div><!-- .bf-main-content -->
</div>
	<!-- ─── MODALS ─── -->
	<div class="bf-modal-backdrop" id="bf-modal-backdrop"></div>

	<!-- Modal: Trip Summary -->
	<div class="bf-modal" id="bf-modal-trip-summary">
		<div class="bf-modal__inner bf-modal__inner--trip">
			<div class="bf-modal__header">
				<h3 class="bf-modal__title">Trip Summary</h3>
				<button class="bf-modal__close" type="button">&times;</button>
			</div>
			<div class="bf-modal__body">
				<div class="bf-modal__date-selection-wrapper">
					<div class="bf-modal__date-selection-grid">
						<div class="bf-modal__date-icon-col">
							<img src="/wp-content/uploads/2026/04/book-check-in.svg" alt="" width="24" height="24">
						</div>
						<div class="bf-modal__date-info-col" id="bf-modal-trigger-checkin">
							<span class="bf-modal__date-label">Check in</span>
							<span class="bf-modal__date-val" id="bf-modal-val-checkin">08/14/2026</span>
						</div>
					</div>
					<div class="bf-modal__vertical-divider"></div>
					<div class="bf-modal__date-selection-grid">
						<div class="bf-modal__date-icon-col">
							<img src="/wp-content/uploads/2026/04/book-check-in.svg" alt="" width="24" height="24">
						</div>
						<div class="bf-modal__date-info-col" id="bf-modal-trigger-checkout">
							<span class="bf-modal__date-label">Check out</span>
							<span class="bf-modal__date-val" id="bf-modal-val-checkout">08/19/2026</span>
						</div>
					</div>
				</div>

				<!-- Custom Calendar Grid (reusable logic) -->
				<div class="bf-calendar" id="bf-modal-calendar">
					<div class="bf-calendar__header">
						<button class="bf-calendar__prev" type="button">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
								stroke-width="2">
								<path d="M15 18l-6-6 6-6" />
							</svg>
						</button>
						<span class="bf-calendar__month-year">August 2026</span>
						<button class="bf-calendar__next" type="button">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
								stroke-width="2">
								<path d="M9 18l6-6-6-6" />
							</svg>
						</button>
					</div>
					<div class="bf-calendar__weekdays">
						<span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span><span>S</span>
					</div>
					<div class="bf-calendar__grid" id="bf-calendar-grid">
						<!-- JS populated -->
					</div>
				</div>
			</div>
			<button class="bf-modal__save" type="button">Save</button>
		</div>
	</div>

	<!-- Modal: Selected Room -->
	<div class="bf-modal" id="bf-modal-selected-room">
		<div class="bf-modal__inner">
			<div class="bf-modal__header">
				<h3 class="bf-modal__title">Selected Room</h3>
				<button class="bf-modal__close" type="button">&times;</button>
			</div>
			<div class="bf-modal__body">
				<div class="bf-modal__room-options">
					<?php foreach ($rooms as $index => $room):
						$is_room_booked = ( $room['availability'] === 'fully-booked' );
						?>
						<label class="bf-modal__room-option<?php echo $is_room_booked ? ' bf-modal__room-option--booked' : ''; ?>" style="<?php echo $is_room_booked ? 'opacity: 0.6; position: relative; cursor: not-allowed; pointer-events: none; filter: grayscale(100%);' : ''; ?>">
							<div class="bf-modal__room-option-text">
								<span class="bf-modal__room-option-name"><?php echo esc_html($room['title']); ?></span>
								<span class="bf-modal__room-option-cap">Max <?php echo esc_html($room['capacity']); ?>
									persons</span>
								<?php if ($is_room_booked): ?>
									<span style="display:inline-block;margin-top:4px;padding:2px 8px;border-radius:4px;background:#fef2f2;color:#dc2626;font-size:11px;font-weight:600;">Fully Booked</span>
								<?php endif; ?>
							</div>
							<input type="radio" name="bf_modal_room" class="bf-modal__radio"
								value="<?php echo esc_attr($room['title']); ?>"
								data-price="<?php echo esc_attr($room['price']); ?>"
								data-capacity="<?php echo esc_attr($room['capacity']); ?>"
								data-excerpt="<?php echo esc_attr($room['excerpt']); ?>"
								data-beds="<?php echo esc_attr(wp_json_encode($room['beds'])); ?>"
								data-image="<?php echo esc_url($room['image']); ?>"
								data-availability="<?php echo esc_attr($room['availability']); ?>"
								<?php echo $is_room_booked ? ' disabled' : ''; ?>
								<?php echo 0 === $index && ! $is_room_booked ? ' checked' : ''; ?>>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
			<button class="bf-modal__save" type="button">Save Selection</button>
		</div>
	</div>

	<!-- Modal: Personal Information -->
	<div class="bf-modal" id="bf-modal-personal-info">
		<div class="bf-modal__inner">
			<div class="bf-modal__header">
				<h3 class="bf-modal__title">Personal Information</h3>
				<button class="bf-modal__close" type="button">&times;</button>
			</div>
			<div class="bf-modal__body">
				<div class="bf-field">
					<label class="bf-field__label">Your Full Name</label>
					<input type="text" class="bf-field__input" id="bf-modal-pi-name"
						placeholder="Last Name, First Name">
				</div>
				<div class="bf-field">
					<label class="bf-field__label">Your Email</label>
					<div class="bf-field__input-wrap bf-field__input-wrap--icon">
						<img src="/wp-content/uploads/2026/04/mail-envelope.svg" alt="" class="bf-field__icon"
							width="20" height="20">
						<input type="email" class="bf-field__input bf-field__input--with-icon" id="bf-modal-pi-email"
							placeholder="Enter Email Address">
					</div>
				</div>
				<div class="bf-field">
					<label class="bf-field__label">Your Phone Number</label>
					<div class="bf-field__phone-row">
						<div class="bf-field__country-code">
							<span class="bf-field__flag">🇵🇭</span>
							<span>+63</span>
							<svg width="12" height="12" viewBox="0 0 12 12">
								<path d="M3 5l3 3 3-3" stroke="currentColor" stroke-width="1.5" fill="none" />
							</svg>
						</div>
						<input type="tel" class="bf-field__input bf-field__input--phone" id="bf-modal-pi-phone"
							placeholder="e.g. 905 123 456">
					</div>
				</div>
			</div>
			<button class="bf-modal__save" type="button">Save</button>
		</div>
	</div>

	<!-- Modal: Additional Guests -->
	<div class="bf-modal" id="bf-modal-additional-guests">
		<div class="bf-modal__inner">
			<div class="bf-modal__header">
				<h3 class="bf-modal__title">Additional Guests</h3>
				<button class="bf-modal__close" type="button">&times;</button>
			</div>
			<div class="bf-modal__body" id="bf-modal-guests-list">
				<!-- JS populates additional guest rows here -->
			</div>
			<button class="bf-modal__save" type="button">Save</button>
		</div>
	</div>

	<!-- Modal: Success -->
	<div class="bf-modal bf-modal--success" id="bf-modal-success">
		<div class="bf-modal__inner bf-modal__inner--success">
			<div class="bf-modal__success-icon">
				<svg width="40" height="40" viewBox="0 0 24 24" fill="none">
					<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" fill="#fff" />
				</svg>
			</div>
			<h2 class="bf-modal__success-title">You're all set! Your stay at CWC is confirmed.</h2>
			<p class="bf-modal__success-desc">
				Please check your email for your booking summary, including your accommodation details, check-in
				instructions, and reservation information. If you don't see it, kindly check your spam or promotions
				folder.
			</p>
			<p class="bf-modal__success-redirect">
				You'll be redirected to the homepage in a few seconds...
			</p>
			<p class="bf-modal__success-home">
				If nothing happens, <a href="/">click here</a> to go home.
			</p>
		</div>
	</div>

</div>