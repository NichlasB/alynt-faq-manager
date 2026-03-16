<?php
/**
 * Transient cache management for FAQ collections.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes/shared
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clear all collection transient caches when FAQs or collections are modified.
 *
 * Deletes all transients whose names match the alynt_faq_collections_ prefix
 * directly via a targeted SQL query to avoid loading every transient into memory.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_clear_collection_cache() {
    global $wpdb;
    
    // Delete only our specific transients
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '%' . $wpdb->esc_like('_transient_alynt_faq_collections_') . '%'
        )
    );
}

// Clear cache when FAQs or collections are modified
add_action('save_post_alynt_faq', 'alynt_faq_clear_collection_cache');
add_action('edited_alynt_faq_collection', 'alynt_faq_clear_collection_cache');
add_action('created_alynt_faq_collection', 'alynt_faq_clear_collection_cache');
add_action('deleted_alynt_faq_collection', 'alynt_faq_clear_collection_cache');
