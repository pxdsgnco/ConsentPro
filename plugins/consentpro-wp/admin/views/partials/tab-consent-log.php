<?php
/**
 * Consent log tab.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="consentpro-consent-log">
    <p><?php esc_html_e( 'Consent logging is a Pro feature. Upgrade to track consent records for compliance auditing.', 'consentpro' ); ?></p>

    <?php if ( ConsentPro_License::is_pro() ) : ?>
        <p><?php esc_html_e( 'Consent log will be implemented in a future update.', 'consentpro' ); ?></p>
    <?php else : ?>
        <a href="https://consentpro.io/pricing" class="button button-primary" target="_blank" rel="noopener">
            <?php esc_html_e( 'Upgrade to Pro', 'consentpro' ); ?>
        </a>
    <?php endif; ?>
</div>
