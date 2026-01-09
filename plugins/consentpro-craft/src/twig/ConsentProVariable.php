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
use Twig\Markup;

/**
 * Template variable for ConsentPro.
 *
 * Usage in Twig:
 * - {{ craft.consentpro.banner() }}
 * - {% do craft.consentpro.autoInject() %}
 * - {{ craft.consentpro.license.isPro() }}
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
     * Auto-inject banner assets.
     *
     * Call this in templates to automatically inject CSS and JS.
     */
    public function autoInject(): void
    {
        $settings = ConsentPro::getInstance()->getSettings();

        if (!$settings->enabled) {
            return;
        }

        $view = Craft::$app->getView();

        // Register CSS
        $view->registerCssFile('@web/cpresources/consentpro/consentpro.min.css', [
            'position' => View::POS_HEAD,
        ]);

        // Register JS
        $view->registerJsFile('@web/cpresources/consentpro/consentpro.min.js', [
            'position' => View::POS_END,
            'defer' => true,
        ]);
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
