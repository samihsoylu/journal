<?php

declare(strict_types=1);

// BASE_PATH = Parent directory
define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

// Load test environment from .env.test if it exists
$envTestPath = BASE_PATH . '/.env.test';

if (file_exists($envTestPath)) {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH, '.env.test');
    $dotenv->load();
}

// Set test environment
$_ENV['APP_ENV'] = 'test';

// Project constants needed by tests
const MODEL_PATH = BASE_PATH . '/private/lib/Database/Model/';
const TEMPLATE_PATH = BASE_PATH . '/private/templates/';
const CACHE_PATH = BASE_PATH . '/private/cache/';
const TEMPLATE_CACHE_PATH = BASE_PATH . '/private/cache/templates/';
const SESSION_CACHE_PATH = BASE_PATH . '/private/cache/sessions/';
const DATABASE_CACHE_PATH = BASE_PATH . '/private/cache/database';
const EXPORT_CACHE_PATH = BASE_PATH . '/private/cache/export';
const SCRIPTS_PATH = BASE_PATH . '/private/scripts';
const DEFAULT_CACHE_EXPIRY_TIME = 3600;
const DEFAULT_SESSION_EXPIRY_TIME = 86400;
