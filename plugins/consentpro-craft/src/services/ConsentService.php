<?php
/**
 * Consent Service
 *
 * @link      https://consentpro.io
 * @copyright Copyright (c) ConsentPro Team
 */

namespace consentpro\consentpro\services;

use Craft;
use craft\base\Component;
use consentpro\consentpro\ConsentPro;
use consentpro\consentpro\events\ConfigEvent;
use consentpro\consentpro\events\RegisterCategoriesEvent;

/**
 * Consent service for building banner configuration.
 */
class ConsentService extends Component
{
    /**
     * Event triggered before rendering config.
     */
    public const EVENT_BEFORE_RENDER = 'beforeRender';

    /**
     * Event for registering custom categories.
     */
    public const EVENT_REGISTER_CATEGORIES = 'registerCategories';

    /**
     * EU country codes.
     */
    private const EU_COUNTRIES = [
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
        'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
        'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE',
    ];

    /**
     * Get banner configuration array.
     *
     * @return array
     */
    public function getConfig(): array
    {
        $settings = ConsentPro::getInstance()->getSettings();

        $config = [
            'geo' => $this->detectGeo(),
            'geoEnabled' => $settings->getEnvValue('geoEnabled'),
            'policyUrl' => $settings->getEnvValue('policyUrl'),
            'categories' => $this->getCategories(),
            'text' => $this->getText(),
            'colors' => [
                'primary' => $settings->colorPrimary,
                'secondary' => $settings->colorSecondary,
                'background' => $settings->colorBackground,
                'text' => $settings->colorText,
            ],
        ];

        // Fire event to allow config modification
        if ($this->hasEventHandlers(self::EVENT_BEFORE_RENDER)) {
            $event = new ConfigEvent(['config' => $config]);
            $this->trigger(self::EVENT_BEFORE_RENDER, $event);
            $config = $event->config;
        }

        return $config;
    }

    /**
     * Detect geo region from Cloudflare header.
     *
     * @return string|null
     */
    public function detectGeo(): ?string
    {
        $country = Craft::$app->getRequest()->getHeaders()->get('CF-IPCountry');

        if ($country === null) {
            return null;
        }

        $country = strtoupper($country);

        if ($country === 'CA') {
            return 'CA';
        }

        if (in_array($country, self::EU_COUNTRIES, true)) {
            return 'EU';
        }

        return null;
    }

    /**
     * Get formatted categories from settings.
     *
     * Uses custom category names and descriptions from settings,
     * falling back to defaults if not set.
     *
     * @return array
     */
    public function getCategories(): array
    {
        $settings = ConsentPro::getInstance()->getSettings();
        $savedCategories = $settings->categories;

        // Default category definitions
        $defaults = [
            'essential' => [
                'name' => Craft::t('consentpro', 'Essential'),
                'description' => Craft::t('consentpro', 'Required for the website to function properly.'),
            ],
            'analytics' => [
                'name' => Craft::t('consentpro', 'Analytics'),
                'description' => Craft::t('consentpro', 'Help us understand how visitors use our site.'),
            ],
            'marketing' => [
                'name' => Craft::t('consentpro', 'Marketing'),
                'description' => Craft::t('consentpro', 'Show relevant ads and track marketing campaigns.'),
            ],
            'personalization' => [
                'name' => Craft::t('consentpro', 'Personalization'),
                'description' => Craft::t('consentpro', 'Remember your preferences for a better experience.'),
            ],
        ];

        $categories = [];

        foreach (['essential', 'analytics', 'marketing', 'personalization'] as $id) {
            $savedCat = $savedCategories[$id] ?? [];
            $default = $defaults[$id];

            $categories[] = [
                'id' => $id,
                'name' => !empty($savedCat['name'])
                    ? $savedCat['name']
                    : $default['name'],
                'description' => !empty($savedCat['description'])
                    ? $savedCat['description']
                    : $default['description'],
                'required' => $id === 'essential',
            ];
        }

        // Fire event to allow category registration/modification
        if ($this->hasEventHandlers(self::EVENT_REGISTER_CATEGORIES)) {
            $event = new RegisterCategoriesEvent(['categories' => $categories]);
            $this->trigger(self::EVENT_REGISTER_CATEGORIES, $event);
            $categories = $event->categories;
        }

        return $categories;
    }

    /**
     * Get banner text strings from settings.
     *
     * Uses custom text from appearance settings,
     * falling back to defaults if not set.
     *
     * @return array
     */
    public function getText(): array
    {
        $settings = ConsentPro::getInstance()->getSettings();

        return [
            'heading' => !empty($settings->textHeading)
                ? $settings->textHeading
                : Craft::t('consentpro', 'We value your privacy'),
            'description' => Craft::t('consentpro', 'We use cookies to enhance your browsing experience and analyze our traffic.'),
            'acceptAll' => !empty($settings->textAccept)
                ? $settings->textAccept
                : Craft::t('consentpro', 'Accept All'),
            'rejectNonEssential' => !empty($settings->textReject)
                ? $settings->textReject
                : Craft::t('consentpro', 'Reject Non-Essential'),
            'settings' => !empty($settings->textSettings)
                ? $settings->textSettings
                : Craft::t('consentpro', 'Settings'),
            'save' => !empty($settings->textSave)
                ? $settings->textSave
                : Craft::t('consentpro', 'Save Preferences'),
            'back' => Craft::t('consentpro', 'Back'),
        ];
    }

    /**
     * Get custom CSS for banner styling.
     *
     * Only returns CSS if user has a Pro license.
     *
     * @return string
     */
    public function getCustomCss(): string
    {
        // Only return custom CSS for Pro users
        if (!ConsentPro::getInstance()->license->isPro()) {
            return '';
        }

        return ConsentPro::getInstance()->getSettings()->customCss ?? '';
    }

    /**
     * Check if banner should be shown based on geo and settings.
     *
     * @return bool
     */
    public function shouldShowBanner(): bool
    {
        $settings = ConsentPro::getInstance()->getSettings();

        // Check if banner is enabled
        if (!$settings->getEnvValue('enabled')) {
            return false;
        }

        // If geo-targeting is disabled, always show
        if (!$settings->getEnvValue('geoEnabled')) {
            return true;
        }

        // Check geo region
        $geo = $this->detectGeo();

        // Show for EU and CA visitors, or if geo detection failed (fail-safe)
        return $geo === 'EU' || $geo === 'CA' || $geo === null;
    }
}
