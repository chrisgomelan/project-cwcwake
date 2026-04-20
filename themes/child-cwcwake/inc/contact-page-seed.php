<?php
/**
 * One-shot Contact page seeder.
 *
 * The initial site seed in `cwc_create_initial_pages()` referenced a
 * non-existent template (`page-contact-us`) and never put any block
 * content on the Contact page. This file:
 *
 *   1. Forces `_wp_page_template = page-contact` on the existing page
 *      so the block-template hierarchy renders the right chrome.
 *   2. Populates `post_content` with the two contact blocks
 *      (`cwc/contact-info` + `cwc/contact-form`) only when the page
 *      is currently empty (never clobbers editor content).
 *
 * Runs once on `init`, guarded by `cwc_contact_page_seeded`.
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the serialized block markup for the Contact page body.
 *
 * Both blocks expose attribute defaults that match the design spec,
 * so we only need to align the sections here and pass `align: full`.
 *
 * @since 1.0.0
 *
 * @return string Block markup ready for `post_content`.
 */
function cwc_render_contact_page_blocks()
{
	$flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

	$info_attrs = wp_json_encode( [ 'align' => 'full' ], $flags );
	$form_attrs = wp_json_encode( [ 'align' => 'full' ], $flags );

	return implode(
		"\n\n",
		[
			'<!-- wp:cwc/contact-info ' . $info_attrs . ' /-->',
			'<!-- wp:cwc/contact-form ' . $form_attrs . ' /-->',
		]
	);
}

/**
 * Force the right template + seed initial content on the Contact page.
 *
 * Located by slug — accepts either `contact-us` (created by the
 * initial seeder) or `contact` (likely future rename) without code
 * changes.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_seed_contact_page()
{
	if ( get_option( 'cwc_contact_page_seeded' ) ) {
		return;
	}

	$page = get_page_by_path( 'contact-us' );
	if ( ! $page instanceof WP_Post ) {
		$page = get_page_by_path( 'contact' );
	}

	if ( ! $page instanceof WP_Post ) {
		return;
	}

	update_post_meta( (int) $page->ID, '_wp_page_template', 'page-contact' );

	if ( '' === trim( (string) $page->post_content ) ) {
		wp_update_post(
			[
				'ID'           => (int) $page->ID,
				'post_content' => cwc_render_contact_page_blocks(),
			]
		);
	}

	update_option( 'cwc_contact_page_seeded', true );
}
add_action( 'init', 'cwc_seed_contact_page', 30 );
