<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Service\Contract;

use SamihSoylu\Journal\Domain\Entity\Enum\Role;

interface UserServiceInterface
{
    public function createUser(
        string $username,
        #[\SensitiveParameter]
        string $password,
        string $emailAddress,
        Role $role,
    ): void;
}
