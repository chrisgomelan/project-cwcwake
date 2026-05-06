<?php
/**
 * CWC Accommodations — One-shot migration from legacy theme pages.
 *
 * Converts the four legacy room pages (Villas / Cabanas / Dwell /
 * Cabin under `/accommodations/`) into `accommodation` CPT posts,
 * pre-fills their meta from the bundled catalogue, and trashes the
 * legacy pages so their slugs are freed for the CPT permalinks.
 *
 * Why a migration is needed:
 *
 *   - The legacy pages own slugs `villas`, `cabanas`, `dwell`,
 *     `cabin` under the Accommodations parent. WordPress's
 *     `wp_unique_post_slug()` runs *before* the CPT-aware rewrite
 *     resolves, so an unmigrated install would force the new CPT
 *     posts onto `villas-2`, breaking the canonical
 *     `/accommodations/villas/` permalink.
 *
 *   - The catalogue has rich per-room copy that we don't want to
 *     lose; copying it into post meta keeps the existing rooms
 *     looking identical the moment the CPT goes live.
 *
 * The bundled catalogue (`cwc_acc_default_catalogue()`) is a
 * verbatim copy of the data that used to live in
 * `themes/child-cwcwake/inc/room-detail-pages.php`. Inlining it
 * here means the plugin has zero runtime dependency on the theme
 * for the migration step — once the CPT posts exist, editors own
 * the data and the catalogue becomes purely vestigial.
 *
 * The migration is option-guarded by `cwc_accommodation_migrated`
 * so it runs exactly once. To re-run on a dev environment:
 *
 *     wp option delete cwc_accommodation_migrated
 *
 * @package CWC_Accommodations
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default room catalogue used by the one-shot migration.
 *
 * Kept inside the plugin (not pulled from the theme) so the
 * migration is reproducible on any install where the theme might
 * have been replaced. Image URLs are site-relative so they resolve
 * against `home_url()` at lookup time.
 *
 * After the first migration run this catalogue is essentially
 * dead data — editors maintain rooms via the admin UI from then
 * on. We keep the function so dev environments can re-run the
 * migration after a `wp option delete cwc_accommodation_migrated`.
 *
 * @since 1.0.0
 *
 * @return array<string,array<string,mixed>> Slug-keyed catalogue.
 */
function cwc_acc_default_catalogue() {
	$uploads = '/wp-content/uploads/2026/04/';

	$amenities_full = [
		[ 'icon' => 'wifi',       'label' => 'Free Wi-Fi' ],
		[ 'icon' => 'parking',    'label' => 'Free Parking' ],
		[ 'icon' => 'pool',       'label' => 'Pool Access' ],
		[ 'icon' => 'air',        'label' => 'Air Conditioning' ],
		[ 'icon' => 'garden',     'label' => 'Garden View' ],
		[ 'icon' => 'bar',        'label' => 'Mini Bar' ],
		[ 'icon' => 'coffee',     'label' => 'Coffee Maker' ],
		[ 'icon' => 'smoke-free', 'label' => 'Non-Smoking' ],
	];

	$amenities_compact = [
		[ 'icon' => 'wifi',       'label' => 'Free Wi-Fi' ],
		[ 'icon' => 'parking',    'label' => 'Free Parking' ],
		[ 'icon' => 'pool',       'label' => 'Pool Access' ],
		[ 'icon' => 'air',        'label' => 'Air Conditioning' ],
		[ 'icon' => 'coffee',     'label' => 'Coffee Maker' ],
		[ 'icon' => 'smoke-free', 'label' => 'Non-Smoking' ],
	];

	return [
		'villas'  => [
			'title'           => 'VILLAS',
			'description'     => 'A spacious private villa designed for relaxation, perfect for families or groups seeking comfort, privacy, and unforgettable views just steps from the cable park.',
			'amenities'       => $amenities_full,
			'price'           => 'PHP 19,500',
			'price_sub_label' => 'per night · maximum 4 persons',
			'gallery'         => [
				[ 'url' => $uploads . 'image-1-villa-scaled.webp', 'alt' => 'Villa exterior view' ],
				[ 'url' => $uploads . 'image-2-villa-scaled.webp', 'alt' => 'Villa living area' ],
				[ 'url' => $uploads . 'image-3-villa-scaled.webp', 'alt' => 'Villa bedroom' ],
				[ 'url' => $uploads . 'image-4-villa-scaled.webp', 'alt' => 'Villa bathroom' ],
			],
		],
		'cabanas' => [
			'title'           => 'CABANAS',
			'description'     => 'Open-air cabanas blending tropical comfort with the laid-back wakeboarding lifestyle — ideal for friends or small families staying close to the action.',
			'amenities'       => $amenities_full,
			'price'           => 'PHP 12,500',
			'price_sub_label' => 'per night · maximum 4 persons',
			'gallery'         => [
				[ 'url' => $uploads . 'CABANA.webp', 'alt' => 'Cabana exterior' ],
			],
		],
		'dwell'   => [
			'title'           => 'DWELL',
			'description'     => 'A cozy dwell-style room for couples or solo travelers — modern essentials, restful nights, and easy access to every CWC experience.',
			'amenities'       => $amenities_compact,
			'price'           => 'PHP 7,500',
			'price_sub_label' => 'per night · maximum 2 persons',
			'gallery'         => [
				[ 'url' => $uploads . 'DWELL.webp', 'alt' => 'Dwell room interior' ],
			],
		],
		'cabin'   => [
			'title'           => 'CABIN',
			'description'     => 'A simple, comfortable cabin for budget-friendly stays — everything you need to recharge between sessions on the water.',
			'amenities'       => $amenities_compact,
			'price'           => 'PHP 6,500',
			'price_sub_label' => 'per night · maximum 2 persons',
			'gallery'         => [
				[ 'url' => $uploads . 'CABIN.webp', 'alt' => 'Cabin interior' ],
			],
		],
	];
}

