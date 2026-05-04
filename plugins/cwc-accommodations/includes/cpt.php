<?php
/**
 * CWC Accommodations — Custom Post Type, meta fields, and catalogues.
 *
 * Foundation of the room management system. Owns:
 *
 *   - The `accommodation` post type (mounted at
 *     `/accommodations/<slug>/`).
 *   - Six per-room meta fields (`_cwc_price`, `_cwc_price_sub`,
 *     `_cwc_capacity`, `_cwc_availability`, `_cwc_amenities`,
 *     `_cwc_gallery_ids`) registered with `show_in_rest` so the
 *     block editor and any future headless surface can read and
 *     write them through the REST API.
 *   - The amenity + policy icon catalogues every other module
 *     reads from. Centralising them here means the meta-box
 *     checkbox UI, the global-policies admin page, and the
 *     front-end block renders all share a single source of truth.
 *
 * Theme coupling: the icon SVGs themselves live in the active
 * theme's `assets/images/` folder so the designer can iterate on
 * them alongside the rest of the brand. `cwc_icon_url_for_slug()`
 * resolves them via `get_stylesheet_directory_uri()`, so the
 * plugin is theme-agnostic at the plumbing level — swapping in a
 * different theme that ships matching SVGs just works.
 *
 * @package CWC_Accommodations
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------------------------------------------------
 * CPT registration
 * --------------------------------------------------------- */

function cwc_get_room_inventory( $post_id ) {
	$physical_rooms = cwc_get_physical_rooms( $post_id );
	if ( ! empty( $physical_rooms ) ) {
		$count = 0;
		foreach ( $physical_rooms as $room ) {
			if ( 'available' === ( $room['status'] ?? 'available' ) ) {
				$count++;
			}
		}
		return $count;
	}
	$inventory = get_post_meta( $post_id, '_cwc_inventory', true );
	return $inventory !== '' ? intval( $inventory ) : 1; // Default to 1 if not set
}

function cwc_get_physical_rooms( $post_id ) {
	$rooms_raw = get_post_meta( $post_id, '_cwc_physical_rooms', true );
	if ( empty( $rooms_raw ) ) {
		return [];
	}
	return json_decode( $rooms_raw, true ) ?: [];
}

/**
 * Register the `accommodation` post type and flush rewrites once.
 *
 * Non-hierarchical because rooms don't nest the way albums do — a
 * Villas post is never a "child of" Cabanas. We expose the CPT in
 * REST so the block editor's PluginDocumentSettingPanel and any
 * future headless integration can read room data without scraping
 * the rendered HTML.
 *
 * The `template` property pre-fills the editor canvas with the three
 * blocks an editor will almost always want on a room page: the
 * gallery, the info panel, and the "other rooms" rail. They start
 * with no attributes so the meta-driven fallbacks take over until
 * the editor types something into the block UI.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_register_accommodation_cpt() {
	$labels = [
		'name'                  => _x( 'Accommodations', 'Post type general name', 'child-cwcwake' ),
		'singular_name'         => _x( 'Accommodation', 'Post type singular name', 'child-cwcwake' ),
		'menu_name'             => _x( 'Accommodations', 'Admin Menu text', 'child-cwcwake' ),
		'name_admin_bar'        => _x( 'Accommodation', 'Add New on Toolbar', 'child-cwcwake' ),
		'add_new'               => __( 'Add New', 'child-cwcwake' ),
		'add_new_item'          => __( 'Add New Room', 'child-cwcwake' ),
		'new_item'              => __( 'New Room', 'child-cwcwake' ),
		'edit_item'             => __( 'Edit Room', 'child-cwcwake' ),
		'view_item'             => __( 'View Room', 'child-cwcwake' ),
		'all_items'             => __( 'All Rooms', 'child-cwcwake' ),
		'search_items'          => __( 'Search Rooms', 'child-cwcwake' ),
		'not_found'             => __( 'No rooms found.', 'child-cwcwake' ),
		'not_found_in_trash'    => __( 'No rooms found in Trash.', 'child-cwcwake' ),
		'featured_image'        => _x( 'Room Cover Image', 'Featured image label', 'child-cwcwake' ),
		'set_featured_image'    => _x( 'Set cover image', 'Set featured image label', 'child-cwcwake' ),
		'remove_featured_image' => _x( 'Remove cover image', 'Remove featured image label', 'child-cwcwake' ),
		'use_featured_image'    => _x( 'Use as cover image', 'Use as featured image label', 'child-cwcwake' ),
		'archives'              => _x( 'Room archives', 'Archive label', 'child-cwcwake' ),
	];

	$args = [
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_rest'       => true,
		'menu_position'      => 6,
		'menu_icon'          => 'dashicons-admin-home',
		'show_in_nav_menus'  => true,
		'capability_type'    => 'post',
		'hierarchical'       => false,
		'has_archive'        => false,
		'rewrite'            => [
			'slug'       => 'accommodations',
			'with_front' => false,
		],
		'supports'           => [
			'title',
			'editor',
			'thumbnail',
			'excerpt',
			'revisions',
			'custom-fields',
			'page-attributes',
		],
		/*
		 * Intentionally no block `template` here.
		 *
		 * The single-accommodation page template
		 * (`themes/child-cwcwake/templates/single-accommodation.html`)
		 * renders `cwc/room-gallery`, `cwc/room-info`, and
		 * `cwc/other-rooms` directly with no attributes — those blocks
		 * pull their data from the meta fields registered below via
		 * `cwc_is_accommodation_context()` fallbacks.
		 *
		 * Pre-filling the same blocks into `post_content` would cause
		 * the room sections to render twice (once from the template,
		 * once from `core/post-content`), and would create the false
		 * impression that editors should manage room data inside the
		 * Gutenberg block canvas instead of through the meta boxes.
		 *
		 * `post_content` stays free for genuine per-room overrides
		 * (promos, embedded videos, etc.) — anything dropped there
		 * still renders below the standard layout.
		 */
	];

	register_post_type( 'accommodation', $args );

	cwc_register_accommodation_meta();

	if ( ! get_option( 'cwc_accommodation_rewrites_flushed' ) ) {
		flush_rewrite_rules();
		update_option( 'cwc_accommodation_rewrites_flushed', true );
	}
}
add_action( 'init', 'cwc_register_accommodation_cpt' );

