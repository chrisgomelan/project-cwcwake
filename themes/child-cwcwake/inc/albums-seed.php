<?php
/**
 * Self-healing seeder for the three top-level Album categories.
 *
 * Creates and maintains the three top-level `cwc_album` posts that
 * back the Gallery landing page (`/gallery/`):
 *
 *   - Events          (slug: events)
 *   - Lifestyle       (slug: lifestyle)
 *   - Explore CamSur  (slug: explore-camsur)
 *
 * Reliability behaviours:
 *
 *   1. Per-slug existence checks (no global "ran once" flag), so
 *      accidentally permanently-deleting one category re-creates it
 *      on the next page load without affecting the others.
 *   2. Restore-from-trash: if a category is found in the trash, it
 *      gets `wp_untrash_post()` + republished instead of being
 *      duplicated under a `-2` slug.
 *   3. Title fallback: when slug lookup fails (because Gutenberg
 *      renamed it to `events-2` after a slug collision), we also
 *      probe by post_title so a previously seeded post is reused.
 *   4. Parent normalisation: even when found, we make sure
 *      `post_parent = 0` (top-level) so the category cannot be
 *      mistakenly nested.
 *   5. Featured image is wired from the bundled webp covers when
 *      missing — never overwritten, so editors can swap them.
 *
 * To run an explicit, full re-seed: clear the
 * `cwc_album_categories_seed_lock` transient (it is only used to
 * throttle disk-touching writes to once per minute, not to gate
 * seeding entirely).
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Catalogue of top-level album categories to seed.
 *
 * Each entry maps the album slug to the data we need at insert
 * time. `cover_basename` is the filename (without size suffix) of
 * the existing media library upload — see
 * `cwc_albums_find_attachment_by_basename()`.
 *
 * Editors should treat the slug as immutable; renaming a top-level
 * album breaks both the static `/gallery/` cards and the parent
 * enforcement in `inc/albums-parent-enforcement.php`.
 *
 * @since 1.0.0
 *
 * @return array<string, array{title:string, excerpt:string, cover_basename:string, menu_order:int}>
 */
function cwc_album_categories_catalogue() {
	return array(
		'events'         => array(
			'title'          => 'Events',
			'excerpt'        => 'Relive the highlights of CWC\'s most exciting events — from high-energy wakeboarding competitions to unforgettable nights and crowd-filled celebrations. Browse through moments that capture the energy, atmosphere, and community that make every event at CWC worth experiencing.',
			'cover_basename' => 'events.webp',
			'menu_order'     => 1,
		),
		'lifestyle'      => array(
			'title'          => 'Lifestyle',
			'excerpt'        => 'A glimpse into the laid-back lifestyle at CWC — sun-drenched mornings, scenic afternoons, and the easygoing rhythm of life by the water.',
			'cover_basename' => 'lifestyle.webp',
			'menu_order'     => 2,
		),
		'explore-camsur' => array(
			'title'          => 'Explore CamSur',
			'excerpt'        => 'Step beyond CWC and discover the natural beauty, hidden corners, and local charm of Camarines Sur.',
			'cover_basename' => 'explore-camsur.webp',
			'menu_order'     => 3,
		),
	);
}

/**
 * The list of canonical top-level album slugs.
 *
 * Exposed as a helper so other code (e.g. parent enforcement) can
 * stay in sync with the catalogue without duplicating the keys.
 *
 * @since 1.0.0
 *
 * @return string[]
 */
function cwc_album_canonical_parent_slugs() {
	return array_keys( cwc_album_categories_catalogue() );
}

/**
 * Look up an attachment ID by its uploaded file basename.
 *
 * `_wp_attached_file` stores paths like `2026/04/events.webp`, so
 * we match with a `LIKE` ending in the basename. Returns 0 when no
 * attachment is found (common in fresh installs that haven't run
 * the media imports yet).
 *
 * @since 1.0.0
 *
 * @param string $basename File basename, e.g. `events.webp`.
 * @return int Attachment ID, or 0.
 */
