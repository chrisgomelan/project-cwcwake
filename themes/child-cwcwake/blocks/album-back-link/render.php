<?php
/**
 * Render template for the cwc/album-back-link block.
 *
 * Outputs a right-aligned "← Back to {parent or Gallery}" link
 * shown on the single-album template (see the right-side link in
 * `designs/albums-page-design.png`).
 *
 * Resolution rules (delegated to {@see cwc_album_back_link()}):
 *   - Sub-album → "Back to {parent album title}".
 *   - Top-level → "Back to Gallery" (linking to the /gallery/ page).
 *
 * Renders nothing when not on a `cwc_album` singular view.
 *
 * @var array $attributes Block attributes (unused).
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_singular( 'cwc_album' ) ) {
	return;
}

$album_post_id   = (int) get_queried_object_id();
$back_link_data = cwc_album_back_link( $album_post_id );

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'cwc-album-back-link' ) );
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<a class="cwc-album-back-link__anchor" href="<?php echo esc_url( $back_link_data['url'] ); ?>">
		<span class="cwc-album-back-link__arrow" aria-hidden="true">&#8592;</span>
		<span class="cwc-album-back-link__label"><?php echo esc_html( $back_link_data['label'] ); ?></span>
	</a>
</div>
