<?php

declare(strict_types=1);

use DI\Container;
use Doctrine\ORM\EntityManagerInterface;
use Tests\TestFramework\TestOrm\Doctrine\DoctrineTestOrm;
use Tests\TestFramework\TestOrm\Doctrine\DoctrineTestOrmTransaction;
use Tests\TestFramework\TestOrm\TestOrm;
use Tests\TestFramework\TestOrm\TestOrmTransaction;

/**
 * Test service configuration.
 *
 * Registers TestOrm services for integration tests.
 * Loaded only in 'test' environment.
 */
return static function (Container $container) : void {
    $container->set(TestOrm::class, static fn (Container $c) : TestOrm => new DoctrineTestOrm($c->get(EntityManagerInterface::class)));

    $container->set(TestOrmTransaction::class, static fn (Container $c) : TestOrmTransaction => new DoctrineTestOrmTransaction($c->get(EntityManagerInterface::class)));
};
