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
		register_setting(
			'consentpro_general',
			'consentpro_general',
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize_general' ],
			]
		);

		// Appearance settings.
		register_setting(
			'consentpro_appearance',
			'consentpro_appearance',
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize_appearance' ],
			]
		);

		// Category settings.
		register_setting(
			'consentpro_categories',
			'consentpro_categories',
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize_categories' ],
			]
		);

		// License settings.
		register_setting(
			'consentpro_license',
			'consentpro_license_key',
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);
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
		// Sanitize color fields.
		$primary    = sanitize_hex_color( $input['color_primary'] ?? '' );
		$secondary  = sanitize_hex_color( $input['color_secondary'] ?? '' );
		$background = sanitize_hex_color( $input['color_background'] ?? '' );
		$text       = sanitize_hex_color( $input['color_text'] ?? '' );

		// Sanitize text fields with character limits.
		$text_heading  = mb_substr( sanitize_text_field( $input['text_heading'] ?? '' ), 0, 100 );
		$text_accept   = mb_substr( sanitize_text_field( $input['text_accept'] ?? '' ), 0, 30 );
		$text_reject   = mb_substr( sanitize_text_field( $input['text_reject'] ?? '' ), 0, 30 );
		$text_settings = mb_substr( sanitize_text_field( $input['text_settings'] ?? '' ), 0, 30 );
		$text_save     = mb_substr( sanitize_text_field( $input['text_save'] ?? '' ), 0, 30 );

		return [
			'color_primary'    => $primary ? $primary : '#2563eb',
			'color_secondary'  => $secondary ? $secondary : '#64748b',
			'color_background' => $background ? $background : '#ffffff',
			'color_text'       => $text ? $text : '#1e293b',
			'text_heading'     => $text_heading,
			'text_accept'      => $text_accept,
			'text_reject'      => $text_reject,
			'text_settings'    => $text_settings,
			'text_save'        => $text_save,
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

		// Allowed HTML for descriptions (links only per acceptance criteria).
		$allowed_html = [
			'a' => [
				'href'   => [],
				'title'  => [],
				'target' => [],
				'rel'    => [],
			],
		];

		// Only allow known category keys.
		$valid_keys = [ 'essential', 'analytics', 'marketing', 'personalization' ];

		foreach ( $input as $key => $category ) {
			$sanitized_key = sanitize_key( $key );

			// Skip unknown category keys.
			if ( ! in_array( $sanitized_key, $valid_keys, true ) ) {
				continue;
			}

			$sanitized[ $sanitized_key ] = [
				'name'        => sanitize_text_field( $category['name'] ?? '' ),
				'description' => wp_kses( $category['description'] ?? '', $allowed_html ),
				// Essential category cannot be toggled off.
				'default'     => 'essential' === $sanitized_key || ! empty( $category['default'] ),
			];
		}

		return $sanitized;
	}
}
