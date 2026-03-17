<?php
/**
 * Custom post type and taxonomy registration for Alynt FAQ Manager.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register Custom Post Type and Taxonomy
add_action('init', 'alynt_faq_register_post_type_and_taxonomy');

function alynt_faq_get_collection_rewrite_slug() {
    $slug = apply_filters('alynt_faq_collection_rewrite_slug', 'faq-collection');
    $slug = sanitize_title((string) $slug);

    return '' !== $slug ? $slug : 'faq-collection';
}

function alynt_faq_get_post_type_rewrite_slug() {
    $slug = apply_filters('alynt_faq_post_type_rewrite_slug', 'faq-archive');
    $slug = sanitize_title((string) $slug);

    return '' !== $slug ? $slug : 'faq-archive';
}

function alynt_faq_get_rest_base() {
    $rest_base = apply_filters('alynt_faq_rest_base', 'faqs');
    $rest_base = sanitize_title((string) $rest_base);

    return '' !== $rest_base ? $rest_base : 'faqs';
}

/**
 * Register the alynt_faq_collection taxonomy.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_register_collection_taxonomy() {
    $taxonomy_labels = array(
        'name'              => _x('Collections', 'taxonomy general name', 'alynt-faq'),
        'singular_name'     => _x('Collection', 'taxonomy singular name', 'alynt-faq'),
        'search_items'      => __('Search Collections', 'alynt-faq'),
        'all_items'         => __('All Collections', 'alynt-faq'),
        'parent_item'       => __('Parent Collection', 'alynt-faq'),
        'parent_item_colon' => __('Parent Collection:', 'alynt-faq'),
        'edit_item'         => __('Edit Collection', 'alynt-faq'),
        'update_item'       => __('Update Collection', 'alynt-faq'),
        'add_new_item'      => __('Add New Collection', 'alynt-faq'),
        'new_item_name'     => __('New Collection Name', 'alynt-faq'),
        'menu_name'         => __('Collections', 'alynt-faq')
    );

    $taxonomy_args = array(
        'hierarchical'      => true,
        'labels'            => $taxonomy_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => alynt_faq_get_collection_rewrite_slug()),
        'show_in_rest'      => true,
    );

    register_taxonomy('alynt_faq_collection', array('alynt_faq'), $taxonomy_args);
}

/**
 * Register the alynt_faq custom post type.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_register_faq_post_type() {
    $post_type_labels = array(
        'name'               => _x('FAQs', 'post type general name', 'alynt-faq'),
        'singular_name'      => _x('FAQ', 'post type singular name', 'alynt-faq'),
        'menu_name'          => _x('FAQs', 'admin menu', 'alynt-faq'),
        'name_admin_bar'     => _x('FAQ', 'add new on admin bar', 'alynt-faq'),
        'add_new'            => _x('Add New', 'faq', 'alynt-faq'),
        'add_new_item'       => __('Add New FAQ', 'alynt-faq'),
        'edit_item'          => __('Edit FAQ', 'alynt-faq'),
        'new_item'           => __('New FAQ', 'alynt-faq'),
        'view_item'          => __('View FAQ', 'alynt-faq'),
        'search_items'       => __('Search FAQs', 'alynt-faq'),
        'not_found'          => __('No FAQs found', 'alynt-faq'),
        'not_found_in_trash' => __('No FAQs found in Trash', 'alynt-faq'),
    );

    $post_type_args = array(
        'labels'              => $post_type_labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => alynt_faq_get_post_type_rewrite_slug()),
        'capability_type'     => array('alynt_faq', 'alynt_faqs'),
        'map_meta_cap'        => true,
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => 20,
        'menu_icon'           => 'dashicons-format-chat',
        'supports'            => array('title', 'editor', 'revisions'),
        'show_in_rest'        => true,
        'rest_base'           => alynt_faq_get_rest_base(),
    );

    register_post_type('alynt_faq', $post_type_args);
}

/**
 * Register both the FAQ post type and collection taxonomy.
 *
 * Hooked to 'init'.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_register_post_type_and_taxonomy() {
    alynt_faq_register_collection_taxonomy();
    alynt_faq_register_faq_post_type();
}

/**
 * Rename the default 'Uncategorized' term in the collection taxonomy to 'No Collection'.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_change_default_term_name() {
    $taxonomy = 'alynt_faq_collection';
    $default_term_id = (int) get_option('default_term_' . $taxonomy, 0);

    $default_term = false;

    if ($default_term_id > 0) {
        $default_term = get_term($default_term_id, $taxonomy);
    }

    if (!$default_term || is_wp_error($default_term)) {
        $default_term = get_term_by('name', 'Uncategorized', $taxonomy);
    }

    if (!$default_term || is_wp_error($default_term)) {
        return;
    }

    if ('No Collection' === $default_term->name && 'no-collection' === $default_term->slug) {
        return;
    }

    $result = wp_update_term($default_term->term_id, $taxonomy, array(
            'name' => 'No Collection',
            'slug' => 'no-collection'
        ));

    if (is_wp_error($result)) {
        error_log('[Alynt FAQ Manager] Failed to rename default FAQ collection term: ' . $result->get_error_message());
    }
}

// Redirect archive page to designated FAQ page
add_action('template_redirect', 'alynt_faq_redirect_archive');

/**
 * Determine the target URL for FAQ archive redirects.
 *
 * Allows third parties to supply a page ID through the
 * 'alynt_faq_archive_redirect_page_id' filter.
 *
 * @since 1.0.0
 *
 * @return string Redirect URL, or empty string when no target can be resolved.
 */
function alynt_faq_get_archive_redirect_url() {
    $redirect_page_id = (int) apply_filters('alynt_faq_archive_redirect_page_id', 0);

    if ($redirect_page_id > 0) {
        $redirect_url = get_permalink($redirect_page_id);

        if (!empty($redirect_url)) {
            return $redirect_url;
        }
    }

    $faq_page = get_page_by_path('faq');

    if ($faq_page instanceof WP_Post) {
        $redirect_url = get_permalink($faq_page->ID);

        if (!empty($redirect_url)) {
            return $redirect_url;
        }
    }

    return '';
}
/**
 * Redirect the FAQ post type archive to the designated FAQ page.
 *
 * Sends visitors hitting the FAQ archive to the configured FAQ page via
 * a safe redirect when a target page can be resolved.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_redirect_archive() {
    if (is_post_type_archive('alynt_faq')) {
        $redirect_url = alynt_faq_get_archive_redirect_url();

        if (!empty($redirect_url)) {
            wp_safe_redirect($redirect_url);
            exit;
        }
    }
}
