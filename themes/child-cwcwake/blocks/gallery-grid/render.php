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
 *   - albumCount  (string) Free-form label override, e.g. "6 ALBUMS".
 *                          Leave empty when `albumSlug`/`albumId` is
 *                          set so the count is resolved live.
 *   - albumSlug   (string) Optional `cwc_album` slug. When provided
 *                          (and `albumCount` is empty) the count is
 *                          read live from the CPT via
 *                          `cwc_album_child_count()` so the landing
 *                          card stays in sync with the editor.
 *   - albumId     (int)    Same as `albumSlug` but by ID. Wins over
 *                          `albumSlug` when both are present.
 *   - url         (string) Optional link override. When omitted the
 *                          card link is resolved live from the
 *                          wired album's permalink so the card never
 *                          falls behind if WordPress bumps the slug
 *                          (e.g. `events` → `events-2` after a
 *                          collision). When neither `url` nor a
 *                          published album is found, the card
 *                          renders as a non-clickable <div>.
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

$items = $attributes['items'] ?? array();

if ( empty( $items ) ) {
	return;
}

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'class' => 'cwc-gallery-grid',
	)
);
?>

<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<ul class="cwc-gallery-grid__list">
		<?php
		foreach ( $items as $item ) :
			$item_title     = $item['title'] ?? '';
			$image          = $item['image'] ?? '';
			$album_count    = $item['albumCount'] ?? '';
			$album_slug     = $item['albumSlug'] ?? '';
			$album_id       = isset( $item['albumId'] ) ? (int) $item['albumId'] : 0;
			$url            = $item['url'] ?? '';
			$width          = ( ( $item['width'] ?? 'half' ) === 'full' ) ? 'full' : 'half';
			$object_position = $item['objectPosition'] ?? '';
			$item_class     = 'cwc-gallery-grid__item cwc-gallery-grid__item--' . $width;

			/*
			 * Resolve the published cwc_album behind this card once,
			 * up front. We use the same lookup for two things:
			 *
			 *   1. The live "N Albums" count.
			 *   2. The card's link target — using `get_permalink()`
			 *      means the link follows the post even if WordPress
			 *      had to bump its slug to avoid a collision (e.g.
			 *      `events` → `events-2`). Hardcoded `url` strings
			 *      don't survive that, but a permalink lookup does.
			 *
			 * Lookup is restricted to `post_status = publish` so
			 * trashed / draft / deleted categories never contribute a
			 * misleading "0 ALBUMS" label nor a broken link target.
			 */
			$resolved_id = 0;

			if ( $album_id > 0 ) {
				$candidate = get_post( $album_id );
				if ( $candidate instanceof WP_Post && 'cwc_album' === $candidate->post_type && 'publish' === $candidate->post_status ) {
					$resolved_id = (int) $candidate->ID;
				}
			}

			if ( 0 === $resolved_id && '' !== $album_slug ) {
				$matches = get_posts(
					array(
						'name'             => $album_slug,
						'post_type'        => 'cwc_album',
						'post_status'      => 'publish',
						'posts_per_page'   => 1,
						'fields'           => 'ids',
						'no_found_rows'    => true,
						'suppress_filters' => false,
					)
				);
				if ( ! empty( $matches ) ) {
					$resolved_id = (int) $matches[0];
				}
			}

			// Live count fallback (only if the editor didn't override).
			if ( '' === $album_count && $resolved_id > 0 && function_exists( 'cwc_album_child_count' ) ) {
				$child_count = cwc_album_child_count( $resolved_id );
				$album_count = sprintf(
					/* translators: %d: Number of albums inside a category. */
					_n( '%d Album', '%d Albums', $child_count, 'child-cwcwake' ),
					$child_count
				);
			}

			/*
			 * Link target precedence:
			 *   1. Resolved cwc_album permalink (always current).
			 *   2. Editor-supplied `url` override (back-compat for
			 *      non-CPT cards or hand-curated destinations).
			 *
			 * Permalink wins so the card never lags behind a slug
			 * change. Drop to the override only when no album was
			 * resolved.
			 */
			if ( $resolved_id > 0 ) {
				$url = (string) get_permalink( $resolved_id );
			}

			$is_link   = ! empty( $url );
			$html_tag  = $is_link ? 'a' : 'div';
			$href_attr = $is_link ? sprintf( ' href="%s"', esc_url( $url ) ) : '';
			?>
			<li class="<?php echo esc_attr( $item_class ); ?>">
				<<?php echo $html_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="cwc-gallery-grid__card"<?php echo $href_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<div class="cwc-gallery-grid__media">
						<?php if ( ! empty( $image ) ) : ?>
							<img
								class="cwc-gallery-grid__img"
								src="<?php echo esc_url( $image ); ?>"
								alt="<?php echo esc_attr( $item_title ); ?>"
								loading="lazy"
								<?php if ( ! empty( $object_position ) ) : ?>
									style="object-position: <?php echo esc_attr( $object_position ); ?>;"
								<?php endif; ?>
							/>
						<?php endif; ?>

						<span class="cwc-gallery-grid__overlay" aria-hidden="true"></span>

						<span class="cwc-gallery-grid__corner cwc-gallery-grid__corner--tl" aria-hidden="true"></span>
						<span class="cwc-gallery-grid__corner cwc-gallery-grid__corner--tr" aria-hidden="true"></span>
						<span class="cwc-gallery-grid__corner cwc-gallery-grid__corner--bl" aria-hidden="true"></span>
						<span class="cwc-gallery-grid__corner cwc-gallery-grid__corner--br" aria-hidden="true"></span>
					</div>

					<?php if ( ! empty( $item_title ) || ! empty( $album_count ) ) : ?>
						<div class="cwc-gallery-grid__meta">
							<?php if ( ! empty( $item_title ) ) : ?>
								<h3 class="cwc-gallery-grid__title"><?php echo esc_html( $item_title ); ?></h3>
							<?php endif; ?>

							<?php if ( ! empty( $album_count ) ) : ?>
								<span class="cwc-gallery-grid__count"><?php echo esc_html( $album_count ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</<?php echo $html_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
