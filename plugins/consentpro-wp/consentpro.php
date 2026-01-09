<?php
/**
 * Plugin Name:       ConsentPro
 * Plugin URI:        https://consentpro.io
 * Description:       Premium consent banner combining privacy policy, cookie consent, and category-based script blocking.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            ConsentPro Team
 * Author URI:        https://consentpro.io
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       consentpro
 * Domain Path:       /languages
 *
 * @package ConsentPro
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants.
define( 'CONSENTPRO_VERSION', '1.0.0' );
define( 'CONSENTPRO_PLUGIN_FILE', __FILE__ );
define( 'CONSENTPRO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CONSENTPRO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CONSENTPRO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Activation hook.
 *
 * @return void
 */
function consentpro_activate(): void {
    require_once CONSENTPRO_PLUGIN_DIR . 'includes/class-activator.php';
    ConsentPro_Activator::activate();
}
register_activation_hook( __FILE__, 'consentpro_activate' );

/**
 * Deactivation hook.
 *
 * @return void
 */
function consentpro_deactivate(): void {
    require_once CONSENTPRO_PLUGIN_DIR . 'includes/class-deactivator.php';
    ConsentPro_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'consentpro_deactivate' );

/**
 * Initialize plugin.
 *
 * @return void
 */
function consentpro_init(): void {
    require_once CONSENTPRO_PLUGIN_DIR . 'includes/class-consentpro.php';
    $plugin = new ConsentPro();
    $plugin->run();
}
add_action( 'plugins_loaded', 'consentpro_init' );
