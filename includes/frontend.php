<?php
/**
 * Frontend output hooks for Alynt FAQ Manager.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes/frontend
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue the single FAQ stylesheet on single FAQ pages.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_enqueue_single_template_assets() {
    if (is_singular('alynt_faq')) {
        wp_enqueue_style(
            'alynt-faq-single',
            ALYNT_FAQ_PLUGIN_URL . 'assets/css/single-faq.css',
            array(),
            ALYNT_FAQ_VERSION
        );
    }
}
add_action('wp_enqueue_scripts', 'alynt_faq_enqueue_single_template_assets');

function alynt_faq_enqueue_taxonomy_template_assets() {
    if (is_tax('alynt_faq_collection')) {
        wp_enqueue_style('alynt-faq-style');
        wp_enqueue_script('alynt-faq-script');
    }
}
add_action('wp_enqueue_scripts', 'alynt_faq_enqueue_taxonomy_template_assets', 15);

/**
 * Add saved custom FAQ CSS as inline styles on plugin stylesheet handles.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_add_inline_custom_css() {
    $custom_css = trim(alynt_faq_get_custom_css_option_value());

    if ('' === $custom_css) {
        return;
    }

    $sanitized_css = alynt_faq_sanitize_custom_css($custom_css);

    foreach (array('alynt-faq-style', 'alynt-faq-single') as $handle) {
        if (wp_style_is($handle, 'enqueued')) {
            wp_add_inline_style($handle, $sanitized_css);
        }
    }
}
add_action('wp_enqueue_scripts', 'alynt_faq_add_inline_custom_css', 20);