<?php
/**
 * AJAX handlers for consent logging.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX handler class.
 */
class ConsentPro_Consent_Ajax {

	/**
	 * Register AJAX hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		// Admin AJAX endpoints (require manage_options).
		add_action( 'wp_ajax_consentpro_get_metrics', [ $this, 'get_metrics' ] );
		add_action( 'wp_ajax_consentpro_get_log_entries', [ $this, 'get_log_entries' ] );
		add_action( 'wp_ajax_consentpro_clear_log', [ $this, 'clear_log' ] );
	}

	/**
	 * Get consent metrics.
	 *
	 * @return void
	 */
	public function get_metrics(): void {
		check_ajax_referer( 'consentpro_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized', 'consentpro' ) ], 403 );
		}

		if ( ! ConsentPro_License::is_pro() ) {
			wp_send_json_error( [ 'message' => __( 'Pro license required', 'consentpro' ) ], 403 );
		}

		$days        = isset( $_POST['days'] ) ? absint( $_POST['days'] ) : 30;
		$consent_log = new ConsentPro_Consent_Log();
		$metrics     = $consent_log->get_metrics( $days );

		wp_send_json_success( $metrics );
	}

	/**
	 * Get paginated log entries.
	 *
	 * @return void
	 */
	public function get_log_entries(): void {
		check_ajax_referer( 'consentpro_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized', 'consentpro' ) ], 403 );
		}

		if ( ! ConsentPro_License::is_pro() ) {
			wp_send_json_error( [ 'message' => __( 'Pro license required', 'consentpro' ) ], 403 );
		}

		$page     = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		$per_page = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 50;

		// Enforce reasonable limits.
		$per_page = min( $per_page, 100 );

		$consent_log = new ConsentPro_Consent_Log();
		$result      = $consent_log->get_log_entries( $page, $per_page );

		wp_send_json_success( $result );
	}

	/**
	 * Clear the consent log.
	 *
	 * @return void
	 */
	public function clear_log(): void {
		check_ajax_referer( 'consentpro_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized', 'consentpro' ) ], 403 );
		}

		if ( ! ConsentPro_License::is_pro() ) {
			wp_send_json_error( [ 'message' => __( 'Pro license required', 'consentpro' ) ], 403 );
		}

		$consent_log = new ConsentPro_Consent_Log();
		$deleted     = $consent_log->clear_log();

		wp_send_json_success(
			[
				'message' => sprintf(
					/* translators: %d: Number of entries deleted */
					__( 'Cleared %d log entries.', 'consentpro' ),
					$deleted
				),
				'deleted' => $deleted,
			]
		);
	}
}
