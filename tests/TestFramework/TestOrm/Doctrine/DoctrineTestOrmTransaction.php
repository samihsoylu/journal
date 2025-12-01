<?php

declare(strict_types=1);

namespace Tests\TestFramework\TestOrm\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Tests\TestFramework\TestOrm\TestOrmTransactionInterface;

/**
 * Doctrine implementation of TestOrmTransactionInterface.
 *
 * Manages database transactions for test isolation:
 * - Enables nested transactions with savepoints
 * - Provides rollback capability to restore database state
 */
final readonly class DoctrineTestOrmTransaction implements TestOrmTransactionInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function beginTransaction() : void
    {
        // Enable savepoints for nested transactions
        $this->entityManager->getConnection()->setNestTransactionsWithSavepoints(true);
        $this->entityManager->beginTransaction();
    }

    public function rollback() : void
    {
        $this->entityManager->rollback();

        // Clear the entity manager to ensure clean state
        $this->entityManager->clear();
    }
}