function cwc_albums_find_attachment_by_basename( $basename ) {
	if ( '' === $basename ) {
		return 0;
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array(
				array(
					'key'     => '_wp_attached_file',
					'value'   => '/' . ltrim( $basename, '/' ),
					'compare' => 'LIKE',
				),
			),
		)
	);

	$ids = $query->posts;
	return ! empty( $ids ) ? (int) $ids[0] : 0;
}

/**
 * Find an existing category album by canonical slug or fallback by title.
 *
 * Search order:
 *   1. Exact slug match (any status, including `trash`) — the
 *      common case once the seeder has run.
 *   2. Title match (any status), to recover after slug collisions
 *      with legacy pages bumped the slug to `<slug>-2`.
 *
 * @since 1.0.0
 *
 * @param string $slug  Canonical slug, e.g. `events`.
 * @param string $title Display title, e.g. `Events`.
 * @return WP_Post|null The album post if found, else null.
 */
function cwc_album_find_category_post( $slug, $title ) {
	// Pass 1: exact slug match (across all statuses).
	$by_slug = get_posts(
		array(
			'name'             => $slug,
			'post_type'        => 'cwc_album',
			'post_status'      => 'any',
			'posts_per_page'   => 1,
			'suppress_filters' => false,
		)
	);
	if ( ! empty( $by_slug ) ) {
		return $by_slug[0];
	}

	// Pass 2: title fallback (handles `events-2` style slug bumps).
	if ( '' !== $title ) {
		$by_title = get_posts(
			array(
				'title'            => $title,
				'post_type'        => 'cwc_album',
				'post_status'      => 'any',
				'posts_per_page'   => 1,
				'suppress_filters' => false,
			)
		);
		if ( ! empty( $by_title ) ) {
			return $by_title[0];
		}
	}

	return null;
}

/**
 * Make sure a single category album exists, is published, and at top level.
 *
 * Encapsulates the heal-or-create logic for one catalogue entry so
 * `cwc_seed_album_categories()` reads as a flat foreach.
 *
 * Slug repair: when a previous insert had its slug bumped to
 * `<slug>-2` (because of a legacy page collision) and we later
 * removed the colliding page, this updates the album back to the
 * canonical slug. We pass the slug change through `wp_update_post()`
 * which re-runs `wp_unique_post_slug()` — so if a collision is still
 * present the slug stays bumped instead of throwing.
 *
 * @since 1.0.0
 *
 * @param string $slug Canonical slug, e.g. `events`.
 * @param array  $data Catalogue row from {@see cwc_album_categories_catalogue()}.
 * @return int Resolved post ID (0 on failure).
 */
function cwc_album_ensure_category( $slug, array $data ) {
	$existing = cwc_album_find_category_post( $slug, $data['title'] );

	if ( $existing instanceof WP_Post ) {
		$updates = array( 'ID' => (int) $existing->ID );

		// Restore from trash automatically.
		if ( 'trash' === $existing->post_status ) {
			wp_untrash_post( (int) $existing->ID );
			$updates['post_status'] = 'publish';
		} elseif ( 'publish' !== $existing->post_status ) {
			$updates['post_status'] = 'publish';
		}

		// Force top-level (categories are never nested).
		if ( 0 !== (int) $existing->post_parent ) {
			$updates['post_parent'] = 0;
		}

		// Heal a slug bumped by a previous collision.
		if ( $existing->post_name !== $slug ) {
			$updates['post_name'] = $slug;
		}

		// Heal an editor-cleared menu_order so the static cards stay ordered.
		if ( 0 === (int) $existing->menu_order ) {
			$updates['menu_order'] = (int) $data['menu_order'];
		}

		if ( count( $updates ) > 1 ) {
			wp_update_post( $updates );
		}

		$post_id = (int) $existing->ID;
	} else {
		$post_id = wp_insert_post(
			array(
				'post_type'    => 'cwc_album',
				'post_status'  => 'publish',
				'post_title'   => $data['title'],
				'post_name'    => $slug,
				'post_excerpt' => $data['excerpt'],
				'post_content' => '',
				'post_parent'  => 0,
				'menu_order'   => (int) $data['menu_order'],
			),
			true
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return 0;
		}
	}

	// Wire the cover when the editor hasn't set one yet.
	if ( ! has_post_thumbnail( $post_id ) ) {
		$attachment_id = cwc_albums_find_attachment_by_basename( $data['cover_basename'] );
		if ( $attachment_id > 0 ) {
			set_post_thumbnail( $post_id, $attachment_id );
		}
	}

	return $post_id;
}

