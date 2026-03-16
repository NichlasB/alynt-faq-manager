<?php
/**
 * Plugin Name: Alynt FAQ Manager
 * Plugin URI: https://github.com/NichlasB/alynt-faq-manager
 * Description: A custom FAQ management system with collections, ordering, and responsive accordion display
 * Version: 1.0.5
 * Author: Alynt
 * Author URI: https://alynt.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * GitHub Plugin URI: NichlasB/alynt-faq-manager
 * Text Domain: alynt-faq
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 8.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check and update plugin version on each load.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_check_version() {
    $current_version = get_option('alynt_faq_version', '0');
    if (version_compare($current_version, ALYNT_FAQ_VERSION, '<')) {
        // Run upgrade routine if needed

        // Update version in database
        update_option('alynt_faq_version', ALYNT_FAQ_VERSION);
        
        // Clear any caches
        wp_cache_flush();
    }
}
add_action('plugins_loaded', 'alynt_faq_check_version');

// Ensure no output has been sent before plugin initialization
if (!defined('ALYNT_FAQ_LOADED')) {
    define('ALYNT_FAQ_LOADED', true);
    
    // Define plugin constants
    define('ALYNT_FAQ_VERSION', '1.0.5');
    define('ALYNT_FAQ_PLUGIN_DIR', plugin_dir_path(__FILE__));
    define('ALYNT_FAQ_PLUGIN_URL', plugin_dir_url(__FILE__));

    // Include required files
    require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/shared/cache.php';
    require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/post-type-registration.php';
    require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/admin/post-type-columns.php';
    require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/capabilities.php';
    require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/admin/assets.php';
    require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/admin/reorder-page.php';
    require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/admin/custom-css-page.php';
    require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/frontend/collection-renderer.php';
    require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/frontend/shortcode.php';
    require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/class-alynt-faq-template-loader.php';
    require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/frontend/theme-hooks.php';
    require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/frontend.php';

    // Register activation/deactivation hooks
    register_activation_hook(__FILE__, 'alynt_faq_activate');
    register_deactivation_hook(__FILE__, 'alynt_faq_deactivate');

    /**
     * Plugin activation callback.
     *
     * Runs on plugin activation: sets initial version option and flushes rewrite rules.
     *
     * @since 1.0.0
     *
     * @return void
     */
    function alynt_faq_activate() {
        // Create necessary database tables and options
        add_option('alynt_faq_version', ALYNT_FAQ_VERSION);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation callback.
     *
     * Flushes rewrite rules on deactivation.
     *
     * @since 1.0.0
     *
     * @return void
     */
    function alynt_faq_deactivate() {
        // Cleanup temporary data if needed
        flush_rewrite_rules();
    }

    /**
     * Add plugin action links to the plugins list table.
     *
     * @since 1.0.0
     *
     * @param array $links Existing action links.
     *
     * @return array Modified action links with FAQs and Reorder entries prepended.
     */
    function alynt_faq_action_links($links) {
        $plugin_links = array(
            '<a href="' . admin_url('edit.php?post_type=alynt_faq') . '">FAQs</a>',
            '<a href="' . admin_url('admin.php?page=alynt-faq-order') . '">Reorder FAQs</a>'
        );
        return array_merge($plugin_links, $links);
    }
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'alynt_faq_action_links');

    /**
     * Create plugin directory structure if directories do not exist.
     *
     * @since 1.0.0
     *
     * @return void
     */
    function alynt_faq_create_plugin_structure() {
        $directories = array(
            ALYNT_FAQ_PLUGIN_DIR . 'includes',
            ALYNT_FAQ_PLUGIN_DIR . 'includes/admin',
            ALYNT_FAQ_PLUGIN_DIR . 'includes/frontend',
            ALYNT_FAQ_PLUGIN_DIR . 'includes/shared',
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

    /**
     * Initialize the plugin.
     *
     * @since 1.0.0
     *
     * @return void
     */
    function alynt_faq_init() {
        alynt_faq_create_plugin_structure();
    }
    add_action('init', 'alynt_faq_init');
}