<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register Custom Post Type and Taxonomy
add_action('init', 'alynt_faq_register_post_type_and_taxonomy');

function alynt_faq_register_post_type_and_taxonomy() {
    // Register FAQ Collection Taxonomy
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
        'show_ui'          => true,
        'show_admin_column' => true,
        'query_var'        => true,
        'rewrite'          => array('slug' => 'faq-collection'),
        'show_in_rest'     => true,
    );

    register_taxonomy('alynt_faq_collection', array('alynt_faq'), $taxonomy_args);

    // Register FAQ Post Type
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
        'rewrite'             => array('slug' => 'faq'),
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => 20,
        'menu_icon'           => 'dashicons-format-chat',
        'supports'            => array('title', 'editor', 'revisions'),
        'show_in_rest'        => true,
        'rest_base'           => 'faqs',
    );

    register_post_type('alynt_faq', $post_type_args);

    // Change default "Uncategorized" term to "No Collection"
    add_action('admin_init', 'alynt_faq_change_default_term_name');
}

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

// Add custom columns to FAQ post type admin list
add_filter('manage_alynt_faq_posts_columns', 'alynt_faq_set_custom_columns');
function alynt_faq_set_custom_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        if ($key === 'title') {
            $new_columns[$key] = $value;
            $new_columns['collection'] = 'Collection';
            $new_columns['order'] = 'Order';
        } else if ($key !== 'date') {
            $new_columns[$key] = $value;
        }
    }
    $new_columns['date'] = 'Date';
    return $new_columns;
}

// Populate custom columns
add_action('manage_alynt_faq_posts_custom_column', 'alynt_faq_custom_column_content', 10, 2);
function alynt_faq_custom_column_content($column, $post_id) {
    switch ($column) {
        case 'collection':
            $terms = get_the_terms($post_id, 'alynt_faq_collection');
            if (!empty($terms)) {
                $term_names = array();
                foreach ($terms as $term) {
                    $term_names[] = sprintf(
                        '<a href="%s">%s</a>',
                        esc_url(admin_url('edit.php?post_type=alynt_faq&alynt_faq_collection=' . $term->slug)),
                        esc_html($term->name)
                    );
                }
                echo implode(', ', $term_names);
            } else {
                echo '<span class="no-collection">No Collection</span>';
            }
            break;
        case 'order':
            echo get_post_field('menu_order', $post_id);
            break;
    }
}

// Make the custom columns sortable
add_filter('manage_edit-alynt_faq_sortable_columns', 'alynt_faq_sortable_columns');
function alynt_faq_sortable_columns($columns) {
    $columns['order'] = 'menu_order';
    return $columns;
}

// Add filter for Collections in admin
add_action('restrict_manage_posts', 'alynt_faq_add_taxonomy_filters');
function alynt_faq_add_taxonomy_filters() {
    global $typenow;
    if ($typenow === 'alynt_faq') {
        $taxonomy = 'alynt_faq_collection';
        $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
        wp_dropdown_categories(array(
            'show_option_all' => 'All Collections',
            'taxonomy'        => $taxonomy,
            'name'           => $taxonomy,
            'orderby'        => 'name',
            'selected'       => $selected,
            'hierarchical'   => true,
            'depth'          => 3,
            'show_count'     => true,
            'hide_empty'     => false,
        ));
    }
}

// Convert taxonomy ID to slug for filtering
add_filter('parse_query', 'alynt_faq_convert_taxonomy_id_to_term_in_query');
function alynt_faq_convert_taxonomy_id_to_term_in_query($query) {
    global $pagenow;
    $post_type = 'alynt_faq';
    $taxonomy = 'alynt_faq_collection';
    $q_vars = &$query->query_vars;

    if ($pagenow == 'edit.php' 
        && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type
        && isset($q_vars[$taxonomy]) 
        && is_numeric($q_vars[$taxonomy]) 
        && $q_vars[$taxonomy] != 0
    ) {
        $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
        $q_vars[$taxonomy] = $term->slug;
    }
}