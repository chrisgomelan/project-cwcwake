<?php
require_once( '../../../../wp-load.php' );
echo "Migrated: " . (get_option('cwc_accommodation_migrated') ? 'YES' : 'NO') . "\n";
echo "Flushed: " . (get_option('cwc_accommodation_rewrites_flushed') ? 'YES' : 'NO') . "\n";
echo "Flushed V: " . get_option('cwc_accommodation_rewrites_flushed_v') . "\n";
echo "Blog Seeded: " . (get_option('cwc_blog_posts_seeded') ? 'YES' : 'NO') . "\n";
echo "Cleared V1: " . (get_option('cwc_accommodation_post_content_cleared_v1') ? 'YES' : 'NO') . "\n";
