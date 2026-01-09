<?php
/**
 * Plugin activator.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Activation handler.
 */
class ConsentPro_Activator {

    /**
     * Activate the plugin.
     *
     * Set default options on first activation.
     *
     * @return void
     */
    public static function activate(): void {
        // Set default options if not already set.
        if ( false === get_option( 'consentpro_general' ) ) {
            $defaults = [
                'enabled'     => true,
                'policy_url'  => '',
                'geo_enabled' => true,
            ];
            add_option( 'consentpro_general', $defaults );
        }

        if ( false === get_option( 'consentpro_appearance' ) ) {
            $defaults = [
                'color_primary'    => '#2563eb',
                'color_secondary'  => '#64748b',
                'color_background' => '#ffffff',
                'color_text'       => '#1e293b',
            ];
            add_option( 'consentpro_appearance', $defaults );
        }

        if ( false === get_option( 'consentpro_categories' ) ) {
            $defaults = [
                'analytics'       => [
                    'name'        => __( 'Analytics', 'consentpro' ),
                    'description' => __( 'Help us understand how visitors use our site.', 'consentpro' ),
                    'default'     => false,
                ],
                'marketing'       => [
                    'name'        => __( 'Marketing', 'consentpro' ),
                    'description' => __( 'Show relevant ads and track marketing campaigns.', 'consentpro' ),
                    'default'     => false,
                ],
                'personalization' => [
                    'name'        => __( 'Personalization', 'consentpro' ),
                    'description' => __( 'Remember your preferences for a better experience.', 'consentpro' ),
                    'default'     => false,
                ],
            ];
            add_option( 'consentpro_categories', $defaults );
        }

        // Flush rewrite rules.
        flush_rewrite_rules();
    }
}
