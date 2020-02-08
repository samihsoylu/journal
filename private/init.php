<?php

// Project constants
define('BASE_URL', '/');
define('BASE_PATH', realpath(__DIR__ . '/../'));
define('MODEL_PATH', BASE_PATH . '/private/lib/Database/Model/');
define('TEMPLATE_PATH', BASE_PATH . '/private/templates/');
define('ASSETS_URL', BASE_URL . '/assets/');

// Composer dependencies
require(BASE_PATH . '/vendor/autoload.php');

// Load environmental variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_SCHEMA', 'DB_USERNAME', 'DB_PASSWORD']);
$dotenv->required('DEV_MODE')->isBoolean();