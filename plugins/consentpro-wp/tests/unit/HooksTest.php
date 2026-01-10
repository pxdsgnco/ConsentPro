<?php
/**
 * Hook tests for ConsentPro.
 *
 * @package ConsentPro
 */

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Test ConsentPro filter hooks.
 */
class HooksTest extends TestCase {

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();
		global $consentpro_test_options, $consentpro_test_filters;
		$consentpro_test_options = [];
		$consentpro_test_filters = [];
	}

	/**
	 * Tear down test fixtures.
	 */
	protected function tear_down(): void {
		global $consentpro_test_options, $consentpro_test_filters;
		$consentpro_test_options = [];
		$consentpro_test_filters = [];
		parent::tear_down();
	}

	/**
	 * Test consentpro_config filter modifies config array.
	 */
	public function test_consentpro_config_filter_modifies_config(): void {
		// Arrange: Set up default options.
		update_option( 'consentpro_general', [
			'enabled'     => true,
			'policy_url'  => 'https://example.com/privacy',
			'geo_enabled' => true,
		] );
		update_option( 'consentpro_appearance', [
			'color_primary' => '#ff0000',
		] );
		update_option( 'consentpro_categories', [] );

		// Add filter to modify config.
		add_filter( 'consentpro_config', function( $config ) {
			$config['customField'] = 'test_value';
			$config['geoEnabled']  = false;
			return $config;
		} );

		// Act: Render banner and capture output.
		$banner = new ConsentPro_Banner();
		ob_start();
		$banner->render();
		$output = ob_get_clean();

		// Assert: Check the output contains modified config.
		$this->assertStringContainsString( 'data-config=', $output );
		$this->assertStringContainsString( 'customField', $output );
		$this->assertStringContainsString( 'test_value', $output );

		// Decode and verify config structure.
		preg_match( '/data-config="([^"]+)"/', $output, $matches );
		$config = json_decode( html_entity_decode( $matches[1] ), true );

		$this->assertSame( 'test_value', $config['customField'] );
		$this->assertFalse( $config['geoEnabled'] );
	}

	/**
	 * Test consentpro_config filter receives correct config structure.
	 */
	public function test_consentpro_config_filter_receives_correct_structure(): void {
		// Arrange.
		update_option( 'consentpro_general', [
			'enabled'     => true,
			'policy_url'  => 'https://example.com/policy',
			'geo_enabled' => false,
		] );
		update_option( 'consentpro_appearance', [] );
		update_option( 'consentpro_categories', [] );

		$captured_config = null;

		add_filter( 'consentpro_config', function( $config ) use ( &$captured_config ) {
			$captured_config = $config;
			return $config;
		} );

		// Act.
		$banner = new ConsentPro_Banner();
		ob_start();
		$banner->render();
		ob_get_clean();

		// Assert: Config has expected keys.
		$this->assertArrayHasKey( 'geo', $captured_config );
		$this->assertArrayHasKey( 'geoEnabled', $captured_config );
		$this->assertArrayHasKey( 'policyUrl', $captured_config );
		$this->assertArrayHasKey( 'categories', $captured_config );
		$this->assertArrayHasKey( 'text', $captured_config );
		$this->assertArrayHasKey( 'colors', $captured_config );
		$this->assertSame( 'https://example.com/policy', $captured_config['policyUrl'] );
	}

	/**
	 * Test consentpro_categories filter modifies categories array.
	 */
	public function test_consentpro_categories_filter_modifies_categories(): void {
		// Arrange.
		update_option( 'consentpro_general', [ 'enabled' => true ] );
		update_option( 'consentpro_appearance', [] );
		update_option( 'consentpro_categories', [] );

		// Add filter to add a custom category.
		add_filter( 'consentpro_categories', function( $categories ) {
			$categories[] = [
				'id'          => 'social',
				'name'        => 'Social Media',
				'description' => 'Social sharing features.',
				'required'    => false,
			];
			return $categories;
		} );

		// Act.
		$banner = new ConsentPro_Banner();
		ob_start();
		$banner->render();
		$output = ob_get_clean();

		// Assert: Output contains the custom category.
		$this->assertStringContainsString( 'social', $output );
		$this->assertStringContainsString( 'Social Media', $output );
	}

	/**
	 * Test consentpro_categories filter receives all default categories.
	 */
	public function test_consentpro_categories_filter_receives_default_categories(): void {
		// Arrange.
		update_option( 'consentpro_general', [ 'enabled' => true ] );
		update_option( 'consentpro_appearance', [] );
		update_option( 'consentpro_categories', [] );

		$captured_categories = null;

		add_filter( 'consentpro_categories', function( $categories ) use ( &$captured_categories ) {
			$captured_categories = $categories;
			return $categories;
		} );

		// Act.
		$banner = new ConsentPro_Banner();
		ob_start();
		$banner->render();
		ob_get_clean();

		// Assert: Has 4 default categories.
		$this->assertCount( 4, $captured_categories );

		$category_ids = array_column( $captured_categories, 'id' );
		$this->assertContains( 'essential', $category_ids );
		$this->assertContains( 'analytics', $category_ids );
		$this->assertContains( 'marketing', $category_ids );
		$this->assertContains( 'personalization', $category_ids );

		// Essential should be required.
		$essential = array_filter( $captured_categories, fn( $c ) => $c['id'] === 'essential' );
		$essential = array_values( $essential )[0];
		$this->assertTrue( $essential['required'] );
	}

	/**
	 * Test consentpro_categories filter can modify existing category.
	 */
	public function test_consentpro_categories_filter_modifies_existing_category(): void {
		// Arrange.
		update_option( 'consentpro_general', [ 'enabled' => true ] );
		update_option( 'consentpro_appearance', [] );
		update_option( 'consentpro_categories', [] );

		add_filter( 'consentpro_categories', function( $categories ) {
			foreach ( $categories as &$category ) {
				if ( $category['id'] === 'analytics' ) {
					$category['description'] = 'Custom analytics description.';
				}
			}
			return $categories;
		} );

		// Act.
		$banner = new ConsentPro_Banner();
		ob_start();
		$banner->render();
		$output = ob_get_clean();

		// Assert.
		$this->assertStringContainsString( 'Custom analytics description.', $output );
	}

	/**
	 * Test consentpro_should_show filter controls banner visibility.
	 */
	public function test_consentpro_should_show_filter_controls_visibility(): void {
		// Arrange: Enable banner in options.
		update_option( 'consentpro_general', [ 'enabled' => true ] );

		// Add filter to hide banner.
		add_filter( 'consentpro_should_show', function( $should_show ) {
			return false;
		} );

		// Act: Test that filter value is returned.
		$result = apply_filters( 'consentpro_should_show', true );

		// Assert.
		$this->assertFalse( $result );
	}

	/**
	 * Test consentpro_should_show filter receives boolean.
	 */
	public function test_consentpro_should_show_filter_receives_boolean(): void {
		$captured_value = null;

		add_filter( 'consentpro_should_show', function( $should_show ) use ( &$captured_value ) {
			$captured_value = $should_show;
			return $should_show;
		} );

		// Act.
		apply_filters( 'consentpro_should_show', true );

		// Assert.
		$this->assertTrue( $captured_value );
	}

	/**
	 * Test consentpro_assets_url filter changes asset URL.
	 */
	public function test_consentpro_assets_url_filter_changes_url(): void {
		// Arrange: Add filter to change URL.
		add_filter( 'consentpro_assets_url', function( $url ) {
			return 'https://cdn.example.com/consentpro/';
		} );

		// Act: Apply filter.
		$result = apply_filters( 'consentpro_assets_url', CONSENTPRO_PLUGIN_URL );

		// Assert.
		$this->assertSame( 'https://cdn.example.com/consentpro/', $result );
	}

	/**
	 * Test consentpro_assets_url filter receives default plugin URL.
	 */
	public function test_consentpro_assets_url_filter_receives_default_url(): void {
		$captured_url = null;

		add_filter( 'consentpro_assets_url', function( $url ) use ( &$captured_url ) {
			$captured_url = $url;
			return $url;
		} );

		// Act.
		apply_filters( 'consentpro_assets_url', CONSENTPRO_PLUGIN_URL );

		// Assert.
		$this->assertSame( CONSENTPRO_PLUGIN_URL, $captured_url );
	}

	/**
	 * Test filters work without any callbacks registered.
	 */
	public function test_filters_return_default_without_callbacks(): void {
		// No filters registered.

		// Act & Assert: Filters return original values.
		$config_result = apply_filters( 'consentpro_config', [ 'test' => 'value' ] );
		$this->assertSame( [ 'test' => 'value' ], $config_result );

		$show_result = apply_filters( 'consentpro_should_show', true );
		$this->assertTrue( $show_result );

		$url_result = apply_filters( 'consentpro_assets_url', CONSENTPRO_PLUGIN_URL );
		$this->assertSame( CONSENTPRO_PLUGIN_URL, $url_result );

		$categories_result = apply_filters( 'consentpro_categories', [ [ 'id' => 'test' ] ] );
		$this->assertSame( [ [ 'id' => 'test' ] ], $categories_result );
	}

	/**
	 * Test multiple filters can be chained.
	 */
	public function test_multiple_filters_chain_correctly(): void {
		// Arrange: Add two filters.
		add_filter( 'consentpro_config', function( $config ) {
			$config['first'] = true;
			return $config;
		} );

		add_filter( 'consentpro_config', function( $config ) {
			$config['second'] = true;
			return $config;
		} );

		// Act.
		$result = apply_filters( 'consentpro_config', [] );

		// Assert: Both filters applied.
		$this->assertTrue( $result['first'] );
		$this->assertTrue( $result['second'] );
	}
}
