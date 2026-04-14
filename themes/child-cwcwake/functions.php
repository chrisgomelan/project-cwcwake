<?php
/**
 * CWC Wake Child Theme — functions.php
 *
 * @package CWC_Wake
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CWC_VERSION', wp_get_theme()->get( 'Version' ) );

/**
 * Enqueue parent and child theme stylesheets.
 */
function cwc_enqueue_styles() {
	wp_enqueue_style(
		'cwc-google-fonts',
		'https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Archivo:wght@400;500;600;700&display=swap',
		[],
		null
	);

	wp_enqueue_style(
		'twentytwentyfive-style',
		get_template_directory_uri() . '/style.css',
		[ 'cwc-google-fonts' ],
		wp_get_theme( 'twentytwentyfive' )->get( 'Version' )
	);

	wp_enqueue_style(
		'cwc-global',
		get_stylesheet_directory_uri() . '/assets/css/global.css',
		[ 'twentytwentyfive-style' ],
		CWC_VERSION
	);

	wp_enqueue_style(
		'cwc-style',
		get_stylesheet_uri(),
		[ 'cwc-global' ],
		CWC_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'cwc_enqueue_styles' );

/**
 * Load Google Fonts in the block editor to match frontend.
 */
function cwc_enqueue_editor_styles() {
	add_editor_style( 'https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Archivo:wght@400;500;600;700&display=swap' );
	add_editor_style( 'assets/css/global.css' );
}
add_action( 'after_setup_theme', 'cwc_enqueue_editor_styles' );

/**
 * Theme setup: register supports, menus, image sizes.
 */
function cwc_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	] );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );

	register_nav_menus( [
		'primary'   => __( 'Primary Navigation', 'child-cwcwake' ),
		'footer'    => __( 'Footer Navigation', 'child-cwcwake' ),
	] );
}
add_action( 'after_setup_theme', 'cwc_theme_setup' );

/**
 * Create initial page structure on theme activation.
 *
 * Builds the full site hierarchy: top-level pages and their children.
 * Only runs once — guarded by the 'cwc_pages_created' option.
 */
function cwc_create_initial_pages() {
	if ( get_option( 'cwc_pages_created' ) ) {
		return;
	}

	$pages = [
		'Home'           => [ 'order' => 1, 'template' => '' ],
		'Activities'     => [
			'order'    => 2,
			'template' => 'page-activities',
			'children' => [ 'Water Sports', 'Land Activities', 'Elite Facilities' ],
		],
		'Accommodations' => [
			'order'    => 3,
			'template' => 'page-accommodations',
			'children' => [ 'Villas', 'Cabanas', 'Dwell', 'Cabin' ],
		],
		'Plan Your Trip' => [
			'order'    => 4,
			'template' => 'page-plan-your-trip',
			'children' => [ 'Rates', 'FAQs', 'Blogs', 'Gallery' ],
		],
		'About'          => [ 'order' => 5, 'template' => 'page-about' ],
	];

	foreach ( $pages as $title => $config ) {
		$post_data = [
			'post_title'  => $title,
			'post_status' => 'publish',
			'post_type'   => 'page',
			'menu_order'  => $config['order'],
		];

		if ( ! empty( $config['template'] ) ) {
			$post_data['page_template'] = $config['template'];
		}

		$parent_id = wp_insert_post( $post_data );

		if ( is_wp_error( $parent_id ) ) {
			continue;
		}

		if ( ! empty( $config['template'] ) ) {
			update_post_meta( $parent_id, '_wp_page_template', $config['template'] );
		}

		if ( ! empty( $config['children'] ) ) {
			$child_order = 1;
			foreach ( $config['children'] as $child_title ) {
				$child_id = wp_insert_post( [
					'post_title'  => $child_title,
					'post_status' => 'publish',
					'post_type'   => 'page',
					'post_parent' => $parent_id,
					'menu_order'  => $child_order++,
				] );

				if ( ! is_wp_error( $child_id ) ) {
					update_post_meta( $child_id, '_wp_page_template', 'page-child' );
				}
			}
		}
	}

	$home = get_page_by_title( 'Home' );
	if ( $home ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home->ID );
	}

	update_option( 'cwc_pages_created', true );
}
add_action( 'after_switch_theme', 'cwc_create_initial_pages' );

/**
 * Build the primary navigation menu matching the site structure.
 *
 * Creates the menu and assigns it to the 'primary' location.
 * Only runs once — guarded by the 'cwc_menu_created' option.
 */
function cwc_create_primary_menu() {
	if ( get_option( 'cwc_menu_created' ) ) {
		return;
	}

	$menu_name = 'Primary Navigation';
	$menu_id   = wp_create_nav_menu( $menu_name );

	if ( is_wp_error( $menu_id ) ) {
		return;
	}

	$menu_order = 1;
	$pages      = get_pages( [ 'sort_column' => 'menu_order', 'hierarchical' => false ] );
	$page_map   = [];

	foreach ( $pages as $page ) {
		$page_map[ $page->post_title ] = $page->ID;
	}

	$structure = [
		'Home'           => [],
		'Activities'     => [ 'Water Sports', 'Land Activities', 'Elite Facilities' ],
		'Accommodations' => [ 'Villas', 'Cabanas', 'Dwell', 'Cabin' ],
		'Plan Your Trip' => [ 'Rates', 'FAQs', 'Blogs', 'Gallery' ],
		'About'          => [],
	];

	foreach ( $structure as $parent_title => $children ) {
		if ( ! isset( $page_map[ $parent_title ] ) ) {
			continue;
		}

		$parent_menu_item_id = wp_update_nav_menu_item( $menu_id, 0, [
			'menu-item-title'     => $parent_title,
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $page_map[ $parent_title ],
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-position'  => $menu_order++,
		] );

		foreach ( $children as $child_title ) {
			if ( ! isset( $page_map[ $child_title ] ) ) {
				continue;
			}

			wp_update_nav_menu_item( $menu_id, 0, [
				'menu-item-title'     => $child_title,
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_map[ $child_title ],
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent_menu_item_id,
				'menu-item-position'  => $menu_order++,
			] );
		}
	}

	$locations              = get_theme_mod( 'nav_menu_locations', [] );
	$locations['primary']   = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	update_option( 'cwc_menu_created', true );
}
add_action( 'after_switch_theme', 'cwc_create_primary_menu', 20 );
