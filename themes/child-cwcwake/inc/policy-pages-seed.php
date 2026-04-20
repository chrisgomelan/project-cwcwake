<?php
/**
 * One-shot Privacy Policy + Terms & Conditions page seeder.
 *
 * Both pages use the same shared `cwc/policy-content` block; this
 * file owns:
 *
 *   1. The clause data per page (intro paragraph + ordered list of
 *      `[label, body]` sections), copied from the Figma mockups in
 *      `designs/privacy-policy-design.png` and
 *      `designs/terms-and-conditions.png`.
 *   2. Forcing the right `_wp_page_template` on the existing pages so
 *      the right banner/breadcrumbs chrome wraps the content.
 *   3. Populating `post_content` once if it's still empty (never
 *      clobbers editor content).
 *
 * Runs on `init`, guarded by `cwc_policy_pages_seeded`. Safe to ship
 * to existing sites — the option key prevents re-runs.
 *
 * @package CWC_Wake
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Catalogue of legal pages and the block payload they should be seeded with.
 *
 * The shape mirrors the `cwc/policy-content` block attributes:
 *   - `intro`    string      Plain-text intro paragraph(s); `\n\n` splits paragraphs.
 *   - `sections` array<array> Ordered clauses: `[ 'label' => string, 'body' => string ]`.
 *
 * @since 1.0.0
 *
 * @return array<string, array{
 *     slug:     string,
 *     template: string,
 *     intro:    string,
 *     sections: array<int, array{label:string, body:string}>
 * }> Catalogue keyed by a stable id.
 */
function cwc_policy_pages_catalogue() {
	return [
		'privacy-policy' => [
			'slug'     => 'privacy-policy',
			'template' => 'page-privacy-policy',
			'intro'    => "Welcome to CamSur Watersports Complex. Your privacy is important to us, and we are committed to protecting your personal information. This Privacy Policy explains how we collect, use, and safeguard your data when you use our website.",
			'sections' => [
				[
					'label' => 'Information We Collect',
					'body'  => "We may collect personal information such as your name, email address, phone number, and booking details when you register or make a reservation on our site. We also collect non-personal information like browser type, device information, and website usage data to improve user experience.",
				],
				[
					'label' => 'How We Use Your Information',
					'body'  => "Your information is used to process bookings, provide customer support, and improve our services. We may also use your contact details to send updates, promotions, or important notifications related to your reservations.",
				],
				[
					'label' => 'Sharing of Information',
					'body'  => "We do not sell or trade your personal information. However, we may share your data with trusted partners or service providers who help us operate the website and deliver services, such as payment processors or booking systems.",
				],
				[
					'label' => 'Data Security',
					'body'  => "We implement appropriate security measures to protect your personal information from unauthorized access, alteration, or disclosure. While we strive to protect your data, please note that no online platform is completely secure.",
				],
				[
					'label' => 'Cookies',
					'body'  => "Our website uses cookies to enhance your browsing experience. Cookies help us understand user behavior and improve website functionality. You may choose to disable cookies through your browser settings.",
				],
				[
					'label' => 'Your Rights',
					'body'  => "You have the right to access, update, or delete your personal information. If you wish to make changes or request removal of your data, you may contact us directly.",
				],
				[
					'label' => 'Changes to This Policy',
					'body'  => "We may update this Privacy Policy from time to time. Any changes will be posted on this page with an updated effective date.",
				],
				[
					'label' => 'Contact Us',
					'body'  => "If you have any questions or concerns about this Privacy Policy, please contact us at:\nEmail: support@camsurwatersports.com\nPhone: +63 912 345 6789",
				],
			],
		],
		'terms-and-conditions' => [
			'slug'     => 'terms-and-conditions',
			'template' => 'page-terms-and-conditions',
			'intro'    => "Welcome to CamSur Watersports Complex. Your privacy is important to us, and we are committed to protecting your personal information. This Privacy Policy explains how we collect, use, and safeguard your data when you use our website.",
			'sections' => [
				[
					'label' => 'Use of Website',
					'body'  => "Welcome to CamSur Watersports. By accessing and using our website, you agree to comply with and be bound by the following Terms and Conditions. Please read them carefully before making any bookings or using our services.",
				],
				[
					'label' => 'Bookings and Reservations',
					'body'  => "All bookings made through our website are subject to availability and confirmation. You are responsible for providing accurate and complete information when making a reservation. We reserve the right to cancel or refuse bookings if necessary.",
				],
				[
					'label' => 'Payments',
					'body'  => "Payments must be completed through the approved methods available on the website. Prices are subject to change without prior notice. Once a booking is confirmed, payment is considered final unless stated otherwise in our cancellation policy.",
				],
				[
					'label' => 'Cancellations and Refunds',
					'body'  => "Cancellations must be made within the allowed time frame to be eligible for a refund. Refund policies may vary depending on the activity or service booked. Processing of refunds may take several business days.",
				],
				[
					'label' => 'User Responsibilities',
					'body'  => "Users are expected to behave responsibly and follow all safety guidelines when participating in watersports activities. Any damage to property or violation of rules may result in penalties or termination of services.",
				],
				[
					'label' => 'Limitation of Liability',
					'body'  => "CamSur Watersports Complex shall not be held liable for any injuries, losses, or damages incurred during the use of our services, except where required by law. Participation in activities is at your own risk.",
				],
				[
					'label' => 'Intellectual Property',
					'body'  => "All content on this website, including text, images, and design, is the property of CamSur Watersports & Booking. Unauthorized use, reproduction, or distribution is strictly prohibited.",
				],
				[
					'label' => 'Changes to Terms',
					'body'  => "We reserve the right to update or modify these Terms and Conditions at any time. Changes will be effective immediately upon posting on this page.",
				],
				[
					'label' => 'Contact Us',
					'body'  => "If you have any questions or concerns about this Privacy Policy, please contact us at:\nEmail: support@camsurwatersports.com\nPhone: +63 912 345 6789",
				],
			],
		],
	];
}

