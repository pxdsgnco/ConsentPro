<?php
/**
 * Settings Controller
 *
 * @link      https://consentpro.io
 * @copyright Copyright (c) ConsentPro Team
 */

namespace consentpro\consentpro\controllers;

use Craft;
use craft\web\Controller;
use consentpro\consentpro\ConsentPro;
use yii\web\Response;

/**
 * Settings controller for Control Panel.
 */
class SettingsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        $this->requireAdmin();

        return parent::beforeAction($action);
    }

    /**
     * Settings index action.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        $settings = ConsentPro::getInstance()->getSettings();

        return $this->renderTemplate('consentpro/settings/index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Save settings action.
     *
     * @return Response|null
     */
    public function actionSave(): ?Response
    {
        $this->requirePostRequest();

        $plugin = ConsentPro::getInstance();
        $settings = $plugin->getSettings();

        // Populate from POST
        $settings->enabled = (bool) Craft::$app->getRequest()->getBodyParam('enabled');
        $settings->policyUrl = Craft::$app->getRequest()->getBodyParam('policyUrl', '');
        $settings->geoEnabled = (bool) Craft::$app->getRequest()->getBodyParam('geoEnabled');
        $settings->colorPrimary = Craft::$app->getRequest()->getBodyParam('colorPrimary', '#2563eb');
        $settings->colorSecondary = Craft::$app->getRequest()->getBodyParam('colorSecondary', '#64748b');
        $settings->colorBackground = Craft::$app->getRequest()->getBodyParam('colorBackground', '#ffffff');
        $settings->colorText = Craft::$app->getRequest()->getBodyParam('colorText', '#1e293b');
        $settings->licenseKey = Craft::$app->getRequest()->getBodyParam('licenseKey', '');

        // Save to project config
        if (!Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->toArray())) {
            Craft::$app->getSession()->setError(Craft::t('consentpro', 'Couldn\'t save settings.'));

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('consentpro', 'Settings saved.'));

        return $this->redirectToPostedUrl();
    }
}
