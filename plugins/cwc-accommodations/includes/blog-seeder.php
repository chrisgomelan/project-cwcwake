<?php
/**
 * CWC Accommodations — Blog Post Seeder.
 *
 * One-shot seeder that generates the dataset the Blogs page
 * (`/plan-your-trip/blogs/`) needs to render in a non-empty state
 * before editors fill it in:
 *
 *   - 4 default categories (Events / Resort News / Pro Tips / Local Guide).
 *   - 5 "featured" posts (`_cwc_blog_featured = 1`) for the asymmetric
 *     grid in the Featured Blogs section.
 *   - 3 "events" posts with future `_cwc_event_date` meta for the
 *     Upcoming Events timeline.
 *   - 6 filler posts so the All Blogs grid + pagination has enough
 *     records to demo with.
 *
 * @package CWC_Accommodations
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------------------------------------------------------
 * Public meta key constants
 * --------------------------------------------------------- */

if ( ! defined( 'CWC_BLOG_META_FEATURED' ) ) {
	define( 'CWC_BLOG_META_FEATURED', '_cwc_blog_featured' );
}

if ( ! defined( 'CWC_BLOG_META_EVENT_DATE' ) ) {
	define( 'CWC_BLOG_META_EVENT_DATE', '_cwc_event_date' );
}

/* ---------------------------------------------------------
 * Seeder hook
 * --------------------------------------------------------- */

/**
 * Run the blog seeder once per environment.
 *
 * @return void
 */
function cwc_maybe_seed_blog_posts(): void {
	if ( get_option( 'cwc_blog_posts_seeded' ) ) {
		return;
	}

	if ( ! is_admin() && ! wp_doing_cron() && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	}

	$created = cwc_seed_blog_posts();
	update_option( 'cwc_blog_posts_seeded', time() );

	do_action( 'cwc_blog_posts_seeded', $created );
}
add_action( 'init', 'cwc_maybe_seed_blog_posts', 40 );

/* ---------------------------------------------------------
 * Core seeder
 * --------------------------------------------------------- */

/**
 * Seed sample blog posts and the four default categories.
 *
 * @return int Number of new posts created.
 */
function cwc_seed_blog_posts(): int {
	$cat_ids = cwc_seed_blog_categories();
	if ( empty( $cat_ids ) ) {
		return 0;
	}

	$dataset = cwc_blog_seed_dataset();

	$count = 0;
	foreach ( $dataset as $row ) {
		if ( cwc_blog_seed_insert_row( $row, $cat_ids ) ) {
			$count++;
		}
	}

	return $count;
}

/**
 * Ensure the four default blog categories exist.
 *
 * @return array<string,int> Category name → term ID.
 */
function cwc_seed_blog_categories(): array {
	if ( ! taxonomy_exists( 'category' ) ) {
		return [];
	}

	$names = [ 'Events', 'Resort News', 'Pro Tips', 'Local Guide' ];
	$ids   = [];

	foreach ( $names as $name ) {
		$existing = get_term_by( 'name', $name, 'category' );
		if ( $existing instanceof WP_Term ) {
			$ids[ $name ] = (int) $existing->term_id;
			continue;
		}

		$inserted = wp_insert_term( $name, 'category' );
		if ( ! is_wp_error( $inserted ) ) {
			$ids[ $name ] = (int) $inserted['term_id'];
		}
	}

	return $ids;
}

/**
 * Canonical seed dataset for the Blogs page.
 *
 * @return array<int,array<string,mixed>>
 */
