<?php
/**
 * Prune Consent Log Job
 *
 * @link      https://consentpro.io
 * @copyright Copyright (c) ConsentPro Team
 */

namespace consentpro\consentpro\jobs;

use Craft;
use craft\queue\BaseJob;
use consentpro\consentpro\ConsentPro;

/**
 * Queue job to prune old consent log entries.
 *
 * Runs daily via Craft queue to delete entries older than
 * the retention period (default: 90 days).
 */
class PruneConsentLogJob extends BaseJob
{
    /**
     * Default retention period in days.
     */
    public int $retentionDays = 90;

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        // Only prune for Pro users (Core doesn't have logging)
        if (!ConsentPro::getInstance()->license->isPro()) {
            Craft::info('[ConsentPro] Skipping consent log prune - Pro license required.', 'consentpro');
            return;
        }

        $deleted = ConsentPro::getInstance()->consentLog->pruneOldEntries($this->retentionDays);

        if ($deleted > 0) {
            Craft::info(
                "[ConsentPro] Pruned {$deleted} consent log entries older than {$this->retentionDays} days.",
                'consentpro'
            );
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): ?string
    {
        return Craft::t('consentpro', 'Pruning old consent log entries');
    }
}
