<?php
/**
 * Uninstall script for Alynt FAQ Manager.
 *
 * @package Alynt_FAQ_Manager
 */

// If uninstall is not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Optional hardening: ensure only privileged users can trigger uninstall cleanup.
if ( function_exists( 'current_user_can' ) && ! current_user_can( 'activate_plugins' ) ) {
    return;
}

/**
 * Remove all plugin data for the current site context.
 *
 * @return void
 */
function alynt_faq_cleanup_site_data() {
    $faq_post_ids = get_posts(
        array(
            'post_type'   => 'alynt_faq',
            'numberposts' => -1,
            'post_status' => 'any',
            'fields'      => 'ids',
        )
    );

    foreach ( $faq_post_ids as $post_id ) {
        wp_delete_post( (int) $post_id, true );
    }

    $term_ids = get_terms(
        array(
            'taxonomy'   => 'alynt_faq_collection',
            'hide_empty' => false,
            'fields'     => 'ids',
        )
    );

    if ( ! empty( $term_ids ) && ! is_wp_error( $term_ids ) ) {
        foreach ( $term_ids as $term_id ) {
            wp_delete_term( (int) $term_id, 'alynt_faq_collection' );
        }
    }

    $options_to_delete = array(
        'alynt_faq_version',
        'alynt_faq_custom_css',
        'alynt_faq_collection_cache_version',
    );

    foreach ( $options_to_delete as $option_name ) {
        delete_option( $option_name );
    }

    $capabilities = array(
        'edit_alynt_faq',
        'read_alynt_faq',
        'delete_alynt_faq',
        'edit_alynt_faqs',
        'edit_others_alynt_faqs',
        'publish_alynt_faqs',
        'read_private_alynt_faqs',
        'delete_alynt_faqs',
        'delete_private_alynt_faqs',
        'delete_published_alynt_faqs',
        'delete_others_alynt_faqs',
        'edit_private_alynt_faqs',
        'edit_published_alynt_faqs',
    );

    foreach ( array( 'administrator', 'editor' ) as $role_name ) {
        $role = get_role( $role_name );

        if ( ! $role ) {
            continue;
        }

        foreach ( $capabilities as $capability ) {
            $role->remove_cap( $capability );
        }
    }

    // Clear only plugin-specific transients.
    global $wpdb;
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            $wpdb->esc_like( '_transient_alynt_faq_collections_' ) . '%',
            $wpdb->esc_like( '_transient_timeout_alynt_faq_collections_' ) . '%'
        )
    );
}

if ( is_multisite() ) {
    $site_ids = get_sites(
        array(
            'fields' => 'ids',
        )
    );

    foreach ( $site_ids as $site_id ) {
        switch_to_blog( (int) $site_id );
        alynt_faq_cleanup_site_data();
        restore_current_blog();
    }
} else {
    alynt_faq_cleanup_site_data();
}