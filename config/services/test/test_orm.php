<?php

declare(strict_types=1);

use DI\Container;
use Doctrine\ORM\EntityManagerInterface;
use SamihSoylu\Journal\Infrastructure\Adapter\Action\Synchronous\SynchronousActionDispatcher;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionDispatcherInterface;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\Doctrine\DoctrineTestOrm;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\Doctrine\DoctrineTestOrmTransaction;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\TestOrmInterface;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\TestOrmTransactionInterface;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestPath;

return function (Container $container) {
    $container->set(TestOrmInterface::class, function (Container $container) {
        return $container->get(DoctrineTestOrm::class);
    });

    $container->set(TestOrmTransactionInterface::class, function(Container $container) {
        return $container->get(DoctrineTestOrmTransaction::class);
    });
};