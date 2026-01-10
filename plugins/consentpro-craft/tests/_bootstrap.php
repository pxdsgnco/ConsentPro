<?php
/**
 * Codeception bootstrap file for ConsentPro Craft plugin tests.
 */

define('YII_ENV', 'test');

// Use the existing Craft installation's autoloader
// This assumes the plugin is installed in a Craft project for testing
$craftPath = getenv('CRAFT_PATH') ?: dirname(__DIR__, 4);

if (file_exists($craftPath . '/vendor/autoload.php')) {
    require $craftPath . '/vendor/autoload.php';
}
