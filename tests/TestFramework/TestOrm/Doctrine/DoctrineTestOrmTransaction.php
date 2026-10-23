<?php

declare(strict_types=1);

namespace Tests\TestFramework\TestOrm\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Tests\TestFramework\TestOrm\TestOrmTransaction;

/**
 * Doctrine implementation of TestOrmTransactionInterface.
 *
 * Manages database transactions for test isolation using DBAL Connection
 * (not EntityManager) because Connection doesn't close on errors.
 */
final class DoctrineTestOrmTransaction implements TestOrmTransaction
{
    /** @var array<Connection> */
    private static array $connections = [];

    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    public function beginTransaction() : void
    {
        $connection = $this->entityManager->getConnection();

        $connection->setNestTransactionsWithSavepoints(true);
        $connection->beginTransaction();

        self::$connections[] = $connection;
    }

    public function rollback() : void
    {
        foreach (self::$connections as $connection) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
        }
    }
}
