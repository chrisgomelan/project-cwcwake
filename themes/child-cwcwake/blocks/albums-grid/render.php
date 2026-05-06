<?php
/**
 * Render template for the cwc/albums-grid block.
 *
 * Two stacked sections, both driven by the `cwc_album` CPT:
 *
 *   1. Standard grid — a 2-column grid of album cover cards.
 *      Used as the main listing on `/gallery/` and on parent albums
 *      to show their child albums.
 *
 *   2. MORE ALBUMS — an optional featured row of wide cards with
 *      title + photo count overlaid on the cover image. Matches the
 *      "MORE ALBUMS" treatment in `designs/albums-landing-design.md`.
 *
 * Counts on each card:
 *   - When the album has child albums → "N ALBUMS"
 *   - Else                            → "N PHOTOS"
 * Editors can force one mode via the `countMode` attribute.
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

$parent_id          = isset( $attributes['parentId'] ) ? (int) $attributes['parentId'] : 0;
$show_standard_grid = ! isset( $attributes['showStandardGrid'] ) || (bool) $attributes['showStandardGrid'];
$standard_limit     = isset( $attributes['standardLimit'] ) ? (int) $attributes['standardLimit'] : 0;
$count_mode         = isset( $attributes['countMode'] ) ? (string) $attributes['countMode'] : 'auto';
$show_more_section  = ! empty( $attributes['showMoreSection'] );
$more_title         = isset( $attributes['moreTitle'] ) ? (string) $attributes['moreTitle'] : '';
$more_limit         = isset( $attributes['moreLimit'] ) ? (int) $attributes['moreLimit'] : 2;
$more_order_by      = isset( $attributes['moreOrderBy'] ) ? (string) $attributes['moreOrderBy'] : 'latest';

/*
 * On a singular album page with `parentId = -1` (sentinel) we
 * resolve the current post as the parent so the same block markup
 * works on both the landing page and on individual album pages.
 */
if ( -1 === $parent_id && is_singular( 'cwc_album' ) ) {
	$parent_id = (int) get_queried_object_id();
}

$albums = cwc_album_get_children( $parent_id );

/*
 * We only return early if we have no albums at all to show in either section.
 * However, we want to allow the "More Section" to fetch from elsewhere if
 * it's meant to be global.
 */
if ( empty( $albums ) && ! $show_more_section ) {
	return;
}

if ( ! function_exists( 'cwc_album_card_count_label' ) ) {
	/**
	 * Format the count label for an album card.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $album Album post.
	 * @param string  $mode  Count mode: `auto`, `photos`, or `albums`.
	 * @return string Localised label, e.g. "6 PHOTOS" / "3 ALBUMS".
	 */
	function cwc_album_card_count_label( WP_Post $album, $mode = 'auto' ) {
		$child_count = cwc_album_child_count( (int) $album->ID );
		$photo_count = cwc_album_photo_count( (int) $album->ID );

		if ( 'photos' === $mode ) {
			return sprintf(
				/* translators: %d: Number of photos. */
				_n( '%d Photo', '%d Photos', $photo_count, 'child-cwcwake' ),
				$photo_count
			);
		}
		if ( 'albums' === $mode ) {
			return sprintf(
				/* translators: %d: Number of child albums. */
				_n( '%d Album', '%d Albums', $child_count, 'child-cwcwake' ),
				$child_count
			);
		}

		// Auto: prefer the more meaningful number.
		if ( $child_count > 0 ) {
			return sprintf(
				/* translators: %d: Number of child albums. */
				_n( '%d Album', '%d Albums', $child_count, 'child-cwcwake' ),
				$child_count
			);
		}
		return sprintf(
			/* translators: %d: Number of photos. */
			_n( '%d Photo', '%d Photos', $photo_count, 'child-cwcwake' ),
			$photo_count
		);
	}
}

if ( ! function_exists( 'cwc_album_cover_url' ) ) {
	/**
	 * Resolve the cover image URL for an album.
	 *
	 * Prefers the featured image; falls back to the album's first
	 * uploaded photo so editors don't have to set both.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $album Album post.
	 * @param string  $size  Image size (default: `large`).
	 * @return string Cover image URL or empty string.
	 */
	function cwc_album_cover_url( WP_Post $album, $size = 'large' ) {
		if ( has_post_thumbnail( $album ) ) {
			return get_the_post_thumbnail_url( $album, $size );
		}

		$photos = cwc_album_get_photos( (int) $album->ID );
		if ( ! empty( $photos ) ) {
			$src = wp_get_attachment_image_url( $photos[0]->ID, $size );
			if ( $src ) {
				return $src;
			}
		}
		return '';
	}
}

/*
 * Pick out the featured albums for the MORE ALBUMS row first so
 * they don't appear twice. Order strategies map directly onto
 * `WP_Query` ordering primitives.
 */

/*
 * Resolve the current "context" ID to exclude it from "More" sections.
 * If we're on a singular album, we don't want to show that album (or its
 * top-level parent if we're showing root albums) in the featured row.
 */
$current_root_id = 0;
if ( is_singular( 'cwc_album' ) ) {
	$ancestors = get_post_ancestors( get_the_ID() );
	if ( ! empty( $ancestors ) ) {
		$current_root_id = (int) end( $ancestors );
	} else {
		$current_root_id = (int) get_the_ID();
	}
}

