<?php
/**
 * Settings page template.
 *
 * @package ConsentPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab navigation, no data modification.
$consentpro_active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';

// Show preview panel on Appearance and Categories tabs only.
$consentpro_show_preview = in_array( $consentpro_active_tab, [ 'appearance', 'categories' ], true );
?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<nav class="nav-tab-wrapper">
		<a href="?page=consentpro&tab=general" class="nav-tab <?php echo 'general' === $consentpro_active_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'General', 'consentpro' ); ?>
		</a>
		<a href="?page=consentpro&tab=appearance" class="nav-tab <?php echo 'appearance' === $consentpro_active_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Appearance', 'consentpro' ); ?>
		</a>
		<a href="?page=consentpro&tab=categories" class="nav-tab <?php echo 'categories' === $consentpro_active_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Categories', 'consentpro' ); ?>
		</a>
		<a href="?page=consentpro&tab=consent-log" class="nav-tab <?php echo 'consent-log' === $consentpro_active_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Consent Log', 'consentpro' ); ?>
		</a>
		<a href="?page=consentpro&tab=license" class="nav-tab <?php echo 'license' === $consentpro_active_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'License', 'consentpro' ); ?>
		</a>
	</nav>

	<div class="consentpro-settings-layout<?php echo $consentpro_show_preview ? ' consentpro-settings-layout--with-preview' : ''; ?>">
		<div class="consentpro-settings-content">
			<?php
			switch ( $consentpro_active_tab ) {
				case 'appearance':
					include CONSENTPRO_PLUGIN_DIR . 'admin/views/partials/tab-appearance.php';
					break;
				case 'categories':
					include CONSENTPRO_PLUGIN_DIR . 'admin/views/partials/tab-categories.php';
					break;
				case 'consent-log':
					include CONSENTPRO_PLUGIN_DIR . 'admin/views/partials/tab-consent-log.php';
					break;
				case 'license':
					include CONSENTPRO_PLUGIN_DIR . 'admin/views/partials/tab-license.php';
					break;
				default:
					include CONSENTPRO_PLUGIN_DIR . 'admin/views/partials/tab-general.php';
					break;
			}
			?>
		</div>

		<?php if ( $consentpro_show_preview ) : ?>
		<aside class="consentpro-preview-panel" aria-labelledby="consentpro-preview-title">
			<div class="consentpro-preview-header">
				<h2 id="consentpro-preview-title"><?php esc_html_e( 'Banner Preview', 'consentpro' ); ?></h2>
				<div class="consentpro-preview-controls-wrapper">
					<div class="consentpro-preview-controls" role="group" aria-label="<?php esc_attr_e( 'Preview layer toggle', 'consentpro' ); ?>">
						<button type="button"
								class="consentpro-preview-toggle consentpro-preview-toggle--active"
								data-layer="1"
								aria-pressed="true">
							<?php esc_html_e( 'Layer 1', 'consentpro' ); ?>
						</button>
						<button type="button"
								class="consentpro-preview-toggle"
								data-layer="2"
								aria-pressed="false">
							<?php esc_html_e( 'Layer 2', 'consentpro' ); ?>
						</button>
					</div>
					<div class="consentpro-viewport-controls" role="group" aria-label="<?php esc_attr_e( 'Preview viewport toggle', 'consentpro' ); ?>">
						<button type="button"
								class="consentpro-viewport-toggle"
								data-viewport="desktop"
								aria-pressed="true"
								title="<?php esc_attr_e( 'Desktop view', 'consentpro' ); ?>">
							<span class="dashicons dashicons-desktop"></span>
							<span class="screen-reader-text"><?php esc_html_e( 'Desktop', 'consentpro' ); ?></span>
						</button>
						<button type="button"
								class="consentpro-viewport-toggle"
								data-viewport="mobile"
								aria-pressed="false"
								title="<?php esc_attr_e( 'Mobile view', 'consentpro' ); ?>">
							<span class="dashicons dashicons-smartphone"></span>
							<span class="screen-reader-text"><?php esc_html_e( 'Mobile', 'consentpro' ); ?></span>
						</button>
					</div>
				</div>
			</div>
			<div class="consentpro-preview-frame-wrapper">
				<iframe id="consentpro-preview-iframe"
						class="consentpro-preview-iframe"
						title="<?php esc_attr_e( 'Consent banner preview', 'consentpro' ); ?>"
						sandbox="allow-scripts allow-same-origin"
						aria-live="polite"></iframe>
			</div>
			<p class="consentpro-preview-note">
				<?php esc_html_e( 'Preview updates as you type. Save to apply changes to your site.', 'consentpro' ); ?>
			</p>
		</aside>
		<?php endif; ?>
	</div>
</div>
