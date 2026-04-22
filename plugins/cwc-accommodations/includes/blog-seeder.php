<?php
/**
 * CWC Accommodations — Blog Post Seeder.
 *
 * One-shot seeder that generates the dataset the Blogs page
 * (`/plan-your-trip/blogs/`) needs to render in a non-empty state
 * before editors fill it in:
 *
 *   - 4 default categories (Events / Resort News / Pro Tips / Local Guide).
 *   - 5 "featured" posts (`_cwc_blog_featured = 1`) for the asymmetric
 *     grid in the Featured Blogs section.
 *   - 3 "events" posts with future `_cwc_event_date` meta for the
 *     Upcoming Events timeline.
 *   - 6 filler posts so the All Blogs grid + pagination has enough
 *     records to demo with.
 *
 * Why a separate seeder lives in this plugin:
 *
 *   - The Blogs page is functionally tied to the room booking flow
 *     (it surfaces resort news / events). Keeping the seed alongside
 *     the rest of the plugin's content seeding (`migrate.php`) means
 *     a single activation lights up every customer-facing surface.
 *   - The blog blocks themselves stay theme-side because they're
 *     presentational, but the plugin owns the *data shape* (meta
 *     keys + which posts are featured) so it survives a theme swap.
 *
 * Idempotency:
 *
 *   - Guarded by the `cwc_blog_posts_seeded` option so it never
 *     runs twice. Delete the option (`wp option delete
 *     cwc_blog_posts_seeded`) on a dev environment to re-seed.
 *   - Per-post duplicate detection uses a slug-keyed `WP_Query`
 *     (replacing the deprecated-since-6.2 `get_page_by_title()`)
 *     so editors can rename posts after seeding without breaking
 *     idempotency on a re-run.
 *
 * @package CWC_Accommodations
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------------------------------------------------
 * Public meta key constants
 *
 * Exported so the theme-side blocks can read the same key names
 * without hard-coding magic strings — and so a future migration
 * can rename the keys in one place.
 * --------------------------------------------------------- */

if ( ! defined( 'CWC_BLOG_META_FEATURED' ) ) {
	define( 'CWC_BLOG_META_FEATURED', '_cwc_blog_featured' );
}

if ( ! defined( 'CWC_BLOG_META_EVENT_DATE' ) ) {
	define( 'CWC_BLOG_META_EVENT_DATE', '_cwc_event_date' );
}

/* ---------------------------------------------------------
 * Seeder hook
 *
 * Runs once on `init` priority `40` (after the CPT + migration
 * hooks at priorities 10–34) and is option-guarded so production
 * never pays for the work twice. Front-end requests are skipped
 * entirely — no seeding outside of an admin / WP-CLI context.
 * --------------------------------------------------------- */

/**
 * Run the blog seeder once per environment.
 *
 * Front-end requests skip the seeder entirely so a cold visitor
 * never pays the lookup cost. We explicitly allow `wp-cron.php`
 * and WP-CLI so a fresh dev install can be re-seeded headlessly.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_maybe_seed_blog_posts(): void {
	if ( get_option( 'cwc_blog_posts_seeded' ) ) {
		return;
	}

	if ( ! is_admin() && ! wp_doing_cron() && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	}

	$created = cwc_seed_blog_posts();

	/*
	 * Marking the option as the timestamp (not just `true`) gives
	 * us forensic value later — `wp option get cwc_blog_posts_seeded`
	 * shows when the dataset landed.
	 */
	update_option( 'cwc_blog_posts_seeded', time() );

	/**
	 * Fires once after the blog seeder finishes.
	 *
	 * Other plugins / themes can hook this to e.g. attach default
	 * featured images or send a "site ready" notification.
	 *
	 * @since 1.0.0
	 *
	 * @param int $created Number of new posts created during the run.
	 */
	do_action( 'cwc_blog_posts_seeded', $created );
}
add_action( 'init', 'cwc_maybe_seed_blog_posts', 40 );

/* ---------------------------------------------------------
 * Core seeder
 * --------------------------------------------------------- */

/**
 * Seed sample blog posts and the four default categories.
 *
 * Splits the dataset into three buckets that mirror the Blogs page
 * layout (`designs/blogs-design.md`):
 *
 *   - Featured (`featured = true`) — the 5 cards in the asymmetric
 *     hero grid.
 *   - Events (`event_offset_days = N`) — gets `_cwc_event_date` meta
 *     N days in the future so the timeline renders a believable
 *     near-term schedule on first paint.
 *   - Filler — populates the All Blogs grid + pagination so editors
 *     can preview the layout before adding real content.
 *
 * Existing posts are detected by slug (NOT title) because slugs are
 * the actual unique key WordPress enforces — two seeds with the
 * same title would otherwise both succeed and quietly create
 * `the-history-of-cwc-wake-park-2`.
 *
 * @since 1.0.0
 *
 * @return int Number of new posts created (existing matches are skipped).
 */
