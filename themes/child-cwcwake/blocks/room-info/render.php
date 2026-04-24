<?php
/**
 * Render template for the cwc/room-info block.
 *
 * Renders the main room detail card: blue accent bar, room title,
 * description, amenity chip cloud, inline pricing/booking box, and
 * a tabular policies list. Layout matches the Figma room landing
 * spec; see designs/room-landing-page-design.md for the source of truth.
 *
 * Data resolution follows `room-management-transition.md` § 4:
 *
 *   1. **Block attribute** — wins. If an editor typed a value into
 *      the block UI, that's what renders.
 *   2. **Accommodation post meta** — fills the gaps. When the block
 *      is on a singular `accommodation` and the attribute is empty,
 *      we fall back to the matching `_cwc_*` meta + global policies.
 *   3. **Empty / placeholder** — final default if neither source has
 *      a value.
 *
 * Availability (`_cwc_availability`) drives the booking UI:
 *
 *   - `available`    → normal Book Now button.
 *   - `fully-booked` → "Fully Booked" message + "Inquire" link.
 *   - `maintenance`  → pricing aside hidden, "Coming Soon" badge
 *                      shown next to the title.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner block markup (unused).
 * @var WP_Block $block      Block instance.
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
---------------------------------------------------------
 * Resolve the accommodation context (if any)
 * ---------------------------------------------------------
 */

/*
 * `cwc_is_accommodation_context()` is the single check we use to
 * decide whether to consult post meta. Outside an accommodation
 * (e.g. the block dropped on a regular page) the fallback chain
 * stops at "block attribute" and the block behaves exactly the way
 * it always has — no surprise data leaks from another room's meta.
 */
$current_post_id      = 0;
$is_accommodation_ctx = false;
if ( function_exists( 'cwc_is_accommodation_context' ) && cwc_is_accommodation_context() ) {
	$current_post         = get_post();
	$current_post_id      = $current_post instanceof WP_Post ? (int) $current_post->ID : 0;
	$is_accommodation_ctx = $current_post_id > 0;
}

/*
---------------------------------------------------------
 * Pull attributes (block-editor-supplied values win)
 * ---------------------------------------------------------
 */

$block_title       = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
$description_label = isset( $attributes['descriptionLabel'] ) ? (string) $attributes['descriptionLabel'] : '';
$description       = isset( $attributes['description'] ) ? (string) $attributes['description'] : '';
$amenities_label   = isset( $attributes['amenitiesLabel'] ) ? (string) $attributes['amenitiesLabel'] : '';
$amenities         = isset( $attributes['amenities'] ) && is_array( $attributes['amenities'] ) ? $attributes['amenities'] : array();
$price             = isset( $attributes['price'] ) ? (string) $attributes['price'] : '';
$price_sub_label   = isset( $attributes['priceSubLabel'] ) ? (string) $attributes['priceSubLabel'] : '';
$book_button_label = isset( $attributes['bookButtonLabel'] ) ? (string) $attributes['bookButtonLabel'] : '';
$book_button_url   = isset( $attributes['bookButtonUrl'] ) ? (string) $attributes['bookButtonUrl'] : '';
$policies_label    = isset( $attributes['policiesLabel'] ) ? (string) $attributes['policiesLabel'] : '';
$policies_intro    = isset( $attributes['policiesIntro'] ) ? (string) $attributes['policiesIntro'] : '';
$policies          = isset( $attributes['policies'] ) && is_array( $attributes['policies'] ) ? $attributes['policies'] : array();

/*
---------------------------------------------------------
 * Meta fallbacks (only when on a single accommodation)
 * ---------------------------------------------------------
 */

$availability = 'available';

