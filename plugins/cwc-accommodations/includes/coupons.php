<?php
/**
 * Coupon Management System for CWC Accommodations.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the cwc_coupon Custom Post Type.
 */
function cwc_register_coupon_cpt() {
	$labels = [
		'name'               => 'Coupons',
		'singular_name'      => 'Coupon',
		'add_new'            => 'Add New Coupon',
		'add_new_item'       => 'Add New Coupon',
		'edit_item'          => 'Edit Coupon',
		'new_item'           => 'New Coupon',
		'view_item'          => 'View Coupon',
		'search_items'       => 'Search Coupons',
		'not_found'          => 'No coupons found',
		'not_found_in_trash' => 'No coupons found in trash',
		'menu_name'          => 'Coupons',
	];

	$args = [
		'labels'              => $labels,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => 'edit.php?post_type=accommodation', // Submenu of Accommodations
		'capability_type'     => 'post',
		'hierarchical'        => false,
		'supports'            => [ 'title' ], // Title will be the code
		'has_archive'         => false,
		'menu_icon'           => 'dashicons-tag',
	];

	register_post_type( 'cwc_coupon', $args );
}
add_action( 'init', 'cwc_register_coupon_cpt' );

/**
 * Add Meta Boxes for Coupon configuration.
 */
function cwc_coupon_add_meta_boxes() {
	add_meta_box(
		'cwc_coupon_settings',
		'Coupon Settings',
		'cwc_coupon_render_meta_box',
		'cwc_coupon',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'cwc_coupon_add_meta_boxes' );

function cwc_coupon_render_meta_box( $post ) {
	$discount_type = get_post_meta( $post->ID, '_cwc_coupon_type', true ) ?: 'fixed';
	$amount        = get_post_meta( $post->ID, '_cwc_coupon_amount', true );
	$expiry        = get_post_meta( $post->ID, '_cwc_coupon_expiry', true );
	$usage_limit   = get_post_meta( $post->ID, '_cwc_coupon_limit', true );
	$usage_count   = get_post_meta( $post->ID, '_cwc_coupon_count', true ) ?: 0;

	wp_nonce_field( 'cwc_coupon_save', 'cwc_coupon_nonce' );
	?>
	<table class="form-table">
		<tr>
			<th><label>Discount Type</label></th>
			<td>
				<select name="cwc_coupon_type" style="width: 100%; max-width: 400px;">
					<option value="fixed" <?php selected( $discount_type, 'fixed' ); ?>>Fixed Amount (₱)</option>
					<option value="percent" <?php selected( $discount_type, 'percent' ); ?>>Percentage (%)</option>
				</select>
			</td>
		</tr>
		<tr>
			<th><label>Amount</label></th>
			<td><input type="number" step="0.01" name="cwc_coupon_amount" value="<?php echo esc_attr( $amount ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th><label>Expiry Date</label></th>
			<td><input type="date" name="cwc_coupon_expiry" value="<?php echo esc_attr( $expiry ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th><label>Usage Limit</label></th>
			<td>
				<input type="number" name="cwc_coupon_limit" value="<?php echo esc_attr( $usage_limit ); ?>" class="regular-text">
				<p class="description">Leave empty for unlimited.</p>
			</td>
		</tr>
		<tr>
			<th><label>Usage Count</label></th>
			<td><strong><?php echo esc_html( $usage_count ); ?></strong> times used.</td>
		</tr>
	</table>
	<?php
}

function cwc_coupon_save_meta( $post_id ) {
	if ( ! isset( $_POST['cwc_coupon_nonce'] ) || ! wp_verify_nonce( $_POST['cwc_coupon_nonce'], 'cwc_coupon_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( isset( $_POST['cwc_coupon_type'] ) ) {
		update_post_meta( $post_id, '_cwc_coupon_type', sanitize_text_field( $_POST['cwc_coupon_type'] ) );
	}
	if ( isset( $_POST['cwc_coupon_amount'] ) ) {
		update_post_meta( $post_id, '_cwc_coupon_amount', floatval( $_POST['cwc_coupon_amount'] ) );
	}
	if ( isset( $_POST['cwc_coupon_expiry'] ) ) {
		update_post_meta( $post_id, '_cwc_coupon_expiry', sanitize_text_field( $_POST['cwc_coupon_expiry'] ) );
	}
	if ( isset( $_POST['cwc_coupon_limit'] ) ) {
		update_post_meta( $post_id, '_cwc_coupon_limit', sanitize_text_field( $_POST['cwc_coupon_limit'] ) );
	}
}
add_action( 'save_post_cwc_coupon', 'cwc_coupon_save_meta' );

/**
 * AJAX Handler to validate coupon.
 */
function cwc_validate_coupon_ajax() {
	$code = isset( $_POST['code'] ) ? strtoupper( sanitize_text_field( $_POST['code'] ) ) : '';

	if ( empty( $code ) ) {
		wp_send_json_error( [ 'message' => 'Invalid code.' ] );
	}

	$coupons = get_posts( [
		'post_type'   => 'cwc_coupon',
		'title'       => $code,
		'post_status' => 'publish',
		'numberposts' => 1,
	] );

	if ( empty( $coupons ) ) {
		wp_send_json_error( [ 'message' => 'Coupon code not found.' ] );
	}

	$coupon_id    = $coupons[0]->ID;
	$type         = get_post_meta( $coupon_id, '_cwc_coupon_type', true );
	$amount       = floatval( get_post_meta( $coupon_id, '_cwc_coupon_amount', true ) );
	$expiry       = get_post_meta( $coupon_id, '_cwc_coupon_expiry', true );
	$usage_limit  = get_post_meta( $coupon_id, '_cwc_coupon_limit', true );
	$usage_count  = (int) get_post_meta( $coupon_id, '_cwc_coupon_count', true );

	// Check expiry
	if ( ! empty( $expiry ) && strtotime( $expiry ) < current_time( 'timestamp' ) ) {
		wp_send_json_error( [ 'message' => 'Coupon has expired.' ] );
	}

	// Check limit
	if ( ! empty( $usage_limit ) && $usage_count >= (int) $usage_limit ) {
		wp_send_json_error( [ 'message' => 'Coupon usage limit reached.' ] );
	}

	wp_send_json_success( [
		'code'    => $code,
		'type'    => $type,
		'amount'  => $amount,
		'message' => 'Coupon applied successfully!',
	] );
}
add_action( 'wp_ajax_cwc_validate_coupon', 'cwc_validate_coupon_ajax' );
add_action( 'wp_ajax_nopriv_cwc_validate_coupon', 'cwc_validate_coupon_ajax' );