/**
 * Run the legacy → CPT migration once.
 *
 * Hooked at `init` priority `34` — after the CPT registers
 * (priority `10`) and after the theme's legacy seeder priority
 * (`30`) so we can trash the pages it just touched. The plugin's
 * `cwc_acc_default_catalogue()` is the source of truth for the
 * seed data; it doesn't depend on the theme being loaded.
 *
 * Filterable so a host site can swap in extra rooms or tweak
 * pricing during migration without forking the plugin.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_migrate_accommodations_to_cpt() {
	if ( get_option( 'cwc_accommodation_migrated' ) ) {
		return;
	}

	$catalogue = cwc_acc_default_catalogue();

	/**
	 * Filter the migration catalogue.
	 *
	 * Lets sites add rooms or override copy without editing the
	 * plugin. Return shape must match `cwc_acc_default_catalogue()`.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string,array<string,mixed>> $catalogue Slug-keyed seed data.
	 */
	$catalogue = apply_filters( 'cwc_accommodation_migration_catalogue', $catalogue );

	if ( ! is_array( $catalogue ) || empty( $catalogue ) ) {
		// Mark migrated anyway so we don't retry every request — the
		// editor can still add rooms manually from this point.
		update_option( 'cwc_accommodation_migrated', true );
		return;
	}

	foreach ( $catalogue as $slug => $room ) {
		$slug = sanitize_key( (string) $slug );
		if ( '' === $slug ) {
			continue;
		}

		$existing_cpt = cwc_find_accommodation_by_slug( $slug );

		// 1) Make sure a CPT post for this room exists.
		if ( ! $existing_cpt instanceof WP_Post ) {
			$cpt_id = cwc_create_accommodation_post_from_catalogue( $slug, $room );
		} else {
			$cpt_id = (int) $existing_cpt->ID;
			cwc_apply_accommodation_meta_from_catalogue( $cpt_id, $room );
		}

		if ( $cpt_id <= 0 ) {
			continue;
		}

		// 2) Trash the colliding legacy page to free the slug.
		$legacy = get_page_by_path( 'accommodations/' . $slug );
		if ( $legacy instanceof WP_Post && 'page' === $legacy->post_type && 'trash' !== $legacy->post_status ) {
			wp_trash_post( (int) $legacy->ID );
		}

		/*
		 * 3) Heal the CPT slug back to the canonical value if the
		 *    earlier insert had to use `<slug>-2` because of the
		 *    legacy page collision. Now that the page is trashed,
		 *    `wp_unique_post_slug()` will accept the clean slug.
		 */
		$current_slug = (string) get_post_field( 'post_name', $cpt_id );
		if ( $current_slug !== $slug ) {
			wp_update_post(
				[
					'ID'        => $cpt_id,
					'post_name' => $slug,
				]
			);
		}

		/*
		 * 4) Sweep up any nav-menu item that still references the
		 *    legacy page by ID. Without this, the Appearance ->
		 *    Menus screen would show a broken Villas / Cabanas /
		 *    Dwell / Cabin row even though the front-end nav
		 *    filter already hides them. Cosmetic, but worth doing
		 *    once during migration.
		 */
		if ( $legacy instanceof WP_Post ) {
			cwc_remove_menu_items_for_post( (int) $legacy->ID );
		}
	}

	update_option( 'cwc_accommodation_migrated', true );
}
add_action( 'init', 'cwc_migrate_accommodations_to_cpt', 34 );

