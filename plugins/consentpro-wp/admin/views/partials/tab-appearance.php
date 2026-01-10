<?php
/**
 * Appearance settings tab.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$consentpro_options = get_option( 'consentpro_appearance', [] );

// Define defaults for data attributes.
$consentpro_defaults = [
	'color_primary'    => '#2563eb',
	'color_secondary'  => '#64748b',
	'color_background' => '#ffffff',
	'color_text'       => '#1e293b',
];
?>

<form method="post" action="options.php">
	<?php settings_fields( 'consentpro_appearance' ); ?>

	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">
				<label for="consentpro-color-primary"><?php esc_html_e( 'Primary Color', 'consentpro' ); ?></label>
			</th>
			<td>
				<input type="text"
						id="consentpro-color-primary"
						name="consentpro_appearance[color_primary]"
						value="<?php echo esc_attr( $consentpro_options['color_primary'] ?? $consentpro_defaults['color_primary'] ); ?>"
						data-default="<?php echo esc_attr( $consentpro_defaults['color_primary'] ); ?>"
						class="consentpro-color-field">
				<p class="description"><?php esc_html_e( 'Used for buttons and links.', 'consentpro' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="consentpro-color-secondary"><?php esc_html_e( 'Secondary Color', 'consentpro' ); ?></label>
			</th>
			<td>
				<input type="text"
						id="consentpro-color-secondary"
						name="consentpro_appearance[color_secondary]"
						value="<?php echo esc_attr( $consentpro_options['color_secondary'] ?? $consentpro_defaults['color_secondary'] ); ?>"
						data-default="<?php echo esc_attr( $consentpro_defaults['color_secondary'] ); ?>"
						class="consentpro-color-field">
				<p class="description"><?php esc_html_e( 'Used for secondary text and borders.', 'consentpro' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="consentpro-color-background"><?php esc_html_e( 'Background Color', 'consentpro' ); ?></label>
			</th>
			<td>
				<input type="text"
						id="consentpro-color-background"
						name="consentpro_appearance[color_background]"
						value="<?php echo esc_attr( $consentpro_options['color_background'] ?? $consentpro_defaults['color_background'] ); ?>"
						data-default="<?php echo esc_attr( $consentpro_defaults['color_background'] ); ?>"
						class="consentpro-color-field">
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="consentpro-color-text"><?php esc_html_e( 'Text Color', 'consentpro' ); ?></label>
			</th>
			<td>
				<input type="text"
						id="consentpro-color-text"
						name="consentpro_appearance[color_text]"
						value="<?php echo esc_attr( $consentpro_options['color_text'] ?? $consentpro_defaults['color_text'] ); ?>"
						data-default="<?php echo esc_attr( $consentpro_defaults['color_text'] ); ?>"
						class="consentpro-color-field">
			</td>
		</tr>
	</table>

	<?php submit_button(); ?>
</form>
