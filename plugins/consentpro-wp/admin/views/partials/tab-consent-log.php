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
	<?php if ( ! ConsentPro_License::is_pro() ) : ?>
		<div class="consentpro-pro-notice">
			<h3><?php esc_html_e( 'Consent Logging - Pro Feature', 'consentpro' ); ?></h3>
			<p><?php esc_html_e( 'Track consent records for compliance auditing with ConsentPro Pro. View aggregated metrics, browse individual consent events, and maintain audit-ready logs.', 'consentpro' ); ?></p>
			<a href="https://consentpro.io/pricing" class="button button-primary" target="_blank" rel="noopener">
				<?php esc_html_e( 'Upgrade to Pro', 'consentpro' ); ?>
			</a>
		</div>
	<?php else : ?>
		<!-- Metrics Card -->
		<div class="consentpro-metrics-card">
			<h3><?php esc_html_e( 'Consent Metrics (Last 30 Days)', 'consentpro' ); ?></h3>
			<div id="consentpro-metrics-container" class="consentpro-metrics-container">
				<p class="consentpro-loading"><?php esc_html_e( 'Loading metrics...', 'consentpro' ); ?></p>
			</div>
		</div>

		<!-- Log Table -->
		<div class="consentpro-log-card">
			<div class="consentpro-log-header">
				<h3><?php esc_html_e( 'Consent Log', 'consentpro' ); ?></h3>
				<button type="button" id="consentpro-clear-log" class="button button-secondary">
					<?php esc_html_e( 'Clear Log', 'consentpro' ); ?>
				</button>
			</div>
			<div id="consentpro-log-table-container" class="consentpro-log-table-container">
				<p class="consentpro-loading"><?php esc_html_e( 'Loading log entries...', 'consentpro' ); ?></p>
			</div>
			<div id="consentpro-log-pagination" class="consentpro-log-pagination"></div>
		</div>

		<div class="consentpro-log-info">
			<p class="description">
				<?php esc_html_e( 'Consent events are logged when visitors interact with your consent banner. Data is anonymized and automatically pruned after 90 days.', 'consentpro' ); ?>
			</p>
		</div>
	<?php endif; ?>
</div>
