<?php
/**
 * Shortcode registration and frontend asset enqueueing for the FAQ accordion.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes/frontend
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register shortcode
add_shortcode('alynt_faq', 'alynt_faq_shortcode');
add_action('wp_enqueue_scripts', 'alynt_faq_register_frontend_assets');

/**
 * Register the frontend FAQ stylesheet and JavaScript.
 *
 * Assets are registered early so they can be enqueued on demand when
 * the [alynt_faq] shortcode is rendered, avoiding unnecessary loading
 * on pages that do not use the shortcode.
 *
 * @since 1.0.6
 *
 * @return void
 */
function alynt_faq_register_frontend_assets() {
    wp_register_style('alynt-faq-style', ALYNT_FAQ_PLUGIN_URL . 'assets/css/frontend.css', array(), ALYNT_FAQ_VERSION);
    wp_register_script('alynt-faq-script', ALYNT_FAQ_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), ALYNT_FAQ_VERSION, true);
}

/**
 * Parse, validate, and normalize the [alynt_faq] shortcode attributes.
 *
 * Applies the 'alynt_faq_shortcode_atts' filter to allow third-party modification
 * of the resolved attribute values.
 *
 * @since 1.0.0
 *
 * @param array|string $atts Raw shortcode attributes.
 *
 * @return array Normalized attribute array with keys: collection-columns, close-opened, collection, orderby.
 */
function alynt_faq_normalize_shortcode_attributes($atts) {
    $defaults = array(
        'collection-columns' => '1',
        'close-opened' => 'no',
        'collection' => '',
        'orderby' => 'menu_order'
    );

    $atts = shortcode_atts($defaults, $atts, 'alynt_faq');
    $atts = apply_filters('alynt_faq_shortcode_atts', $atts);

    if (!is_array($atts)) {
        $atts = array();
    }

    $atts = shortcode_atts($defaults, $atts, 'alynt_faq');

    $atts['collection-columns'] = min(max(absint($atts['collection-columns']), 1), 2);
    $atts['close-opened'] = $atts['close-opened'] === 'yes' ? 'yes' : 'no';
    $atts['orderby'] = in_array($atts['orderby'], array('date', 'abc', 'menu_order')) ? $atts['orderby'] : 'menu_order';

    return $atts;
}

/**
 * Build the CSS class list for the shortcode outer container element.
 *
 * @since 1.0.0
 *
 * @param array $atts Normalized shortcode attributes.
 *
 * @return string[] Array of CSS class names.
 */
function alynt_faq_get_shortcode_container_classes($atts) {
    return array(
        'alynt-faq-container',
        'columns-' . $atts['collection-columns'],
        'close-opened-' . $atts['close-opened']
    );
}

/**
 * Build the fallback "no FAQs found" message markup.
 *
 * @since 1.0.6
 *
 * @return string HTML markup for empty shortcode results.
 */
function alynt_faq_get_no_results_markup() {
    return '<p class="alynt-faq-no-results">' . esc_html__('No FAQs found.', 'alynt-faq') . '</p>';
}

/**
 * Retrieve collection taxonomy terms matching the shortcode attribute, with transient caching.
 *
 * @since 1.0.0
 *
 * @param array $atts Normalized shortcode attributes.
 *
 * @return WP_Term[]|WP_Error Array of term objects, or WP_Error on failure.
 */
function alynt_faq_get_collection_terms($atts) {
    $cache_key = alynt_faq_get_collection_cache_key($atts);
    $collection_terms = get_transient($cache_key);

    if (false !== $collection_terms) {
        return $collection_terms;
    }

    if (!empty($atts['collection'])) {
        $collection_slugs = array_filter(array_map('sanitize_title', array_map('trim', explode(',', (string) $atts['collection']))));

        if (empty($collection_slugs)) {
            $collection_terms = array();
        } else {
            $collection_terms = get_terms(array(
                'taxonomy' => 'alynt_faq_collection',
                'slug' => $collection_slugs,
                'hide_empty' => true
            ));
        }
    } else {
        $collection_terms = get_terms(array(
            'taxonomy' => 'alynt_faq_collection',
            'hide_empty' => true
        ));
    }

    if (is_wp_error($collection_terms)) {
        error_log('[Alynt FAQ Manager] Failed to load FAQ collections for shortcode: ' . $collection_terms->get_error_message());
    } else {
        set_transient($cache_key, $collection_terms, HOUR_IN_SECONDS);
    }

    return $collection_terms;
}

/**
 * Render the [alynt_faq] shortcode output.
 *
 * Resolves collections and returns the full accordion HTML.
 *
 * @since 1.0.0
 *
 * @param array|string $atts Shortcode attributes.
 *
 * @return string The rendered shortcode HTML.
 */
function alynt_faq_shortcode($atts) {
    wp_enqueue_style('alynt-faq-style');
    wp_enqueue_script('alynt-faq-script');

    if (function_exists('alynt_faq_attach_inline_custom_css')) {
        alynt_faq_attach_inline_custom_css('alynt-faq-style');
    }

    $atts = alynt_faq_normalize_shortcode_attributes($atts);
    $collection_terms = alynt_faq_get_collection_terms($atts);

    ob_start();

    echo '<a href="#faq-content" class="screen-reader-text">' . esc_html__('Skip to FAQ Content', 'alynt-faq') . '</a>';
    echo '<div class="' . esc_attr(implode(' ', alynt_faq_get_shortcode_container_classes($atts))) . '" id="faq-content">';

    if (is_wp_error($collection_terms)) {
        echo '<p class="alynt-faq-no-results">' . esc_html__('FAQs could not be loaded right now. Please try again later.', 'alynt-faq') . '</p>';
    } elseif (!empty($collection_terms)) {
        $has_output = false;

        foreach ($collection_terms as $collection) {
            $collection_output = alynt_faq_render_collection($collection, $atts['orderby']);

            if ('' !== $collection_output) {
                $has_output = true;
                echo $collection_output;
            }
        }

        if (!$has_output) {
            echo alynt_faq_get_no_results_markup();
        }
    } else {
        echo alynt_faq_get_no_results_markup();
    }

    echo '</div>';

    return ob_get_clean();
}
