<?php

declare(strict_types=1);

use DI\Container;
use Doctrine\ORM\EntityManagerInterface;
use Tests\TestFramework\TestOrm\Doctrine\DoctrineTestOrm;
use Tests\TestFramework\TestOrm\Doctrine\DoctrineTestOrmTransaction;
use Tests\TestFramework\TestOrm\TestOrmInterface;
use Tests\TestFramework\TestOrm\TestOrmTransactionInterface;

/**
 * Test service configuration.
 *
 * Registers TestOrm services for integration tests.
 * Loaded only in 'test' environment.
 */
return static function (Container $container) : void {
    $container->set(TestOrmInterface::class, static fn (Container $c) : TestOrmInterface => new DoctrineTestOrm($c->get(EntityManagerInterface::class)));

    $container->set(TestOrmTransactionInterface::class, static fn (Container $c) : TestOrmTransactionInterface => new DoctrineTestOrmTransaction($c->get(EntityManagerInterface::class)));
};
