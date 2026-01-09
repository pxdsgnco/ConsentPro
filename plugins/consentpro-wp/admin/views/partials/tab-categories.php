<?php
/**
 * Categories settings tab.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$consentpro_categories = get_option( 'consentpro_categories', [] );
?>

<form method="post" action="options.php">
	<?php settings_fields( 'consentpro_categories' ); ?>

	<p><?php esc_html_e( 'Configure the consent categories shown in the settings panel. Essential category is always enabled.', 'consentpro' ); ?></p>

	<div class="consentpro-categories">
		<?php foreach ( [ 'analytics', 'marketing', 'personalization' ] as $consentpro_key ) : ?>
			<?php $consentpro_category = $consentpro_categories[ $consentpro_key ] ?? []; ?>
			<div class="consentpro-category-card">
				<h3><?php echo esc_html( ucfirst( $consentpro_key ) ); ?></h3>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="consentpro-<?php echo esc_attr( $consentpro_key ); ?>-name"><?php esc_html_e( 'Display Name', 'consentpro' ); ?></label>
						</th>
						<td>
							<input type="text" id="consentpro-<?php echo esc_attr( $consentpro_key ); ?>-name" name="consentpro_categories[<?php echo esc_attr( $consentpro_key ); ?>][name]" value="<?php echo esc_attr( $consentpro_category['name'] ?? ucfirst( $consentpro_key ) ); ?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="consentpro-<?php echo esc_attr( $consentpro_key ); ?>-description"><?php esc_html_e( 'Description', 'consentpro' ); ?></label>
						</th>
						<td>
							<textarea id="consentpro-<?php echo esc_attr( $consentpro_key ); ?>-description" name="consentpro_categories[<?php echo esc_attr( $consentpro_key ); ?>][description]" rows="2" class="large-text"><?php echo esc_textarea( $consentpro_category['description'] ?? '' ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Default State', 'consentpro' ); ?>
						</th>
						<td>
							<label>
								<input type="checkbox" name="consentpro_categories[<?php echo esc_attr( $consentpro_key ); ?>][default]" value="1" <?php checked( ! empty( $consentpro_category['default'] ) ); ?>>
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
