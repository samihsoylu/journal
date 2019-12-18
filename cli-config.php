<?php

use App\Database\Database;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once __DIR__ . '/private/init.php';

return ConsoleRunner::createHelperSet(Database::getInstance());