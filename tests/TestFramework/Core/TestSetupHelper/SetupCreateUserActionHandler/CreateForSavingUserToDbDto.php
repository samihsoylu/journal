<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\Core\TestSetupHelper\SetupCreateUserActionHandler;

use SamihSoylu\Journal\Domain\Entity\Enum\Role;

final readonly class CreateForSavingUserToDbDto
{
    public function __construct(
        public string $expectedUsername,
        public string $expectedEmail,
        public Role $expectedRole,
        public string $expectedProtectedKeyForDb,
        public string $expectedHashedPassword,
    ) {}
}
