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

// Remove the admin bar for a cleaner frontend experience.
add_filter( 'show_admin_bar', '__return_false' );

/**
 * Enqueue parent and child theme stylesheets.
 */
function cwc_enqueue_styles() {
	wp_enqueue_style(
		'cwc-google-fonts',
		'https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap',
		array(),
		CWC_VERSION
	);

	wp_enqueue_style(
		'cwc-google-fonts-archivo',
		'https://fonts.googleapis.com/css2?family=Archivo:ital,wght@0,100..900;1,100..900&display=swap',
		array(),
		CWC_VERSION
	);

	wp_enqueue_style(
		'twentytwentyfive-style',
		get_template_directory_uri() . '/style.css',
		array( 'cwc-google-fonts', 'cwc-google-fonts-archivo' ),
		wp_get_theme( 'twentytwentyfive' )->get( 'Version' )
	);

	wp_enqueue_style(
		'cwc-global',
		get_stylesheet_directory_uri() . '/assets/css/global.css',
		array( 'twentytwentyfive-style' ),
		CWC_VERSION
	);

	wp_enqueue_style(
		'cwc-header',
		get_stylesheet_directory_uri() . '/assets/css/header.css',
		array( 'cwc-global' ),
		CWC_VERSION
	);

	wp_enqueue_style(
		'cwc-footer',
		get_stylesheet_directory_uri() . '/assets/css/footer.css',
		array( 'cwc-global' ),
		CWC_VERSION
	);

	wp_enqueue_style(
		'cwc-style',
		get_stylesheet_uri(),
		array( 'cwc-header' ),
		CWC_VERSION
	);

	wp_enqueue_script(
		'cwc-header',
		get_stylesheet_directory_uri() . '/assets/js/header.js',
		array(),
		CWC_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	// Custom Image Modal — Lightweight native popup slider.
	wp_enqueue_script(
		'cwc-image-modal',
		get_stylesheet_directory_uri() . '/assets/js/image-modal.js',
		array(),
		CWC_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	// Scroll to Top — Premium navigation helper.
	wp_enqueue_script(
		'cwc-scroll-top',
		get_stylesheet_directory_uri() . '/assets/js/scroll-to-top.js',
		array(),
		CWC_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	// Global Toast System.
	wp_enqueue_script(
		'cwc-toast',
		get_stylesheet_directory_uri() . '/assets/js/toast.js',
		array(),
		CWC_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
	wp_enqueue_script(
		'cwc-header-search',
		get_stylesheet_directory_uri() . '/assets/js/header-search.js',
		array( 'jquery' ),
		CWC_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_enqueue_style(
		'cwc-header-search',
		get_stylesheet_directory_uri() . '/assets/css/header-search.css',
		array( 'cwc-header' ),
		CWC_VERSION
	);

	wp_localize_script(
		'cwc-header',
		'cwcVars',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'cwc_enqueue_styles' );

/**
 * Add preconnect resource hints for Google Fonts to speed up font download.
 *
 * @param array  $urls          URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed for.
 * @return array Modified resource hints.
 */
function cwc_preconnect_google_fonts( $urls, $relation_type ) {
	if ( wp_style_is( 'cwc-google-fonts', 'queue' ) && 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'cwc_preconnect_google_fonts', 10, 2 );

/**
 * Preload LCP (Largest Contentful Paint) resources for the main pages.
 * By explicitly telling the browser to download these hero images immediately,
 * we dramatically speed up the LCP metric across the site.
 */
function cwc_preload_lcp_resources() {
	$preload_image = '';

	if ( is_front_page() ) {
		$preload_image = '/wp-content/uploads/2026/05/hero-home.webp';
	} elseif ( is_page( 'water-sports' ) ) {
		$preload_image = '/wp-content/uploads/2026/04/watersports-page-bg-banner-e1776914994956.webp';
	} elseif ( is_page( 'accommodations' ) ) {
		$preload_image = '/wp-content/uploads/2026/04/accomodations-banner-bg.webp';
	} elseif ( is_page( 'gallery' ) || is_singular( 'cwc_album' ) || is_tax( 'cwc_album_category' ) ) {
		$preload_image = '/wp-content/uploads/2026/04/gallery-banner-bg.webp';
	} elseif ( is_page( 'contact-us' ) || is_page( 'contact' ) ) {
		$preload_image = '/wp-content/uploads/2026/04/contact-banner-bg.webp';
	} elseif ( is_page( 'blogs' ) ) {
		$preload_image = '/wp-content/uploads/2026/04/blogs-banner-bg-e1776821235201.webp';
	} elseif ( is_page( 'rates' ) ) {
		$preload_image = '/wp-content/uploads/2026/04/rates-banner-bg.webp';
	}

	if ( ! empty( $preload_image ) ) {
		echo '<link rel="preload" as="image" href="' . esc_url( $preload_image ) . '">';
	}
}
add_action( 'wp_head', 'cwc_preload_lcp_resources', 1 );




/**
 * Enqueue styles and scripts for single blog posts.
 *
 * Loads:
 * - single-post.css — post title, hero, content-box, two-column layout.
 * - single-post.js  — populates the hero section from localised data and
 *                      drives the Table of Contents scrollspy / dot indicator.
 *
 * Also passes a `cwcSinglePost` object to JS with the featured image URL,
 * human-readable publish date, estimated read time, and the theme URI for
 * resolving icon paths.
 */
function cwc_enqueue_single_post_assets() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	wp_enqueue_style(
		'cwc-single-post',
		get_stylesheet_directory_uri() . '/assets/css/single-post.css',
		array( 'cwc-global' ),
		CWC_VERSION
	);

	wp_enqueue_script(
		'cwc-single-post',
		get_stylesheet_directory_uri() . '/assets/js/single-post.js',
		array(),
		CWC_VERSION,
		true
	);

	$post_id   = get_the_ID();
	$thumb_url = '';
	$thumb_id  = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb_id > 0 ) {
		$src = wp_get_attachment_image_url( $thumb_id, 'full' );
		if ( is_string( $src ) && '' !== $src ) {
			$thumb_url = $src;
		}
	}

	$word_count = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) );
	$read_min   = max( 1, (int) ceil( $word_count / 200 ) );

	wp_localize_script(
		'cwc-single-post',
		'cwcSinglePost',
		array(
			'image'    => esc_url( $thumb_url ),
			'date'     => get_the_date( 'F j, Y', $post_id ),
			'readTime' => $read_min . ' minute' . ( $read_min > 1 ? 's' : '' ) . ' read',
			'themeUri' => get_stylesheet_directory_uri(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'cwc_enqueue_single_post_assets' );

/**
 * Automatically add IDs to H2 and H3 headings on single posts.
 *
 * The Table of Contents block generates anchors from heading text via
 * `sanitize_title()`. This filter applies the same transform to the
 * actual `<h2>` / `<h3>` tags so the scrollspy JS can match them up.
 * Existing IDs are left untouched.
 *
 * @param string $content Post content.
 * @return string Modified content.
 */
function cwc_add_heading_ids( $content ) {
	if ( ! is_singular( 'post' ) ) {
		return $content;
	}

	return preg_replace_callback(
		'/<(h[23])(.*?)>(.*?)<\/h\1>/i',
		function ( $matches ) {
			$tag   = $matches[1];
			$attrs = $matches[2];
			$text  = $matches[3];

			if ( strpos( $attrs, 'id=' ) !== false ) {
				return $matches[0];
			}

			$id = sanitize_title( wp_strip_all_tags( $text ) );
			return "<$tag $attrs id=\"$id\">$text</$tag>";
		},
		$content
	);
}
add_filter( 'the_content', 'cwc_add_heading_ids' );

/**
 * Inject the Scroll to Top button markup into the footer.
 */
function cwc_inject_scroll_top_html() {
	?>
	<button id="cwc-scroll-top" class="cwc-scroll-top" aria-label="Scroll to top">
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
			stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
			<path d="M12 19V5M5 12l7-7 7 7" />
		</svg>
	</button>
	<?php
}
add_action( 'wp_footer', 'cwc_inject_scroll_top_html' );

/**
 * Load Google Fonts in the block editor to match frontend.
 */
function cwc_enqueue_editor_styles() {
	add_editor_style( 'https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap' );
	add_editor_style( 'https://fonts.googleapis.com/css2?family=Archivo:ital,wght@0,100..900;1,100..900&display=swap' );
	add_editor_style( 'assets/css/global.css' );
}
add_action( 'after_setup_theme', 'cwc_enqueue_editor_styles' );

/**
 * Theme setup: register supports, menus, image sizes.
 */
function cwc_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Navigation', 'child-cwcwake' ),
			'footer'  => __( 'Footer Navigation', 'child-cwcwake' ),
		)
	);
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

	$pages = array(
		'Home'               => array(
			'order'    => 1,
			'template' => '',
		),
		'Activities'         => array(
			'order'    => 2,
			'template' => 'page-activities',
			'children' => array( 'Water Sports', 'Land Activities', 'Elite Facilities' ),
		),
		'Accommodations'     => array(
			'order'    => 3,
			'template' => 'page-accommodations',
			'children' => array(
				'Villas'  => 'room-detail',
				'Cabanas' => 'room-detail',
				'Dwell'   => 'room-detail',
				'Cabin'   => 'room-detail',
			),
		),
		'Plan Your Trip'     => array(
			'order'    => 4,
			'template' => 'page-plan-your-trip',
			'children' => array(
				'Rates'   => 'page-child',
				'FAQs'    => 'page-child',
				'Blogs'   => 'page-child',
				'Gallery' => array(
					'template' => 'page-gallery',
					'children' => array(
						'Events'         => 'page-child',
						'Lifestyle'      => 'page-child',
						'Explore CamSur' => 'page-child',
					),
				),
			),
		),
		'About'              => array(
			'order'    => 5,
			'template' => 'page-about',
		),
		'Contact Us'         => array(
			'order'    => 6,
			'template' => 'page-contact',
		),
		'Terms & Conditions' => array(
			'order'    => 7,
			'template' => 'page-terms-and-conditions',
		),
		'Privacy Policy'     => array(
			'order'    => 8,
			'template' => 'page-privacy-policy',
		),
	);

	foreach ( $pages as $title => $config ) {
		$post_data = array(
			'post_title'  => $title,
			'post_status' => 'publish',
			'post_type'   => 'page',
			'menu_order'  => $config['order'],
		);

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
			cwc_seed_child_pages( $config['children'], $parent_id );
		}
	}

	$home_query = new WP_Query(
		array(
			'title'                  => 'Home',
			'post_type'              => 'page',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		)
	);
	$home       = $home_query->have_posts() ? $home_query->posts[0] : null;

	if ( $home ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home->ID );
	}

	update_option( 'cwc_pages_created', true );
}
add_action( 'after_switch_theme', 'cwc_create_initial_pages' );

/**
 * Recursively create child pages under a given parent.
 *
 * Each entry in $children may be expressed in one of three forms:
 *
 *   1. `'Title'`                              — numeric key, defaults to `page-child` template, no grandchildren.
 *   2. `'Title' => 'template-slug'`           — assoc form, custom template, no grandchildren.
 *   3. `'Title' => ['template' => 'tpl',
 *                   'children' => [ ... ]]`  — assoc form with grandchildren (recursed).
 *
 * @param array $children  Children definitions.
 * @param int   $parent_id Parent post ID to attach the new pages to.
 */
function cwc_seed_child_pages( array $children, $parent_id ) {
	$order = 1;

	foreach ( $children as $key => $value ) {
		if ( is_int( $key ) ) {
			$title         = $value;
			$template      = 'page-child';
			$grandchildren = array();
		} elseif ( is_array( $value ) ) {
			$title         = $key;
			$template      = $value['template'] ?? 'page-child';
			$grandchildren = $value['children'] ?? array();
		} else {
			$title         = $key;
			$template      = $value;
			$grandchildren = array();
		}

		$child_id = wp_insert_post(
			array(
				'post_title'  => $title,
				'post_status' => 'publish',
				'post_type'   => 'page',
				'post_parent' => $parent_id,
				'menu_order'  => $order++,
			)
		);

		if ( is_wp_error( $child_id ) ) {
			continue;
		}

		if ( ! empty( $template ) ) {
			update_post_meta( $child_id, '_wp_page_template', $template );
		}

		if ( ! empty( $grandchildren ) ) {
			cwc_seed_child_pages( $grandchildren, $child_id );
		}
	}
}

/*
 * Room detail pages — per-room catalogue, block-markup renderer,
 * and one-shot content seeder. Lives in `inc/` to keep functions.php
 * focused on theme bootstrapping.
 */
require_once get_stylesheet_directory() . '/inc/room-detail-pages.php';

/*
 * Contact form (`cwc/contact-form`) and footer newsletter form
 * server-side handlers. Both submit to `admin-post.php`, validate +
 * dispatch through `wp_mail()` (so WP Mail SMTP routes them), and
 * round-trip status via the Post/Redirect/Get pattern.
 */
require_once get_stylesheet_directory() . '/inc/contact-form-handler.php';
require_once get_stylesheet_directory() . '/inc/newsletter-subscribe.php';

/*
 * Floating site chat (xAI Grok via REST proxy). Excluded on booking-flow pages.
 */
require_once get_stylesheet_directory() . '/inc/chat-assistant.php';

/*
 * One-shot Contact page seeder — assigns the `page-contact` template
 * and populates the page with the two contact blocks if it is still
 * empty. Idempotent (option-guarded).
 */
require_once get_stylesheet_directory() . '/inc/contact-page-seed.php';

/*
 * One-shot Privacy Policy + Terms & Conditions seeder. Forces the
 * matching `page-privacy-policy` / `page-terms-and-conditions`
 * templates and seeds the shared `cwc/policy-content` block with the
 * clauses copied from the Figma mockups. Idempotent.
 */
// Policy pages content is now managed directly in templates.

/*
 * Albums (Gallery) custom post type. Registers a hierarchical
 * `cwc_album` CPT mounted at `/gallery/<slug>/` with helpers used
 * by the `cwc/albums-grid` and `cwc/album-back-link` blocks and
 * the `single-cwc_album` template.
 */
require_once get_stylesheet_directory() . '/inc/albums-cpt.php';

/*
 * Self-healing seeder for the three top-level Album categories
 * (Events / Lifestyle / Explore CamSur). Recreates / restores from
 * trash on every request (throttled to once per minute), and wires
 * each one's cover webp as the featured image so the single-album
 * banner + landing page card share the same hero asset.
 */
require_once get_stylesheet_directory() . '/inc/albums-seed.php';

/*
 * Editorial guard-rails for the cwc_album CPT: restricts the
 * Parent dropdown to the three canonical categories, and demotes
 * orphan top-level publishes to draft with an admin notice.
 */
require_once get_stylesheet_directory() . '/inc/albums-parent-enforcement.php';

/*
 * Blogs page support.
 *
 * Two pieces:
 *   - `blog-helpers.php` defines the shared utilities consumed by
 *     the three Blogs page blocks (image URL resolution, excerpt
 *     building, blog landing URL). Loaded outside of any block's
 *     render.php so the helpers exist exactly once per request.
 *   - `blogs-page-seed.php` forces `page-blogs` on the existing
 *     /plan-your-trip/blogs/ page so the template hierarchy renders
 *     the new sections without an editor having to manually
 *     re-pick the template. Idempotent (option-guarded).
 *
 * The actual blog *content* (sample posts + categories) is seeded
 * by the `cwc-accommodations` plugin
 * (`includes/blog-seeder.php`) so a theme swap doesn't take the
 * sample data with it.
 */
require_once get_stylesheet_directory() . '/inc/blog-helpers.php';
require_once get_stylesheet_directory() . '/inc/blogs-page-seed.php';

/*
 * One-shot FAQs page seeder — assigns the `page-faqs` template
 * to the existing `/plan-your-trip/faqs/` page so the template
 * hierarchy renders the FAQ accordion without editors having to
 * manually re-pick the template. Idempotent (option-guarded).
 */
require_once get_stylesheet_directory() . '/inc/faqs-page-seed.php';



/*
 * Water Sports page seeder — assigns the `page-water-sports` template
 * to the existing `/activities/water-sports/` page. Idempotent (option-guarded).
 */
require_once get_stylesheet_directory() . '/inc/water-sports-page-seed.php';

/*
 * Land Activities page seeder — assigns the `page-land-activities` template
 * to the existing `/activities/land-activities/` page. Idempotent (option-guarded).
 */
require_once get_stylesheet_directory() . '/inc/land-activities-page-seed.php';
require_once get_stylesheet_directory() . '/inc/elite-facilities-page-seed.php';

/*
 * Accommodations (Rooms) data layer.
 *
 * The `accommodation` CPT, its meta fields, the admin meta boxes,
 * the Global Policies settings page, and the legacy migration all
 * live in the `cwc-accommodations` plugin under
 * `wp-content/plugins/cwc-accommodations/`. The plugin must be
 * active for room pages to render — when it isn't, it surfaces an
 * admin notice telling editors what's missing.
 *
 * Theme-side responsibilities for rooms are limited to:
 *   - The visual blocks (`cwc/room-info`, `cwc/room-gallery`,
 *     `cwc/other-rooms`) under `themes/child-cwcwake/blocks/`.
 *   - The `single-accommodation.html` template.
 *   - The amenity / policy icon SVGs under
 *     `themes/child-cwcwake/assets/images/`, resolved by the
 *     plugin via `get_stylesheet_directory_uri()`.
 *
 * See `room-management-transition.md` for the full architecture.
 */

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
	$pages      = get_pages(
		array(
			'sort_column'  => 'menu_order',
			'hierarchical' => false,
		)
	);
	$page_map   = array();

	foreach ( $pages as $page ) {
		$page_map[ $page->post_title ] = $page->ID;
	}

	$structure = array(
		'Home'           => array(),
		'Activities'     => array( 'Water Sports', 'Land Activities', 'Elite Facilities' ),
		'Accommodations' => array( 'Villas', 'Cabanas', 'Dwell', 'Cabin' ),
		'Gallery'        => array( 'Events', 'Lifestyle', 'Explore CamSur' ),
		'Plan Your Trip' => array( 'Rates', 'FAQs', 'Blogs' ),
		'About'          => array(),
	);

	foreach ( $structure as $parent_title => $children ) {
		if ( ! isset( $page_map[ $parent_title ] ) ) {
			continue;
		}

		$parent_menu_item_id = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $parent_title,
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_map[ $parent_title ],
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $menu_order++,
			)
		);

		foreach ( $children as $child_title ) {
			if ( ! isset( $page_map[ $child_title ] ) ) {
				continue;
			}

			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => $child_title,
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $page_map[ $child_title ],
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-parent-id' => $parent_menu_item_id,
					'menu-item-position'  => $menu_order++,
				)
			);
		}
	}

	$locations            = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	update_option( 'cwc_menu_created', true );
}
add_action( 'after_switch_theme', 'cwc_create_primary_menu', 20 );

