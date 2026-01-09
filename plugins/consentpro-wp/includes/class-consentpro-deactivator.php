<?php
/**
 * Plugin deactivator.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deactivation handler.
 */
class ConsentPro_Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * Clean up scheduled events.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		// Clear scheduled license validation cron.
		$timestamp = wp_next_scheduled( 'consentpro_validate_license' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'consentpro_validate_license' );
		}

		// Flush rewrite rules.
		flush_rewrite_rules();
	}
}