/*
---------------------------------------------------------
 * Legacy page cleanup
 * ---------------------------------------------------------
 */

/**
 * Trash the legacy gallery child PAGES that collide with album slugs.
 *
 * `cwc_create_initial_pages()` originally seeded three child pages
 * under `Plan Your Trip > Gallery`:
 *
 *   - plan-your-trip/gallery/events
 *   - plan-your-trip/gallery/lifestyle
 *   - plan-your-trip/gallery/explore-camsur
 *
 * Now that albums own that part of the URL space, those pages are
 * orphaned AND their slugs (`events`, `lifestyle`, `explore-camsur`)
 * collide with the album slugs, forcing the seeder's inserts into
 * `events-2` / `lifestyle-2` / `explore-camsur-2` and breaking the
 * `/gallery/events/` permalink.
 *
 * This trashes them once and remembers via an option so we don't
 * keep re-trashing pages an editor might intentionally restore.
 * Trashing (vs. deleting) gives editors a fallback if they need
 * any of the original copy.
 *
 * Hooked at `init` priority `33` — before the seeder (priority 35)
 * so the slug repair in `cwc_album_ensure_category()` succeeds on
 * the same request.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_album_trash_legacy_gallery_pages() {
	if ( get_option( 'cwc_album_legacy_gallery_pages_trashed' ) ) {
		return;
	}

	$legacy_paths = array(
		'plan-your-trip/gallery/events',
		'plan-your-trip/gallery/lifestyle',
		'plan-your-trip/gallery/explore-camsur',
	);

	foreach ( $legacy_paths as $path ) {
		$page = get_page_by_path( $path );
		if ( $page instanceof WP_Post && 'page' === $page->post_type && 'trash' !== $page->post_status ) {
			wp_trash_post( (int) $page->ID );
		}
	}

	update_option( 'cwc_album_legacy_gallery_pages_trashed', true );
}
add_action( 'init', 'cwc_album_trash_legacy_gallery_pages', 33 );

/**
 * Make sure all three category albums exist on every request.
 *
 * Cheap to run because:
 *   - The whole pass is gated behind a 60-second transient lock so
 *     we don't hit the DB on every request once the categories
 *     are healthy.
 *   - Each per-slug check is a single indexed query.
 *
 * Hooked at `init` priority `35` (after the CPT registers at
 * default priority `10`).
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_seed_album_categories() {
	if ( ! post_type_exists( 'cwc_album' ) ) {
		return;
	}

	/*
	 * Throttle: only run a real heal pass at most once per minute,
	 * regardless of how many requests hit the site.
	 *
	 * The lock key includes a version suffix so that bumping this
	 * file's behaviour (e.g. the legacy-page cleanup we just added)
	 * naturally invalidates any in-flight lock from a previous code
	 * version — the user gets a fresh heal pass without having to
	 * manually clear the transient.
	 */
	$lock_key = 'cwc_album_categories_seed_lock_v2';
	if ( get_transient( $lock_key ) ) {
		return;
	}
	set_transient( $lock_key, 1, MINUTE_IN_SECONDS );

	foreach ( cwc_album_categories_catalogue() as $slug => $data ) {
		cwc_album_ensure_category( $slug, $data );
	}
}
add_action( 'init', 'cwc_seed_album_categories', 35 );

/**
 * Clean up the legacy one-shot guard if it's still present.
 *
 * Earlier versions of this file gated seeding behind a single
 * `cwc_album_categories_seeded` option. The new self-healing model
 * doesn't need it; remove it once so old installs join the new
 * world without manual DB editing.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_album_drop_legacy_seed_flag() {
	if ( get_option( 'cwc_album_categories_seeded' ) ) {
		delete_option( 'cwc_album_categories_seeded' );
	}
}
add_action( 'init', 'cwc_album_drop_legacy_seed_flag', 34 );
