<?php
/**
 * CWC Wake — Accommodation meta boxes.
 *
 * Renders the admin UI editors use to fill the six per-room meta
 * fields. Three meta boxes are added so related fields cluster
 * visually:
 *
 *   1. "Pricing & Availability"  — price, sub-label, capacity,
 *                                  availability dropdown.
 *   2. "Amenities"               — checkbox list driven by
 *                                  `cwc_amenity_catalogue()`.
 *   3. "Gallery"                 — comma-separated attachment IDs
 *                                  + a "Pick from media library"
 *                                  button that uses `wp.media`.
 *
 * The classic add_meta_box flow (rather than a Gutenberg sidebar)
 * is intentional: it keeps the implementation entirely in PHP, ships
 * with no JS bundling step, and the resulting boxes still appear
 * inside the block editor's lower meta-box area. We only enqueue
 * `wp.media` for the gallery picker — everything else is plain HTML.
 *
 * Each box uses one shared nonce (`cwc_accommodation_meta_nonce`)
 * and one shared save handler (`cwc_save_accommodation_meta`) so we
 * don't have three nonces / three save callbacks to keep in sync.
 *
 * @package CWC_Accommodations
 * @since   1.0.0
 */

if (!defined('ABSPATH')) {
	exit;
}

/* ---------------------------------------------------------
 * Box registration
 * --------------------------------------------------------- */

/**
 * Register the three meta boxes on the accommodation editor screen.
 *
 * Hooked at `add_meta_boxes_accommodation` so we only run when WP
 * is actually building the accommodation editor — cheaper than
 * `add_meta_boxes` + a `if ( 'accommodation' !== $post_type )` guard.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_register_accommodation_meta_boxes()
{
	add_meta_box(
		'cwc_accommodation_pricing',
		__('Pricing & Availability', 'cwc-accommodations'),
		'cwc_render_accommodation_pricing_box',
		'accommodation',
		'side',
		'default'
	);

	add_meta_box(
		'cwc_accommodation_amenities',
		__('Amenities', 'cwc-accommodations'),
		'cwc_render_accommodation_amenities_box',
		'accommodation',
		'normal',
		'default'
	);

	add_meta_box(
		'cwc_accommodation_gallery',
		__('Room Gallery', 'cwc-accommodations'),
		'cwc_render_accommodation_gallery_box',
		'accommodation',
		'normal',
		'default'
	);

	add_meta_box(
		'cwc_accommodation_inclusions',
		__('Inclusions', 'cwc-accommodations'),
		'cwc_render_accommodation_inclusions_box',
		'accommodation',
		'normal',
		'default'
	);

	add_meta_box(
		'cwc_accommodation_beds',
		__('Beds Configuration', 'cwc-accommodations'),
		'cwc_render_accommodation_beds_box',
		'accommodation',
		'side',
		'default'
	);
}
add_action('add_meta_boxes_accommodation', 'cwc_register_accommodation_meta_boxes');

/**
 * Register the Event Details meta box on the post editor screen.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_register_post_event_meta_box()
{
	add_meta_box(
		'cwc_post_event_details',
		__('Event Details', 'cwc-accommodations'),
		'cwc_render_post_event_details_box',
		'post',
		'side',
		'default'
	);
}
add_action('add_meta_boxes_post', 'cwc_register_post_event_meta_box');

/* ---------------------------------------------------------
 * Box renderers
 * --------------------------------------------------------- */

/**
 * Render the Pricing & Availability box.
 *
 * Sidebar box (compact form). Availability is a `<select>` with the
 * same three options the meta sanitizer accepts; the help text under
 * each option explains the front-end behaviour so editors don't
 * need to read the spec to know what each value does.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Current post being edited.
 * @return void
 */
