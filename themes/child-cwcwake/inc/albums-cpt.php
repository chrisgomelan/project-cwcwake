<?php
/**
 * CWC Wake — Albums Custom Post Type.
 *
 * Albums are hierarchical posts that power the Gallery section of the
 * site. Each album:
 *
 *   - Lives at `/gallery/<slug>/`.
 *   - Can have child albums (e.g. `Events > Halloween 2026`).
 *   - Uses its featured image as the cover thumbnail and the page
 *     banner background on the single-album view.
 *   - Stores its photos as native image attachments (uploaded
 *     through the editor via a `core/gallery` block, which parents
 *     the attachments to the post automatically).
 *
 * This file owns the CPT registration, query/breadcrumb integration,
 * and the small set of helpers the album block + single template
 * lean on (cover URL, photo count, child count, "back to" link).
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------------------------------------------------
 * CPT registration
 * --------------------------------------------------------- */

/**
 * Register the `cwc_album` post type and flush rewrites once.
 *
 * The CPT is hierarchical so editors can nest albums (e.g. an
 * "Events" parent album with per-event child albums). The slug is
 * `gallery` so URLs match the design (`/gallery/events/halloween/`).
 *
 * Rewrites are flushed once via the `cwc_albums_rewrites_flushed`
 * option so freshly cloned environments serve album URLs without
 * the editor having to manually re-save permalinks.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_register_album_cpt() {
	$labels = [
		'name'                  => _x( 'Albums', 'Post type general name', 'child-cwcwake' ),
		'singular_name'         => _x( 'Album', 'Post type singular name', 'child-cwcwake' ),
		'menu_name'             => _x( 'Albums', 'Admin Menu text', 'child-cwcwake' ),
		'name_admin_bar'        => _x( 'Album', 'Add New on Toolbar', 'child-cwcwake' ),
		'add_new'               => __( 'Add New', 'child-cwcwake' ),
		'add_new_item'          => __( 'Add New Album', 'child-cwcwake' ),
		'new_item'              => __( 'New Album', 'child-cwcwake' ),
		'edit_item'             => __( 'Edit Album', 'child-cwcwake' ),
		'view_item'             => __( 'View Album', 'child-cwcwake' ),
		'all_items'             => __( 'All Albums', 'child-cwcwake' ),
		'search_items'          => __( 'Search Albums', 'child-cwcwake' ),
		'parent_item_colon'     => __( 'Parent Album:', 'child-cwcwake' ),
		'not_found'             => __( 'No albums found.', 'child-cwcwake' ),
		'not_found_in_trash'    => __( 'No albums found in Trash.', 'child-cwcwake' ),
		'featured_image'        => _x( 'Album Cover Image', 'Featured image label', 'child-cwcwake' ),
		'set_featured_image'    => _x( 'Set cover image', 'Set featured image label', 'child-cwcwake' ),
		'remove_featured_image' => _x( 'Remove cover image', 'Remove featured image label', 'child-cwcwake' ),
		'use_featured_image'    => _x( 'Use as cover image', 'Use as featured image label', 'child-cwcwake' ),
		'archives'              => _x( 'Album archives', 'Archive label', 'child-cwcwake' ),
	];

	$args = [
		'labels'              => $labels,
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-format-gallery',
		'capability_type'     => 'post',
		'hierarchical'        => true,
		'has_archive'         => false,
		'rewrite'             => [
			'slug'       => 'gallery',
			'with_front' => false,
		],
		'supports'            => [
			'title',
			'editor',
			'thumbnail',
			'excerpt',
			'page-attributes',
			'revisions',
			'custom-fields',
		],
		'template'            => [
			[ 'core/gallery', [ 'columns' => 3, 'imageCrop' => true, 'linkTo' => 'media' ] ],
		],
	];

	register_post_type( 'cwc_album', $args );

	if ( ! get_option( 'cwc_albums_rewrites_flushed' ) ) {
		flush_rewrite_rules();
		update_option( 'cwc_albums_rewrites_flushed', true );
	}
}
add_action( 'init', 'cwc_register_album_cpt' );

/**
 * Re-flush rewrites if the CPT registration ever changes.
 *
 * Bump `CWC_VERSION` (or the option key) when you tweak the rewrite
 * rules so existing installs pick up the new permalink structure.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_album_maybe_refresh_rewrites() {
	if ( get_option( 'cwc_albums_rewrites_flushed_v' ) === CWC_VERSION ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'cwc_albums_rewrites_flushed_v', CWC_VERSION );
}
add_action( 'wp_loaded', 'cwc_album_maybe_refresh_rewrites' );

/* ---------------------------------------------------------
 * Helpers
 * --------------------------------------------------------- */

/**
 * Return all image attachments parented to an album.
 *
 * Editors uploading images through the album's `core/gallery` block
 * end up with attachments whose `post_parent` is the album ID, so
 * this is enough to build the photo count + photo grid.
 *
 * @since 1.0.0
 *
 * @param int $post_id Album post ID.
 * @return WP_Post[] List of attachment posts (image MIME types only).
 */
