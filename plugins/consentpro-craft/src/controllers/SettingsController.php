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
     * Valid tab identifiers.
     */
    private const VALID_TABS = ['general', 'appearance', 'categories', 'license'];

    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        $this->requireAdmin();

        return parent::beforeAction($action);
    }

    /**
     * Settings index action with tab support.
     *
     * @param string|null $tab The active tab.
     * @return Response
     */
    public function actionIndex(?string $tab = null): Response
    {
        $settings = ConsentPro::getInstance()->getSettings();

        // Get tab from query param or default to 'general'
        $selectedTab = $tab ?? Craft::$app->getRequest()->getQueryParam('tab', 'general');

        // Validate tab
        if (!in_array($selectedTab, self::VALID_TABS, true)) {
            $selectedTab = 'general';
        }

        return $this->renderTemplate('consentpro/settings/index', [
            'settings' => $settings,
            'selectedTab' => $selectedTab,
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
        $request = Craft::$app->getRequest();

        // Get current tab for redirect
        $selectedTab = $request->getBodyParam('selectedTab', 'general');

        // =========================================================================
        // General Tab Fields (US-028)
        // =========================================================================

        // Only update if not overridden by env var
        if (!$settings->isEnvOverride('enabled')) {
            $settings->enabled = (bool) $request->getBodyParam('enabled');
        }

        if (!$settings->isEnvOverride('policyUrl')) {
            $settings->policyUrl = trim($request->getBodyParam('policyUrl', ''));
        }

        if (!$settings->isEnvOverride('geoEnabled')) {
            $settings->geoEnabled = (bool) $request->getBodyParam('geoEnabled');
        }

        // =========================================================================
        // Appearance Tab Fields - Colors (US-029)
        // =========================================================================

        $settings->colorPrimary = $request->getBodyParam('colorPrimary', '#2563eb');
        $settings->colorSecondary = $request->getBodyParam('colorSecondary', '#64748b');
        $settings->colorBackground = $request->getBodyParam('colorBackground', '#ffffff');
        $settings->colorText = $request->getBodyParam('colorText', '#1e293b');

        // =========================================================================
        // Appearance Tab Fields - Text (US-029)
        // =========================================================================

        // Text fields with character limit enforcement
        $settings->textHeading = mb_substr(
            trim($request->getBodyParam('textHeading', 'We value your privacy')),
            0,
            100
        );

        $settings->textAccept = mb_substr(
            trim($request->getBodyParam('textAccept', 'Accept All')),
            0,
            30
        );

        $settings->textReject = mb_substr(
            trim($request->getBodyParam('textReject', 'Reject Non-Essential')),
            0,
            30
        );

        $settings->textSettings = mb_substr(
            trim($request->getBodyParam('textSettings', 'Cookie Settings')),
            0,
            30
        );

        $settings->textSave = mb_substr(
            trim($request->getBodyParam('textSave', 'Save Preferences')),
            0,
            30
        );

        // =========================================================================
        // Categories Tab Fields (US-030)
        // =========================================================================

        $categoriesInput = $request->getBodyParam('categories', []);
        if (is_array($categoriesInput) && !empty($categoriesInput)) {
            $settings->categories = $this->sanitizeCategories($categoriesInput);
        }

        // =========================================================================
        // License Tab Fields
        // =========================================================================

        $settings->licenseKey = trim($request->getBodyParam('licenseKey', ''));

        // =========================================================================
        // Validation
        // =========================================================================

        if (!$settings->validate()) {
            Craft::$app->getSession()->setError(
                Craft::t('consentpro', 'Couldn\'t save settings. Please check the form for errors.')
            );

            // Return to the same tab with validation errors
            return $this->renderTemplate('consentpro/settings/index', [
                'settings' => $settings,
                'selectedTab' => $selectedTab,
            ]);
        }

        // =========================================================================
        // Save to Project Config
        // =========================================================================

        if (!Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->toArray())) {
            Craft::$app->getSession()->setError(
                Craft::t('consentpro', 'Couldn\'t save settings.')
            );

            return null;
        }

        Craft::$app->getSession()->setNotice(
            Craft::t('consentpro', 'Settings saved.')
        );

        return $this->redirectToPostedUrl();
    }

    /**
     * Preview action for live preview iframe.
     *
     * @return Response
     */
    public function actionPreview(): Response
    {
        $settings = ConsentPro::getInstance()->getSettings();
        $consentService = ConsentPro::getInstance()->consent;

        return $this->renderTemplate('consentpro/settings/_preview-frame', [
            'settings' => $settings,
            'config' => $consentService->getConfig(),
        ]);
    }

    /**
     * Sanitize categories input from POST.
     *
     * @param array $input The raw categories input.
     * @return array The sanitized categories.
     */
    private function sanitizeCategories(array $input): array
    {
        $sanitized = [];
        $validKeys = ['essential', 'analytics', 'marketing', 'personalization'];

        // Allowed HTML tags for descriptions (only links)
        $allowedTags = '<a>';

        foreach ($input as $key => $category) {
            // Skip invalid keys
            if (!in_array($key, $validKeys, true)) {
                continue;
            }

            // Sanitize name (strip all HTML)
            $name = isset($category['name']) && is_string($category['name'])
                ? trim(strip_tags($category['name']))
                : '';

            // Sanitize description (allow only links)
            $description = isset($category['description']) && is_string($category['description'])
                ? trim(strip_tags($category['description'], $allowedTags))
                : '';

            // Handle required field
            // Essential is always required, others can be toggled
            if ($key === 'essential') {
                $required = true;
            } else {
                $required = isset($category['required'])
                    && (bool) $category['required'];
            }

            $sanitized[$key] = [
                'name' => $name,
                'description' => $description,
                'required' => $required,
            ];
        }

        // Ensure all categories exist with defaults if missing
        $defaults = [
            'essential' => [
                'name' => 'Essential',
                'description' => 'Required for the website to function properly.',
                'required' => true,
            ],
            'analytics' => [
                'name' => 'Analytics',
                'description' => 'Help us understand how visitors interact with our website.',
                'required' => false,
            ],
            'marketing' => [
                'name' => 'Marketing',
                'description' => 'Used to track visitors across websites.',
                'required' => false,
            ],
            'personalization' => [
                'name' => 'Personalization',
                'description' => 'Remember your choices for a better experience.',
                'required' => false,
            ],
        ];

        foreach ($validKeys as $key) {
            if (!isset($sanitized[$key])) {
                $sanitized[$key] = $defaults[$key];
            }
        }

        return $sanitized;
    }
}
