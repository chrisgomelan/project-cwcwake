<?php
/**
 * FAQ Section block — render template.
 *
 * Renders a two-column FAQ layout:
 *   Left sidebar  — search input + category tabs.
 *   Right content — section heading + accordion items.
 *
 * FAQ data is defined in PHP so editors can extend categories and
 * questions without touching markup. The view.js handles accordion
 * toggling, category switching, and live search filtering.
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme_uri = get_stylesheet_directory_uri();
$plus_svg  = $theme_uri . '/assets/images/plus-icon.svg';
$minus_svg = $theme_uri . '/assets/images/minus.svg';

$categories_raw = $attributes['categories'] ?? [];
$categories     = [];

if ( ! empty( $categories_raw ) ) {
	// If categories are passed via attributes, they might be an array of objects.
	// We convert them to the expected associative array structure if needed.
	foreach ( $categories_raw as $cat ) {
		$slug = $cat['slug'] ?? sanitize_title( $cat['label'] ?? 'faq' );
		$categories[ $slug ] = [
			'label' => $cat['label'] ?? '',
			'items' => $cat['items'] ?? [],
		];
	}
} else {
	$categories = cwc_get_faq_data();
}

$cat_keys  = array_keys( $categories );
$first_key = $cat_keys[0] ?? '';
?>

<section class="cwc-faq" data-cwc-faq>

	<!-- Left sidebar -->
	<aside class="cwc-faq__sidebar">
		<div class="cwc-faq__search-wrap">
			<svg class="cwc-faq__search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
				viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
				stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<circle cx="11" cy="11" r="8"/>
				<line x1="21" y1="21" x2="16.65" y2="16.65"/>
			</svg>
			<input
				type="search"
				class="cwc-faq__search"
				placeholder="Search Help Center"
				aria-label="Search FAQs"
				data-cwc-faq-search
			>
		</div>

		<nav class="cwc-faq__tabs" aria-label="FAQ categories">
			<?php foreach ( $categories as $slug => $category_data ) : ?>
				<button
					class="cwc-faq__tab<?php echo $slug === $first_key ? ' is-active' : ''; ?>"
					type="button"
					data-cwc-faq-tab="<?php echo esc_attr( $slug ); ?>"
				>
					<?php echo esc_html( $category_data['label'] ); ?>
				</button>
			<?php endforeach; ?>
		</nav>
	</aside>

	<!-- Vertical divider -->
	<div class="cwc-faq__divider" aria-hidden="true"></div>

	<!-- Right content -->
	<div class="cwc-faq__content">
		<?php foreach ( $categories as $slug => $category_data ) : ?>
			<div
				class="cwc-faq__category<?php echo $slug === $first_key ? ' is-active' : ''; ?>"
				data-cwc-faq-category="<?php echo esc_attr( $slug ); ?>"
			>
				<h2 class="cwc-faq__category-title"><?php echo esc_html( $category_data['label'] ); ?></h2>

				<?php foreach ( $category_data['items'] as $idx => $item ) : ?>
					<div class="cwc-faq__item" data-cwc-faq-item>
						<button class="cwc-faq__question" type="button" aria-expanded="false">
							<span class="cwc-faq__question-text"><?php echo esc_html( $item['q'] ); ?></span>
							<img class="cwc-faq__icon cwc-faq__icon--plus" src="<?php echo esc_url( $plus_svg ); ?>" alt="" aria-hidden="true" width="30" height="21">
							<img class="cwc-faq__icon cwc-faq__icon--minus" src="<?php echo esc_url( $minus_svg ); ?>" alt="" aria-hidden="true" width="30" height="3">
						</button>
						<div class="cwc-faq__answer" role="region">
							<div class="cwc-faq__answer-inner">
								<p><?php echo esc_html( $item['a'] ); ?></p>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>

		<!-- No results message (hidden by default) -->
		<div class="cwc-faq__no-results" data-cwc-faq-no-results hidden>
			<p>No matching questions found. Try a different search term.</p>
		</div>
	</div>

</section>
<?php