function cwc_render_accommodation_pricing_box($post)
{
	wp_nonce_field('cwc_accommodation_meta', 'cwc_accommodation_meta_nonce');

	$price = (string) get_post_meta($post->ID, '_cwc_price', true);
	$price_sub = (string) get_post_meta($post->ID, '_cwc_price_sub', true);
	$capacity = (int) get_post_meta($post->ID, '_cwc_capacity', true);
	$availability = (string) get_post_meta($post->ID, '_cwc_availability', true);
	if ('' === $availability) {
		$availability = 'available';
	}

	$availability_options = [
		'available' => ['label' => __('Available', 'cwc-accommodations'), 'help' => __('Normal "Book Now" button.', 'cwc-accommodations')],
		'fully-booked' => ['label' => __('Fully Booked', 'cwc-accommodations'), 'help' => __('"Book Now" replaced with "Fully Booked / Inquire".', 'cwc-accommodations')],
		'maintenance' => ['label' => __('Maintenance', 'cwc-accommodations'), 'help' => __('Pricing box hidden, "Coming Soon" badge shown.', 'cwc-accommodations')],
	];
	?>
	<p>
		<label for="cwc_price"><strong><?php esc_html_e('Price', 'cwc-accommodations'); ?></strong></label><br />
		<input type="text" id="cwc_price" name="cwc_price" value="<?php echo esc_attr($price); ?>"
			placeholder="PHP 19,500" class="widefat" />
		<span class="description"><?php esc_html_e('Display value (free-form text).', 'cwc-accommodations'); ?></span>
	</p>

	<p>
		<label
			for="cwc_price_sub"><strong><?php esc_html_e('Price Sub-label', 'cwc-accommodations'); ?></strong></label><br />
		<input type="text" id="cwc_price_sub" name="cwc_price_sub" value="<?php echo esc_attr($price_sub); ?>"
			placeholder="per night" class="widefat" />
	</p>

	<table>
		<tr>
			<th><label for="cwc_capacity"><?php esc_html_e('Max Guests', 'cwc-accommodations'); ?></label></th>
			<td>
				<input type="number" id="cwc_capacity" name="cwc_capacity"
					value="<?php echo esc_attr((string) $capacity); ?>" class="small-text" min="1" />
				<p class="description">
					<?php esc_html_e('Maximum number of persons allowed in this room type.', 'cwc-accommodations'); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="cwc_availability"><?php esc_html_e('Room Status', 'cwc-accommodations'); ?></label></th>
			<td>
				<select id="cwc_availability" name="cwc_availability" class="widefat">
					<?php foreach ($availability_options as $value => $option): ?>
						<option value="<?php echo esc_attr($value); ?>" <?php selected($availability, $value); ?>>
							<?php echo esc_html($option['label']); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php echo esc_html($availability_options[$availability]['help'] ?? ''); ?></p>
			</td>
		</tr>
	</table>

	<hr />

	<h3><?php esc_html_e('Individual Room Units Tracking', 'cwc-accommodations'); ?></h3>
	<p class="description">
		<?php esc_html_e('Add every physical room here to track its specific availability status.', 'cwc-accommodations'); ?>
	</p>

	<div id="cwc-physical-rooms-wrapper">
		<ul id="cwc-physical-rooms-list" style="margin: 0; padding: 0; list-style: none;">
			<?php
			$physical_rooms = cwc_get_physical_rooms($post->ID);
			foreach ($physical_rooms as $idx => $room):
				?>
				<li class="cwc-physical-room-row"
					style="display: flex; gap: 10px; align-items: center; margin-bottom: 8px; background: #f9f9f9; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
					<input type="hidden" name="cwc_physical_rooms[<?php echo $idx; ?>][id]"
						value="<?php echo esc_attr($room['id'] ?? ''); ?>" />
					<input type="text" name="cwc_physical_rooms[<?php echo $idx; ?>][name]"
						value="<?php echo esc_attr($room['name'] ?? ''); ?>" placeholder="Room Name (e.g. Villa 101)"
						class="widefat" />
					<select name="cwc_physical_rooms[<?php echo $idx; ?>][status]">
						<option value="available" <?php selected($room['status'] ?? 'available', 'available'); ?>>
							<?php esc_html_e('Available', 'cwc-accommodations'); ?></option>
						<option value="booked" <?php selected($room['status'] ?? '', 'booked'); ?>>
							<?php esc_html_e('Booked / Blocked', 'cwc-accommodations'); ?></option>
					</select>
					<button type="button" class="button cwc-remove-physical-room" title="Remove Room">×</button>
				</li>
			<?php endforeach; ?>
		</ul>
		<button type="button" class="button"
			id="cwc-add-physical-room"><?php esc_html_e('+ Add Physical Room', 'cwc-accommodations'); ?></button>
	</div>

	<script>
		(function ($) {
			$(function () {
				const $list = $('#cwc-physical-rooms-list');
				const $addBtn = $('#cwc-add-physical-room');

				$addBtn.on('click', function () {
					const idx = $list.find('li').length;
					const html = `
					<li class="cwc-physical-room-row" style="display: flex; gap: 10px; align-items: center; margin-bottom: 8px; background: #f9f9f9; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
						<input type="hidden" name="cwc_physical_rooms[${idx}][id]" value="" />
						<input type="text" name="cwc_physical_rooms[${idx}][name]" value="" placeholder="Room Name" class="widefat" />
						<select name="cwc_physical_rooms[${idx}][status]">
							<option value="available">Available</option>
							<option value="booked">Booked / Blocked</option>
						</select>
						<button type="button" class="button cwc-remove-physical-room">×</button>
					</li>
				`;
					$list.append(html);
				});

				$list.on('click', '.cwc-remove-physical-room', function () {
					$(this).closest('li').remove();
				});
			});
		})(jQuery);
	</script>
	<?php
}

