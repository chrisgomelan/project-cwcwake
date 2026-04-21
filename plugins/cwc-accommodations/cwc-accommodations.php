<?php
/**
 * Plugin Name:       CWC Wake — Accommodations
 * Plugin URI:        https://camsurwatersportscomplex.com/
 * Description:       Custom Post Type, meta fields, admin UI, and global policies for the CWC Wake room management system. Originally lived in the child-cwcwake theme; extracted to a plugin so room data survives theme changes.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            CWC Wake
 * Text Domain:       cwc-accommodations
 * Domain Path:       /languages
 *
 * @package CWC_Accommodations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------------------------------------------------
 * Plugin constants
 *
 * One central place that resolves the plugin's own paths so the
 * include files don't each have to recompute them. The icon
 * SVGs intentionally live in the active theme (they're brand
 * assets the designer iterates on alongside the rest of the
 * site) — the helpers in `includes/cpt.php` resolve those via
 * `get_stylesheet_directory_uri()` so the plugin stays
 * theme-agnostic at the plumbing level.
 * --------------------------------------------------------- */

define( 'CWC_ACC_VERSION', '1.0.0' );
define( 'CWC_ACC_FILE', __FILE__ );
define( 'CWC_ACC_PATH', plugin_dir_path( __FILE__ ) );
define( 'CWC_ACC_URL', plugin_dir_url( __FILE__ ) );

/* ---------------------------------------------------------
 * Module loader
 *
 * Loaded in dependency order:
 *
 *   1. cpt.php       — registers the CPT, meta, and shared
 *                      catalogues every other module relies on.
 *   2. metabox.php   — admin editor UI (depends on the CPT
 *                      being registered first so its hook fires).
 *   3. policies.php  — Settings → Global Policies page (depends
 *                      on `cwc_policy_icon_catalogue()` from cpt.php).
 *   4. migrate.php   — one-shot migration from legacy theme
 *                      pages into the new CPT (depends on every
 *                      other module being available before init
 *                      priority 34 fires).
 *
 * Each file is `require_once` so reactivating the plugin (which
 * re-runs this bootstrap) is idempotent.
 * --------------------------------------------------------- */

require_once CWC_ACC_PATH . 'includes/cpt.php';
require_once CWC_ACC_PATH . 'includes/settings.php';
require_once CWC_ACC_PATH . 'includes/metabox.php';
require_once CWC_ACC_PATH . 'includes/policies.php';
require_once CWC_ACC_PATH . 'includes/migrate.php';

/* ---------------------------------------------------------
 * Activation / deactivation
 *
 * Activation needs to:
 *   - Register the CPT (so `flush_rewrite_rules()` knows about
 *     the `/accommodations/<slug>/` permalink structure).
 *   - Flush rewrites so the new permalinks resolve immediately
 *     without an editor having to manually re-save permalinks.
 *
 * Deactivation flushes rewrites again so any cached
 * `/accommodations/<slug>/` rules don't outlive the plugin and
 * 404 silently after a deactivation.
 *
 * No data is touched on either hook — rooms are content the user
 * created, and we never delete content on deactivation. Real
 * removal lives in `uninstall.php` (intentionally absent so
 * deactivation is fully reversible).
 * --------------------------------------------------------- */

/**
 * Run on plugin activation.
 *
 * Calling `cwc_register_accommodation_cpt()` directly (instead of
 * waiting for `init`) means the CPT is registered before
 * `flush_rewrite_rules()` runs, so the rewrite tables include the
 * new rules on the very first request after activation.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_acc_activate() {
	if ( function_exists( 'cwc_register_accommodation_cpt' ) ) {
		cwc_register_accommodation_cpt();
	}
	flush_rewrite_rules();
}
register_activation_hook( CWC_ACC_FILE, 'cwc_acc_activate' );

/**
 * Run on plugin deactivation.
 *
 * Strips the rewrite rules the plugin added so an `/accommodations/`
 * permalink doesn't keep matching after the plugin is gone.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_acc_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( CWC_ACC_FILE, 'cwc_acc_deactivate' );

/* ---------------------------------------------------------
 * Theme dependency notice
 *
 * The room blocks (`cwc/room-info`, `cwc/room-gallery`,
 * `cwc/other-rooms`) and the `single-accommodation.html` template
 * live in the child-cwcwake theme. The plugin's data layer works
 * with any theme, but a different theme would not know how to
 * render an accommodation. Surface a one-time admin notice when
 * the expected theme is not active so editors don't wonder why
 * room pages render blank.
 *
 * Intentionally non-blocking: the plugin still works, the notice
 * just steers integrators toward the matching theme.
 * --------------------------------------------------------- */

/**
 * Render the theme-mismatch admin notice.
 *
 * Only shown to users with `manage_options` (admin-level) on
 * admin pages, never on the front-end. The notice is dismissible
 * so it doesn't keep nagging editors who consciously chose a
 * different theme.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_acc_render_theme_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$expected = 'child-cwcwake';
	$theme    = wp_get_theme();
	if ( $theme->get_stylesheet() === $expected || $theme->get_template() === $expected ) {
		return;
	}

	?>
	<div class="notice notice-warning is-dismissible">
		<p>
			<strong><?php esc_html_e( 'CWC Accommodations:', 'cwc-accommodations' ); ?></strong>
			<?php
			printf(
				/* translators: %s: expected theme slug. */
				esc_html__( 'The room data layer is active, but the matching theme (%s) is not. Single-room pages will render blank until the theme is activated.', 'cwc-accommodations' ),
				'<code>child-cwcwake</code>'
			);
			?>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'cwc_acc_render_theme_notice' );
