<?php
/**
 * ConsentPro Install Migration
 *
 * @link      https://consentpro.io
 * @copyright Copyright (c) ConsentPro Team
 */

namespace consentpro\consentpro\migrations;

use Craft;
use craft\db\Migration;

/**
 * Install migration for ConsentPro plugin.
 *
 * Creates the consent log table for storing consent events.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTables();
        $this->createIndexes();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%consentpro_consent_log}}');

        return true;
    }

    /**
     * Create database tables.
     */
    private function createTables(): void
    {
        // Create consent log table
        $this->createTable('{{%consentpro_consent_log}}', [
            'id' => $this->primaryKey(),
            'timestamp' => $this->dateTime()->notNull(),
            'consentType' => $this->string(30)->notNull(),
            'categories' => $this->string(255)->notNull(),
            'region' => $this->string(10)->null(),
            'visitorHash' => $this->string(64)->notNull(),
        ]);
    }

    /**
     * Create indexes for efficient queries.
     */
    private function createIndexes(): void
    {
        // Index for timestamp-based queries (30-day metrics, 90-day prune)
        $this->createIndex(
            'idx_consentpro_log_timestamp',
            '{{%consentpro_consent_log}}',
            'timestamp'
        );

        // Index for consent type aggregation
        $this->createIndex(
            'idx_consentpro_log_consent_type',
            '{{%consentpro_consent_log}}',
            'consentType'
        );

        // Index for region-based filtering
        $this->createIndex(
            'idx_consentpro_log_region',
            '{{%consentpro_consent_log}}',
            'region'
        );

        // Composite index for deduplication check (visitor hash + date)
        $this->createIndex(
            'idx_consentpro_log_visitor_hash',
            '{{%consentpro_consent_log}}',
            'visitorHash'
        );
    }
}
