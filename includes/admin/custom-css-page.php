<?php
/**
 * FAQ Custom CSS admin page and AJAX save handler.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes/admin
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the Custom CSS submenu page under the FAQ post type menu.
 *
 * @since 1.0.0
 *
 * @return void
 */
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

/**
 * Validate nonce, capability, and presence of CSS data in the AJAX request.
 *
 * Sends a JSON error and exits on any validation failure.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_validate_custom_css_request() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'alynt_faq_custom_css')) {
        wp_send_json_error(array(
            'message' => __('Security check failed.', 'alynt-faq')
        ));
    }

    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error(array(
            'message' => __('You do not have permission to edit custom CSS.', 'alynt-faq')
        ));
    }

    if (!isset($_POST['css'])) {
        wp_send_json_error(array(
            'message' => __('No CSS content provided.', 'alynt-faq')
        ));
    }
}

/**
 * Retrieve and sanitize the raw CSS string from the AJAX POST payload.
 *
 * Strips all HTML tags and unescapes slashes introduced by PHP magic quotes.
 *
 * @since 1.0.0
 *
 * @return string Sanitized CSS string.
 */
function alynt_faq_get_sanitized_custom_css() {
    return stripslashes(wp_strip_all_tags($_POST['css']));
}

/**
 * Validate that the CSS string contains safe and well-formed rules.
 *
 * Checks for required curly-brace structure and rejects known harmful CSS
 * patterns such as expression(), @import, and data: URIs.
 *
 * @since 1.0.0
 *
 * @param string $custom_css The sanitized CSS string to validate.
 *
 * @return void
 */
function alynt_faq_validate_custom_css_content($custom_css) {
    if (empty($custom_css)) {
        return;
    }

    if (strpos($custom_css, '{') === false || strpos($custom_css, '}') === false) {
        wp_send_json_error(array(
            'message' => __('Invalid CSS format. CSS must contain valid rules with { } brackets.', 'alynt-faq')
        ));
    }

    $harmful_patterns = array(
        'expression',
        'javascript:',
        'behavior:',
        '-moz-binding',
        '@import',
        'data:',
    );

    foreach ($harmful_patterns as $pattern) {
        if (stripos($custom_css, $pattern) !== false) {
            wp_send_json_error(array(
                'message' => __('Invalid CSS content detected.', 'alynt-faq')
            ));
        }
    }
}

/**
 * AJAX handler for saving the custom CSS option.
 *
 * Validates the request, sanitizes input, and saves to the alynt_faq_custom_css option.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_save_custom_css() {
    alynt_faq_validate_custom_css_request();

    $custom_css = alynt_faq_get_sanitized_custom_css();
    alynt_faq_validate_custom_css_content($custom_css);
    $result = update_option('alynt_faq_custom_css', $custom_css);
    
    if ($result === false) {
        wp_send_json_error(array(
            'message' => __('Failed to save CSS. Please try again.', 'alynt-faq')
        ));
    }

    wp_send_json_success(array(
        'message' => __('Custom CSS saved successfully.', 'alynt-faq')
    ));
}

/**
 * Output the available CSS classes reference panel on the Custom CSS page.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_render_custom_css_documentation() {
    ?>
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
    <?php
}

/**
 * Output the CSS textarea editor and form submit controls.
 *
 * @since 1.0.0
 *
 * @param string $custom_css The currently saved CSS to pre-populate the editor.
 *
 * @return void
 */
function alynt_faq_render_custom_css_editor($custom_css) {
    ?>
    <div class="css-editor">
        <textarea name="alynt_faq_custom_css" 
        id="alynt_faq_custom_css" 
        rows="20" 
        class="large-text code"
        style="font-family: monospace;"><?php echo esc_textarea($custom_css); ?></textarea>
    </div>

    <p class="submit">
        <?php submit_button('Save Custom CSS', 'primary', 'submit', false); ?>
        <button type="button" class="button" id="reset-css">Reset to Default</button>
    </p>
    <?php
}

/**
 * Render the full Custom CSS admin page.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_render_custom_css_page() {
    $custom_css = get_option('alynt_faq_custom_css', '');
    ?>
    <div class="wrap">
        <h1>FAQ Custom CSS</h1>
        
        <div class="alynt-faq-css-container">
            <form method="post" action="" id="custom-css-form">
                <div id="save-feedback" class="notice" style="display: none;"></div>
                <?php wp_nonce_field('alynt_faq_custom_css', 'alynt_faq_custom_css_nonce'); ?>
                <?php alynt_faq_render_custom_css_documentation(); ?>
                <?php alynt_faq_render_custom_css_editor($custom_css); ?>
            </form>
        </div>
    </div>
    <?php
}