/**
 * Locate an existing accommodation post by slug.
 *
 * Searches across all post statuses so a draft / trashed migration
 * artefact is detected and reused instead of duplicated. Returns
 * `null` when no match exists.
 *
 * @since 1.0.0
 *
 * @param string $slug Canonical slug (e.g. `villas`).
 * @return WP_Post|null
 */
function cwc_find_accommodation_by_slug( $slug ) {
	$slug = sanitize_key( (string) $slug );
	if ( '' === $slug ) {
		return null;
	}

	$matches = get_posts(
		[
			'name'             => $slug,
			'post_type'        => 'accommodation',
			'post_status'      => 'any',
			'posts_per_page'   => 1,
			'no_found_rows'    => true,
			'suppress_filters' => false,
		]
	);

	return $matches ? $matches[0] : null;
}

/**
 * Create one accommodation CPT post from a catalogue row.
 *
 * Title / slug / excerpt come from the catalogue; `post_content`
 * is left empty so the CPT's `template` (room-gallery, room-info,
 * other-rooms — all attribute-less) takes over. The blocks then
 * resolve their data from the meta we set with
 * `cwc_apply_accommodation_meta_from_catalogue()`.
 *
 * Featured image is wired from the room's first gallery URL when
 * we can resolve it to an attachment — that drives the page-banner
 * background on the single template.
 *
 * @since 1.0.0
 *
 * @param string                       $slug Canonical slug.
 * @param array<string,mixed>          $room Catalogue row.
 * @return int Created post ID, or 0 on failure.
 */
function cwc_create_accommodation_post_from_catalogue( $slug, array $room ) {
	$post_id = wp_insert_post(
		[
			'post_type'    => 'accommodation',
			'post_status'  => 'publish',
			'post_title'   => isset( $room['title'] ) ? (string) $room['title'] : ucfirst( $slug ),
			'post_name'    => $slug,
			'post_excerpt' => isset( $room['description'] ) ? (string) $room['description'] : '',
			'post_content' => '',
			'menu_order'   => cwc_accommodation_default_menu_order( $slug ),
		],
		true
	);

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return 0;
	}

	cwc_apply_accommodation_meta_from_catalogue( (int) $post_id, $room );

	return (int) $post_id;
}

/**
 * Copy catalogue data into a post's `_cwc_*` meta.
 *
 * Idempotent: re-running with the same payload is a no-op against
 * the database thanks to `update_post_meta`'s built-in change
 * detection. Skips fields that already have a value so an editor
 * who hand-edited the meta after migration doesn't get clobbered
 * by a re-run.
 *
 * Capacity is parsed out of the price-sub label ("per night ·
 * maximum 4 persons" → 4). The legacy catalogue doesn't carry an
 * explicit capacity field, so this is the cleanest way to seed it
 * without inventing per-slug overrides.
 *
 * @since 1.0.0
 *
 * @param int                 $post_id Accommodation post ID.
 * @param array<string,mixed> $room    Catalogue row.
 * @return void
 */
