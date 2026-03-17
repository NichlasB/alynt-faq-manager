<?php
/**
 * Custom capability registration and permission enforcement for the FAQ post type.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check permissions before allowing collection management.
 *
 * Kills the request with a 403 error if the current user cannot manage categories.
 *
 * @since 1.0.0
 *
 * @param string $taxonomy The taxonomy being modified.
 *
 * @return void
 */
function alynt_faq_check_collection_permissions($term_id = 0, $tt_id = 0, $taxonomy = '') {
    if ('alynt_faq_collection' !== $taxonomy) {
        return;
    }

    if (!current_user_can('manage_categories')) {
        wp_die(
            __('You do not have sufficient permissions to manage FAQ collections.', 'alynt-faq'),
            __('Permission Denied', 'alynt-faq'),
            array('response' => 403, 'back_link' => true)
        );
    }
}
add_action('create_term', 'alynt_faq_check_collection_permissions', 10, 3);
add_action('edit_term', 'alynt_faq_check_collection_permissions', 10, 3);
add_action('delete_term', 'alynt_faq_check_collection_permissions', 10, 3);

/**
 * Add custom FAQ capabilities to the administrator role on plugin activation.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_add_capabilities() {
    $admin = get_role('administrator');
    
    if ($admin) {
        $admin->add_cap('edit_alynt_faq');
        $admin->add_cap('read_alynt_faq');
        $admin->add_cap('delete_alynt_faq');
        $admin->add_cap('edit_alynt_faqs');
        $admin->add_cap('edit_others_alynt_faqs');
        $admin->add_cap('publish_alynt_faqs');
        $admin->add_cap('read_private_alynt_faqs');
        $admin->add_cap('delete_alynt_faqs');
        $admin->add_cap('delete_private_alynt_faqs');
        $admin->add_cap('delete_published_alynt_faqs');
        $admin->add_cap('delete_others_alynt_faqs');
        $admin->add_cap('edit_private_alynt_faqs');
        $admin->add_cap('edit_published_alynt_faqs');
    }
}
