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
     * Outputs banner HTML container, registers CSS/JS assets, and includes
     * the initialization script. This is the recommended method for most users.
     *
     * @return Markup
     */
    public function banner(): Markup
    {
        $settings = ConsentPro::getInstance()->getSettings();

        if (!$settings->enabled) {
            return new Markup('', 'UTF-8');
        }

        // Register asset bundle for JS/CSS
        Craft::$app->getView()->registerAssetBundle(ConsentProAsset::class);

        $config = ConsentPro::getInstance()->consent->getConfig();

        $html = sprintf(
            '<div id="consentpro-banner" class="consentpro" role="dialog" aria-labelledby="consentpro-heading" aria-modal="false" data-config="%s"></div>',
            htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8')
        );

        // Add custom CSS if available (Pro feature)
        $customCss = ConsentPro::getInstance()->consent->getCustomCss();
        if (!empty($customCss)) {
            $html .= sprintf(
                '<style id="consentpro-custom-css">%s</style>',
                htmlspecialchars($customCss, ENT_QUOTES, 'UTF-8')
            );
        }

        // Add inline init script
        $html .= sprintf(
            '<script id="consentpro-init">%s</script>',
            $this->getInitScript()
        );

        return new Markup($html, 'UTF-8');
    }

    /**
     * Output just the script and style tags with initialization.
     *
     * Use this when you want to manually control asset placement.
     * Outputs CSS link, JS script, and initialization script.
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
            '<script src="%s" defer></script>' . "\n" .
            '<script id="consentpro-init">%s</script>',
            htmlspecialchars($cssUrl, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($jsUrl, ENT_QUOTES, 'UTF-8'),
            $this->getInitScript()
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
     * Provides access to license methods in templates:
     * - {{ craft.consentpro.license.isPro() }}
     * - {{ craft.consentpro.license.isEnterprise() }}
     * - {{ craft.consentpro.license.getLicenseData() }}
     * - {{ craft.consentpro.license.getGraceDaysRemaining() }}
     * - {{ craft.consentpro.license.getLastValidated() }}
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

            public function isEnterprise(): bool
            {
                return ConsentPro::getInstance()->license->isEnterprise();
            }

            public function getLicenseData(): array
            {
                return ConsentPro::getInstance()->license->getLicenseData();
            }

            public function getGraceDaysRemaining(): ?int
            {
                return ConsentPro::getInstance()->license->getGraceDaysRemaining();
            }

            public function getLastValidated(): ?int
            {
                return ConsentPro::getInstance()->license->getLastValidated();
            }
        };
    }

    /**
     * Get the inline initialization script.
     *
     * This script initializes the ConsentPro banner, manager, and script blocker
     * after the main JS file loads. It handles DOMContentLoaded timing and
     * exposes global helper methods.
     *
     * @return string
     */
    private function getInitScript(): string
    {
        return <<<'JS'
(function(){
'use strict';
function initConsentPro(){
if(typeof ConsentPro==='undefined'){return;}
var config=ConsentPro.GeoDetector.parseConfigFromDOM('#consentpro-banner');
if(!config){return;}
if(!ConsentPro.GeoDetector.shouldShowBanner(config)){return;}
var manager=new ConsentPro.ConsentManager();
var banner=new ConsentPro.BannerUI(manager);
var blocker=new ConsentPro.ScriptBlocker();
banner.init('consentpro-banner',config);
var consent=manager.getConsent();
if(consent&&manager.isConsentValid()){
blocker.init(consent.categories);
banner.renderFooterToggle();
}else{
banner.show();
blocker.init({essential:true,analytics:false,marketing:false,personalization:false});
}
window.ConsentPro.manager=manager;
window.ConsentPro.show=function(){banner.show();};
window.ConsentPro.getConsent=function(){return manager.getConsent();};
}
if(document.readyState==='loading'){
document.addEventListener('DOMContentLoaded',initConsentPro);
}else{
initConsentPro();
}
})();
JS;
    }
}
