<?php

declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('log_errors', 0);
error_reporting(E_ALL);

date_default_timezone_set('UTC');

$_ENV['JOURNAL_PROJECT_NAME'] = 'Journal';
$_ENV['JOURNAL_VERSION'] = 'v2.0.0';

$_ENV['JOURNAL_ROOT_DIR'] = dirname(__DIR__);
$_ENV['JOURNAL_CONFIG_DIR'] = $_ENV['JOURNAL_ROOT_DIR'] . '/config';
$_ENV['JOURNAL_APPLICATION_DIR'] = $_ENV['JOURNAL_ROOT_DIR'] . '/src/Application';

$_ENV['JOURNAL_TEST_DOUBLE_DIR_PATH'] = $_ENV['JOURNAL_ROOT_DIR'] . '/tests/TestFramework/TestDouble';

$_ENV['JOURNAL_DB_ENTITY_DIR'] = $_ENV['JOURNAL_ROOT_DIR'] . '/src/Domain/Entity';
$_ENV['JOURNAL_DB_PROXY_DIR'] = $_ENV['JOURNAL_ROOT_DIR'] . '/var/doctrine/proxy';
$_ENV['JOURNAL_DB_CACHE_DIR'] = $_ENV['JOURNAL_ROOT_DIR'] . '/var/doctrine';

$_ENV['JOURNAL_CONTROLLER_DIR'] = $_ENV['JOURNAL_ROOT_DIR'] . '/src/Presentation/Controller';

$_ENV['CONSOLE_COMMAND_DIR'] = $_ENV['JOURNAL_ROOT_DIR'] . '/src/Presentation/Console';
$_ENV['CONSOLE_COMMAND_NAMESPACE'] = 'SamihSoylu\\Journal\\Presentation\\Console\\';


$autoloader = $_ENV['JOURNAL_ROOT_DIR'] . '/vendor/autoload.php';
if (!file_exists($autoloader)) {
    throw new \RuntimeException("Please run composer install --no-dev");
}

require $autoloader;


$dotenv = Dotenv\Dotenv::createImmutable($_ENV['JOURNAL_ROOT_DIR']);
$dotenv->load();
$dotenv->required(['JOURNAL_ENV', 'JOURNAL_DB_DSN']);
$dotenv->ifPresent('JOURNAL_ENABLE_DEBUG')->isBoolean();


$_ENV['JOURNAL_ENABLE_DEBUG'] = boolval($_ENV['JOURNAL_ENABLE_DEBUG'] ?? false);
if (!$_ENV['JOURNAL_ENABLE_DEBUG']) {
    ini_set('display_errors', 0);
    error_reporting(0);
}