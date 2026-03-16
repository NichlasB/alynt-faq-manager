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

/**
 * Register the alynt_faq_collection taxonomy.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_register_collection_taxonomy() {
    $taxonomy_labels = array(
        'name'              => 'Collections',
        'singular_name'     => 'Collection',
        'search_items'      => 'Search Collections',
        'all_items'         => 'All Collections',
        'parent_item'       => 'Parent Collection',
        'parent_item_colon' => 'Parent Collection:',
        'edit_item'         => 'Edit Collection',
        'update_item'       => 'Update Collection',
        'add_new_item'      => 'Add New Collection',
        'new_item_name'     => 'New Collection Name',
        'menu_name'         => 'Collections'
    );

    $taxonomy_args = array(
        'hierarchical'      => true,
        'labels'            => $taxonomy_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'faq-collection'),
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
        'name'               => 'FAQs',
        'singular_name'      => 'FAQ',
        'menu_name'          => 'FAQs',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New FAQ',
        'edit_item'          => 'Edit FAQ',
        'new_item'           => 'New FAQ',
        'view_item'          => 'View FAQ',
        'search_items'       => 'Search FAQs',
        'not_found'          => 'No FAQs found',
        'not_found_in_trash' => 'No FAQs found in Trash',
    );

    $post_type_args = array(
        'labels'              => $post_type_labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'faq-archive'),
        'capability_type'     => array('alynt_faq', 'alynt_faqs'),
        'map_meta_cap'        => true,
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => 20,
        'menu_icon'           => 'dashicons-format-chat',
        'supports'            => array('title', 'editor', 'revisions'),
        'show_in_rest'        => true,
        'rest_base'           => 'faqs',
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
    $default_term = get_term_by('name', 'Uncategorized', $taxonomy);
    
    if ($default_term) {
        wp_update_term($default_term->term_id, $taxonomy, array(
            'name' => 'No Collection',
            'slug' => 'no-collection'
        ));
    }
}
add_action('admin_init', 'alynt_faq_change_default_term_name');

// Redirect archive page to designated FAQ page
add_action('template_redirect', 'alynt_faq_redirect_archive');
/**
 * Redirect the FAQ post type archive to the designated FAQ page.
 *
 * Sends visitors hitting /faq-archive/ to /faq/ via a safe redirect.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_redirect_archive() {
    if (is_post_type_archive('alynt_faq')) {
        wp_safe_redirect(home_url('/faq/'));
        exit;
    }
}
