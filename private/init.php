<?php
// Developer debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Project constants
define('BASE_PATH', dirname(__DIR__));
define('MODEL_PATH', BASE_PATH . '/private/lib/Database/Model/');
define('TEMPLATE_PATH', BASE_PATH . '/private/templates/');

// Composer dependencies
require(BASE_PATH . '/vendor/autoload.php');

// Load environmental variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_SCHEMA', 'DB_USERNAME', 'DB_PASSWORD', 'BASE_URL']);
$dotenv->required('DEV_MODE')->isBoolean();

define('BASE_URL', rtrim($_ENV['BASE_URL'], '/'));
define('ASSETS_URL', BASE_URL . '/assets');