<?php
/**
 * CWC Wake — Accommodations Settings Hub.
 *
 * Manages the shared Icon Library and the Dynamic Amenities list.
 *
 * @package CWC_Accommodations
 * @since   1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Settings Hub submenu.
 */
function cwc_register_accommodations_settings_menu() {
	add_submenu_page(
		'edit.php?post_type=accommodation',
		__( 'Amenities & Icons', 'cwc-accommodations' ),
		__( 'Amenities & Icons', 'cwc-accommodations' ),
		'manage_options',
		'cwc-amenity-settings',
		'cwc_render_accommodations_settings_page'
	);

	add_submenu_page(
		'edit.php?post_type=accommodation',
		__( 'Inclusions', 'cwc-accommodations' ),
		__( 'Inclusions', 'cwc-accommodations' ),
		'manage_options',
		'cwc-inclusion-settings',
		'cwc_render_accommodations_inclusions_page'
	);
}
add_action( 'admin_menu', 'cwc_register_accommodations_settings_menu' );

/**
 * Handle saving of Amenities and Icon Pool.
 */
function cwc_handle_accommodations_settings_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Unauthorized.', 'cwc-accommodations' ) );
	}

	check_admin_referer( 'cwc_acc_settings_save', 'cwc_acc_settings_nonce' );

	// 1. Save Icon Pool
	$pool = [];
	if ( isset( $_POST['icon_pool'] ) && is_array( $_POST['icon_pool'] ) ) {
		foreach ( $_POST['icon_pool'] as $row ) {
			$slug = sanitize_key( $row['slug'] ?? '' );
			$val  = sanitize_text_field( wp_unslash( $row['value'] ?? '' ) );
			if ( $slug && $val ) {
				$pool[ $slug ] = $val;
			}
		}
		update_option( 'cwc_icon_pool', $pool );
	}

	// 2. Save Amenities
	$amenities = [];
	if ( isset( $_POST['amenities'] ) && is_array( $_POST['amenities'] ) ) {
		foreach ( $_POST['amenities'] as $row ) {
			$slug  = sanitize_key( $row['slug'] ?? '' );
			$label = sanitize_text_field( wp_unslash( $row['label'] ?? '' ) );
			$icon  = sanitize_key( $row['icon'] ?? '' );
			if ( $slug && $label ) {
				$amenities[ $slug ] = [ 'label' => $label, 'icon' => $icon ];
			}
		}
		update_option( 'cwc_dynamic_amenities', $amenities );
	}

	// 3. Save Inclusions
	$inclusions = [];
	if ( isset( $_POST['inclusions'] ) && is_array( $_POST['inclusions'] ) ) {
		foreach ( $_POST['inclusions'] as $row ) {
			$slug  = sanitize_key( $row['slug'] ?? '' );
			$label = sanitize_text_field( wp_unslash( $row['label'] ?? '' ) );
			if ( $slug && $label ) {
				$inclusions[ $slug ] = [ 'label' => $label ];
			}
		}
		update_option( 'cwc_dynamic_inclusions', $inclusions );
	}

	// 4. Save Bed Types
	$beds = [];
	if ( isset( $_POST['bed_types'] ) && is_array( $_POST['bed_types'] ) ) {
		foreach ( $_POST['bed_types'] as $row ) {
			$slug  = sanitize_key( $row['slug'] ?? '' );
			$label = sanitize_text_field( wp_unslash( $row['label'] ?? '' ) );
			$icon  = sanitize_key( $row['icon'] ?? '' );
			if ( $slug && $label ) {
				$beds[ $slug ] = [ 'label' => $label, 'icon' => $icon ];
			}
		}
		update_option( 'cwc_dynamic_beds', $beds );
	}

	$seeded = 0;
	if ( isset( $_POST['cwc_seed_blogs'] ) ) {
		$seeded = cwc_seed_blog_posts();
	}

	wp_safe_redirect( add_query_arg( [ 
		'post_type' => 'accommodation', 
		'page'      => $_POST['page'] ?? 'cwc-amenity-settings', 
		'updated'   => '1',
		'seeded'    => $seeded
	], admin_url( 'edit.php' ) ) );
	exit;
}
add_action( 'admin_post_cwc_acc_settings_save', 'cwc_handle_accommodations_settings_save' );

/**
 * Render the settings page.
 */