/**
 * Render the Beds configuration box.
 *
 * @since 1.2.0
 *
 * @param WP_Post $post Current post being edited.
 * @return void
 */
function cwc_render_accommodation_beds_box($post)
{
	$beds_raw = (string) get_post_meta($post->ID, '_cwc_beds', true);
	$beds = json_decode($beds_raw, true) ?: [];
	$catalogue = cwc_bed_catalogue();
	?>
	<p class="description">
		<?php esc_html_e('Specify the bed types available in this room.', 'cwc-accommodations'); ?>
	</p>

	<div id="cwc-beds-wrapper">
		<ul id="cwc-beds-list" style="margin: 0; padding: 0; list-style: none;">
			<?php foreach ($beds as $idx => $bed): ?>
				<li class="cwc-bed-row"
					style="display: flex; gap: 5px; align-items: center; margin-bottom: 8px; background: #f9f9f9; padding: 5px; border: 1px solid #ddd; border-radius: 4px;">
					<input type="number" name="cwc_beds[<?php echo $idx; ?>][count]"
						value="<?php echo esc_attr($bed['count'] ?? 1); ?>" style="width: 50px;" min="1" />
					<select name="cwc_beds[<?php echo $idx; ?>][type]" style="flex: 1;">
						<?php foreach ($catalogue as $slug => $row): ?>
							<option value="<?php echo esc_attr($slug); ?>" <?php selected($bed['type'] ?? '', $slug); ?>>
								<?php echo esc_html($row['label']); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<button type="button" class="button cwc-remove-bed" title="Remove">×</button>
				</li>
			<?php endforeach; ?>
		</ul>
		<button type="button" class="button"
			id="cwc-add-bed"><?php esc_html_e('+ Add Bed', 'cwc-accommodations'); ?></button>
	</div>

	<script>
		(function ($) {
			$(function () {
				const $list = $('#cwc-beds-list');
				const $addBtn = $('#cwc-add-bed');
				const catalogue = <?php echo wp_json_encode($catalogue); ?>;

				$addBtn.on('click', function () {
					const idx = $list.find('li').length;
					let options = '';
					for (const slug in catalogue) {
						options += `<option value="${slug}">${catalogue[slug].label}</option>`;
					}

					const html = `
					<li class="cwc-bed-row" style="display: flex; gap: 5px; align-items: center; margin-bottom: 8px; background: #f9f9f9; padding: 5px; border: 1px solid #ddd; border-radius: 4px;">
						<input type="number" name="cwc_beds[${idx}][count]" value="1" style="width: 50px;" min="1" />
						<select name="cwc_beds[${idx}][type]" style="flex: 1;">
							${options}
						</select>
						<button type="button" class="button cwc-remove-bed">×</button>
					</li>
				`;
					$list.append(html);
				});

				$list.on('click', '.cwc-remove-bed', function () {
					$(this).closest('li').remove();
				});
			});
		})(jQuery);
	</script>
	<?php
}

/**
 * Render the Amenities checkbox list.
 *
 * Iterates `cwc_amenity_catalogue()` so adding a new amenity in the
 * catalogue automatically lights up a new checkbox here. Each
 * checkbox value is the amenity slug; `cwc_save_accommodation_meta`
 * joins the checked slugs with commas before storing.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Current post being edited.
 * @return void
 */
