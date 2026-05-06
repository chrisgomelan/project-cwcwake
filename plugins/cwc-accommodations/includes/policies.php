<?php
/**
 * CWC Wake — Global Policies admin page + storage.
 *
 * Spec'd in `room-management-transition.md` § 2.C: a single
 * `cwc_global_policies` option holds a JSON-encoded array of policy
 * rows (icon slug + name + description) that every room page reads
 * from. Update it once, all rooms reflect the change — no per-room
 * editing required for shared house rules.
 *
 * Surface area:
 *
 *   - Settings menu: "Global Policies" (under Settings → Global Policies).
 *   - Storage: WordPress option `cwc_global_policies`, JSON-encoded
 *     array of `{ icon, name, description }` rows.
 *   - Public read API: `cwc_get_global_policies()` returns the
 *     decoded array; the room-info block calls it as the meta-level
 *     fallback after the per-block attribute, before giving up.
 *   - First-run defaults: when the option is empty we hand back the
 *     same seven standard policies the legacy `room-detail-pages.php`
 *     catalogue used, so existing rooms render identically out of
 *     the box.
 *
 * Storage is JSON (not a serialized PHP array) on purpose: smaller
 * on disk, easier to inspect with wp-cli `option get`, and trivial
 * to import/export between environments.
 *
 * @package CWC_Accommodations
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------------------------------------------------
 * Public read API
 * --------------------------------------------------------- */

/**
 * Read the current global policies array.
 *
 * Decodes the JSON option, validates each row against the policy
 * icon catalogue, and falls back to the standard seven defaults
 * when the option is empty / malformed. Returning a clean array
 * here means callers never have to defensively check shape.
 *
 * @since 1.0.0
 *
 * @return array<int,array{icon:string,name:string,description:string}>
 */
function cwc_get_global_policies() {
	$raw = get_option( 'cwc_global_policies', '' );

	$rows = [];
	if ( is_string( $raw ) && '' !== $raw ) {
		$decoded = json_decode( $raw, true );
		if ( is_array( $decoded ) ) {
			$rows = $decoded;
		}
	} elseif ( is_array( $raw ) ) {
		// Tolerate a non-JSON value if anyone wrote one directly.
		$rows = $raw;
	}

	$rows = array_values( array_filter( array_map( 'cwc_normalize_policy_row', $rows ) ) );

	if ( empty( $rows ) ) {
		return cwc_default_global_policies();
	}

	return $rows;
}

/**
 * Coerce one raw policy row into the canonical shape.
 *
 * Drops rows missing both name and description (an empty entry is
 * never useful) and trims away any unknown keys so callers can
 * `wp_json_encode` the array without leaking junk.
 *
 * @since 1.0.0
 *
 * @param mixed $row Raw row (expected array).
 * @return array{icon:string,name:string,description:string}|null Null when the row is unusable.
 */
function cwc_normalize_policy_row( $row ) {
	if ( ! is_array( $row ) ) {
		return null;
	}

	$name = isset( $row['name'] ) ? sanitize_text_field( (string) $row['name'] ) : '';
	$desc = isset( $row['description'] ) ? wp_kses_post( (string) $row['description'] ) : '';
	$icon = isset( $row['icon'] ) ? sanitize_key( (string) $row['icon'] ) : '';

	if ( '' === $name && '' === $desc ) {
		return null;
	}

	$catalogue = cwc_policy_icon_catalogue();
	if ( '' !== $icon && ! isset( $catalogue[ $icon ] ) ) {
		// Unknown slug — keep the row but blank the icon so it renders as text-only.
		$icon = '';
	}

	return [
		'icon'        => $icon,
		'name'        => $name,
		'description' => $desc,
	];
}

/**
 * The seven standard policies the legacy catalogue shipped with.
 *
 * Used as the first-paint default before an editor saves the
 * settings page even once. Mirrors `inc/room-detail-pages.php`
 * verbatim so existing room pages don't visually regress when they
 * stop reading from the legacy catalogue.
 *
 * @since 1.0.0
 *
 * @return array<int,array{icon:string,name:string,description:string}>
 */
