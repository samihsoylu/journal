<?php

declare(strict_types=1);

use DI\Container;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManagerInterface;
use SamihSoylu\Journal\Domain\Entity\Category;
use SamihSoylu\Journal\Domain\Entity\Entry;
use SamihSoylu\Journal\Domain\Entity\Template;
use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Domain\Repository\CategoryRepositoryInterface;
use SamihSoylu\Journal\Domain\Repository\EntryRepositoryInterface;
use SamihSoylu\Journal\Domain\Repository\TemplateRepositoryInterface;
use SamihSoylu\Journal\Domain\Repository\UserRepositoryInterface;
use SamihSoylu\Journal\Framework\Doctrine\DoctrineOrmFactory;

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

    $container->set(CategoryRepositoryInterface::class, function (Container $container) {
        $entityManager = $container->get(EntityManagerInterface::class);

        return $entityManager->getRepository(Category::class);
    });

    $container->set(EntryRepositoryInterface::class, function (Container $container) {
        $entityManager = $container->get(EntityManagerInterface::class);

        return $entityManager->getRepository(Entry::class);
    });

    $container->set(TemplateRepositoryInterface::class, function (Container $container) {
        $entityManager = $container->get(EntityManagerInterface::class);

        return $entityManager->getRepository(Template::class);
    });

    $container->set(UserRepositoryInterface::class, function (Container $container) {
        $entityManager = $container->get(EntityManagerInterface::class);

        return $entityManager->getRepository(User::class);
    });
};