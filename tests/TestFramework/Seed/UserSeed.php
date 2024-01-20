<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\Seed;

use SamihSoylu\CipherSuite\PasswordKeyManager\PasswordKeyManagerInterface;
use SamihSoylu\Journal\Domain\Entity\Enum\Role;
use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Infrastructure\Port\PasswordHasher\PasswordHasherInterface;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\TestOrmInterface;

final readonly class UserSeed
{
    public const DEFAULT_PASSWORD = 'unsecure-test-password';
    private User $user;

    public function __construct(
        private TestOrmInterface $testOrm,
        private PasswordHasherInterface $passwordHasher,
        private PasswordKeyManagerInterface $passwordKeyManager,
    ) {
        $this->user = new User();

        $this->setDefaultValues();
    }

    private function setDefaultValues(): void
    {
        $protectedKey = $this->passwordKeyManager->createProtectedKeyForDb(self::DEFAULT_PASSWORD);
        $hashedPassword = $this->passwordHasher->hash(self::DEFAULT_PASSWORD);

        $this->user->setUsername('joe')
            ->setPassword($hashedPassword)
            ->setEmailAddress('joe@example.com')
            ->setProtectedKey($protectedKey)
            ->setPreferredTimezone('Europe/Amsterdam')
            ->setRole(Role::OWNER);
    }

    public function withUsername(string $username): self
    {
        $this->user->setUsername($username);

        return $this;
    }

    public function withPassword(string $password): self
    {
        $this->user->setPassword($password);

        return $this;
    }

    public function withRoles(Role $role): self
    {
        $this->user->setRole($role);

        return $this;
    }

    public function withProtectedKey(string $protectedKey): self
    {
        $this->user->setProtectedKey($protectedKey);

        return $this;
    }

    public function withPreferredTimezone(string $preferredTimezone): self
    {
        $this->user->setPreferredTimezone($preferredTimezone);

        return $this;
    }

    public function save(): User
    {
        $this->testOrm->persist($this->user);

        return $this->user;
    }
}
