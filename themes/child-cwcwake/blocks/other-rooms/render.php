<?php
/**
 * Render template for the cwc/other-rooms block.
 *
 * Renders a card with a blue accent bar, an "Other Rooms" heading, and
 * a row of sibling room thumbnails. Each thumbnail is a full-bleed
 * image with a translucent overlay and a centered uppercase label.
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

$heading = isset($attributes['heading']) ? (string) $attributes['heading'] : '';
$items = isset($attributes['items']) && is_array($attributes['items']) ? $attributes['items'] : [];

/*
 * Meta fallback (room-management-transition.md § 4):
 *
 *   - When no items were entered into the block UI and we're on a
 *     single accommodation post, query the other published rooms
 *     and render a card per result.
 *   - The current room is excluded so the rail doesn't link back
 *     to itself; results are sorted by `menu_order` then title for
 *     a deterministic order an editor can shuffle from the post
 *     attributes panel.
 *
 * Limit of 6 keeps the rail scannable on wide screens and matches
 * the four-card design baseline plus a little headroom for sites
 * with more rooms.
 */
if ( empty( $items ) && function_exists( 'cwc_is_accommodation_context' ) && cwc_is_accommodation_context() ) {
	$current = get_post();
	$current_id = $current instanceof WP_Post ? (int) $current->ID : 0;

	$siblings = get_posts(
		[
			'post_type'      => 'accommodation',
			'post_status'    => 'publish',
			'posts_per_page' => 6,
			'post__not_in'   => $current_id > 0 ? [ $current_id ] : [],
			'orderby'        => [ 'menu_order' => 'ASC', 'title' => 'ASC' ],
			'no_found_rows'  => true,
		]
	);

	foreach ( $siblings as $sibling ) {
		$thumb_id = (int) get_post_thumbnail_id( $sibling );
		$image    = $thumb_id > 0 ? (string) wp_get_attachment_image_url( $thumb_id, 'large' ) : '';
		$items[]  = [
			'label' => get_the_title( $sibling ),
			'image' => $image,
			'url'   => (string) get_permalink( $sibling ),
		];
	}

	if ( '' === $heading ) {
		$heading = __( 'Other Rooms', 'child-cwcwake' );
	}
}

if (empty($items)) {
	return;
}

$wrapper_attrs = get_block_wrapper_attributes(['class' => 'cwc-other-rooms']);
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="cwc-other-rooms__panel">
		<span class="cwc-other-rooms__accent" aria-hidden="true"></span>

		<div class="cwc-other-rooms__body">
			<?php if ($heading !== ''): ?>
				<h2 class="cwc-other-rooms__heading"><?php echo esc_html($heading); ?></h2>
			<?php endif; ?>

			<ul class="cwc-other-rooms__list">
				<?php
				foreach ($items as $item) {
					if (!is_array($item)) {
						continue;
					}
					$label = isset($item['label']) ? (string) $item['label'] : '';
					$image = isset($item['image']) ? (string) $item['image'] : '';
					$url = isset($item['url']) ? (string) $item['url'] : '';

					if ($label === '' && $image === '') {
						continue;
					}

					$has_link = $url !== '';
					$tag = $has_link ? 'a' : 'div';
					?>
					<li class="cwc-other-rooms__item">
						<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							class="cwc-other-rooms__card"
							<?php if ($has_link): ?>
								href="<?php echo esc_url($url); ?>"
								aria-label="<?php echo esc_attr($label); ?>"
							<?php endif; ?>
							>
							<?php if ($image !== ''): ?>
								<span class="cwc-other-rooms__image" role="img" <?php echo $label !== '' ? 'aria-label="' . esc_attr($label) . '"' : ''; ?>
									style="background-image:url('<?php echo esc_url($image); ?>');"></span>
							<?php endif; ?>
							<span class="cwc-other-rooms__overlay" aria-hidden="true"></span>
							<?php if ($label !== ''): ?>
								<span class="cwc-other-rooms__label"><?php echo esc_html($label); ?></span>
							<?php endif; ?>
						</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					</li>
					<?php
				}
				?>
			</ul>
		</div>
	</div>
</section>