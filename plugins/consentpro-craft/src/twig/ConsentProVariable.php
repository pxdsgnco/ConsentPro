<?php
/**
 * Template Variable
 *
 * @link      https://consentpro.io
 * @copyright Copyright (c) ConsentPro Team
 */

namespace consentpro\consentpro\twig;

use Craft;
use craft\web\View;
use consentpro\consentpro\ConsentPro;
use consentpro\consentpro\assetbundles\ConsentProAsset;
use Twig\Markup;

/**
 * Template variable for ConsentPro.
 *
 * Usage in Twig:
 * - {{ craft.consentpro.banner() }} - Outputs banner HTML
 * - {{ craft.consentpro.scripts() }} - Outputs CSS/JS tags only
 * - {% do craft.consentpro.autoInject() %} - Registers assets via AssetBundle
 * - {{ craft.consentpro.license.isPro() }} - Check Pro license status
 */
class ConsentProVariable
{
    /**
     * Render the consent banner.
     *
     * @return Markup
     */
    public function banner(): Markup
    {
        $settings = ConsentPro::getInstance()->getSettings();

        if (!$settings->enabled) {
            return new Markup('', 'UTF-8');
        }

        $config = ConsentPro::getInstance()->consent->getConfig();

        $html = sprintf(
            '<div id="consentpro-banner" class="consentpro" role="dialog" aria-labelledby="consentpro-heading" aria-modal="false" data-config="%s"></div>',
            htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8')
        );

        return new Markup($html, 'UTF-8');
    }

    /**
     * Output just the script and style tags.
     *
     * Use this when you want to manually control asset placement
     * without using the full banner() output.
     *
     * @return Markup
     */
    public function scripts(): Markup
    {
        $settings = ConsentPro::getInstance()->getSettings();

        if (!$settings->enabled) {
            return new Markup('', 'UTF-8');
        }

        $view = Craft::$app->getView();

        // Register the asset bundle to get published URLs
        $bundle = $view->registerAssetBundle(ConsentProAsset::class);

        // Build the HTML tags
        $cssUrl = $bundle->baseUrl . '/consentpro.min.css';
        $jsUrl = $bundle->baseUrl . '/consentpro.min.js';

        $html = sprintf(
            '<link rel="stylesheet" href="%s">' . "\n" .
            '<script src="%s" defer></script>',
            htmlspecialchars($cssUrl, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($jsUrl, ENT_QUOTES, 'UTF-8')
        );

        return new Markup($html, 'UTF-8');
    }

    /**
     * Auto-inject banner assets via AssetBundle.
     *
     * Call this in templates to automatically register CSS and JS
     * through Craft's asset pipeline with proper versioning.
     */
    public function autoInject(): void
    {
        $settings = ConsentPro::getInstance()->getSettings();

        if (!$settings->enabled) {
            return;
        }

        // Register the asset bundle
        Craft::$app->getView()->registerAssetBundle(ConsentProAsset::class);
    }

    /**
     * Get license helper.
     *
     * @return object
     */
    public function getLicense(): object
    {
        return new class {
            public function isPro(): bool
            {
                return ConsentPro::getInstance()->license->isPro();
            }
        };
    }
}