if ( $is_accommodation_ctx ) {
	if ( '' === $block_title ) {
		$block_title = get_the_title( $current_post_id );
	}

	if ( '' === $description ) {
		/*
		 * Prefer the excerpt for the short blurb shown next to the
		 * amenity chips — `post_content` now holds the block stack
		 * itself, which would render as raw HTML if dropped here.
		 */
		$description = (string) get_post_field( 'post_excerpt', $current_post_id );
	}

	if ( empty( $amenities ) && function_exists( 'cwc_accommodation_amenities' ) ) {
		$amenities = cwc_accommodation_amenities( $current_post_id );
	}

	if ( '' === $price ) {
		$price = (string) get_post_meta( $current_post_id, '_cwc_price', true );
	}

	$meta_price_sub = trim( (string) get_post_meta( $current_post_id, '_cwc_price_sub', true ) );
	$meta_capacity  = trim( (string) get_post_meta( $current_post_id, '_cwc_capacity', true ) );

	if ( '' !== $meta_capacity && false === stripos( $meta_price_sub, 'maximum' ) ) {
		$person_text  = ( 1 === (int) $meta_capacity ) ? 'person' : 'persons';
		$capacity_str = sprintf( 'Maximum %s %s', $meta_capacity, $person_text );

		if ( '' !== $meta_price_sub ) {
			$meta_price_sub .= ' · ' . $capacity_str;
		} else {
			$meta_price_sub = $capacity_str;
		}
	}

	if ( '' !== $meta_price_sub ) {
		/*
		 * Override the block default ("per night") when the editor
		 * filled the meta field. Comparing against the JSON default
		 * lets a per-room sub-label win without forcing every
		 * editor to clear the block-level value first.
		 */
		if ( '' === $price_sub_label || 'per night' === $price_sub_label ) {
			$price_sub_label = $meta_price_sub;
		} elseif ( '' !== $meta_capacity && false === stripos( $price_sub_label, 'maximum' ) ) {
			$person_text      = ( 1 === (int) $meta_capacity ) ? 'person' : 'persons';
			$price_sub_label .= ' · Maximum ' . $meta_capacity . ' ' . $person_text;
		}
	}

	if ( empty( $policies ) && function_exists( 'cwc_get_global_policies' ) ) {
		$policies = cwc_get_global_policies();
	}

	if ( function_exists( 'cwc_accommodation_availability' ) ) {
		$availability = cwc_accommodation_availability( $current_post_id );
	}
}

/*
---------------------------------------------------------
 * Bail gracefully if there's still nothing to show
 * ---------------------------------------------------------
 */

if ( '' === $block_title && '' === $description && empty( $amenities ) && empty( $policies ) ) {
	return;
}

/*
---------------------------------------------------------
 * Availability-driven booking UI
 * ---------------------------------------------------------
 */

/*
 * Each availability state owns a small data bag the booking aside
 * pulls from. Centralising the strings here (instead of three nested
 * if-blocks in the markup) keeps the JSX-like template below readable.
 *
 * - `show_pricing`  hides the entire pricing aside on `maintenance`.
 * - `show_button`   hides only the call-to-action.
 * - `button_label`  / `button_url` override the editor-supplied values
 *   when the room is `fully-booked` (so "Book Now" never appears next
 *   to a "Fully Booked" notice).
 * - `notice` is a short status line printed above the price.
 * - `badge` is the "Coming Soon" pill rendered next to the title in
 *   maintenance mode.
 */
$state = array(
	'show_pricing' => true,
	'show_button'  => true,
	'button_label' => $book_button_label,
	'button_url'   => $book_button_url,
	'notice'       => '',
	'badge'        => '',
);

if ( 'fully-booked' === $availability ) {
	$state['button_label'] = __( 'Inquire', 'child-cwcwake' );
	$state['button_url']   = '/contact/';
	$state['notice']       = __( 'Fully Booked', 'child-cwcwake' );
} elseif ( 'maintenance' === $availability ) {
	$state['show_pricing'] = false;
	$state['badge']        = __( 'Coming Soon', 'child-cwcwake' );
}

/*
---------------------------------------------------------
 * Render
 * ---------------------------------------------------------
 */

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'class' => 'cwc-room-info cwc-room-info--' . sanitize_html_class( $availability ),
	)
);

/**
 * Build an `<img>` tag for an amenity / policy icon.
 *
 * Delegates to the centralized `cwc_icon_url_for_slug()` so the
 * icon catalogue is shared with the meta-box UI and the global
 * policies admin page. Returns an empty string for an unknown slug
 * so a typo in the editor cannot break layout.
 *
 * @since 1.0.0
 *
 * @param string $slug Icon slug (e.g. `wifi`, `check-in`).
 * @return string Image tag markup, or empty string when the slug is unknown.
 */
