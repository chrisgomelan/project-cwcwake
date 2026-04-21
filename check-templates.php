<?php
require_once dirname(__DIR__) . '/wp-load.php';

$accommodations = get_page_by_path('accommodations');
if ($accommodations) {
    echo "Accommodations Page ID: " . $accommodations->ID . "\n";
    $children = get_pages(['child_of' => $accommodations->ID]);
    foreach ($children as $child) {
        $template = get_post_meta($child->ID, '_wp_page_template', true);
        echo "Page: " . $child->post_title . " (Slug: " . $child->post_name . ") - Template: " . $template . "\n";
    }
} else {
    echo "Accommodations page not found.\n";
    // Check all pages just in case
    $query = new WP_Query([
        'post_type' => 'page',
        'posts_per_page' => -1
    ]);
    foreach ($query->posts as $page) {
        $template = get_post_meta($page->ID, '_wp_page_template', true);
        if ($template === 'page-room-detail') {
             echo "Room Detail Page Found: " . $page->post_title . " (Slug: " . $page->post_name . ")\n";
        }
    }
}
