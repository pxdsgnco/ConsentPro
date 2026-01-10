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

		/**
		 * Filter the base URL for ConsentPro assets.
		 *
		 * Useful for serving assets from a CDN or custom location.
		 *
		 * @since 1.0.0
		 * @param string $url The base URL for assets. Default is plugin URL.
		 */
		$assets_url = apply_filters( 'consentpro_assets_url', CONSENTPRO_PLUGIN_URL );

		wp_enqueue_style(
			'consentpro',
			$assets_url . 'assets/consentpro.min.css',
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

		/** This filter is documented in public/class-consentpro-public.php */
		$assets_url = apply_filters( 'consentpro_assets_url', CONSENTPRO_PLUGIN_URL );

		wp_enqueue_script(
			'consentpro',
			$assets_url . 'assets/consentpro.min.js',
			[],
			$this->version,
			true
		);

		// Add init script inline after the main script.
		wp_add_inline_script( 'consentpro', $this->get_init_script(), 'after' );
	}

	/**
	 * Add defer attribute to ConsentPro script tag.
	 *
	 * Uses script_loader_tag filter for WP 6.0+ compatibility.
	 *
	 * @param string $tag    Script HTML tag.
	 * @param string $handle Script handle.
	 * @return string Modified tag.
	 */
	public function add_defer_attribute( string $tag, string $handle ): string {
		if ( 'consentpro' !== $handle ) {
			return $tag;
		}

		// Don't add defer if already present.
		if ( strpos( $tag, 'defer' ) !== false ) {
			return $tag;
		}

		return str_replace( ' src', ' defer src', $tag );
	}

	/**
	 * Add resource hints for ConsentPro assets.
	 *
	 * Adds preload hint for CSS to improve loading performance.
	 *
	 * @param array  $urls          URLs to add hints for.
	 * @param string $relation_type Hint type (preload, prefetch, etc.).
	 * @return array Modified URLs.
	 */
	public function add_resource_hints( array $urls, string $relation_type ): array {
		if ( 'preload' !== $relation_type ) {
			return $urls;
		}

		if ( ! $this->should_show_banner() ) {
			return $urls;
		}

		/** This filter is documented in public/class-consentpro-public.php */
		$assets_url = apply_filters( 'consentpro_assets_url', CONSENTPRO_PLUGIN_URL );

		$urls[] = [
			'href' => $assets_url . 'assets/consentpro.min.css',
			'as'   => 'style',
		];

		return $urls;
	}

	/**
	 * Output CSS variables for banner colors.
	 *
	 * Outputs inline style tag in <head> with CSS custom properties.
	 *
	 * @return void
	 */
	public function output_css_variables(): void {
		if ( ! $this->should_show_banner() ) {
			return;
		}

		$appearance = get_option( 'consentpro_appearance', [] );

		$colors = [
			'--consentpro-color-primary'    => $appearance['color_primary'] ?? '#2563eb',
			'--consentpro-color-secondary'  => $appearance['color_secondary'] ?? '#64748b',
			'--consentpro-color-background' => $appearance['color_background'] ?? '#ffffff',
			'--consentpro-color-text'       => $appearance['color_text'] ?? '#1e293b',
		];

		$css = ':root{';
		foreach ( $colors as $var => $value ) {
			$css .= esc_attr( $var ) . ':' . esc_attr( $value ) . ';';
		}
		$css .= '}';

		printf( '<style id="consentpro-css-vars">%s</style>', $css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS is escaped above.

		// Output custom CSS if Pro license and CSS is set.
		$this->output_custom_css( $appearance );
	}

	/**
	 * Output custom CSS for banner styling.
	 *
	 * Only outputs if Pro license is active and custom CSS is set.
	 * Sanitizes CSS to remove potentially harmful content.
	 *
	 * @param array $appearance Appearance settings.
	 * @return void
	 */
	private function output_custom_css( array $appearance ): void {
		// Only output for Pro users.
		if ( ! ConsentPro_License::is_pro() ) {
			return;
		}

		$custom_css = $appearance['custom_css'] ?? '';

		if ( empty( $custom_css ) ) {
			return;
		}

		// Sanitize CSS: strip script tags and other potentially harmful content.
		$custom_css = $this->sanitize_css( $custom_css );

		if ( empty( $custom_css ) ) {
			return;
		}

		printf( '<style id="consentpro-custom-css">%s</style>', $custom_css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS sanitized above.
	}

	/**
	 * Sanitize CSS input.
	 *
	 * Removes potentially harmful content from CSS:
	 * - Script tags
	 * - JavaScript URLs
	 * - Expression() functions
	 * - Behavior URLs
	 *
	 * @param string $css Raw CSS input.
	 * @return string Sanitized CSS.
	 */
	private function sanitize_css( string $css ): string {
		// Remove any script tags.
		$css = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $css );

		// Remove JavaScript protocol URLs.
		$css = preg_replace( '/javascript\s*:/i', '', $css );

		// Remove expression() - IE CSS expressions.
		$css = preg_replace( '/expression\s*\(/i', '', $css );

		// Remove behavior URLs - IE behavior.
		$css = preg_replace( '/behavior\s*:/i', '', $css );

		// Remove -moz-binding - Mozilla XBL.
		$css = preg_replace( '/-moz-binding\s*:/i', '', $css );

		// Remove data: URLs (can contain JavaScript).
		$css = preg_replace( '/url\s*\(\s*[\'"]?\s*data:/i', 'url(', $css );

		// Trim whitespace.
		$css = trim( $css );

		return $css;
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
	 * Get inline initialization script.
	 *
	 * The core JS (IIFE) exports window.ConsentPro with:
	 * - ConsentManager
	 * - BannerUI
	 * - GeoDetector
	 * - ScriptBlocker
	 *
	 * @return string Initialization script.
	 */
	private function get_init_script(): string {
		return <<<'JS'
(function(){
'use strict';
function initConsentPro(){
if(typeof ConsentPro==='undefined'){return;}
var config=ConsentPro.GeoDetector.parseConfigFromDOM('#consentpro-banner');
if(!config){return;}
if(!ConsentPro.GeoDetector.shouldShowBanner(config)){return;}
var manager=new ConsentPro.ConsentManager();
var banner=new ConsentPro.BannerUI(manager);
var blocker=new ConsentPro.ScriptBlocker();
banner.init('consentpro-banner',config);
var consent=manager.getConsent();
if(consent&&manager.isConsentValid()){
blocker.init(consent.categories);
banner.renderFooterToggle();
}else{
banner.show();
blocker.init({essential:true,analytics:false,marketing:false,personalization:false});
}
window.ConsentPro.manager=manager;
window.ConsentPro.show=function(){banner.show();};
window.ConsentPro.getConsent=function(){return manager.getConsent();};
}
if(document.readyState==='loading'){
document.addEventListener('DOMContentLoaded',initConsentPro);
}else{
initConsentPro();
}
})();
JS;
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

		/**
		 * Filter whether the consent banner should be displayed.
		 *
		 * @since 1.0.0
		 * @param bool $should_show Whether to show the banner. Default true.
		 */
		return apply_filters( 'consentpro_should_show', true );
	}

	/**
	 * Check for consent cookie and log if present.
	 *
	 * Reads the consent cookie set by the frontend JS and logs
	 * the consent event to the database for metrics tracking.
	 *
	 * @return void
	 */
	public function maybe_log_consent(): void {
		// Only log if Pro license active.
		if ( ! ConsentPro_License::is_pro() ) {
			return;
		}

		// Check for consent cookie.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		if ( ! isset( $_COOKIE['consentpro'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$cookie_value = sanitize_text_field( wp_unslash( $_COOKIE['consentpro'] ) );
		$consent_data = json_decode( urldecode( $cookie_value ), true );

		if ( ! is_array( $consent_data ) || empty( $consent_data['categories'] ) ) {
			return;
		}

		$consent_log = new ConsentPro_Consent_Log();
		$consent_log->log_consent( $consent_data );
	}
}