/**
 * Build the breadcrumb trail for the current request.
 *
 * Shared by the `cwc/breadcrumbs` block and by `cwc/page-banner`
 * when it renders breadcrumbs inside the banner area.
 *
 * Each crumb is `[ 'label' => string, 'url' => string|null ]`.
 * A null URL marks the current (non-link) item.
 *
 * @param string $home_label The label for the home link.
 */
function cwc_build_breadcrumbs( $home_label = 'Home' ) {
	$crumbs = array(
		array(
			'label' => $home_label,
			'url'   => home_url( '/' ),
		),
	);

	if ( is_front_page() ) {
		$crumbs[ count( $crumbs ) - 1 ]['url'] = null;
	} elseif ( is_page() ) {
		$ancestors = array_reverse( get_post_ancestors( get_the_ID() ) );
		foreach ( $ancestors as $ancestor_id ) {
			$crumbs[] = array(
				'label' => get_the_title( $ancestor_id ),
				'url'   => get_permalink( $ancestor_id ),
			);
		}
		$crumbs[] = array(
			'label' => get_the_title(),
			'url'   => null,
		);
	} elseif ( is_singular() ) {
		$crumbs[] = array(
			'label' => get_the_title(),
			'url'   => null,
		);
	} elseif ( is_category() || is_tag() || is_tax() || is_post_type_archive() || is_archive() ) {
		$crumbs[] = array(
			'label' => wp_strip_all_tags( get_the_archive_title() ),
			'url'   => null,
		);
	} elseif ( is_search() ) {
		$crumbs[] = array(
			/* translators: %s: Search query. */
			'label' => sprintf( __( 'Search: %s', 'child-cwcwake' ), get_search_query() ),
			'url'   => null,
		);
	} elseif ( is_404() ) {
		$crumbs[] = array(
			'label' => __( 'Not Found', 'child-cwcwake' ),
			'url'   => null,
		);
	}

	/** This filter is documented in blocks/breadcrumbs/render.php */
	return apply_filters( 'cwc_breadcrumbs_items', $crumbs );
}

