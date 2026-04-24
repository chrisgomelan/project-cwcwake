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

$categories = array(
	'getting-started'       => array(
		'label' => 'Getting Started',
		'items' => array(
			array(
				'q' => 'Do I need prior experience to try wakeboarding at CWC?',
				'a' => 'No, beginners are welcome. CWC provides basic instruction and guidance, making it easy for first-timers to get started.',
			),
			array(
				'q' => 'What should I wear for wakeboarding?',
				'a' => 'Wear comfortable swimwear or athletic gear. Rash guards are recommended for added protection, and don\'t forget sunscreen.',
			),
			array(
				'q' => 'Is equipment included or do I bring my own?',
				'a' => 'CWC offers rental equipment such as wakeboards, helmets, and life vests. You can bring your own gear if you prefer.',
			),
			array(
				'q' => 'Are there instructors available for beginners?',
				'a' => 'Yes, trained instructors are available to assist and guide you through the basics before you hit the water.',
			),
			array(
				'q' => 'What is the first step when I arrive?',
				'a' => 'Start by registering at the front desk, choose your activity or package, rent equipment if needed, and attend a quick orientation before riding.',
			),
		),
	),
	'reservations-payments' => array(
		'label' => 'Reservations & Payments',
		'items' => array(
			array(
				'q' => 'How do I make a reservation?',
				'a' => 'You can book online through our website or contact our front desk directly via phone or email to reserve your preferred dates and activities.',
			),
			array(
				'q' => 'What payment methods are accepted?',
				'a' => 'We accept cash, credit/debit cards, GCash, and bank transfers. Payment details are provided upon booking confirmation.',
			),
			array(
				'q' => 'Can I cancel or reschedule my booking?',
				'a' => 'Yes, cancellations and rescheduling are allowed up to 48 hours before your visit. Late cancellations may incur a fee.',
			),
			array(
				'q' => 'Is a deposit required for bookings?',
				'a' => 'A 50% non-refundable deposit is required to confirm your reservation. The remaining balance is due on the day of your visit.',
			),
		),
	),
	'stay-comfort'          => array(
		'label' => 'Stay & Comfort',
		'items' => array(
			array(
				'q' => 'What types of accommodations are available?',
				'a' => 'CWC offers villas, cabanas, dwell units, and cabin-style rooms. Each is designed for comfort with views of the park and lake.',
			),
			array(
				'q' => 'Are the rooms air-conditioned?',
				'a' => 'Yes, all rooms come with air-conditioning, hot/cold showers, and basic amenities for a comfortable stay.',
			),
			array(
				'q' => 'Can I check in early or check out late?',
				'a' => 'Early check-in and late check-out are subject to availability. Contact our front desk in advance to arrange.',
			),
		),
	),
	'food-social'           => array(
		'label' => 'Food & Social',
		'items' => array(
			array(
				'q' => 'Are there restaurants or food stalls at CWC?',
				'a' => 'Yes, the park has an on-site restaurant and a bar serving a variety of local and international dishes, snacks, and beverages.',
			),
			array(
				'q' => 'Can I bring my own food?',
				'a' => 'Outside food is allowed in designated picnic areas, but corkage fees may apply. Check with the front desk for details.',
			),
			array(
				'q' => 'Are there social events or activities at night?',
				'a' => 'CWC hosts occasional live music events, bonfires, and themed nights. Check our social media for upcoming events.',
			),
		),
	),
	'travel-location'       => array(
		'label' => 'Travel & Location',
		'items' => array(
			array(
				'q' => 'Where is CWC located?',
				'a' => 'CWC is located at the Provincial Capitol Complex in Cadlan, Pili, Camarines Sur, Philippines — about 30 minutes from Naga City.',
			),
			array(
				'q' => 'How do I get to CWC from Manila?',
				'a' => 'You can take a direct flight to Naga Airport (about 1 hour), then a 30-minute drive to CWC. Alternatively, take a bus from Cubao to Naga City.',
			),
			array(
				'q' => 'Is parking available?',
				'a' => 'Yes, free parking is available for guests within the complex grounds.',
			),
		),
	),
);

$cat_keys  = array_keys( $categories );
$first_key = $cat_keys[0];
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
