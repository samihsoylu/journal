<?php

$exampleConfigFile = __DIR__  . '/.env.example';
$configFile = __DIR__ . '/.env';
if (!file_exists($configFile)) {
    copy($exampleConfigFile, $configFile);
}

require_once(__DIR__ . '/private/init.php');