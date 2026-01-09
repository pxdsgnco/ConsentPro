<?php
/**
 * Settings registration.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Settings class.
 */
class ConsentPro_Settings {

    /**
     * Register all settings.
     *
     * @return void
     */
    public function register(): void {
        // General settings.
        register_setting( 'consentpro_general', 'consentpro_general', [
            'type'              => 'array',
            'sanitize_callback' => [ $this, 'sanitize_general' ],
        ] );

        // Appearance settings.
        register_setting( 'consentpro_appearance', 'consentpro_appearance', [
            'type'              => 'array',
            'sanitize_callback' => [ $this, 'sanitize_appearance' ],
        ] );

        // Category settings.
        register_setting( 'consentpro_categories', 'consentpro_categories', [
            'type'              => 'array',
            'sanitize_callback' => [ $this, 'sanitize_categories' ],
        ] );

        // License settings.
        register_setting( 'consentpro_license', 'consentpro_license_key', [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ] );
    }

    /**
     * Sanitize general settings.
     *
     * @param array $input Raw input.
     * @return array Sanitized input.
     */
    public function sanitize_general( array $input ): array {
        return [
            'enabled'     => ! empty( $input['enabled'] ),
            'policy_url'  => esc_url_raw( $input['policy_url'] ?? '' ),
            'geo_enabled' => ! empty( $input['geo_enabled'] ),
        ];
    }

    /**
     * Sanitize appearance settings.
     *
     * @param array $input Raw input.
     * @return array Sanitized input.
     */
    public function sanitize_appearance( array $input ): array {
        return [
            'color_primary'    => sanitize_hex_color( $input['color_primary'] ?? '#2563eb' ),
            'color_secondary'  => sanitize_hex_color( $input['color_secondary'] ?? '#64748b' ),
            'color_background' => sanitize_hex_color( $input['color_background'] ?? '#ffffff' ),
            'color_text'       => sanitize_hex_color( $input['color_text'] ?? '#1e293b' ),
        ];
    }

    /**
     * Sanitize category settings.
     *
     * @param array $input Raw input.
     * @return array Sanitized input.
     */
    public function sanitize_categories( array $input ): array {
        $sanitized = [];

        foreach ( $input as $key => $category ) {
            $sanitized[ sanitize_key( $key ) ] = [
                'name'        => sanitize_text_field( $category['name'] ?? '' ),
                'description' => sanitize_textarea_field( $category['description'] ?? '' ),
                'default'     => ! empty( $category['default'] ),
            ];
        }

        return $sanitized;
    }
}
