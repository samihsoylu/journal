<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\Core;

use SamihSoylu\Journal\Tests\TestFramework\Core\TestSetupHelper\SetupCreateUserActionHandler;

final readonly class TestSetupHelper
{
    public function __construct(
        private SetupCreateUserActionHandler $setupCreateUserActionHandler,
    ) {}

    public function createUserActionHandler(): SetupCreateUserActionHandler
    {
        return $this->setupCreateUserActionHandler;
    }
}
