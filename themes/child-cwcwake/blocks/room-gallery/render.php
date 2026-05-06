<?php
/**
 * Render template for the cwc/room-gallery block.
 *
 * Renders a "Back to Accomodations" link followed by a three-column asymmetric
 * image grid (1 large left, 1 tall center, 2 stacked right). A "See All
 * Images" pill is overlaid on the large image when a URL is supplied.
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

$images          = isset( $attributes['images'] ) && is_array( $attributes['images'] ) ? $attributes['images'] : array();
$back_link_label = isset( $attributes['backLinkLabel'] ) ? (string) $attributes['backLinkLabel'] : '';
$back_link_url   = isset( $attributes['backLinkUrl'] ) ? (string) $attributes['backLinkUrl'] : '';
$see_all_label   = isset( $attributes['seeAllLabel'] ) ? (string) $attributes['seeAllLabel'] : '';
$see_all_url     = isset( $attributes['seeAllUrl'] ) ? (string) $attributes['seeAllUrl'] : '';

/*
 * Meta fallback (room-management-transition.md § 4):
 *
 *   - When the editor hasn't supplied images directly on the
 *     block, and we're rendering on a single accommodation post,
 *     pull `_cwc_gallery_ids` and resolve each ID to a URL via
 *     `wp_get_attachment_image_url()`.
 *   - The "Back to Accommodations" link similarly defaults to the
 *     archive when no override was set, so editors never have to
 *     touch the block once the room post has its meta filled.
 */
if ( function_exists( 'cwc_is_accommodation_context' ) && cwc_is_accommodation_context() ) {
	$current_post = get_post();
	if ( $current_post instanceof WP_Post ) {
		if ( empty( $images ) && function_exists( 'cwc_accommodation_gallery_images' ) ) {
			$images = cwc_accommodation_gallery_images( (int) $current_post->ID );
		}
		if ( '' === $back_link_label ) {
			$back_link_label = __( 'Back to Accommodations', 'child-cwcwake' );
		}
		if ( '' === $back_link_url ) {
			$back_link_url = '/accommodations/';
		}
		if ( '' === $see_all_label ) {
			$see_all_label = __( 'See All Images', 'child-cwcwake' );
		}
	}
}

if ( empty( $images ) ) {
	return;
}

/*
 * The grid expects exactly 4 image slots; pad with empty entries so the
 * skeleton still renders if the editor supplies fewer images.
 */
$slots = array_pad( array_slice( $images, 0, 4 ), 4, '' );

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'cwc-room-gallery' ) );

$see_all_icon_src = get_stylesheet_directory_uri() . '/assets/images/see-all-images.svg';

/**
 * Resolve a slot to its image URL.
 *
 * Accepts either a plain URL string or an associative array containing
 * a `url` (and optional `alt`) key.
 *
 * @since 1.0.0
 *
 * @param mixed $slot Image slot value.
 * @return array{url:string,alt:string}
 */
$resolve_slot = static function ( $slot ): array {
	if ( is_array( $slot ) ) {
		return array(
			'url' => isset( $slot['url'] ) ? (string) $slot['url'] : '',
			'alt' => isset( $slot['alt'] ) ? (string) $slot['alt'] : '',
		);
	}

	return array(
		'url' => (string) $slot,
		'alt' => '',
	);
};
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( '' !== $back_link_label && '' !== $back_link_url ) : ?>
		<div class="cwc-room-gallery__back">
			<a class="cwc-room-gallery__back-link" href="<?php echo esc_url( $back_link_url ); ?>">
				<span aria-hidden="true">&larr;</span>
				<span><?php echo esc_html( $back_link_label ); ?></span>
			</a>
		</div>
	<?php endif; ?>

	<div class="cwc-room-gallery__grid" data-images="<?php echo esc_attr( min( 4, count( $images ) ) ); ?>">
		<?php
		$positions = array( 'main', 'center', 'top', 'bottom' );
		foreach ( $positions as $index => $position ) :
			$slot = $resolve_slot( $slots[ $index ] );
			if ( '' === $slot['url'] ) {
				continue;
			}
			$tile_class = 'cwc-room-gallery__tile cwc-room-gallery__tile--' . $position;
			?>
			<figure class="<?php echo esc_attr( $tile_class ); ?>">
				<img class="cwc-room-gallery__image" src="<?php echo esc_url( $slot['url'] ); ?>"
					alt="<?php echo esc_attr( $slot['alt'] ); ?>" loading="lazy" />

				<?php if ( 'main' === $position && '' !== $see_all_label ) : ?>
					<?php if ( '' !== $see_all_url ) : ?>
						<a class="cwc-room-gallery__see-all js-cwc-open-gallery-modal" href="<?php echo esc_url( $see_all_url ); ?>">
							<img class="cwc-room-gallery__see-all-icon" src="<?php echo esc_url( $see_all_icon_src ); ?>" alt=""
								width="18" height="18" loading="lazy" decoding="async" aria-hidden="true" />
							<span><?php echo esc_html( $see_all_label ); ?></span>
						</a>
					<?php else : ?>
						<span class="cwc-room-gallery__see-all js-cwc-open-gallery-modal" style="cursor: pointer;" aria-hidden="true">
							<img class="cwc-room-gallery__see-all-icon" src="<?php echo esc_url( $see_all_icon_src ); ?>" alt=""
								width="18" height="18" loading="lazy" decoding="async" />
							<span><?php echo esc_html( $see_all_label ); ?></span>
						</span>
					<?php endif; ?>
				<?php endif; ?>
			</figure>
		<?php endforeach; ?>
	</div>

	<!-- Modal for See All Images -->
	<div id="cwc-gallery-grid-modal" class="cwc-gallery-modal" aria-hidden="true">
		<div class="cwc-gallery-modal__overlay"></div>
		<div class="cwc-gallery-modal__container">
			<div class="cwc-gallery-modal__header">
				<button class="cwc-gallery-modal__close" aria-label="Close gallery">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
				</button>
			</div>
			<div class="cwc-gallery-modal__content">
				<div class="cwc-gallery-modal__grid">
					<?php
					foreach ( $images as $img ) :
						$img_data = $resolve_slot( $img );
						if ( empty( $img_data['url'] ) ) {
							continue;
						}
						?>
						<div class="cwc-gallery-modal__item">
							<img class="cwc-gallery-modal__img" src="<?php echo esc_url( $img_data['url'] ); ?>" alt="<?php echo esc_attr( $img_data['alt'] ); ?>" loading="lazy">
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</section>