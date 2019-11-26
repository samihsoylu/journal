<?php

use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use App\Exceptions\InvalidConfigFileException;
use App\Utilities\Config;

require(__DIR__ . '/../vendor/autoload.php');

/*
 * Step 1: Scan for all config files
 */
$configDirectory = __DIR__ . '/conf/';
$configFiles = glob($configDirectory . '*.json');

/*
 * Step 2: Load all config files and generate constants
 */
foreach ($configFiles as $filePath) {
    if (strpos($filePath, 'example')) {
        // Ignore default example files
        continue;
    }

    try {
        // Load config file and create constants from json
        Config::initialise($filePath);
    } catch (Exception $e) {
        echo $e->getMessage();
        exit();
    }
}

/*
 * Step 3: Check if all required constants are loaded
 */
try {
    Config::ensureConstantsAreDefined([
        'ENVIRONMENT',
        'BASE_URL',
        'DATABASE_HOST',
        'DATABASE_USERNAME',
        'DATABASE_PASSWORD',
        'DATABASE_SCHEMA',
        'DATABASE_CHARSET',
    ]);
} catch (InvalidConfigFileException $e) {
    echo $e->getMessage();
    exit();
}

/*
 * Step 4: Instantiate Doctrine
 */
$isDevMode = (ENVIRONMENT === 'staging') ? true : false;
$pathToModels = [__DIR__ . '/models/'];

$dbParams = array(
    'driver'   => 'pdo_mysql',
    'user'     => DATABASE_USERNAME,
    'password' => DATABASE_PASSWORD,
    'dbname'   => DATABASE_SCHEMA,
);

try {
    $config = Setup::createAnnotationMetadataConfiguration($pathToModels, $isDevMode);
    $entityManager = EntityManager::create($dbParams, $config);
} catch (ORMException $e) {
    echo $e->getMessage();
    exit();
}
