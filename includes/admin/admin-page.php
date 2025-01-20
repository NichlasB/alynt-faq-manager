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

// Helper function to count FAQs in a collection
function wp_count_posts_by_collection($collection_id) {
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

// Enqueue admin scripts and styles
add_action('admin_enqueue_scripts', 'alynt_faq_admin_scripts');

function alynt_faq_admin_scripts($hook) {
    if ($hook !== 'alynt_faq_page_alynt-faq-order' && $hook !== 'alynt_faq_page_alynt-faq-custom-css') {
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
                    $faq_count = wp_count_posts_by_collection($collection->term_id);
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

// Add submenu page for Custom CSS
function alynt_faq_add_custom_css_page() {
    add_submenu_page(
        'edit.php?post_type=alynt_faq',
        'Custom CSS',
        'Custom CSS',
        'manage_options',
        'alynt-faq-custom-css',
        'alynt_faq_render_custom_css_page'
    );
}
add_action('admin_menu', 'alynt_faq_add_custom_css_page');

// Add AJAX handler for custom CSS
add_action('wp_ajax_alynt_faq_save_custom_css', 'alynt_faq_save_custom_css');
function alynt_faq_save_custom_css() {
    if (!check_ajax_referer('alynt_faq_custom_css', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }

    $custom_css = isset($_POST['custom_css']) ? wp_strip_all_tags($_POST['custom_css']) : '';
    update_option('alynt_faq_custom_css', $custom_css);
    
    wp_send_json_success('Custom CSS saved successfully');
}

// Render the Custom CSS page
function alynt_faq_render_custom_css_page() {
    $custom_css = get_option('alynt_faq_custom_css', '');
    ?>
    <div class="wrap">
        <h1>FAQ Custom CSS</h1>
        
        <div class="alynt-faq-css-container">
            <form method="post" action="" id="custom-css-form">
                <?php wp_nonce_field('alynt_faq_custom_css', 'alynt_faq_custom_css_nonce'); ?>
                
                <div class="css-documentation">
                    <h3>Available CSS Classes</h3>
                    <ul>
                        <li><code>.alynt-faq-collection</code> - Main container for FAQ collection</li>
                        <li><code>.faq-item</code> - Individual FAQ container</li>
                        <li><code>.faq-question</code> - Question button</li>
                        <li><code>.faq-answer</code> - Answer container</li>
                        <li><code>.icon-plus</code>, <code>.icon-minus</code> - Toggle icons</li>
                        <li><code>.question-text</code> - Question text</li>
                        <li><code>.answer-content</code> - Answer content</li>
                    </ul>
                    
                    <h4>Example:</h4>
                    <pre>
.faq-question {
    color: #your-color;
    font-size: 1.2rem;
}

.icon-plus, .icon-minus {
    --icon-color: #your-color;
}</pre>
                </div>

                <div class="css-editor">
                    <textarea name="alynt_faq_custom_css" 
                            id="alynt_faq_custom_css" 
                            rows="20" 
                            class="large-text code"
                            style="font-family: monospace;"><?php echo esc_textarea($custom_css); ?></textarea>
                </div>

                <?php submit_button('Save Custom CSS'); ?>
                
                <button type="button" class="button" id="reset-css">Reset to Default</button>
            </form>
        </div>
    </div>

    <style>
        .alynt-faq-css-container {
            margin-top: 20px;
        }
        .css-documentation {
            background: #fff;
            padding: 20px;
            border: 1px solid #ccd0d4;
            margin-bottom: 20px;
        }
        .css-documentation pre {
            background: #f6f7f7;
            padding: 15px;
            border: 1px solid #ddd;
            white-space: pre-wrap;
        }
        .css-documentation code {
            background: #f6f7f7;
            padding: 3px 5px;
        }
        #reset-css {
            margin-left: 10px;
        }
    </style>
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