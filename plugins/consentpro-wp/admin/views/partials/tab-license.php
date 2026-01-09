<?php
/**
 * License settings tab.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$license_key = get_option( 'consentpro_license_key', '' );
$license     = get_option( 'consentpro_license', [] );
?>

<form method="post" action="options.php">
    <?php settings_fields( 'consentpro_license' ); ?>

    <table class="form-table" role="presentation">
        <tr>
            <th scope="row">
                <label for="consentpro-license-key"><?php esc_html_e( 'License Key', 'consentpro' ); ?></label>
            </th>
            <td>
                <input type="text" id="consentpro-license-key" name="consentpro_license_key" value="<?php echo esc_attr( $license_key ); ?>" class="regular-text">
                <p class="description"><?php esc_html_e( 'Enter your license key to unlock Pro features.', 'consentpro' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php esc_html_e( 'Status', 'consentpro' ); ?>
            </th>
            <td>
                <?php if ( ConsentPro_License::is_pro() ) : ?>
                    <span class="consentpro-license-status consentpro-license-status--active">
                        <?php esc_html_e( 'Active', 'consentpro' ); ?> - <?php echo esc_html( ucfirst( $license['tier'] ?? 'Pro' ) ); ?>
                    </span>
                    <?php if ( ! empty( $license['expires'] ) ) : ?>
                        <p class="description">
                            <?php
                            printf(
                                /* translators: %s: expiry date */
                                esc_html__( 'Expires: %s', 'consentpro' ),
                                esc_html( date_i18n( get_option( 'date_format' ), strtotime( $license['expires'] ) ) )
                            );
                            ?>
                        </p>
                    <?php endif; ?>
                <?php else : ?>
                    <span class="consentpro-license-status consentpro-license-status--inactive">
                        <?php esc_html_e( 'Core (Free)', 'consentpro' ); ?>
                    </span>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <?php submit_button( __( 'Save & Validate', 'consentpro' ) ); ?>
</form>