/**
 * Re-flush rewrites if the CPT registration ever changes.
 *
 * Bumping `CWC_ACC_VERSION` after editing the rewrite block above
 * makes deployed sites re-register the rules on the next request
 * without an editor having to manually re-save permalinks.
 *
 * Falls back gracefully when the constant isn't defined yet (e.g.
 * during partial-upgrade scenarios where the bootstrap file hasn't
 * loaded but a CLI script tries to register the CPT directly).
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_accommodation_maybe_refresh_rewrites() {
	$version = defined( 'CWC_ACC_VERSION' ) ? CWC_ACC_VERSION : '0';
	if ( get_option( 'cwc_accommodation_rewrites_flushed_v' ) === $version ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'cwc_accommodation_rewrites_flushed_v', $version );
}
add_action( 'wp_loaded', 'cwc_accommodation_maybe_refresh_rewrites' );

/* ---------------------------------------------------------
 * Meta registration
 * --------------------------------------------------------- */

/**
 * Register the six per-room meta fields.
 *
 * Schema mirrors `room-management-transition.md` § 2.B exactly:
 *
 *   - `_cwc_price`         (string)  Display price, e.g. "PHP 19,500".
 *   - `_cwc_price_sub`     (string)  Sub-label, e.g. "per night".
 *   - `_cwc_capacity`      (integer) Max occupancy (used for filtering).
 *   - `_cwc_availability`  (enum)    available | fully-booked | maintenance.
 *   - `_cwc_amenities`     (string)  Comma-separated amenity slugs. We
 *                                    store as a string (not array) so
 *                                    classic meta boxes round-trip
 *                                    cleanly — the catalogue resolves
 *                                    each slug to icon + label at render.
 *   - `_cwc_gallery_ids`   (string)  Comma-separated attachment IDs for
 *                                    the room-gallery block fallback.
 *
 * `show_in_rest` exposes each field to the editor and to any future
 * headless surface; `single` keeps the API shape `{ key: value }`
 * instead of `{ key: [ value ] }` for simplicity.
 *
 * `auth_callback` checks `edit_post` so REST writes still respect
 * WordPress capabilities — without this any logged-in user could
 * PATCH meta via the REST API.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_register_accommodation_meta() {
	$auth = static function ( $allowed, $meta_key, $post_id ) {
		return current_user_can( 'edit_post', (int) $post_id );
	};

	$fields = [
		'_cwc_price'          => ['type' => 'string', 'rest' => true],
		'_cwc_price_sub'      => ['type' => 'string', 'rest' => true],
		'_cwc_capacity'       => ['type' => 'integer','rest' => true],
		'_cwc_availability'   => ['type' => 'string', 'rest' => true],
		'_cwc_amenities'      => ['type' => 'string', 'rest' => false],
		'_cwc_inclusions'     => ['type' => 'string', 'rest' => false],
		'_cwc_inventory'      => ['type' => 'integer','rest' => true],
		'_cwc_physical_rooms' => ['type' => 'string', 'rest' => true],
		'_cwc_gallery_ids'    => ['type' => 'string', 'rest' => true],
		'_cwc_beds'           => ['type' => 'string', 'rest' => true],
	];

	foreach ( $fields as $key => $info ) {
		$type      = $info['type'];
		$show_rest = $info['rest'];
		register_post_meta(
			'accommodation',
			$key,
			[
				'type'              => $type,
				'single'            => true,
				'show_in_rest'      => $show_rest,
				'auth_callback'     => $auth,
				'sanitize_callback' => cwc_accommodation_meta_sanitizer( $key ),
				'default'           => 'integer' === $type ? 0 : '',
			]
		);
	}
}

/**
 * Resolve a per-key sanitize callback for accommodation meta.
 *
 * Each meta field has slightly different cleaning needs (an integer
 * has to clamp, an enum has to whitelist, a free-form text just
 * needs HTML stripped). Keeping these in one place — instead of
 * inline closures during registration — makes the rules easy to
 * audit and reuse from the meta-box save handler.
 *
 * @since 1.0.0
 *
 * @param string $key Meta key.
 * @return callable Sanitizer that takes a raw value and returns the cleaned value.
 */
