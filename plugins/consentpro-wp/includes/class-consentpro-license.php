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
	 * Includes grace period logic: if API validation failed recently
	 * but we had a valid license before, allow grace period access.
	 *
	 * @return bool
	 */
	public static function is_pro(): bool {
		$license = get_option( self::OPTION_NAME, [] );

		// Check if we're in grace period (API failed but previously valid).
		if ( ! empty( $license['grace_period_start'] ) && ! empty( $license['was_valid'] ) ) {
			$grace_start = (int) $license['grace_period_start'];
			$grace_end   = $grace_start + ( self::GRACE_PERIOD_DAYS * DAY_IN_SECONDS );

			// Still within grace period.
			if ( time() < $grace_end ) {
				return true;
			}
		}

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
	 * Get remaining grace period days.
	 *
	 * @return int|null Days remaining, or null if not in grace period.
	 */
	public static function get_grace_days_remaining(): ?int {
		$license = get_option( self::OPTION_NAME, [] );

		if ( empty( $license['grace_period_start'] ) ) {
			return null;
		}

		$grace_start = (int) $license['grace_period_start'];
		$grace_end   = $grace_start + ( self::GRACE_PERIOD_DAYS * DAY_IN_SECONDS );
		$remaining   = $grace_end - time();

		if ( $remaining <= 0 ) {
			return null;
		}

		return (int) ceil( $remaining / DAY_IN_SECONDS );
	}

	/**
	 * Validate license key with remote API.
	 *
	 * Handles grace period: if API fails but we had a valid license,
	 * start grace period countdown instead of immediately invalidating.
	 *
	 * @param string $key License key.
	 * @return array Validation result.
	 */
	public static function validate( string $key ): array {
		$current_license = get_option( self::OPTION_NAME, [] );
		$was_valid       = ! empty( $current_license['valid'] ) && ! empty( $current_license['tier'] ) && 'core' !== $current_license['tier'];

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

		// Handle API connection failure - enter grace period if previously valid.
		if ( is_wp_error( $response ) ) {
			if ( $was_valid ) {
				self::start_grace_period( $current_license );
				return [
					'valid'        => false,
					'error'        => $response->get_error_message(),
					'grace_period' => true,
				];
			}
			return [
				'valid' => false,
				'error' => $response->get_error_message(),
			];
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) ) {
			if ( $was_valid ) {
				self::start_grace_period( $current_license );
				return [
					'valid'        => false,
					'error'        => __( 'Invalid API response', 'consentpro' ),
					'grace_period' => true,
				];
			}
			return [
				'valid' => false,
				'error' => __( 'Invalid API response', 'consentpro' ),
			];
		}

		// Store license data.
		if ( ! empty( $data['valid'] ) ) {
			$data['last_check'] = time();
			// Clear any grace period since we got a valid response.
			unset( $data['grace_period_start'] );
			unset( $data['was_valid'] );
			update_option( self::OPTION_NAME, $data );
		} else {
			// License invalid - clear stored data.
			$data['last_check'] = time();
			update_option( self::OPTION_NAME, $data );
		}

		return $data;
	}

	/**
	 * Start grace period for license validation.
	 *
	 * @param array $current_license Current license data.
	 * @return void
	 */
	private static function start_grace_period( array $current_license ): void {
		// Only start grace period if not already in one.
		if ( empty( $current_license['grace_period_start'] ) ) {
			$current_license['grace_period_start'] = time();
			$current_license['was_valid']          = true;
			update_option( self::OPTION_NAME, $current_license );
		}
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
