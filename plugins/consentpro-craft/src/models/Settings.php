<?php
/**
 * ConsentPro Settings Model
 *
 * @link      https://consentpro.io
 * @copyright Copyright (c) ConsentPro Team
 */

namespace consentpro\consentpro\models;

use craft\base\Model;

/**
 * Settings model for ConsentPro plugin.
 */
class Settings extends Model
{
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

    /**
     * Category definitions.
     */
    public array $categories = [];

    /**
     * License key.
     */
    public string $licenseKey = '';

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        return [
            [['enabled', 'geoEnabled'], 'boolean'],
            [['policyUrl'], 'url', 'defaultScheme' => 'https'],
            [
                ['colorPrimary', 'colorSecondary', 'colorBackground', 'colorText'],
                'match',
                'pattern' => '/^#[0-9A-Fa-f]{6}$/',
            ],
            [['licenseKey'], 'string', 'max' => 255],
        ];
    }
}
