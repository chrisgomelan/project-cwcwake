<?php
/**
 * One-shot Water Sports page seeder.
 *
 * Forces `_wp_page_template = page-water-sports` on the existing
 * `/activities/water-sports/` page so the block-template hierarchy
 * picks up `templates/page-water-sports.html`.
 *
 * Runs once on `init`, guarded by `cwc_water_sports_page_seeded`.
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Force `page-water-sports` on the existing Water Sports page.
 *
 * @since 1.0.0
 * @return void
 */
function cwc_seed_water_sports_page(): void {
	if ( get_option( 'cwc_water_sports_page_seeded_v2' ) ) {
		return;
	}

	$page = get_page_by_path( 'activities/water-sports' );
	if ( ! $page instanceof WP_Post ) {
		$page = get_page_by_path( 'water-sports' );
	}

	if ( ! $page instanceof WP_Post ) {
		return;
	}

	// Force template assignment.
	update_post_meta( (int) $page->ID, '_wp_page_template', 'page-water-sports' );

	// Clear post content so the .html template file takes priority.
	wp_update_post(
		array(
			'ID'           => $page->ID,
			'post_content' => '',
		)
	);

	update_option( 'cwc_water_sports_page_seeded_v2', time() );
}
add_action( 'init', 'cwc_seed_water_sports_page', 30 );