function cwc_accommodation_meta_sanitizer( $key ) {
	switch ( $key ) {
		case '_cwc_capacity':
			return static function ( $value ) {
				return max( 0, (int) $value );
			};

		case '_cwc_availability':
			return static function ( $value ) {
				$allowed = [ 'available', 'limited', 'booked', 'closed', 'fully-booked', 'maintenance' ];
				$value   = is_string( $value ) ? $value : '';
				return in_array( $value, $allowed, true ) ? $value : 'available';
			};

		case '_cwc_amenities':
			return static function ( $value ) {
				return cwc_accommodation_normalize_slug_csv( $value, array_keys( cwc_amenity_catalogue() ) );
			};

		case '_cwc_inclusions':
			return static function ( $value ) {
				return cwc_accommodation_normalize_slug_csv( $value, array_keys( cwc_inclusion_catalogue() ) );
			};

		case '_cwc_inventory':
			return static function ( $value ) {
				return intval( $value );
			};

		case '_cwc_physical_rooms':
		case '_cwc_beds':
			return static function ( $value ) {
				return sanitize_text_field( $value );
			};

		case '_cwc_gallery_ids':
			return static function ( $value ) {
				/*
				 * IDs come from the media-library picker as a comma-
				 * separated string. We split, cast each token to int,
				 * drop zeros, and rejoin — that way a stray space or
				 * empty trailing token can't poison the lookup.
				 */
				$value = is_string( $value ) ? $value : '';
				$ids   = array_filter( array_map( 'intval', explode( ',', $value ) ) );
				return implode( ',', $ids );
			};

		default:
			return static function ( $value ) {
				return is_string( $value ) ? sanitize_text_field( $value ) : '';
			};
	}
}

/**
 * Clean a comma-separated slug list against an allow-list.
 *
 * Helper used by the amenities sanitizer (and reusable for any
 * future slug-bag meta field). Splits on commas, trims, lowercases,
 * drops empties, deduplicates, and finally intersects with the
 * allow-list so a stale or typo'd slug from an older catalogue
 * version can't slip into the database.
 *
 * @since 1.0.0
 *
 * @param mixed         $value  Raw value (expected string).
 * @param array<string> $allowed Whitelist of acceptable slugs.
 * @return string Cleaned comma-separated slug list (no spaces).
 */
function cwc_accommodation_normalize_slug_csv( $value, array $allowed ) {
	$value = is_string( $value ) ? $value : '';
	$parts = array_filter( array_map( 'sanitize_key', array_map( 'trim', explode( ',', $value ) ) ) );
	$parts = array_values( array_unique( $parts ) );
	$parts = array_values( array_intersect( $parts, $allowed ) );
	return implode( ',', $parts );
}

