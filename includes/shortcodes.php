<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register shortcode
add_shortcode('alynt_faq', 'alynt_faq_shortcode');

function alynt_faq_shortcode($atts) {
    // Enqueue necessary scripts and styles
    wp_enqueue_style('alynt-faq-style', ALYNT_FAQ_PLUGIN_URL . 'assets/css/frontend.css', array(), ALYNT_FAQ_VERSION);
    wp_enqueue_script('alynt-faq-script', ALYNT_FAQ_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), ALYNT_FAQ_VERSION, true);

    // Localize script
    wp_localize_script('alynt-faq-script', 'alyntFaq', array(
        'closeOpened' => 'no' // Default value as per requirement
    ));

    // Parse shortcode attributes
    $atts = shortcode_atts(array(
        'collection-columns' => '1',
        'close-opened' => 'no',
        'collection' => '',
        'orderby' => 'menu_order'
    ), $atts, 'alynt_faq');

    // Sanitize attributes
    $columns = absint($atts['collection-columns']);
    $columns = min(max($columns, 1), 2); // Ensure columns is between 1 and 2
    $close_opened = $atts['close-opened'] === 'yes' ? 'yes' : 'no';
    $orderby = in_array($atts['orderby'], array('date', 'abc', 'menu_order')) ? $atts['orderby'] : 'menu_order';

    // Start output buffering
    ob_start();

    // Add skip link for accessibility
    echo '<a href="#faq-content" class="screen-reader-text">Skip to FAQ Content</a>';

    // Container classes based on attributes
    $container_classes = array(
        'alynt-faq-container',
        'columns-' . $columns,
        'close-opened-' . $close_opened
    );

    echo '<style>.alynt-faq-container{opacity:0}</style>';

    echo '<div class="' . esc_attr(implode(' ', $container_classes)) . '" id="faq-content">';

    // Get collections
    $collection_terms = array();
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

    if (!empty($collection_terms) && !is_wp_error($collection_terms)) {
        foreach ($collection_terms as $collection) {
            echo alynt_faq_render_collection($collection, $orderby);
        }
    } else {
        echo '<p class="alynt-faq-no-results">No FAQs found.</p>';
    }

    echo '</div>'; // Close container

    // Return the buffered content
    return ob_get_clean();
}

function alynt_faq_render_collection($collection, $orderby) {
    $args = array(
        'post_type' => 'alynt_faq',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'alynt_faq_collection',
                'field' => 'term_id',
                'terms' => $collection->term_id
            )
        )
    );

    // Set ordering
    switch ($orderby) {
        case 'date':
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
        break;
        case 'abc':
        $args['orderby'] = 'title';
        $args['order'] = 'ASC';
        break;
        default: // menu_order
        $args['orderby'] = 'menu_order';
        $args['order'] = 'ASC';
    }

    $faqs = new WP_Query($args);

    if (!$faqs->have_posts()) {
        return '';
    }

    $output = '<div class="alynt-faq-collection">';
    
   // Collection header
    $output .= sprintf(
        '<div class="collection-header">
        <h2 class="collection-title">%s</h2>
        <div class="collection-controls">
        <button class="expand-all" aria-expanded="false">
        Expand All
        </button>
        <button class="collapse-all" aria-expanded="true">
        Collapse All
        </button>
        </div>
        </div>',
        esc_html($collection->name)
    );

    // FAQ items
    $output .= '<div class="faq-items">';
    
    while ($faqs->have_posts()) {
        $faqs->the_post();
        $post_link = get_permalink();
        
        // In the FAQ item output section, modify the structure:
        $output .= sprintf(
            '<div class="faq-item">
            <div class="faq-header">
            <button class="faq-question" aria-expanded="false" aria-controls="faq-%1$s">
            <svg class="icon-plus" aria-hidden="true" viewBox="0 0 24 24">
            <path d="M24 10h-10v-10h-4v10h-10v4h10v10h4v-10h10z"/>
            </svg>
            <svg class="icon-minus" aria-hidden="true" viewBox="0 0 24 24">
            <path d="M0 10h24v4h-24z"/>
            </svg>
            <span class="question-text">%2$s</span>
            </button>
            <a href="%4$s" class="view-full-post" target="_blank" aria-label="View FAQ page for: %5$s">
            <svg class="icon-external" aria-hidden="true" viewBox="0 0 24 24">
            <path d="M19 19H5V5h7V3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/>
            </svg>
            <span>New Tab</span>
            </a>
            </div>
            <div class="faq-answer" id="faq-%1$s" hidden>
            <div class="answer-content">%3$s</div>
            </div>
            </div>',
            esc_attr(get_the_ID()),
            esc_html(get_the_title()),
            apply_filters('the_content', get_the_content()),
            esc_url($post_link),
            esc_attr(get_the_title())
        );
    }
    
    $output .= '</div>'; // Close faq-items
    $output .= '</div>'; // Close alynt-faq-collection

    wp_reset_postdata();
    
    return $output;
}