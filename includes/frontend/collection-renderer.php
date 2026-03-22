<?php
/**
 * Frontend collection rendering functions for the FAQ accordion.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes/frontend
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
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
function alynt_faq_get_collection_query_args( $collection, $orderby ) {
	$args = array(
		'post_type'              => 'alynt_faq',
		'posts_per_page'         => -1,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'tax_query'              => array(
			array(
				'taxonomy' => 'alynt_faq_collection',
				'field'    => 'term_id',
				'terms'    => $collection->term_id,
			),
		),
	);

	switch ( $orderby ) {
		case 'date':
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			break;
		case 'abc':
			$args['orderby'] = 'title';
			$args['order']   = 'ASC';
			break;
		default:
			$args['orderby'] = 'menu_order';
			$args['order']   = 'ASC';
	}

	$filtered_args = apply_filters( 'alynt_faq_collection_args', $args );

	if ( ! is_array( $filtered_args ) ) {
		return $args;
	}

	return $filtered_args;
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
function alynt_faq_render_collection_header( $collection ) {
	$items_id = 'faq-items-' . esc_attr( $collection->term_id );
	return sprintf(
		'<header class="collection-header">
        <h2 class="collection-title" id="alynt-faq-collection-title-%5$s">%1$s</h2>
        <div class="collection-controls">
        <button class="alynt-faq-expand-all expand-all" type="button" aria-controls="%4$s">
        %2$s
        </button>
        <button class="alynt-faq-collapse-all collapse-all" type="button" aria-controls="%4$s">
        %3$s
        </button>
        </div>
        </header>',
		esc_html( $collection->name ),
		esc_html__( 'Expand All', 'alynt-faq' ),
		esc_html__( 'Collapse All', 'alynt-faq' ),
		$items_id,
		esc_attr( $collection->term_id )
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
function alynt_faq_render_collection_item( $post_id, $title, $content, $post_link ) {
	$question_classes = apply_filters( 'alynt_faq_question_classes', array( 'faq-question' ) );
	$answer_classes   = apply_filters( 'alynt_faq_answer_classes', array( 'faq-answer' ) );

	if ( ! is_array( $question_classes ) ) {
		$question_classes = array( 'faq-question' );
	}

	if ( ! is_array( $answer_classes ) ) {
		$answer_classes = array( 'faq-answer' );
	}

	$question_classes    = implode( ' ', $question_classes );
	$answer_classes      = implode( ' ', $answer_classes );
	$view_faq_aria_label = sprintf(
		/* translators: %s: FAQ question title. */
		__( 'View FAQ page for: %s (opens in new tab)', 'alynt-faq' ),
		$title
	);

	return sprintf(
		'<article class="alynt-faq-item faq-item">
        <div class="alynt-faq-header faq-header">
        <button class="alynt-faq-question %6$s" type="button" aria-expanded="true" aria-controls="faq-%1$s">
        <svg class="alynt-faq-icon-plus icon-plus" aria-hidden="true" viewBox="0 0 24 24">
        <path d="M24 10h-10v-10h-4v10h-10v4h10v10h4v-10h10z"/>
        </svg>
        <svg class="alynt-faq-icon-minus icon-minus" aria-hidden="true" viewBox="0 0 24 24">
        <path d="M0 10h24v4h-24z"/>
        </svg>
        <span class="alynt-faq-question-text question-text">%2$s</span>
        </button>
        <a href="%4$s" class="alynt-faq-view-full-post view-full-post" target="_blank" rel="noopener noreferrer" aria-label="%8$s">
        <svg class="alynt-faq-icon-external icon-external" aria-hidden="true" viewBox="0 0 24 24">
        <path d="M19 19H5V5h7V3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/>
        </svg>
        <span>%9$s</span>
        </a>
        </div>
        <div class="alynt-faq-answer %7$s" id="faq-%1$s" aria-hidden="false">
        <div class="alynt-faq-answer-content answer-content">%3$s</div>
        </div>
        </article>',
		esc_attr( $post_id ),
		esc_html( $title ),
		apply_filters( 'the_content', $content ),
		esc_url( $post_link ),
		esc_attr( $title ),
		esc_attr( $question_classes ),
		esc_attr( $answer_classes ),
		esc_attr( $view_faq_aria_label ),
		esc_html__( 'View FAQ Page', 'alynt-faq' )
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
function alynt_faq_render_collection( $collection, $orderby ) {
	$faqs = new WP_Query( alynt_faq_get_collection_query_args( $collection, $orderby ) );

	if ( ! $faqs->have_posts() ) {
		return '';
	}

	$output  = '<section class="alynt-faq-collection" aria-labelledby="alynt-faq-collection-title-' . esc_attr( $collection->term_id ) . '">';
	$output .= alynt_faq_render_collection_header( $collection );
	$output .= '<div class="faq-items" id="faq-items-' . esc_attr( $collection->term_id ) . '">';

	while ( $faqs->have_posts() ) {
		$faqs->the_post();
		$output .= alynt_faq_render_collection_item(
			get_the_ID(),
			get_the_title(),
			get_the_content(),
			get_permalink()
		);
	}

	$output .= '</div>';
	$output .= '</section>';

	wp_reset_postdata();

	return $output;
}
