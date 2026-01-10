<?php
/**
 * Register Categories Event
 *
 * @link      https://consentpro.io
 * @copyright Copyright (c) ConsentPro Team
 */

namespace consentpro\consentpro\events;

use yii\base\Event;

/**
 * Event for registering custom consent categories.
 *
 * Example usage:
 * ```php
 * use consentpro\consentpro\services\ConsentService;
 * use consentpro\consentpro\events\RegisterCategoriesEvent;
 * use yii\base\Event;
 *
 * Event::on(
 *     ConsentService::class,
 *     ConsentService::EVENT_REGISTER_CATEGORIES,
 *     function(RegisterCategoriesEvent $event) {
 *         // Add a custom category
 *         $event->categories[] = [
 *             'id' => 'social',
 *             'name' => 'Social Media',
 *             'description' => 'Enable social sharing features.',
 *             'required' => false,
 *         ];
 *
 *         // Modify an existing category
 *         foreach ($event->categories as &$category) {
 *             if ($category['id'] === 'analytics') {
 *                 $category['description'] = 'Custom analytics description.';
 *             }
 *         }
 *     }
 * );
 * ```
 */
class RegisterCategoriesEvent extends Event
{
    /**
     * @var array The consent categories array.
     */
    public array $categories = [];
}