function cwc_album_get_photos( $post_id ) {
	if ( ! $post_id ) {
		return [];
	}

	$attachments = get_posts(
		[
			'post_parent'    => (int) $post_id,
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'post_status'    => 'inherit',
			'numberposts'    => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		]
	);

	return $attachments instanceof WP_Error ? [] : $attachments;
}

/**
 * Return the number of image attachments parented to an album.
 *
 * @since 1.0.0
 *
 * @param int $post_id Album post ID.
 * @return int Photo count.
 */
function cwc_album_photo_count( $post_id ) {
	return count( cwc_album_get_photos( $post_id ) );
}

/**
 * Return the published direct child albums of a given parent.
 *
 * Pass `0` to fetch top-level albums (used by the Gallery landing
 * page). Results are ordered by `menu_order` then title so editors
 * can hand-curate ordering through the page-attributes panel.
 *
 * @since 1.0.0
 *
 * @param int $parent_id Parent album ID, or 0 for top-level.
 * @return WP_Post[] List of child album posts.
 */
function cwc_album_get_children( $parent_id = 0 ) {
	$children = get_posts(
		[
			'post_type'   => 'cwc_album',
			'post_parent' => (int) $parent_id,
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby'     => [ 'menu_order' => 'ASC', 'title' => 'ASC' ],
		]
	);

	return $children instanceof WP_Error ? [] : $children;
}

/**
 * Number of direct child albums under a parent.
 *
 * @since 1.0.0
 *
 * @param int $parent_id Parent album ID.
 * @return int Child album count.
 */
function cwc_album_child_count( $parent_id ) {
	return count( cwc_album_get_children( $parent_id ) );
}

/**
 * Resolve the canonical Gallery landing page.
 *
 * The site's Gallery page lives under `Plan Your Trip` (path
 * `plan-your-trip/gallery`), but the CPT permalink slug is just
 * `gallery`. We probe both paths so the link works regardless of
 * how the page tree is rearranged.
 *
 * @since 1.0.0
 *
 * @return string Permalink to the Gallery page (or `/gallery/` fallback).
 */
function cwc_album_gallery_url() {
	$candidates = [ 'plan-your-trip/gallery', 'gallery' ];

	foreach ( $candidates as $path ) {
		$page = get_page_by_path( $path );
		if ( $page instanceof WP_Post ) {
			return get_permalink( $page );
		}
	}

	return home_url( '/gallery/' );
}

/**
 * Build the "Back to …" link target + label for a single album.
 *
 * If the current album has a parent, link to the parent album.
 * Otherwise link to the Gallery landing page.
 *
 * @since 1.0.0
 *
 * @param int $post_id Album post ID.
 * @return array{label:string, url:string} Back-link descriptor.
 */
function cwc_album_back_link( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return [
			'label' => __( 'Back to Gallery', 'child-cwcwake' ),
			'url'   => cwc_album_gallery_url(),
		];
	}

	if ( (int) $post->post_parent > 0 ) {
		$parent = get_post( (int) $post->post_parent );
		if ( $parent instanceof WP_Post ) {
			return [
				/* translators: %s: Parent album title. */
				'label' => sprintf( __( 'Back to %s', 'child-cwcwake' ), get_the_title( $parent ) ),
				'url'   => get_permalink( $parent ),
			];
		}
	}

	return [
		'label' => __( 'Back to Gallery', 'child-cwcwake' ),
		'url'   => cwc_album_gallery_url(),
	];
}

/* ---------------------------------------------------------
 * Breadcrumb integration
 * --------------------------------------------------------- */

/**
 * Inject "Gallery" + ancestor albums into the breadcrumb trail on
 * a single album page.
 *
 * Default `cwc_build_breadcrumbs()` would render `Home > Halloween`
 * for an album, missing the Gallery context. We rebuild the trail
 * for `cwc_album` requests so it reads
 * `Home > Gallery > Events > Halloween` instead.
 *
 * @since 1.0.0
 *
 * @param array $crumbs Existing crumb list.
 * @return array Modified list.
 */
function cwc_album_breadcrumbs( $crumbs ) {
	if ( ! is_singular( 'cwc_album' ) ) {
		return $crumbs;
	}

	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return $crumbs;
	}

	/*
	 * Rebuild from scratch so we own ordering + formatting:
	 * Home → Gallery → (ancestors…) → Current.
	 */
	$rebuilt = [
		[ 'label' => __( 'Home', 'child-cwcwake' ), 'url' => home_url( '/' ) ],
		[ 'label' => __( 'Gallery', 'child-cwcwake' ), 'url' => cwc_album_gallery_url() ],
	];

	$ancestors = array_reverse( get_post_ancestors( $post ) );
	foreach ( $ancestors as $ancestor_id ) {
		$rebuilt[] = [
			'label' => get_the_title( $ancestor_id ),
			'url'   => get_permalink( $ancestor_id ),
		];
	}

	$rebuilt[] = [ 'label' => get_the_title( $post ), 'url' => null ];

	return $rebuilt;
}
add_filter( 'cwc_breadcrumbs_items', 'cwc_album_breadcrumbs' );
