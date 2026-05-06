<?php
require_once dirname(__DIR__) . '/wp-load.php';

$pages = get_pages(['hierarchical' => 0]);
foreach ($pages as $p) {
    echo $p->ID . ' | ' . $p->post_title . ' | ' . $p->post_name . ' | Parent: ' . $p->post_parent . ' | Template: ' . get_post_meta($p->ID, '_wp_page_template', true) . "\n";
}