function cwc_blog_seed_dataset(): array {
	$dataset = [
		// Featured Blogs (5 posts)
		[
			'title'    => 'Plan Your Trip: What to Know Before Visiting CWC',
			'cat'      => 'Local Guide',
			'excerpt'  => 'From travel essentials to insider tips and everything you need to plan a smooth and stress-free visit to CWC.',
			'image'    => 'plan-your-trip.webp',
			'featured' => true,
			'content'  => '
<h2>Introduction</h2>
<p>Planning a trip to CamSur Watersports Complex (CWC)? Whether you\'re chasing adrenaline on the water or looking for a relaxing getaway, this guide covers everything you need to know before you go. From travel tips to must-try activities, get ready for a smooth and unforgettable experience.</p>

<h2>Getting There</h2>
<p>CWC is located in Camarines Sur and is accessible by land or air. If you\'re coming from Manila, you can take a short flight to Naga City or enjoy a scenic road trip. From there, it\'s just a quick ride to the park.</p>

<!-- wp:image {"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="/wp-content/uploads/2026/04/landing-activities.webp" alt="getting there"/></figure>
<!-- /wp:image -->

<h2>Best Time to Visit</h2>
<p>CWC is located in Camarines Sur and is accessible by land or air. If you\'re coming from Manila, you can take a short flight to Naga City or enjoy a scenic road trip. From there, it\'s just a quick ride to the park.</p>

<h3>Weekday vs Weekend</h3>
<p>Weekdays are ideal if you want to avoid crowds, while weekends offer a more lively atmosphere with more activities and events.</p>

<h2>What to Bring</h2>
<p>CWC is located in Camarines Sur and is accessible by land or air. If you\'re coming from Manila, you can take a short flight to Naga City or enjoy a scenic road trip. From there, it\'s just a quick ride to the park.</p>

<h3>Essentials</h3>
<ul>
	<li>Swimwear and extra clothes</li>
	<li>Sunscreen and sunglasses</li>
	<li>Waterproof bag for valuables</li>
	<li>Slippers or comfortable footwear</li>
</ul>

<!-- wp:image {"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="/wp-content/uploads/2026/04/cabana-rentals.webp" alt="what to bring"/></figure>
<!-- /wp:image -->

<h2>Accommodation Options</h2>
<p>Stay close to the action with cozy, air-conditioned rooms within the complex. Booking in advance is highly recommended, especially during peak seasons and holidays.</p>

<h2>Final Thoughts</h2>
<p>A trip to CWC is all about balance—thrill and relaxation, activity and downtime. With the right planning, you can make the most out of your visit and create an unforgettable experience.</p>
',
		],
		[
			'title'    => 'Ride the Waves: Wakeboarding Experiences at CWC',
			'cat'      => 'Pro Tips',
			'excerpt'  => 'Feel the thrill of riding across the cable wakes. Discover why CWC is a must-visit for water sports lovers.',
			'image'    => 'ride-the-waves-2.webp',
			'featured' => true,
			'content'  => '
<h2>Introduction to Wakeboarding</h2>
<p>Whether you are a seasoned pro or a complete beginner, wakeboarding at CWC is an experience like no other. Our cable systems are designed to cater to all levels, providing the perfect environment for learning or perfecting new tricks.</p>

<!-- wp:image {"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="/wp-content/uploads/2026/04/water-sports.webp" alt="wakeboarding"/></figure>
<!-- /wp:image -->

<h2>What to Expect</h2>
<p>Expect vibrant energy and a supportive community. Our instructors are always on hand to help you get started, and you will find plenty of gear available for rent at the pro shop.</p>
',
		],
		[
			'title'    => 'Experience the Energy: Events and Nightlife at CWC',
			'cat'      => 'Events',
			'excerpt'  => 'Discover after-dark events, music nights, and live performances that turn CWC into a non-stop lifestyle destination.',
			'image'    => 'experiece-the-energy-2.webp',
			'featured' => true,
			'content'  => '
<h2>The Nightlife Scene</h2>
<p>After the cables stop spinning, the party begins. CWC is home to some of the Most exciting events in Camarines Sur, from acoustic sessions to full-blown DJ sets that keep the energy high long after the sun goes down.</p>

<!-- wp:image {"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="/wp-content/uploads/2026/04/clubhouse-resto-bar.webp" alt="nightlife"/></figure>
<!-- /wp:image -->
',
		],
		[
			'title'    => 'Top 5 Hidden Spots in CamSur',
			'cat'      => 'Local Guide',
			'excerpt'  => 'Explore the natural beauty of Camarines Sur beyond the wakeboarding park — beaches, trails, and food finds.',
			'image'    => 'explore-camsur.webp',
			'featured' => true,
			'content'  => '
<h2>Beyond the Park</h2>
<p>Camarines Sur is a treasure trove of natural wonders. While CWC is the heart of the action, taking a day trip to explore nearby beaches and hiking trails is highly recommended for those who love the great outdoors.</p>
',
		],
		[
			'title'    => 'A Beginner\'s Guide to Cable Parks',
			'cat'      => 'Pro Tips',
			'excerpt'  => 'First time at a cable park? Here is everything you need to know before you hit the water.',
			'image'    => 'cabana-rentals.webp',
			'featured' => true,
			'content'  => '
<h2>Getting Started</h2>
<p>If its your first time at a cable park, dont worry. We have dedicated beginner cables that travel at a slower speed, making it much easier to find your balance and get the feel of the board before moving to the main lake.</p>
',
		],

		// Upcoming Events (3 posts)
		[
			'title'             => 'Sunset Ride Sessions',
			'cat'               => 'Events',
			'excerpt'           => 'Ride into golden hour and experience one of the most relaxing yet visually stunning moments at CWCLake.',
			'image'             => 'sunset-ride-sessions-events.webp',
			'event_offset_days' => 7,
		],
		[
			'title'             => 'Summer Wake Championship 2026',
			'cat'               => 'Events',
			'excerpt'           => 'The biggest competition of the year is coming back. Register now to compete or cheer on your riders.',
			'image'             => 'cwc-wakeboarding-showdown.webp',
			'event_offset_days' => 14,
		],
		[
			'title'             => 'Evening Acoustic Sessions at the Bar',
			'cat'               => 'Events',
			'excerpt'           => 'Unwind after a day on the water with live acoustic performances every Friday at the resort bar.',
			'image'             => 'clubhouse-resto-bar.webp',
			'event_offset_days' => 21,
		],

		// Filler Blogs (6+ posts)
		[
			'title'   => 'New Luxury Villas Now Open for Booking',
			'cat'     => 'Resort News',
			'excerpt' => 'Experience a new level of comfort with our newly launched premium villas overlooking the park.',
			'image'   => 'VILLAS.webp',
		],
		[
			'title'   => 'Sustainable Tourism at CWC',
			'cat'     => 'Resort News',
			'excerpt' => 'How we are working to protect the local environment while welcoming guests from around the world.',
			'image'   => 'doodle-bg.webp',
		],
		[
			'title'   => 'CWC Gear Guide: Choosing Your First Board',
			'cat'     => 'Pro Tips',
			'excerpt' => 'Choosing the right board for your style — a quick walk-through of shapes, sizes, and skill levels.',
			'image'   => 'ride-the-waves.webp',
		],
		[
			'title'   => 'Traveling to CamSur: Tips & Tricks',
			'cat'     => 'Local Guide',
			'excerpt' => 'The easiest ways to get to CWC from Manila — flights, vans, and the scenic road option.',
			'image'   => 'contact-banner-bg.webp',
		],
		[
			'title'   => 'The History of CWC Wake Park',
			'cat'     => 'Resort News',
			'excerpt' => 'From a dream to a world-class destination — the story behind CWC.',
			'image'   => 'blogs-banner-bg.webp',
		],
		[
			'title'   => 'Resort Wellness Month',
			'cat'     => 'Resort News',
			'excerpt' => 'Focus on your health with our special yoga sessions and healthy meal plans all through May.',
			'image'   => 'lifestyle.webp',
		],
	];

	return apply_filters( 'cwc_blog_seed_dataset', $dataset );
}

/**
 * Insert a single seed row, skipping if a post with the same slug exists.
 *
 * @param array<string,mixed> $row     Seed row data.
 * @param array<string,int>   $cat_ids Category map.
 * @return bool True on success, false on skip.
 */
function cwc_blog_seed_insert_row( array $row, array $cat_ids ): bool {
	$title = (string) ( $row['title'] ?? '' );
	$cat   = (string) ( $row['cat'] ?? '' );

	if ( '' === $title || ! isset( $cat_ids[ $cat ] ) ) {
		return false;
	}

	$slug = sanitize_title( $title );
	if ( cwc_blog_post_exists_by_slug( $slug ) ) {
		return false;
	}

	$excerpt = (string) ( $row['excerpt'] ?? '' );
	$content = (string) ( $row['content'] ?? ( $excerpt . ' Lorem ipsum dolor sit amet, consectetur adipiscing elit.' ) );

	$post_id = wp_insert_post( [
		'post_title'    => $title,
		'post_name'     => $slug,
		'post_content'  => $content,
		'post_excerpt'  => $excerpt,
		'post_status'   => 'publish',
		'post_type'     => 'post',
		'post_category' => [ $cat_ids[ $cat ] ],
	] );

	if ( is_wp_error( $post_id ) || 0 === $post_id ) {
		return false;
	}

	if ( ! empty( $row['featured'] ) ) {
		update_post_meta( $post_id, CWC_BLOG_META_FEATURED, '1' );
	}

	if ( ! empty( $row['event_offset_days'] ) ) {
		$offset = max( 1, (int) $row['event_offset_days'] );
		$date   = gmdate( 'Y-m-d', strtotime( "+{$offset} days" ) );
		update_post_meta( $post_id, CWC_BLOG_META_EVENT_DATE, $date );
	}

	// Attach featured image
	$filename = (string) ( $row['image'] ?? '' );
	if ( $filename ) {
		$attach_id = cwc_get_attachment_id_by_filename( $filename );
		if ( $attach_id ) {
			set_post_thumbnail( $post_id, $attach_id );
		}
	}

	return true;
}

/**
 * Check if a post exists by slug.
 *
 * @param string $slug
 * @return bool
 */
function cwc_blog_post_exists_by_slug( string $slug ): bool {
	if ( '' === $slug ) {
		return false;
	}

	$query = new WP_Query( [
		'post_type'      => 'post',
		'post_status'    => 'any',
		'name'           => $slug,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	] );

	return $query->have_posts();
}

/**
 * Helper to get or create an attachment ID from a filename in the 2026/04 folder.
 *
 * @param string $filename
 * @return int
 */
function cwc_get_attachment_id_by_filename( $filename ) {
	global $wpdb;

	// Check if already an attachment in DB
	$target_path = '2026/04/' . ltrim( $filename, '/' );
	$attachment_id = $wpdb->get_var( $wpdb->prepare(
		"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value = %s",
		$target_path
	) );

	if ( $attachment_id ) {
		return (int) $attachment_id;
	}

	// If not in DB, check if file exists on disk and sideload it
	$upload_dir = wp_upload_dir();
	$file_path  = $upload_dir['basedir'] . '/' . $target_path;

	if ( ! file_exists( $file_path ) ) {
		return 0;
	}

	$file_type = wp_check_filetype( $filename, null );
	$attachment = [
		'post_mime_type' => $file_type['type'],
		'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
		'post_content'   => '',
		'post_status'    => 'inherit'
	];

	$attach_id = wp_insert_attachment( $attachment, $file_path );
	if ( ! is_wp_error( $attach_id ) ) {
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		return $attach_id;
	}

	return 0;
}
