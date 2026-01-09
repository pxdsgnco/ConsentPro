<?php
/**
 * Appearance settings tab.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$options = get_option( 'consentpro_appearance', [] );
?>

<form method="post" action="options.php">
    <?php settings_fields( 'consentpro_appearance' ); ?>

    <table class="form-table" role="presentation">
        <tr>
            <th scope="row">
                <label for="consentpro-color-primary"><?php esc_html_e( 'Primary Color', 'consentpro' ); ?></label>
            </th>
            <td>
                <input type="color" id="consentpro-color-primary" name="consentpro_appearance[color_primary]" value="<?php echo esc_attr( $options['color_primary'] ?? '#2563eb' ); ?>">
                <p class="description"><?php esc_html_e( 'Used for buttons and links.', 'consentpro' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="consentpro-color-secondary"><?php esc_html_e( 'Secondary Color', 'consentpro' ); ?></label>
            </th>
            <td>
                <input type="color" id="consentpro-color-secondary" name="consentpro_appearance[color_secondary]" value="<?php echo esc_attr( $options['color_secondary'] ?? '#64748b' ); ?>">
                <p class="description"><?php esc_html_e( 'Used for secondary text and borders.', 'consentpro' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="consentpro-color-background"><?php esc_html_e( 'Background Color', 'consentpro' ); ?></label>
            </th>
            <td>
                <input type="color" id="consentpro-color-background" name="consentpro_appearance[color_background]" value="<?php echo esc_attr( $options['color_background'] ?? '#ffffff' ); ?>">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="consentpro-color-text"><?php esc_html_e( 'Text Color', 'consentpro' ); ?></label>
            </th>
            <td>
                <input type="color" id="consentpro-color-text" name="consentpro_appearance[color_text]" value="<?php echo esc_attr( $options['color_text'] ?? '#1e293b' ); ?>">
            </td>
        </tr>
    </table>

    <?php submit_button(); ?>
</form>