/**
 * Render the breadcrumb trail HTML.
 *
 * @param array $args {
 *     Array of arguments.
 *
 *     @type string $home_label     Label for the first crumb. Default 'Home'.
 *     @type bool   $show_home_icon Whether to show the home icon. Default true.
 *     @type string $extra_class    Optional extra class appended to the <nav> element.
 * }
 * @return string Rendered <nav> markup, or empty string if there's nothing to render.
 */
function cwc_render_breadcrumbs( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'home_label'     => 'Home',
			'show_home_icon' => true,
			'extra_class'    => '',
		)
	);

	$crumbs = cwc_build_breadcrumbs( $args['home_label'] );
	if ( count( $crumbs ) < 2 ) {
		return '';
	}

	$class = trim( 'cwc-breadcrumbs ' . $args['extra_class'] );
	$last  = count( $crumbs ) - 1;

	ob_start();
	?>
	<nav class="<?php echo esc_attr( $class ); ?>" role="navigation"
		aria-label="<?php esc_attr_e( 'Breadcrumb', 'child-cwcwake' ); ?>">
		<ol class="cwc-breadcrumbs__list">
			<?php
			foreach ( $crumbs as $i => $crumb ) :
				$is_first = ( 0 === $i );
				$is_last  = ( $last === $i );
				?>
				<li class="cwc-breadcrumbs__item<?php echo $is_last ? ' cwc-breadcrumbs__item--current' : ''; ?>">
					<?php if ( ! empty( $crumb['url'] ) ) : ?>
						<a class="cwc-breadcrumbs__link" href="<?php echo esc_url( $crumb['url'] ); ?>">
							<?php if ( $is_first && $args['show_home_icon'] ) : ?>
								<svg class="cwc-breadcrumbs__home-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14"
									viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
									<path d="M12 3 2 12h3v8h6v-6h2v6h6v-8h3z" />
								</svg>
							<?php endif; ?>
							<span><?php echo esc_html( $crumb['label'] ); ?></span>
						</a>
					<?php else : ?>
						<span class="cwc-breadcrumbs__current" aria-current="page">
							<?php echo esc_html( $crumb['label'] ); ?>
						</span>
					<?php endif; ?>

					<?php if ( ! $is_last ) : ?>
						<span class="cwc-breadcrumbs__separator" aria-hidden="true">&rsaquo;</span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	</nav>
	<?php
	return ob_get_clean();
}

/**
 * Register custom blocks.
 */
