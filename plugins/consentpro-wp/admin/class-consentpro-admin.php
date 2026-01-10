<?php
/**
 * Admin functionality.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin class.
 */
class ConsentPro_Admin {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private string $version;

	/**
	 * Constructor.
	 *
	 * @param string $version Plugin version.
	 */
	public function __construct( string $version ) {
		$this->version = $version;
	}

	/**
	 * Add admin menu page.
	 *
	 * @return void
	 */
	public function add_menu_page(): void {
		add_options_page(
			__( 'ConsentPro Settings', 'consentpro' ),
			__( 'ConsentPro', 'consentpro' ),
			'manage_options',
			'consentpro',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		$settings = new ConsentPro_Settings();
		$settings->register();
	}

	/**
	 * Enqueue admin styles.
	 *
	 * @param string $hook_suffix Current admin page.
	 * @return void
	 */
	public function enqueue_styles( string $hook_suffix ): void {
		if ( 'settings_page_consentpro' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_style(
			'consentpro-admin',
			CONSENTPRO_PLUGIN_URL . 'admin/assets/admin.css',
			[ 'wp-color-picker' ],
			$this->version
		);
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook_suffix Current admin page.
	 * @return void
	 */
	public function enqueue_scripts( string $hook_suffix ): void {
		if ( 'settings_page_consentpro' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_script(
			'consentpro-admin',
			CONSENTPRO_PLUGIN_URL . 'admin/assets/admin.js',
			[ 'jquery', 'wp-color-picker' ],
			$this->version,
			true
		);

		// Localize preview configuration.
		$appearance = get_option( 'consentpro_appearance', [] );
		$categories = get_option( 'consentpro_categories', [] );

		$preview_config = [
			'cssUrl'     => CONSENTPRO_PLUGIN_URL . 'assets/consentpro.min.css',
			'colors'     => [
				'primary'    => $appearance['color_primary'] ?? '#2563eb',
				'secondary'  => $appearance['color_secondary'] ?? '#64748b',
				'background' => $appearance['color_background'] ?? '#ffffff',
				'text'       => $appearance['color_text'] ?? '#1e293b',
			],
			'text'       => [
				'heading'            => $appearance['text_heading'] ?? '',
				'acceptAll'          => $appearance['text_accept'] ?? '',
				'rejectNonEssential' => $appearance['text_reject'] ?? '',
				'settings'           => $appearance['text_settings'] ?? '',
				'save'               => $appearance['text_save'] ?? '',
				'settingsTitle'      => __( 'Privacy Preferences', 'consentpro' ),
			],
			'categories' => $this->format_preview_categories( $categories ),
		];

		wp_localize_script( 'consentpro-admin', 'consentproPreviewConfig', $preview_config );
	}

	/**
	 * Format categories for preview.
	 *
	 * @param array $categories Saved categories.
	 * @return array Formatted categories for JavaScript.
	 */
	private function format_preview_categories( array $categories ): array {
		$defaults = [
			'essential'       => [
				'name'        => __( 'Essential', 'consentpro' ),
				'description' => __( 'Required for the website to function properly.', 'consentpro' ),
			],
			'analytics'       => [
				'name'        => __( 'Analytics', 'consentpro' ),
				'description' => __( 'Help us understand how visitors interact with our website.', 'consentpro' ),
			],
			'marketing'       => [
				'name'        => __( 'Marketing', 'consentpro' ),
				'description' => __( 'Used to display relevant advertisements.', 'consentpro' ),
			],
			'personalization' => [
				'name'        => __( 'Personalization', 'consentpro' ),
				'description' => __( 'Remember your preferences for enhanced features.', 'consentpro' ),
			],
		];

		$formatted = [];

		foreach ( [ 'essential', 'analytics', 'marketing', 'personalization' ] as $id ) {
			$cat         = $categories[ $id ] ?? [];
			$formatted[] = [
				'id'          => $id,
				'name'        => ! empty( $cat['name'] ) ? $cat['name'] : $defaults[ $id ]['name'],
				'description' => ! empty( $cat['description'] ) ? $cat['description'] : $defaults[ $id ]['description'],
				'required'    => 'essential' === $id,
			];
		}

		return $formatted;
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		include CONSENTPRO_PLUGIN_DIR . 'admin/views/settings-page.php';
	}
}
