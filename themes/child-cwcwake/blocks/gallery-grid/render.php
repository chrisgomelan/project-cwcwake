<?php
/**
 * CWC Wake — Gallery Grid block render template.
 *
 * Renders a 12-column grid of gallery category cards. Each card is a
 * rounded image with decorative L-shaped corner brackets, a category
 * name, and an album count — matching `designs/gallery-design.md`.
 *
 * Per-item `width` ("half" | "full") controls the column span so a
 * row can mix two half-width cards followed by a single full-width
 * card (Events + Lifestyle, then Explore CamSur).
 *
 * Each item:
 *   - title       (string) Category name displayed under the image.
 *   - image       (string) Image URL.
 *   - albumCount  (string) Free-form label, e.g. "6 ALBUMS".
 *   - url         (string) Optional link target. When empty, the card
 *                          is rendered as a non-clickable <div>.
 *   - width       (string) "half" (default) or "full".
 *
 * @package CWC_Wake
 * @since   1.0.0
 *
 * @var array $attributes Block attributes passed in by WordPress.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items = $attributes['items'] ?? [];

if ( empty( $items ) ) {
	return;
}

$wrapper_attrs = get_block_wrapper_attributes( [
	'class' => 'cwc-gallery-grid',
] );
?>

<section <?php echo $wrapper_attrs; ?>>
	<ul class="cwc-gallery-grid__list">
		<?php foreach ( $items as $item ) :
			$title       = $item['title']       ?? '';
			$image       = $item['image']       ?? '';
			$album_count = $item['albumCount']  ?? '';
			$url         = $item['url']         ?? '';
			$width       = ( ( $item['width'] ?? 'half' ) === 'full' ) ? 'full' : 'half';
			$item_class  = 'cwc-gallery-grid__item cwc-gallery-grid__item--' . $width;
			$is_link     = ! empty( $url );
			$tag         = $is_link ? 'a' : 'div';
			$href_attr   = $is_link ? sprintf( ' href="%s"', esc_url( $url ) ) : '';
		?>
			<li class="<?php echo esc_attr( $item_class ); ?>">
				<<?php echo $tag; ?> class="cwc-gallery-grid__card"<?php echo $href_attr; ?>>
					<div class="cwc-gallery-grid__media">
						<?php if ( ! empty( $image ) ) : ?>
							<img
								class="cwc-gallery-grid__img"
								src="<?php echo esc_url( $image ); ?>"
								alt="<?php echo esc_attr( $title ); ?>"
								loading="lazy"
							/>
						<?php endif; ?>

						<span class="cwc-gallery-grid__overlay" aria-hidden="true"></span>

						<span class="cwc-gallery-grid__corner cwc-gallery-grid__corner--tl" aria-hidden="true"></span>
						<span class="cwc-gallery-grid__corner cwc-gallery-grid__corner--tr" aria-hidden="true"></span>
						<span class="cwc-gallery-grid__corner cwc-gallery-grid__corner--bl" aria-hidden="true"></span>
						<span class="cwc-gallery-grid__corner cwc-gallery-grid__corner--br" aria-hidden="true"></span>
					</div>

					<?php if ( ! empty( $title ) || ! empty( $album_count ) ) : ?>
						<div class="cwc-gallery-grid__meta">
							<?php if ( ! empty( $title ) ) : ?>
								<h3 class="cwc-gallery-grid__title"><?php echo esc_html( $title ); ?></h3>
							<?php endif; ?>

							<?php if ( ! empty( $album_count ) ) : ?>
								<span class="cwc-gallery-grid__count"><?php echo esc_html( $album_count ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</<?php echo $tag; ?>>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
