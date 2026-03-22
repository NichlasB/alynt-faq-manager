<?php
/**
 * Admin asset enqueueing for the FAQ reorder and custom CSS pages.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes/admin
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Enqueue admin scripts and styles.
add_action( 'admin_enqueue_scripts', 'alynt_faq_admin_scripts' );

/**
 * Enqueue admin stylesheet and JavaScript on FAQ admin pages.
 *
 * Only loads assets on the Reorder FAQs and Custom CSS subpages.
 *
 * @since 1.0.0
 *
 * @param string $hook The current admin page hook suffix.
 *
 * @return void
 */
function alynt_faq_admin_scripts( $hook ) {
	if ( $hook !== 'alynt_faq_page_alynt-faq-order' && $hook !== 'alynt_faq_page_alynt-faq-custom-css' ) {
		return;
	}

	wp_enqueue_style(
		'alynt-faq-admin',
		ALYNT_FAQ_PLUGIN_URL . 'assets/css/admin.css',
		array(),
		ALYNT_FAQ_VERSION
	);

	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script(
		'alynt-faq-admin',
		ALYNT_FAQ_PLUGIN_URL . 'assets/js/admin.js',
		array( 'jquery', 'jquery-ui-sortable' ),
		ALYNT_FAQ_VERSION,
		true
	);

	$collection_id = filter_input( INPUT_GET, 'collection', FILTER_VALIDATE_INT );
	if ( false === $collection_id || null === $collection_id ) {
		$collection_id = 0;
	}

	wp_localize_script(
		'alynt-faq-admin',
		'alyntFaqAdmin',
		array(
			'ajaxurl'           => admin_url( 'admin-ajax.php' ),
			'nonce'             => wp_create_nonce( 'alynt_faq_reorder' ),
			'collectionId'      => $collection_id,
			'requestTimeout'    => 15000,
			'unsafeCssPatterns' => alynt_faq_get_unsafe_css_patterns(),
			'messages'          => array(
				'orderSaved'      => __( 'FAQ order has been updated.', 'alynt-faq' ),
				'orderSaving'     => __( 'Saving FAQ order...', 'alynt-faq' ),
				'error'           => __( 'An error occurred while saving the order.', 'alynt-faq' ),
				'cssSaved'        => __( 'Custom CSS saved successfully.', 'alynt-faq' ),
				'cssError'        => __( 'Error saving custom CSS.', 'alynt-faq' ),
				'cssSaving'       => __( 'Saving CSS...', 'alynt-faq' ),
				'cssResetConfirm' => __( 'Are you sure you want to reset to default CSS? This will remove all custom CSS.', 'alynt-faq' ),
				'cssFormatError'  => __( 'Please enter valid CSS rules that include both opening and closing braces.', 'alynt-faq' ),
				'cssUnsafeError'  => __( 'Please remove unsafe CSS content and try again.', 'alynt-faq' ),
				'networkError'    => __( 'Connection lost. Check your internet connection and try again.', 'alynt-faq' ),
				'timeoutError'    => __( 'The request took too long. Please try again.', 'alynt-faq' ),
				'serverError'     => __( 'The server could not complete the request. Please try again in a few minutes.', 'alynt-faq' ),
				'unsavedChanges'  => __( 'You have unsaved CSS changes. If you leave now, your changes will be lost.', 'alynt-faq' ),
			),
		)
	);
}
