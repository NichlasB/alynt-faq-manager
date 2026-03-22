<?php
/**
 * Transient cache management for FAQ collections.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes/shared
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the current collection cache version token.
 *
 * @since 1.0.0
 *
 * @return string Cache version hash.
 */
function alynt_faq_get_collection_cache_version() {
	$version = get_option( 'alynt_faq_collection_cache_version', '' );

	if ( ! is_string( $version ) || '' === $version ) {
		$version = md5( (string) microtime( true ) . wp_rand() );
		add_option( 'alynt_faq_collection_cache_version', $version, '', false );
	}

	return $version;
}

/**
 * Build a transient cache key for a collection query payload.
 *
 * @since 1.0.0
 *
 * @param array $atts Normalized shortcode attributes.
 *
 * @return string Cache key.
 */
function alynt_faq_get_collection_cache_key( $atts ) {
	return 'alynt_faq_collections_' . md5(
		wp_json_encode(
			array(
				'version' => alynt_faq_get_collection_cache_version(),
				'atts'    => $atts,
			)
		)
	);
}

/**
 * Clear collection caches when an FAQ post changes.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID being changed.
 *
 * @return void
 */
function alynt_faq_clear_collection_cache_for_post( $post_id ) {
	if ( 'alynt_faq' !== get_post_type( $post_id ) ) {
		return;
	}

	alynt_faq_clear_collection_cache();
}

/**
 * Clear all collection transient caches when FAQs or collections are modified.
 *
 * Deletes all transients whose names match the alynt_faq_collections_ prefix
 * directly via a targeted SQL query to avoid loading every transient into memory.
 *
 * @since 1.0.0
 *
 * @return void
 */
function alynt_faq_clear_collection_cache() {
	global $wpdb;

	$new_version = md5( (string) microtime( true ) . wp_rand() );
	$updated     = update_option( 'alynt_faq_collection_cache_version', $new_version, false );

	if ( false === $updated && get_option( 'alynt_faq_collection_cache_version', '' ) !== $new_version ) {
		return;
	}

	// Delete only our specific transients and their timeout rows.
	$result = $wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_alynt_faq_collections_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_alynt_faq_collections_' ) . '%'
		)
	);

	if ( false === $result ) {
		return;
	}
}

// Clear cache when FAQs or collections are modified.
add_action( 'save_post_alynt_faq', 'alynt_faq_clear_collection_cache' );
add_action( 'before_delete_post', 'alynt_faq_clear_collection_cache_for_post' );
add_action( 'trashed_post', 'alynt_faq_clear_collection_cache_for_post' );
add_action( 'untrashed_post', 'alynt_faq_clear_collection_cache_for_post' );
add_action( 'edited_alynt_faq_collection', 'alynt_faq_clear_collection_cache' );
add_action( 'created_alynt_faq_collection', 'alynt_faq_clear_collection_cache' );
add_action( 'deleted_alynt_faq_collection', 'alynt_faq_clear_collection_cache' );