function cwc_default_global_policies() {
	return [
		[
			'icon'        => 'check-in',
			'name'        => 'Check-in',
			'description' => 'From 02:00 PM to 09:00 PM',
		],
		[
			'icon'        => 'check-out',
			'name'        => 'Check-out',
			'description' => 'Until 12:00 PM',
		],
		[
			'icon'        => 'breakfast',
			'name'        => 'Breakfast',
			'description' => 'Breakfast Available (may be included in selected rooms).',
		],
		[
			'icon'        => 'reception',
			'name'        => 'Reception Hours',
			'description' => 'Open until 09:00 PM',
		],
		[
			'icon'        => 'children',
			'name'        => 'Children and beds',
			'description' => 'Infants (0–3 yrs): free. Children (4–8 yrs): extra bed charge applies. Guests (9+): considered adults.',
		],
		[
			'icon'        => 'no-age',
			'name'        => 'No age restriction',
			'description' => 'Guests of all ages are welcome.',
		],
		[
			'icon'        => 'smoking',
			'name'        => 'Smoking',
			'description' => 'Smoking is not allowed.',
		],
	];
}

/* ---------------------------------------------------------
 * Settings menu + screen
 * --------------------------------------------------------- */

/**
 * Add the Global Policies item as a submenu of the Accommodations
 * post-type menu.
 *
 * Co-locating it with All Rooms / Add New means an editor only has
 * to learn one place in the sidebar to manage the entire room
 * system — `Accommodations → All Rooms / Add New / Global Policies`.
 *
 * Parent slug for a CPT submenu is the `edit.php?post_type=<slug>`
 * URL — that's the URL of the CPT's "All Rooms" screen and the
 * thing WP_List_Table uses internally to anchor child menus. Cap
 * stays `manage_options` because policies affect the whole site.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_register_global_policies_menu() {
	add_submenu_page(
		'edit.php?post_type=accommodation',
		__( 'Global Policies', 'cwc-accommodations' ),
		__( 'Global Policies', 'cwc-accommodations' ),
		'manage_options',
		'cwc-global-policies',
		'cwc_render_global_policies_page'
	);
}
add_action( 'admin_menu', 'cwc_register_global_policies_menu' );

/**
 * Persist a save submission from the policies admin page.
 *
 * Bound to `admin_post_` rather than the Settings API so the form
 * can use a simple repeater without registering a `register_setting`
 * + `add_settings_section` + `add_settings_field` boilerplate trio.
 * The handler validates the nonce + capability, normalizes each
 * row, and redirects back to the page with a `?updated=1` flag the
 * renderer surfaces as an admin notice.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_handle_global_policies_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to update global policies.', 'cwc-accommodations' ), 403 );
	}

	check_admin_referer( 'cwc_global_policies_save', 'cwc_global_policies_nonce' );

	$rows = [];

	if ( isset( $_POST['policies'] ) && is_array( $_POST['policies'] ) ) {
		$raw_rows = wp_unslash( $_POST['policies'] );
		foreach ( $raw_rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$normalized = cwc_normalize_policy_row( $row );
			if ( $normalized ) {
				$rows[] = $normalized;
			}
		}
	}

	update_option( 'cwc_global_policies', wp_json_encode( $rows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );

	/*
	 * Redirect back to the page where the form lives — now a child
	 * of the Accommodations CPT menu, not Settings. The sub-page
	 * registered with `add_submenu_page` is reachable at
	 * `edit.php?post_type=accommodation&page=cwc-global-policies`.
	 */
	wp_safe_redirect(
		add_query_arg(
			[
				'post_type' => 'accommodation',
				'page'      => 'cwc-global-policies',
				'updated'   => '1',
			],
			admin_url( 'edit.php' )
		)
	);
	exit;
}
add_action( 'admin_post_cwc_global_policies_save', 'cwc_handle_global_policies_save' );