function cwc_register_blocks() {
	register_block_type( get_stylesheet_directory() . '/blocks/hero-section' );
	register_block_type( get_stylesheet_directory() . '/blocks/intro-section' );
	register_block_type( get_stylesheet_directory() . '/blocks/showcase-section' );
	register_block_type( get_stylesheet_directory() . '/blocks/accommodations-section' );
	register_block_type( get_stylesheet_directory() . '/blocks/reviews-section' );
	register_block_type( get_stylesheet_directory() . '/blocks/page-banner' );
	register_block_type( get_stylesheet_directory() . '/blocks/breadcrumbs' );
	register_block_type( get_stylesheet_directory() . '/blocks/gallery-grid' );
	register_block_type( get_stylesheet_directory() . '/blocks/cards-section' );
	register_block_type( get_stylesheet_directory() . '/blocks/room-gallery' );
	register_block_type( get_stylesheet_directory() . '/blocks/room-info' );
	register_block_type( get_stylesheet_directory() . '/blocks/other-rooms' );
	register_block_type( get_stylesheet_directory() . '/blocks/contact-info' );
	register_block_type( get_stylesheet_directory() . '/blocks/contact-form' );
	register_block_type( get_stylesheet_directory() . '/blocks/policy-content' );
	register_block_type( get_stylesheet_directory() . '/blocks/albums-grid' );
	register_block_type( get_stylesheet_directory() . '/blocks/album-back-link' );
	register_block_type( get_stylesheet_directory() . '/blocks/why-stay' );
	register_block_type( get_stylesheet_directory() . '/blocks/featured-blogs' );
	register_block_type( get_stylesheet_directory() . '/blocks/upcoming-events' );
	register_block_type( get_stylesheet_directory() . '/blocks/all-blogs' );
	register_block_type( get_stylesheet_directory() . '/blocks/table-of-contents' );
	register_block_type( get_stylesheet_directory() . '/blocks/rates-manager' );
	register_block_type( get_stylesheet_directory() . '/blocks/faq-section' );
	register_block_type( get_stylesheet_directory() . '/blocks/about-timeline' );
	register_block_type( get_stylesheet_directory() . '/blocks/about-champions' );
	register_block_type( get_stylesheet_directory() . '/blocks/about-certified' );
	register_block_type( get_stylesheet_directory() . '/blocks/about-empowering' );
	register_block_type( get_stylesheet_directory() . '/blocks/before-footer-cta' );

	// Water Sports page blocks.
	register_block_type( get_stylesheet_directory() . '/blocks/feature-split' );
	register_block_type( get_stylesheet_directory() . '/blocks/feature-banner' );
	register_block_type( get_stylesheet_directory() . '/blocks/coaching-section' );

	// Land Activities page blocks.
	register_block_type( get_stylesheet_directory() . '/blocks/land-feature-split' );
	register_block_type( get_stylesheet_directory() . '/blocks/header-multi-image' );

	// Book a Room blocks.
	register_block_type( get_stylesheet_directory() . '/blocks/book-a-room' );
	register_block_type( get_stylesheet_directory() . '/blocks/booking-flow' );
}
add_action( 'init', 'cwc_register_blocks' );

/**
 * Manually enqueue room-info view script on single accommodation pages.
 *
 * The block viewScript declaration in block.json sometimes fails to
 * fire on server-rendered blocks. Enqueuing explicitly guarantees
 * the modal logic runs every time the room-info block is on screen.
 */
function cwc_enqueue_room_info_scripts() {
	if ( is_singular( 'accommodation' ) ) {
		wp_enqueue_script(
			'cwc-room-info-view',
			get_stylesheet_directory_uri() . '/blocks/room-info/view.js',
			array(),
			CWC_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'cwc_enqueue_room_info_scripts' );

/**
 * Add a body class to mark the front page.
 *
 * Used by the header CSS/JS so the transparent-on-top behavior only
 * applies on the home page; all other pages get an opaque header.
 *
 * @param array $classes Body classes.
 * @return array Modified classes.
 */
function cwc_body_class_front_page( $classes ) {
	if ( is_front_page() || is_page( 'about' ) || is_page( 'accommodations' ) || is_page( 'water-sports' ) || is_page( 'land-activities' ) || is_page( 'elite-facilities' ) ) {
		$classes[] = 'cwc-home';
	}
	return $classes;
}
add_filter( 'body_class', 'cwc_body_class_front_page' );

/**
 * Pages that must never appear in the header navigation.
 *
 * Looked up by slug and cached per request. Terms & Conditions and
 * Privacy Policy live in the footer only; they're filtered out of
 * both classic nav menus and the page-list fallback that the
 * `wp:navigation` block uses when no menu is explicitly assigned.
 */
function cwc_header_excluded_page_ids() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$cache = array();
	foreach ( array( 'contact-us', 'terms-and-conditions', 'privacy-policy' ) as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$cache[] = (int) $page->ID;
		}
	}
	return $cache;
}

/**
 * Strip excluded pages from any classic nav menu (covers a menu
 * assigned to the `primary` location and rendered by `wp:navigation`).
 *
 * @param array $items Nav menu objects.
 * @return array Filtered menu objects.
 */
function cwc_filter_nav_menu_items( $items ) {
	$excluded = cwc_header_excluded_page_ids();
	if ( empty( $excluded ) ) {
		return $items;
	}

	return array_values(
		array_filter(
			$items,
			function ( $item ) use ( $excluded ) {
				return 'page' !== $item->object || ! in_array( (int) $item->object_id, $excluded, true );
			}
		)
	);
}
add_filter( 'wp_nav_menu_objects', 'cwc_filter_nav_menu_items' );

/**
 * Strip excluded pages from `get_pages()` on the public frontend.
 *
 * The `core/page-list` block (the fallback used by `wp:navigation`
 * when no menu is set) calls `get_pages()` to build its tree. The
 * admin and REST contexts are left untouched so editors and page
 * pickers still see every page.
 *
 * @param WP_Post[] $pages Pages array from `get_pages()`.
 * @return WP_Post[] Filtered pages.
 */
function cwc_filter_get_pages( $pages ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $pages;
	}

	$excluded = cwc_header_excluded_page_ids();
	if ( empty( $excluded ) ) {
		return $pages;
	}

	return array_values(
		array_filter(
			$pages,
			function ( $p ) use ( $excluded ) {
				return ! in_array( (int) $p->ID, $excluded, true );
			}
		)
	);
}
add_filter( 'get_pages', 'cwc_filter_get_pages' );

/**
 * Resolve the Accommodations page ID (cached per request).
 *
 * Used by the nav-collapsing filters below to find children of the
 * Accommodations page so the header dropdown can be flattened.
 *
 * @since 1.0.0
 *
 * @return int Page ID, or 0 when the page is missing.
 */
function cwc_accommodations_page_id() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$page  = get_page_by_path( 'accommodations' );
	$cache = $page instanceof WP_Post ? (int) $page->ID : 0;
	return $cache;
}

/**
 * Hide the Accommodations submenu in the header navigation.
 *
 * The four room detail pages (Villas, Cabanas, Dwell, Cabin) are
 * children of `accommodations` and would otherwise render as a
 * dropdown under the Accommodations menu item. The room cards on the
 * Accommodations landing page already act as the entry point, so the
 * dropdown is redundant.
 *
 * Filtering is intentionally scoped to nav rendering only — the pages
 * remain published, indexable, and reachable from the room cards and
 * the "Other Rooms" block.
 *
 * @since 1.0.0
 *
 * @param array $items Nav menu objects.
 * @return array Filtered list with Accommodations children removed.
 */
function cwc_filter_nav_remove_accommodations_children( $items ) {
	$accommodations_id = cwc_accommodations_page_id();
	if ( ! $accommodations_id ) {
		return $items;
	}

	/*
	 * Find the menu item that links to the Accommodations page so we
	 * can drop any item whose `menu_item_parent` matches its ID.
	 */
	$accommodations_item_id = 0;
	foreach ( $items as $item ) {
		if ( 'page' === $item->object && (int) $item->object_id === $accommodations_id ) {
			$accommodations_item_id = (int) $item->ID;
			break;
		}
	}

	if ( ! $accommodations_item_id ) {
		return $items;
	}

	return array_values(
		array_filter(
			$items,
			function ( $item ) use ( $accommodations_item_id ) {
				return (int) $item->menu_item_parent !== $accommodations_item_id;
			}
		)
	);
}
add_filter( 'wp_nav_menu_objects', 'cwc_filter_nav_remove_accommodations_children' );

