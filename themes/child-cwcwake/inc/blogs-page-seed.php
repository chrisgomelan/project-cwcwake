<?php
/**
 * One-shot Blogs page seeder.
 *
 * The initial site seed in `cwc_create_initial_pages()` created
 * `Plan Your Trip → Blogs` with the generic `page-child` template,
 * which does NOT render the Blogs page sections. This file:
 *
 *   1. Forces `_wp_page_template = page-blogs` on the existing
 *      page so the block-template hierarchy picks up
 *      `templates/page-blogs.html`.
 *   2. Leaves `post_content` empty by design. Every section on the
 *      page (`cwc/featured-blogs`, `cwc/upcoming-events`,
 *      `cwc/all-blogs`) lives in the template itself and pulls
 *      its own data from posts + meta — there is nothing to seed
 *      into the page body, and forcing block markup here would
 *      create the false impression that editors should manage
 *      blog rollups inside the page editor.
 *
 * Runs once on `init`, guarded by `cwc_blogs_page_seeded`. Looks
 * the page up by path so the same seeder works whether the page
 * lives at `/plan-your-trip/blogs/` (current) or gets promoted to
 * `/blogs/` later.
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Force `page-blogs` on the existing Blogs page.
 *
 * Resolves the page by trying the canonical nested path first,
 * then a top-level fallback. Marks the option with a timestamp
 * (not just `true`) so `wp option get cwc_blogs_page_seeded`
 * shows when the page was reconfigured.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_seed_blogs_page(): void {
	if ( get_option( 'cwc_blogs_page_seeded' ) ) {
		return;
	}

	$page = get_page_by_path( 'plan-your-trip/blogs' );
	if ( ! $page instanceof WP_Post ) {
		$page = get_page_by_path( 'blogs' );
	}

	if ( ! $page instanceof WP_Post ) {
		return;
	}

	update_post_meta( (int) $page->ID, '_wp_page_template', 'page-blogs' );

	update_option( 'cwc_blogs_page_seeded', time() );
}
add_action( 'init', 'cwc_seed_blogs_page', 30 );
