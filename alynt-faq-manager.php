<?php
/**
 * Plugin Name: Alynt FAQ Manager
 * Plugin URI: https://github.com/NichlasB/alynt-faq-manager
 * Description: A custom FAQ management system with collections, ordering, and responsive accordion display
 * Version: 1.0.4
 * Author: Alynt
 * Author URI: https://alynt.com
 * Text Domain: alynt-faq
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 8.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin Update Checker
require_once __DIR__ . '/vendor/autoload.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

if (class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
    $myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/NichlasB/alynt-faq-manager',
    __FILE__,
    'alynt-faq-manager'
);

    // Set the branch that contains the stable release.
    $myUpdateChecker->setBranch('main');
    
    // Optional: If you're using releases, you can set the checker to use them.
    $myUpdateChecker->getVcsApi()->enableReleaseAssets();
}

/**
 * Check and update plugin version
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
    define('ALYNT_FAQ_VERSION', '1.0.4');
    define('ALYNT_FAQ_PLUGIN_DIR', plugin_dir_path(__FILE__));
    define('ALYNT_FAQ_PLUGIN_URL', plugin_dir_url(__FILE__));

    // Include required files
    require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/post-types.php';
    require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/admin/admin-page.php';
    require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/shortcodes.php';
    require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/templates.php';
    require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/frontend.php';

    // Register activation/deactivation hooks
    register_activation_hook(__FILE__, 'alynt_faq_activate');
    register_deactivation_hook(__FILE__, 'alynt_faq_deactivate');

    /**
     * Plugin activation callback
     */
    function alynt_faq_activate() {
        // Create necessary database tables and options
        add_option('alynt_faq_version', ALYNT_FAQ_VERSION);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation callback
     */
    function alynt_faq_deactivate() {
        // Cleanup temporary data if needed
        flush_rewrite_rules();
    }

    /**
     * Add plugin action links
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
     * Create plugin directory structure
     */
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

    /**
 * Enqueue archive styles only on FAQ archive page
 */
    function alynt_faq_enqueue_archive_styles() {
        if (is_post_type_archive('alynt_faq')) {
            wp_enqueue_style(
                'alynt-faq-archive',
                ALYNT_FAQ_PLUGIN_URL . 'assets/css/archive-faq.css',
                array(),
                ALYNT_FAQ_VERSION
            );
        }
    }
    add_action('wp_enqueue_scripts', 'alynt_faq_enqueue_archive_styles');

    // Initialize plugin
    function alynt_faq_init() {
        alynt_faq_create_plugin_structure();
    }
    add_action('init', 'alynt_faq_init');
}