/**
 * Hide Accommodations child pages from the page-list nav fallback.
 *
 * When `wp:navigation` falls back to `core/page-list` (no menu
 * assigned), it walks the page tree from `get_pages()`. Drop pages
 * whose `post_parent` is the Accommodations page so the same
 * dropdown-collapsing behavior applies in that path.
 *
 * Admin and REST contexts are left untouched so editors and the
 * page-picker still see every page.
 *
 * @since 1.0.0
 *
 * @param WP_Post[] $pages Pages array from `get_pages()`.
 * @return WP_Post[] Filtered pages.
 */
function cwc_filter_get_pages_remove_accommodations_children( $pages ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $pages;
	}

	$accommodations_id = cwc_accommodations_page_id();
	if ( ! $accommodations_id ) {
		return $pages;
	}

	return array_values(
		array_filter(
			$pages,
			function ( $p ) use ( $accommodations_id ) {
				return (int) $p->post_parent !== $accommodations_id;
			}
		)
	);
}
add_filter( 'get_pages', 'cwc_filter_get_pages_remove_accommodations_children' );

/**
 * Allow SVG uploads in the Media Library.
 *
 * @param array $mimes Array of mime types keyed by the file extension regex.
 * @return array Modified mime types.
 */
function cwc_allow_svg_uploads( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter( 'upload_mimes', 'cwc_allow_svg_uploads' );

/**
 * Block direct access to navigation hub pages.
 *
 * Visiting /activities/ or /plan-your-trip/ directly triggers a 404.
 * Their child pages (Water Sports, Rates, etc.) remain fully accessible.
 * Admin previews are left untouched so editors can still edit these pages.
 */
function cwc_block_hub_pages() {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	if ( ! is_page() ) {
		return;
	}

	$blocked_slugs = array( 'activities', 'plan-your-trip' );
	$post          = get_queried_object();

	if ( ! $post instanceof WP_Post ) {
		return;
	}

	// Only block if this page itself is one of the hub slugs (not a child).
	if ( 0 === $post->post_parent && in_array( $post->post_name, $blocked_slugs, true ) ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}
add_action( 'template_redirect', 'cwc_block_hub_pages' );

/**
 * Block URL parameters on the booking page.
 *
 * The booking flow now relies on session storage. Direct access
 * with ?room= or ?checkin= triggers a 404.
 */
function cwc_block_booking_url_params() {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	if ( ! is_page( 'booking' ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check; triggers 404 for disallowed params.
	if ( isset( $_GET['room'] ) || isset( $_GET['checkin'] ) || isset( $_GET['checkout'] ) || isset( $_GET['guests'] ) ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}
add_action( 'template_redirect', 'cwc_block_booking_url_params' );

/**
 * Exclude navigation hub pages from search results.
 *
 * "Activities" and "Plan Your Trip" are parent nav pages with no
 * standalone content. This uses a direct $wpdb query to resolve their
 * IDs, bypassing any get_pages/get_page_by_path filters that could
 * interfere. It also hooks posts_where as a safety net.
 */
function cwc_hub_page_exclude_ids() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	global $wpdb;

	// Resolve the two hub page IDs directly — no filters, no cache layers.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional; results cached in static $cache.
	$hub_ids = $wpdb->get_col(
		"SELECT ID FROM {$wpdb->posts}
		 WHERE post_type   = 'page'
		   AND post_status = 'publish'
		   AND post_name   IN ('activities', 'plan-your-trip')"
	);

	$exclude = array_map( 'intval', (array) $hub_ids );

	// Add all descendants of those pages.
	if ( ! empty( $exclude ) ) {
		$placeholders = implode( ',', array_fill( 0, count( $exclude ), '%d' ) );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- $placeholders is a safe, generated string of %d tokens.
		$query = $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts}
			 WHERE post_type   = 'page'
			   AND post_status = 'publish'
			   AND post_parent IN ({$placeholders})",
			...$exclude
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- Intentional; results cached in static $cache.
		$child_ids = $wpdb->get_col( $query );
		$exclude   = array_unique( array_merge( $exclude, array_map( 'intval', (array) $child_ids ) ) );
	}

	$cache = $exclude;
	return $cache;
}

/**
 * Exclude hub pages and their children from search results.
 *
 * @param WP_Query $query The WP_Query instance.
 */
function cwc_exclude_hub_pages_from_search( WP_Query $query ) {
	if ( ! $query->is_search() || ! $query->is_main_query() || is_admin() ) {
		return;
	}

	$exclude = cwc_hub_page_exclude_ids();
	if ( empty( $exclude ) ) {
		return;
	}

	$existing = (array) $query->get( 'post__not_in' );
	$query->set( 'post__not_in', array_unique( array_merge( $existing, $exclude ) ) );
}
add_action( 'pre_get_posts', 'cwc_exclude_hub_pages_from_search' );

/**
 * SQL-level safety net: strip hub pages from search even if
 * post__not_in is overridden elsewhere.
 *
 * @param string   $where The WHERE clause of the query.
 * @param WP_Query $query The WP_Query instance.
 * @return string Modified WHERE clause.
 */
function cwc_exclude_hub_pages_where( $where, WP_Query $query ) {
	if ( ! $query->is_search() || ! $query->is_main_query() || is_admin() ) {
		return $where;
	}

	$exclude = cwc_hub_page_exclude_ids();
	if ( empty( $exclude ) ) {
		return $where;
	}

	global $wpdb;
	$ids_in = implode( ',', $exclude );
	$where .= " AND {$wpdb->posts}.ID NOT IN ({$ids_in})";
	return $where;
}
add_filter( 'posts_where', 'cwc_exclude_hub_pages_where', 10, 2 );

/**
 * Fix WordPress rejecting SVGs even when the MIME type is allowed.
 *
 * @param array        $data     Array of file data.
 * @param string       $file     Full path to the file.
 * @param string       $filename The name of the file.
 * @param array|string $mimes    Array of mime types or string of mime types.
 * @return array Modified file data.
 */
function cwc_fix_svg_filetype( $data, $file, $filename, $mimes ) {
 // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Signature required by wp_check_filetype_and_ext filter.
	$ext = pathinfo( $filename, PATHINFO_EXTENSION );
	if ( 'svg' === strtolower( $ext ) ) {
		$data['type'] = 'image/svg+xml';
		$data['ext']  = 'svg';
	}
	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'cwc_fix_svg_filetype', 10, 4 );

/**
 * Switch the block template for cwc_album based on whether it's a category or album.
 *
 * Uses 'single-cwc_album-category.html' for top-level categories and
 * 'single-cwc_album.html' for individual photo albums.
 *
 * @param string $template The path to the template.
 * @return string Modified template path.
 */
function cwc_album_template_switcher( $template ) {
	if ( ! is_singular( 'cwc_album' ) ) {
		return $template;
	}

	$post = get_queried_object();
	if ( ! $post || 0 !== $post->post_parent ) {
		return $template;
	}

	// It's a top-level category album. Try to load the category template.
	$category_template = locate_block_template( 'single-cwc_album-category', 'single-cwc_album-category', array() );
	if ( $category_template ) {
		return $category_template;
	}

	return $template;
}
add_filter( 'single_template', 'cwc_album_template_switcher', 20 );

/**
 * Defer non-critical CSS to improve render-blocking times.
 *
 * @param string $html   The link tag for the enqueued style.
 * @param string $handle The style's registered handle.
 * @param string $href   The stylesheet's source URL.
 * @param string $media  The stylesheet's media attribute.
 * @return string Modified link tag.
 */
function cwc_defer_non_critical_css( $html, $handle, $href, $media ) {
	$defer_styles = array(
		'cwc-footer',
	);

	if ( in_array( $handle, $defer_styles, true ) ) {
		// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		$html = sprintf(
			'<link rel="stylesheet" id="%s-css" href="%s" media="print" onload="this.media=\'all\'" />', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
			esc_attr( $handle ),
			esc_url( $href )
		);
		// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		$html .= sprintf(
			'<noscript><link rel="stylesheet" id="%s-noscript-css" href="%s" media="%s" /></noscript>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
			esc_attr( $handle ),
			esc_url( $href ),
			esc_attr( $media )
		);
	}

	return $html;
}
add_filter( 'style_loader_tag', 'cwc_defer_non_critical_css', 10, 4 );

require_once get_stylesheet_directory() . '/inc/email-handler.php';

/**
 * Global FAQ Data.
 * Centralized source of truth for the FAQ block and Search suggestions.
 */
function cwc_get_faq_data() {
	return array(
		'getting-started'       => array(
			'label' => 'Getting Started',
			'items' => array(
				array(
					'q' => 'Do I need prior experience to try wakeboarding at CWC?',
					'a' => 'No, beginners are welcome. CWC provides basic instruction and guidance, making it easy for first-timers to get started.',
				),
				array(
					'q' => 'What should I wear for wakeboarding?',
					'a' => 'Wear comfortable swimwear or athletic gear. Rash guards are recommended for added protection, and don\'t forget sunscreen.',
				),
				array(
					'q' => 'Is equipment included or do I bring my own?',
					'a' => 'CWC offers rental equipment such as wakeboards, helmets, and life vests. You can bring your own gear if you prefer.',
				),
				array(
					'q' => 'Are there instructors available for beginners?',
					'a' => 'Yes, trained instructors are available to assist and guide you through the basics before you hit the water.',
				),
				array(
					'q' => 'What is the first step when I arrive?',
					'a' => 'Start by registering at the front desk, choose your activity or package, rent equipment if needed, and attend a quick orientation before riding.',
				),
			),
		),
		'reservations-payments' => array(
			'label' => 'Reservations & Payments',
			'items' => array(
				array(
					'q' => 'How do I make a reservation?',
					'a' => 'You can book online through our website or contact our front desk directly via phone or email to reserve your preferred dates and activities.',
				),
				array(
					'q' => 'What payment methods are accepted?',
					'a' => 'We accept cash, credit/debit cards, GCash, and bank transfers. Payment details are provided upon booking confirmation.',
				),
				array(
					'q' => 'Can I cancel or reschedule my booking?',
					'a' => 'Yes, cancellations and rescheduling are allowed up to 48 hours before your visit. Late cancellations may incur a fee.',
				),
				array(
					'q' => 'Do you have ongoing promos or discounts?',
					'a' => 'We occasionally offer seasonal promos and group discounts. Check our social media pages or contact the front desk for the latest offers.',
				),
				array(
					'q' => 'Is a deposit required for bookings?',
					'a' => 'A 50% non-refundable deposit is required to confirm your reservation. The remaining balance is due on the day of your visit.',
				),
			),
		),
		'stay-comfort'          => array(
			'label' => 'Stay & Comfort',
			'items' => array(
				array(
					'q' => 'What types of accommodations are available?',
					'a' => 'CWC offers villas, cabanas, dwell units, and cabin-style rooms. Each is designed for comfort with views of the park and lake.',
				),
				array(
					'q' => 'Are the rooms air-conditioned?',
					'a' => 'Yes, all rooms come with air-conditioning, hot/cold showers, and basic amenities for a comfortable stay.',
				),
				array(
					'q' => 'Are there affordable or budget-friendly rooms?',
					'a' => 'Yes, our Cabins are designed for budget-friendly stays, offering all the essentials to recharge between wakeboarding sessions at a lower price point.',
				),
				array(
					'q' => 'Can I check in early or check out late?',
					'a' => 'Early check-in and late check-out are subject to availability. Contact our front desk in advance to arrange.',
				),
			),
		),
		'food-social'           => array(
			'label' => 'Food & Social',
			'items' => array(
				array(
					'q' => 'Are there restaurants or food stalls at CWC?',
					'a' => 'Yes, the park has an on-site restaurant and a bar serving a variety of local and international dishes, snacks, and beverages.',
				),
				array(
					'q' => 'Can I bring my own food?',
					'a' => 'Outside food is allowed in designated picnic areas, but corkage fees may apply. Check with the front desk for details.',
				),
				array(
					'q' => 'Are there social events or activities at night?',
					'a' => 'CWC hosts occasional live music events, bonfires, and themed nights. Check our social media for upcoming events.',
				),
			),
		),
		'travel-location'       => array(
			'label' => 'Travel & Location',
			'items' => array(
				array(
					'q' => 'Where is CWC located?',
					'a' => 'CWC is located at the Provincial Capitol Complex in Cadlan, Pili, Camarines Sur, Philippines — about 30 minutes from Naga City.',
				),
				array(
					'q' => 'How do I get to CWC from Manila?',
					'a' => 'You can take a direct flight to Naga Airport (about 1 hour), then a 30-minute drive to CWC. Alternatively, take a bus from Cubao to Naga City.',
				),
				array(
					'q' => 'Is parking available?',
					'a' => 'Yes, free parking is available for guests within the complex grounds.',
				),
			),
		),
	);
}

/**
 * AJAX Handler: Global Search.
 * Searches FAQs, Accommodations, and Posts/Pages.
 */
add_action( 'wp_ajax_cwc_global_search', 'cwc_global_search_handler' );
add_action( 'wp_ajax_nopriv_cwc_global_search', 'cwc_global_search_handler' );

/**
 * Intent-to-keyword mapping for natural language search.
 *
 * Maps conversational concepts (e.g. "swim", "eat", "sleep") to
 * site-relevant search terms so queries like "I want to swim, is
 * there a pool?" surface Water Sports, accommodation, and FAQ results
 * instead of an empty state.
 *
 * @return array<string, array{keywords: string[], pages: array<string, string>}>
 */
function cwc_get_intent_map() {
	$home = home_url( '/' );
	return array(
		// Water / swimming intent.
		'swim'       => array(
			'keywords' => array( 'pool', 'water', 'wakeboard', 'lake', 'aqua' ),
			'pages'    => array(
				'Water Sports'     => $home . 'water-sports/',
				'Elite Facilities' => $home . 'elite-facilities/',
			),
		),
		'pool'       => array(
			'keywords' => array( 'swim', 'water', 'aqua', 'lake' ),
			'pages'    => array(
				'Water Sports'     => $home . 'water-sports/',
				'Elite Facilities' => $home . 'elite-facilities/',
			),
		),
		'wakeboard'  => array(
			'keywords' => array( 'water', 'cable', 'ride', 'board', 'beginner' ),
			'pages'    => array(
				'Water Sports' => $home . 'water-sports/',
				'Rates'        => $home . 'rates/',
			),
		),
		'surf'       => array(
			'keywords' => array( 'wakeboard', 'water', 'board', 'wave' ),
			'pages'    => array( 'Water Sports' => $home . 'water-sports/' ),
		),
		// Accommodation intent.
		'sleep'      => array(
			'keywords' => array( 'room', 'accommodation', 'villa', 'cabin', 'cabana', 'stay', 'dwell' ),
			'pages'    => array( 'Accommodations' => $home . 'accommodations/' ),
		),
		'stay'       => array(
			'keywords' => array( 'room', 'accommodation', 'villa', 'cabin', 'cabana', 'book', 'dwell' ),
			'pages'    => array( 'Accommodations' => $home . 'accommodations/' ),
		),
		'room'       => array(
			'keywords' => array( 'accommodation', 'villa', 'cabin', 'cabana', 'stay', 'dwell' ),
			'pages'    => array( 'Accommodations' => $home . 'accommodations/' ),
		),
		'hotel'      => array(
			'keywords' => array( 'accommodation', 'villa', 'room', 'cabin', 'stay' ),
			'pages'    => array( 'Accommodations' => $home . 'accommodations/' ),
		),
		// Food / dining intent.
		'eat'        => array(
			'keywords' => array( 'food', 'restaurant', 'dining', 'bar', 'meal' ),
			'pages'    => array( 'FAQs' => $home . 'faqs/' ),
		),
		'food'       => array(
			'keywords' => array( 'restaurant', 'dining', 'eat', 'bar', 'meal' ),
			'pages'    => array( 'FAQs' => $home . 'faqs/' ),
		),
		'restaurant' => array(
			'keywords' => array( 'food', 'dining', 'eat', 'bar' ),
			'pages'    => array( 'FAQs' => $home . 'faqs/' ),
		),
		// Pricing intent.
		'price'      => array(
			'keywords' => array( 'rate', 'cost', 'fee', 'budget', 'promo', 'discount' ),
			'pages'    => array( 'Rates' => $home . 'rates/' ),
		),
		'cost'       => array(
			'keywords' => array( 'rate', 'price', 'fee', 'budget' ),
			'pages'    => array( 'Rates' => $home . 'rates/' ),
		),
		'cheap'      => array(
			'keywords' => array( 'budget', 'affordable', 'cabin', 'rate', 'promo' ),
			'pages'    => array(
				'Rates'          => $home . 'rates/',
				'Accommodations' => $home . 'accommodations/',
			),
		),
		'budget'     => array(
			'keywords' => array( 'cheap', 'affordable', 'cabin', 'rate', 'promo' ),
			'pages'    => array( 'Rates' => $home . 'rates/' ),
		),
		// Booking intent.
		'book'       => array(
			'keywords' => array( 'reservation', 'reserve', 'booking', 'schedule' ),
			'pages'    => array( 'Book Now' => $home . 'book-now/' ),
		),
		'reserve'    => array(
			'keywords' => array( 'book', 'reservation', 'booking' ),
			'pages'    => array( 'Book Now' => $home . 'book-now/' ),
		),
		// Travel / location intent.
		'directions' => array(
			'keywords' => array( 'location', 'map', 'travel', 'address', 'get' ),
			'pages'    => array(
				'FAQs'       => $home . 'faqs/',
				'Contact Us' => $home . 'contact-us/',
			),
		),
		'location'   => array(
			'keywords' => array( 'directions', 'map', 'address', 'where' ),
			'pages'    => array( 'Contact Us' => $home . 'contact-us/' ),
		),
		// Activity intent.
		'activity'   => array(
			'keywords' => array( 'sport', 'adventure', 'fun', 'ride', 'game' ),
			'pages'    => array(
				'Water Sports'     => $home . 'water-sports/',
				'Land Activities'  => $home . 'land-activities/',
				'Elite Facilities' => $home . 'elite-facilities/',
			),
		),
		'fun'        => array(
			'keywords' => array( 'activity', 'adventure', 'sport', 'game', 'play' ),
			'pages'    => array(
				'Water Sports'    => $home . 'water-sports/',
				'Land Activities' => $home . 'land-activities/',
			),
		),
		// Family intent.
		'kid'        => array(
			'keywords' => array( 'child', 'family', 'children', 'kids' ),
			'pages'    => array(
				'Land Activities'  => $home . 'land-activities/',
				'Elite Facilities' => $home . 'elite-facilities/',
				'FAQs'             => $home . 'faqs/',
			),
		),
		'family'     => array(
			'keywords' => array( 'kid', 'child', 'group', 'children' ),
			'pages'    => array(
				'Accommodations' => $home . 'accommodations/',
				'Rates'          => $home . 'rates/',
			),
		),
		// Photo / gallery intent.
		'photo'      => array(
			'keywords' => array( 'gallery', 'picture', 'image', 'album' ),
			'pages'    => array( 'Gallery' => $home . 'gallery/' ),
		),
		'picture'    => array(
			'keywords' => array( 'gallery', 'photo', 'image', 'album' ),
			'pages'    => array( 'Gallery' => $home . 'gallery/' ),
		),
	);
}

/**
 * Handle the cwc_global_search AJAX action.
 *
 * Searches across FAQs, accommodations, and posts/pages using a
 * phrase-aware, word-based matching algorithm with stop-word filtering.
 * When no results are found, performs intent-based keyword expansion
 * and always provides smart fallback suggestions.
 *
 * @return void Sends JSON response and exits.
 */
function cwc_global_search_handler() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public search endpoint; read-only, no state changes.
	$q = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

	if ( strlen( $q ) < 2 ) {
		wp_send_json_success(
			array(
				'faqs'           => array(),
				'accommodations' => array(),
				'posts'          => array(),
				'suggestions'    => array(),
				'intent_hint'    => '',
			)
		);
	}

	$results = array(
		'faqs'           => array(),
		'accommodations' => array(),
		'posts'          => array(),
		'suggestions'    => array(),
		'intent_hint'    => '',
	);

	// ── Shared text-processing helpers ──────────────────────────
	$clean_q    = strtolower( preg_replace( '/[^\w\s]/', '', $q ) );
	$stop_words = array( 'i', 'want', 'the', 'a', 'an', 'and', 'for', 'to', 'how', 'do', 'can', 'is', 'are', 'what', 'where', 'there', 'any', 'have', 'has', 'does', 'it', 'my', 'me', 'we', 'you', 'your', 'this', 'that' );
	$q_words    = array_filter(
		explode( ' ', $clean_q ),
		function ( $w ) use ( $stop_words ) {
			return strlen( $w ) > 2 && ! in_array( $w, $stop_words, true );
		}
	);

	// If all words were stop words, use the original words.
	if ( empty( $q_words ) ) {
		$q_words = array_filter(
			explode( ' ', $clean_q ),
			function ( $w ) {
				return strlen( $w ) > 2;
			}
		);
	}

	// ── 1. Search FAQs ─────────────────────────────────────────
	$faq_data = cwc_get_faq_data();
	$faq_url  = home_url( '/faqs/' );

	$results['faqs'] = cwc_search_faqs( $faq_data, $faq_url, $clean_q, $q_words );

	// ── 2. Search Accommodations ────────────────────────────────
	$acc_query = new WP_Query(
		array(
			'post_type'      => 'accommodation',
			'post_status'    => 'publish',
			's'              => $q,
			'posts_per_page' => 5,
		)
	);

	foreach ( $acc_query->posts as $post ) {
		$results['accommodations'][] = array(
			'title' => get_the_title( $post->ID ),
			'url'   => get_permalink( $post->ID ),
			'type'  => 'accommodation',
		);
	}

	// ── 3. Search Posts/Pages ───────────────────────────────────
	$post_query = new WP_Query(
		array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			's'              => $q,
			'posts_per_page' => 5,
		)
	);

	foreach ( $post_query->posts as $post ) {
		$results['posts'][] = array(
			'title' => get_the_title( $post->ID ),
			'url'   => get_permalink( $post->ID ),
			'type'  => $post->post_type,
		);
	}

	// ── 4. Intent-based fallback when results are empty ─────────
	$total = count( $results['faqs'] ) + count( $results['accommodations'] ) + count( $results['posts'] );

	if ( $total === 0 ) {
		$intent         = cwc_resolve_intent( $q_words, $clean_q );
		$expanded_words = $intent['keywords'];

		if ( ! empty( $expanded_words ) ) {
			$results['intent_hint'] = $intent['hint'];

			// Re-search FAQs with expanded keywords.
			$results['faqs'] = cwc_search_faqs( $faq_data, $faq_url, '', $expanded_words );

			// Re-search posts/pages with each expanded keyword.
			$seen_ids = array();
			foreach ( $expanded_words as $kw ) {
				$kw_query = new WP_Query(
					array(
						'post_type'      => array( 'post', 'page', 'accommodation' ),
						'post_status'    => 'publish',
						's'              => $kw,
						'posts_per_page' => 3,
					)
				);
				foreach ( $kw_query->posts as $post ) {
					if ( isset( $seen_ids[ $post->ID ] ) ) {
						continue;
					}
					$seen_ids[ $post->ID ] = true;
					$bucket                = 'accommodation' === $post->post_type ? 'accommodations' : 'posts';
					$results[ $bucket ][]  = array(
						'title' => get_the_title( $post->ID ),
						'url'   => get_permalink( $post->ID ),
						'type'  => $post->post_type,
					);
				}
			}

			$results['accommodations'] = array_slice( $results['accommodations'], 0, 5 );
			$results['posts']          = array_slice( $results['posts'], 0, 5 );

			// Add curated page suggestions from intent map.
			foreach ( $intent['pages'] as $label => $url ) {
				$results['suggestions'][] = array(
					'title' => $label,
					'url'   => $url,
					'type'  => 'suggestion',
				);
			}
		}
	}

	// ── 5. Always-on fallback suggestions ───────────────────────
	$total_after = count( $results['faqs'] ) + count( $results['accommodations'] ) + count( $results['posts'] );

	if ( $total_after === 0 ) {
		$results['suggestions'] = cwc_get_fallback_suggestions( $q );
		if ( empty( $results['intent_hint'] ) ) {
			$results['intent_hint'] = 'We couldn\'t find an exact match, but here are some pages you might find helpful:';
		}
	}

	wp_send_json_success( $results );
}

