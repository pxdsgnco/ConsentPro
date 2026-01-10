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

// Define defaults for color fields.
$consentpro_color_defaults = [
	'color_primary'    => '#2563eb',
	'color_secondary'  => '#64748b',
	'color_background' => '#ffffff',
	'color_text'       => '#1e293b',
];

// Define defaults for text fields.
$consentpro_text_defaults = [
	'text_heading'  => __( 'We value your privacy', 'consentpro' ),
	'text_accept'   => __( 'Accept All', 'consentpro' ),
	'text_reject'   => __( 'Reject Non-Essential', 'consentpro' ),
	'text_settings' => __( 'Cookie Settings', 'consentpro' ),
	'text_save'     => __( 'Save Preferences', 'consentpro' ),
];
?>

<form method="post" action="options.php">
	<?php settings_fields( 'consentpro_appearance' ); ?>

	<h2><?php esc_html_e( 'Colors', 'consentpro' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">
				<label for="consentpro-color-primary"><?php esc_html_e( 'Primary Color', 'consentpro' ); ?></label>
			</th>
			<td>
				<input type="text"
						id="consentpro-color-primary"
						name="consentpro_appearance[color_primary]"
						value="<?php echo esc_attr( $consentpro_options['color_primary'] ?? $consentpro_color_defaults['color_primary'] ); ?>"
						data-default="<?php echo esc_attr( $consentpro_color_defaults['color_primary'] ); ?>"
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
						value="<?php echo esc_attr( $consentpro_options['color_secondary'] ?? $consentpro_color_defaults['color_secondary'] ); ?>"
						data-default="<?php echo esc_attr( $consentpro_color_defaults['color_secondary'] ); ?>"
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
						value="<?php echo esc_attr( $consentpro_options['color_background'] ?? $consentpro_color_defaults['color_background'] ); ?>"
						data-default="<?php echo esc_attr( $consentpro_color_defaults['color_background'] ); ?>"
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
						value="<?php echo esc_attr( $consentpro_options['color_text'] ?? $consentpro_color_defaults['color_text'] ); ?>"
						data-default="<?php echo esc_attr( $consentpro_color_defaults['color_text'] ); ?>"
						class="consentpro-color-field">
			</td>
		</tr>
	</table>

	<h2 class="consentpro-section-title"><?php esc_html_e( 'Banner Text', 'consentpro' ); ?></h2>
	<p class="description"><?php esc_html_e( 'Customize the text shown on the consent banner.', 'consentpro' ); ?></p>

	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">
				<label for="consentpro-text-heading"><?php esc_html_e( 'Banner Heading', 'consentpro' ); ?></label>
			</th>
			<td>
				<input type="text"
						id="consentpro-text-heading"
						name="consentpro_appearance[text_heading]"
						value="<?php echo esc_attr( $consentpro_options['text_heading'] ?? '' ); ?>"
						class="regular-text"
						maxlength="100"
						placeholder="<?php echo esc_attr( $consentpro_text_defaults['text_heading'] ); ?>"
						aria-describedby="consentpro-text-heading-desc">
				<p class="description" id="consentpro-text-heading-desc">
					<?php esc_html_e( 'Main heading shown on Layer 1 banner. Maximum 100 characters.', 'consentpro' ); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="consentpro-text-accept"><?php esc_html_e( 'Accept Button', 'consentpro' ); ?></label>
			</th>
			<td>
				<input type="text"
						id="consentpro-text-accept"
						name="consentpro_appearance[text_accept]"
						value="<?php echo esc_attr( $consentpro_options['text_accept'] ?? '' ); ?>"
						class="regular-text"
						maxlength="30"
						placeholder="<?php echo esc_attr( $consentpro_text_defaults['text_accept'] ); ?>"
						aria-describedby="consentpro-text-accept-desc">
				<p class="description" id="consentpro-text-accept-desc">
					<?php esc_html_e( 'Text for the "Accept All" button. Maximum 30 characters.', 'consentpro' ); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="consentpro-text-reject"><?php esc_html_e( 'Reject Button', 'consentpro' ); ?></label>
			</th>
			<td>
				<input type="text"
						id="consentpro-text-reject"
						name="consentpro_appearance[text_reject]"
						value="<?php echo esc_attr( $consentpro_options['text_reject'] ?? '' ); ?>"
						class="regular-text"
						maxlength="30"
						placeholder="<?php echo esc_attr( $consentpro_text_defaults['text_reject'] ); ?>"
						aria-describedby="consentpro-text-reject-desc">
				<p class="description" id="consentpro-text-reject-desc">
					<?php esc_html_e( 'Text for the "Reject Non-Essential" button. Maximum 30 characters.', 'consentpro' ); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="consentpro-text-settings"><?php esc_html_e( 'Settings Link', 'consentpro' ); ?></label>
			</th>
			<td>
				<input type="text"
						id="consentpro-text-settings"
						name="consentpro_appearance[text_settings]"
						value="<?php echo esc_attr( $consentpro_options['text_settings'] ?? '' ); ?>"
						class="regular-text"
						maxlength="30"
						placeholder="<?php echo esc_attr( $consentpro_text_defaults['text_settings'] ); ?>"
						aria-describedby="consentpro-text-settings-desc">
				<p class="description" id="consentpro-text-settings-desc">
					<?php esc_html_e( 'Text for the settings/preferences link. Maximum 30 characters.', 'consentpro' ); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="consentpro-text-save"><?php esc_html_e( 'Save Button', 'consentpro' ); ?></label>
			</th>
			<td>
				<input type="text"
						id="consentpro-text-save"
						name="consentpro_appearance[text_save]"
						value="<?php echo esc_attr( $consentpro_options['text_save'] ?? '' ); ?>"
						class="regular-text"
						maxlength="30"
						placeholder="<?php echo esc_attr( $consentpro_text_defaults['text_save'] ); ?>"
						aria-describedby="consentpro-text-save-desc">
				<p class="description" id="consentpro-text-save-desc">
					<?php esc_html_e( 'Text for the save preferences button (Layer 2). Maximum 30 characters.', 'consentpro' ); ?>
				</p>
			</td>
		</tr>
	</table>

	<?php submit_button(); ?>
</form>