function cwc_apply_accommodation_meta_from_catalogue( $post_id, array $room ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return;
	}

	$apply = static function ( $key, $value ) use ( $post_id ) {
		$existing = get_post_meta( $post_id, $key, true );
		if ( '' === (string) $existing && '' !== (string) $value ) {
			update_post_meta( $post_id, $key, $value );
		}
	};

	$price_sub = isset( $room['price_sub_label'] ) ? (string) $room['price_sub_label'] : '';

	$apply( '_cwc_price', isset( $room['price'] ) ? (string) $room['price'] : '' );
	$apply( '_cwc_price_sub', $price_sub );

	$capacity = cwc_parse_capacity_from_subtitle( $price_sub );
	if ( $capacity > 0 ) {
		$apply( '_cwc_capacity', (string) $capacity );
	}

	$apply( '_cwc_availability', 'available' );

	if ( ! empty( $room['amenities'] ) && is_array( $room['amenities'] ) ) {
		$slugs = [];
		foreach ( $room['amenities'] as $amenity ) {
			if ( is_array( $amenity ) && isset( $amenity['icon'] ) ) {
				$slugs[] = sanitize_key( (string) $amenity['icon'] );
			}
		}
		$slugs = array_filter( array_unique( $slugs ) );
		if ( ! empty( $slugs ) ) {
			$apply( '_cwc_amenities', implode( ',', $slugs ) );
		}
	}

	if ( ! empty( $room['gallery'] ) && is_array( $room['gallery'] ) ) {
		$ids = [];
		foreach ( $room['gallery'] as $entry ) {
			$url = '';
			if ( is_array( $entry ) && isset( $entry['url'] ) ) {
				$url = (string) $entry['url'];
			} elseif ( is_string( $entry ) ) {
				$url = $entry;
			}
			if ( '' === $url ) {
				continue;
			}

			$attachment_id = cwc_resolve_attachment_id_from_url( $url );
			if ( $attachment_id > 0 ) {
				$ids[] = $attachment_id;
			}
		}

		$ids = array_values( array_unique( $ids ) );
		if ( ! empty( $ids ) ) {
			$apply( '_cwc_gallery_ids', implode( ',', $ids ) );

			// Wire the first gallery image as the featured image for the page-banner.
			if ( ! has_post_thumbnail( $post_id ) ) {
				set_post_thumbnail( $post_id, $ids[0] );
			}
		}
	}
}

/**
 * Best-effort capacity parser from a catalogue sub-label.
 *
 * Catalogue strings look like `per night · maximum 4 persons`. We
 * extract the first integer that follows "maximum" so future copy
 * tweaks ("max 6 guests", "maximum of 8 persons") still work
 * without per-room overrides. Returns 0 on no match so the caller
 * can decide whether to skip the meta update.
 *
 * @since 1.0.0
 *
 * @param string $subtitle Catalogue sub-label.
 * @return int Parsed capacity, or 0 when none found.
 */
function cwc_parse_capacity_from_subtitle( $subtitle ) {
	$subtitle = (string) $subtitle;
	if ( '' === $subtitle ) {
		return 0;
	}

	if ( preg_match( '/maximum(?:\s+of)?\s+(\d+)/i', $subtitle, $matches ) ) {
		return (int) $matches[1];
	}

	if ( preg_match( '/max(?:\s+of)?\s+(\d+)/i', $subtitle, $matches ) ) {
		return (int) $matches[1];
	}

	return 0;
}

/**
 * Resolve a public image URL to its attachment ID.
 *
 * Wraps `attachment_url_to_postid()` with one extra round of
 * normalization: URLs in the legacy catalogue start with
 * `/wp-content/uploads/...` (host-less) but `attachment_url_to_postid`
 * needs an absolute URL. We prepend the site URL when the input
 * looks relative.
 *
 * Returns 0 when no match is found so the caller can skip the
 * gallery entry without poisoning the saved IDs list.
 *
 * @since 1.0.0
 *
 * @param string $url Image URL (absolute or site-relative).
 * @return int Attachment ID, or 0 when unresolved.
 */
function cwc_resolve_attachment_id_from_url( $url ) {
	$url = (string) $url;
	if ( '' === $url ) {
		return 0;
	}

	if ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) {
		$url = home_url( $url );
	}

	$attachment_id = attachment_url_to_postid( $url );

	return $attachment_id > 0 ? (int) $attachment_id : 0;
}

/**
 * Stable menu order for the four canonical rooms.
 *
 * Drives the order in which the "Other Rooms" rail lists siblings
 * (and the order they appear in WP Admin's All Rooms list). Unknown
 * slugs default to a high number so editor-added rooms drop to the
 * end until manually reordered.
 *
 * @since 1.0.0
 *
 * @param string $slug Room slug.
 * @return int Menu order value.
 */
function cwc_accommodation_default_menu_order( $slug ) {
	$order = [
		'villas'  => 10,
		'cabanas' => 20,
		'dwell'   => 30,
		'cabin'   => 40,
	];

	return $order[ $slug ] ?? 100;
}

/**
 * Delete every nav-menu item that points at a given page post ID.
 *
 * Menu items are stored as `nav_menu_item` posts whose link target
 * is held in the `_menu_item_object_id` meta plus a `_menu_item_object`
 * meta of `page` (or other post-type slug). We query for the pair
 * and delete each match — both the visible row in Appearance ->
 * Menus and any cached references go away in one pass.
 *
 * Used by the migration to clean up the four orphaned room links
 * created by `cwc_create_primary_menu()`.
 *
 * @since 1.0.0
 *
 * @param int $page_id Post ID whose menu references should be removed.
 * @return void
 */