function cwc_seed_blog_posts(): int {
	$cat_ids = cwc_seed_blog_categories();
	if ( empty( $cat_ids ) ) {
		return 0;
	}

	$posts_to_seed = cwc_blog_seed_dataset();

	$count = 0;
	foreach ( $posts_to_seed as $row ) {
		if ( cwc_blog_seed_insert_row( $row, $cat_ids ) ) {
			$count++;
		}
	}

	return $count;
}

/**
 * Ensure the four default blog categories exist.
 *
 * Returns a `name => term_id` map so the per-post insert loop can
 * resolve each post's category in O(1) without re-querying the
 * taxonomy table per row.
 *
 * @since 1.0.0
 *
 * @return array<string,int> Category name → term ID. Empty when taxonomy is unavailable.
 */
function cwc_seed_blog_categories(): array {
	if ( ! taxonomy_exists( 'category' ) ) {
		return [];
	}

	$names = [ 'Events', 'Resort News', 'Pro Tips', 'Local Guide' ];

	$ids = [];
	foreach ( $names as $name ) {
		$existing = get_term_by( 'name', $name, 'category' );
		if ( $existing instanceof WP_Term ) {
			$ids[ $name ] = (int) $existing->term_id;
			continue;
		}

		$inserted = wp_insert_term( $name, 'category' );
		if ( is_wp_error( $inserted ) ) {
			continue;
		}

		$ids[ $name ] = (int) $inserted['term_id'];
	}

	return $ids;
}

/**
 * Canonical seed dataset for the Blogs page.
 *
 * Each row is a flat associative array — no objects — so it
 * round-trips cleanly through `apply_filters()`. `featured` flips
 * the `_cwc_blog_featured` meta on; `event_offset_days` (when set)
 * stamps `_cwc_event_date` to that many days in the future so the
 * Upcoming Events timeline shows a believable near-term schedule.
 *
 * Filterable so a host site can extend the seed (e.g. a launch
 * checklist's "Add CEO welcome post") without forking the plugin.
 *
 * @since 1.0.0
 *
 * @return array<int,array<string,mixed>>
 */
function cwc_blog_seed_dataset(): array {
	$dataset = [
		[
			'title'    => 'Plan Your Trip: What to Know Before Visiting CWC',
			'cat'      => 'Local Guide',
			'excerpt'  => 'From travel essentials to insider tips and everything you need to plan a smooth and stress-free visit to CWC. Perfect for first-time visitors who want to make the most of every minute.',
			'featured' => true,
		],
		[
			'title'    => 'Ride the Waves: Wakeboarding Experiences You Shouldn\'t Miss',
			'cat'      => 'Pro Tips',
			'excerpt'  => 'Feel the thrill of riding across the cable wakes. Discover the best wakeboarding venues, ideal times to ride, and why CWC is a must-visit for water sports lovers.',
			'featured' => true,
		],
		[
			'title'    => 'Experience the Energy: Events and Nightlife at CWC',
			'cat'      => 'Events',
			'excerpt'  => 'Discover after-dark events, music nights, and live performances that turn CWC from a daytime adventure into a non-stop lifestyle destination.',
			'featured' => true,
		],
		[
			'title'    => 'Top 5 Hidden Spots in CamSur',
			'cat'      => 'Local Guide',
			'excerpt'  => 'Explore the natural beauty of Camarines Sur beyond the wakeboarding park — quiet beaches, mountain trails, and food finds locals love.',
			'featured' => true,
		],
		[
			'title'    => 'A Beginner\'s Guide to Cable Parks',
			'cat'      => 'Pro Tips',
			'excerpt'  => 'First time at a cable park? Here is everything you need to know before you hit the water — from gear to etiquette.',
			'featured' => true,
		],

		[
			'title'             => 'Sunset Ride Sessions',
			'cat'               => 'Events',
			'excerpt'           => 'Ride into golden hour and experience one of the most relaxing yet visually stunning moments at CWC. As the sun begins to set, the lake transforms into a warm, glowing backdrop — perfect for smooth cable runs and effortless tricks. Whether you\'re a beginner enjoying a calm ride or an experienced rider chasing that perfect silhouette shot, the atmosphere is unmatched. With laid-back music playing and a chill lakeside vibe, it\'s not just about the ride — it\'s about slowing down, soaking in the view, and ending your day on a high note.',
			'event_offset_days' => 7,
		],
		[
			'title'             => 'Summer Wake Championship 2026',
			'cat'               => 'Events',
			'excerpt'           => 'The biggest competition of the year is coming back. Register now to compete or come cheer on your favorite riders.',
			'event_offset_days' => 14,
		],
		[
			'title'             => 'Evening Acoustic Sessions at the Bar',
			'cat'               => 'Events',
			'excerpt'           => 'Unwind after a day on the water with live acoustic performances every Friday at the resort bar.',
			'event_offset_days' => 21,
		],

		[
			'title'   => 'New Luxury Villas Now Open for Booking',
			'cat'     => 'Resort News',
			'excerpt' => 'Experience a new level of comfort with our newly launched premium villas overlooking the park.',
		],
		[
			'title'   => 'Sustainable Tourism at CWC',
			'cat'     => 'Resort News',
			'excerpt' => 'How we are working to protect the local environment while welcoming guests from around the world.',
		],
		[
			'title'   => 'CWC Gear Guide: Choosing Your First Board',
			'cat'     => 'Pro Tips',
			'excerpt' => 'Choosing the right board for your style — a quick walk-through of shapes, sizes, and skill levels.',
		],
		[
			'title'   => 'Traveling to CamSur: Tips & Tricks',
			'cat'     => 'Local Guide',
			'excerpt' => 'The easiest ways to get to CWC from Manila — flights, vans, and the scenic road option.',
		],
		[
			'title'   => 'The History of CWC Wake Park',
			'cat'     => 'Resort News',
			'excerpt' => 'From a dream to a world-class destination — the story behind CWC.',
		],
		[
			'title'   => 'Resort Wellness Month',
			'cat'     => 'Resort News',
			'excerpt' => 'Focus on your health with our special yoga sessions and healthy meal plans all through May.',
		],
	];

	/**
	 * Filter the blog seeder dataset.
	 *
	 * Lets sites add or rewrite seed rows without forking the
	 * plugin. Each row must be an array containing at least `title`
	 * and `cat`; supported optional keys are `excerpt`, `featured`,
	 * and `event_offset_days`.
	 *
	 * @since 1.0.0
	 *
	 * @param array<int,array<string,mixed>> $dataset Default seed rows.
	 */
	return apply_filters( 'cwc_blog_seed_dataset', $dataset );
}

