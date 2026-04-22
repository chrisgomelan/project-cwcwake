<?php
/**
 * CWC Accommodations — Blog Post Seeder
 *
 * Programmatically generates sample blog posts to test the blog layouts.
 *
 * @package CWC_Accommodations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seed sample blog posts and categories.
 *
 * @return int Number of posts created.
 */
function cwc_seed_blog_posts() {
	$categories = [ 'Events', 'Resort News', 'Pro Tips', 'Local Guide' ];
	$cat_ids    = [];

	// 1. Create Categories
	foreach ( $categories as $cat_name ) {
		$term = get_term_by( 'name', $cat_name, 'category' );
		if ( ! $term ) {
			$inserted = wp_insert_term( $cat_name, 'category' );
			$cat_ids[ $cat_name ] = is_wp_error( $inserted ) ? 0 : $inserted['term_id'];
		} else {
			$cat_ids[ $cat_name ] = $term->term_id;
		}
	}

	$posts_to_seed = [
		// Featured Grid Posts (Need 5)
		[
			'title'   => 'Experience the Thrill: Pro Wakeboarding Tips',
			'cat'     => 'Pro Tips',
			'excerpt' => 'Master the basics and advanced tricks with our comprehensive guide to wakeboarding at CWC.',
		],
		[
			'title'   => 'CWC Night Jam: Upcoming Event this April',
			'cat'     => 'Events',
			'excerpt' => 'Join us for a night of music, lights, and non-stop cable action under the stars.',
		],
		[
			'title'   => 'New Luxury Villas Now Open for Booking',
			'cat'     => 'Resort News',
			'excerpt' => 'Experience a new level of comfort with our newly launched premium villas overlooking the park.',
		],
		[
			'title'   => 'Top 5 Hidden Spots in CamSur',
			'cat'     => 'Local Guide',
			'excerpt' => 'Explore the natural beauty of Camarines Sur beyond the wakeboarding park.',
		],
		[
			'title'   => 'A Beginner’s Guide to Cable Parks',
			'cat'     => 'Pro Tips',
			'excerpt' => 'First time at a cable park? Here is everything you need to know before you hit the water.',
		],
		// Upcoming Events section (Needs at least 3)
		[
			'title'   => 'Summer Wake Championship 2026',
			'cat'     => 'Events',
			'excerpt' => 'The biggest competition of the year is coming back. Register now to compete.',
		],
		[
			'title'   => 'Resort Wellness Month',
			'cat'     => 'Resort News',
			'excerpt' => 'Focus on your health with our special yoga sessions and healthy meal plans all through May.',
		],
		[
			'title'   => 'Evening Acoustic Sessions at the Bar',
			'cat'     => 'Events',
			'excerpt' => 'Unwind after a day on the water with live acoustic performances every Friday.',
		],
		// Filling up "All Blogs" for pagination
		[ 'title' => 'Sustainable Tourism at CWC', 'cat' => 'Resort News', 'excerpt' => 'How we are working to protect our local environment.' ],
		[ 'title' => 'CWC Gear Guide: Part 1', 'cat' => 'Pro Tips', 'excerpt' => 'Choosing the right board for your style.' ],
		[ 'title' => 'Traveling to CamSur: Tips & Tricks', 'cat' => 'Local Guide', 'excerpt' => 'The easiest ways to get to CWC from Manila.' ],
		[ 'title' => 'The History of CWC Wake Park', 'cat' => 'Resort News', 'excerpt' => 'From a dream to a world-class destination.' ],
	];

	$count = 0;
	foreach ( $posts_to_seed as $p ) {
		// Avoid duplicates by checking title
		$existing = get_page_by_title( $p['title'], OBJECT, 'post' );
		if ( $existing ) {
			continue;
		}

		$post_id = wp_insert_post( [
			'post_title'   => $p['title'],
			'post_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' . $p['excerpt'],
			'post_excerpt' => $p['excerpt'],
			'post_status'  => 'publish',
			'post_type'    => 'post',
			'post_category' => [ $cat_ids[ $p['cat'] ] ]
		] );

		if ( $post_id ) {
			$count++;
			// Try to set a placeholder featured image if possible, 
			// or just leave it for the user to pick.
		}
	}

	return $count;
}