/**
 * Search FAQs using phrase and word-based matching.
 *
 * @param array    $faq_data  Structured FAQ categories from cwc_get_faq_data().
 * @param string   $faq_url   URL to link FAQ results to.
 * @param string   $phrase    Cleaned query phrase for exact matching (may be empty).
 * @param string[] $words     Array of significant query words.
 * @return array Matched FAQ items (max 5).
 */
function cwc_search_faqs( $faq_data, $faq_url, $phrase, $words ) {
	$matches = array();

	foreach ( $faq_data as $cat ) {
		foreach ( $cat['items'] as $item ) {
			$clean_item_q = strtolower( preg_replace( '/[^\w\s]/', '', $item['q'] ) );
			$clean_item_a = strtolower( preg_replace( '/[^\w\s]/', '', $item['a'] ) );

			$is_match = false;

			// Priority 1: Phrase match.
			if ( ! empty( $phrase ) && ( stripos( $clean_item_q, $phrase ) !== false || stripos( $clean_item_a, $phrase ) !== false ) ) {
				$is_match = true;
			} elseif ( ! empty( $words ) ) {
				// Priority 2: Word-based match.
				$match_count = 0;
				foreach ( $words as $word ) {
					if ( stripos( $clean_item_q, $word ) !== false || stripos( $clean_item_a, $word ) !== false ) {
						++$match_count;
					}
				}
				$threshold = count( $words ) === 1 ? 1 : max( 1, (int) ceil( count( $words ) * 0.4 ) );
				if ( $match_count >= $threshold ) {
					$is_match = true;
				}
			}

			if ( $is_match ) {
				$matches[] = array(
					'title'   => $item['q'],
					'excerpt' => wp_trim_words( $item['a'], 15 ),
					'url'     => $faq_url,
				);
			}
		}
	}

	return array_slice( $matches, 0, 5 );
}

