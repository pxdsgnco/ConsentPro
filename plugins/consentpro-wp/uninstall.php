<?php
/**
 * Uninstall ConsentPro.
 *
 * @package ConsentPro
 */

// Exit if not called by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete options.
delete_option( 'consentpro_general' );
delete_option( 'consentpro_appearance' );
delete_option( 'consentpro_categories' );
delete_option( 'consentpro_license' );
delete_option( 'consentpro_license_key' );

// Clear scheduled events.
$consentpro_timestamp = wp_next_scheduled( 'consentpro_validate_license' );
if ( $consentpro_timestamp ) {
	wp_unschedule_event( $consentpro_timestamp, 'consentpro_validate_license' );
}

$consentpro_prune_timestamp = wp_next_scheduled( 'consentpro_prune_consent_log' );
if ( $consentpro_prune_timestamp ) {
	wp_unschedule_event( $consentpro_prune_timestamp, 'consentpro_prune_consent_log' );
}

// Drop consent log table.
global $wpdb;
$consentpro_table = $wpdb->prefix . 'consentpro_log';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$wpdb->query( "DROP TABLE IF EXISTS $consentpro_table" );
delete_option( 'consentpro_db_version' );
