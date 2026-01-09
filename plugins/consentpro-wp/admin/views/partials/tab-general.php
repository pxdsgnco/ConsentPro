<?php
/**
 * General settings tab.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$consentpro_options = get_option( 'consentpro_general', [] );
?>

<form method="post" action="options.php">
	<?php settings_fields( 'consentpro_general' ); ?>

	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Enable Banner', 'consentpro' ); ?>
			</th>
			<td>
				<label>
					<input type="checkbox" name="consentpro_general[enabled]" value="1" <?php checked( ! empty( $consentpro_options['enabled'] ) ); ?>>
					<?php esc_html_e( 'Show consent banner to visitors', 'consentpro' ); ?>
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="consentpro-policy-url"><?php esc_html_e( 'Privacy Policy URL', 'consentpro' ); ?></label>
			</th>
			<td>
				<input type="url" id="consentpro-policy-url" name="consentpro_general[policy_url]" value="<?php echo esc_url( $consentpro_options['policy_url'] ?? '' ); ?>" class="regular-text">
				<p class="description"><?php esc_html_e( 'Link to your privacy policy page.', 'consentpro' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Geo-Targeting', 'consentpro' ); ?>
			</th>
			<td>
				<label>
					<input type="checkbox" name="consentpro_general[geo_enabled]" value="1" <?php checked( ! empty( $consentpro_options['geo_enabled'] ) ); ?>>
					<?php esc_html_e( 'Only show banner to EU and California visitors', 'consentpro' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'Requires Cloudflare for geo-detection.', 'consentpro' ); ?></p>
			</td>
		</tr>
	</table>

	<?php submit_button(); ?>
</form>
