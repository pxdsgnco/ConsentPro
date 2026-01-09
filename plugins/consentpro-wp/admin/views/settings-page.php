<?php
/**
 * Settings page template.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <nav class="nav-tab-wrapper">
        <a href="?page=consentpro&tab=general" class="nav-tab <?php echo 'general' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e( 'General', 'consentpro' ); ?>
        </a>
        <a href="?page=consentpro&tab=appearance" class="nav-tab <?php echo 'appearance' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e( 'Appearance', 'consentpro' ); ?>
        </a>
        <a href="?page=consentpro&tab=categories" class="nav-tab <?php echo 'categories' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e( 'Categories', 'consentpro' ); ?>
        </a>
        <a href="?page=consentpro&tab=consent-log" class="nav-tab <?php echo 'consent-log' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e( 'Consent Log', 'consentpro' ); ?>
        </a>
        <a href="?page=consentpro&tab=license" class="nav-tab <?php echo 'license' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <?php esc_html_e( 'License', 'consentpro' ); ?>
        </a>
    </nav>

    <div class="consentpro-settings-content">
        <?php
        switch ( $active_tab ) {
            case 'appearance':
                include CONSENTPRO_PLUGIN_DIR . 'admin/views/partials/tab-appearance.php';
                break;
            case 'categories':
                include CONSENTPRO_PLUGIN_DIR . 'admin/views/partials/tab-categories.php';
                break;
            case 'consent-log':
                include CONSENTPRO_PLUGIN_DIR . 'admin/views/partials/tab-consent-log.php';
                break;
            case 'license':
                include CONSENTPRO_PLUGIN_DIR . 'admin/views/partials/tab-license.php';
                break;
            default:
                include CONSENTPRO_PLUGIN_DIR . 'admin/views/partials/tab-general.php';
                break;
        }
        ?>
    </div>
</div>
