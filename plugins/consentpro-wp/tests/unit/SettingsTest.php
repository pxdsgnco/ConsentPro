<?php
/**
 * Settings round-trip tests.
 *
 * @package ConsentPro
 */

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Test ConsentPro_Settings class.
 */
class SettingsTest extends TestCase {

	/**
	 * Settings instance.
	 *
	 * @var ConsentPro_Settings
	 */
	private $settings;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();
		global $consentpro_test_options;
		$consentpro_test_options = [];
		$this->settings          = new ConsentPro_Settings();
	}

	/**
	 * Tear down test fixtures.
	 */
	protected function tear_down(): void {
		global $consentpro_test_options;
		$consentpro_test_options = [];
		parent::tear_down();
	}

	/**
	 * Test general settings round-trip - save and load with all fields.
	 */
	public function test_general_settings_round_trip(): void {
		// Arrange: Input data.
		$input = [
			'enabled'     => '1',
			'policy_url'  => 'https://example.com/privacy-policy',
			'geo_enabled' => '1',
		];

		// Act: Sanitize and save.
		$sanitized = $this->settings->sanitize_general( $input );
		update_option( 'consentpro_general', $sanitized );

		// Assert: Load and verify.
		$loaded = get_option( 'consentpro_general' );

		$this->assertIsArray( $loaded );
		$this->assertTrue( $loaded['enabled'] );
		$this->assertSame( 'https://example.com/privacy-policy', $loaded['policy_url'] );
		$this->assertTrue( $loaded['geo_enabled'] );
	}

	/**
	 * Test general settings round-trip - unchecked checkboxes.
	 */
	public function test_general_settings_unchecked_checkboxes(): void {
		// Arrange: Input without checkboxes (unchecked behavior).
		$input = [
			'policy_url' => 'https://example.com/privacy',
		];

		// Act.
		$sanitized = $this->settings->sanitize_general( $input );
		update_option( 'consentpro_general', $sanitized );
		$loaded = get_option( 'consentpro_general' );

		// Assert.
		$this->assertFalse( $loaded['enabled'] );
		$this->assertFalse( $loaded['geo_enabled'] );
		$this->assertSame( 'https://example.com/privacy', $loaded['policy_url'] );
	}

	/**
	 * Test general settings - URL sanitization removes dangerous protocols.
	 */
	public function test_general_settings_url_sanitization(): void {
		// Arrange: Input with potentially unsafe URL.
		$input = [
			'enabled'     => '1',
			'policy_url'  => 'javascript:alert("xss")',
			'geo_enabled' => '',
		];

		// Act.
		$sanitized = $this->settings->sanitize_general( $input );

		// Assert: Malicious URL should be sanitized.
		$this->assertSame( '', $sanitized['policy_url'] );
	}

	/**
	 * Test general settings - empty policy URL is allowed.
	 */
	public function test_general_settings_empty_policy_url(): void {
		$input = [
			'enabled'    => '1',
			'policy_url' => '',
		];

		$sanitized = $this->settings->sanitize_general( $input );

		$this->assertSame( '', $sanitized['policy_url'] );
		$this->assertTrue( $sanitized['enabled'] );
	}

	/**
	 * Test appearance settings round-trip with valid hex colors.
	 */
	public function test_appearance_settings_round_trip(): void {
		// Arrange.
		$input = [
			'color_primary'    => '#ff5733',
			'color_secondary'  => '#33ff57',
			'color_background' => '#f0f0f0',
			'color_text'       => '#333333',
		];

		// Act.
		$sanitized = $this->settings->sanitize_appearance( $input );
		update_option( 'consentpro_appearance', $sanitized );
		$loaded = get_option( 'consentpro_appearance' );

		// Assert.
		$this->assertSame( '#ff5733', $loaded['color_primary'] );
		$this->assertSame( '#33ff57', $loaded['color_secondary'] );
		$this->assertSame( '#f0f0f0', $loaded['color_background'] );
		$this->assertSame( '#333333', $loaded['color_text'] );
	}

	/**
	 * Test appearance settings - invalid hex falls back to defaults.
	 */
	public function test_appearance_settings_invalid_hex_uses_default(): void {
		$input = [
			'color_primary'    => 'not-a-color',
			'color_secondary'  => 'rgb(255,0,0)',
			'color_background' => '',
			'color_text'       => '#1e293b', // Valid.
		];

		$sanitized = $this->settings->sanitize_appearance( $input );

		// Invalid colors should fall back to defaults.
		$this->assertSame( '#2563eb', $sanitized['color_primary'] );
		$this->assertSame( '#64748b', $sanitized['color_secondary'] );
		$this->assertSame( '#ffffff', $sanitized['color_background'] );
		$this->assertSame( '#1e293b', $sanitized['color_text'] );
	}

	/**
	 * Test appearance settings - 3-digit hex codes are valid.
	 */
	public function test_appearance_settings_shorthand_hex(): void {
		$input = [
			'color_primary'    => '#f00',
			'color_secondary'  => '#0f0',
			'color_background' => '#00f',
			'color_text'       => '#fff',
		];

		$sanitized = $this->settings->sanitize_appearance( $input );

		$this->assertSame( '#f00', $sanitized['color_primary'] );
		$this->assertSame( '#0f0', $sanitized['color_secondary'] );
		$this->assertSame( '#00f', $sanitized['color_background'] );
		$this->assertSame( '#fff', $sanitized['color_text'] );
	}

	/**
	 * Test category settings round-trip.
	 */
	public function test_category_settings_round_trip(): void {
		$input = [
			'analytics' => [
				'name'        => 'Analytics Cookies',
				'description' => 'Help us understand site usage.',
				'default'     => '1',
			],
			'marketing' => [
				'name'        => 'Marketing',
				'description' => '<script>alert("xss")</script>Ad targeting',
				'default'     => '',
			],
		];

		$sanitized = $this->settings->sanitize_categories( $input );
		update_option( 'consentpro_categories', $sanitized );
		$loaded = get_option( 'consentpro_categories' );

		// Assert analytics.
		$this->assertSame( 'Analytics Cookies', $loaded['analytics']['name'] );
		$this->assertSame( 'Help us understand site usage.', $loaded['analytics']['description'] );
		$this->assertTrue( $loaded['analytics']['default'] );

		// Assert marketing - XSS should be stripped.
		$this->assertSame( 'Marketing', $loaded['marketing']['name'] );
		$this->assertStringNotContainsString( '<script>', $loaded['marketing']['description'] );
		$this->assertFalse( $loaded['marketing']['default'] );
	}

	/**
	 * Test category settings - keys are sanitized.
	 */
	public function test_category_settings_key_sanitization(): void {
		$input = [
			'ANALYTICS' => [
				'name'        => 'Analytics',
				'description' => 'Test',
				'default'     => '',
			],
			'my-category' => [
				'name'        => 'My Category',
				'description' => 'Test',
				'default'     => '1',
			],
		];

		$sanitized = $this->settings->sanitize_categories( $input );

		// Keys should be lowercase.
		$this->assertArrayHasKey( 'analytics', $sanitized );
		$this->assertArrayHasKey( 'my-category', $sanitized );
		$this->assertArrayNotHasKey( 'ANALYTICS', $sanitized );
	}

	/**
	 * Test category settings - empty values handled correctly.
	 */
	public function test_category_settings_empty_values(): void {
		$input = [
			'personalization' => [
				// Missing 'name' and 'description'.
				'default' => '',
			],
		];

		$sanitized = $this->settings->sanitize_categories( $input );

		$this->assertSame( '', $sanitized['personalization']['name'] );
		$this->assertSame( '', $sanitized['personalization']['description'] );
		$this->assertFalse( $sanitized['personalization']['default'] );
	}
}
