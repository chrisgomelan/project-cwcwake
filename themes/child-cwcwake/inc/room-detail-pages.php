<?php
/**
 * Room detail page content + one-shot seeder.
 *
 * Owns the per-room data ("catalogue") and the logic that turns it into
 * the serialized block markup stored on each room page in the database.
 * Lives in `inc/` instead of `functions.php` because the data set is
 * domain-specific (Villas / Cabanas / Dwell / Cabin), is large enough to
 * deserve its own file, and only the seeder hooks into WordPress.
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Catalogue of per-room landing-page content.
 *
 * Single source of truth for the four room detail pages
 * (Villas, Cabanas, Dwell, Cabin). Used by `cwc_render_room_detail_blocks()`
 * to build block markup and by `cwc_seed_room_detail_pages()` to populate
 * the matching WordPress pages.
 *
 * @since 1.0.0
 *
 * @return array<string,array<string,mixed>> Keyed by page slug.
 */
function cwc_room_detail_catalogue() {
	$uploads = '/wp-content/uploads/2026/04/';

	$standard_policies = array(
		array(
			'icon'        => 'check-in',
			'name'        => 'Check-in',
			'description' => 'From 02:00 PM to 09:00 PM',
		),
		array(
			'icon'        => 'check-out',
			'name'        => 'Check-out',
			'description' => 'Until 12:00 PM',
		),
		array(
			'icon'        => 'breakfast',
			'name'        => 'Breakfast',
			'description' => 'Breakfast Available (may be included in selected rooms).',
		),
		array(
			'icon'        => 'reception',
			'name'        => 'Reception Hours',
			'description' => 'Open until 09:00 PM',
		),
		array(
			'icon'        => 'children',
			'name'        => 'Children and beds',
			'description' => 'Infants (0–3 yrs): free. Children (4–8 yrs): extra bed charge applies. Guests (9+): considered adults.',
		),
		array(
			'icon'        => 'no-age',
			'name'        => 'No age restriction',
			'description' => 'Guests of all ages are welcome.',
		),
		array(
			'icon'        => 'smoking',
			'name'        => 'Smoking',
			'description' => 'Smoking is not allowed.',
		),
	);

	$amenities_full = array(
		array(
			'icon'  => 'wifi',
			'label' => 'Free Wi-Fi',
		),
		array(
			'icon'  => 'parking',
			'label' => 'Free Parking',
		),
		array(
			'icon'  => 'pool',
			'label' => 'Pool Access',
		),
		array(
			'icon'  => 'air',
			'label' => 'Air Conditioning',
		),
		array(
			'icon'  => 'garden',
			'label' => 'Garden View',
		),
		array(
			'icon'  => 'bar',
			'label' => 'Mini Bar',
		),
		array(
			'icon'  => 'coffee',
			'label' => 'Coffee Maker',
		),
		array(
			'icon'  => 'smoke-free',
			'label' => 'Non-Smoking',
		),
	);

	$amenities_compact = array(
		array(
			'icon'  => 'wifi',
			'label' => 'Free Wi-Fi',
		),
		array(
			'icon'  => 'parking',
			'label' => 'Free Parking',
		),
		array(
			'icon'  => 'pool',
			'label' => 'Pool Access',
		),
		array(
			'icon'  => 'air',
			'label' => 'Air Conditioning',
		),
		array(
			'icon'  => 'coffee',
			'label' => 'Coffee Maker',
		),
		array(
			'icon'  => 'smoke-free',
			'label' => 'Non-Smoking',
		),
	);

	/*
	 * The four sibling thumbnails reuse the existing accommodations
	 * card images. Each room's "siblings" list is the other three.
	 */
	$siblings_pool = array(
		'villas'  => array(
			'label' => 'VILLAS',
			'image' => $uploads . 'VILLAS.webp',
			'url'   => '/accommodations/villas/',
		),
		'cabanas' => array(
			'label' => 'CABANAS',
			'image' => $uploads . 'CABANA.webp',
			'url'   => '/accommodations/cabanas/',
		),
		'dwell'   => array(
			'label' => 'DWELL',
			'image' => $uploads . 'DWELL.webp',
			'url'   => '/accommodations/dwell/',
		),
		'cabin'   => array(
			'label' => 'CABIN',
			'image' => $uploads . 'CABIN.webp',
			'url'   => '/accommodations/cabin/',
		),
	);

	$siblings_for = static function ( string $current ) use ( $siblings_pool ): array {
		$out = array();
		foreach ( $siblings_pool as $slug => $card ) {
			if ( $slug === $current ) {
				continue;
			}
			$out[] = $card;
		}
		return $out;
	};

	/*
	 * Only the Villa room has four distinct hero images uploaded so far.
	 * Other rooms reuse their accommodations thumbnail four times as a
	 * placeholder; editors can swap them in the Media Library / page editor.
	 */
	$villa_gallery = array(
		array(
			'url' => $uploads . 'image-1-villa-scaled.webp',
			'alt' => 'Villa exterior view',
		),
		array(
			'url' => $uploads . 'image-2-villa-scaled.webp',
			'alt' => 'Villa living area',
		),
		array(
			'url' => $uploads . 'image-3-villa-scaled.webp',
			'alt' => 'Villa bedroom',
		),
		array(
			'url' => $uploads . 'image-4-villa-scaled.webp',
			'alt' => 'Villa bathroom',
		),
	);

	$placeholder_gallery = static function ( string $image, string $alt ) use ( $uploads ) {
		$src = $uploads . $image;
		return array(
			array(
				'url' => $src,
				'alt' => $alt,
			),
			array(
				'url' => $src,
				'alt' => $alt,
			),
			array(
				'url' => $src,
				'alt' => $alt,
			),
			array(
				'url' => $src,
				'alt' => $alt,
			),
		);
	};

	return array(
		'villas'  => array(
			'title'           => 'VILLAS',
			'description'     => 'A spacious private villa designed for relaxation, perfect for families or groups seeking comfort, privacy, and unforgettable views just steps from the cable park.',
			'amenities'       => $amenities_full,
			'price'           => 'PHP 19,500',
			'price_sub_label' => 'per night · maximum 4 persons',
			'gallery'         => $villa_gallery,
			'siblings'        => $siblings_for( 'villas' ),
			'policies'        => $standard_policies,
		),
		'cabanas' => array(
			'title'           => 'CABANAS',
			'description'     => 'Open-air cabanas blending tropical comfort with the laid-back wakeboarding lifestyle — ideal for friends or small families staying close to the action.',
			'amenities'       => $amenities_full,
			'price'           => 'PHP 12,500',
			'price_sub_label' => 'per night · maximum 4 persons',
			'gallery'         => $placeholder_gallery( 'CABANA.webp', 'Cabana exterior' ),
			'siblings'        => $siblings_for( 'cabanas' ),
			'policies'        => $standard_policies,
		),
		'dwell'   => array(
			'title'           => 'DWELL',
			'description'     => 'A cozy dwell-style room for couples or solo travelers — modern essentials, restful nights, and easy access to every CWC experience.',
			'amenities'       => $amenities_compact,
			'price'           => 'PHP 7,500',
			'price_sub_label' => 'per night · maximum 2 persons',
			'gallery'         => $placeholder_gallery( 'DWELL.webp', 'Dwell room interior' ),
			'siblings'        => $siblings_for( 'dwell' ),
			'policies'        => $standard_policies,
		),
		'cabin'   => array(
			'title'           => 'CABIN',
			'description'     => 'A simple, comfortable cabin for budget-friendly stays — everything you need to recharge between sessions on the water.',
			'amenities'       => $amenities_compact,
			'price'           => 'PHP 6,500',
			'price_sub_label' => 'per night · maximum 2 persons',
			'gallery'         => $placeholder_gallery( 'CABIN.webp', 'Cabin interior' ),
			'siblings'        => $siblings_for( 'cabin' ),
			'policies'        => $standard_policies,
		),
	);
}