function cwc_remove_menu_items_for_post( $page_id ) {
	$page_id = (int) $page_id;
	if ( $page_id <= 0 ) {
		return;
	}

	$menu_items = get_posts(
		[
			'post_type'      => 'nav_menu_item',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'   => '_menu_item_object_id',
					'value' => (string) $page_id,
				],
				[
					'key'   => '_menu_item_object',
					'value' => 'page',
				],
			],
			'fields'         => 'ids',
		]
	);

	foreach ( $menu_items as $menu_item_id ) {
		wp_delete_post( (int) $menu_item_id, true );
	}
}

/**
 * Disable the legacy theme-side room seeders after migration.
 *
 * The theme's `cwc_seed_room_detail_pages` / `_labels` callbacks
 * exist for back-compat with installs that haven't yet activated
 * this plugin. Once migration has run, those callbacks point at
 * trashed pages, so they're pure waste — strip them off `init`
 * before they fire.
 *
 * `function_exists()` guards the `remove_action` calls because the
 * theme that defines them may not be active. Hook priority `5`
 * runs before the legacy seeders' priority `30`, but after WP has
 * loaded all theme + plugin code, so by the time we look the
 * functions either exist (theme active) or don't (theme swapped).
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_disable_legacy_room_seeder_after_migration() {
	if ( ! get_option( 'cwc_accommodation_migrated' ) ) {
		return;
	}

	if ( function_exists( 'cwc_seed_room_detail_pages' ) ) {
		remove_action( 'init', 'cwc_seed_room_detail_pages', 30 );
	}

	if ( function_exists( 'cwc_migrate_room_detail_labels' ) ) {
		remove_action( 'init', 'cwc_migrate_room_detail_labels', 31 );
	}
}
add_action( 'init', 'cwc_disable_legacy_room_seeder_after_migration', 5 );

/**
 * One-shot cleanup: zero out `post_content` left behind by the old
 * CPT block-template pre-fill.
 *
 * Earlier versions registered a `template` argument on the
 * `accommodation` CPT that auto-inserted `cwc/room-gallery`,
 * `cwc/room-info`, and `cwc/other-rooms` into every new post's
 * `post_content`. The page template
 * (`templates/single-accommodation.html`) now renders those same
 * three blocks inline, pulling everything from CPT meta — so any
 * pre-filled blocks still sitting in `post_content` would render a
 * second time and double the room sections on the front-end.
 *
 * The cleanup is conservative: a post is only touched when its
 * content matches the exact pre-fill pattern (the three template
 * blocks back-to-back with empty attributes and no other content).
 * If an editor added so much as a heading between the blocks, the
 * post is left alone — `core/post-content` will keep rendering it
 * below the standard layout, which is the documented extension
 * point in the new template.
 *
 * Guarded by `cwc_accommodation_post_content_cleared_v1` so the
 * scan runs once per environment. Bump the suffix if the pre-fill
 * pattern ever changes.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_clear_legacy_template_content() {
	if ( get_option( 'cwc_accommodation_post_content_cleared_v1' ) ) {
		return;
	}

	$ids = get_posts(
		[
			'post_type'      => 'accommodation',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		]
	);

	if ( empty( $ids ) ) {
		update_option( 'cwc_accommodation_post_content_cleared_v1', time() );
		return;
	}

	/*
	 * The pre-fill blocks are written by `serialize_block()` so
	 * whitespace and ordering are deterministic. We compare against
	 * the canonical serialized form rather than a regex so a single
	 * extra character (= editor intent) leaves the post untouched.
	 */
	$canonical = trim(
		"<!-- wp:cwc/room-gallery /-->\n\n" .
		"<!-- wp:cwc/room-info /-->\n\n" .
		"<!-- wp:cwc/other-rooms /-->"
	);

	foreach ( $ids as $post_id ) {
		$content = trim( (string) get_post_field( 'post_content', $post_id ) );
		if ( '' === $content ) {
			continue;
		}

		if ( $content !== $canonical ) {
			continue;
		}

		wp_update_post(
			[
				'ID'           => (int) $post_id,
				'post_content' => '',
			]
		);
	}

	update_option( 'cwc_accommodation_post_content_cleared_v1', time() );
}
add_action( 'init', 'cwc_clear_legacy_template_content', 36 );
