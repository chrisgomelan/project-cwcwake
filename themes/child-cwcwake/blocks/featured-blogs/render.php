<?php
/**
 * Featured Blogs block — server render template.
 *
 * Renders the asymmetric 5-card hero grid documented in
 * `designs/blogs-design.md` § 1.
 *
 * Data source priority:
 *
 *   1. Up to 5 most-recent posts that carry the
 *      `_cwc_blog_featured = 1` meta flag (set by
 *      `cwc_seed_blog_posts()` and editable per-post).
 *   2. If the flagged set returns fewer than 5, the gap is back-filled
 *      with the most recent published posts that aren't already in
 *      the result. This keeps the layout intact even before any
 *      editor flips the featured switch.
 *
 * The grid layout (1 large + 4 medium) is fixed by design — the
 * block intentionally does not expose a "card count" attribute, so
 * adding more featured posts in the admin won't quietly break the
 * mockup.
 *
 * @package CWC_Wake
 * @since   1.0.0
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Helper declarations live above the markup so they're available
 * on the very first render. Wrapping in `function_exists()` keeps
 * us safe if the file is ever required twice (e.g. block reused
 * on the same page) — but the guard prevents PHP's normal
 * top-of-file function hoisting, which is why they cannot live at
 * the bottom of the file.
 */
if ( ! function_exists( 'cwc_featured_blogs_query' ) ) :
	/**
	 * Resolve up to N posts to render in the Featured Blogs grid.
	 *
	 * Builds the result in two passes: featured-flagged posts first
	 * (newest → oldest), then a back-fill pass that pulls the latest
	 * published posts not already in the set. This keeps the design
	 * intact on a fresh install where nothing has been flagged yet,
	 * while still respecting editorial intent once the flag is used.
	 *
	 * @since 1.0.0
	 *
	 * @param int $limit Maximum number of posts to return.
	 * @return WP_Post[] Ordered list of WP_Post objects (newest first).
	 */
	function cwc_featured_blogs_query( int $limit ): array {
		if ( $limit <= 0 ) {
			return array();
		}

		$meta_key = defined( 'CWC_BLOG_META_FEATURED' ) ? CWC_BLOG_META_FEATURED : '_cwc_blog_featured';

		$featured = get_posts(
			array(
				'post_type'              => 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => $limit,
				'orderby'                => 'date',
				'order'                  => 'ASC',
				'meta_key'               => $meta_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'             => '1',
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
			)
		);

		if ( count( $featured ) >= $limit ) {
			return $featured;
		}

		$exclude  = wp_list_pluck( $featured, 'ID' );
		$fallback = get_posts(
			array(
				'post_type'              => 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => $limit - count( $featured ),
				'orderby'                => 'date',
				'order'                  => 'DESC',
				'post__not_in'           => $exclude,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
			)
		);

		return array_merge( $featured, $fallback );
	}
endif;

$heading           = isset( $attributes['heading'] ) ? (string) $attributes['heading'] : '';
$heading_highlight = isset( $attributes['headingHighlight'] ) ? (string) $attributes['headingHighlight'] : '';
$description       = isset( $attributes['description'] ) ? (string) $attributes['description'] : '';
$read_more_label   = isset( $attributes['readMoreLabel'] ) ? (string) $attributes['readMoreLabel'] : __( 'Read More', 'child-cwcwake' );
$placeholder       = isset( $attributes['placeholderImage'] ) ? (string) $attributes['placeholderImage'] : '';

$featured_posts = cwc_featured_blogs_query( 5 );

if ( empty( $featured_posts ) ) {
	return;
}

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'cwc-featured-blogs' ) );

/*
 * The grid hard-codes 5 slots so the design's asymmetric layout
 * (1 large + 4 medium) stays intact. If a site ends up with fewer
 * than 5 published posts in total, the missing slots simply
 * collapse — preferable to silently rearranging the layout.
 */
$slots = array_slice( $featured_posts, 0, 5 );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<header class="cwc-featured-blogs__header">
		<?php if ( '' !== $heading || '' !== $heading_highlight ) : ?>
			<h2 class="cwc-featured-blogs__title">
				<?php if ( '' !== $heading ) : ?>
					<span class="cwc-featured-blogs__title-text"><?php echo esc_html( $heading ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $heading_highlight ) : ?>
					<em class="cwc-featured-blogs__title-highlight"><?php echo esc_html( $heading_highlight ); ?></em>
				<?php endif; ?>
			</h2>
		<?php endif; ?>

		<?php if ( '' !== $description ) : ?>
			<p class="cwc-featured-blogs__description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	</header>

	<div class="cwc-featured-blogs__grid">
		<?php
		foreach ( $slots as $index => $post_obj ) :
			$current_post_id = (int) $post_obj->ID;
			$image           = cwc_blog_card_image_url( $current_post_id, $placeholder );
			$post_title      = get_the_title( $current_post_id );
			$excerpt         = cwc_blog_card_excerpt( $post_obj, 22 );
			$date            = get_the_date( 'M j, Y', $current_post_id );
			$url             = (string) get_permalink( $current_post_id );

			/*
			 * Slot index drives the BEM modifier so the CSS grid can
			 * place each card without inline style attributes — keeps
			 * presentation in style.css where editors can override.
			 */
			$slot_class = 'cwc-featured-blogs__card cwc-featured-blogs__card--slot-' . ( $index + 1 );
			?>
			<article class="<?php echo esc_attr( $slot_class ); ?>">
				<a class="cwc-featured-blogs__link" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( $post_title ); ?>">
					<?php if ( '' !== $image ) : ?>
						<span class="cwc-featured-blogs__image" role="img"
							aria-label="<?php echo esc_attr( $post_title ); ?>"
							style="background-image:url('<?php echo esc_url( $image ); ?>');"></span>
					<?php endif; ?>
					<span class="cwc-featured-blogs__overlay" aria-hidden="true"></span>

					<div class="cwc-featured-blogs__content">
						<h3 class="cwc-featured-blogs__card-title"><?php echo esc_html( $post_title ); ?></h3>

						<?php if ( '' !== $excerpt ) : ?>
							<p class="cwc-featured-blogs__card-excerpt"><?php echo esc_html( $excerpt ); ?></p>
						<?php endif; ?>

						<span class="cwc-featured-blogs__cta"><?php echo esc_html( $read_more_label ); ?></span>
					</div>

					<?php if ( '' !== $date ) : ?>
						<span class="cwc-featured-blogs__date"><?php echo esc_html( $date ); ?></span>
					<?php endif; ?>
				</a>
			</article>
			<?php
		endforeach;
		?>
	</div>
</section>

