<?php
/**
 * Categories settings tab.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$categories = get_option( 'consentpro_categories', [] );
?>

<form method="post" action="options.php">
    <?php settings_fields( 'consentpro_categories' ); ?>

    <p><?php esc_html_e( 'Configure the consent categories shown in the settings panel. Essential category is always enabled.', 'consentpro' ); ?></p>

    <div class="consentpro-categories">
        <?php foreach ( [ 'analytics', 'marketing', 'personalization' ] as $key ) : ?>
            <?php $category = $categories[ $key ] ?? []; ?>
            <div class="consentpro-category-card">
                <h3><?php echo esc_html( ucfirst( $key ) ); ?></h3>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="consentpro-<?php echo esc_attr( $key ); ?>-name"><?php esc_html_e( 'Display Name', 'consentpro' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="consentpro-<?php echo esc_attr( $key ); ?>-name" name="consentpro_categories[<?php echo esc_attr( $key ); ?>][name]" value="<?php echo esc_attr( $category['name'] ?? ucfirst( $key ) ); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="consentpro-<?php echo esc_attr( $key ); ?>-description"><?php esc_html_e( 'Description', 'consentpro' ); ?></label>
                        </th>
                        <td>
                            <textarea id="consentpro-<?php echo esc_attr( $key ); ?>-description" name="consentpro_categories[<?php echo esc_attr( $key ); ?>][description]" rows="2" class="large-text"><?php echo esc_textarea( $category['description'] ?? '' ); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?php esc_html_e( 'Default State', 'consentpro' ); ?>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="consentpro_categories[<?php echo esc_attr( $key ); ?>][default]" value="1" <?php checked( ! empty( $category['default'] ) ); ?>>
                                <?php esc_html_e( 'Enabled by default', 'consentpro' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
        <?php endforeach; ?>
    </div>

    <?php submit_button(); ?>
</form>
