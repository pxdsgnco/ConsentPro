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

        return [
            'geo' => $this->detectGeo(),
            'geoEnabled' => $settings->geoEnabled,
            'policyUrl' => $settings->policyUrl,
            'categories' => $this->getCategories(),
            'text' => $this->getText(),
            'colors' => [
                'primary' => $settings->colorPrimary,
                'secondary' => $settings->colorSecondary,
                'background' => $settings->colorBackground,
                'text' => $settings->colorText,
            ],
        ];
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
     * Get formatted categories.
     *
     * @return array
     */
    public function getCategories(): array
    {
        $settings = ConsentPro::getInstance()->getSettings();

        $categories = [
            [
                'id' => 'essential',
                'name' => Craft::t('consentpro', 'Essential'),
                'description' => Craft::t('consentpro', 'Required for the website to function properly.'),
                'required' => true,
            ],
            [
                'id' => 'analytics',
                'name' => Craft::t('consentpro', 'Analytics'),
                'description' => Craft::t('consentpro', 'Help us understand how visitors use our site.'),
                'required' => false,
            ],
            [
                'id' => 'marketing',
                'name' => Craft::t('consentpro', 'Marketing'),
                'description' => Craft::t('consentpro', 'Show relevant ads and track marketing campaigns.'),
                'required' => false,
            ],
            [
                'id' => 'personalization',
                'name' => Craft::t('consentpro', 'Personalization'),
                'description' => Craft::t('consentpro', 'Remember your preferences for a better experience.'),
                'required' => false,
            ],
        ];

        return $categories;
    }

    /**
     * Get banner text strings.
     *
     * @return array
     */
    public function getText(): array
    {
        return [
            'heading' => Craft::t('consentpro', 'We value your privacy'),
            'description' => Craft::t('consentpro', 'We use cookies to enhance your browsing experience and analyze our traffic.'),
            'acceptAll' => Craft::t('consentpro', 'Accept All'),
            'rejectNonEssential' => Craft::t('consentpro', 'Reject Non-Essential'),
            'settings' => Craft::t('consentpro', 'Settings'),
            'save' => Craft::t('consentpro', 'Save Preferences'),
            'back' => Craft::t('consentpro', 'Back'),
        ];
    }
}
