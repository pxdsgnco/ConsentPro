<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package ConsentPro
 */

// Load Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Define WordPress stubs for unit testing.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}

// Global test options storage.
global $consentpro_test_options;
$consentpro_test_options = [];

// Stub WordPress functions used in tests.
if ( ! function_exists( 'get_option' ) ) {
	/**
	 * Mock get_option for testing.
	 *
	 * @param string $option  Option name.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	function get_option( $option, $default = false ) {
		global $consentpro_test_options;
		return $consentpro_test_options[ $option ] ?? $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	/**
	 * Mock update_option for testing.
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  Option value.
	 * @return bool
	 */
	function update_option( $option, $value ) {
		global $consentpro_test_options;
		$consentpro_test_options[ $option ] = $value;
		return true;
	}
}

if ( ! function_exists( 'add_option' ) ) {
	/**
	 * Mock add_option for testing.
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  Option value.
	 * @return bool
	 */
	function add_option( $option, $value ) {
		return update_option( $option, $value );
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	/**
	 * Mock delete_option for testing.
	 *
	 * @param string $option Option name.
	 * @return bool
	 */
	function delete_option( $option ) {
		global $consentpro_test_options;
		unset( $consentpro_test_options[ $option ] );
		return true;
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	/**
	 * Mock esc_url_raw for testing.
	 *
	 * @param string $url URL to escape.
	 * @return string
	 */
	function esc_url_raw( $url ) {
		$url = trim( $url );

		// Reject dangerous protocols.
		if ( preg_match( '/^(javascript|vbscript|data):/i', $url ) ) {
			return '';
		}

		return filter_var( $url, FILTER_SANITIZE_URL ) ?: '';
	}
}

if ( ! function_exists( 'sanitize_hex_color' ) ) {
	/**
	 * Mock sanitize_hex_color for testing.
	 *
	 * @param string $color Hex color.
	 * @return string|null
	 */
	function sanitize_hex_color( $color ) {
		if ( '' === $color ) {
			return '';
		}

		// 3 or 6 hex digits, or the empty string.
		if ( preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color ) ) {
			return $color;
		}

		return null;
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	/**
	 * Mock sanitize_text_field for testing.
	 *
	 * @param string $str String to sanitize.
	 * @return string
	 */
	function sanitize_text_field( $str ) {
		$str = strip_tags( $str );
		return trim( $str );
	}
}

if ( ! function_exists( 'sanitize_textarea_field' ) ) {
	/**
	 * Mock sanitize_textarea_field for testing.
	 *
	 * @param string $str String to sanitize.
	 * @return string
	 */
	function sanitize_textarea_field( $str ) {
		$str = strip_tags( $str );
		return trim( $str );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	/**
	 * Mock sanitize_key for testing.
	 *
	 * @param string $key Key to sanitize.
	 * @return string
	 */
	function sanitize_key( $key ) {
		return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) );
	}
}

if ( ! function_exists( 'register_setting' ) ) {
	/**
	 * Mock register_setting for testing.
	 *
	 * @param string $option_group Settings group.
	 * @param string $option_name  Option name.
	 * @param array  $args         Arguments.
	 * @return void
	 */
	function register_setting( $option_group, $option_name, $args = [] ) {
		// No-op for testing.
	}
}

// Load plugin files for testing.
require_once dirname( __DIR__ ) . '/admin/class-consentpro-settings.php';
