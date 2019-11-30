<?php

use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require(__DIR__ . '/../vendor/autoload.php');

define('BASE_PATH', __DIR__ . '/../');

// Loads .env file
$dotenv = Dotenv\Dotenv::create(BASE_PATH);
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_SCHEMA', 'DB_USERNAME', 'DB_PASSWORD', 'DEV_MODE']);

// Instantiate doctrine
$dbParams = [
    'driver'   => 'pdo_mysql',
    'host'     => $_ENV['DB_HOST'],
    'user'     => $_ENV['DB_USERNAME'],
    'password' => $_ENV['DB_PASSWORD'],
    'dbname'   => $_ENV['DB_SCHEMA'],
];

try {
    $config = Setup::createAnnotationMetadataConfiguration([__DIR__ . '/models/'], $_ENV['DEV_MODE']);
    $entityManager = EntityManager::create($dbParams, $config);
} catch (ORMException $e) {
    echo $e->getMessage();
    exit();
}