/* ---------------------------------------------------------
 * Icon catalogues (single source of truth)
 * --------------------------------------------------------- */

/**
 * Catalogue of room amenities the site supports.
 *
 * Maps a stable slug → display label + bundled SVG filename. The
 * meta-box checkbox UI iterates this list to render its options;
 * the front-end `room-info` block iterates the same list (filtered
 * by the room's saved slugs) to render the chip cloud. Adding a new
 * amenity is therefore a one-line change here — both UIs pick it up.
 *
 * Filterable so a future plugin can extend the list without forking
 * the theme.
 *
 * @since 1.0.0
 *
 * @return array<string,array{label:string,icon:string}>
 */
function cwc_amenity_catalogue() {
	$defaults = [
		'wifi'       => [ 'label' => 'Free Wi-Fi',        'icon' => 'wifi' ],
		'parking'    => [ 'label' => 'Free Parking',      'icon' => 'parking' ],
		'pool'       => [ 'label' => 'Pool Access',       'icon' => 'pool' ],
		'air'        => [ 'label' => 'Air Conditioning',  'icon' => 'air' ],
		'garden'     => [ 'label' => 'Garden View',       'icon' => 'garden' ],
		'bar'        => [ 'label' => 'Mini Bar',          'icon' => 'bar' ],
		'coffee'     => [ 'label' => 'Coffee Maker',      'icon' => 'coffee' ],
		'smoke-free' => [ 'label' => 'Non-Smoking',       'icon' => 'smoke-free' ],
	];

	$dynamic = get_option( 'cwc_dynamic_amenities', [] );

	/*
	 * Always MERGE dynamic entries on top of the hardcoded defaults.
	 * Using dynamic-only ($catalogue = $dynamic) would cause the
	 * sanitizer's array_intersect to strip default slugs (wifi, pool,
	 * etc.) the moment any custom amenity is added, silently wiping
	 * them from every saved room on the next save.
	 */
	$catalogue = is_array( $dynamic ) && ! empty( $dynamic )
		? array_merge( $defaults, $dynamic )
		: $defaults;

	/**
	 * Filter the room amenity catalogue.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string,array{label:string,icon:string}> $catalogue Slug → label + icon.
	 */
	return apply_filters( 'cwc_amenity_catalogue', $catalogue );
}

/**
 * Catalogue of room inclusions the site supports.
 *
 * Maps a stable slug → display label. Like amenities, this drives
 * the checkbox UI in the admin and the chip cloud on the front-end.
 *
 * @since 1.1.0
 *
 * @return array<string,array{label:string}>
 */
function cwc_inclusion_catalogue() {
	$defaults = [
		'wakeboard-4'    => [ 'label' => 'Free Wakeboard for 4 Guests' ],
		'airport-pick'   => [ 'label' => 'Free Airport Pick Up in Naga Airport' ],
		'golf-coach'     => [ 'label' => 'Free 18 holes Gold maximum of 4 Guests or One hour with Golf Coach' ],
		'shuttle-naga'   => [ 'label' => 'Free Shuttle to Naga City' ],
		'skate-park'     => [ 'label' => 'Free Use of Skate Park' ],
		'bike-track'     => [ 'label' => 'Free Use of Bike Track' ],
		'playground'     => [ 'label' => "Free Use of Children's Playground" ],
		'basketball'     => [ 'label' => 'Free Use of Outdoor Basketball Court' ],
	];

	$dynamic = get_option( 'cwc_dynamic_inclusions', [] );

	/*
	 * Always MERGE dynamic entries on top of the hardcoded defaults
	 * so that default inclusion slugs remain valid even after a
	 * custom inclusion is added via the Settings page.
	 */
	$catalogue = is_array( $dynamic ) && ! empty( $dynamic )
		? array_merge( $defaults, $dynamic )
		: $defaults;

	return apply_filters( 'cwc_inclusion_catalogue', $catalogue );
}

/**
 * Catalogue of policy icon slugs the site supports.
 *
 * Same shape as the amenity catalogue but for the icons that prefix
 * each row in the Policies table. Keeping the slug list explicit
 * means the global-policies admin UI can offer a dropdown instead
 * of asking editors to know the icon filenames.
 *
 * @since 1.0.0
 *
 * @return array<string,array{label:string,icon:string}>
 */
