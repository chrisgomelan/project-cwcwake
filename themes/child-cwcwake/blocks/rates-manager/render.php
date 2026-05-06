<?php
/**
 * CWC Wake — Rates Manager Block
 *
 * Tabbed interface for Park Hours and Rates.
 *
 * @package CWC_Wake
 */

if (!defined('ABSPATH')) {
	exit;
}

if ( function_exists( 'cwc_get_global_rates' ) ) {
	$categories = cwc_get_global_rates();
} else {
	$categories = $attributes['categories'] ?? array();
}

if (empty($categories)) {
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
			<!-- Left Side: Sidebar -->
			<div class="cwc-rates-manager__sidebar">
				<!-- Fixed Inquiry Card -->
				<div class="cwc-rates-manager__inquiry-card">
					<div class="cwc-rates-manager__inquiry-icon-wrap">
						<div class="cwc-rates-manager__inquiry-icon">
							<img src="/wp-content/uploads/2026/04/envelope.svg">
						</div>
					</div>
					<div class="cwc-rates-manager__inquiry-content">
						<h3 class="cwc-rates-manager__inquiry-title">Inquire about CWC Rates</h3>
						<p class="cwc-rates-manager__inquiry-desc">Get assistance for booking, packages, or group rates
						</p>
					</div>
					<button type="button" class="cwc-rates-manager__inquiry-btn js-open-inquiry-modal">Send</button>
					<p class="cwc-rates-manager__inquiry-footer">We'll respond within 24-48 hours</p>
				</div>

				<!-- Custom Dropdown -->
				<div class="cwc-rates-manager__dropdown">
					<button class="cwc-rates-manager__dropdown-toggle" type="button" aria-haspopup="listbox"
						aria-expanded="false">
						<span
							class="cwc-rates-manager__dropdown-current"><?php echo esc_html($categories[0]['title']); ?></span>
						<svg class="cwc-rates-manager__dropdown-chevron" width="24" height="24" viewBox="0 0 24 24"
							fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
								stroke-linejoin="round" />
						</svg>
					</button>
					<ul class="cwc-rates-manager__dropdown-menu" role="listbox">
						<?php foreach ($categories as $index => $rate_cat): ?>
							<li class="cwc-rates-manager__dropdown-item <?php echo 0 === $index ? 'is-active' : ''; ?>"
								data-target="<?php echo esc_attr($rate_cat['id']); ?>" role="option"
								aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>" tabindex="0">
								<?php echo esc_html($rate_cat['title']); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>

			<!-- Right Side: Content Area -->
			<div class="cwc-rates-manager__content-area">
				<?php foreach ($categories as $index => $rate_cat): ?>
					<div id="cat-<?php echo esc_attr($rate_cat['id']); ?>"
						class="cwc-rates-manager__panel <?php echo 0 === $index ? 'is-active' : ''; ?>">
						<div class="cwc-rates-manager__accent-bar"></div>

						<div class="cwc-rates-manager__panel-body">
							<div class="cwc-rates-manager__panel-header">
								<h2 class="cwc-rates-manager__title"><?php echo esc_html($rate_cat['title']); ?></h2>
								<!-- Dropdown will be moved here via CSS/JS on mobile -->
							</div>
							<p class="cwc-rates-manager__description"><?php echo esc_html($rate_cat['description']); ?>
							</p>

							<?php if (!empty($rate_cat['table'])): ?>
								<div class="cwc-rates-manager__table-wrap">
									<table class="cwc-rates-manager__table">
										<?php foreach ($rate_cat['table'] as $r_idx => $row): ?>
											<tr>
												<?php foreach ($row as $cell): ?>
													<td class="cwc-rates-manager__cell">
														<?php echo esc_html($cell); ?>
													</td>
												<?php endforeach; ?>
											</tr>
										<?php endforeach; ?>
									</table>
								</div>

								<!-- Mobile Cards View -->
								<div class="cwc-rates-manager__cards">
									<?php foreach ($rate_cat['table'] as $r_idx => $row): ?>
										<div class="cwc-rates-manager__card">
											<?php if (!empty($row[0])): ?>
												<div class="cwc-rates-manager__card-header">
													<?php echo esc_html($row[0]); ?>
												</div>
											<?php endif; ?>
											<div class="cwc-rates-manager__card-body">
												<?php if (!empty($row[1])): ?>
													<div class="cwc-rates-manager__card-time">
														<?php echo esc_html($row[1]); ?>
													</div>
												<?php endif; ?>

												<?php if (count($row) > 2): ?>
													<div class="cwc-rates-manager__card-tags">
														<?php
														// Treat the rest of the columns as tags/pills
														for ($i = 2; $i < count($row); $i++) {
															if (empty($row[$i]))
																continue;
															$tags = explode(',', $row[$i]);
															foreach ($tags as $tag) {
																echo '<span class="cwc-rates-manager__tag">' . esc_html(trim($tag)) . '</span>';
															}
														}
														?>
													</div>
												<?php endif; ?>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

		</div>

	</div>

	<!-- Inquiry Modal -->
	<div class="cwc-inquiry-modal" id="cwc-inquiry-modal" aria-hidden="true">
		<div class="cwc-inquiry-modal__overlay js-close-modal"></div>
		<div class="cwc-inquiry-modal__container">
			<div class="cwc-inquiry-modal__header"
				style="background-image: url('/wp-content/uploads/2026/04/form-modal-bg.webp');">
				<button class="cwc-inquiry-modal__close js-close-modal" aria-label="Close modal">&times;</button>
			</div>
			<div class="cwc-inquiry-modal__body">
				<form class="cwc-inquiry-form">
					<div class="cwc-inquiry-form__group">
						<label class="cwc-inquiry-form__label">Email <span class="required">*</span></label>
						<input type="email" class="cwc-inquiry-form__input" placeholder="Enter email address" required>
					</div>
					<div class="cwc-inquiry-form__group">
						<label class="cwc-inquiry-form__label">Subject <span class="required">*</span></label>
						<div class="cwc-inquiry-form__select-wrap">
							<select class="cwc-inquiry-form__select" required>
								<option value="" disabled selected>Choose a subject</option>
								<?php foreach ($categories as $rate_cat): ?>
									<option value="<?php echo esc_attr($rate_cat['title']); ?>">
										<?php echo esc_html($rate_cat['title']); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="cwc-inquiry-form__group">
						<label class="cwc-inquiry-form__label">Message <span class="required">*</span></label>
						<textarea class="cwc-inquiry-form__textarea" placeholder="Enter message..." required></textarea>
					</div>
					<button type="submit" class="cwc-inquiry-form__submit">Send Inquiry</button>
				</form>
			</div>
		</div>
	</div>
</div>