<?php
/**
 * Template loader for Alynt FAQ Manager.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Locates and loads theme-overridable templates for the FAQ post type and taxonomy.
 *
 * Theme developers can override plugin templates by placing files in
 * their-theme/alynt-faq/ directory.
 *
 * @package Alynt_FAQ_Manager
 * @since   1.0.0
 */
class Alynt_FAQ_Template_Loader {
	/**
	 * Constructor. Hooks template filters into WordPress.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'template_include', array( $this, 'template_loader' ) );
		add_filter( 'single_template', array( $this, 'load_single_template' ), 20 );
	}

	/**
	 * Filter the template file for FAQ collection taxonomy archives.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template Absolute path to the template file WordPress resolved.
	 *
	 * @return string Path to the located template, or the original path if not overridden.
	 */
	public function template_loader( $template ) {
		if ( is_tax( 'alynt_faq_collection' ) ) {
			$default_file = 'taxonomy-alynt-faq-collection.php';
			$new_template = $this->locate_template( $default_file );
			return ( $new_template ) ? $new_template : $template;
		}
		return $template;
	}

	/**
	 * Load the plugin's single FAQ template for singular alynt_faq views.
	 *
	 * Theme templates take precedence; the plugin template is used as fallback.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template Absolute path to the template file WordPress resolved.
	 *
	 * @return string Path to the plugin single template if it exists, otherwise the original path.
	 */
	public function load_single_template( $template ) {
		if ( is_singular( 'alynt_faq' ) ) {
			$plugin_template = ALYNT_FAQ_PLUGIN_DIR . 'templates/single-alynt-faq.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}
		return $template;
	}

	/**
	 * Locate a template file, checking the theme before falling back to the plugin.
	 *
	 * Applies the 'alynt_faq_locate_template' filter to allow third-party overrides.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template_name Filename of the template to locate (e.g. 'single-alynt_faq.php').
	 *
	 * @return string Absolute path to the located template file, or empty string if not found.
	 */
	public function locate_template( $template_name ) {
		$template = '';

		$theme_template = locate_template(
			array(
				"alynt-faq/{$template_name}",
				$template_name,
			)
		);

		if ( $theme_template ) {
			$template = $theme_template;
		} else {
			$plugin_template = ALYNT_FAQ_PLUGIN_DIR . 'templates/' . $template_name;
			if ( file_exists( $plugin_template ) ) {
				$template = $plugin_template;
			}
		}

		return apply_filters( 'alynt_faq_locate_template', $template, $template_name );
	}
}

$GLOBALS['alynt_faq_template_loader'] = new Alynt_FAQ_Template_Loader();
