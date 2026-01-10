<?php
/**
 * License settings tab.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$consentpro_license_key = get_option( 'consentpro_license_key', '' );
$consentpro_license     = get_option( 'consentpro_license', [] );
$consentpro_is_pro      = ConsentPro_License::is_pro();
$consentpro_grace_days  = ConsentPro_License::get_grace_days_remaining();
?>

<div class="consentpro-license-container">
	<!-- License Status Card -->
	<div class="consentpro-license-card">
		<div class="consentpro-license-card__header">
			<h3><?php esc_html_e( 'License Status', 'consentpro' ); ?></h3>
		</div>
		<div class="consentpro-license-card__body">
			<div id="consentpro-license-status" class="consentpro-license-status-display">
				<?php if ( $consentpro_is_pro ) : ?>
					<div class="consentpro-license-indicator consentpro-license-indicator--active">
						<span class="consentpro-license-indicator__icon dashicons dashicons-yes-alt"></span>
						<div class="consentpro-license-indicator__info">
							<span class="consentpro-license-indicator__label"><?php esc_html_e( 'Active', 'consentpro' ); ?></span>
							<span class="consentpro-license-indicator__tier"><?php echo esc_html( ucfirst( $consentpro_license['tier'] ?? 'Pro' ) ); ?></span>
						</div>
					</div>
					<?php if ( ! empty( $consentpro_license['expires'] ) ) : ?>
						<p class="consentpro-license-expiry">
							<?php
							printf(
								/* translators: %s: expiry date */
								esc_html__( 'Expires: %s', 'consentpro' ),
								esc_html( date_i18n( get_option( 'date_format' ), strtotime( $consentpro_license['expires'] ) ) )
							);
							?>
						</p>
					<?php endif; ?>
					<?php if ( $consentpro_grace_days ) : ?>
						<p class="consentpro-license-grace-warning">
							<span class="dashicons dashicons-warning"></span>
							<?php
							printf(
								/* translators: %d: number of days */
								esc_html__( 'Grace period: %d days remaining. Please check your license.', 'consentpro' ),
								(int) $consentpro_grace_days
							);
							?>
						</p>
					<?php endif; ?>
				<?php else : ?>
					<div class="consentpro-license-indicator consentpro-license-indicator--inactive">
						<span class="consentpro-license-indicator__icon dashicons dashicons-marker"></span>
						<div class="consentpro-license-indicator__info">
							<span class="consentpro-license-indicator__label"><?php esc_html_e( 'Core', 'consentpro' ); ?></span>
							<span class="consentpro-license-indicator__tier"><?php esc_html_e( 'Free', 'consentpro' ); ?></span>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- License Key Form -->
	<div class="consentpro-license-card">
		<div class="consentpro-license-card__header">
			<h3><?php esc_html_e( 'License Key', 'consentpro' ); ?></h3>
		</div>
		<div class="consentpro-license-card__body">
			<div class="consentpro-license-form">
				<div class="consentpro-license-input-group">
					<input
						type="text"
						id="consentpro-license-key"
						name="consentpro_license_key"
						value="<?php echo esc_attr( $consentpro_license_key ); ?>"
						class="regular-text"
						placeholder="XXXX-XXXX-XXXX-XXXX"
						autocomplete="off"
					>
					<button
						type="button"
						id="consentpro-validate-license"
						class="button button-primary"
					>
						<span class="consentpro-btn-text"><?php esc_html_e( 'Activate License', 'consentpro' ); ?></span>
						<span class="consentpro-btn-spinner spinner" style="display: none;"></span>
					</button>
				</div>
				<p class="description"><?php esc_html_e( 'Enter your license key to unlock Pro features.', 'consentpro' ); ?></p>

				<!-- Validation Message Area -->
				<div id="consentpro-license-message" class="consentpro-license-message" style="display: none;"></div>
			</div>
		</div>
	</div>

	<!-- Pro Features List -->
	<div class="consentpro-license-card">
		<div class="consentpro-license-card__header">
			<h3><?php esc_html_e( 'Pro Features', 'consentpro' ); ?></h3>
		</div>
		<div class="consentpro-license-card__body">
			<ul class="consentpro-features-list">
				<li>
					<span class="dashicons dashicons-yes"></span>
					<?php esc_html_e( 'Custom CSS injection for banner styling', 'consentpro' ); ?>
				</li>
				<li>
					<span class="dashicons dashicons-yes"></span>
					<?php esc_html_e( 'Consent analytics dashboard', 'consentpro' ); ?>
				</li>
				<li>
					<span class="dashicons dashicons-yes"></span>
					<?php esc_html_e( 'Consent event logging', 'consentpro' ); ?>
				</li>
				<li>
					<span class="dashicons dashicons-yes"></span>
					<?php esc_html_e( 'Priority support', 'consentpro' ); ?>
				</li>
			</ul>
			<?php if ( ! $consentpro_is_pro ) : ?>
				<div class="consentpro-upgrade-cta">
					<a href="https://consentpro.io/pricing" target="_blank" class="button button-primary">
						<?php esc_html_e( 'Upgrade to Pro', 'consentpro' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