/**
 * Build the serialized block markup for a single room detail page.
 *
 * Combines the three custom blocks — `cwc/room-gallery`, `cwc/room-info`,
 * and `cwc/other-rooms` — using the per-room data from the catalogue.
 * Output is suitable for storing in `wp_posts.post_content`.
 *
 * @since 1.0.0
 *
 * @param string $slug Page slug (e.g. `villas`, `cabanas`, `dwell`, `cabin`).
 * @return string Serialized block markup, or empty string when slug is unknown.
 */
function cwc_render_room_detail_blocks( string $slug ): string {
	$catalogue = cwc_room_detail_catalogue();
	if ( ! isset( $catalogue[ $slug ] ) ) {
		return '';
	}

	$room = $catalogue[ $slug ];

	/*
	 * JSON_UNESCAPED_SLASHES keeps URL paths readable inside the block
	 * delimiter; JSON_UNESCAPED_UNICODE preserves non-ASCII glyphs
	 * (en-dashes in the policy descriptions, for example).
	 */
	$json_flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

	$gallery_attrs = wp_json_encode(
		array(
			'images'        => $room['gallery'],
			'backLinkLabel' => 'Back to Accommodations',
			'backLinkUrl'   => '/accommodations/',
			'seeAllLabel'   => 'See All Images',
			'seeAllUrl'     => '',
			'align'         => 'full',
		),
		$json_flags
	);

	$info_attrs = wp_json_encode(
		array(
			'title'            => $room['title'],
			'descriptionLabel' => 'Room Description',
			'description'      => $room['description'],
			'amenitiesLabel'   => 'Room Amenities',
			'amenities'        => $room['amenities'],
			'price'            => $room['price'],
			'priceSubLabel'    => $room['price_sub_label'],
			'bookButtonLabel'  => 'Book Now',
			'bookButtonUrl'    => '#book',
			'policiesLabel'    => 'Policies',
			'policiesIntro'    => 'Everything you need to know about your stay policies and house rules.',
			'policies'         => $room['policies'],
			'align'            => 'full',
		),
		$json_flags
	);

	$others_attrs = wp_json_encode(
		array(
			'heading' => 'Other Rooms',
			'items'   => $room['siblings'],
			'align'   => 'full',
		),
		$json_flags
	);

	return implode(
		"\n\n",
		array(
			'<!-- wp:cwc/room-gallery ' . $gallery_attrs . ' /-->',
			'<!-- wp:cwc/room-info ' . $info_attrs . ' /-->',
			'<!-- wp:cwc/other-rooms ' . $others_attrs . ' /-->',
		)
	);
}

