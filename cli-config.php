<?php

declare(strict_types=1);

use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;

require_once __DIR__ . '/private/init.php';

/** @var App\Framework\Kernel $kernel */
$executable = basename($_SERVER['argv'][0] ?? '');

// Return DependencyFactory for doctrine-migrations
if ($executable === 'doctrine-migrations') {
    return $kernel->get(DependencyFactory::class);
}

// Run ORM console for other Doctrine tools
ConsoleRunner::run(
    new SingleManagerProvider(
        $kernel->get(EntityManagerInterface::class),
    ),
);
