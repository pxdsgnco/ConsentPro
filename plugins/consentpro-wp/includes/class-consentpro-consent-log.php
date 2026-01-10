<?php
/**
 * Consent logging functionality.
 *
 * @package ConsentPro
 */

// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names cannot use placeholders.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent log handler.
 */
class ConsentPro_Consent_Log {

	/**
	 * Table name.
	 *
	 * @var string
	 */
	private string $table_name;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'consentpro_log';
	}

	/**
	 * Create the database table.
	 *
	 * @return void
	 */
	public static function create_table(): void {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'consentpro_log';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			consent_type VARCHAR(30) NOT NULL,
			categories VARCHAR(255) NOT NULL,
			region VARCHAR(10) DEFAULT NULL,
			visitor_hash VARCHAR(64) DEFAULT NULL,
			PRIMARY KEY (id),
			KEY idx_timestamp (timestamp),
			KEY idx_consent_type (consent_type),
			KEY idx_region (region)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Store DB version for future migrations.
		update_option( 'consentpro_db_version', '1.0.0' );
	}

	/**
	 * Drop the database table.
	 *
	 * @return void
	 */
	public static function drop_table(): void {
		global $wpdb;
		$table_name = $wpdb->prefix . 'consentpro_log';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
		delete_option( 'consentpro_db_version' );
	}

	/**
	 * Log a consent event from cookie data.
	 *
	 * @param array $consent_data Parsed consent data from cookie.
	 * @return bool Whether the log was inserted.
	 */
	public function log_consent( array $consent_data ): bool {
		global $wpdb;

		// Validate required fields.
		if ( empty( $consent_data['categories'] ) ) {
			return false;
		}

		$categories = $consent_data['categories'];

		// Determine consent type.
		$consent_type = $this->determine_consent_type( $categories );

		// Generate visitor hash for deduplication (daily rotation).
		$visitor_hash = $this->generate_visitor_hash();

		// Check if we already logged this visitor today.
		if ( $this->has_logged_today( $visitor_hash ) ) {
			return false;
		}

		// Insert the log entry.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			$this->table_name,
			[
				'timestamp'    => current_time( 'mysql', true ),
				'consent_type' => $consent_type,
				'categories'   => wp_json_encode( $categories ),
				'region'       => isset( $consent_data['geo'] ) ? sanitize_text_field( $consent_data['geo'] ) : null,
				'visitor_hash' => $visitor_hash,
			],
			[ '%s', '%s', '%s', '%s', '%s' ]
		);

		return false !== $result;
	}

	/**
	 * Determine consent type from categories.
	 *
	 * @param array $categories Category consent states.
	 * @return string Consent type.
	 */
	private function determine_consent_type( array $categories ): string {
		$non_essential = [ 'analytics', 'marketing', 'personalization' ];

		$all_accepted = true;
		$all_rejected = true;

		foreach ( $non_essential as $cat ) {
			if ( empty( $categories[ $cat ] ) ) {
				$all_accepted = false;
			} else {
				$all_rejected = false;
			}
		}

		if ( $all_accepted ) {
			return 'accept_all';
		}

		if ( $all_rejected ) {
			return 'reject_non_essential';
		}

		return 'custom';
	}

	/**
	 * Generate a hashed visitor identifier.
	 *
	 * Uses daily salt rotation for privacy.
	 *
	 * @return string SHA-256 hash.
	 */
	private function generate_visitor_hash(): string {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$daily_salt = gmdate( 'Y-m-d' );

		return hash( 'sha256', $daily_salt . $ip . $user_agent );
	}

	/**
	 * Check if visitor was already logged today.
	 *
	 * @param string $visitor_hash Visitor hash.
	 * @return bool
	 */
	private function has_logged_today( string $visitor_hash ): bool {
		global $wpdb;

		$today_start = gmdate( 'Y-m-d 00:00:00' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->table_name} WHERE visitor_hash = %s AND timestamp >= %s",
				$visitor_hash,
				$today_start
			)
		);

		return (int) $count > 0;
	}

	/**
	 * Get metrics for dashboard widget.
	 *
	 * @param int $days Number of days to look back.
	 * @return array Metrics data.
	 */
	public function get_metrics( int $days = 30 ): array {
		global $wpdb;

		$since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					consent_type,
					COUNT(*) as count
				FROM {$this->table_name}
				WHERE timestamp >= %s
				GROUP BY consent_type",
				$since
			),
			ARRAY_A
		);

		$metrics = [
			'total'                => 0,
			'accept_all'           => 0,
			'reject_non_essential' => 0,
			'custom'               => 0,
			'accept_percent'       => 0,
			'reject_percent'       => 0,
			'custom_percent'       => 0,
		];

		if ( $results ) {
			foreach ( $results as $row ) {
				$metrics[ $row['consent_type'] ] = (int) $row['count'];
				$metrics['total']               += (int) $row['count'];
			}
		}

		// Calculate percentages.
		if ( $metrics['total'] > 0 ) {
			$metrics['accept_percent'] = round(
				( $metrics['accept_all'] / $metrics['total'] ) * 100,
				1
			);
			$metrics['reject_percent'] = round(
				( $metrics['reject_non_essential'] / $metrics['total'] ) * 100,
				1
			);
			$metrics['custom_percent'] = round(
				( $metrics['custom'] / $metrics['total'] ) * 100,
				1
			);
		}

		return $metrics;
	}

	/**
	 * Get paginated log entries.
	 *
	 * @param int $page     Page number (1-indexed).
	 * @param int $per_page Items per page.
	 * @return array Log entries and pagination info.
	 */
	public function get_log_entries( int $page = 1, int $per_page = 50 ): array {
		global $wpdb;

		$offset = ( $page - 1 ) * $per_page;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$entries = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, timestamp, consent_type, categories, region
				FROM {$this->table_name}
				ORDER BY timestamp DESC
				LIMIT %d OFFSET %d",
				$per_page,
				$offset
			),
			ARRAY_A
		);

		return [
			'entries'     => $entries ? $entries : [],
			'total'       => $total,
			'page'        => $page,
			'per_page'    => $per_page,
			'total_pages' => (int) ceil( $total / $per_page ),
		];
	}

	/**
	 * Clear all log entries.
	 *
	 * @return int Number of rows deleted.
	 */
	public function clear_log(): int {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "TRUNCATE TABLE {$this->table_name}" );

		return $count;
	}

	/**
	 * Prune entries older than specified days.
	 *
	 * @param int $days Days to keep.
	 * @return int Number of rows deleted.
	 */
	public function prune_old_entries( int $days = 90 ): int {
		global $wpdb;

		$cutoff = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$this->table_name} WHERE timestamp < %s",
				$cutoff
			)
		);

		return false !== $result ? (int) $result : 0;
	}
}
