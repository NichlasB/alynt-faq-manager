<?php
if (!defined('ABSPATH')) {
    exit;
}

// Output custom CSS in frontend
function alynt_faq_output_custom_css() {
    $custom_css = get_option('alynt_faq_custom_css');
    if (!empty($custom_css)) {
        echo "<style type='text/css'>\n";
        echo wp_strip_all_tags($custom_css) . "\n";
        echo "</style>";
    }
}
add_action('wp_head', 'alynt_faq_output_custom_css');