function cwc_render_accommodations_settings_page() {
	wp_enqueue_media();
	// Handle Seeding
	$pool      = get_option( 'cwc_icon_pool', [] );
	$amenities = get_option( 'cwc_dynamic_amenities', [] );
	$bed_types = get_option( 'cwc_dynamic_beds', [] );
	$updated   = isset( $_GET['updated'] );
	$seeded    = isset( $_GET['seeded'] ) ? intval( $_GET['seeded'] ) : -1;

	// Ensure legacy icons are visible in the pool if it's empty
	if ( empty( $pool ) ) {
		$pool = [
			'wifi' => 'free-wifi.svg', 'parking' => 'free-parking.svg', 'pool' => 'swimming-pool.svg',
			'air' => 'air-conditioning.svg', 'garden' => 'garden&terrace.svg', 'bar' => 'bar.svg',
			'coffee' => 'coffee-shop.svg', 'smoke-free' => 'smoke-free.svg'
		];
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Amenities & Icon Library', 'cwc-accommodations' ); ?></h1>

		<?php if ( $updated ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'cwc-accommodations' ); ?></p></div>
		<?php endif; ?>

		<?php if ( $seeded >= 0 ) : ?>
			<div class="notice notice-info is-dismissible"><p><?php printf( esc_html__( '%d sample blog posts created/checked.', 'cwc-accommodations' ), $seeded ); ?></p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="cwc_acc_settings_save" />
			<?php wp_nonce_field( 'cwc_acc_settings_save', 'cwc_acc_settings_nonce' ); ?>

			<h2><?php esc_html_e( '1. Icon Library (The Pool)', 'cwc-accommodations' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Define slugs and upload SVGs here. These icons can then be used for both Amenities and Policies.', 'cwc-accommodations' ); ?></p>
			
			<div id="cwc-icon-pool-rows" style="display:flex;flex-direction:column;gap:.5rem;margin:1rem 0;">
				<?php $i = 0; foreach ( $pool as $slug => $val ) : 
					$icon_url = is_numeric($val) ? wp_get_attachment_url($val) : (get_stylesheet_directory_uri() . '/assets/images/' . $val);
				?>
					<div class="cwc-row" style="display:grid;grid-template-columns:150px 60px 1fr 100px 50px;gap:1rem;background:#fff;padding:.5rem;border:1px solid #ccd0d4;align-items:center;">
						<input type="text" name="icon_pool[<?php echo $i; ?>][slug]" value="<?php echo esc_attr( $slug ); ?>" placeholder="slug" class="widefat" />
						<div class="cwc-preview-box" style="width:40px;height:40px;background:#f0f0f1;display:flex;align-items:center;justify-content:center;border:1px solid #dcdcde;border-radius:4px;">
							<img src="<?php echo esc_url($icon_url); ?>" style="max-width:24px;max-height:24px;display:block;" />
						</div>
						<span class="description" style="font-family:monospace;"><?php echo esc_html( is_numeric($val) ? "ID: $val" : $val ); ?></span>
						<input type="hidden" name="icon_pool[<?php echo $i; ?>][value]" value="<?php echo esc_attr( $val ); ?>" class="cwc-icon-val" />
						<button type="button" class="button cwc-icon-pick"><?php esc_html_e( 'Change', 'cwc-accommodations' ); ?></button>
						<button type="button" class="button-link-delete cwc-remove-row">×</button>
					</div>
				<?php $i++; endforeach; ?>
			</div>
			<button type="button" class="button" id="cwc-add-icon"><?php esc_html_e( '+ Add Icon to Pool', 'cwc-accommodations' ); ?></button>

			<hr style="margin:2rem 0;" />

			<h2><?php esc_html_e( '2. Amenities Catalogue', 'cwc-accommodations' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Manage the list of amenities that appear as checkboxes on the Room editor.', 'cwc-accommodations' ); ?></p>

			<div id="cwc-amenity-rows" style="display:flex;flex-direction:column;gap:.5rem;margin:1rem 0;">
				<?php $j = 0; foreach ( $amenities as $slug => $data ) : ?>
					<div class="cwc-row" style="display:grid;grid-template-columns:150px 1fr 150px 50px;gap:1rem;background:#fff;padding:.5rem;border:1px solid #ccd0d4;align-items:center;">
						<input type="text" name="amenities[<?php echo $j; ?>][slug]" value="<?php echo esc_attr( $slug ); ?>" placeholder="slug" class="widefat" />
						<input type="text" name="amenities[<?php echo $j; ?>][label]" value="<?php echo esc_attr( $data['label'] ); ?>" placeholder="Label (e.g. Free WiFi)" class="widefat" />
						<select name="amenities[<?php echo $j; ?>][icon]" class="widefat">
							<?php foreach ( $pool as $p_slug => $p_val ) : ?>
								<option value="<?php echo esc_attr( $p_slug ); ?>" <?php selected( $data['icon'], $p_slug ); ?>><?php echo esc_html( $p_slug ); ?></option>
							<?php endforeach; ?>
						</select>
						<button type="button" class="button-link-delete cwc-remove-row">×</button>
					</div>
				<?php $j++; endforeach; ?>
			</div>
			<button type="button" class="button" id="cwc-add-amenity"><?php esc_html_e( '+ Add Amenity', 'cwc-accommodations' ); ?></button>

			<hr style="margin:2rem 0;" />

			<h2><?php esc_html_e( '3. Bed Types Catalogue', 'cwc-accommodations' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Manage the bed types that can be assigned to rooms.', 'cwc-accommodations' ); ?></p>

			<div id="cwc-bed-type-rows" style="display:flex;flex-direction:column;gap:.5rem;margin:1rem 0;">
				<?php $m = 0; foreach ( $bed_types as $slug => $data ) : ?>
					<div class="cwc-row" style="display:grid;grid-template-columns:150px 1fr 150px 50px;gap:1rem;background:#fff;padding:.5rem;border:1px solid #ccd0d4;align-items:center;">
						<input type="text" name="bed_types[<?php echo $m; ?>][slug]" value="<?php echo esc_attr( $slug ); ?>" placeholder="slug" class="widefat" />
						<input type="text" name="bed_types[<?php echo $m; ?>][label]" value="<?php echo esc_attr( $data['label'] ); ?>" placeholder="Label (e.g. Queen Bed)" class="widefat" />
						<select name="bed_types[<?php echo $m; ?>][icon]" class="widefat">
							<?php foreach ( $pool as $p_slug => $p_val ) : ?>
								<option value="<?php echo esc_attr( $p_slug ); ?>" <?php selected( $data['icon'], $p_slug ); ?>><?php echo esc_html( $p_slug ); ?></option>
							<?php endforeach; ?>
						</select>
						<button type="button" class="button-link-delete cwc-remove-row">×</button>
					</div>
				<?php $m++; endforeach; ?>
			</div>
			<button type="button" class="button" id="cwc-add-bed-type"><?php esc_html_e( '+ Add Bed Type', 'cwc-accommodations' ); ?></button>

			<div style="margin-top:2rem; display:flex; align-items:center; gap:10px;">
				<?php submit_button( __( 'Save All Settings', 'cwc-accommodations' ), 'primary', 'submit', false ); ?>
				<button type="submit" name="cwc_seed_blogs" class="button"><?php esc_html_e( 'Seed Sample Blog Posts', 'cwc-accommodations' ); ?></button>
			</div>
		</form>

		<script>
		( function () {
			let frame;
			let activeBtn;

			// Event delegation for "Change" / "Pick Icon" buttons
			document.addEventListener( 'click', ( event ) => {
				const target = event.target;
				if ( ! target.matches( '.cwc-icon-pick' ) ) {
					return;
				}

				event.preventDefault();
				activeBtn = target;

				if ( frame ) {
					frame.open();
					return;
				}

				frame = wp.media( {
					title: <?php echo wp_json_encode( __( 'Select Icon', 'cwc-accommodations' ) ); ?>,
					button: { text: <?php echo wp_json_encode( __( 'Use Icon', 'cwc-accommodations' ) ); ?> },
					multiple: false,
				} );

				frame.on( 'select', () => {
					const attachment = frame.state().get( 'selection' ).first().toJSON();
					const $row      = activeBtn.closest( '.cwc-row' );
					const $input    = $row.querySelector( '.cwc-icon-val' );
					const $preview  = $row.querySelector( '.cwc-preview-box' );
					const $desc     = $row.querySelector( 'span.description' );

					if ( $input ) {
						$input.value = attachment.id;
					}
					if ( $preview ) {
						$preview.innerHTML = `<img src="${attachment.url}" style="max-width:24px;max-height:24px;display:block;" />`;
					}
					if ( $desc ) {
						$desc.textContent = `ID: ${attachment.id}`;
					}
				} );

				frame.open();
			} );

			// Add Icon to Pool
			const addIconBtn = document.getElementById( 'cwc-add-icon' );
			if ( addIconBtn ) {
				addIconBtn.addEventListener( 'click', () => {
					const list    = document.getElementById( 'cwc-icon-pool-rows' );
					const counter = list.querySelectorAll( '.cwc-row' ).length;
					const wrapper = document.createElement( 'div' );
					
					wrapper.className = 'cwc-row';
					wrapper.style.cssText = 'display:grid;grid-template-columns:150px 60px 1fr 100px 50px;gap:1rem;background:#fff;padding:.5rem;border:1px solid #ccd0d4;align-items:center;';
					wrapper.innerHTML = `
						<input type="text" name="icon_pool[${counter}][slug]" placeholder="slug" class="widefat" />
						<div class="cwc-preview-box" style="width:40px;height:40px;background:#f0f0f1;display:flex;align-items:center;justify-content:center;border:1px solid #dcdcde;border-radius:4px;"></div>
						<span class="description" style="font-family:monospace;">New Icon</span>
						<input type="hidden" name="icon_pool[${counter}][value]" class="cwc-icon-val" />
						<button type="button" class="button cwc-icon-pick">Pick Icon</button>
						<button type="button" class="button-link-delete cwc-remove-row">×</button>
					`;
					list.appendChild( wrapper );
				} );
			}

			// Add Amenity
			const addAmenityBtn = document.getElementById( 'cwc-add-amenity' );
			if ( addAmenityBtn ) {
				addAmenityBtn.addEventListener( 'click', () => {
					const list    = document.getElementById( 'cwc-amenity-rows' );
					const counter = list.querySelectorAll( '.cwc-row' ).length;
					const pool    = document.querySelectorAll( '#cwc-icon-pool-rows input[name*="[slug]"]' );
					
					let options = '';
					pool.forEach( ( input ) => {
						const slug = input.value;
						if ( slug ) options += `<option value="${slug}">${slug}</option>`;
					} );

					const wrapper = document.createElement( 'div' );
					wrapper.className = 'cwc-row';
					wrapper.style.cssText = 'display:grid;grid-template-columns:150px 1fr 150px 50px;gap:1rem;background:#fff;padding:.5rem;border:1px solid #ccd0d4;align-items:center;';
					wrapper.innerHTML = `
						<input type="text" name="amenities[${counter}][slug]" placeholder="slug" class="widefat" />
						<input type="text" name="amenities[${counter}][label]" placeholder="Label" class="widefat" />
						<select name="amenities[${counter}][icon]" class="widefat">${options}</select>
						<button type="button" class="button-link-delete cwc-remove-row">×</button>
					`;
					list.appendChild( wrapper );
				} );
			}
			
			// Add Bed Type
			const addBedTypeBtn = document.getElementById( 'cwc-add-bed-type' );
			if ( addBedTypeBtn ) {
				addBedTypeBtn.addEventListener( 'click', () => {
					const list    = document.getElementById( 'cwc-bed-type-rows' );
					const counter = list.querySelectorAll( '.cwc-row' ).length;
					const pool    = document.querySelectorAll( '#cwc-icon-pool-rows input[name*="[slug]"]' );
					
					let options = '';
					pool.forEach( ( input ) => {
						const slug = input.value;
						if ( slug ) options += `<option value="${slug}">${slug}</option>`;
					} );

					const wrapper = document.createElement( 'div' );
					wrapper.className = 'cwc-row';
					wrapper.style.cssText = 'display:grid;grid-template-columns:150px 1fr 150px 50px;gap:1rem;background:#fff;padding:.5rem;border:1px solid #ccd0d4;align-items:center;';
					wrapper.innerHTML = `
						<input type="text" name="bed_types[${counter}][slug]" placeholder="slug" class="widefat" />
						<input type="text" name="bed_types[${counter}][label]" placeholder="Label" class="widefat" />
						<select name="bed_types[${counter}][icon]" class="widefat">${options}</select>
						<button type="button" class="button-link-delete cwc-remove-row">×</button>
					`;
					list.appendChild( wrapper );
				} );
			}

			// Remove Row
			document.addEventListener( 'click', ( event ) => {
				if ( event.target.matches( '.cwc-remove-row' ) ) {
					event.target.closest( '.cwc-row' )?.remove();
				}
			} );
		} )();
		</script>
	</div>
	<?php
}

/**
 * Render the Inclusions settings page.
 */
function cwc_render_accommodations_inclusions_page() {
	$inclusions = get_option( 'cwc_dynamic_inclusions', [] );
	$updated    = isset( $_GET['updated'] );

	// Default starting list if empty
	if ( empty( $inclusions ) ) {
		$inclusions = [
			'wakeboard-4'    => [ 'label' => 'Free Wakeboard for 4 Guests' ],
			'airport-pick'   => [ 'label' => 'Free Airport Pick Up in Naga Airport' ],
			'golf-coach'     => [ 'label' => 'Free 18 holes Gold maximum of 4 Guests or One hour with Golf Coach' ],
			'shuttle-naga'   => [ 'label' => 'Free Shuttle to Naga City' ],
			'skate-park'     => [ 'label' => 'Free Use of Skate Park' ],
			'bike-track'     => [ 'label' => 'Free Use of Bike Track' ],
			'playground'     => [ 'label' => 'Free Use of Children\'s Playground' ],
			'basketball'     => [ 'label' => 'Free Use of Outdoor Basketball Court' ],
		];
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Room Inclusions', 'cwc-accommodations' ); ?></h1>

		<?php if ( $updated ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Inclusions saved.', 'cwc-accommodations' ); ?></p></div>
		<?php endif; ?>

		<p class="description"><?php esc_html_e( 'Define the inclusions that can be checked on each room. These appear as text pills on the frontend.', 'cwc-accommodations' ); ?></p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="cwc_acc_settings_save" />
			<input type="hidden" name="page" value="cwc-inclusion-settings" />
			<?php wp_nonce_field( 'cwc_acc_settings_save', 'cwc_acc_settings_nonce' ); ?>

			<div id="cwc-inclusion-rows" style="display:flex;flex-direction:column;gap:.5rem;margin:1rem 0;">
				<?php $k = 0; foreach ( $inclusions as $slug => $data ) : ?>
					<div class="cwc-row" style="display:grid;grid-template-columns:200px 1fr 50px;gap:1rem;background:#fff;padding:.5rem;border:1px solid #ccd0d4;align-items:center;">
						<input type="text" name="inclusions[<?php echo $k; ?>][slug]" value="<?php echo esc_attr( $slug ); ?>" placeholder="slug (e.g. free-wifi)" class="widefat" />
						<input type="text" name="inclusions[<?php echo $k; ?>][label]" value="<?php echo esc_attr( $data['label'] ); ?>" placeholder="Display Label" class="widefat" />
						<button type="button" class="button-link-delete cwc-remove-row">×</button>
					</div>
				<?php $k++; endforeach; ?>
			</div>
			
			<button type="button" class="button" id="cwc-add-inclusion"><?php esc_html_e( '+ Add New Inclusion', 'cwc-accommodations' ); ?></button>

			<div style="margin-top:2rem;">
				<?php submit_button( __( 'Save Inclusions', 'cwc-accommodations' ) ); ?>
			</div>
		</form>

		<script>
		( function () {
			const addBtn = document.getElementById( 'cwc-add-inclusion' );
			if ( addBtn ) {
				addBtn.addEventListener( 'click', () => {
					const list    = document.getElementById( 'cwc-inclusion-rows' );
					const counter = list.querySelectorAll( '.cwc-row' ).length;
					const wrapper = document.createElement( 'div' );
					wrapper.className = 'cwc-row';
					wrapper.style.cssText = 'display:grid;grid-template-columns:200px 1fr 50px;gap:1rem;background:#fff;padding:.5rem;border:1px solid #ccd0d4;align-items:center;';
					wrapper.innerHTML = `
						<input type="text" name="inclusions[${counter}][slug]" placeholder="slug" class="widefat" />
						<input type="text" name="inclusions[${counter}][label]" placeholder="Display Label" class="widefat" />
						<button type="button" class="button-link-delete cwc-remove-row">×</button>
					`;
					list.appendChild( wrapper );
				} );
			}

			document.addEventListener( 'click', ( event ) => {
				if ( event.target.matches( '.cwc-remove-row' ) ) {
					event.target.closest( '.cwc-row' )?.remove();
				}
			} );
		} )();
		</script>
	</div>
	<?php
}
