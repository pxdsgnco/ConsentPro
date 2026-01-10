<?php
/**
 * Consent Log Service
 *
 * @link      https://consentpro.io
 * @copyright Copyright (c) ConsentPro Team
 */

namespace consentpro\consentpro\services;

use Craft;
use craft\base\Component;
use craft\db\Query;
use consentpro\consentpro\ConsentPro;

/**
 * Service for managing consent event logs.
 *
 * Handles logging consent events, retrieving metrics,
 * paginated log entries, and pruning old records.
 */
class ConsentLogService extends Component
{
    /**
     * Table name for consent log.
     */
    private const TABLE = '{{%consentpro_consent_log}}';

    /**
     * Log a consent event.
     *
     * @param string $type Consent type: accept_all, reject_non_essential, custom
     * @param array $categories Consented categories {essential: true, analytics: bool, ...}
     * @param string|null $region Geo region: EU, CA, or null
     * @return bool Whether the event was logged
     */
    public function logConsent(string $type, array $categories, ?string $region): bool
    {
        // Only log for Pro users
        if (!ConsentPro::getInstance()->license->isPro()) {
            return false;
        }

        $visitorHash = $this->generateVisitorHash();

        // Check if already logged today (deduplication)
        if ($this->hasLoggedToday($visitorHash)) {
            return false;
        }

        // Insert log entry
        Craft::$app->getDb()->createCommand()
            ->insert(self::TABLE, [
                'timestamp' => date('Y-m-d H:i:s'),
                'consentType' => $type,
                'categories' => json_encode($categories),
                'region' => $region,
                'visitorHash' => $visitorHash,
            ])
            ->execute();

        return true;
    }

    /**
     * Get consent metrics for a time period.
     *
     * @param int $days Number of days to include (default: 30)
     * @return array Metrics with totals and percentages
     */
    public function getMetrics(int $days = 30): array
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        // Get counts grouped by consent type
        $results = (new Query())
            ->select(['consentType', 'COUNT(*) as count'])
            ->from(self::TABLE)
            ->where(['>=', 'timestamp', $since])
            ->groupBy('consentType')
            ->all();

        // Initialize metrics
        $metrics = [
            'total' => 0,
            'accept_all' => 0,
            'reject_non_essential' => 0,
            'custom' => 0,
            'accept_percent' => 0.0,
            'reject_percent' => 0.0,
            'custom_percent' => 0.0,
        ];

        // Populate counts
        foreach ($results as $row) {
            $type = $row['consentType'];
            $count = (int) $row['count'];

            if (isset($metrics[$type])) {
                $metrics[$type] = $count;
            }
            $metrics['total'] += $count;
        }

        // Calculate percentages
        if ($metrics['total'] > 0) {
            $metrics['accept_percent'] = round(
                ($metrics['accept_all'] / $metrics['total']) * 100,
                1
            );
            $metrics['reject_percent'] = round(
                ($metrics['reject_non_essential'] / $metrics['total']) * 100,
                1
            );
            $metrics['custom_percent'] = round(
                ($metrics['custom'] / $metrics['total']) * 100,
                1
            );
        }

        return $metrics;
    }

    /**
     * Get paginated log entries.
     *
     * @param int $page Page number (1-indexed)
     * @param int $perPage Entries per page (max 100)
     * @return array Entries with pagination metadata
     */
    public function getEntries(int $page = 1, int $perPage = 50): array
    {
        // Enforce limits
        $page = max(1, $page);
        $perPage = min(100, max(1, $perPage));
        $offset = ($page - 1) * $perPage;

        // Get total count
        $total = (new Query())
            ->from(self::TABLE)
            ->count();

        // Get entries
        $entries = (new Query())
            ->select(['id', 'timestamp', 'consentType', 'categories', 'region'])
            ->from(self::TABLE)
            ->orderBy(['timestamp' => SORT_DESC])
            ->offset($offset)
            ->limit($perPage)
            ->all();

        // Parse JSON categories
        foreach ($entries as &$entry) {
            $entry['categories'] = json_decode($entry['categories'], true) ?? [];
        }

        return [
            'entries' => $entries,
            'total' => (int) $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Clear all log entries.
     *
     * @return int Number of deleted entries
     */
    public function clearLog(): int
    {
        $deleted = Craft::$app->getDb()->createCommand()
            ->delete(self::TABLE)
            ->execute();

        return $deleted;
    }

    /**
     * Prune entries older than specified days.
     *
     * @param int $days Retention period in days (default: 90)
     * @return int Number of deleted entries
     */
    public function pruneOldEntries(int $days = 90): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $deleted = Craft::$app->getDb()->createCommand()
            ->delete(self::TABLE, ['<', 'timestamp', $cutoff])
            ->execute();

        return $deleted;
    }

    /**
     * Check if visitor has already logged consent today.
     *
     * @param string $visitorHash The visitor hash
     * @return bool Whether visitor has logged today
     */
    private function hasLoggedToday(string $visitorHash): bool
    {
        $today = date('Y-m-d 00:00:00');

        return (new Query())
            ->from(self::TABLE)
            ->where(['visitorHash' => $visitorHash])
            ->andWhere(['>=', 'timestamp', $today])
            ->exists();
    }

    /**
     * Generate a visitor hash for deduplication.
     *
     * Uses daily salt + IP + User-Agent, hashed for privacy.
     * Resets daily to prevent tracking across days.
     *
     * @return string SHA-256 hash
     */
    private function generateVisitorHash(): string
    {
        $request = Craft::$app->getRequest();

        // Daily salt (rotates each day)
        $dailySalt = date('Y-m-d');

        // Get IP (anonymized last octet for privacy)
        $ip = $request->getUserIP() ?? '0.0.0.0';

        // Get User-Agent
        $userAgent = $request->getUserAgent() ?? '';

        // Generate hash
        return hash('sha256', $dailySalt . $ip . $userAgent);
    }

    /**
     * Determine consent type from categories.
     *
     * @param array $categories The consent categories
     * @return string Consent type: accept_all, reject_non_essential, custom
     */
    public function determineConsentType(array $categories): string
    {
        // Essential is always true
        $nonEssentialCategories = ['analytics', 'marketing', 'personalization'];

        $allAccepted = true;
        $allRejected = true;

        foreach ($nonEssentialCategories as $category) {
            $accepted = !empty($categories[$category]);
            if (!$accepted) {
                $allAccepted = false;
            }
            if ($accepted) {
                $allRejected = false;
            }
        }

        if ($allAccepted) {
            return 'accept_all';
        }

        if ($allRejected) {
            return 'reject_non_essential';
        }

        return 'custom';
    }
}
