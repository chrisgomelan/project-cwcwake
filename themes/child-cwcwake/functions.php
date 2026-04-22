<?php
/**
 * CWC Wake Child Theme — functions.php
 *
 * @package CWC_Wake
 * @since   0.1.0
 */

if (!defined('ABSPATH')) {
	exit;
}

define('CWC_VERSION', wp_get_theme()->get('Version'));

/**
 * Enqueue parent and child theme stylesheets.
 */
function cwc_enqueue_styles()
{
	wp_enqueue_style(
		'cwc-google-fonts',
		'https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Archivo:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap',
		[],
		null
	);

	wp_enqueue_style(
		'twentytwentyfive-style',
		get_template_directory_uri() . '/style.css',
		['cwc-google-fonts'],
		wp_get_theme('twentytwentyfive')->get('Version')
	);

	wp_enqueue_style(
		'cwc-global',
		get_stylesheet_directory_uri() . '/assets/css/global.css',
		['twentytwentyfive-style'],
		CWC_VERSION
	);

	wp_enqueue_style(
		'cwc-header',
		get_stylesheet_directory_uri() . '/assets/css/header.css',
		['cwc-global'],
		CWC_VERSION
	);

	wp_enqueue_style(
		'cwc-footer',
		get_stylesheet_directory_uri() . '/assets/css/footer.css',
		['cwc-global'],
		CWC_VERSION
	);

	wp_enqueue_style(
		'cwc-style',
		get_stylesheet_uri(),
		['cwc-header'],
		CWC_VERSION
	);

	wp_enqueue_script(
		'cwc-header',
		get_stylesheet_directory_uri() . '/assets/js/header.js',
		[],
		CWC_VERSION,
		true
	);

	// Custom Image Modal — Lightweight native popup slider.
	wp_enqueue_script(
		'cwc-image-modal',
		get_stylesheet_directory_uri() . '/assets/js/image-modal.js',
		[],
		CWC_VERSION,
		true
	);
}
add_action('wp_enqueue_scripts', 'cwc_enqueue_styles');

/**
 * Load Google Fonts in the block editor to match frontend.
 */
function cwc_enqueue_editor_styles()
{
	add_editor_style('https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Archivo:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');
	add_editor_style('assets/css/global.css');
}
add_action('after_setup_theme', 'cwc_enqueue_editor_styles');

/**
 * Theme setup: register supports, menus, image sizes.
 */
function cwc_theme_setup()
{
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	]);
	add_theme_support('editor-styles');
	add_theme_support('wp-block-styles');
	add_theme_support('responsive-embeds');

	register_nav_menus([
		'primary' => __('Primary Navigation', 'child-cwcwake'),
		'footer' => __('Footer Navigation', 'child-cwcwake'),
	]);
}
add_action('after_setup_theme', 'cwc_theme_setup');

/**
 * Create initial page structure on theme activation.
 *
 * Builds the full site hierarchy: top-level pages and their children.
 * Only runs once — guarded by the 'cwc_pages_created' option.
 */
