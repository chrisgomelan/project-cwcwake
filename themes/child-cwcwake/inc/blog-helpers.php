<?php
/**
 * CWC Wake — Blog block helpers.
 *
 * Shared utilities consumed by the three Blogs page blocks
 * (`cwc/featured-blogs`, `cwc/upcoming-events`, `cwc/all-blogs`).
 * Lives outside of any individual block's `render.php` so the
 * functions are declared exactly once per request — defining
 * helpers inside a render template would re-declare them on
 * every block render (fatal once two blocks land on the same page).
 *
 * The helpers wrap small WordPress idioms (excerpt building, image
 * URL resolution, blog page URL discovery) so the per-block
 * templates stay focused on markup.
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve a card background image URL for a blog post.
 *
 * Prefers the post's featured image at the requested size and
 * falls back to a caller-supplied placeholder so freshly seeded
 * posts (which have no thumbnail yet) still paint a card with
 * imagery instead of a flat color block.
 *
 * @since 1.0.0
 *
 * @param int    $post_id     Post ID to look up.
 * @param string $placeholder Fallback image URL when no thumbnail is set.
 * @param string $size        Optional. WordPress image size slug. Default `large`.
 * @return string Resolved image URL or empty string when neither source has a value.
 */
function cwc_blog_card_image_url( int $post_id, string $placeholder = '', string $size = 'large' ): string {
	$thumb_id = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb_id > 0 ) {
		$src = wp_get_attachment_image_url( $thumb_id, $size );
		if ( is_string( $src ) && '' !== $src ) {
			return $src;
		}
	}

	return $placeholder;
}

/**
 * Build a trimmed, single-line excerpt for a blog card.
 *
 * Honors a manually-authored `post_excerpt` first (editors win
 * over auto-generation), then falls back to a word-count-trimmed
 * version of `post_content` with shortcodes / blocks stripped.
 * Returns plain text — callers must escape on output.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post  Post being rendered.
 * @param int     $words Maximum word count for auto-trimmed excerpts.
 * @return string Plain-text excerpt safe to drop into a card.
 */
function cwc_blog_card_excerpt( WP_Post $post, int $words = 22 ): string {
	$manual = trim( (string) $post->post_excerpt );
	if ( '' !== $manual ) {
		return $manual;
	}

	$raw = strip_shortcodes( (string) $post->post_content );
	$raw = wp_strip_all_tags( $raw );

	return wp_trim_words( $raw, max( 1, $words ), '…' );
}

/**
 * Return the canonical Blogs landing URL.
 *
 * The site seeds Blogs as a child of "Plan Your Trip", so the
 * permalink is conventionally `/plan-your-trip/blogs/`. We resolve
 * it dynamically by slug + parent walk so a future restructure
 * (e.g. promoting Blogs to a top-level page) doesn't break links.
 * Falls back to `/blogs/` when the page has not been created yet.
 *
 * @since 1.0.0
 *
 * @return string Absolute or root-relative URL to the Blogs page.
 */
function cwc_blog_landing_url(): string {
	$page = get_page_by_path( 'plan-your-trip/blogs' );
	if ( $page instanceof WP_Post ) {
		return (string) get_permalink( $page );
	}

	$page = get_page_by_path( 'blogs' );
	if ( $page instanceof WP_Post ) {
		return (string) get_permalink( $page );
	}

	return home_url( '/blogs/' );
}

/**
 * Inject a "Blogs" parent crumb on single blog posts.
 *
 * `cwc_build_breadcrumbs()` only emits `Home → <post title>` for
 * `is_singular()` results, which leaves single posts dangling
 * under Home with no clear way back to the listing page. This
 * filter splices a `Blogs` crumb in just before the current title
 * so navigation matches the menu structure.
 *
 * Only runs for the built-in `post` type — CPTs (`accommodation`,
 * `cwc_album`) inject their own parent crumbs from their
 * respective modules.
 *
 * @since 1.0.0
 *
 * @param array $crumbs Existing crumb list (each item: `label`, `url`).
 * @return array Crumbs with the "Blogs" parent inserted.
 */
function cwc_blog_inject_breadcrumb( array $crumbs ): array {
	if ( ! is_singular( 'post' ) ) {
		return $crumbs;
	}

	$blogs_url = cwc_blog_landing_url();
	if ( '' === $blogs_url ) {
		return $crumbs;
	}

	/*
	 * Splice the "Blogs" crumb directly before the trailing
	 * (current-page) crumb. Using a fixed offset of `count - 1`
	 * keeps the math obvious and avoids a `usort()` rebuild that
	 * would silently drop URL-less items.
	 */
	$last     = array_pop( $crumbs );
	$crumbs[] = array(
		'label' => __( 'Blogs', 'child-cwcwake' ),
		'url'   => $blogs_url,
	);
	$crumbs[] = $last;

	return $crumbs;
}
add_filter( 'cwc_breadcrumbs_items', 'cwc_blog_inject_breadcrumb' );

/**
 * Resolve the permalink to a category archive by name.
 *
 * Used by the All Blogs filter so the dropdown's `<option>` values
 * can be regular permalinks — keeps filtering bookmarkable, history-
 * navigable, and accessible without JS.
 *
 * @since 1.0.0
 *
 * @param string $name Category name (case-sensitive match against `wp_terms.name`).
 * @return string Category permalink, or empty string when the term is missing.
 */
function cwc_blog_category_link_by_name( string $name ): string {
	if ( '' === $name ) {
		return '';
	}

	$term = get_term_by( 'name', $name, 'category' );
	if ( ! $term instanceof WP_Term ) {
		return '';
	}

	$link = get_term_link( $term );

	return is_wp_error( $link ) ? '' : (string) $link;
}
