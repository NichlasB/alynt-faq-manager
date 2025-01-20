<?php
if (!defined('ABSPATH')) {
    exit;
}

// Output custom CSS in frontend
function alynt_faq_output_custom_css() {
    $custom_css = get_option('alynt_faq_custom_css');
    if (!empty($custom_css)) {
        echo '<style type="text/css">' . esc_html($custom_css) . '</style>';
    }
}
add_action('wp_head', 'alynt_faq_output_custom_css');