$more_ids = array();
if ( $show_more_section && $more_limit > 0 ) {
	// The pool for "More Albums" is either the children (if we have any)
	// or the top-level albums (fallback).
	$more_pool_source = ! empty( $albums ) ? $albums : cwc_album_get_children( 0 );

	// Filter out the current context (self or root ancestor) BEFORE slicing,
	// otherwise we might end up with fewer items than the limit if the
	// current item was picked in the slice.
	$more_pool_source = array_filter(
		$more_pool_source,
		static function ( $album ) use ( $current_root_id ) {
			return (int) $album->ID !== $current_root_id;
		}
	);

	switch ( $more_order_by ) {
		case 'random':
			shuffle( $more_pool_source );
			break;

		case 'menu_order':
			// Already ordered by menu_order via cwc_album_get_children.
			break;

		case 'latest':
		default:
			usort(
				$more_pool_source,
				static function ( $a, $b ) {
					return strtotime( $b->post_date ) <=> strtotime( $a->post_date );
				}
			);
			break;
	}

	$more_ids = array_slice( wp_list_pluck( $more_pool_source, 'ID' ), 0, $more_limit );
}

$more_ids      = array_map( 'intval', $more_ids );
$standard_pool = array_values(
	array_filter(
		$albums,
		static function ( $album ) use ( $more_ids ) {
			return ! in_array( (int) $album->ID, $more_ids, true );
		}
	)
);
if ( $standard_limit > 0 ) {
	$standard_pool = array_slice( $standard_pool, 0, $standard_limit );
}

$more_pool = array();
if ( ! empty( $more_ids ) ) {
	$all_possible = ! empty( $albums ) ? $albums : cwc_album_get_children( 0 );
	$more_pool    = array_values(
		array_filter(
			$all_possible,
			static function ( $album ) use ( $more_ids ) {
				return in_array( (int) $album->ID, $more_ids, true );
			}
		)
	);
	usort(
		$more_pool,
		static function ( $a, $b ) use ( $more_ids ) {
			return array_search( (int) $a->ID, $more_ids, true ) <=> array_search( (int) $b->ID, $more_ids, true );
		}
	);
}

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'cwc-albums' ) );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php if ( $show_standard_grid && ! empty( $standard_pool ) ) : ?>
		<ul class="cwc-albums__grid">
			<?php
			foreach ( $standard_pool as $album ) :
				$is_single   = ( 1 === count( $standard_pool ) );
				$cover       = cwc_album_cover_url( $album, $is_single ? 'full' : 'large' );
				$label       = cwc_album_card_count_label( $album, $count_mode );
				$href        = get_permalink( $album );
				$album_title = get_the_title( $album );
				?>
				<li class="cwc-albums__item">
					<a class="cwc-albums__card" href="<?php echo esc_url( $href ); ?>" aria-label="<?php echo esc_attr( $album_title . ' — ' . $label ); ?>">
						<div class="cwc-albums__media">
							<?php if ( '' !== $cover ) : ?>
								<img
									class="cwc-albums__img"
									src="<?php echo esc_url( $cover ); ?>"
									alt="<?php echo esc_attr( $album_title ); ?>"
									loading="lazy"
									decoding="async"
								/>
							<?php endif; ?>

							<span class="cwc-albums__overlay" aria-hidden="true"></span>

							<span class="cwc-albums__corner cwc-albums__corner--tl" aria-hidden="true"></span>
							<span class="cwc-albums__corner cwc-albums__corner--tr" aria-hidden="true"></span>
							<span class="cwc-albums__corner cwc-albums__corner--bl" aria-hidden="true"></span>
							<span class="cwc-albums__corner cwc-albums__corner--br" aria-hidden="true"></span>
						</div>

						<div class="cwc-albums__meta">
							<h3 class="cwc-albums__title"><?php echo esc_html( $album_title ); ?></h3>
							<span class="cwc-albums__count"><?php echo esc_html( $label ); ?></span>
						</div>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if ( $show_more_section && ! empty( $more_pool ) ) : ?>
		<div class="cwc-albums__more">
			<?php if ( '' !== trim( $more_title ) ) : ?>
				<h2 class="cwc-albums__more-title"><?php echo esc_html( $more_title ); ?></h2>
			<?php endif; ?>

			<ul class="cwc-albums__featured-list">
				<?php
				foreach ( $more_pool as $album ) :
					$cover       = cwc_album_cover_url( $album, 'full' );
					$label       = cwc_album_card_count_label( $album, $count_mode );
					$href        = get_permalink( $album );
					$album_title = get_the_title( $album );
					?>
					<li class="cwc-albums__featured-item">
						<a class="cwc-albums__featured-card" href="<?php echo esc_url( $href ); ?>" aria-label="<?php echo esc_attr( $album_title . ' — ' . $label ); ?>">
							<?php if ( '' !== $cover ) : ?>
								<img
									class="cwc-albums__featured-img"
									src="<?php echo esc_url( $cover ); ?>"
									alt="<?php echo esc_attr( $album_title ); ?>"
									loading="lazy"
									decoding="async"
								/>
							<?php endif; ?>

							<span class="cwc-albums__featured-overlay" aria-hidden="true"></span>

							<span class="cwc-albums__corner cwc-albums__corner--tl cwc-albums__corner--light" aria-hidden="true"></span>
							<span class="cwc-albums__corner cwc-albums__corner--tr cwc-albums__corner--light" aria-hidden="true"></span>
							<span class="cwc-albums__corner cwc-albums__corner--bl cwc-albums__corner--light" aria-hidden="true"></span>
							<span class="cwc-albums__corner cwc-albums__corner--br cwc-albums__corner--light" aria-hidden="true"></span>

							<div class="cwc-albums__featured-content">
								<h3 class="cwc-albums__featured-title"><?php echo esc_html( $album_title ); ?></h3>
								<span class="cwc-albums__featured-count"><?php echo esc_html( $label ); ?></span>
							</div>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
</section>
