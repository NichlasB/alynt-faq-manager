<?php
/**
 * Plugin deactivator.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes
 * @since      1.0.7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin deactivation.
 */
class ALYNT_FAQ_Deactivator {
	/**
	 * Runs the plugin deactivation routine.
	 *
	 * @return void
	 */
	public static function deactivate() {
		if ( function_exists( 'alynt_faq_deactivate' ) ) {
			alynt_faq_deactivate();
			return;
		}

		flush_rewrite_rules();
	}
}
