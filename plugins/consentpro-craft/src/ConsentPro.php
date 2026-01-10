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
use consentpro\consentpro\services\LicenseService;
use consentpro\consentpro\twig\ConsentProVariable;
use consentpro\consentpro\twig\ConsentProExtension;
use yii\base\Event;

/**
 * ConsentPro Plugin
 *
 * @property-read ConsentService $consent
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
    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate(
            'consentpro/settings/index',
            ['settings' => $this->getSettings()]
        );
    }
}