function cwc_render_accommodation_amenities_box($post)
{
	$selected_raw = (string) get_post_meta($post->ID, '_cwc_amenities', true);
	$selected = array_filter(array_map('trim', explode(',', $selected_raw)));
	$catalogue = cwc_amenity_catalogue();
	?>
	<p class="description">
		<?php esc_html_e('Tick every amenity this room offers. Icons and labels come from the shared theme catalogue.', 'cwc-accommodations'); ?>
	</p>
	<?php /* Sentinel: confirms this form section was actually rendered and submitted */ ?>
	<input type="hidden" name="cwc_amenities_submitted" value="1" />

	<ul class="cwc-amenity-checklist"
		style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.5rem 1rem;margin:0;padding:0;list-style:none;">
		<?php foreach ($catalogue as $slug => $row):
			$id = 'cwc_amenity_' . sanitize_html_class($slug);
			?>
			<li>
				<label for="<?php echo esc_attr($id); ?>" style="display:flex;align-items:center;gap:.5rem;">
					<input type="checkbox" id="<?php echo esc_attr($id); ?>" name="cwc_amenities[]"
						value="<?php echo esc_attr($slug); ?>" <?php checked(in_array($slug, $selected, true)); ?> />
					<span><?php echo esc_html($row['label']); ?></span>
				</label>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * Render the Gallery box: comma-separated IDs + media picker button.
 *
 * The text input is the source of truth (so what gets saved matches
 * what the editor sees). The "Pick from media library" button
 * opens `wp.media` in multi-select mode and rewrites the input on
 * confirm. A live preview row of thumbnails sits underneath so
 * editors can sanity-check the IDs without leaving the page.
 *
 * `wp_enqueue_media()` is what makes `wp.media` available — without
 * it the JS picker would silently fail.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Current post being edited.
 * @return void
 */
function cwc_render_accommodation_gallery_box($post)
{
	wp_enqueue_media();

	$ids_raw = (string) get_post_meta($post->ID, '_cwc_gallery_ids', true);
	$ids = array_filter(array_map('intval', explode(',', $ids_raw)));
	?>
	<p class="description">
		<?php esc_html_e('Choose the images shown in the hero gallery on the front-end. The first four are used by default; extra IDs are kept for future "see all" support.', 'cwc-accommodations'); ?>
	</p>

	<p>
		<input type="text" id="cwc_gallery_ids" name="cwc_gallery_ids" value="<?php echo esc_attr($ids_raw); ?>"
			class="widefat" placeholder="123,124,125,126" />
	</p>

	<p>
		<button type="button" class="button" id="cwc_gallery_pick">
			<?php esc_html_e('Pick from Media Library', 'cwc-accommodations'); ?>
		</button>
		<button type="button" class="button-link" id="cwc_gallery_clear">
			<?php esc_html_e('Clear', 'cwc-accommodations'); ?>
		</button>
	</p>

	<div id="cwc_gallery_preview" style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.5rem;">
		<?php foreach ($ids as $attachment_id):
			$thumb = wp_get_attachment_image_url($attachment_id, 'thumbnail');
			if (!$thumb) {
				continue;
			}
			?>
			<img src="<?php echo esc_url($thumb); ?>" alt="" width="72" height="72"
				style="object-fit:cover;border:1px solid #dcdcde;border-radius:4px;" />
		<?php endforeach; ?>
	</div>

	<script>
		(function () {
			const pickBtn = document.getElementById('cwc_gallery_pick');
			const clearBtn = document.getElementById('cwc_gallery_clear');
			const input = document.getElementById('cwc_gallery_ids');
			const preview = document.getElementById('cwc_gallery_preview');

			if (!pickBtn || !input || !preview) {
				return;
			}

			const renderPreview = (attachments) => {
				preview.innerHTML = '';
				attachments.forEach((att) => {
					const thumb = att?.sizes?.thumbnail?.url || att?.url || '';
					if (!thumb) {
						return;
					}
					const img = document.createElement('img');
					img.src = thumb;
					img.alt = '';
					img.width = 72;
					img.height = 72;
					img.style.cssText = 'object-fit:cover;border:1px solid #dcdcde;border-radius:4px;';
					preview.appendChild(img);
				});
			};

			let frame;
			pickBtn.addEventListener('click', (event) => {
				event.preventDefault();

				if (frame) {
					frame.open();
					return;
				}

				/*
				 * Pre-select whatever the input currently lists so the
				 * editor can incrementally add / remove rather than
				 * having to rebuild the selection from scratch.
				 */
				const currentIds = input.value
					.split(',')
					.map((id) => parseInt(id, 10))
					.filter(Boolean);

				frame = wp.media({
					title: <?php echo wp_json_encode(__('Select Room Gallery Images', 'cwc-accommodations')); ?>,
					library: { type: 'image' },
					button: { text: <?php echo wp_json_encode(__('Use these images', 'cwc-accommodations')); ?> },
					multiple: 'add',
				});

				frame.on('open', () => {
					const selection = frame.state().get('selection');
					currentIds.forEach((id) => {
						const att = wp.media.attachment(id);
						att.fetch();
						selection.add(att ? [att] : []);
					});
				});

				frame.on('select', () => {
					const attachments = frame.state().get('selection').toJSON();
					input.value = attachments.map((att) => att.id).join(',');
					renderPreview(attachments);
				});

				frame.open();
			});

			clearBtn?.addEventListener('click', (event) => {
				event.preventDefault();
				input.value = '';
				preview.innerHTML = '';
			});
		})();
	</script>
	<?php
}

/**
 * Render the Inclusions box.
 *
 * Text area for comma-separated list of inclusions.
 *
 * @since 1.1.0
 *
 * @param WP_Post $post Current post being edited.
 * @return void
 */
function cwc_render_accommodation_inclusions_box($post)
{
	$selected_raw = (string) get_post_meta($post->ID, '_cwc_inclusions', true);
	$selected = array_filter(array_map('trim', explode(',', $selected_raw)));
	$catalogue = cwc_inclusion_catalogue();
	?>
	<p class="description">
		<?php esc_html_e('Tick every inclusion this room offers. These labels come from the shared inclusion catalogue.', 'cwc-accommodations'); ?>
	</p>
	<?php /* Sentinel: confirms this form section was actually rendered and submitted */ ?>
	<input type="hidden" name="cwc_inclusions_submitted" value="1" />

	<ul class="cwc-inclusion-checklist"
		style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:.5rem 1rem;margin:0;padding:0;list-style:none;">
		<?php foreach ($catalogue as $slug => $row):
			$id = 'cwc_inclusion_' . sanitize_html_class($slug);
			?>
			<li>
				<label for="<?php echo esc_attr($id); ?>" style="display:flex;align-items:center;gap:.5rem;">
					<input type="checkbox" id="<?php echo esc_attr($id); ?>" name="cwc_inclusions[]"
						value="<?php echo esc_attr($slug); ?>" <?php checked(in_array($slug, $selected, true)); ?> />
					<span><?php echo esc_html($row['label']); ?></span>
				</label>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * Render the Event Details box for standard posts.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Current post being edited.
 * @return void
 */
function cwc_render_post_event_details_box($post)
{
	wp_nonce_field('cwc_post_event_meta', 'cwc_post_event_meta_nonce');

	$event_date = (string) get_post_meta($post->ID, '_cwc_event_date', true);
	?>
	<p>
		<label for="cwc_event_date"><strong><?php esc_html_e('Event Date', 'cwc-accommodations'); ?></strong></label><br />
		<input type="date" id="cwc_event_date" name="cwc_event_date" value="<?php echo esc_attr($event_date); ?>"
			class="widefat" />
		<span
			class="description"><?php esc_html_e('Format: YYYY-MM-DD. Only used if the post is in the "Events" category.', 'cwc-accommodations'); ?></span>
	</p>
	<?php
}

/* ---------------------------------------------------------
 * Save handler
 * --------------------------------------------------------- */

/**
 * Persist the meta-box values into post meta.
 *
 * One handler for all three boxes — they all submit through the
 * same shared nonce. Validation rules (allowed availability values,
 * amenity slug whitelist, integer clamp) live in
 * `cwc_accommodation_meta_sanitizer()` so REST writes and form
 * submissions enforce the same shape.
 *
 * Bails early on autosave / cron / unauthorized requests so we
 * never overwrite editor data with a partial autosave payload.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post being saved.
 * @return void
 */
function cwc_save_accommodation_meta($post_id)
{
	if (!isset($_POST['cwc_accommodation_meta_nonce'])) {
		return;
	}

	$nonce = sanitize_text_field(wp_unslash($_POST['cwc_accommodation_meta_nonce']));
	if (!wp_verify_nonce($nonce, 'cwc_accommodation_meta')) {
		return;
	}

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	if ('accommodation' !== get_post_type($post_id)) {
		return;
	}

	// Handle Physical Rooms (JSON encoded array)
	$physical_rooms = [];
	if (isset($_POST['cwc_physical_rooms']) && is_array($_POST['cwc_physical_rooms'])) {
		foreach ($_POST['cwc_physical_rooms'] as $room) {
			$id = isset($room['id']) ? preg_replace('/[^a-z0-9_-]/i', '', (string) $room['id']) : '';
			$name = sanitize_text_field($room['name'] ?? '');
			$status = sanitize_key($room['status'] ?? 'available');
			if ($name) {
				$physical_rooms[] = ['id' => strtolower($id), 'name' => $name, 'status' => $status];
			}
		}
	}

	$pairs = [
		'_cwc_price' => $_POST['cwc_price'] ?? '',
		'_cwc_price_sub' => $_POST['cwc_price_sub'] ?? '',
		'_cwc_capacity' => $_POST['cwc_capacity'] ?? 0,
		'_cwc_availability' => $_POST['cwc_availability'] ?? 'available',
		'_cwc_inventory' => count($physical_rooms),
		'_cwc_gallery_ids' => $_POST['cwc_gallery_ids'] ?? '',
		'_cwc_physical_rooms' => wp_json_encode($physical_rooms),
	];

	// Handle Beds (JSON encoded array)
	$beds = [];
	if (isset($_POST['cwc_beds']) && is_array($_POST['cwc_beds'])) {
		foreach ($_POST['cwc_beds'] as $bed) {
			$count = (int) ($bed['count'] ?? 1);
			$type = sanitize_key($bed['type'] ?? '');
			if ($type) {
				$beds[] = ['count' => $count, 'type' => $type];
			}
		}
	}
	$pairs['_cwc_beds'] = wp_json_encode($beds);

	/*
	 * Amenities and Inclusions arrive as checkbox arrays.
	 *
	 * IMPORTANT: Unchecked checkboxes are absent from $_POST entirely.
	 * We use a sentinel hidden field ('cwc_amenities_submitted') to
	 * distinguish between:
	 *   (a) The form was submitted with no boxes checked → save empty.
	 *   (b) The meta-box AJAX fired without rendering this section → preserve existing value.
	 * Without this guard, a Gutenberg meta-box-loader request that fires
	 * before the checkbox state is registered would wipe all saved amenities.
	 */
	if (isset($_POST['cwc_amenities_submitted'])) {
		$amenities_in = isset($_POST['cwc_amenities']) && is_array($_POST['cwc_amenities'])
			? array_map('sanitize_key', wp_unslash($_POST['cwc_amenities']))
			: [];
		$pairs['_cwc_amenities'] = implode(',', $amenities_in);
	}
	// else: no sentinel → section wasn't rendered; preserve existing _cwc_amenities in DB.

	if (isset($_POST['cwc_inclusions_submitted'])) {
		$inclusions_in = isset($_POST['cwc_inclusions']) && is_array($_POST['cwc_inclusions'])
			? array_map('sanitize_key', wp_unslash($_POST['cwc_inclusions']))
			: [];
		$pairs['_cwc_inclusions'] = implode(',', $inclusions_in);
	}
	// else: no sentinel → section wasn't rendered; preserve existing _cwc_inclusions in DB.

	foreach ($pairs as $key => $raw) {
		$raw = is_string($raw) ? wp_unslash($raw) : $raw;
		$sanitizer = cwc_accommodation_meta_sanitizer($key);
		$clean = $sanitizer($raw);

		update_post_meta($post_id, $key, $clean);
	}
}
add_action('save_post_accommodation', 'cwc_save_accommodation_meta');

/**
 * Save the Event Details for standard posts.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID.
 * @return void
 */
function cwc_save_post_event_meta($post_id)
{
	if (!isset($_POST['cwc_post_event_meta_nonce'])) {
		return;
	}

	$nonce = sanitize_text_field(wp_unslash($_POST['cwc_post_event_meta_nonce']));
	if (!wp_verify_nonce($nonce, 'cwc_post_event_meta')) {
		return;
	}

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	if ('post' !== get_post_type($post_id)) {
		return;
	}

	if (isset($_POST['cwc_event_date'])) {
		update_post_meta($post_id, '_cwc_event_date', sanitize_text_field(wp_unslash($_POST['cwc_event_date'])));
	}
}
add_action('save_post_post', 'cwc_save_post_event_meta');
