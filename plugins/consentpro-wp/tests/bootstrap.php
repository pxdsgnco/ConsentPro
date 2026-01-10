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

// Global test filters storage.
global $consentpro_test_filters;
$consentpro_test_filters = [];

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

if ( ! function_exists( 'apply_filters' ) ) {
	/**
	 * Mock apply_filters for testing.
	 *
	 * @param string $hook_name Filter hook name.
	 * @param mixed  $value     Value to filter.
	 * @param mixed  ...$args   Additional arguments.
	 * @return mixed
	 */
	function apply_filters( $hook_name, $value, ...$args ) {
		global $consentpro_test_filters;
		if ( isset( $consentpro_test_filters[ $hook_name ] ) ) {
			foreach ( $consentpro_test_filters[ $hook_name ] as $callback ) {
				$value = call_user_func_array( $callback, array_merge( [ $value ], $args ) );
			}
		}
		return $value;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	/**
	 * Mock add_filter for testing.
	 *
	 * @param string   $hook_name     Filter hook name.
	 * @param callable $callback      Callback function.
	 * @param int      $priority      Priority (unused in mock).
	 * @param int      $accepted_args Number of args (unused in mock).
	 * @return bool
	 */
	function add_filter( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
		global $consentpro_test_filters;
		if ( ! isset( $consentpro_test_filters[ $hook_name ] ) ) {
			$consentpro_test_filters[ $hook_name ] = [];
		}
		$consentpro_test_filters[ $hook_name ][] = $callback;
		return true;
	}
}

if ( ! function_exists( 'remove_all_filters' ) ) {
	/**
	 * Mock remove_all_filters for testing.
	 *
	 * @param string $hook_name Filter hook name.
	 * @return bool
	 */
	function remove_all_filters( $hook_name ) {
		global $consentpro_test_filters;
		unset( $consentpro_test_filters[ $hook_name ] );
		return true;
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	/**
	 * Mock wp_json_encode for testing.
	 *
	 * @param mixed $data Data to encode.
	 * @param int   $options JSON options.
	 * @param int   $depth Max depth.
	 * @return string|false
	 */
	function wp_json_encode( $data, $options = 0, $depth = 512 ) {
		return json_encode( $data, $options, $depth );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	/**
	 * Mock esc_attr for testing.
	 *
	 * @param string $text Text to escape.
	 * @return string
	 */
	function esc_attr( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	/**
	 * Mock wp_unslash for testing.
	 *
	 * @param string|array $value Value to unslash.
	 * @return string|array
	 */
	function wp_unslash( $value ) {
		return is_array( $value ) ? array_map( 'stripslashes', $value ) : stripslashes( $value );
	}
}

if ( ! function_exists( '__' ) ) {
	/**
	 * Mock __ for testing.
	 *
	 * @param string $text   Text to translate.
	 * @param string $domain Text domain.
	 * @return string
	 */
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'wp_kses' ) ) {
	/**
	 * Mock wp_kses for testing.
	 *
	 * @param string $content Content to filter.
	 * @param array  $allowed_html Allowed HTML elements.
	 * @param array  $allowed_protocols Allowed protocols.
	 * @return string
	 */
	function wp_kses( $content, $allowed_html, $allowed_protocols = [] ) {
		// Simple implementation: strip all tags except allowed ones.
		$allowed_tags = '';
		if ( is_array( $allowed_html ) ) {
			foreach ( array_keys( $allowed_html ) as $tag ) {
				$allowed_tags .= '<' . $tag . '>';
			}
		}
		return strip_tags( $content, $allowed_tags );
	}
}

// Define plugin URL constant for testing.
if ( ! defined( 'CONSENTPRO_PLUGIN_URL' ) ) {
	define( 'CONSENTPRO_PLUGIN_URL', 'https://example.com/wp-content/plugins/consentpro/' );
}

// Load plugin files for testing.
require_once dirname( __DIR__ ) . '/admin/class-consentpro-settings.php';
require_once dirname( __DIR__ ) . '/public/class-consentpro-banner.php';
