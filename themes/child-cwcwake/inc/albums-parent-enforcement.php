<?php
/**
 * Editorial guard-rails for the `cwc_album` post type.
 *
 * Enforces the rule that the only valid top-level Albums are the
 * three fixed categories defined in
 * {@see cwc_album_categories_catalogue()}: `events`, `lifestyle`,
 * and `explore-camsur`. Every other album must be a child of one
 * of those three.
 *
 * What this file does:
 *
 *   1. **REST + classic-editor query filter** — restricts the Parent
 *      autocomplete dropdown in the block editor (and any other
 *      caller asking for "potential parents") to only the three
 *      canonical categories. Eliminates the "No items found"
 *      papercut by handing Gutenberg a curated list up-front.
 *
 *   2. **Save guard** (`wp_insert_post_data`) — when an editor
 *      tries to publish an album whose parent isn't one of the
 *      three categories AND the post itself isn't a category, we
 *      demote the status to `draft` so it never goes live as an
 *      orphan. A transient surfaces an admin notice on the next
 *      screen so the editor knows what to fix.
 *
 *   3. **Edit-screen notice** — on the post-edit screen for any
 *      non-category album, we render a persistent notice telling
 *      the editor exactly what they need to do.
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
---------------------------------------------------------
 * Parent dropdown — restrict the choices to the three
 * canonical categories.
 * ---------------------------------------------------------
 */

/**
 * Restrict the REST `parent` lookups for `cwc_album` to the three
 * canonical category posts.
 *
 * Hooked via `rest_cwc_album_query` so it only affects
 * REST queries (which Gutenberg's PostParentControl uses).
 * Does not affect front-end queries.
 *
 * @since 1.0.0
 *
 * @param array           $args    `WP_Query` args.
 * @param WP_REST_Request $request The REST request.
 * @return array Modified args.
 */
function cwc_album_restrict_parent_rest_query( $args, $request ) {
	/*
	 * The block editor sends `?per_page=…&_locale=user&parent_exclude=…`
	 * when populating the parent dropdown. We can't 100% disambiguate
	 * "list of potential parents" from "general listing", so we apply
	 * the restriction whenever a `parent_exclude` is present (a strong
	 * signal Gutenberg is asking for parent candidates) OR when the
	 * caller explicitly asks for `cwc_album_parents=1`.
	 */
	$is_parent_lookup = ! empty( $request['parent_exclude'] )
		|| ! empty( $request['cwc_album_parents'] );

	if ( ! $is_parent_lookup ) {
		return $args;
	}

	$parent_ids = cwc_album_canonical_parent_ids();
	if ( empty( $parent_ids ) ) {
		return $args;
	}

	$args['post__in']      = $parent_ids;
	$args['post_parent']   = 0;
	$args['post_status']   = 'publish';
	$args['orderby']       = 'menu_order title';
	$args['order']         = 'ASC';
	$args['no_found_rows'] = true;

	return $args;
}
add_filter( 'rest_cwc_album_query', 'cwc_album_restrict_parent_rest_query', 10, 2 );

/**
 * Restrict the classic-editor parent dropdown (`wp_dropdown_pages`)
 * for `cwc_album` to the three canonical categories.
 *
 * Acts as a safety net for any environment where the classic editor
 * is forced on for the post type (e.g. Classic Editor plugin).
 *
 * @since 1.0.0
 *
 * @param array $args  Args passed to `wp_dropdown_pages()`.
 * @return array
 */
function cwc_album_restrict_parent_dropdown_args( $args ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'cwc_album' !== ( $screen->post_type ?? '' ) ) {
		return $args;
	}

	$parent_ids = cwc_album_canonical_parent_ids();
	if ( ! empty( $parent_ids ) ) {
		$args['include']     = implode( ',', $parent_ids );
		$args['post_status'] = 'publish';
	}

	return $args;
}
add_filter( 'page_attributes_dropdown_pages_args', 'cwc_album_restrict_parent_dropdown_args' );

/**
 * Resolve the canonical parent slugs to their current post IDs.
 *
 * Cached per request via a static so repeated calls (e.g. from the
 * REST filter on every keystroke) don't re-query.
 *
 * @since 1.0.0
 *
 * @return int[] Post IDs for events / lifestyle / explore-camsur (in catalogue order).
 */
