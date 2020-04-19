<?php

session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// BASE_PATH = Parent directory
define('BASE_PATH', dirname(__DIR__));

// Composer autoloader
require(BASE_PATH . '/vendor/autoload.php');

// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_SCHEMA', 'DB_USERNAME', 'DB_PASSWORD', 'BASE_URL', 'DEBUG_MODE']);
$dotenv->required('DEBUG_MODE')->isBoolean();

define('BASE_URL', rtrim($_ENV['BASE_URL'], '/'));
define('DEBUG_MODE', ($_ENV['DEBUG_MODE'] === 'true'));

if (!DEBUG_MODE) {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Project constants
const MODEL_PATH          = BASE_PATH . '/private/lib/Database/Model/';
const TEMPLATE_PATH       = BASE_PATH . '/private/templates/';
const TEMPLATE_CACHE_PATH = BASE_PATH . '/private/cache/templates/';
const ASSETS_URL          = BASE_URL . '/assets';

