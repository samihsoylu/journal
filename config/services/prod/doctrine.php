<?php

declare(strict_types=1);

use App\Framework\Doctrine\DoctrineOrmFactory;
use DI\Container;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManagerInterface;

return static function (Container $container) : void {
    $container->set(DoctrineOrmFactory::class, static fn () => new DoctrineOrmFactory(
        $_ENV['DB_HOST'],
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        $_ENV['DB_SCHEMA'],
        BASE_PATH,
        DATABASE_CACHE_PATH,
        DATABASE_CACHE_PATH,
        MODEL_PATH,
        DEBUG_MODE,
    ));

    $container->set(DependencyFactory::class, static function (Container $container) {
        $factory = $container->get(DoctrineOrmFactory::class);

        return $factory->createOrm();
    });

    $container->set(EntityManagerInterface::class, static function (Container $container) {
        $factory = $container->get(DependencyFactory::class);

        return $factory->getEntityManager();
    });
};
