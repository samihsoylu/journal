<?php

declare(strict_types=1);

namespace Tests\TestFramework\TestOrm;

/**
 * Interface for transaction management in tests.
 *
 * Provides transaction control to enable test isolation:
 * - Begin transaction at test start
 * - Rollback at test end to restore database state
 */
interface TestOrmTransactionInterface
{
    /**
     * Begin a database transaction.
     */
    public function beginTransaction() : void;

    /**
     * Rollback the current transaction.
     */
    public function rollback() : void;
}
