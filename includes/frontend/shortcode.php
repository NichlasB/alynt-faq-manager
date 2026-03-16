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

/**
 * Enqueue the frontend FAQ stylesheet and JavaScript.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_enqueue_frontend_assets() {
    wp_enqueue_style('alynt-faq-style', ALYNT_FAQ_PLUGIN_URL . 'assets/css/frontend.css', array(), ALYNT_FAQ_VERSION);
    wp_enqueue_script('alynt-faq-script', ALYNT_FAQ_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), ALYNT_FAQ_VERSION, true);
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
    $atts = shortcode_atts(array(
        'collection-columns' => '1',
        'close-opened' => 'no',
        'collection' => '',
        'orderby' => 'menu_order'
    ), $atts, 'alynt_faq');

    $atts['collection-columns'] = min(max(absint($atts['collection-columns']), 1), 2);
    $atts['close-opened'] = $atts['close-opened'] === 'yes' ? 'yes' : 'no';
    $atts['orderby'] = in_array($atts['orderby'], array('date', 'abc', 'menu_order')) ? $atts['orderby'] : 'menu_order';

    return apply_filters('alynt_faq_shortcode_atts', $atts);
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
 * Retrieve collection taxonomy terms matching the shortcode attribute, with transient caching.
 *
 * @since 1.0.0
 *
 * @param array $atts Normalized shortcode attributes.
 *
 * @return WP_Term[]|WP_Error Array of term objects, or WP_Error on failure.
 */
function alynt_faq_get_collection_terms($atts) {
    $cache_key = 'alynt_faq_collections_' . md5(serialize($atts));
    $collection_terms = wp_cache_get($cache_key);

    if (false !== $collection_terms) {
        return $collection_terms;
    }

    if (!empty($atts['collection'])) {
        $collection_slugs = array_map('trim', explode(',', $atts['collection']));
        $collection_terms = get_terms(array(
            'taxonomy' => 'alynt_faq_collection',
            'slug' => $collection_slugs,
            'hide_empty' => true
        ));
    } else {
        $collection_terms = get_terms(array(
            'taxonomy' => 'alynt_faq_collection',
            'hide_empty' => true
        ));
    }

    if (!is_wp_error($collection_terms)) {
        wp_cache_set($cache_key, $collection_terms, '', HOUR_IN_SECONDS);
    }

    return $collection_terms;
}

/**
 * Render the [alynt_faq] shortcode output.
 *
 * Enqueues frontend assets, resolves collections, and returns the full accordion HTML.
 *
 * @since 1.0.0
 *
 * @param array|string $atts Shortcode attributes.
 *
 * @return string The rendered shortcode HTML.
 */
function alynt_faq_shortcode($atts) {
    alynt_faq_enqueue_frontend_assets();
    $atts = alynt_faq_normalize_shortcode_attributes($atts);
    $collection_terms = alynt_faq_get_collection_terms($atts);

    ob_start();

    echo '<a href="#faq-content" class="screen-reader-text">Skip to FAQ Content</a>';
    echo '<style>.alynt-faq-container{opacity:0}</style>';
    echo '<div class="' . esc_attr(implode(' ', alynt_faq_get_shortcode_container_classes($atts))) . '" id="faq-content">';

    if (!empty($collection_terms) && !is_wp_error($collection_terms)) {
        foreach ($collection_terms as $collection) {
            echo alynt_faq_render_collection($collection, $atts['orderby']);
        }
    } else {
        echo '<p class="alynt-faq-no-results">No FAQs found.</p>';
    }

    echo '</div>';

    return ob_get_clean();
}
