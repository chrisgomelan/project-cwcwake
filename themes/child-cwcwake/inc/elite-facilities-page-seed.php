<?php
/**
 * One-shot Elite Facilities page seeder.
 *
 * Assigns `page-elite-facilities` template to `/elite-facilities/` (or `elite-facilities`).
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assign elite facilities template to the Elite Facilities page.
 *
 * @return void
 */
function cwc_seed_elite_facilities_page(): void {
	if ( get_option( 'cwc_elite_facilities_page_seeded' ) ) {
		return;
	}

	$page = get_page_by_path( 'activities/elite-facilities' );
	if ( ! $page instanceof WP_Post ) {
		$page = get_page_by_path( 'elite-facilities' );
	}

	if ( ! $page instanceof WP_Post ) {
		return;
	}

	update_post_meta( (int) $page->ID, '_wp_page_template', 'page-elite-facilities' );

	wp_update_post(
		array(
			'ID'           => $page->ID,
			'post_content' => '',
		)
	);

	update_option( 'cwc_elite_facilities_page_seeded', time() );
}
add_action( 'init', 'cwc_seed_elite_facilities_page', 30 );
