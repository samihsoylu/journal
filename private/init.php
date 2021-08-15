<?php

// Default error settings
ini_set('display_errors', 1);
error_reporting(E_ALL);

// BASE_PATH = Parent directory
define('BASE_PATH', dirname(__DIR__));

$pathToAutoLoader = BASE_PATH . '/vendor/autoload.php';
if (!file_exists($pathToAutoLoader)) {
    echo "The composer autoloader can not be found, did you forget to run 'composer install --no-dev'?";
    exit(1);
}

// Composer autoloader
require($pathToAutoLoader);

// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();
$dotenv->required(['SITE_TITLE', 'DB_HOST', 'DB_SCHEMA', 'DB_USERNAME', 'DB_PASSWORD', 'BASE_URL', 'DEBUG_MODE', 'ADMIN_EMAIL_ADDRESS']);
$dotenv->required('DEBUG_MODE')->isBoolean();

define('BASE_URL', rtrim($_ENV['BASE_URL'], '/'));
define('DEBUG_MODE', ($_ENV['DEBUG_MODE'] === 'true'));
define('SSL_IS_ENABLED', ($_ENV['USE_SSL'] === 'true'));
define('SITE_TITLE', $_ENV['SITE_TITLE']);
define('ADMIN_EMAIL_ADDRESS', $_ENV['ADMIN_EMAIL_ADDRESS']);

if (!DEBUG_MODE) {
    ini_set('display_errors', 0);
    error_reporting(0);
}

if (SSL_IS_ENABLED) {
    // **PREVENTING SESSION HIJACKING**
    // Prevents javascript XSS attacks aimed to steal the session ID
    ini_set('session.cookie_httponly', 1);

    // **PREVENTING SESSION FIXATION**
    // Session ID cannot be passed through URLs
    ini_set('session.use_only_cookies', 1);

    // Uses a secure connection (HTTPS) if possible
    ini_set('session.cookie_secure', 1);
}

// Project constants
const MODEL_PATH                  = BASE_PATH . '/private/lib/Database/Model/';
const TEMPLATE_PATH               = BASE_PATH . '/private/templates/';
const TEMPLATE_CACHE_PATH         = BASE_PATH . '/private/cache/templates/';
const SESSION_CACHE_PATH          = BASE_PATH . '/private/cache/sessions/';
const DATABASE_CACHE_PATH         = BASE_PATH . '/private/cache/database';
const ASSETS_URL                  = BASE_URL  . '/assets';
const DEFAULT_CACHE_EXPIRY_TIME   = 3600;  // 1 hour
const DEFAULT_SESSION_EXPIRY_TIME = 86400; // 24 hours

const PROJECT_VERSION = '1.2.3';

// Prevents warnings from popping up when using this init file through the CLI
if (headers_sent()) {
    echo "Headers were sent, session_start() was not invoked.\n";
} else {
    session_start();
}
