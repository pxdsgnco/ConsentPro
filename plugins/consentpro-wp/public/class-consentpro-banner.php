<?php
/**
 * Banner rendering.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Banner class.
 */
class ConsentPro_Banner {

	/**
	 * EU country codes.
	 *
	 * @var array
	 */
	private const EU_COUNTRIES = [
		'AT',
		'BE',
		'BG',
		'HR',
		'CY',
		'CZ',
		'DK',
		'EE',
		'FI',
		'FR',
		'DE',
		'GR',
		'HU',
		'IE',
		'IT',
		'LV',
		'LT',
		'LU',
		'MT',
		'NL',
		'PL',
		'PT',
		'RO',
		'SK',
		'SI',
		'ES',
		'SE',
	];

	/**
	 * Render the banner container with config.
	 *
	 * @return void
	 */
	public function render(): void {
		$config = $this->get_config();
		$config = apply_filters( 'consentpro_config', $config );

		printf(
			'<div id="consentpro-banner" class="consentpro" role="dialog" aria-labelledby="consentpro-heading" aria-modal="false" data-config="%s"></div>',
			esc_attr( wp_json_encode( $config ) )
		);
	}

	/**
	 * Build banner configuration.
	 *
	 * @return array
	 */
	private function get_config(): array {
		$general    = get_option( 'consentpro_general', [] );
		$appearance = get_option( 'consentpro_appearance', [] );
		$categories = get_option( 'consentpro_categories', [] );

		return [
			'geo'        => $this->detect_geo(),
			'geoEnabled' => ! empty( $general['geo_enabled'] ),
			'policyUrl'  => $general['policy_url'] ?? '',
			'categories' => $this->format_categories( $categories ),
			'text'       => $this->get_text(),
			'colors'     => [
				'primary'    => $appearance['color_primary'] ?? '#2563eb',
				'secondary'  => $appearance['color_secondary'] ?? '#64748b',
				'background' => $appearance['color_background'] ?? '#ffffff',
				'text'       => $appearance['color_text'] ?? '#1e293b',
			],
		];
	}

	/**
	 * Detect geo region from Cloudflare header.
	 *
	 * @return string|null
	 */
	private function detect_geo(): ?string {
		$country = isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) ) : null;

		if ( 'CA' === $country ) {
			return 'CA';
		}

		if ( in_array( $country, self::EU_COUNTRIES, true ) ) {
			return 'EU';
		}

		return null;
	}

	/**
	 * Format categories for config.
	 *
	 * @param array $categories Raw categories.
	 * @return array
	 */
	private function format_categories( array $categories ): array {
		// Default category descriptions.
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

		// Essential is always first and always required.
		$essential   = $categories['essential'] ?? [];
		$formatted[] = [
			'id'          => 'essential',
			'name'        => ! empty( $essential['name'] ) ? $essential['name'] : $defaults['essential']['name'],
			'description' => ! empty( $essential['description'] ) ? $essential['description'] : $defaults['essential']['description'],
			'required'    => true,
		];

		// Add other categories in consistent order.
		foreach ( [ 'analytics', 'marketing', 'personalization' ] as $id ) {
			$category    = $categories[ $id ] ?? [];
			$formatted[] = [
				'id'          => $id,
				'name'        => ! empty( $category['name'] ) ? $category['name'] : $defaults[ $id ]['name'],
				'description' => ! empty( $category['description'] ) ? $category['description'] : $defaults[ $id ]['description'],
				'required'    => false,
			];
		}

		return apply_filters( 'consentpro_categories', $formatted );
	}

	/**
	 * Get banner text strings.
	 *
	 * @return array
	 */
	private function get_text(): array {
		$appearance = get_option( 'consentpro_appearance', [] );

		return [
			'heading'            => ! empty( $appearance['text_heading'] )
				? $appearance['text_heading']
				: __( 'We value your privacy', 'consentpro' ),
			'description'        => __( 'We use cookies to enhance your browsing experience and analyze our traffic.', 'consentpro' ),
			'acceptAll'          => ! empty( $appearance['text_accept'] )
				? $appearance['text_accept']
				: __( 'Accept All', 'consentpro' ),
			'rejectNonEssential' => ! empty( $appearance['text_reject'] )
				? $appearance['text_reject']
				: __( 'Reject Non-Essential', 'consentpro' ),
			'settings'           => ! empty( $appearance['text_settings'] )
				? $appearance['text_settings']
				: __( 'Cookie Settings', 'consentpro' ),
			'save'               => ! empty( $appearance['text_save'] )
				? $appearance['text_save']
				: __( 'Save Preferences', 'consentpro' ),
			'back'               => __( 'Back', 'consentpro' ),
			'settingsTitle'      => __( 'Privacy Preferences', 'consentpro' ),
			'footerToggle'       => __( 'Privacy Settings', 'consentpro' ),
		];
	}
}
