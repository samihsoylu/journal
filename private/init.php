<?php

define('BASE_PATH', realpath(__DIR__ . '/../'));

// Composer dependencies
require(BASE_PATH . '/vendor/autoload.php');

// Load environmental variables
$dotenv = Dotenv\Dotenv::create(BASE_PATH);
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_SCHEMA', 'DB_USERNAME', 'DB_PASSWORD', 'DEV_MODE']);
$dotenv->required('DEV_MODE')->isBoolean();