/**
 * Build the serialized `cwc/policy-content` block markup for a page.
 *
 * Uses `wp_json_encode` for the attribute payload so the section
 * data round-trips safely through Gutenberg's parser.
 *
 * @since 1.0.0
 *
 * @param array $entry Catalogue entry (see {@see cwc_policy_pages_catalogue()}).
 * @return string Block markup ready for `post_content`.
 */
function cwc_render_policy_page_blocks( array $entry ) {
	$flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

	$attrs = wp_json_encode(
		[
			'align'    => 'full',
			'intro'    => isset( $entry['intro'] ) ? (string) $entry['intro'] : '',
			'sections' => isset( $entry['sections'] ) ? array_values( $entry['sections'] ) : [],
		],
		$flags
	);

	return '<!-- wp:cwc/policy-content ' . $attrs . ' /-->';
}

/**
 * Force the right template + seed initial content on the policy pages.
 *
 * Idempotent: skipped after the first successful run via the
 * `cwc_policy_pages_seeded` option. Pages are matched by slug so
 * editors can rename the post titles freely.
 *
 * @since 1.0.0
 *
 * @return void
 */
function cwc_seed_policy_pages() {
	if ( get_option( 'cwc_policy_pages_seeded' ) ) {
		return;
	}

	foreach ( cwc_policy_pages_catalogue() as $entry ) {
		$page = get_page_by_path( $entry['slug'] );
		if ( ! $page instanceof WP_Post ) {
			continue;
		}

		update_post_meta( (int) $page->ID, '_wp_page_template', $entry['template'] );

		if ( '' === trim( (string) $page->post_content ) ) {
			wp_update_post(
				[
					'ID'           => (int) $page->ID,
					'post_content' => cwc_render_policy_page_blocks( $entry ),
				]
			);
		}
	}

	update_option( 'cwc_policy_pages_seeded', true );
}
add_action( 'init', 'cwc_seed_policy_pages', 30 );
