<?php
/**
 * Banner rendering tests.
 *
 * @package ConsentPro
 */

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Test ConsentPro_Banner class.
 */
class BannerTest extends TestCase {

	/**
	 * Banner instance.
	 *
	 * @var ConsentPro_Banner
	 */
	private $banner;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();
		global $consentpro_test_options, $consentpro_test_filters;
		$consentpro_test_options = [];
		$consentpro_test_filters = [];
		$this->banner            = new ConsentPro_Banner();

		// Clear server variables.
		unset( $_SERVER['HTTP_CF_IPCOUNTRY'] );
	}

	/**
	 * Tear down test fixtures.
	 */
	protected function tear_down(): void {
		global $consentpro_test_options, $consentpro_test_filters;
		$consentpro_test_options = [];
		$consentpro_test_filters = [];
		unset( $_SERVER['HTTP_CF_IPCOUNTRY'] );
		parent::tear_down();
	}

	/**
	 * Test render outputs banner container with required attributes.
	 */
	public function test_render_outputs_banner_container(): void {
		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'id="consentpro-banner"', $output );
		$this->assertStringContainsString( 'class="consentpro"', $output );
		$this->assertStringContainsString( 'role="dialog"', $output );
		$this->assertStringContainsString( 'aria-labelledby="consentpro-heading"', $output );
		$this->assertStringContainsString( 'aria-modal="false"', $output );
		$this->assertStringContainsString( 'data-config=', $output );
	}

	/**
	 * Test render includes valid JSON in data-config.
	 */
	public function test_render_outputs_valid_json_config(): void {
		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		// Extract data-config value.
		preg_match( '/data-config="([^"]+)"/', $output, $matches );
		$this->assertNotEmpty( $matches[1] );

		// Decode HTML entities and parse JSON.
		$json   = html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' );
		$config = json_decode( $json, true );

		$this->assertIsArray( $config );
		$this->assertArrayHasKey( 'geo', $config );
		$this->assertArrayHasKey( 'geoEnabled', $config );
		$this->assertArrayHasKey( 'policyUrl', $config );
		$this->assertArrayHasKey( 'categories', $config );
		$this->assertArrayHasKey( 'text', $config );
		$this->assertArrayHasKey( 'colors', $config );
	}

	/**
	 * Test config includes default colors when no options set.
	 */
	public function test_config_includes_default_colors(): void {
		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		preg_match( '/data-config="([^"]+)"/', $output, $matches );
		$config = json_decode( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ), true );

		$this->assertSame( '#2563eb', $config['colors']['primary'] );
		$this->assertSame( '#64748b', $config['colors']['secondary'] );
		$this->assertSame( '#ffffff', $config['colors']['background'] );
		$this->assertSame( '#1e293b', $config['colors']['text'] );
	}

	/**
	 * Test config uses custom colors from options.
	 */
	public function test_config_uses_custom_colors(): void {
		update_option(
			'consentpro_appearance',
			[
				'color_primary'    => '#ff0000',
				'color_secondary'  => '#00ff00',
				'color_background' => '#0000ff',
				'color_text'       => '#ffffff',
			]
		);

		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		preg_match( '/data-config="([^"]+)"/', $output, $matches );
		$config = json_decode( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ), true );

		$this->assertSame( '#ff0000', $config['colors']['primary'] );
		$this->assertSame( '#00ff00', $config['colors']['secondary'] );
		$this->assertSame( '#0000ff', $config['colors']['background'] );
		$this->assertSame( '#ffffff', $config['colors']['text'] );
	}

	/**
	 * Test config includes policy URL from options.
	 */
	public function test_config_includes_policy_url(): void {
		update_option(
			'consentpro_general',
			[
				'policy_url' => 'https://example.com/privacy-policy',
			]
		);

		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		preg_match( '/data-config="([^"]+)"/', $output, $matches );
		$config = json_decode( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ), true );

		$this->assertSame( 'https://example.com/privacy-policy', $config['policyUrl'] );
	}

	/**
	 * Test geo detection returns EU for EU countries.
	 *
	 * @dataProvider eu_country_provider
	 *
	 * @param string $country_code Country code.
	 */
	public function test_geo_detection_returns_eu_for_eu_countries( string $country_code ): void {
		$_SERVER['HTTP_CF_IPCOUNTRY'] = $country_code;

		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		preg_match( '/data-config="([^"]+)"/', $output, $matches );
		$config = json_decode( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ), true );

		$this->assertSame( 'EU', $config['geo'] );
	}

	/**
	 * Data provider for EU countries.
	 *
	 * @return array
	 */
	public static function eu_country_provider(): array {
		return [
			'Austria'     => [ 'AT' ],
			'Belgium'     => [ 'BE' ],
			'Germany'     => [ 'DE' ],
			'France'      => [ 'FR' ],
			'Italy'       => [ 'IT' ],
			'Spain'       => [ 'ES' ],
			'Netherlands' => [ 'NL' ],
			'Poland'      => [ 'PL' ],
			'Sweden'      => [ 'SE' ],
			'Ireland'     => [ 'IE' ],
		];
	}

	/**
	 * Test geo detection returns CA for Canada.
	 */
	public function test_geo_detection_returns_ca_for_canada(): void {
		$_SERVER['HTTP_CF_IPCOUNTRY'] = 'CA';

		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		preg_match( '/data-config="([^"]+)"/', $output, $matches );
		$config = json_decode( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ), true );

		$this->assertSame( 'CA', $config['geo'] );
	}

	/**
	 * Test geo detection returns null for US.
	 */
	public function test_geo_detection_returns_null_for_us(): void {
		$_SERVER['HTTP_CF_IPCOUNTRY'] = 'US';

		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		preg_match( '/data-config="([^"]+)"/', $output, $matches );
		$config = json_decode( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ), true );

		$this->assertNull( $config['geo'] );
	}

	/**
	 * Test geo detection returns null when header missing.
	 */
	public function test_geo_detection_returns_null_when_header_missing(): void {
		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		preg_match( '/data-config="([^"]+)"/', $output, $matches );
		$config = json_decode( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ), true );

		$this->assertNull( $config['geo'] );
	}

	/**
	 * Test geo detection handles lowercase country codes.
	 */
	public function test_geo_detection_handles_lowercase(): void {
		$_SERVER['HTTP_CF_IPCOUNTRY'] = 'de';

		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		preg_match( '/data-config="([^"]+)"/', $output, $matches );
		$config = json_decode( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ), true );

		$this->assertSame( 'EU', $config['geo'] );
	}

	/**
	 * Test geoEnabled reflects option setting.
	 */
	public function test_geo_enabled_reflects_option(): void {
		update_option(
			'consentpro_general',
			[
				'geo_enabled' => true,
			]
		);

		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		preg_match( '/data-config="([^"]+)"/', $output, $matches );
		$config = json_decode( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ), true );

		$this->assertTrue( $config['geoEnabled'] );
	}

	/**
	 * Test default categories are formatted correctly.
	 */
	public function test_default_categories_format(): void {
		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		preg_match( '/data-config="([^"]+)"/', $output, $matches );
		$config = json_decode( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ), true );

		$categories = $config['categories'];

		$this->assertCount( 4, $categories );

		// Essential should be first and required.
		$this->assertSame( 'essential', $categories[0]['id'] );
		$this->assertTrue( $categories[0]['required'] );

		// Other categories should not be required.
		$this->assertSame( 'analytics', $categories[1]['id'] );
		$this->assertFalse( $categories[1]['required'] );

		$this->assertSame( 'marketing', $categories[2]['id'] );
		$this->assertFalse( $categories[2]['required'] );

		$this->assertSame( 'personalization', $categories[3]['id'] );
		$this->assertFalse( $categories[3]['required'] );
	}

	/**
	 * Test custom category names from options.
	 */
	public function test_custom_category_names(): void {
		update_option(
			'consentpro_categories',
			[
				'analytics' => [
					'name'        => 'Custom Analytics Name',
					'description' => 'Custom description',
				],
			]
		);

		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		preg_match( '/data-config="([^"]+)"/', $output, $matches );
		$config = json_decode( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ), true );

		$analytics = array_filter( $config['categories'], fn( $c ) => 'analytics' === $c['id'] );
		$analytics = array_values( $analytics )[0];

		$this->assertSame( 'Custom Analytics Name', $analytics['name'] );
		$this->assertSame( 'Custom description', $analytics['description'] );
	}

	/**
	 * Test text strings include defaults.
	 */
	public function test_text_strings_defaults(): void {
		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		preg_match( '/data-config="([^"]+)"/', $output, $matches );
		$config = json_decode( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ), true );

		$text = $config['text'];

		$this->assertSame( 'We value your privacy', $text['heading'] );
		$this->assertSame( 'Accept All', $text['acceptAll'] );
		$this->assertSame( 'Reject Non-Essential', $text['rejectNonEssential'] );
		$this->assertSame( 'Cookie Settings', $text['settings'] );
		$this->assertSame( 'Save Preferences', $text['save'] );
		$this->assertSame( 'Back', $text['back'] );
		$this->assertSame( 'Privacy Preferences', $text['settingsTitle'] );
		$this->assertSame( 'Privacy Settings', $text['footerToggle'] );
	}

	/**
	 * Test custom text from appearance options.
	 */
	public function test_custom_text_from_options(): void {
		update_option(
			'consentpro_appearance',
			[
				'text_heading'  => 'Your Privacy Matters',
				'text_accept'   => 'Accept Cookies',
				'text_reject'   => 'Decline Optional',
				'text_settings' => 'Preferences',
				'text_save'     => 'Confirm',
			]
		);

		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		preg_match( '/data-config="([^"]+)"/', $output, $matches );
		$config = json_decode( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ), true );

		$text = $config['text'];

		$this->assertSame( 'Your Privacy Matters', $text['heading'] );
		$this->assertSame( 'Accept Cookies', $text['acceptAll'] );
		$this->assertSame( 'Decline Optional', $text['rejectNonEssential'] );
		$this->assertSame( 'Preferences', $text['settings'] );
		$this->assertSame( 'Confirm', $text['save'] );
	}

	/**
	 * Test consentpro_config filter is applied.
	 */
	public function test_config_filter_is_applied(): void {
		add_filter(
			'consentpro_config',
			function ( $config ) {
				$config['customField'] = 'test_value';
				return $config;
			}
		);

		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		preg_match( '/data-config="([^"]+)"/', $output, $matches );
		$config = json_decode( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ), true );

		$this->assertSame( 'test_value', $config['customField'] );
	}

	/**
	 * Test consentpro_categories filter is applied.
	 */
	public function test_categories_filter_is_applied(): void {
		add_filter(
			'consentpro_categories',
			function ( $categories ) {
				$categories[] = [
					'id'          => 'custom',
					'name'        => 'Custom Category',
					'description' => 'Added via filter',
					'required'    => false,
				];
				return $categories;
			}
		);

		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		preg_match( '/data-config="([^"]+)"/', $output, $matches );
		$config = json_decode( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ), true );

		$this->assertCount( 5, $config['categories'] );

		$custom = array_filter( $config['categories'], fn( $c ) => 'custom' === $c['id'] );
		$this->assertNotEmpty( $custom );
	}

	/**
	 * Test data-config is properly escaped for XSS prevention.
	 */
	public function test_data_config_is_escaped(): void {
		update_option(
			'consentpro_general',
			[
				'policy_url' => 'https://example.com/privacy?a=1&b=2',
			]
		);

		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		// Ampersands should be encoded.
		$this->assertStringContainsString( '&amp;', $output );
		$this->assertStringNotContainsString( '"&b=2"', $output );
	}

	/**
	 * Test render handles empty options gracefully.
	 */
	public function test_render_handles_empty_options(): void {
		// All options empty - should not throw.
		ob_start();
		$this->banner->render();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'data-config=', $output );

		preg_match( '/data-config="([^"]+)"/', $output, $matches );
		$config = json_decode( html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' ), true );

		// Should have default structure.
		$this->assertIsArray( $config['categories'] );
		$this->assertIsArray( $config['colors'] );
		$this->assertIsArray( $config['text'] );
	}
}
