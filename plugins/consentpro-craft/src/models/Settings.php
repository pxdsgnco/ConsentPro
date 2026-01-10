<?php
/**
 * ConsentPro Settings Model
 *
 * @link      https://consentpro.io
 * @copyright Copyright (c) ConsentPro Team
 */

namespace consentpro\consentpro\models;

use Craft;
use craft\base\Model;
use craft\helpers\App;

/**
 * Settings model for ConsentPro plugin.
 */
class Settings extends Model
{
    // =========================================================================
    // General Settings (US-028)
    // =========================================================================

    /**
     * Whether banner is enabled.
     */
    public bool $enabled = true;

    /**
     * Privacy policy URL.
     */
    public string $policyUrl = '';

    /**
     * Whether geo-targeting is enabled.
     */
    public bool $geoEnabled = true;

    // =========================================================================
    // Appearance Settings - Colors (US-029)
    // =========================================================================

    /**
     * Primary color (hex).
     */
    public string $colorPrimary = '#2563eb';

    /**
     * Secondary color (hex).
     */
    public string $colorSecondary = '#64748b';

    /**
     * Background color (hex).
     */
    public string $colorBackground = '#ffffff';

    /**
     * Text color (hex).
     */
    public string $colorText = '#1e293b';

    // =========================================================================
    // Appearance Settings - Text Fields (US-029)
    // =========================================================================

    /**
     * Banner heading text (max 100 chars).
     */
    public string $textHeading = 'We value your privacy';

    /**
     * Accept All button text (max 30 chars).
     */
    public string $textAccept = 'Accept All';

    /**
     * Reject Non-Essential button text (max 30 chars).
     */
    public string $textReject = 'Reject Non-Essential';

    /**
     * Settings link text (max 30 chars).
     */
    public string $textSettings = 'Cookie Settings';

    /**
     * Save Preferences button text (max 30 chars).
     */
    public string $textSave = 'Save Preferences';

    // =========================================================================
    // Categories Settings (US-030)
    // =========================================================================

    /**
     * Category definitions with defaults.
     */
    public array $categories = [
        'essential' => [
            'name' => 'Essential',
            'description' => 'Required for the website to function properly. These cookies are always enabled.',
            'required' => true,
        ],
        'analytics' => [
            'name' => 'Analytics',
            'description' => 'Help us understand how visitors interact with our website by collecting and reporting information anonymously.',
            'required' => false,
        ],
        'marketing' => [
            'name' => 'Marketing',
            'description' => 'Used to track visitors across websites to display relevant advertisements.',
            'required' => false,
        ],
        'personalization' => [
            'name' => 'Personalization',
            'description' => 'Allow the website to remember choices you make and provide enhanced, personalized features.',
            'required' => false,
        ],
    ];

    // =========================================================================
    // License Settings
    // =========================================================================

    /**
     * License key.
     */
    public string $licenseKey = '';

    // =========================================================================
    // Environment Variable Support (US-028)
    // =========================================================================

    /**
     * Map of properties to environment variable names.
     */
    private const ENV_MAP = [
        'enabled' => 'CONSENTPRO_ENABLED',
        'geoEnabled' => 'CONSENTPRO_GEO_ENABLED',
        'policyUrl' => 'CONSENTPRO_POLICY_URL',
    ];

    /**
     * Get setting value with environment variable override.
     *
     * @param string $property The property name.
     * @return mixed The resolved value (env var takes precedence).
     */
    public function getEnvValue(string $property): mixed
    {
        if (!isset(self::ENV_MAP[$property])) {
            return $this->$property ?? null;
        }

        $envValue = App::env(self::ENV_MAP[$property]);

        if ($envValue === null || $envValue === '') {
            return $this->$property ?? null;
        }

        // Handle boolean conversion for boolean fields
        if (in_array($property, ['enabled', 'geoEnabled'], true)) {
            return filter_var($envValue, FILTER_VALIDATE_BOOLEAN);
        }

        return $envValue;
    }

    /**
     * Check if a setting is overridden by environment variable.
     *
     * @param string $property The property name.
     * @return bool Whether the property is overridden by env var.
     */
    public function isEnvOverride(string $property): bool
    {
        if (!isset(self::ENV_MAP[$property])) {
            return false;
        }

        $envValue = App::env(self::ENV_MAP[$property]);

        return $envValue !== null && $envValue !== '';
    }

    /**
     * Get the environment variable name for a property.
     *
     * @param string $property The property name.
     * @return string|null The env var name or null if not mapped.
     */
    public function getEnvVarName(string $property): ?string
    {
        return self::ENV_MAP[$property] ?? null;
    }

    // =========================================================================
    // Validation Rules
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        return [
            // General settings
            [['enabled', 'geoEnabled'], 'boolean'],
            [['policyUrl'], 'url', 'defaultScheme' => 'https'],

            // Color validation
            [
                ['colorPrimary', 'colorSecondary', 'colorBackground', 'colorText'],
                'match',
                'pattern' => '/^#[0-9A-Fa-f]{6}$/',
                'message' => Craft::t('consentpro', '{attribute} must be a valid hex color (e.g., #2563eb).'),
            ],

            // Text field character limits
            [['textHeading'], 'string', 'max' => 100],
            [['textAccept', 'textReject', 'textSettings', 'textSave'], 'string', 'max' => 30],

            // Categories validation
            [['categories'], 'validateCategories'],

            // License key
            [['licenseKey'], 'string', 'max' => 255],
        ];
    }

    /**
     * Custom validator for categories array.
     *
     * @param string $attribute The attribute being validated.
     */
    public function validateCategories(string $attribute): void
    {
        $allowedKeys = ['essential', 'analytics', 'marketing', 'personalization'];

        if (!is_array($this->$attribute)) {
            $this->addError($attribute, Craft::t('consentpro', 'Categories must be an array.'));
            return;
        }

        foreach ($this->$attribute as $key => $category) {
            // Validate key
            if (!in_array($key, $allowedKeys, true)) {
                $this->addError(
                    $attribute,
                    Craft::t('consentpro', 'Invalid category key: {key}', ['key' => $key])
                );
                continue;
            }

            // Validate name exists
            if (!isset($category['name']) || !is_string($category['name']) || trim($category['name']) === '') {
                $this->addError(
                    $attribute,
                    Craft::t('consentpro', 'Category "{key}" must have a name.', ['key' => $key])
                );
            }

            // Validate description exists
            if (!isset($category['description']) || !is_string($category['description'])) {
                $this->addError(
                    $attribute,
                    Craft::t('consentpro', 'Category "{key}" must have a description.', ['key' => $key])
                );
            }

            // Ensure essential is always required
            if ($key === 'essential' && (!isset($category['required']) || $category['required'] !== true)) {
                // Auto-fix: essential must always be required
                $this->categories[$key]['required'] = true;
            }
        }
    }
}
