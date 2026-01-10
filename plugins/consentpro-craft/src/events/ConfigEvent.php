<?php
/**
 * Config Event
 *
 * @link      https://consentpro.io
 * @copyright Copyright (c) ConsentPro Team
 */

namespace consentpro\consentpro\events;

use yii\base\Event;

/**
 * Config event for modifying banner configuration before render.
 *
 * Example usage:
 * ```php
 * use consentpro\consentpro\services\ConsentService;
 * use consentpro\consentpro\events\ConfigEvent;
 * use yii\base\Event;
 *
 * Event::on(
 *     ConsentService::class,
 *     ConsentService::EVENT_BEFORE_RENDER,
 *     function(ConfigEvent $event) {
 *         // Add custom config value
 *         $event->config['customField'] = 'value';
 *
 *         // Modify policy URL
 *         $event->config['policyUrl'] = '/custom-privacy-policy';
 *     }
 * );
 * ```
 */
class ConfigEvent extends Event
{
    /**
     * @var array The banner configuration array.
     */
    public array $config = [];
}
