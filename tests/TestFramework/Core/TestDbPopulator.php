<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\Core;

use SamihSoylu\Journal\Tests\TestFramework\Seed\UserSeed;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\TestOrmInterface;

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