function cwc_policy_icon_catalogue() {
	$pool = get_option( 'cwc_icon_pool', [] );
	
	// If pool is empty, provide the legacy slugs as the starting point
	if ( empty( $pool ) ) {
		$catalogue = [
			'check-in'   => [ 'label' => 'Check-in',        'icon' => 'check-in' ],
			'check-out'  => [ 'label' => 'Check-out',       'icon' => 'check-out' ],
			'breakfast'  => [ 'label' => 'Breakfast',       'icon' => 'breakfast' ],
			'reception'  => [ 'label' => 'Reception Hours', 'icon' => 'reception' ],
			'children'   => [ 'label' => 'Children & Beds', 'icon' => 'children' ],
			'no-age'     => [ 'label' => 'Age Restriction', 'icon' => 'no-age' ],
			'smoking'    => [ 'label' => 'Smoking',         'icon' => 'smoking' ],
			'smoke-free' => [ 'label' => 'Non-Smoking',     'icon' => 'smoke-free' ],
			'wifi'       => [ 'label' => 'Wi-Fi',           'icon' => 'wifi' ],
			'parking'    => [ 'label' => 'Parking',         'icon' => 'parking' ],
			'pool'       => [ 'label' => 'Pool',            'icon' => 'pool' ],
		];
	} else {
		$catalogue = [];
		foreach ( $pool as $slug => $val ) {
			$catalogue[ $slug ] = [ 'label' => ucfirst( str_replace( '-', ' ', $slug ) ), 'icon' => $slug ];
		}
	}

	/**
	 * Filter the policy icon catalogue.
	 *
	 * @since 1.0.0
	 *
	 * @param array<string,array{label:string,icon:string}> $catalogue Slug → label + icon.
	 */
	return apply_filters( 'cwc_policy_icon_catalogue', $catalogue );
}

/**
 * Catalogue of bed types the site supports.
 *
 * @since 1.2.0
 *
 * @return array<string,array{label:string,icon:string}>
 */
function cwc_bed_catalogue() {
	$dynamic = get_option( 'cwc_dynamic_beds', [] );
	
	if ( empty( $dynamic ) ) {
		$catalogue = [
			'queen'  => [ 'label' => __( 'Queen Bed', 'cwc-accommodations' ),  'icon' => 'queen-bed' ],
			'king'   => [ 'label' => __( 'King Bed', 'cwc-accommodations' ),   'icon' => 'king-bed' ],
			'twin'   => [ 'label' => __( 'Twin Bed', 'cwc-accommodations' ),   'icon' => 'children-beds' ],
			'single' => [ 'label' => __( 'Single Bed', 'cwc-accommodations' ), 'icon' => 'check-in' ],
		];
	} else {
		$catalogue = $dynamic;
	}

	return apply_filters( 'cwc_bed_catalogue', $catalogue );
}

/**
 * Resolve an icon slug to the public URL of its SVG file.
 *
 * Looks the slug up in the amenity catalogue first, then the policy
 * catalogue (some slugs like `wifi` appear in both). Returns an
 * empty string for an unknown slug so the front-end render can
 * skip the `<img>` cleanly.
 *
 * `rawurlencode()` keeps filesystem-safe characters intact while
 * escaping `&` in `garden&terrace.svg` so the URL survives the
 * trip from HTML attribute → HTTP request without the `&` being
 * parsed as a separate query parameter.
 *
 * @since 1.0.0
 *
 * @param string $slug Icon slug (e.g. `wifi`, `check-in`).
 * @return string Absolute URL to the SVG, or empty string if unknown.
 */
