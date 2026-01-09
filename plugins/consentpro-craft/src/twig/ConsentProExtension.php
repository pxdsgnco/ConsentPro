<?php
/**
 * Twig Extension
 *
 * @link      https://consentpro.io
 * @copyright Copyright (c) ConsentPro Team
 */

namespace consentpro\consentpro\twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use consentpro\consentpro\ConsentPro;

/**
 * Twig extension for ConsentPro.
 */
class ConsentProExtension extends AbstractExtension
{
    /**
     * @inheritdoc
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('consentproBanner', [$this, 'renderBanner'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Render the consent banner.
     *
     * @return string
     */
    public function renderBanner(): string
    {
        $settings = ConsentPro::getInstance()->getSettings();

        if (!$settings->enabled) {
            return '';
        }

        $config = ConsentPro::getInstance()->consent->getConfig();

        return sprintf(
            '<div id="consentpro-banner" class="consentpro" role="dialog" aria-labelledby="consentpro-heading" aria-modal="false" data-config="%s"></div>',
            htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8')
        );
    }
}
