<?php

// This is a configuration file for the doctrine-migrations cli

use App\Database\Database;

require_once __DIR__ . '/private/init.php';

return Database::getInstance();