/**
 * Resolve user intent from query words using the intent map.
 *
 * Scans the user's significant words against the intent map keys
 * (including partial / stem matching) and returns expanded keywords,
 * curated page links, and a human-readable hint.
 *
 * @param string[] $q_words  Significant words from the user query.
 * @param string   $clean_q  Full cleaned query string.
 * @return array{keywords: string[], pages: array<string, string>, hint: string}
 */
function cwc_resolve_intent( $q_words, $clean_q ) {
	$intent_map = cwc_get_intent_map();
	$keywords   = array();
	$pages      = array();
	$matched    = array();

	foreach ( $q_words as $word ) {
		foreach ( $intent_map as $intent_key => $intent_data ) {
			// Match if the word starts with the intent key or vice-versa (stem matching).
			if ( strpos( $word, $intent_key ) === 0 || strpos( $intent_key, $word ) === 0 ) {
				$keywords  = array_merge( $keywords, $intent_data['keywords'] );
				$pages     = array_merge( $pages, $intent_data['pages'] );
				$matched[] = $intent_key;
			}
		}
	}

	// Also check if the full query contains any intent key.
	if ( empty( $matched ) ) {
		foreach ( $intent_map as $intent_key => $intent_data ) {
			if ( strpos( $clean_q, $intent_key ) !== false ) {
				$keywords  = array_merge( $keywords, $intent_data['keywords'] );
				$pages     = array_merge( $pages, $intent_data['pages'] );
				$matched[] = $intent_key;
			}
		}
	}

	$keywords = array_unique( $keywords );
	$hint     = '';

	if ( ! empty( $matched ) ) {
		$hint = 'Based on your question, here\'s what we found related to ' . implode( ', ', array_unique( $matched ) ) . ':';
	}

	return array(
		'keywords' => array_values( $keywords ),
		'pages'    => $pages,
		'hint'     => $hint,
	);
}

