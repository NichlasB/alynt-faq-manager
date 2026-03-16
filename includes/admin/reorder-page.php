<?php
/**
 * FAQ reorder admin page and AJAX handler.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes/admin
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Add admin menu item
add_action('admin_menu', 'alynt_faq_add_reorder_menu');

/**
 * Register the Reorder FAQs submenu page under the FAQ post type menu.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_add_reorder_menu() {
    add_submenu_page(
        'edit.php?post_type=alynt_faq',
        'Reorder FAQs',
        'Reorder FAQs',
        'edit_others_alynt_faqs',
        'alynt-faq-order',
        'alynt_faq_reorder_page'
    );
}

/**
 * Count the number of FAQ posts assigned to a given collection.
 *
 * @since 1.0.0
 *
 * @param int $collection_id Term ID of the collection.
 *
 * @return int Number of FAQ posts in the collection.
 */
function alynt_faq_count_posts_by_collection($collection_id) {
    $args = array(
        'post_type' => 'alynt_faq',
        'tax_query' => array(
            array(
                'taxonomy' => 'alynt_faq_collection',
                'field' => 'term_id',
                'terms' => $collection_id,
            ),
        ),
        'posts_per_page' => -1,
        'fields' => 'ids',
    );
    
    $query = new WP_Query($args);
    return $query->found_posts;
}

/**
 * Render the Reorder FAQs admin page.
 *
 * Displays a collection selector and a sortable list of FAQs for the selected collection.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_reorder_page() {
    if (!current_user_can('edit_others_alynt_faqs')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'alynt-faq'));
    }

    $collections = get_terms(array(
        'taxonomy' => 'alynt_faq_collection',
        'hide_empty' => false,
    ));
    $selected_collection = isset($_GET['collection']) ? absint($_GET['collection']) : 0;
    ?>
    <div class="wrap alynt-faq-reorder">
        <h1>Reorder FAQs</h1>
        
        <div class="collection-selector">
            <select id="collection-dropdown" name="collection">
                <option value="">Select a Collection</option>
                <?php foreach ($collections as $collection) : 
                    $faq_count = alynt_faq_count_posts_by_collection($collection->term_id);
                    ?>
                    <option value="<?php echo esc_attr($collection->term_id); ?>" 
                        <?php selected($selected_collection, $collection->term_id); ?>>
                        <?php echo esc_html($collection->name); ?> 
                        (<?php echo esc_html($faq_count); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="faq-items-container" class="faq-items-container">
            <?php if ($selected_collection) : ?>
                <?php alynt_faq_display_sortable_items($selected_collection); ?>
            <?php else : ?>
                <p class="description">Please select a collection to reorder FAQs.</p>
            <?php endif; ?>
        </div>

        <div id="save-feedback" class="notice" style="display: none;"></div>
    </div>
    <?php
}

/**
 * Output the sortable FAQ list for a given collection.
 *
 * @since 1.0.0
 *
 * @param int $collection_id Term ID of the collection whose FAQs to display.
 *
 * @return void
 */
function alynt_faq_display_sortable_items($collection_id) {
    $args = array(
        'post_type' => 'alynt_faq',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'tax_query' => array(
            array(
                'taxonomy' => 'alynt_faq_collection',
                'field' => 'term_id',
                'terms' => $collection_id,
            ),
        ),
    );

    $faqs = new WP_Query($args);

    if ($faqs->have_posts()) : ?>
        <ul id="sortable-faq-list" class="sortable-list">
            <?php while ($faqs->have_posts()) : $faqs->the_post(); ?>
                <li class="faq-item" data-post-id="<?php echo esc_attr(get_the_ID()); ?>">
                    <div class="faq-handle dashicons dashicons-menu"></div>
                    <div class="faq-title"><?php echo esc_html(get_the_title()); ?></div>
                    <div class="faq-order"><?php echo esc_html(get_post_field('menu_order', get_the_ID())); ?></div>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else : ?>
        <p class="description">No FAQs found in this collection.</p>
    <?php endif;
    wp_reset_postdata();
}

// Handle AJAX reordering
add_action('wp_ajax_alynt_faq_update_order', 'alynt_faq_update_order');

/**
 * Retrieve and sanitize the ordered post ID array from the AJAX request.
 *
 * @since 1.0.0
 *
 * @return int[] Array of sanitized post IDs in the new order.
 */
function alynt_faq_get_reorder_items() {
    return isset($_POST['postIds']) ? array_map('absint', $_POST['postIds']) : array();
}

/**
 * Validate that all items in the reorder list are published alynt_faq posts.
 *
 * Sends a JSON error and exits if any item is invalid.
 *
 * @since 1.0.0
 *
 * @param int[] $items Array of post IDs to validate.
 *
 * @return void
 */
function alynt_faq_validate_reorder_items($items) {
    foreach ($items as $post_id) {
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'alynt_faq') {
            wp_send_json_error(array(
                'message' => __('Invalid FAQ item detected.', 'alynt-faq')
            ));
        }
    }
}

/**
 * Update menu_order for each post to reflect the new sort order.
 *
 * @since 1.0.0
 *
 * @param int[] $items Array of post IDs in the desired order (0-indexed position).
 *
 * @return void
 */
function alynt_faq_persist_reorder_items($items) {
    global $wpdb;

    foreach ($items as $position => $id) {
        $wpdb->update(
            $wpdb->posts,
            array('menu_order' => $position),
            array('ID' => $id, 'post_type' => 'alynt_faq'),
            array('%d'),
            array('%d', '%s')
        );
    }
}

/**
 * Clear the post object cache for each reordered item and flush collection transients.
 *
 * @since 1.0.0
 *
 * @param int[] $items Array of post IDs whose caches should be cleared.
 *
 * @return void
 */
function alynt_faq_clear_reorder_item_caches($items) {
    foreach ($items as $post_id) {
        clean_post_cache($post_id);
    }

    alynt_faq_clear_collection_cache();
}

/**
 * AJAX handler for saving the new FAQ sort order.
 *
 * Verifies nonce and capability, delegates to helper functions, and returns
 * a JSON success or error response.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_update_order() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'alynt_faq_reorder')) {
        wp_send_json_error(array(
            'message' => __('Security check failed.', 'alynt-faq')
        ));
    }

    if (!current_user_can('edit_others_alynt_faqs')) {
        wp_send_json_error(array(
            'message' => __('You do not have permission to perform this action.', 'alynt-faq')
        ));
    }

    $items = alynt_faq_get_reorder_items();

    if (empty($items)) {
        wp_send_json_error(array(
            'message' => __('No items provided for reordering.', 'alynt-faq')
        ));
    }

    alynt_faq_validate_reorder_items($items);
    alynt_faq_persist_reorder_items($items);
    alynt_faq_clear_reorder_item_caches($items);

    wp_send_json_success(array(
        'message' => __('FAQ order updated successfully.', 'alynt-faq')
    ));
}
