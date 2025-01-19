<?php
if (!defined('ABSPATH')) {
    exit;
}

// Add admin menu item
add_action('admin_menu', 'alynt_faq_add_reorder_menu');

function alynt_faq_add_reorder_menu() {
    add_submenu_page(
        'edit.php?post_type=alynt_faq',
        'Reorder FAQs',
        'Reorder FAQs',
        'manage_options',
        'alynt-faq-order',
        'alynt_faq_reorder_page'
    );
}

// Enqueue admin scripts and styles
add_action('admin_enqueue_scripts', 'alynt_faq_admin_scripts');

function alynt_faq_admin_scripts($hook) {
    if ($hook !== 'alynt_faq_page_alynt-faq-order') {
        return;
    }

    wp_enqueue_style(
        'alynt-faq-admin',
        ALYNT_FAQ_PLUGIN_URL . 'assets/css/admin.css',
        array(),
        ALYNT_FAQ_VERSION
    );

    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script(
        'alynt-faq-admin',
        ALYNT_FAQ_PLUGIN_URL . 'assets/js/admin.js',
        array('jquery', 'jquery-ui-sortable'),
        ALYNT_FAQ_VERSION,
        true
    );

    wp_localize_script('alynt-faq-admin', 'alyntFaqAdmin', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('alynt_faq_reorder'),
        'messages' => array(
            'orderSaved' => 'FAQ order has been updated.',
            'error' => 'An error occurred while saving the order.'
        )
    ));
}

// Render the reorder page
function alynt_faq_reorder_page() {
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
                    $faq_count = wp_count_posts('alynt_faq')->publish;
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

// Display sortable FAQ items
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

function alynt_faq_update_order() {
    // Verify nonce
    if (!check_ajax_referer('alynt_faq_reorder', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
    }

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }

    $post_ids = isset($_POST['postIds']) ? $_POST['postIds'] : null;

    if (!$post_ids || !is_array($post_ids)) {
        wp_send_json_error('Invalid data');
    }

    // Update post order
    foreach ($post_ids as $position => $post_id) {
        wp_update_post(array(
            'ID' => absint($post_id),
            'menu_order' => $position
        ));
    }

    wp_send_json_success('Order updated successfully');
}