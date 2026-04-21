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

if (!defined('ABSPATH')) {
	exit;
}

$images = isset($attributes['images']) && is_array($attributes['images']) ? $attributes['images'] : [];
$back_link_label = isset($attributes['backLinkLabel']) ? (string) $attributes['backLinkLabel'] : '';
$back_link_url = isset($attributes['backLinkUrl']) ? (string) $attributes['backLinkUrl'] : '';
$see_all_label = isset($attributes['seeAllLabel']) ? (string) $attributes['seeAllLabel'] : '';
$see_all_url = isset($attributes['seeAllUrl']) ? (string) $attributes['seeAllUrl'] : '';

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
	$post = get_post();
	if ( $post instanceof WP_Post ) {
		if ( empty( $images ) && function_exists( 'cwc_accommodation_gallery_images' ) ) {
			$images = cwc_accommodation_gallery_images( (int) $post->ID );
		}
		if ( '' === $back_link_label ) {
			$back_link_label = __( 'Back to Accommodations', 'child-cwcwake' );
		}
		if ( '' === $back_link_url ) {
			$back_link_url = '/accommodations/';
		}
	}
}

if (empty($images)) {
	return;
}

/*
 * The grid expects exactly 4 image slots; pad with empty entries so the
 * skeleton still renders if the editor supplies fewer images.
 */
$slots = array_pad(array_slice($images, 0, 4), 4, '');

$wrapper_attrs = get_block_wrapper_attributes(['class' => 'cwc-room-gallery']);

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
$resolve_slot = static function ($slot): array {
	if (is_array($slot)) {
		return [
			'url' => isset($slot['url']) ? (string) $slot['url'] : '',
			'alt' => isset($slot['alt']) ? (string) $slot['alt'] : '',
		];
	}

	return [
		'url' => (string) $slot,
		'alt' => '',
	];
};
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ($back_link_label !== '' && $back_link_url !== ''): ?>
		<div class="cwc-room-gallery__back">
			<a class="cwc-room-gallery__back-link" href="<?php echo esc_url($back_link_url); ?>">
				<span aria-hidden="true">&larr;</span>
				<span><?php echo esc_html($back_link_label); ?></span>
			</a>
		</div>
	<?php endif; ?>

	<div class="cwc-room-gallery__grid">
		<?php
		$positions = ['main', 'center', 'top', 'bottom'];
		foreach ($positions as $index => $position):
			$slot = $resolve_slot($slots[$index]);
			if ($slot['url'] === '') {
				continue;
			}
			$tile_class = 'cwc-room-gallery__tile cwc-room-gallery__tile--' . $position;
			?>
			<figure class="<?php echo esc_attr($tile_class); ?>">
				<img class="cwc-room-gallery__image" src="<?php echo esc_url($slot['url']); ?>"
					alt="<?php echo esc_attr($slot['alt']); ?>" loading="lazy" />

				<?php if ($position === 'main' && $see_all_label !== ''): ?>
					<?php if ($see_all_url !== ''): ?>
						<a class="cwc-room-gallery__see-all" href="<?php echo esc_url($see_all_url); ?>">
							<img class="cwc-room-gallery__see-all-icon" src="<?php echo esc_url($see_all_icon_src); ?>" alt=""
								width="18" height="18" loading="lazy" decoding="async" aria-hidden="true" />
							<span><?php echo esc_html($see_all_label); ?></span>
						</a>
					<?php else: ?>
						<span class="cwc-room-gallery__see-all" aria-hidden="true">
							<img class="cwc-room-gallery__see-all-icon" src="<?php echo esc_url($see_all_icon_src); ?>" alt=""
								width="18" height="18" loading="lazy" decoding="async" />
							<span><?php echo esc_html($see_all_label); ?></span>
						</span>
					<?php endif; ?>
				<?php endif; ?>
			</figure>
		<?php endforeach; ?>
	</div>
</section>