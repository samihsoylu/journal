<?php

use App\Database\Database;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once __DIR__ . '/private/init.php';

$provider = Database::getInstance();
$entityManager = $provider->getEntityManager();

return ConsoleRunner::createHelperSet($entityManager);