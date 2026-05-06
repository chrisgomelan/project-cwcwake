<?php
/**
 * One-shot Land Activities page seeder.
 *
 * Forces `_wp_page_template = page-land-activities` on the existing
 * `/activities/land-activities/` page so the block-template hierarchy
 * picks up `templates/page-land-activities.html`.
 *
 * Runs once on `init`, guarded by `cwc_land_activities_page_seeded`.
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Force `page-land-activities` on the existing Land Activities page.
 *
 * @return void
 */
function cwc_seed_land_activities_page(): void {
	if ( get_option( 'cwc_land_activities_page_seeded' ) ) {
		return;
	}

	$page = get_page_by_path( 'activities/land-activities' );
	if ( ! $page instanceof WP_Post ) {
		$page = get_page_by_path( 'land-activities' );
	}

	if ( ! $page instanceof WP_Post ) {
		return;
	}

	// Force template assignment.
	update_post_meta( (int) $page->ID, '_wp_page_template', 'page-land-activities' );

	// Clear post content so the .html template file takes priority.
	wp_update_post(
		array(
			'ID'           => $page->ID,
			'post_content' => '',
		)
	);

	update_option( 'cwc_land_activities_page_seeded', time() );
}
add_action( 'init', 'cwc_seed_land_activities_page', 30 );