$icon = static function ( $slug ) {
	$slug = (string) $slug;
	if ( '' === $slug || ! function_exists( 'cwc_icon_url_for_slug' ) ) {
		return '';
	}

	$src = cwc_icon_url_for_slug( $slug );
	if ( '' === $src ) {
		return '';
	}

	return sprintf(
		'<img class="cwc-room-info__icon" src="%s" alt="" width="20" height="20" loading="lazy" decoding="async" aria-hidden="true" />',
		esc_url( $src )
	);
};
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="cwc-room-info__panel">
		<span class="cwc-room-info__accent" aria-hidden="true"></span>

		<div class="cwc-room-info__body">
			<?php if ( '' !== $block_title ) : ?>
				<h2 class="cwc-room-info__title">
					<?php echo esc_html( $block_title ); ?>
					<?php if ( '' !== $state['badge'] ) : ?>
						<span class="cwc-room-info__badge"><?php echo esc_html( $state['badge'] ); ?></span>
					<?php endif; ?>
				</h2>
			<?php endif; ?>

			<div class="cwc-room-info__layout">
				<div class="cwc-room-info__main">
					<?php if ( '' !== $description ) : ?>
						<div class="cwc-room-info__section">
							<?php if ( '' !== $description_label ) : ?>
								<h3 class="cwc-room-info__label"><?php echo esc_html( $description_label ); ?></h3>
							<?php endif; ?>
							<p class="cwc-room-info__description"><?php echo esc_html( $description ); ?></p>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $amenities ) ) : ?>
						<div class="cwc-room-info__section">
							<?php if ( '' !== $amenities_label ) : ?>
								<h3 class="cwc-room-info__label"><?php echo esc_html( $amenities_label ); ?></h3>
							<?php endif; ?>
							<ul class="cwc-room-info__amenities">
								<?php
								foreach ( $amenities as $amenity ) {
									if ( ! is_array( $amenity ) ) {
										$amenity = array( 'label' => (string) $amenity );
									}
									$label    = isset( $amenity['label'] ) ? (string) $amenity['label'] : '';
									$icon_key = isset( $amenity['icon'] ) ? (string) $amenity['icon'] : '';
									if ( '' === $label ) {
										continue;
									}
									?>
									<li class="cwc-room-info__chip">
										<?php
										if ( '' !== $icon_key ) {
											echo $icon( $icon_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										}
										?>
										<span><?php echo esc_html( $label ); ?></span>
									</li>
									<?php
								}
								?>
							</ul>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( $state['show_pricing'] && ( '' !== $price || '' !== $state['button_label'] || '' !== $state['notice'] ) ) : ?>
					<aside class="cwc-room-info__pricing">
						<?php if ( '' !== $state['notice'] ) : ?>
							<p class="cwc-room-info__notice"><?php echo esc_html( $state['notice'] ); ?></p>
						<?php endif; ?>

						<?php if ( '' !== $price ) : ?>
							<p class="cwc-room-info__price"><?php echo esc_html( $price ); ?></p>
						<?php endif; ?>

						<?php if ( '' !== $price_sub_label ) : ?>
							<p class="cwc-room-info__price-sub"><?php echo esc_html( $price_sub_label ); ?></p>
						<?php endif; ?>

						<?php if ( $state['show_button'] && '' !== $state['button_label'] ) : ?>
							<a class="cwc-room-info__book-button"
								href="<?php echo esc_url( '' !== $state['button_url'] ? $state['button_url'] : '#book' ); ?>">
								<?php echo esc_html( $state['button_label'] ); ?>
							</a>
						<?php endif; ?>
					</aside>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $policies ) ) : ?>
				<div class="cwc-room-info__section cwc-room-info__section--policies">
					<?php if ( '' !== $policies_label ) : ?>
						<h3 class="cwc-room-info__label"><?php echo esc_html( $policies_label ); ?></h3>
					<?php endif; ?>
					<?php if ( '' !== $policies_intro ) : ?>
						<p class="cwc-room-info__policies-intro"><?php echo esc_html( $policies_intro ); ?></p>
					<?php endif; ?>
					<div class="cwc-room-info__policies" role="table">
						<?php
						foreach ( $policies as $policy ) {
							if ( ! is_array( $policy ) ) {
								continue;
							}
							$name     = isset( $policy['name'] ) ? (string) $policy['name'] : '';
							$desc     = isset( $policy['description'] ) ? (string) $policy['description'] : '';
							$icon_key = isset( $policy['icon'] ) ? (string) $policy['icon'] : '';
							if ( '' === $name && '' === $desc ) {
								continue;
							}
							?>
							<div class="cwc-room-info__policy" role="row">
								<div class="cwc-room-info__policy-name" role="cell">
									<?php
									if ( '' !== $icon_key ) {
										echo $icon( $icon_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									}
									?>
									<span><?php echo esc_html( $name ); ?></span>
								</div>
								<div class="cwc-room-info__policy-desc" role="cell">
									<?php echo wp_kses_post( wpautop( $desc ) ); ?>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
