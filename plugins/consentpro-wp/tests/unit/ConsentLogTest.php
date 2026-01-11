<?php
/**
 * Consent log tests.
 *
 * @package ConsentPro
 */

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Mock wpdb class for testing.
 */
class MockWpdb {
	/**
	 * Table prefix.
	 *
	 * @var string
	 */
	public string $prefix = 'wp_';

	/**
	 * Mock get_var result.
	 *
	 * @var mixed
	 */
	public $mock_get_var_result = null;

	/**
	 * Mock get_results result.
	 *
	 * @var mixed
	 */
	public $mock_get_results_result = [];

	/**
	 * Mock query result.
	 *
	 * @var mixed
	 */
	public $mock_query_result = true;

	/**
	 * Mock insert result.
	 *
	 * @var mixed
	 */
	public $mock_insert_result = true;

	/**
	 * Insert calls tracker.
	 *
	 * @var array
	 */
	public array $insert_calls = [];

	/**
	 * Get var mock.
	 *
	 * @param string $query Query.
	 * @return mixed
	 */
	public function get_var( $query = null ) {
		return $this->mock_get_var_result;
	}

	/**
	 * Get results mock.
	 *
	 * @param string $query Query.
	 * @param string $output Output type.
	 * @return mixed
	 */
	public function get_results( $query = null, $output = OBJECT ) {
		return $this->mock_get_results_result;
	}

	/**
	 * Query mock.
	 *
	 * @param string $query Query.
	 * @return mixed
	 */
	public function query( $query ) {
		return $this->mock_query_result;
	}

	/**
	 * Insert mock.
	 *
	 * @param string $table Table name.
	 * @param array  $data Data.
	 * @param array  $format Format.
	 * @return mixed
	 */
	public function insert( $table, $data, $format = null ) {
		$this->insert_calls[] = [ 'table' => $table, 'data' => $data ];
		return $this->mock_insert_result;
	}

	/**
	 * Prepare mock.
	 *
	 * @param string $query Query.
	 * @param mixed  ...$args Arguments.
	 * @return string
	 */
	public function prepare( $query, ...$args ) {
		return $query;
	}

	/**
	 * Get charset collate mock.
	 *
	 * @return string
	 */
	public function get_charset_collate() {
		return 'DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
	}
}

// Define OBJECT constant if not defined.
if ( ! defined( 'OBJECT' ) ) {
	define( 'OBJECT', 'OBJECT' );
}

// Define ARRAY_A constant if not defined.
if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

/**
 * Test ConsentPro_Consent_Log class.
 */
class ConsentLogTest extends TestCase {

	/**
	 * Mock $wpdb instance.
	 *
	 * @var MockWpdb
	 */
	private MockWpdb $mock_wpdb;

	/**
	 * Set up test fixtures.
	 */
	protected function set_up(): void {
		parent::set_up();
		global $wpdb, $consentpro_test_options;

		$consentpro_test_options = [];

		// Create mock $wpdb.
		$this->mock_wpdb = new MockWpdb();
		$wpdb            = $this->mock_wpdb;

		// Clear server variables.
		unset( $_SERVER['REMOTE_ADDR'] );
		unset( $_SERVER['HTTP_USER_AGENT'] );
	}

	/**
	 * Tear down test fixtures.
	 */
	protected function tear_down(): void {
		global $consentpro_test_options;
		$consentpro_test_options = [];
		unset( $_SERVER['REMOTE_ADDR'] );
		unset( $_SERVER['HTTP_USER_AGENT'] );
		parent::tear_down();
	}

	/**
	 * Test determine_consent_type returns accept_all when all non-essential accepted.
	 */
	public function test_determine_consent_type_accept_all(): void {
		$log    = new ConsentPro_Consent_Log();
		$method = new ReflectionMethod( $log, 'determine_consent_type' );
		$method->setAccessible( true );

		$categories = [
			'essential'       => true,
			'analytics'       => true,
			'marketing'       => true,
			'personalization' => true,
		];

		$result = $method->invoke( $log, $categories );

		$this->assertSame( 'accept_all', $result );
	}