/**
 * Build always-on fallback suggestions for truly unmatched queries.
 *
 * Returns a curated set of the site's most popular pages so users
 * always have somewhere to go.
 *
 * @param string $q Original query string (unused for now, available for future ranking).
 * @return array Array of suggestion items.
 */
function cwc_get_fallback_suggestions( $q ) {
	$home = home_url( '/' );
	return array(
		array(
			'title' => 'Water Sports & Wakeboarding',
			'url'   => $home . 'water-sports/',
			'type'  => 'suggestion',
		),
		array(
			'title' => 'Accommodations & Rooms',
			'url'   => $home . 'accommodations/',
			'type'  => 'suggestion',
		),
		array(
			'title' => 'Rates & Pricing',
			'url'   => $home . 'rates/',
			'type'  => 'suggestion',
		),
		array(
			'title' => 'Frequently Asked Questions',
			'url'   => $home . 'faqs/',
			'type'  => 'suggestion',
		),
		array(
			'title' => 'Contact Us',
			'url'   => $home . 'contact-us/',
			'type'  => 'suggestion',
		),
	);
}

/**
 * AJAX Handler: Search Recommendations.
 */
add_action( 'wp_ajax_cwc_search_recommendations', 'cwc_search_recommendations_handler' );
add_action( 'wp_ajax_nopriv_cwc_search_recommendations', 'cwc_search_recommendations_handler' );

/**
 * Handle the cwc_search_recommendations AJAX action.
 *
 * Returns a curated list of search recommendations and a randomised
 * selection of accommodation and blog suggestions.
 *
 * @return void Sends JSON response and exits.
 */
function cwc_search_recommendations_handler() {
	$recommendations = array(
		'How to get to CWC?',
		'What to wear for wakeboarding?',
		'Villas available',
		'Rates for 2026',
		'Beginner wakeboarding',
	);

	$suggestions = array();

	// Add a few accommodations.
	$acc_query = new WP_Query(
		array(
			'post_type'      => 'accommodation',
			'post_status'    => 'publish',
			'posts_per_page' => 2,
			'orderby'        => 'rand',
		)
	);

	foreach ( $acc_query->posts as $post ) {
		$suggestions[] = array(
			'title' => get_the_title( $post->ID ),
			'url'   => get_permalink( $post->ID ),
			'type'  => 'accommodation',
		);
	}

	// Add a few blog posts.
	$blog_query = new WP_Query(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 2,
			'orderby'        => 'date',
		)
	);

	foreach ( $blog_query->posts as $post ) {
		$suggestions[] = array(
			'title' => get_the_title( $post->ID ),
			'url'   => get_permalink( $post->ID ),
			'type'  => 'post',
		);
	}

	wp_send_json_success(
		array(
			'recommendations' => $recommendations,
			'suggestions'     => $suggestions,
		)
	);
}
