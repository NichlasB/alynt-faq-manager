<?php

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __DIR__ ) . DIRECTORY_SEPARATOR );
}

if ( ! defined( 'PLUGIN_PATH' ) ) {
    define( 'PLUGIN_PATH', dirname( __DIR__ ) );
}

require_once PLUGIN_PATH . '/vendor/autoload.php';

if ( ! function_exists( 'add_action' ) ) {
    function add_action() {}
}

if ( ! function_exists( 'add_filter' ) ) {
    function add_filter() {}
}

if ( ! function_exists( 'add_shortcode' ) ) {
    function add_shortcode() {}
}

if ( ! function_exists( 'register_activation_hook' ) ) {
    function register_activation_hook() {}
}

if ( ! function_exists( 'register_deactivation_hook' ) ) {
    function register_deactivation_hook() {}
}

if ( ! function_exists( 'plugin_dir_path' ) ) {
    function plugin_dir_path( $file ) {
        return trailingslashit( dirname( $file ) );
    }
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
    function plugin_dir_url() {
        return 'http://example.com/wp-content/plugins/alynt-faq-manager/';
    }
}

if ( ! function_exists( 'plugin_basename' ) ) {
    function plugin_basename( $file ) {
        return basename( $file );
    }
}

if ( ! function_exists( 'trailingslashit' ) ) {
    function trailingslashit( $value ) {
        return rtrim( $value, '/\\' ) . '/';
    }
}

if ( ! function_exists( 'is_admin' ) ) {
    function is_admin() {
        return false;
    }
}

if ( ! function_exists( 'load_plugin_textdomain' ) ) {
    function load_plugin_textdomain() {}
}

if ( ! function_exists( 'get_option' ) ) {
    function get_option( $option, $default = false ) {
        return $default;
    }
}

if ( ! function_exists( 'update_option' ) ) {
    function update_option() {
        return true;
    }
}

if ( ! function_exists( 'wp_cache_flush' ) ) {
    function wp_cache_flush() {}
}

require_once PLUGIN_PATH . '/alynt-faq-manager.php';
