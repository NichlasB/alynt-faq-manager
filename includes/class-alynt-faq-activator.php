<?php
/**
 * Plugin activator.
 *
 * @package    Alynt_FAQ_Manager
 * @subpackage Alynt_FAQ_Manager/includes
 * @since      1.0.7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin activation.
 */
class ALYNT_FAQ_Activator {
	/**
	 * Runs the plugin activation routine.
	 *
	 * @return void
	 */
	public static function activate() {
		if ( function_exists( 'alynt_faq_activate' ) ) {
			alynt_faq_activate();
			return;
		}

		flush_rewrite_rules();
	}
}
