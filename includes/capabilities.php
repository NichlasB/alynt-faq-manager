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
function alynt_faq_check_collection_permissions($taxonomy = 'alynt_faq_collection') {
    if (!current_user_can('manage_categories')) {
        wp_die(
            __('You do not have sufficient permissions to manage FAQ collections.', 'alynt-faq'),
            __('Permission Denied', 'alynt-faq'),
            array('response' => 403)
        );
    }
}
add_action('create_term', 'alynt_faq_check_collection_permissions', 10, 1);
add_action('edit_term', 'alynt_faq_check_collection_permissions', 10, 1);
add_action('delete_term', 'alynt_faq_check_collection_permissions', 10, 1);

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
register_activation_hook(ALYNT_FAQ_PLUGIN_DIR . 'alynt-faq-manager.php', 'alynt_faq_add_capabilities');

add_action('init', 'alynt_faq_add_admin_capabilities');
/**
 * Add custom FAQ capabilities to all existing administrator users on every init.
 *
 * Ensures administrators who existed before plugin activation also receive the
 * required capabilities without needing to reactivate the plugin.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_add_admin_capabilities() {
    $admins = get_users(['role' => 'administrator']);
    
    foreach ($admins as $admin) {
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
