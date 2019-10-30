<?php

use App\Utilities\Config;

require(__DIR__ . '/../vendor/autoload.php');

/*
 * Scan for all config files
 */
$configDirectory = __DIR__ . '/conf/';
$configFiles = glob($configDirectory . '*.json');

/*
 * Load all config files and generate constants
 */
foreach ($configFiles as $filePath) {
    if (strpos($filePath, 'example')) {
        // Ignore default example files
        continue;
    }

    // Load config file and create constants from json
    Config::initialise($filePath);

    // @todo: think about how to efficiently handle errors
}

