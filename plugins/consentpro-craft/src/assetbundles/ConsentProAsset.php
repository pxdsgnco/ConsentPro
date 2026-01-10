<?php
/**
 * ConsentPro Asset Bundle
 *
 * @link      https://consentpro.io
 * @copyright Copyright (c) ConsentPro Team
 */

namespace consentpro\consentpro\assetbundles;

use craft\web\AssetBundle;

/**
 * Asset bundle for ConsentPro frontend assets.
 *
 * Registers the consent banner JavaScript and CSS files.
 * Assets are automatically versioned using file hash by Craft.
 */
class ConsentProAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        // Path to source assets (relative to plugin src/)
        $this->sourcePath = '@consentpro/consentpro/resources';

        $this->css = [
            'consentpro.min.css',
        ];

        $this->js = [
            'consentpro.min.js',
        ];

        $this->jsOptions = [
            'defer' => true,
        ];

        parent::init();
    }
}
