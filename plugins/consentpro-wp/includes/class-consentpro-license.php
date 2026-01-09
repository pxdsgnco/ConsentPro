<?php
/**
 * License validation handler.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * License manager class.
 */
class ConsentPro_License {

	/**
	 * License API endpoint.
	 *
	 * @var string
	 */
	private const API_ENDPOINT = 'https://api.consentpro.io/v1/license/validate';

	/**
	 * License data option name.
	 *
	 * @var string
	 */
	private const OPTION_NAME = 'consentpro_license';

	/**
	 * Grace period in days.
	 *
	 * @var int
	 */
	private const GRACE_PERIOD_DAYS = 7;

	/**
	 * Check if current license is Pro tier.
	 *
	 * @return bool
	 */
	public static function is_pro(): bool {
		$license = get_option( self::OPTION_NAME, [] );

		if ( empty( $license['valid'] ) ) {
			return false;
		}

		if ( empty( $license['tier'] ) || 'core' === $license['tier'] ) {
			return false;
		}

		// Check expiry.
		if ( ! empty( $license['expires'] ) ) {
			$expires = strtotime( $license['expires'] );
			if ( $expires && time() > $expires ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validate license key with remote API.
	 *
	 * @param string $key License key.
	 * @return array Validation result.
	 */
	public static function validate( string $key ): array {
		$response = wp_remote_post(
			self::API_ENDPOINT,
			[
				'timeout' => 15,
				'body'    => [
					'key'     => $key,
					'domain'  => home_url(),
					'version' => CONSENTPRO_VERSION,
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			return [
				'valid' => false,
				'error' => $response->get_error_message(),
			];
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) ) {
			return [
				'valid' => false,
				'error' => __( 'Invalid API response', 'consentpro' ),
			];
		}

		// Store license data.
		if ( ! empty( $data['valid'] ) ) {
			$data['last_check'] = time();
			update_option( self::OPTION_NAME, $data );
		}

		return $data;
	}

	/**
	 * Schedule weekly license validation.
	 *
	 * @return void
	 */
	public static function schedule_validation(): void {
		if ( ! wp_next_scheduled( 'consentpro_validate_license' ) ) {
			wp_schedule_event( time(), 'weekly', 'consentpro_validate_license' );
		}
	}
}
