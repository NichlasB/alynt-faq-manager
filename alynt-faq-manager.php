<?php
/**
 * Plugin Name: Alynt FAQ Manager
 * Plugin URI: https://github.com/NichlasB/alynt-faq-manager
 * Description: A custom FAQ management system with collections, ordering, and responsive accordion display
 * Version: 1.1.0
 * Author: Alynt
 * Author URI: https://alynt.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * GitHub Plugin URI: NichlasB/alynt-faq-manager
 * Text Domain: alynt-faq
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 8.0
 *
 * @package Alynt_FAQ_Manager
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load plugin translation files.
 *
 * @since 1.0.6
 *
 * @return void
 */
function alynt_faq_load_textdomain() {
	load_plugin_textdomain(
		'alynt-faq',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages/'
	);
}
add_action( 'plugins_loaded', 'alynt_faq_load_textdomain' );

/**
 * Check and update plugin version on each load.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_check_version() {
	$current_version = get_option( 'alynt_faq_version', '0' );
	if ( version_compare( $current_version, ALYNT_FAQ_VERSION, '<' ) ) {
		if ( function_exists( 'alynt_faq_create_plugin_structure' ) ) {
			alynt_faq_create_plugin_structure();
		}

		if ( function_exists( 'alynt_faq_add_capabilities' ) ) {
			alynt_faq_add_capabilities();
		}

		if ( function_exists( 'alynt_faq_change_default_term_name' ) ) {
			alynt_faq_change_default_term_name();
		}

		// Update version in database.
		update_option( 'alynt_faq_version', ALYNT_FAQ_VERSION );

		// Clear any caches.
		wp_cache_flush();
	}
}
add_action( 'init', 'alynt_faq_check_version', 20 );

// Ensure no output has been sent before plugin initialization.
if ( ! defined( 'ALYNT_FAQ_LOADED' ) ) {
	define( 'ALYNT_FAQ_LOADED', true );

	// Define plugin constants.
	define( 'ALYNT_FAQ_VERSION', '1.1.0' );
	define( 'ALYNT_FAQ_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	define( 'ALYNT_FAQ_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

	/**
	 * Get CSS patterns that are considered unsafe for saved custom CSS.
	 *
	 * @since 1.0.7
	 *
	 * @return string[] List of disallowed CSS pattern fragments.
	 */
	function alynt_faq_get_unsafe_css_patterns() {
		return array(
			'expression',
			'javascript:',
			'behavior:',
			'-moz-binding',
			'@import',
			'data:',
		);
	}

	/**
	 * Normalize custom CSS input to a clean string value.
	 *
	 * @since 1.0.7
	 *
	 * @param mixed $custom_css Raw custom CSS value.
	 *
	 * @return string Normalized CSS string.
	 */
	function alynt_faq_normalize_custom_css( $custom_css ) {
		if ( ! is_string( $custom_css ) ) {
			return '';
		}

		$custom_css = wp_strip_all_tags( $custom_css );
		$custom_css = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $custom_css );

		return is_string( $custom_css ) ? $custom_css : '';
	}

	/**
	 * Check whether custom CSS contains blocked unsafe patterns.
	 *
	 * @since 1.0.7
	 *
	 * @param string $custom_css Custom CSS string.
	 *
	 * @return bool True when unsafe content is detected, otherwise false.
	 */
	function alynt_faq_has_unsafe_css_patterns( $custom_css ) {
		if ( '' === $custom_css ) {
			return false;
		}

		foreach ( alynt_faq_get_unsafe_css_patterns() as $pattern ) {
			if ( stripos( $custom_css, $pattern ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the saved custom CSS option as a string.
	 *
	 * @since 1.0.7
	 *
	 * @return string Saved custom CSS value.
	 */
	function alynt_faq_get_custom_css_option_value() {
		$custom_css = get_option( 'alynt_faq_custom_css', '' );

		if ( is_string( $custom_css ) ) {
			return $custom_css;
		}

		if ( null === $custom_css || false === $custom_css ) {
			return '';
		}

		if ( is_scalar( $custom_css ) ) {
			return (string) $custom_css;
		}

		return '';
	}

	/**
	 * Generate a version hash for a custom CSS string.
	 *
	 * @since 1.0.7
	 *
	 * @param string|null $custom_css Optional CSS string to hash.
	 *
	 * @return string Hash representing the CSS content.
	 */
	function alynt_faq_get_custom_css_version( $custom_css = null ) {
		if ( null === $custom_css ) {
			$custom_css = alynt_faq_get_custom_css_option_value();
		}

		return md5( (string) $custom_css );
	}

	/**
	 * Sanitize custom CSS by normalizing it and rejecting unsafe patterns.
	 *
	 * @since 1.0.7
	 *
	 * @param mixed $custom_css Raw custom CSS value.
	 *
	 * @return string Safe CSS string, or an empty string when rejected.
	 */
	function alynt_faq_sanitize_custom_css( $custom_css ) {
		$custom_css = alynt_faq_normalize_custom_css( $custom_css );

		if ( '' === $custom_css ) {
			return '';
		}

		if ( alynt_faq_has_unsafe_css_patterns( $custom_css ) ) {
			return '';
		}

		return $custom_css;
	}

	// Include required files (universal).
	require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/shared/cache.php';
	require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/post-type-registration.php';
	require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/capabilities.php';

	if ( is_admin() ) {
		require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/admin/post-type-columns.php';
		require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/admin/assets.php';
		require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/admin/reorder-page.php';
		require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/admin/custom-css-page.php';
	} else {
		require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/frontend/collection-renderer.php';
		require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/frontend/shortcode.php';
		require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/class-alynt-faq-template-loader.php';
		require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/frontend/theme-hooks.php';
		require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/frontend.php';
	}

	require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/class-alynt-faq-activator.php';
	require_once ALYNT_FAQ_PLUGIN_DIR . 'includes/class-alynt-faq-deactivator.php';

	register_activation_hook( __FILE__, array( 'ALYNT_FAQ_Activator', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'ALYNT_FAQ_Deactivator', 'deactivate' ) );

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
		alynt_faq_create_plugin_structure();
		alynt_faq_register_post_type_and_taxonomy();
		alynt_faq_change_default_term_name();
		alynt_faq_add_capabilities();

		// Create necessary database options.
		update_option( 'alynt_faq_version', ALYNT_FAQ_VERSION );

		// Flush rewrite rules.
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
		// Cleanup temporary data if needed.
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
	function alynt_faq_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . esc_url( admin_url( 'edit.php?post_type=alynt_faq' ) ) . '">' . esc_html__( 'FAQs', 'alynt-faq' ) . '</a>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=alynt-faq-order' ) ) . '">' . esc_html__( 'Reorder FAQs', 'alynt-faq' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'alynt_faq_action_links' );

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
			ALYNT_FAQ_PLUGIN_DIR . 'templates',
		);

		foreach ( $directories as $directory ) {
			if ( ! file_exists( $directory ) ) {
				if ( ! wp_mkdir_p( $directory ) ) {
					return;
				}
			}
		}
	}
}
