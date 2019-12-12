<?php

// Project constants
define('BASE_PATH', realpath(__DIR__ . '/../'));
define('MODEL_PATH', BASE_PATH . '/private/lib/Database/Model/');

// Composer dependencies
require(BASE_PATH . '/vendor/autoload.php');

// Load environmental variables
$dotenv = Dotenv\Dotenv::create(BASE_PATH);
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_SCHEMA', 'DB_USERNAME', 'DB_PASSWORD', 'DEV_MODE']);
$dotenv->required('DEV_MODE')->isBoolean();
