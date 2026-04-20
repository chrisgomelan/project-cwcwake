<?php
/**
 * Render template for the cwc/other-rooms block.
 *
 * Renders a card with a blue accent bar, an "Other Rooms" heading, and
 * a row of sibling room thumbnails. Each thumbnail is a full-bleed
 * image with a translucent overlay and a centered uppercase label.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner block markup (unused).
 * @var WP_Block $block      Block instance.
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading = isset( $attributes['heading'] ) ? (string) $attributes['heading'] : '';
$items   = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : [];

if ( empty( $items ) ) {
	return;
}

$wrapper_attrs = get_block_wrapper_attributes( [ 'class' => 'cwc-other-rooms' ] );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="cwc-other-rooms__panel">
		<span class="cwc-other-rooms__accent" aria-hidden="true"></span>

		<div class="cwc-other-rooms__body">
			<?php if ( $heading !== '' ) : ?>
				<h2 class="cwc-other-rooms__heading"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>

			<ul class="cwc-other-rooms__list">
				<?php
				foreach ( $items as $item ) {
					if ( ! is_array( $item ) ) {
						continue;
					}
					$label = isset( $item['label'] ) ? (string) $item['label'] : '';
					$image = isset( $item['image'] ) ? (string) $item['image'] : '';
					$url   = isset( $item['url'] ) ? (string) $item['url'] : '';

					if ( $label === '' && $image === '' ) {
						continue;
					}

					$has_link = $url !== '';
					$tag      = $has_link ? 'a' : 'div';
					?>
					<li class="cwc-other-rooms__item">
						<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							class="cwc-other-rooms__card"
							<?php if ( $has_link ) : ?>
								href="<?php echo esc_url( $url ); ?>"
								aria-label="<?php echo esc_attr( $label ); ?>"
							<?php endif; ?>
						>
							<?php if ( $image !== '' ) : ?>
								<span
									class="cwc-other-rooms__image"
									role="img"
									<?php echo $label !== '' ? 'aria-label="' . esc_attr( $label ) . '"' : ''; ?>
									style="background-image:url('<?php echo esc_url( $image ); ?>');"
								></span>
							<?php endif; ?>
							<span class="cwc-other-rooms__overlay" aria-hidden="true"></span>
							<?php if ( $label !== '' ) : ?>
								<span class="cwc-other-rooms__label"><?php echo esc_html( $label ); ?></span>
							<?php endif; ?>
						</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					</li>
					<?php
				}
				?>
			</ul>
		</div>
	</div>
</section>
