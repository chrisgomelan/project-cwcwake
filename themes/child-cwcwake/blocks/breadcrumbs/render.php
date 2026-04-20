<?php
/**
 * CWC Wake — Breadcrumbs block render template.
 *
 * Thin wrapper around `cwc_render_breadcrumbs()`. Templates such as
 * Gallery, Accommodations, Contact, Terms & Conditions, and Privacy
 * Policy place this block at the top of the `cwc-tropical-section`
 * group so the breadcrumbs sit on the shared `tropical-bg` surface.
 *
 * Filter the items via `cwc_breadcrumbs_items` if you need to
 * inject extra crumbs (e.g. a "Blog" parent on single posts).
 *
 * @package CWC_Wake
 * @since   1.0.0
 *
 * @var array $attributes Block attributes passed in by WordPress.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wrapper_attrs = get_block_wrapper_attributes( [
	'class' => 'cwc-breadcrumbs__wrap',
] );

$markup = cwc_render_breadcrumbs( [
	'home_label'     => $attributes['homeLabel'] ?? 'Home',
	'show_home_icon' => ! empty( $attributes['showHomeIcon'] ),
] );

if ( '' === $markup ) {
	return;
}
?>
<div <?php echo $wrapper_attrs; ?>>
	<?php echo $markup; ?>
</div>
