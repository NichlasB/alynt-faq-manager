<?php
/**
 * Custom admin list table columns for the FAQ post type.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes/admin
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Add custom columns to FAQ post type admin list
add_filter('manage_alynt_faq_posts_columns', 'alynt_faq_set_custom_columns');
/**
 * Define custom columns for the FAQ post type list table.
 *
 * Inserts Collection and Order columns after the Title column and
 * moves the Date column to the end.
 *
 * @since 1.0.0
 *
 * @param array $columns Existing column definitions.
 *
 * @return array Modified column definitions.
 */
function alynt_faq_set_custom_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        if ($key === 'title') {
            $new_columns[$key] = $value;
            $new_columns['collection'] = __('Collection', 'alynt-faq');
            $new_columns['order'] = __('Order', 'alynt-faq');
        } else if ($key !== 'date') {
            $new_columns[$key] = $value;
        }
    }
    $new_columns['date'] = __('Date', 'alynt-faq');
    return $new_columns;
}

// Populate custom columns
add_action('manage_alynt_faq_posts_custom_column', 'alynt_faq_custom_column_content', 10, 2);
/**
 * Output content for custom FAQ list table columns.
 *
 * @since 1.0.0
 *
 * @param string $column  The column name.
 * @param int    $post_id The current post ID.
 *
 * @return void
 */
function alynt_faq_custom_column_content($column, $post_id) {
    switch ($column) {
        case 'collection':
            $terms = get_the_terms($post_id, 'alynt_faq_collection');
            if ($terms && !is_wp_error($terms)) {
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
                echo '<span class="no-collection">' . esc_html__('No Collection', 'alynt-faq') . '</span>';
            }
            break;
        case 'order':
            echo get_post_field('menu_order', $post_id);
            break;
    }
}

// Make the custom columns sortable
add_filter('manage_edit-alynt_faq_sortable_columns', 'alynt_faq_sortable_columns');
/**
 * Register the Order column as sortable by menu_order.
 *
 * @since 1.0.0
 *
 * @param array $columns Existing sortable column definitions.
 *
 * @return array Modified sortable column definitions.
 */
function alynt_faq_sortable_columns($columns) {
    $columns['order'] = 'menu_order';
    return $columns;
}

// Add filter for Collections in admin
add_action('restrict_manage_posts', 'alynt_faq_add_taxonomy_filters');
/**
 * Add a Collection dropdown filter to the FAQ list table toolbar.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_add_taxonomy_filters() {
    global $typenow;
    if ($typenow === 'alynt_faq') {
        $taxonomy = 'alynt_faq_collection';
        $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
        wp_dropdown_categories(array(
            'show_option_all' => __('All Collections', 'alynt-faq'),
            'taxonomy'        => $taxonomy,
            'name'            => $taxonomy,
            'orderby'         => 'name',
            'selected'        => $selected,
            'hierarchical'    => true,
            'depth'           => 3,
            'show_count'      => true,
            'hide_empty'      => false,
        ));
    }
}

// Convert taxonomy ID to slug for filtering
add_filter('parse_query', 'alynt_faq_convert_taxonomy_id_to_term_in_query');
/**
 * Convert a numeric taxonomy term ID in the query var to its slug.
 *
 * wp_dropdown_categories() submits a term ID; WP_Query expects a slug
 * when filtering by a custom taxonomy via query vars.
 *
 * @since 1.0.0
 *
 * @param WP_Query $query The current WP_Query instance, passed by reference.
 *
 * @return void
 */
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
        if ($term && !is_wp_error($term)) {
            $q_vars[$taxonomy] = $term->slug;
        }
    }
}