	/**
	 * Test determine_consent_type returns reject_non_essential when all rejected.
	 */
	public function test_determine_consent_type_reject_non_essential(): void {
		$log    = new ConsentPro_Consent_Log();
		$method = new ReflectionMethod( $log, 'determine_consent_type' );
		$method->setAccessible( true );

		$categories = [
			'essential'       => true,
			'analytics'       => false,
			'marketing'       => false,
			'personalization' => false,
		];

		$result = $method->invoke( $log, $categories );

		$this->assertSame( 'reject_non_essential', $result );
	}

	/**
	 * Test determine_consent_type returns custom for mixed selection.
	 */
	public function test_determine_consent_type_custom(): void {
		$log    = new ConsentPro_Consent_Log();
		$method = new ReflectionMethod( $log, 'determine_consent_type' );
		$method->setAccessible( true );

		$categories = [
			'essential'       => true,
			'analytics'       => true,
			'marketing'       => false,
			'personalization' => true,
		];

		$result = $method->invoke( $log, $categories );

		$this->assertSame( 'custom', $result );
	}

	/**
	 * Test determine_consent_type handles missing categories.
	 */
	public function test_determine_consent_type_missing_categories(): void {
		$log    = new ConsentPro_Consent_Log();
		$method = new ReflectionMethod( $log, 'determine_consent_type' );
		$method->setAccessible( true );

		// Only analytics set.
		$categories = [
			'essential' => true,
			'analytics' => true,
		];

		$result = $method->invoke( $log, $categories );

		// Missing = false, so it's custom (analytics true, others false).
		$this->assertSame( 'custom', $result );
	}

	/**
	 * Test determine_consent_type handles empty categories.
	 */
	public function test_determine_consent_type_empty_categories(): void {
		$log    = new ConsentPro_Consent_Log();
		$method = new ReflectionMethod( $log, 'determine_consent_type' );
		$method->setAccessible( true );

		$result = $method->invoke( $log, [] );

		// All non-essential empty = false, so reject.
		$this->assertSame( 'reject_non_essential', $result );
	}

	/**
	 * Test generate_visitor_hash creates consistent hash.
	 */
	public function test_generate_visitor_hash_consistency(): void {
		$_SERVER['REMOTE_ADDR']     = '192.168.1.100';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 Test Browser';

		$log    = new ConsentPro_Consent_Log();
		$method = new ReflectionMethod( $log, 'generate_visitor_hash' );
		$method->setAccessible( true );

		$hash1 = $method->invoke( $log );
		$hash2 = $method->invoke( $log );

		// Same inputs should produce same hash.
		$this->assertSame( $hash1, $hash2 );

		// Should be a valid SHA-256 hash (64 characters).
		$this->assertSame( 64, strlen( $hash1 ) );
		$this->assertMatchesRegularExpression( '/^[a-f0-9]{64}$/', $hash1 );
	}

	/**
	 * Test generate_visitor_hash changes with different IP.
	 */
	public function test_generate_visitor_hash_changes_with_ip(): void {
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 Test Browser';

		$log    = new ConsentPro_Consent_Log();
		$method = new ReflectionMethod( $log, 'generate_visitor_hash' );
		$method->setAccessible( true );

		$_SERVER['REMOTE_ADDR'] = '192.168.1.100';
		$hash1                  = $method->invoke( $log );

		$_SERVER['REMOTE_ADDR'] = '192.168.1.101';
		$hash2                  = $method->invoke( $log );

		$this->assertNotSame( $hash1, $hash2 );
	}

	/**
	 * Test generate_visitor_hash changes with different user agent.
	 */
	public function test_generate_visitor_hash_changes_with_user_agent(): void {
		$_SERVER['REMOTE_ADDR'] = '192.168.1.100';

		$log    = new ConsentPro_Consent_Log();
		$method = new ReflectionMethod( $log, 'generate_visitor_hash' );
		$method->setAccessible( true );

		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 Browser A';
		$hash1                      = $method->invoke( $log );

		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 Browser B';
		$hash2                      = $method->invoke( $log );

		$this->assertNotSame( $hash1, $hash2 );
	}

	/**
	 * Test generate_visitor_hash handles missing server variables.
	 */
	public function test_generate_visitor_hash_handles_missing_variables(): void {
		// No REMOTE_ADDR or HTTP_USER_AGENT set.
		$log    = new ConsentPro_Consent_Log();
		$method = new ReflectionMethod( $log, 'generate_visitor_hash' );
		$method->setAccessible( true );

		// Should not throw.
		$hash = $method->invoke( $log );

		$this->assertSame( 64, strlen( $hash ) );
	}

