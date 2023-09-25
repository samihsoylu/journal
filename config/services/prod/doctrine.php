<?php

declare(strict_types=1);

use DI\Container;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManagerInterface;
use SamihSoylu\Journal\Framework\Environment;
use SamihSoylu\Journal\Infrastructure\Adapter\Orm\Doctrine\DoctrineOrmFactory;

return function (Container $container) {
    $container->set(DoctrineOrmFactory::class, function () {
        return new DoctrineOrmFactory(
            $_ENV['JOURNAL_DB_DSN'],
            $_ENV['JOURNAL_ROOT_DIR'],
            $_ENV['JOURNAL_DB_CACHE_DIR'],
            $_ENV['JOURNAL_DB_ENTITY_DIR'],
            $_ENV['JOURNAL_DB_PROXY_DIR'],
            $_ENV['JOURNAL_ENABLE_DEBUG'],
        );
    });

    $container->set(DependencyFactory::class, function (Container $container) {
        $factory = $container->get(DoctrineOrmFactory::class);

        return $factory->create();
    });

    $container->set(EntityManagerInterface::class, function (Container $container) {
        $factory = $container->get(DependencyFactory::class);

        return $factory->getEntityManager();
    });
};