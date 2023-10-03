<?php

declare(strict_types=1);

use Doctrine\Migrations\DependencyFactory;
use SamihSoylu\Journal\Framework\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;

require_once __DIR__ . '/bootstrap.php';

$kernel = Kernel::boot();

$executable = $_SERVER['argv'][0];

if (str_contains((string) $executable, 'doctrine-migrations')) {
    return $kernel->container->get(DependencyFactory::class);
}

ConsoleRunner::run(
    new SingleManagerProvider(
        $kernel->container->get(EntityManagerInterface::class)
    )
);