/**
 * Render the Global Policies settings page.
 *
 * Repeater UI: each row is icon dropdown + name input + textarea +
 * remove button, plus an "Add Policy" button that clones a hidden
 * template row. Tiny vanilla-JS keeps the bundle dependency-free.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_render_global_policies_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to view this page.', 'cwc-accommodations' ), 403 );
	}

	$policies      = cwc_get_global_policies();
	$icon_options  = cwc_policy_icon_catalogue();
	$updated_flag  = isset( $_GET['updated'] ) && '1' === $_GET['updated'];
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Global Policies', 'cwc-accommodations' ); ?></h1>

		<p class="description" style="max-width:60ch;">
			<?php esc_html_e( 'These policies appear in the "Policies" section of every Accommodation page. Updating them here propagates the change site-wide on the next page load.', 'cwc-accommodations' ); ?>
		</p>

		<?php if ( $updated_flag ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Global policies updated.', 'cwc-accommodations' ); ?></p>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="cwc_global_policies_save" />
			<?php wp_nonce_field( 'cwc_global_policies_save', 'cwc_global_policies_nonce' ); ?>

			<div id="cwc-policies-rows" style="display:flex;flex-direction:column;gap:1rem;margin:1.5rem 0;">
				<?php foreach ( $policies as $index => $row ) :
					cwc_render_global_policy_row( $index, $row, $icon_options );
				endforeach; ?>
			</div>

			<p>
				<button type="button" class="button" id="cwc-policies-add">
					<?php esc_html_e( '+ Add Policy', 'cwc-accommodations' ); ?>
				</button>
			</p>

			<?php submit_button( __( 'Save Policies', 'cwc-accommodations' ) ); ?>
		</form>

		<template id="cwc-policies-row-template">
			<?php cwc_render_global_policy_row( '__INDEX__', [ 'icon' => '', 'name' => '', 'description' => '' ], $icon_options ); ?>
		</template>

		<script>
			( function () {
				const list     = document.getElementById( 'cwc-policies-rows' );
				const template = document.getElementById( 'cwc-policies-row-template' );
				const addBtn   = document.getElementById( 'cwc-policies-add' );

				if ( ! list || ! template || ! addBtn ) {
					return;
				}

				let counter = list.querySelectorAll( '[data-policy-row]' ).length;

				addBtn.addEventListener( 'click', () => {
					const html = template.innerHTML.replace( /__INDEX__/g, String( counter++ ) );
					const wrapper = document.createElement( 'div' );
					wrapper.innerHTML = html.trim();
					const row = wrapper.firstElementChild;
					if ( row ) {
						list.appendChild( row );
					}
				} );

				list.addEventListener( 'click', ( event ) => {
					const target = event.target;
					if ( target instanceof HTMLElement && target.matches( '[data-policy-remove]' ) ) {
						event.preventDefault();
						target.closest( '[data-policy-row]' )?.remove();
					}
				} );
			} )();
		</script>
	</div>
	<?php
}

/**
 * Render a single repeater row (used both inline and inside the template).
 *
 * Extracted so the markup is identical between the seeded rows and
 * the JS-cloned blank row — any visual tweak only has to be made
 * once. The numeric `$index` is interpolated into the input names
 * so PHP receives a clean indexed array on submit.
 *
 * @since 1.0.0
 *
 * @param int|string                                                  $index        Index used in `name="policies[$index][...]"`.
 * @param array{icon:string,name:string,description:string}           $row          Row data to pre-fill.
 * @param array<string,array{label:string,icon:string}>               $icon_options Icon catalogue for the dropdown.
 * @return void
 */
function cwc_render_global_policy_row( $index, array $row, array $icon_options ) {
	$icon = $row['icon']        ?? '';
	$name = $row['name']        ?? '';
	$desc = $row['description'] ?? '';
	?>
	<div class="cwc-policy-row" data-policy-row
		style="display:grid;grid-template-columns:200px 1fr 80px;gap:1rem;align-items:start;background:#fff;border:1px solid #c3c4c7;border-radius:6px;padding:1rem;">
		<div>
			<label style="font-weight:600;display:block;margin-bottom:.25rem;"><?php esc_html_e( 'Icon', 'cwc-accommodations' ); ?></label>
			<select name="policies[<?php echo esc_attr( (string) $index ); ?>][icon]" class="widefat">
				<option value=""><?php esc_html_e( '— No Icon —', 'cwc-accommodations' ); ?></option>
				<?php foreach ( $icon_options as $slug => $option ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $icon, $slug ); ?>>
						<?php echo esc_html( $option['label'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<label style="font-weight:600;display:block;margin-top:.75rem;margin-bottom:.25rem;"><?php esc_html_e( 'Name', 'cwc-accommodations' ); ?></label>
			<input type="text" name="policies[<?php echo esc_attr( (string) $index ); ?>][name]"
				value="<?php echo esc_attr( $name ); ?>" class="widefat" />
		</div>

		<div>
			<label style="font-weight:600;display:block;margin-bottom:.25rem;"><?php esc_html_e( 'Description', 'cwc-accommodations' ); ?></label>
			<textarea name="policies[<?php echo esc_attr( (string) $index ); ?>][description]"
				class="widefat" rows="4"><?php echo esc_textarea( $desc ); ?></textarea>
			<p class="description"><?php esc_html_e( 'Plain text or basic HTML. Appears under the policy name on every room page.', 'cwc-accommodations' ); ?></p>
		</div>

		<div>
			<button type="button" class="button button-link-delete" data-policy-remove>
				<?php esc_html_e( 'Remove', 'cwc-accommodations' ); ?>
			</button>
		</div>
	</div>
	<?php
}
