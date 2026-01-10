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

		$urls[] = [
			'href' => CONSENTPRO_PLUGIN_URL . 'assets/consentpro.min.css',
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

		$this->output_init_script();
	}

	/**
	 * Output inline initialization script.
	 *
	 * The core JS (IIFE) exports window.ConsentPro with:
	 * - ConsentManager
	 * - BannerUI
	 * - GeoDetector
	 * - ScriptBlocker
	 *
	 * @return void
	 */
	private function output_init_script(): void {
		$init_script = <<<'JS'
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

		printf( '<script id="consentpro-init">%s</script>', $init_script ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static script, no user input.
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
