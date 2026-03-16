<?php
/**
 * Frontend collection rendering functions for the FAQ accordion.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes/frontend
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Build the WP_Query arguments array for fetching FAQs in a collection.
 *
 * @since 1.0.0
 *
 * @param WP_Term $collection The collection taxonomy term object.
 * @param string  $orderby    Sort order: 'menu_order', 'date', or 'abc'.
 *
 * @return array WP_Query arguments array.
 */
function alynt_faq_get_collection_query_args($collection, $orderby) {
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

    switch ($orderby) {
        case 'date':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
        case 'abc':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
        default:
            $args['orderby'] = 'menu_order';
            $args['order'] = 'ASC';
    }

    return apply_filters('alynt_faq_collection_args', $args);
}

/**
 * Build the HTML string for a collection header with expand/collapse controls.
 *
 * @since 1.0.0
 *
 * @param WP_Term $collection The collection taxonomy term object.
 *
 * @return string HTML markup for the collection header.
 */
function alynt_faq_render_collection_header($collection) {
    return sprintf(
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
}

/**
 * Build the HTML string for a single FAQ accordion item.
 *
 * @since 1.0.0
 *
 * @param int    $post_id   The FAQ post ID.
 * @param string $title     The FAQ question title.
 * @param string $content   The raw FAQ answer content.
 * @param string $post_link The permalink to the individual FAQ post.
 *
 * @return string HTML markup for the FAQ accordion item.
 */
function alynt_faq_render_collection_item($post_id, $title, $content, $post_link) {
    $question_classes = implode(' ', apply_filters('alynt_faq_question_classes', array('faq-question')));
    $answer_classes   = implode(' ', apply_filters('alynt_faq_answer_classes', array('faq-answer')));

    return sprintf(
        '<div class="faq-item">
        <div class="faq-header">
        <button class="%6$s" aria-expanded="false" aria-controls="faq-%1$s">
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
        <div class="%7$s" id="faq-%1$s" hidden>
        <div class="answer-content">%3$s</div>
        </div>
        </div>',
        esc_attr($post_id),
        esc_html($title),
        apply_filters('the_content', $content),
        esc_url($post_link),
        esc_attr($title),
        esc_attr($question_classes),
        esc_attr($answer_classes)
    );
}

/**
 * Build the complete HTML output for a single FAQ collection accordion.
 *
 * @since 1.0.0
 *
 * @param WP_Term $collection The collection taxonomy term object.
 * @param string  $orderby    Sort order passed through to the query: 'menu_order', 'date', or 'abc'.
 *
 * @return string HTML markup for the collection, or empty string if no FAQs found.
 */
function alynt_faq_render_collection($collection, $orderby) {
    $faqs = new WP_Query(alynt_faq_get_collection_query_args($collection, $orderby));

    if (!$faqs->have_posts()) {
        return '';
    }

    $output = '<div class="alynt-faq-collection">';
    $output .= alynt_faq_render_collection_header($collection);
    $output .= '<div class="faq-items">';
    
    while ($faqs->have_posts()) {
        $faqs->the_post();
        $output .= alynt_faq_render_collection_item(
            get_the_ID(),
            get_the_title(),
            get_the_content(),
            get_permalink()
        );
    }
    
    $output .= '</div>';
    $output .= '</div>';

    wp_reset_postdata();
    
    return $output;
}
