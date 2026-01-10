<?php
/**
 * ConsentPro plugin for Craft CMS 5.x
 *
 * Premium consent banner combining privacy policy, cookie consent,
 * and category-based script blocking.
 *
 * @link      https://consentpro.io
 * @copyright Copyright (c) ConsentPro Team
 */

namespace consentpro\consentpro;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use consentpro\consentpro\models\Settings;
use consentpro\consentpro\services\ConsentService;
use consentpro\consentpro\services\ConsentLogService;
use consentpro\consentpro\services\LicenseService;
use consentpro\consentpro\twig\ConsentProVariable;
use consentpro\consentpro\twig\ConsentProExtension;
use consentpro\consentpro\jobs\ValidateLicenseJob;
use consentpro\consentpro\jobs\PruneConsentLogJob;
use yii\base\Event;

/**
 * ConsentPro Plugin
 *
 * @property-read ConsentService $consent
 * @property-read ConsentLogService $consentLog
 * @property-read LicenseService $license
 * @property-read Settings $settings
 *
 * @method Settings getSettings()
 */
class ConsentPro extends Plugin
{
    /**
     * Plugin schema version.
     */
    public string $schemaVersion = '1.0.0';

    /**
     * Plugin has Control Panel settings.
     */
    public bool $hasCpSettings = true;

    /**
     * Plugin has Control Panel section.
     */
    public bool $hasCpSection = false;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        // Register alias for asset bundle
        Craft::setAlias('@consentpro', __DIR__);

        // Register services
        $this->setComponents([
            'consent' => ConsentService::class,
            'consentLog' => ConsentLogService::class,
            'license' => LicenseService::class,
        ]);

        // Register Twig extension for site requests
        if (Craft::$app->getRequest()->getIsSiteRequest()) {
            Craft::$app->getView()->registerTwigExtension(new ConsentProExtension());
        }

        // Register CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event): void {
                $event->rules['consentpro'] = 'consentpro/settings/index';
                $event->rules['consentpro/settings'] = 'consentpro/settings/index';
            }
        );

        // Register template variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event): void {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('consentpro', ConsentProVariable::class);
            }
        );

        // Schedule background jobs (weekly license validation, daily log prune)
        if (Craft::$app->getRequest()->getIsCpRequest() && !Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->scheduleValidationJob();
            $this->schedulePruneJob();
        }

        Craft::info('ConsentPro plugin loaded', __METHOD__);
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    /**
     * Schedule weekly license validation job.
     *
     * Only pushes a job if:
     * - A license key is configured
     * - Last validation was more than 7 days ago (or never)
     */
    private function scheduleValidationJob(): void
    {
        $settings = $this->getSettings();

        // Skip if no license key
        if (empty($settings->licenseKey)) {
            return;
        }

        // Check if validation needed (7 days = 604800 seconds)
        $lastValidated = $settings->licenseLastValidated ?? 0;
        $weekAgo = time() - 604800;

        if ($lastValidated < $weekAgo) {
            // Push job to queue (avoid duplicates by using a unique ID)
            $queue = Craft::$app->getQueue();

            // Check if job already in queue (simple debounce via cache)
            $cache = Craft::$app->getCache();
            $cacheKey = 'consentpro_validation_job_scheduled';

            if (!$cache->get($cacheKey)) {
                $queue->push(new ValidateLicenseJob());
                // Mark as scheduled for 1 hour to prevent duplicate pushes
                $cache->set($cacheKey, true, 3600);
            }
        }
    }

    /**
     * Schedule daily consent log prune job.
     *
     * Only pushes a job if:
     * - Pro license is active
     * - Last prune was more than 24 hours ago
     */
    private function schedulePruneJob(): void
    {
        // Only prune for Pro users
        if (!$this->license->isPro()) {
            return;
        }

        // Check if prune needed (24 hours = 86400 seconds)
        $cache = Craft::$app->getCache();
        $cacheKey = 'consentpro_prune_job_scheduled';

        if (!$cache->get($cacheKey)) {
            $queue = Craft::$app->getQueue();
            $queue->push(new PruneConsentLogJob());
            // Mark as scheduled for 24 hours
            $cache->set($cacheKey, true, 86400);
        }
    }

    /**
     * @inheritdoc
     */
    protected function afterUninstall(): void
    {
        // Remove plugin settings from project config
        Craft::$app->getProjectConfig()->remove('plugins.consentpro');
    }

    /**
     * @inheritdoc
     */
    public function getSettingsResponse(): mixed
    {
        // Redirect to our custom settings controller
        return Craft::$app->getResponse()->redirect(
            \craft\helpers\UrlHelper::cpUrl('consentpro/settings')
        );
    }
}
