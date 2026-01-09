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
		require_once CONSENTPRO_PLUGIN_DIR . 'public/class-consentpro-public.php';
		require_once CONSENTPRO_PLUGIN_DIR . 'public/class-consentpro-banner.php';
		require_once CONSENTPRO_PLUGIN_DIR . 'includes/class-consentpro-license.php';
	}

	/**
	 * Run the plugin - register hooks.
	 *
	 * @return void
	 */
	public function run(): void {
		$this->define_admin_hooks();
		$this->define_public_hooks();
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
		add_action( 'wp_footer', [ $public, 'render_banner' ] );
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
