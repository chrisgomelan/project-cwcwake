<?php
/**
 * Render template for the cwc/why-stay block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner block markup.
 * @var WP_Block $block      Block instance.
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading     = isset( $attributes['heading'] ) ? $attributes['heading'] : '';
$description = isset( $attributes['description'] ) ? (string) $attributes['description'] : '';
$items       = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();
$tone        = isset( $attributes['tone'] ) ? (string) $attributes['tone'] : 'default';

$class_name = 'cwc-why-stay';
if ( 'elite' === $tone ) {
	$class_name .= ' cwc-why-stay--elite';
}
if ( ! empty( $attributes['className'] ) ) {
	$class_name .= ' ' . esc_attr( $attributes['className'] );
}

$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => $class_name ) );
?>
<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<header class="cwc-why-stay__header">
		<?php if ( $heading ) : ?>
			<h2 class="cwc-why-stay__title"><?php echo wp_kses_post( $heading ); ?></h2>
		<?php endif; ?>

		<?php if ( $description ) : ?>
			<p class="cwc-why-stay__description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( ! empty( $items ) ) : ?>
		<div class="cwc-why-stay__grid">
			<?php
			foreach ( $items as $item ) :
				$icon_name  = isset( $item['icon'] ) ? $item['icon'] : '';
				$item_title = isset( $item['title'] ) ? $item['title'] : '';
				$desc       = isset( $item['desc'] ) ? $item['desc'] : '';
				$icon_url   = get_stylesheet_directory_uri() . '/assets/images/' . $icon_name;
				?>
				<div class="cwc-why-stay__item">
					<div class="cwc-why-stay__icon-wrapper">
						<?php if ( $icon_name ) : ?>
							<img class="cwc-why-stay__icon" src="<?php echo esc_url( $icon_url ); ?>" alt="" loading="lazy" aria-hidden="true" />
						<?php endif; ?>
					</div>

					<div class="cwc-why-stay__content">
						<?php if ( $item_title ) : ?>
							<h3 class="cwc-why-stay__item-title"><?php echo esc_html( $item_title ); ?></h3>
						<?php endif; ?>

						<?php if ( $desc ) : ?>
							<p class="cwc-why-stay__item-desc"><?php echo esc_html( $desc ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
