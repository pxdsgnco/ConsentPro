<?php
/**
 * License validation tests.
 *
 * @package ConsentPro
 */

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Test ConsentPro_License class.
 */
class LicenseTest extends TestCase {

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();
		global $consentpro_test_options, $consentpro_test_remote_response, $consentpro_test_scheduled;
		$consentpro_test_options         = [];
		$consentpro_test_remote_response = null;
		$consentpro_test_scheduled       = [];
	}

	/**
	 * Tear down test fixtures.
	 */
	protected function tear_down(): void {
		global $consentpro_test_options, $consentpro_test_remote_response, $consentpro_test_scheduled;
		$consentpro_test_options         = [];
		$consentpro_test_remote_response = null;
		$consentpro_test_scheduled       = [];
		parent::tear_down();
	}

	/**
	 * Test is_pro returns false when no license exists.
	 */
	public function test_is_pro_returns_false_when_no_license(): void {
		$this->assertFalse( ConsentPro_License::is_pro() );
	}

	/**
	 * Test is_pro returns false for core tier.
	 */
	public function test_is_pro_returns_false_for_core_tier(): void {
		update_option(
			'consentpro_license',
			[
				'valid' => true,
				'tier'  => 'core',
			]
		);

		$this->assertFalse( ConsentPro_License::is_pro() );
	}

	/**
	 * Test is_pro returns true for pro tier.
	 */
	public function test_is_pro_returns_true_for_pro_tier(): void {
		update_option(
			'consentpro_license',
			[
				'valid' => true,
				'tier'  => 'pro',
			]
		);

		$this->assertTrue( ConsentPro_License::is_pro() );
	}

	/**
	 * Test is_pro returns true for enterprise tier.
	 */
	public function test_is_pro_returns_true_for_enterprise_tier(): void {
		update_option(
			'consentpro_license',
			[
				'valid' => true,
				'tier'  => 'enterprise',
			]
		);

		$this->assertTrue( ConsentPro_License::is_pro() );
	}

	/**
	 * Test is_pro returns false when license is invalid.
	 */
	public function test_is_pro_returns_false_when_invalid(): void {
		update_option(
			'consentpro_license',
			[
				'valid' => false,
				'tier'  => 'pro',
			]
		);

		$this->assertFalse( ConsentPro_License::is_pro() );
	}

	/**
	 * Test is_pro returns false for expired license.
	 */
	public function test_is_pro_returns_false_for_expired_license(): void {
		update_option(
			'consentpro_license',
			[
				'valid'   => true,
				'tier'    => 'pro',
				'expires' => gmdate( 'Y-m-d', strtotime( '-1 day' ) ),
			]
		);

		$this->assertFalse( ConsentPro_License::is_pro() );
	}

	/**
	 * Test is_pro returns true for non-expired license.
	 */
	public function test_is_pro_returns_true_for_non_expired_license(): void {
		update_option(
			'consentpro_license',
			[
				'valid'   => true,
				'tier'    => 'pro',
				'expires' => gmdate( 'Y-m-d', strtotime( '+30 days' ) ),
			]
		);

		$this->assertTrue( ConsentPro_License::is_pro() );
	}

	/**
	 * Test is_pro respects grace period when API fails.
	 */
	public function test_is_pro_respects_grace_period(): void {
		// Simulate grace period started 3 days ago.
		update_option(
			'consentpro_license',
			[
				'valid'              => false,
				'tier'               => 'pro',
				'grace_period_start' => time() - ( 3 * DAY_IN_SECONDS ),
				'was_valid'          => true,
			]
		);

		// Should still be valid within 7-day grace period.
		$this->assertTrue( ConsentPro_License::is_pro() );
	}

	/**
	 * Test is_pro returns false after grace period expires.
	 */
	public function test_is_pro_returns_false_after_grace_period_expires(): void {
		// Simulate grace period started 8 days ago (past 7-day limit).
		update_option(
			'consentpro_license',
			[
				'valid'              => false,
				'tier'               => 'pro',
				'grace_period_start' => time() - ( 8 * DAY_IN_SECONDS ),
				'was_valid'          => true,
			]
		);

		$this->assertFalse( ConsentPro_License::is_pro() );
	}

	/**
	 * Test is_pro handles edge case just before 7 days.
	 */
	public function test_is_pro_at_grace_period_boundary(): void {
		// Simulate grace period started just under 7 days ago (6 days, 23 hours, 59 minutes).
		update_option(
			'consentpro_license',
			[
				'valid'              => false,
				'tier'               => 'pro',
				'grace_period_start' => time() - ( 7 * DAY_IN_SECONDS ) + 60,
				'was_valid'          => true,
			]
		);

		// Just before 7 days expires, should still be valid.
		$this->assertTrue( ConsentPro_License::is_pro() );
	}

	/**
	 * Test is_pro returns false at exactly 7 days.
	 */
	public function test_is_pro_returns_false_at_exactly_seven_days(): void {
		// Simulate grace period started exactly 7 days ago.
		update_option(
			'consentpro_license',
			[
				'valid'              => false,
				'tier'               => 'pro',
				'grace_period_start' => time() - ( 7 * DAY_IN_SECONDS ),
				'was_valid'          => true,
			]
		);

		// At exactly 7 days, grace period has expired (time() < grace_end is false).
		$this->assertFalse( ConsentPro_License::is_pro() );
	}

	/**
	 * Test get_grace_days_remaining returns correct days.
	 */
	public function test_get_grace_days_remaining_returns_correct_days(): void {
		// Simulate grace period started 3 days ago.
		update_option(
			'consentpro_license',
			[
				'grace_period_start' => time() - ( 3 * DAY_IN_SECONDS ),
			]
		);

		$remaining = ConsentPro_License::get_grace_days_remaining();

		// Should have 4 days remaining (7 - 3 = 4).
		$this->assertSame( 4, $remaining );
	}

	/**
	 * Test get_grace_days_remaining returns null when not in grace period.
	 */
	public function test_get_grace_days_remaining_returns_null_when_not_in_grace(): void {
		update_option(
			'consentpro_license',
			[
				'valid' => true,
				'tier'  => 'pro',
			]
		);

		$this->assertNull( ConsentPro_License::get_grace_days_remaining() );
	}

	/**
	 * Test get_grace_days_remaining returns null after grace period expires.
	 */
	public function test_get_grace_days_remaining_returns_null_after_expiry(): void {
		update_option(
			'consentpro_license',
			[
				'grace_period_start' => time() - ( 10 * DAY_IN_SECONDS ),
			]
		);

		$this->assertNull( ConsentPro_License::get_grace_days_remaining() );
	}

	/**
	 * Test validate stores valid license data.
	 */
	public function test_validate_stores_valid_license_data(): void {
		global $consentpro_test_remote_response;

		$consentpro_test_remote_response = [
			'body'     => wp_json_encode(
				[
					'valid'   => true,
					'tier'    => 'pro',
					'expires' => '2025-12-31',
				]
			),
			'response' => [ 'code' => 200 ],
		];

		$result = ConsentPro_License::validate( 'test-license-key' );

		$this->assertTrue( $result['valid'] );
		$this->assertSame( 'pro', $result['tier'] );

		// Verify data stored.
		$stored = get_option( 'consentpro_license' );
		$this->assertTrue( $stored['valid'] );
		$this->assertSame( 'pro', $stored['tier'] );
		$this->assertArrayHasKey( 'last_check', $stored );
	}

	/**
	 * Test validate handles invalid license response.
	 */
	public function test_validate_handles_invalid_license(): void {
		global $consentpro_test_remote_response;

		$consentpro_test_remote_response = [
			'body'     => wp_json_encode(
				[
					'valid' => false,
					'error' => 'Invalid license key',
				]
			),
			'response' => [ 'code' => 200 ],
		];

		$result = ConsentPro_License::validate( 'invalid-key' );

		$this->assertFalse( $result['valid'] );
	}

	/**
	 * Test validate starts grace period on API error when previously valid.
	 */
	public function test_validate_starts_grace_period_on_api_error(): void {
		global $consentpro_test_remote_response;

		// Set up previously valid license.
		update_option(
			'consentpro_license',
			[
				'valid' => true,
				'tier'  => 'pro',
			]
		);

		// Simulate API error.
		$consentpro_test_remote_response = new WP_Error( 'http_error', 'Connection failed' );

		$result = ConsentPro_License::validate( 'test-key' );

		$this->assertFalse( $result['valid'] );
		$this->assertTrue( $result['grace_period'] );
		$this->assertSame( 'Connection failed', $result['error'] );

		// Verify grace period started.
		$stored = get_option( 'consentpro_license' );
		$this->assertArrayHasKey( 'grace_period_start', $stored );
		$this->assertTrue( $stored['was_valid'] );
	}

	/**
	 * Test validate does not start grace period if not previously valid.
	 */
	public function test_validate_no_grace_period_if_not_previously_valid(): void {
		global $consentpro_test_remote_response;

		// No previously valid license.
		update_option( 'consentpro_license', [] );

		// Simulate API error.
		$consentpro_test_remote_response = new WP_Error( 'http_error', 'Connection failed' );

		$result = ConsentPro_License::validate( 'test-key' );

		$this->assertFalse( $result['valid'] );
		$this->assertArrayNotHasKey( 'grace_period', $result );
	}

	/**
	 * Test validate handles malformed API response.
	 */
	public function test_validate_handles_malformed_response(): void {
		global $consentpro_test_remote_response;

		$consentpro_test_remote_response = [
			'body'     => 'not json',
			'response' => [ 'code' => 200 ],
		];

		$result = ConsentPro_License::validate( 'test-key' );

		$this->assertFalse( $result['valid'] );
		$this->assertSame( 'Invalid API response', $result['error'] );
	}

	/**
	 * Test validate clears grace period on successful validation.
	 */
	public function test_validate_clears_grace_period_on_success(): void {
		global $consentpro_test_remote_response;

		// Set up grace period.
		update_option(
			'consentpro_license',
			[
				'valid'              => false,
				'tier'               => 'pro',
				'grace_period_start' => time() - ( 2 * DAY_IN_SECONDS ),
				'was_valid'          => true,
			]
		);

		// Simulate successful validation.
		$consentpro_test_remote_response = [
			'body'     => wp_json_encode(
				[
					'valid' => true,
					'tier'  => 'pro',
				]
			),
			'response' => [ 'code' => 200 ],
		];

		ConsentPro_License::validate( 'test-key' );

		$stored = get_option( 'consentpro_license' );
		$this->assertArrayNotHasKey( 'grace_period_start', $stored );
		$this->assertArrayNotHasKey( 'was_valid', $stored );
	}

	/**
	 * Test schedule_validation schedules event when not scheduled.
	 */
	public function test_schedule_validation_schedules_event(): void {
		global $consentpro_test_scheduled;

		ConsentPro_License::schedule_validation();

		$this->assertArrayHasKey( 'consentpro_validate_license', $consentpro_test_scheduled );
	}

	/**
	 * Test schedule_validation does not reschedule if already scheduled.
	 */
	public function test_schedule_validation_does_not_reschedule(): void {
		global $consentpro_test_scheduled;

		// Pre-schedule.
		$original_time                                            = time() + 3600;
		$consentpro_test_scheduled['consentpro_validate_license'] = $original_time;

		ConsentPro_License::schedule_validation();

		// Should not have changed.
		$this->assertSame( $original_time, $consentpro_test_scheduled['consentpro_validate_license'] );
	}

	/**
	 * Test is_pro handles empty tier gracefully.
	 */
	public function test_is_pro_handles_empty_tier(): void {
		update_option(
			'consentpro_license',
			[
				'valid' => true,
				'tier'  => '',
			]
		);

		$this->assertFalse( ConsentPro_License::is_pro() );
	}

	/**
	 * Test is_pro handles missing tier key.
	 */
	public function test_is_pro_handles_missing_tier(): void {
		update_option(
			'consentpro_license',
			[
				'valid' => true,
			]
		);

		$this->assertFalse( ConsentPro_License::is_pro() );
	}
}
