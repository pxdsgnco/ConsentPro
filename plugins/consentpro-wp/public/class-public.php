<?php
/**
 * Public-facing functionality.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Public class.
 */
class ConsentPro_Public {

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
     * Enqueue public styles.
     *
     * @return void
     */
    public function enqueue_styles(): void {
        if ( ! $this->should_show_banner() ) {
            return;
        }

        wp_enqueue_style(
            'consentpro',
            CONSENTPRO_PLUGIN_URL . 'assets/consentpro.min.css',
            [],
            $this->version
        );
    }

    /**
     * Enqueue public scripts.
     *
     * @return void
     */
    public function enqueue_scripts(): void {
        if ( ! $this->should_show_banner() ) {
            return;
        }

        wp_enqueue_script(
            'consentpro',
            CONSENTPRO_PLUGIN_URL . 'assets/consentpro.min.js',
            [],
            $this->version,
            true
        );
    }

    /**
     * Render banner container.
     *
     * @return void
     */
    public function render_banner(): void {
        if ( ! $this->should_show_banner() ) {
            return;
        }

        $banner = new ConsentPro_Banner();
        $banner->render();
    }

    /**
     * Check if banner should be displayed.
     *
     * @return bool
     */
    private function should_show_banner(): bool {
        $options = get_option( 'consentpro_general', [] );

        // Check if enabled.
        if ( empty( $options['enabled'] ) ) {
            return false;
        }

        // Allow filtering.
        return apply_filters( 'consentpro_should_show', true );
    }
}