	/**
	 * Test log_consent returns false when categories missing.
	 */
	public function test_log_consent_returns_false_without_categories(): void {
		$log    = new ConsentPro_Consent_Log();
		$result = $log->log_consent( [] );

		$this->assertFalse( $result );
	}

	/**
	 * Test log_consent returns false for empty categories.
	 */
	public function test_log_consent_returns_false_for_empty_categories(): void {
		$log    = new ConsentPro_Consent_Log();
		$result = $log->log_consent( [ 'categories' => [] ] );

		$this->assertFalse( $result );
	}

	/**
	 * Test constructor sets correct table name.
	 */
	public function test_constructor_sets_table_name(): void {
		global $wpdb;
		$wpdb->prefix = 'custom_';

		$log  = new ConsentPro_Consent_Log();
		$prop = new ReflectionProperty( $log, 'table_name' );
		$prop->setAccessible( true );

		$this->assertSame( 'custom_consentpro_log', $prop->getValue( $log ) );
	}

	/**
	 * Test get_log_entries pagination calculation.
	 */
	public function test_get_log_entries_pagination(): void {
		global $wpdb;

		$wpdb->mock_get_var_result     = 150;
		$wpdb->mock_get_results_result = [];

		$log    = new ConsentPro_Consent_Log();
		$result = $log->get_log_entries( 3, 50 );

		$this->assertSame( 150, $result['total'] );
		$this->assertSame( 3, $result['page'] );
		$this->assertSame( 50, $result['per_page'] );
		$this->assertSame( 3, $result['total_pages'] );
	}

	/**
	 * Test get_log_entries handles zero entries.
	 */
	public function test_get_log_entries_handles_zero(): void {
		global $wpdb;

		$wpdb->mock_get_var_result     = 0;
		$wpdb->mock_get_results_result = null;

		$log    = new ConsentPro_Consent_Log();
		$result = $log->get_log_entries();

		$this->assertSame( 0, $result['total'] );
		$this->assertSame( [], $result['entries'] );
		$this->assertSame( 0, $result['total_pages'] );
	}

	/**
	 * Test get_metrics calculates percentages correctly.
	 */
	public function test_get_metrics_calculates_percentages(): void {
		global $wpdb;

		$wpdb->mock_get_results_result = [
			[ 'consent_type' => 'accept_all', 'count' => 60 ],
			[ 'consent_type' => 'reject_non_essential', 'count' => 30 ],
			[ 'consent_type' => 'custom', 'count' => 10 ],
		];

		$log     = new ConsentPro_Consent_Log();
		$metrics = $log->get_metrics( 30 );

		$this->assertSame( 100, $metrics['total'] );
		$this->assertSame( 60, $metrics['accept_all'] );
		$this->assertSame( 30, $metrics['reject_non_essential'] );
		$this->assertSame( 10, $metrics['custom'] );
		$this->assertSame( 60.0, $metrics['accept_percent'] );
		$this->assertSame( 30.0, $metrics['reject_percent'] );
		$this->assertSame( 10.0, $metrics['custom_percent'] );
	}

	/**
	 * Test get_metrics handles zero total.
	 */
	public function test_get_metrics_handles_zero_total(): void {
		global $wpdb;

		$wpdb->mock_get_results_result = [];

		$log     = new ConsentPro_Consent_Log();
		$metrics = $log->get_metrics();

		$this->assertSame( 0, $metrics['total'] );
		$this->assertSame( 0, $metrics['accept_percent'] );
		$this->assertSame( 0, $metrics['reject_percent'] );
		$this->assertSame( 0, $metrics['custom_percent'] );
	}

	/**
	 * Test clear_log returns count of deleted rows.
	 */
	public function test_clear_log_returns_count(): void {
		global $wpdb;

		$wpdb->mock_get_var_result = 42;

		$log   = new ConsentPro_Consent_Log();
		$count = $log->clear_log();

		$this->assertSame( 42, $count );
	}

	/**
	 * Test prune_old_entries method exists and works.
	 */
	public function test_prune_old_entries_works(): void {
		global $wpdb;

		$wpdb->mock_query_result = 5;

		$log   = new ConsentPro_Consent_Log();
		$count = $log->prune_old_entries( 90 );

		$this->assertSame( 5, $count );
	}
}