function cwc_icon_url_for_slug( $slug ) {
	$slug = (string) $slug;
	if ( '' === $slug ) {
		return '';
	}

	// 1. Check Dynamic Icon Pool
	$pool = get_option( 'cwc_icon_pool', [] );
	if ( isset( $pool[ $slug ] ) ) {
		$val = $pool[ $slug ];
		// Case A: Media ID (Numeric)
		if ( is_numeric( $val ) ) {
			return wp_get_attachment_url( (int) $val );
		}
		// Case B: Filename (String)
		return get_stylesheet_directory_uri() . '/assets/images/' . rawurlencode( $val );
	}

	// 2. Fallback to hardcoded legacy filenames for backward compatibility
	$legacy_map = [
		'wifi'       => 'free-wifi.svg',
		'parking'    => 'free-parking.svg',
		'pool'       => 'swimming-pool.svg',
		'air'        => 'air-conditioning.svg',
		'garden'     => 'garden&terrace.svg',
		'bar'        => 'bar.svg',
		'coffee'     => 'coffee-shop.svg',
		'smoke-free' => 'smoke-free.svg',
		'check-in'   => 'check-in.svg',
		'check-out'  => 'checkout.svg',
		'breakfast'  => 'breakfast.svg',
		'reception'  => 'reception-hours.svg',
		'children'   => 'children-beds.svg',
		'no-age'     => 'age-restriction.svg',
		'smoking'    => 'no-smoking.svg',
	];

	$file = $legacy_map[ $slug ] ?? '';
	if ( '' === $file ) {
		return '';
	}

	return get_stylesheet_directory_uri() . '/assets/images/' . rawurlencode( $file );
}

/* ---------------------------------------------------------
 * Per-post helpers
 * --------------------------------------------------------- */

/**
 * Test whether the current request is rendering a single accommodation.
 *
 * Used by the `room-info`, `room-gallery`, and `other-rooms` blocks
 * to decide whether to fall back from empty attributes to post-meta.
 * Falls back to inspecting `get_post()` (not just `is_singular()`)
 * because blocks can render in REST/preview contexts where the main
 * query hasn't fully populated yet.
 *
 * @since 1.0.0
 *
 * @param int|null $post_id Optional explicit post ID.
 * @return bool
 */
function cwc_is_accommodation_context( $post_id = null ) {
	if ( null === $post_id ) {
		$post = get_post();
		if ( ! $post instanceof WP_Post ) {
			return false;
		}
		$post_id = (int) $post->ID;
	}

	return 'accommodation' === get_post_type( (int) $post_id );
}

/**
 * Decode the comma-separated `_cwc_amenities` meta into amenity rows.
 *
 * Resolves each saved slug against `cwc_amenity_catalogue()` so the
 * front-end gets a list of `{ icon, label }` ready to render.
 * Unknown slugs (catalogue removed since this room was saved) are
 * silently dropped — they would have rendered as empty chips
 * otherwise.
 *
 * @since 1.0.0
 *
 * @param int $post_id Accommodation post ID.
 * @return array<int,array{icon:string,label:string}>
 */
function cwc_accommodation_amenities( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return [];
	}

	$raw = (string) get_post_meta( $post_id, '_cwc_amenities', true );
	if ( '' === $raw ) {
		return [];
	}

	$slugs     = array_filter( array_map( 'trim', explode( ',', $raw ) ) );
	$catalogue = cwc_amenity_catalogue();
	$out       = [];

	foreach ( $slugs as $slug ) {
		if ( isset( $catalogue[ $slug ] ) ) {
			$out[] = [
				'icon'  => $catalogue[ $slug ]['icon'] ?? '',
				'label' => $catalogue[ $slug ]['label'],
			];
		}
	}

	return $out;
}

/**
 * Decode the comma-separated `_cwc_inclusions` meta into an array of strings.
 *
 * Each entry is trimmed and cleaned so it can be rendered directly
 * as a text pill.
 *
 * @since 1.1.0
 *
 * @param int $post_id Accommodation post ID.
 * @return array<int,string>
 */
function cwc_accommodation_inclusions( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return [];
	}

	$raw = (string) get_post_meta( $post_id, '_cwc_inclusions', true );
	if ( '' === $raw ) {
		return [];
	}

	$slugs     = array_filter( array_map( 'trim', explode( ',', $raw ) ) );
	$catalogue = cwc_inclusion_catalogue();
	$out       = [];

	foreach ( $slugs as $slug ) {
		if ( isset( $catalogue[ $slug ] ) ) {
			$out[] = $catalogue[ $slug ]['label'];
		}
	}

	return $out;
}

/**
 * Resolve `_cwc_gallery_ids` meta into URL/alt rows.
 *
 * The room-gallery block expects 4 image slots; this returns up to
 * however many IDs are saved (the block pads / truncates as needed).
 * Each row is `{ url, alt }` matching the block's existing schema.
 *
 * Skips attachments that no longer exist so a deleted media item
 * can't crash the render.
 *
 * @since 1.0.0
 *
 * @param int $post_id Accommodation post ID.
 * @return array<int,array{url:string,alt:string}>
 */
