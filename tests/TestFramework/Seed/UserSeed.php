<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\Seed;

use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\TestOrmInterface;
use Symfony\Component\Uid\Uuid;

final class UserSeed
{
    private User $user;

    public function __construct(
        private readonly TestOrmInterface $testOrm,
    ) {
        $this->user = new User();

        $this->setDefaultValues();
    }

    private function setDefaultValues(): void
    {
        $this->user->setUsername('')
            ->setPassword('')
            ->setRoles([])
            ->setEncryptionKey('')
            ->setPreferredTimezone('');
    }

    public function withId(Uuid $id): self
    {
        $this->user->setId($id);

        return $this;
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

    public function withRoles(array $roles): self
    {
        $this->user->setRoles($roles);

        return $this;
    }

    public function withEncryptionKey(string $encryptionKey): self
    {
        $this->user->setProtectedKey($encryptionKey);

        return $this;
    }

    public function withPreferredTimezone(string $preferredTimezone): self
    {
        $this->user->setPreferredTimezone($preferredTimezone);

        return $this;
    }

    public function save(): void
    {
        $this->testOrm->persist($this->user);
    }
}