/**
 * Insert a single seed row, skipping if a post with the same slug exists.
 *
 * Resolves the desired slug via `sanitize_title()` and looks for an
 * existing post by that slug across **any** status (so a draft from
 * a previous run still counts as "already seeded"). When a row
 * declares `event_offset_days`, the future event date is stored in
 * `_cwc_event_date` (Y-m-d) so the timeline block can sort + group
 * by month without parsing freeform text.
 *
 * @since 1.0.0
 *
 * @param array<string,mixed> $row     Seed row.
 * @param array<string,int>   $cat_ids Category name → term ID map from `cwc_seed_blog_categories()`.
 * @return bool True when a post was inserted, false on skip / failure.
 */
function cwc_blog_seed_insert_row( array $row, array $cat_ids ): bool {
	$title = isset( $row['title'] ) ? (string) $row['title'] : '';
	$cat   = isset( $row['cat'] ) ? (string) $row['cat'] : '';

	if ( '' === $title || ! isset( $cat_ids[ $cat ] ) ) {
		return false;
	}

	$slug = sanitize_title( $title );
	if ( cwc_blog_post_exists_by_slug( $slug ) ) {
		return false;
	}

	$excerpt = isset( $row['excerpt'] ) ? (string) $row['excerpt'] : '';

	$post_id = wp_insert_post(
		[
			'post_title'    => $title,
			'post_name'     => $slug,
			'post_content'  => $excerpt . ' Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
			'post_excerpt'  => $excerpt,
			'post_status'   => 'publish',
			'post_type'     => 'post',
			'post_category' => [ $cat_ids[ $cat ] ],
		],
		true
	);

	if ( is_wp_error( $post_id ) || 0 === (int) $post_id ) {
		return false;
	}

	if ( ! empty( $row['featured'] ) ) {
		update_post_meta( (int) $post_id, CWC_BLOG_META_FEATURED, '1' );
	}

	if ( isset( $row['event_offset_days'] ) ) {
		$offset = max( 1, (int) $row['event_offset_days'] );
		$date   = gmdate( 'Y-m-d', strtotime( "+{$offset} days" ) );
		update_post_meta( (int) $post_id, CWC_BLOG_META_EVENT_DATE, $date );
	}

	return true;
}

/**
 * Check whether a `post` already exists with the given slug.
 *
 * `WP_Query` instead of `get_page_by_title()` because the latter
 * is deprecated since WordPress 6.2 and only matched by title
 * anyway — slug-based detection is what we actually want for
 * idempotency.
 *
 * @since 1.0.0
 *
 * @param string $slug Post slug to look for.
 * @return bool True when at least one post (any status) owns the slug.
 */
function cwc_blog_post_exists_by_slug( string $slug ): bool {
	if ( '' === $slug ) {
		return false;
	}

	$query = new WP_Query(
		[
			'post_type'              => 'post',
			'post_status'            => 'any',
			'name'                   => $slug,
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		]
	);

	return $query->have_posts();
}
