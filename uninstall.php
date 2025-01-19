<?php
// If uninstall is not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all FAQ posts
$faq_posts = get_posts(array(
    'post_type' => 'alynt_faq',
    'numberposts' => -1,
    'post_status' => 'any'
));

foreach ($faq_posts as $post) {
    wp_delete_post($post->ID, true);
}

// Delete all FAQ collections
$terms = get_terms(array(
    'taxonomy' => 'alynt_faq_collection',
    'hide_empty' => false
));

if (!empty($terms) && !is_wp_error($terms)) {
    foreach ($terms as $term) {
        wp_delete_term($term->term_id, 'alynt_faq_collection');
    }
}

// Delete plugin options
delete_option('alynt_faq_version');

// Clear any cached data
wp_cache_flush();