<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\Framework\Core;

use SamihSoylu\Journal\Tests\Framework\Seed\UserSeed;
use SamihSoylu\Journal\Tests\Framework\Core\TestOrm\TestOrmInterface;

final class TestDbPopulator
{
    public function __construct(
        private readonly TestOrmInterface $testOrm,
    ) {}

    public function createNewUser(): UserSeed
    {
        return new UserSeed($this->testOrm);
    }
}