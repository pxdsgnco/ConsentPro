<?php
/**
 * Dashboard widget for consent metrics.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard widget class.
 */
class ConsentPro_Dashboard_Widget {

	/**
	 * Widget ID.
	 *
	 * @var string
	 */
	private const WIDGET_ID = 'consentpro_consent_metrics';

	/**
	 * Register the dashboard widget.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! ConsentPro_License::is_pro() ) {
			return;
		}

		wp_add_dashboard_widget(
			self::WIDGET_ID,
			__( 'ConsentPro - Consent Metrics', 'consentpro' ),
			[ $this, 'render' ],
			null,
			null,
			'normal',
			'high'
		);
	}

	/**
	 * Render the widget content.
	 *
	 * @return void
	 */
	public function render(): void {
		$consent_log = new ConsentPro_Consent_Log();
		$metrics     = $consent_log->get_metrics( 30 );

		if ( 0 === $metrics['total'] ) {
			$this->render_empty_state();
			return;
		}

		$this->render_metrics( $metrics );
	}

	/**
	 * Render empty state message.
	 *
	 * @return void
	 */
	private function render_empty_state(): void {
		?>
		<div class="consentpro-metrics-empty">
			<p><?php esc_html_e( 'No consent data recorded yet. Consent metrics will appear here once visitors interact with your consent banner.', 'consentpro' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Render metrics display.
	 *
	 * @param array $metrics Metrics data.
	 * @return void
	 */
	private function render_metrics( array $metrics ): void {
		?>
		<style>
			.consentpro-metrics-widget { padding: 10px 0; }
			.consentpro-metrics-summary { margin-bottom: 15px; }
			.consentpro-metric-card--total {
				text-align: center;
				padding: 15px;
				background: #f0f6fc;
				border-radius: 6px;
			}
			.consentpro-metric-card--total .consentpro-metric-value {
				display: block;
				font-size: 36px;
				font-weight: 700;
				color: #2271b1;
			}
			.consentpro-metric-card--total .consentpro-metric-label {
				display: block;
				font-size: 12px;
				color: #646970;
				margin-top: 5px;
			}
			.consentpro-metric-bar {
				display: flex;
				height: 20px;
				border-radius: 10px;
				overflow: hidden;
				background: #e5e7eb;
				margin-bottom: 10px;
			}
			.consentpro-metric-bar-segment { transition: width 0.3s ease; }
			.consentpro-metric-bar-segment--accept { background: #059669; }
			.consentpro-metric-bar-segment--custom { background: #d97706; }
			.consentpro-metric-bar-segment--reject { background: #dc2626; }
			.consentpro-metrics-legend {
				display: flex;
				justify-content: center;
				gap: 15px;
				font-size: 12px;
				flex-wrap: wrap;
			}
			.consentpro-legend-item::before {
				content: '';
				display: inline-block;
				width: 10px;
				height: 10px;
				border-radius: 2px;
				margin-right: 5px;
				vertical-align: middle;
			}
			.consentpro-legend-item--accept::before { background: #059669; }
			.consentpro-legend-item--custom::before { background: #d97706; }
			.consentpro-legend-item--reject::before { background: #dc2626; }
			.consentpro-metrics-link { margin-top: 15px; text-align: center; }
		</style>
		<div class="consentpro-metrics-widget">
			<div class="consentpro-metrics-summary">
				<div class="consentpro-metric-card consentpro-metric-card--total">
					<span class="consentpro-metric-value"><?php echo esc_html( number_format_i18n( $metrics['total'] ) ); ?></span>
					<span class="consentpro-metric-label"><?php esc_html_e( 'Total Consents (30d)', 'consentpro' ); ?></span>
				</div>
			</div>
			<div class="consentpro-metrics-breakdown">
				<div class="consentpro-metric-bar">
					<?php
					/* translators: %s: percentage value */
					$accept_title = sprintf( __( 'Accept All: %s%%', 'consentpro' ), $metrics['accept_percent'] );
					/* translators: %s: percentage value */
					$custom_title = sprintf( __( 'Custom: %s%%', 'consentpro' ), $metrics['custom_percent'] );
					/* translators: %s: percentage value */
					$reject_title = sprintf( __( 'Reject: %s%%', 'consentpro' ), $metrics['reject_percent'] );
					?>
					<div class="consentpro-metric-bar-segment consentpro-metric-bar-segment--accept"
						style="width: <?php echo esc_attr( $metrics['accept_percent'] ); ?>%"
						title="<?php echo esc_attr( $accept_title ); ?>">
					</div>
					<div class="consentpro-metric-bar-segment consentpro-metric-bar-segment--custom"
						style="width: <?php echo esc_attr( $metrics['custom_percent'] ); ?>%"
						title="<?php echo esc_attr( $custom_title ); ?>">
					</div>
					<div class="consentpro-metric-bar-segment consentpro-metric-bar-segment--reject"
						style="width: <?php echo esc_attr( $metrics['reject_percent'] ); ?>%"
						title="<?php echo esc_attr( $reject_title ); ?>">
					</div>
				</div>
				<div class="consentpro-metrics-legend">
					<span class="consentpro-legend-item consentpro-legend-item--accept">
						<?php
						printf(
							/* translators: %s: percentage */
							esc_html__( 'Accept: %s%%', 'consentpro' ),
							esc_html( $metrics['accept_percent'] )
						);
						?>
					</span>
					<span class="consentpro-legend-item consentpro-legend-item--custom">
						<?php
						printf(
							/* translators: %s: percentage */
							esc_html__( 'Custom: %s%%', 'consentpro' ),
							esc_html( $metrics['custom_percent'] )
						);
						?>
					</span>
					<span class="consentpro-legend-item consentpro-legend-item--reject">
						<?php
						printf(
							/* translators: %s: percentage */
							esc_html__( 'Reject: %s%%', 'consentpro' ),
							esc_html( $metrics['reject_percent'] )
						);
						?>
					</span>
				</div>
			</div>
			<p class="consentpro-metrics-link">
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=consentpro&tab=consent-log' ) ); ?>">
					<?php esc_html_e( 'View detailed log', 'consentpro' ); ?> &rarr;
				</a>
			</p>
		</div>
		<?php
	}
}