function cwc_create_initial_pages()
{
	if (get_option('cwc_pages_created')) {
		return;
	}

	$pages = [
		'Home' => ['order' => 1, 'template' => ''],
		'Activities' => [
			'order' => 2,
			'template' => 'page-activities',
			'children' => ['Water Sports', 'Land Activities', 'Elite Facilities'],
		],
		'Accommodations' => [
			'order' => 3,
			'template' => 'page-accommodations',
			'children' => [
				'Villas' => 'room-detail',
				'Cabanas' => 'room-detail',
				'Dwell' => 'room-detail',
				'Cabin' => 'room-detail',
			],
		],
		'Plan Your Trip' => [
			'order' => 4,
			'template' => 'page-plan-your-trip',
			'children' => [
				'Rates' => 'page-child',
				'FAQs' => 'page-child',
				'Blogs' => 'page-child',
				'Gallery' => [
					'template' => 'page-gallery',
					'children' => [
						'Events' => 'page-child',
						'Lifestyle' => 'page-child',
						'Explore CamSur' => 'page-child',
					],
				],
			],
		],
		'About' => ['order' => 5, 'template' => 'page-about'],
		'Contact Us' => ['order' => 6, 'template' => 'page-contact'],
		'Terms & Conditions' => ['order' => 7, 'template' => 'page-terms-and-conditions'],
		'Privacy Policy' => ['order' => 8, 'template' => 'page-privacy-policy'],
	];

	foreach ($pages as $title => $config) {
		$post_data = [
			'post_title' => $title,
			'post_status' => 'publish',
			'post_type' => 'page',
			'menu_order' => $config['order'],
		];

		if (!empty($config['template'])) {
			$post_data['page_template'] = $config['template'];
		}

		$parent_id = wp_insert_post($post_data);

		if (is_wp_error($parent_id)) {
			continue;
		}

		if (!empty($config['template'])) {
			update_post_meta($parent_id, '_wp_page_template', $config['template']);
		}

		if (!empty($config['children'])) {
			cwc_seed_child_pages($config['children'], $parent_id);
		}
	}

	$home = get_page_by_title('Home');
	if ($home) {
		update_option('show_on_front', 'page');
		update_option('page_on_front', $home->ID);
	}

	update_option('cwc_pages_created', true);
}
add_action('after_switch_theme', 'cwc_create_initial_pages');

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
function cwc_seed_child_pages(array $children, $parent_id)
{
	$order = 1;

	foreach ($children as $key => $value) {
		if (is_int($key)) {
			$title = $value;
			$template = 'page-child';
			$grandchildren = [];
		} elseif (is_array($value)) {
			$title = $key;
			$template = $value['template'] ?? 'page-child';
			$grandchildren = $value['children'] ?? [];
		} else {
			$title = $key;
			$template = $value;
			$grandchildren = [];
		}

		$child_id = wp_insert_post([
			'post_title' => $title,
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => $parent_id,
			'menu_order' => $order++,
		]);

		if (is_wp_error($child_id)) {
			continue;
		}

		if (!empty($template)) {
			update_post_meta($child_id, '_wp_page_template', $template);
		}

		if (!empty($grandchildren)) {
			cwc_seed_child_pages($grandchildren, $child_id);
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
require_once get_stylesheet_directory() . '/inc/policy-pages-seed.php';

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
function cwc_create_primary_menu()
{
	if (get_option('cwc_menu_created')) {
		return;
	}

	$menu_name = 'Primary Navigation';
	$menu_id = wp_create_nav_menu($menu_name);

	if (is_wp_error($menu_id)) {
		return;
	}

	$menu_order = 1;
	$pages = get_pages(['sort_column' => 'menu_order', 'hierarchical' => false]);
	$page_map = [];

	foreach ($pages as $page) {
		$page_map[$page->post_title] = $page->ID;
	}

	$structure = [
		'Home' => [],
		'Activities' => ['Water Sports', 'Land Activities', 'Elite Facilities'],
		'Accommodations' => ['Villas', 'Cabanas', 'Dwell', 'Cabin'],
		'Gallery' => ['Events', 'Lifestyle', 'Explore CamSur'],
		'Plan Your Trip' => ['Rates', 'FAQs', 'Blogs'],
		'About' => [],
	];

	foreach ($structure as $parent_title => $children) {
		if (!isset($page_map[$parent_title])) {
			continue;
		}

		$parent_menu_item_id = wp_update_nav_menu_item($menu_id, 0, [
			'menu-item-title' => $parent_title,
			'menu-item-object' => 'page',
			'menu-item-object-id' => $page_map[$parent_title],
			'menu-item-type' => 'post_type',
			'menu-item-status' => 'publish',
			'menu-item-position' => $menu_order++,
		]);

		foreach ($children as $child_title) {
			if (!isset($page_map[$child_title])) {
				continue;
			}

			wp_update_nav_menu_item($menu_id, 0, [
				'menu-item-title' => $child_title,
				'menu-item-object' => 'page',
				'menu-item-object-id' => $page_map[$child_title],
				'menu-item-type' => 'post_type',
				'menu-item-status' => 'publish',
				'menu-item-parent-id' => $parent_menu_item_id,
				'menu-item-position' => $menu_order++,
			]);
		}
	}

	$locations = get_theme_mod('nav_menu_locations', []);
	$locations['primary'] = $menu_id;
	set_theme_mod('nav_menu_locations', $locations);

	update_option('cwc_menu_created', true);
}
add_action('after_switch_theme', 'cwc_create_primary_menu', 20);

/**
 * Build the breadcrumb trail for the current request.
 *
 * Shared by the `cwc/breadcrumbs` block and by `cwc/page-banner`
 * when it renders breadcrumbs inside the banner area.
 *
 * Each crumb is `[ 'label' => string, 'url' => string|null ]`.
 * A null URL marks the current (non-link) item.
 */
function cwc_build_breadcrumbs($home_label = 'Home')
{
	$crumbs = [
		['label' => $home_label, 'url' => home_url('/')],
	];

	if (is_front_page()) {
		$crumbs[count($crumbs) - 1]['url'] = null;
	} elseif (is_page()) {
		$ancestors = array_reverse(get_post_ancestors(get_the_ID()));
		foreach ($ancestors as $ancestor_id) {
			$crumbs[] = [
				'label' => get_the_title($ancestor_id),
				'url' => get_permalink($ancestor_id),
			];
		}
		$crumbs[] = ['label' => get_the_title(), 'url' => null];
	} elseif (is_singular()) {
		$crumbs[] = ['label' => get_the_title(), 'url' => null];
	} elseif (is_category() || is_tag() || is_tax() || is_post_type_archive() || is_archive()) {
		$crumbs[] = [
			'label' => wp_strip_all_tags(get_the_archive_title()),
			'url' => null,
		];
	} elseif (is_search()) {
		/* translators: %s: Search query. */
		$crumbs[] = [
			'label' => sprintf(__('Search: %s', 'child-cwcwake'), get_search_query()),
			'url' => null,
		];
	} elseif (is_404()) {
		$crumbs[] = ['label' => __('Not Found', 'child-cwcwake'), 'url' => null];
	}

	/** This filter is documented in blocks/breadcrumbs/render.php */
	return apply_filters('cwc_breadcrumbs_items', $crumbs);
}

/**
 * Render the breadcrumb trail HTML.
 *
 * @param array $args {
 *     @type string $home_label     Label for the first crumb. Default 'Home'.
 *     @type bool   $show_home_icon Whether to show the home icon. Default true.
 *     @type string $extra_class    Optional extra class appended to the <nav> element.
 * }
 * @return string Rendered <nav> markup, or empty string if there's nothing to render.
 */
function cwc_render_breadcrumbs($args = [])
{
	$args = wp_parse_args($args, [
		'home_label' => 'Home',
		'show_home_icon' => true,
		'extra_class' => '',
	]);

	$crumbs = cwc_build_breadcrumbs($args['home_label']);
	if (count($crumbs) < 2) {
		return '';
	}

	$class = trim('cwc-breadcrumbs ' . $args['extra_class']);
	$last = count($crumbs) - 1;

	ob_start();
	?>
	<nav class="<?php echo esc_attr($class); ?>" role="navigation"
		aria-label="<?php esc_attr_e('Breadcrumb', 'child-cwcwake'); ?>">
		<ol class="cwc-breadcrumbs__list">
			<?php foreach ($crumbs as $i => $crumb):
				$is_first = (0 === $i);
				$is_last = ($last === $i);
				?>
				<li class="cwc-breadcrumbs__item<?php echo $is_last ? ' cwc-breadcrumbs__item--current' : ''; ?>">
					<?php if (!empty($crumb['url'])): ?>
						<a class="cwc-breadcrumbs__link" href="<?php echo esc_url($crumb['url']); ?>">
							<?php if ($is_first && $args['show_home_icon']): ?>
								<svg class="cwc-breadcrumbs__home-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14"
									viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
									<path d="M12 3 2 12h3v8h6v-6h2v6h6v-8h3z" />
								</svg>
							<?php endif; ?>
							<span><?php echo esc_html($crumb['label']); ?></span>
						</a>
					<?php else: ?>
						<span class="cwc-breadcrumbs__current" aria-current="page">
							<?php echo esc_html($crumb['label']); ?>
						</span>
					<?php endif; ?>

					<?php if (!$is_last): ?>
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
function cwc_register_blocks()
{
	register_block_type(get_stylesheet_directory() . '/blocks/hero-section');
	register_block_type(get_stylesheet_directory() . '/blocks/intro-section');
	register_block_type(get_stylesheet_directory() . '/blocks/showcase-section');
	register_block_type(get_stylesheet_directory() . '/blocks/accommodations-section');
	register_block_type(get_stylesheet_directory() . '/blocks/reviews-section');
	register_block_type(get_stylesheet_directory() . '/blocks/page-banner');
	register_block_type(get_stylesheet_directory() . '/blocks/breadcrumbs');
	register_block_type(get_stylesheet_directory() . '/blocks/gallery-grid');
	register_block_type(get_stylesheet_directory() . '/blocks/cards-section');
	register_block_type(get_stylesheet_directory() . '/blocks/room-gallery');
	register_block_type(get_stylesheet_directory() . '/blocks/room-info');
	register_block_type(get_stylesheet_directory() . '/blocks/other-rooms');
	register_block_type(get_stylesheet_directory() . '/blocks/contact-info');
	register_block_type(get_stylesheet_directory() . '/blocks/contact-form');
	register_block_type(get_stylesheet_directory() . '/blocks/policy-content');
	register_block_type(get_stylesheet_directory() . '/blocks/albums-grid');
	register_block_type(get_stylesheet_directory() . '/blocks/album-back-link');
	register_block_type(get_stylesheet_directory() . '/blocks/why-stay');
	register_block_type(get_stylesheet_directory() . '/blocks/featured-blogs');
	register_block_type(get_stylesheet_directory() . '/blocks/upcoming-events');
	register_block_type(get_stylesheet_directory() . '/blocks/all-blogs');
}
add_action('init', 'cwc_register_blocks');

/**
 * Add a body class to mark the front page.
 *
 * Used by the header CSS/JS so the transparent-on-top behavior only
 * applies on the home page; all other pages get an opaque header.
 */
function cwc_body_class_front_page($classes)
{
	if (is_front_page()) {
		$classes[] = 'cwc-home';
	}
	return $classes;
}
add_filter('body_class', 'cwc_body_class_front_page');

/**
 * Pages that must never appear in the header navigation.
 *
 * Looked up by slug and cached per request. Terms & Conditions and
 * Privacy Policy live in the footer only; they're filtered out of
 * both classic nav menus and the page-list fallback that the
 * `wp:navigation` block uses when no menu is explicitly assigned.
 */
function cwc_header_excluded_page_ids()
{
	static $cache = null;
	if ($cache !== null) {
		return $cache;
	}

	$cache = [];
	foreach (['contact-us', 'terms-and-conditions', 'privacy-policy'] as $slug) {
		$page = get_page_by_path($slug);
		if ($page) {
			$cache[] = (int) $page->ID;
		}
	}
	return $cache;
}

/**
 * Strip excluded pages from any classic nav menu (covers a menu
 * assigned to the `primary` location and rendered by `wp:navigation`).
 */
function cwc_filter_nav_menu_items($items)
{
	$excluded = cwc_header_excluded_page_ids();
	if (empty($excluded)) {
		return $items;
	}

	return array_values(array_filter($items, function ($item) use ($excluded) {
		return 'page' !== $item->object || !in_array((int) $item->object_id, $excluded, true);
	}));
}
add_filter('wp_nav_menu_objects', 'cwc_filter_nav_menu_items');

/**
 * Strip excluded pages from `get_pages()` on the public frontend.
 *
 * The `core/page-list` block (the fallback used by `wp:navigation`
 * when no menu is set) calls `get_pages()` to build its tree. The
 * admin and REST contexts are left untouched so editors and page
 * pickers still see every page.
 */
function cwc_filter_get_pages($pages)
{
	if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) {
		return $pages;
	}

	$excluded = cwc_header_excluded_page_ids();
	if (empty($excluded)) {
		return $pages;
	}

	return array_values(array_filter($pages, function ($p) use ($excluded) {
		return !in_array((int) $p->ID, $excluded, true);
	}));
}
add_filter('get_pages', 'cwc_filter_get_pages');

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
function cwc_accommodations_page_id()
{
	static $cache = null;
	if ($cache !== null) {
		return $cache;
	}

	$page = get_page_by_path('accommodations');
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
function cwc_filter_nav_remove_accommodations_children($items)
{
	$accommodations_id = cwc_accommodations_page_id();
	if (!$accommodations_id) {
		return $items;
	}

	/*
	 * Find the menu item that links to the Accommodations page so we
	 * can drop any item whose `menu_item_parent` matches its ID.
	 */
	$accommodations_item_id = 0;
	foreach ($items as $item) {
		if ('page' === $item->object && (int) $item->object_id === $accommodations_id) {
			$accommodations_item_id = (int) $item->ID;
			break;
		}
	}

	if (!$accommodations_item_id) {
		return $items;
	}

	return array_values(array_filter($items, function ($item) use ($accommodations_item_id) {
		return (int) $item->menu_item_parent !== $accommodations_item_id;
	}));
}
add_filter('wp_nav_menu_objects', 'cwc_filter_nav_remove_accommodations_children');

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
function cwc_filter_get_pages_remove_accommodations_children($pages)
{
	if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) {
		return $pages;
	}

	$accommodations_id = cwc_accommodations_page_id();
	if (!$accommodations_id) {
		return $pages;
	}

	return array_values(array_filter($pages, function ($p) use ($accommodations_id) {
		return (int) $p->post_parent !== $accommodations_id;
	}));
}
add_filter('get_pages', 'cwc_filter_get_pages_remove_accommodations_children');

/**
 * Allow SVG uploads in the Media Library.
 */
function cwc_allow_svg_uploads($mimes)
{
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter('upload_mimes', 'cwc_allow_svg_uploads');

/**
 * Fix WordPress rejecting SVGs even when the MIME type is allowed.
 */
function cwc_fix_svg_filetype($data, $file, $filename, $mimes)
{
	$ext = pathinfo($filename, PATHINFO_EXTENSION);
	if ('svg' === strtolower($ext)) {
		$data['type'] = 'image/svg+xml';
		$data['ext'] = 'svg';
	}
	return $data;
}
add_filter('wp_check_filetype_and_ext', 'cwc_fix_svg_filetype', 10, 4);

/**
 * Switch the block template for cwc_album based on whether it's a category or album.
 *
 * Uses 'single-cwc_album-category.html' for top-level categories and
 * 'single-cwc_album.html' for individual photo albums.
 */
function cwc_album_template_switcher($template)
{
	if (!is_singular('cwc_album')) {
		return $template;
	}

	$post = get_queried_object();
	if (!$post || $post->post_parent !== 0) {
		return $template;
	}

	// It's a top-level category album. Try to load the category template.
	$category_template = locate_block_template('single-cwc_album-category', 'single-cwc_album-category', []);
	if ($category_template) {
		return $category_template;
	}

	return $template;
}
add_filter('single_template', 'cwc_album_template_switcher', 20);
