<?php
/**
 * Plugin Name: Alynt FAQ Manager
 * Plugin URI: 
 * Description: A custom FAQ management system with collections, ordering, and responsive accordion display
 * Version: 1.0.0
 * Author: 
 * Author URI: 
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ALYNT_FAQ_VERSION', '1.0.0');
define('ALYNT_FAQ_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ALYNT_FAQ_PLUGIN_URL', plugin_dir_url(__FILE__));

// Register activation hook
register_activation_hook(__FILE__, 'alynt_faq_activate');

function alynt_faq_activate() {
    // Flush rewrite rules on activation
    flush_rewrite_rules();
}

// Register deactivation hook
register_deactivation_hook(__FILE__, 'alynt_faq_deactivate');

function alynt_faq_deactivate() {
    // Flush rewrite rules on deactivation
    flush_rewrite_rules();
}

// Include required files
require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/post-types.php';
require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/admin/admin-page.php';
require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/shortcodes.php';
require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/templates.php';

// Add plugin action links
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'alynt_faq_action_links');

function alynt_faq_action_links($links) {
    $plugin_links = array(
        '<a href="' . admin_url('edit.php?post_type=alynt_faq') . '">FAQs</a>',
        '<a href="' . admin_url('admin.php?page=alynt-faq-order') . '">Reorder FAQs</a>'
    );
    return array_merge($plugin_links, $links);
}

// Create plugin directory structure on activation
function alynt_faq_create_plugin_structure() {
    $directories = array(
        ALYNT_FAQ_PLUGIN_DIR . 'includes',
        ALYNT_FAQ_PLUGIN_DIR . 'includes/admin',
        ALYNT_FAQ_PLUGIN_DIR . 'assets',
        ALYNT_FAQ_PLUGIN_DIR . 'assets/css',
        ALYNT_FAQ_PLUGIN_DIR . 'assets/js',
        ALYNT_FAQ_PLUGIN_DIR . 'templates'
    );

    foreach ($directories as $directory) {
        if (!file_exists($directory)) {
            wp_mkdir_p($directory);
        }
    }
}

// Run directory creation on activation
register_activation_hook(__FILE__, 'alynt_faq_create_plugin_structure');