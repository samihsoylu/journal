<?php

use App\Utilities\JSONFile;

require(__DIR__ . '/../vendor/autoload.php');

/**
 * Load all settings
 */
JSONFile::initialise(__DIR__ . '/conf/env.json');