/**
 * Seed the four existing room detail pages with template + block content.
 *
 * Runs once (option-guarded by `cwc_room_detail_seeded`) on the next
 * `init` after this code is deployed. For each known room slug it:
 *
 *   1. Locates the page under `accommodations/<slug>` (skip if missing).
 *   2. Forces `_wp_page_template` to `room-detail` so the shared
 *      template renders the page chrome.
 *   3. Replaces `post_content` with freshly rendered block markup
 *      *only* when the page is currently empty — never clobbers
 *      content an editor has already saved.
 *
 * Set `delete_option('cwc_room_detail_seeded')` from wp-cli to re-run.
 *
 * @since 1.0.0
 */
function cwc_seed_room_detail_pages() {
	$catalogue = cwc_room_detail_catalogue();
	$seeded    = (bool) get_option( 'cwc_room_detail_seeded', false );

	foreach ( $catalogue as $slug => $_room ) {
		$page = get_page_by_path( 'accommodations/' . $slug );

		// Fallback check if it was moved to root.
		if ( ! $page instanceof WP_Post ) {
			$page = get_page_by_path( $slug );
		}

		if ( ! $page instanceof WP_Post ) {
			continue;
		}

		/*
		 * Always ensure the template is correct. This fixes issues where the
		 * template was renamed or lost, even on already-seeded sites.
		 */
		update_post_meta( (int) $page->ID, '_wp_page_template', 'room-detail' );

		/*
		 * Only inject the blocks if the page is empty and we haven't
		 * successfully seeded it yet.
		 */
		if ( ! $seeded && '' === trim( (string) $page->post_content ) ) {
			wp_update_post(
				array(
					'ID'           => (int) $page->ID,
					'post_content' => cwc_render_room_detail_blocks( $slug ),
				)
			);
		}
	}

	if ( ! $seeded ) {
		update_option( 'cwc_room_detail_seeded', true );
	}
}
add_action( 'init', 'cwc_seed_room_detail_pages', 30 );

	/**
	 * Targeted label migrations for already-seeded room pages.
	 *
	 * The seeder above only writes content into pages that are empty, so any
	 * label change made later in the catalogue does not propagate to live
	 * pages. This runs a list of safe `str_replace` patches (old → new) on
	 * the four known room pages, then bumps a version option so each patch
	 * runs exactly once.
	 *
	 * To add another label rename in the future, append a new entry to
	 * `$patches` and bump `CWC_ROOM_DETAIL_LABEL_VERSION`.
	 *
	 * @since 1.0.0
	 */
function cwc_migrate_room_detail_labels() {
	$current_version = 2;
	$stored_version  = (int) get_option( 'cwc_room_detail_label_version', 0 );

	if ( $stored_version >= $current_version ) {
		return;
	}

	/*
	 * Each pair is matched as a literal string against `post_content`.
	 * Patterns include the JSON key so we never touch unrelated copy
	 * (e.g. body text that happens to mention "Back to Rooms").
	 */
	$patches = array(
		'"backLinkLabel":"Back to Rooms"'         => '"backLinkLabel":"Back to Accommodations"',
		'"backLinkLabel":"Back to Accomodations"' => '"backLinkLabel":"Back to Accommodations"',
	);

	$slugs = array_keys( cwc_room_detail_catalogue() );

	foreach ( $slugs as $slug ) {
		$page = get_page_by_path( 'accommodations/' . $slug );

		if ( ! $page instanceof WP_Post ) {
			continue;
		}

		$original = (string) $page->post_content;
		$updated  = strtr( $original, $patches );

		if ( $updated === $original ) {
			continue;
		}

		wp_update_post(
			array(
				'ID'           => (int) $page->ID,
				'post_content' => $updated,
			)
		);
	}

	update_option( 'cwc_room_detail_label_version', $current_version );
}
add_action( 'init', 'cwc_migrate_room_detail_labels', 31 );
