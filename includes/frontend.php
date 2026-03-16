<?php
/**
 * Frontend output hooks for Alynt FAQ Manager.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes/frontend
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Output the saved custom CSS in the front-end <head>.
 *
 * Retrieves the alynt_faq_custom_css option and prints it in a <style> tag.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_output_custom_css() {
    $custom_css = get_option('alynt_faq_custom_css');
    if (!empty($custom_css)) {
        echo "<style type='text/css'>\n";
        echo wp_strip_all_tags($custom_css) . "\n";
        echo "</style>";
    }
}
add_action('wp_head', 'alynt_faq_output_custom_css');