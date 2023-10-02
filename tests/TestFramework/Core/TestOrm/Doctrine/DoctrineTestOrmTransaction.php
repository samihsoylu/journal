<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\TestOrmTransactionInterface;

final class DoctrineTestOrmTransaction implements TestOrmTransactionInterface
{
    /** @var Connection[] */
    private static array $connections = [];

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function beginTransaction(): void
    {
        $connection = $this->entityManager->getConnection();

        $connection->setNestTransactionsWithSavepoints(true);
        $connection->beginTransaction();

        self::$connections[] = $connection;
    }

    public function rollback(): void
    {
        foreach (self::$connections as $connection) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
        }
    }
}