function cwc_album_canonical_parent_ids() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$ids = array();
	foreach ( cwc_album_canonical_parent_slugs() as $slug ) {
		$matches = get_posts(
			array(
				'name'           => $slug,
				'post_type'      => 'cwc_album',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		if ( ! empty( $matches ) ) {
			$ids[] = (int) $matches[0];
		}
	}

	$cache = $ids;
	return $cache;
}

/*
---------------------------------------------------------
 * Save guard — block orphan top-level albums.
 * ---------------------------------------------------------
 */

/**
 * Demote orphan top-level albums to draft on save.
 *
 * The only top-level albums we accept are the three canonical
 * categories (matched by slug). Any other album with
 * `post_parent === 0` is a configuration mistake; we silently
 * downgrade `post_status` to `draft` and stash a transient notice
 * keyed by user so the editor sees actionable feedback on the next
 * admin page load.
 *
 * Hooked at `wp_insert_post_data` (priority `20`) so it runs
 * after WordPress's own slug normalisation but before the row
 * is written.
 *
 * @since 1.0.0
 *
 * @param array $data    Filtered post data ready for the DB.
 * @param array $postarr Raw post data submitted by the caller.
 * @return array Possibly-mutated `$data`.
 */
function cwc_album_block_orphan_publishes( $data, $postarr ) {
	if ( 'cwc_album' !== ( $data['post_type'] ?? '' ) ) {
		return $data;
	}

	// Only act on real publishes; drafts/auto-drafts/inherited are fine.
	if ( ! in_array( $data['post_status'] ?? '', array( 'publish', 'future' ), true ) ) {
		return $data;
	}

	$post_parent = (int) ( $data['post_parent'] ?? 0 );
	if ( $post_parent > 0 ) {
		return $data;
	}

	$canonical_slugs = cwc_album_canonical_parent_slugs();

	/*
	 * Three signals to recognise a canonical category, in priority order:
	 *
	 *   1. The slug WordPress is about to write (`$data['post_name']`)
	 *      — clean case, no collision.
	 *   2. The slug the caller asked for (`$postarr['post_name']`)
	 *      — needed because `wp_unique_post_slug()` runs BEFORE this
	 *      filter and may have bumped `events` to `events-2` due to
	 *      a collision with the legacy gallery child page.
	 *   3. `sanitize_title( $post_title )` — the seeder always sets
	 *      "Events", "Lifestyle", or "Explore CamSur" as the title;
	 *      slugifying that gives back the canonical slug. This is
	 *      the resilient path that also catches editor-created
	 *      categories typed by hand.
	 */
	$current_slug   = (string) ( $data['post_name'] ?? '' );
	$submitted_slug = (string) ( $postarr['post_name'] ?? '' );
	$post_title     = (string) ( $data['post_title'] ?? ( $postarr['post_title'] ?? '' ) );
	$title_slug     = '' !== $post_title ? sanitize_title( $post_title ) : '';

	if (
		( '' !== $current_slug && in_array( $current_slug, $canonical_slugs, true ) )
		|| ( '' !== $submitted_slug && in_array( $submitted_slug, $canonical_slugs, true ) )
		|| ( '' !== $title_slug && in_array( $title_slug, $canonical_slugs, true ) )
	) {
		return $data;
	}

	// Demote to draft and remember why so the next admin page can show a notice.
	$data['post_status'] = 'draft';

	$user_id = get_current_user_id();
	if ( $user_id > 0 ) {
		set_transient(
			'cwc_album_orphan_notice_' . $user_id,
			array(
				'title' => $data['post_title'] ?? __( 'Untitled album', 'child-cwcwake' ),
			),
			MINUTE_IN_SECONDS * 5
		);
	}

	return $data;
}
add_filter( 'wp_insert_post_data', 'cwc_album_block_orphan_publishes', 20, 2 );

/*
---------------------------------------------------------
 * Admin notices — editor guidance.
 * ---------------------------------------------------------
 */

/**
 * Render the "demoted to draft" notice queued by the save guard.
 *
 * Reads the per-user transient set by
 * {@see cwc_album_block_orphan_publishes()}, renders a one-shot
 * `notice-warning` admin notice, then clears the transient.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_album_render_orphan_notice() {
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return;
	}

	$payload = get_transient( 'cwc_album_orphan_notice_' . $user_id );
	if ( ! $payload || ! is_array( $payload ) ) {
		return;
	}

	delete_transient( 'cwc_album_orphan_notice_' . $user_id );

	$title = isset( $payload['title'] ) ? (string) $payload['title'] : '';
	?>
	<div class="notice notice-warning">
		<p>
			<strong><?php esc_html_e( 'Album saved as draft.', 'child-cwcwake' ); ?></strong>
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: Album title. */
					__( '"%s" was saved as a draft because it has no parent. Every album must live under one of the three categories: Events, Lifestyle, or Explore CamSur. Open the album, set Parent in the sidebar, then publish.', 'child-cwcwake' ),
					$title
				)
			);
			?>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'cwc_album_render_orphan_notice' );

/**
 * Persistent guidance on the album edit screen.
 *
 * Shows on every album editor screen (except for the three
 * canonical categories themselves) as a `notice-info` reminding
 * the editor to set a Parent before publishing.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_album_render_edit_screen_hint() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'cwc_album' !== ( $screen->post_type ?? '' ) || 'post' !== ( $screen->base ?? '' ) ) {
		return;
	}

	$post = get_post();
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	// Don't nag editors who are editing the canonical category posts.
	if ( in_array( $post->post_name, cwc_album_canonical_parent_slugs(), true ) ) {
		return;
	}

	?>
	<div class="notice notice-info">
		<p>
			<strong><?php esc_html_e( 'Pick a parent before publishing.', 'child-cwcwake' ); ?></strong>
			<?php esc_html_e( 'Every album must be a child of Events, Lifestyle, or Explore CamSur. Use the Parent control in the sidebar — only those three are selectable.', 'child-cwcwake' ); ?>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'cwc_album_render_edit_screen_hint' );
