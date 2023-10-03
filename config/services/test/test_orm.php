<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\Doctrine\DoctrineTestOrm;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\Doctrine\DoctrineTestOrmTransaction;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\TestOrmInterface;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\TestOrmTransactionInterface;

return function (Container $container): void {
    $container->set(TestOrmInterface::class, fn (Container $container) => $container->get(DoctrineTestOrm::class));

    $container->set(TestOrmTransactionInterface::class, fn (Container $container) => $container->get(DoctrineTestOrmTransaction::class));
};
