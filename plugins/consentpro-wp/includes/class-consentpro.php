<?php
/**
 * Main ConsentPro plugin class.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 */
class ConsentPro {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	protected string $version;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->version = CONSENTPRO_VERSION;
		$this->load_dependencies();
	}

	/**
	 * Load required dependencies.
	 *
	 * @return void
	 */
	private function load_dependencies(): void {
		require_once CONSENTPRO_PLUGIN_DIR . 'admin/class-consentpro-admin.php';
		require_once CONSENTPRO_PLUGIN_DIR . 'admin/class-consentpro-settings.php';
		require_once CONSENTPRO_PLUGIN_DIR . 'admin/class-consentpro-dashboard-widget.php';
		require_once CONSENTPRO_PLUGIN_DIR . 'public/class-consentpro-public.php';
		require_once CONSENTPRO_PLUGIN_DIR . 'public/class-consentpro-banner.php';
		require_once CONSENTPRO_PLUGIN_DIR . 'includes/class-consentpro-license.php';
		require_once CONSENTPRO_PLUGIN_DIR . 'includes/class-consentpro-consent-log.php';
		require_once CONSENTPRO_PLUGIN_DIR . 'includes/class-consentpro-consent-ajax.php';
	}

	/**
	 * Run the plugin - register hooks.
	 *
	 * @return void
	 */
	public function run(): void {
		$this->maybe_upgrade();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Check for database upgrades and run migrations if needed.
	 *
	 * WordPress does not re-run activation hooks on plugin updates,
	 * so we must check for needed migrations on each load.
	 *
	 * @return void
	 */
	private function maybe_upgrade(): void {
		$current_db_version = get_option( 'consentpro_db_version', '0' );

		// Create consent log table if it doesn't exist (upgrade from pre-1.0.0).
		if ( version_compare( $current_db_version, '1.0.0', '<' ) ) {
			ConsentPro_Consent_Log::create_table();
		}
	}

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	private function define_admin_hooks(): void {
		$admin = new ConsentPro_Admin( $this->version );

		add_action( 'admin_menu', [ $admin, 'add_menu_page' ] );
		add_action( 'admin_init', [ $admin, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $admin, 'enqueue_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $admin, 'enqueue_scripts' ] );

		// Dashboard widget.
		$widget = new ConsentPro_Dashboard_Widget();
		add_action( 'wp_dashboard_setup', [ $widget, 'register' ] );

		// AJAX handlers.
		$ajax = new ConsentPro_Consent_Ajax();
		$ajax->register();

		// Schedule daily pruning of old log entries.
		add_action( 'consentpro_prune_consent_log', [ $this, 'prune_consent_log' ] );
		if ( ! wp_next_scheduled( 'consentpro_prune_consent_log' ) ) {
			wp_schedule_event( time(), 'daily', 'consentpro_prune_consent_log' );
		}
	}

	/**
	 * Prune old consent log entries.
	 *
	 * @return void
	 */
	public function prune_consent_log(): void {
		if ( ! ConsentPro_License::is_pro() ) {
			return;
		}

		$consent_log = new ConsentPro_Consent_Log();
		$consent_log->prune_old_entries( 90 );
	}

	/**
	 * Register public hooks.
	 *
	 * @return void
	 */
	private function define_public_hooks(): void {
		$public = new ConsentPro_Public( $this->version );

		add_action( 'wp_enqueue_scripts', [ $public, 'enqueue_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $public, 'enqueue_scripts' ] );
		add_action( 'wp_head', [ $public, 'output_css_variables' ], 99 );
		add_action( 'wp_footer', [ $public, 'render_banner' ] );
		add_filter( 'script_loader_tag', [ $public, 'add_defer_attribute' ], 10, 2 );
		add_filter( 'wp_resource_hints', [ $public, 'add_resource_hints' ], 10, 2 );

		// Consent logging on page visits.
		add_action( 'template_redirect', [ $public, 'maybe_log_consent' ] );
	}

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	public function get_version(): string {
		return $this->version;
	}
}
