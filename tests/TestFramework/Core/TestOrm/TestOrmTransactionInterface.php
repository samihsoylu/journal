<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm;

interface TestOrmTransactionInterface
{
    public function beginTransaction(): void;

    public function rollback(): void;
}