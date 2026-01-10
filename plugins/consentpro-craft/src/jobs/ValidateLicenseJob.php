<?php
/**
 * Validate License Job
 *
 * @link      https://consentpro.io
 * @copyright Copyright (c) ConsentPro Team
 */

namespace consentpro\consentpro\jobs;

use Craft;
use craft\queue\BaseJob;
use consentpro\consentpro\ConsentPro;

/**
 * Queue job to periodically validate license key.
 *
 * This job runs weekly via the Craft queue to re-validate the
 * license key with the remote API. If validation fails, the
 * LicenseService handles grace period logic.
 */
class ValidateLicenseJob extends BaseJob
{
    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        $settings = ConsentPro::getInstance()->getSettings();

        // Skip if no license key configured
        if (empty($settings->licenseKey)) {
            Craft::info('[ConsentPro] No license key configured, skipping validation.', 'consentpro');
            return;
        }

        // Validate with remote API
        $result = ConsentPro::getInstance()->license->validate($settings->licenseKey);

        if (!empty($result['valid'])) {
            Craft::info('[ConsentPro] License validation successful.', 'consentpro');
        } elseif (!empty($result['grace_period'])) {
            Craft::warning(
                '[ConsentPro] License validation failed, grace period active: ' . ($result['error'] ?? 'Unknown'),
                'consentpro'
            );
        } else {
            Craft::warning(
                '[ConsentPro] License validation failed: ' . ($result['error'] ?? 'Unknown'),
                'consentpro'
            );
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): ?string
    {
        return Craft::t('consentpro', 'Validating ConsentPro license');
    }
}
