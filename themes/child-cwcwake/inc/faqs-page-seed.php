<?php
/**
 * One-shot FAQs page seeder.
 *
 * Forces `_wp_page_template = page-faqs` on the existing
 * `Plan Your Trip → FAQs` page so the block-template hierarchy
 * picks up `templates/page-faqs.html` instead of the generic
 * `page-child` template assigned during the initial site seed.
 *
 * Runs once on `init`, guarded by `cwc_faqs_page_seeded`.
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function cwc_seed_faqs_page(): void {
	if ( get_option( 'cwc_faqs_page_seeded' ) ) {
		return;
	}

	$page = get_page_by_path( 'plan-your-trip/faqs' );
	if ( ! $page instanceof WP_Post ) {
		$page = get_page_by_path( 'faqs' );
	}

	if ( ! $page instanceof WP_Post ) {
		return;
	}

	update_post_meta( (int) $page->ID, '_wp_page_template', 'page-faqs' );

	update_option( 'cwc_faqs_page_seeded', time() );
}
add_action( 'init', 'cwc_seed_faqs_page', 30 );
