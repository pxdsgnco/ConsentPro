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

// Define default descriptions for each category.
$consentpro_defaults = [
	'essential'       => [
		'name'        => __( 'Essential', 'consentpro' ),
		'description' => __( 'Required for the website to function properly. These cookies are always enabled.', 'consentpro' ),
	],
	'analytics'       => [
		'name'        => __( 'Analytics', 'consentpro' ),
		'description' => __( 'Help us understand how visitors interact with our website by collecting and reporting information anonymously.', 'consentpro' ),
	],
	'marketing'       => [
		'name'        => __( 'Marketing', 'consentpro' ),
		'description' => __( 'Used to track visitors across websites to display relevant advertisements.', 'consentpro' ),
	],
	'personalization' => [
		'name'        => __( 'Personalization', 'consentpro' ),
		'description' => __( 'Allow the website to remember choices you make and provide enhanced, personalized features.', 'consentpro' ),
	],
];
?>

<form method="post" action="options.php">
	<?php settings_fields( 'consentpro_categories' ); ?>

	<p><?php esc_html_e( 'Configure the consent categories shown in the settings panel. Customize the display name and description for each category.', 'consentpro' ); ?></p>

	<div class="consentpro-categories">
		<!-- Essential Category (Always Enabled) -->
		<?php $consentpro_essential = $consentpro_categories['essential'] ?? []; ?>
		<div class="consentpro-category-card consentpro-category-card--essential">
			<div class="consentpro-category-header">
				<h3><?php esc_html_e( 'Essential', 'consentpro' ); ?></h3>
				<span class="consentpro-badge consentpro-badge--required"><?php esc_html_e( 'Always Enabled', 'consentpro' ); ?></span>
			</div>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="consentpro-essential-name"><?php esc_html_e( 'Display Name', 'consentpro' ); ?></label>
					</th>
					<td>
						<input type="text"
								id="consentpro-essential-name"
								name="consentpro_categories[essential][name]"
								value="<?php echo esc_attr( $consentpro_essential['name'] ?? '' ); ?>"
								class="regular-text"
								placeholder="<?php echo esc_attr( $consentpro_defaults['essential']['name'] ); ?>">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="consentpro-essential-description"><?php esc_html_e( 'Description', 'consentpro' ); ?></label>
					</th>
					<td>
						<textarea id="consentpro-essential-description"
								name="consentpro_categories[essential][description]"
								rows="3"
								class="large-text"
								placeholder="<?php echo esc_attr( $consentpro_defaults['essential']['description'] ); ?>"
								aria-describedby="consentpro-essential-desc-help"><?php echo esc_textarea( $consentpro_essential['description'] ?? '' ); ?></textarea>
						<p class="description" id="consentpro-essential-desc-help">
							<?php esc_html_e( 'Note: Essential cookies are always enabled and cannot be disabled by visitors. Basic HTML links are allowed.', 'consentpro' ); ?>
						</p>
					</td>
				</tr>
			</table>
		</div>

		<!-- Other Categories (Analytics, Marketing, Personalization) -->
		<?php foreach ( [ 'analytics', 'marketing', 'personalization' ] as $consentpro_key ) : ?>
			<?php $consentpro_category = $consentpro_categories[ $consentpro_key ] ?? []; ?>
			<div class="consentpro-category-card">
				<h3><?php echo esc_html( $consentpro_defaults[ $consentpro_key ]['name'] ); ?></h3>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="consentpro-<?php echo esc_attr( $consentpro_key ); ?>-name"><?php esc_html_e( 'Display Name', 'consentpro' ); ?></label>
						</th>
						<td>
							<input type="text"
									id="consentpro-<?php echo esc_attr( $consentpro_key ); ?>-name"
									name="consentpro_categories[<?php echo esc_attr( $consentpro_key ); ?>][name]"
									value="<?php echo esc_attr( $consentpro_category['name'] ?? '' ); ?>"
									class="regular-text"
									placeholder="<?php echo esc_attr( $consentpro_defaults[ $consentpro_key ]['name'] ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="consentpro-<?php echo esc_attr( $consentpro_key ); ?>-description"><?php esc_html_e( 'Description', 'consentpro' ); ?></label>
						</th>
						<td>
							<textarea id="consentpro-<?php echo esc_attr( $consentpro_key ); ?>-description"
									name="consentpro_categories[<?php echo esc_attr( $consentpro_key ); ?>][description]"
									rows="3"
									class="large-text"
									placeholder="<?php echo esc_attr( $consentpro_defaults[ $consentpro_key ]['description'] ); ?>"
									aria-describedby="consentpro-<?php echo esc_attr( $consentpro_key ); ?>-desc-help"><?php echo esc_textarea( $consentpro_category['description'] ?? '' ); ?></textarea>
							<p class="description" id="consentpro-<?php echo esc_attr( $consentpro_key ); ?>-desc-help">
								<?php
								printf(
									/* translators: %s: Example HTML link markup */
									esc_html__( 'Basic HTML links are allowed (e.g., %s).', 'consentpro' ),
									'<code>&lt;a href="..."&gt;link&lt;/a&gt;</code>'
								);
								?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Default State', 'consentpro' ); ?>
						</th>
						<td>
							<label>
								<input type="checkbox"
										name="consentpro_categories[<?php echo esc_attr( $consentpro_key ); ?>][default]"
										value="1"
										<?php checked( ! empty( $consentpro_category['default'] ) ); ?>>
								<?php esc_html_e( 'Pre-selected by default (visitors can still opt out)', 'consentpro' ); ?>
							</label>
						</td>
					</tr>
				</table>
			</div>
		<?php endforeach; ?>
	</div>

	<?php submit_button(); ?>
</form>
