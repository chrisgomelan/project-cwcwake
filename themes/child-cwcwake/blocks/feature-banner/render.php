<?php
/**
 * Feature Banner — Full-width image card with overlay + title.
 *
 * Reusable across pages for feature highlights
 * (Inflatable Aqua Adventure, etc.)
 *
 * Optional grid of image cards below the hero strip (Performance Training Zones).
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bg_image      = $attributes['backgroundImage'] ?? '';
$title_line1   = $attributes['titleLine1'] ?? '';
$title_accent  = $attributes['titleAccent'] ?? '';
$title_line2   = $attributes['titleLine2'] ?? '';
$accent_color  = isset( $attributes['accentColor'] ) ? trim( (string) $attributes['accentColor'] ) : '';
$description   = $attributes['description'] ?? '';
$grid_items    = isset( $attributes['gridItems'] ) && is_array( $attributes['gridItems'] ) ? $attributes['gridItems'] : array();

$hero_style = '';
if ( '' !== $bg_image ) {
	$hero_style = sprintf( 'background-image:url(%s);', esc_url( $bg_image ) );
}

$accent_style = '';
if ( '' !== $accent_color ) {
	$accent_style = sprintf( 'color:%s;', esc_attr( $accent_color ) );
}

$section_classes = array( 'cwc-feature-banner' );
if ( ! empty( $grid_items ) ) {
	$section_classes[] = 'cwc-feature-banner--with-grid';
}

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $section_classes ),
	)
);
?>

<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="cwc-feature-banner__hero"<?php echo $hero_style ? ' style="' . esc_attr( $hero_style ) . '"' : ''; ?>>
		<div class="cwc-feature-banner__overlay" aria-hidden="true"></div>
		<div class="cwc-feature-banner__content">
			<?php if ( '' !== $title_line1 || '' !== $title_accent || '' !== $title_line2 ) : ?>
				<h2 class="cwc-feature-banner__title">
					<?php if ( '' !== $title_line1 ) : ?>
						<?php echo esc_html( $title_line1 ); ?>
					<?php endif; ?>
					<?php if ( '' !== $title_accent ) : ?>
						<em class="cwc-feature-banner__accent"<?php echo $accent_style ? ' style="' . esc_attr( $accent_style ) . '"' : ''; ?>><?php echo esc_html( $title_accent ); ?></em>
					<?php endif; ?>
					<?php if ( '' !== $title_line2 ) : ?>
						<?php echo esc_html( $title_line2 ); ?>
					<?php endif; ?>
				</h2>
			<?php endif; ?>

			<?php if ( '' !== $description ) : ?>
				<p class="cwc-feature-banner__desc"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( ! empty( $grid_items ) ) : ?>
		<div class="cwc-feature-banner__grid-wrap">
			<div class="cwc-feature-banner__grid">
				<?php
				foreach ( $grid_items as $cell ) :
					if ( ! is_array( $cell ) ) {
						continue;
					}
					$g_title = isset( $cell['title'] ) ? (string) $cell['title'] : '';
					$g_desc  = isset( $cell['description'] ) ? (string) $cell['description'] : '';
					$g_img   = isset( $cell['image'] ) ? trim( (string) $cell['image'] ) : '';
					?>
					<div class="cwc-feature-banner__grid-cell">
						<?php if ( '' !== $g_img ) : ?>
							<img class="cwc-feature-banner__grid-img" src="<?php echo esc_url( $g_img ); ?>" alt="" loading="lazy" aria-hidden="true">
						<?php endif; ?>
						<div class="cwc-feature-banner__grid-overlay" aria-hidden="true"></div>
						<div class="cwc-feature-banner__grid-text">
							<?php if ( '' !== $g_title ) : ?>
								<h3 class="cwc-feature-banner__grid-title"><?php echo esc_html( $g_title ); ?></h3>
							<?php endif; ?>
							<?php if ( '' !== $g_desc ) : ?>
								<p class="cwc-feature-banner__grid-desc"><?php echo esc_html( $g_desc ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</section>
