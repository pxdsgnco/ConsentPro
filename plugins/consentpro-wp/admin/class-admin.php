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

        wp_enqueue_style(
            'consentpro-admin',
            CONSENTPRO_PLUGIN_URL . 'admin/assets/admin.css',
            [],
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
            [],
            $this->version,
            true
        );
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
