<?php
/**
 * CWC Wake — Rates Manager Block
 *
 * Tabbed interface for Park Hours and Rates.
 *
 * @package CWC_Wake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$categories = $attributes['categories'] ?? array();
if ( empty( $categories ) ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'cwc-rates-manager',
	)
);
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="cwc-rates-manager__inner">
		
		<div class="cwc-rates-manager__columns">
			
			<!-- Left Side: Tabs -->
			<div class="cwc-rates-manager__sidebar">
				<div class="cwc-rates-manager__tabs">
					<?php foreach ( $categories as $index => $rate_cat ) : ?>
						<button 
							class="cwc-rates-manager__tab <?php echo 0 === $index ? 'is-active' : ''; ?>"
							data-target="<?php echo esc_attr( $rate_cat['id'] ); ?>"
							type="button"
						>
							<?php echo esc_html( $rate_cat['title'] ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Right Side: Content -->
			<div class="cwc-rates-manager__content-area">
				<?php foreach ( $categories as $index => $rate_cat ) : ?>
					<div 
						id="cat-<?php echo esc_attr( $rate_cat['id'] ); ?>" 
						class="cwc-rates-manager__panel <?php echo 0 === $index ? 'is-active' : ''; ?>"
					>
						<div class="cwc-rates-manager__accent-bar"></div>
						
						<div class="cwc-rates-manager__panel-body">
							<h2 class="cwc-rates-manager__title"><?php echo esc_html( $rate_cat['title'] ); ?></h2>
							<p class="cwc-rates-manager__description"><?php echo esc_html( $rate_cat['description'] ); ?></p>

							<?php if ( ! empty( $rate_cat['table'] ) ) : ?>
								<div class="cwc-rates-manager__table-wrap">
									<table class="cwc-rates-manager__table">
										<?php foreach ( $rate_cat['table'] as $r_idx => $row ) : ?>
											<tr>
												<?php foreach ( $row as $cell ) : ?>
													<<?php echo 0 === $r_idx ? 'th' : 'td'; ?> class="cwc-rates-manager__cell">
														<?php echo esc_html( $cell ); ?>
													</<?php echo 0 === $r_idx ? 'th' : 'td'; ?>>
												<?php endforeach; ?>
											</tr>
										<?php endforeach; ?>
									</table>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

		</div>

	</div>
</div>
