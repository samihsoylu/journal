<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy;

use PHPUnit\Framework\Assert;
use SamihSoylu\Journal\Application\Service\Contract\UserServiceInterface;
use SamihSoylu\Journal\Domain\Entity\Enum\Role;

final class SpyUserService extends Assert implements UserServiceInterface
{
    /** @var array<string> */
    private array $invokedMethods = [];

    public function createUser(
        string $username,
        #[\SensitiveParameter]
        string $password,
        string $emailAddress,
        Role $role,
    ): void {
        $this->invokedMethods[] = __FUNCTION__;
    }

    public function assertMethodInvoked(string $methodName): void
    {
        self::assertContains($methodName, $this->invokedMethods, "Method '{$methodName}' was not invoked");
    }
}