function cwc_accommodation_gallery_images( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return [];
	}

	$raw = (string) get_post_meta( $post_id, '_cwc_gallery_ids', true );
	if ( '' === $raw ) {
		return [];
	}

	$ids = array_filter( array_map( 'intval', explode( ',', $raw ) ) );
	$out = [];

	foreach ( $ids as $attachment_id ) {
		$url = wp_get_attachment_image_url( $attachment_id, 'large' );
		if ( ! $url ) {
			continue;
		}
		$out[] = [
			'url' => $url,
			'alt' => (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
		];
	}

	return $out;
}

/**
 * Inject "Accommodations" between Home and the room title on
 * single-accommodation breadcrumbs.
 *
 * The generic `cwc_build_breadcrumbs()` produces `Home → <Room>` for
 * any singular CPT. For accommodations we want the landing page to
 * appear in the trail so visitors can hop back to the rooms grid
 * with one click.
 *
 * Mirrors the same pattern used by the cwc_album breadcrumb filter.
 *
 * @since 1.0.0
 *
 * @param array<int,array{label:string,url:?string}> $crumbs Existing crumbs.
 * @return array<int,array{label:string,url:?string}>
 */
function cwc_inject_accommodation_breadcrumb( $crumbs ) {
	if ( ! is_singular( 'accommodation' ) ) {
		return $crumbs;
	}

	if ( ! is_array( $crumbs ) || count( $crumbs ) < 2 ) {
		return $crumbs;
	}

	$accommodations_page = get_page_by_path( 'accommodations' );
	$accommodations_url  = $accommodations_page instanceof WP_Post
		? (string) get_permalink( $accommodations_page )
		: home_url( '/accommodations/' );

	$home = $crumbs[0];
	$tail = array_slice( $crumbs, 1 );

	return array_merge(
		[ $home ],
		[
			[
				'label' => __( 'Accommodations', 'child-cwcwake' ),
				'url'   => $accommodations_url,
			],
		],
		$tail
	);
}
add_filter( 'cwc_breadcrumbs_items', 'cwc_inject_accommodation_breadcrumb' );

/**
 * Resolve the current request's accommodation availability.
 *
 * Centralised so the front-end blocks (room-info today, plus
 * anything else later) all read the same value with the same
 * default. Falls back to `available` so an un-set room never
 * accidentally renders as "Maintenance".
 *
 * @since 1.0.0
 *
 * @param int $post_id Accommodation post ID.
 * @return string `available` | `fully-booked` | `maintenance`.
 */
function cwc_accommodation_availability( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return 'available';
	}

	$rooms = cwc_get_physical_rooms( $post_id );
	
	if ( empty( $rooms ) ) {
		// Fallback to old meta if no physical rooms are defined
		$old_value = (string) get_post_meta( $post_id, '_cwc_availability', true );
		return in_array( $old_value, [ 'available', 'fully-booked' ], true ) ? $old_value : 'available';
	}

	$has_available = false;
	foreach ( $rooms as $room ) {
		if ( 'available' === ( $room['status'] ?? '' ) ) {
			$has_available = true;
			break;
		}
	}

	return $has_available ? 'available' : 'fully-booked';
}

/**
 * Resolve `_cwc_beds` meta into a list of { icon, label } rows.
 *
 * @since 1.2.0
 *
 * @param int $post_id Accommodation post ID.
 * @return array<int,array{icon_url:string,label:string}>
 */
function cwc_get_room_beds( $post_id ) {
	$beds_raw = get_post_meta( $post_id, '_cwc_beds', true );
	if ( empty( $beds_raw ) ) {
		return [];
	}

	$beds_data = json_decode( $beds_raw, true ) ?: [];
	$catalogue = cwc_bed_catalogue();
	$out       = [];

	foreach ( $beds_data as $row ) {
		$type  = $row['type'] ?? '';
		$count = (int) ( $row['count'] ?? 0 );
		if ( $count <= 0 || ! isset( $catalogue[ $type ] ) ) {
			continue;
		}

		$label = sprintf( _n( '%1$s %2$s', '%1$s %2$ss', $count, 'cwc-accommodations' ), $count, $catalogue[ $type ]['label'] );
		$out[] = [
			'icon_url' => cwc_icon_url_for_slug( $catalogue[ $type ]['icon'] ),
			'label'    => $label,
		];
	}

	return $out;
}