<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy;

use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Domain\Repository\UserRepositoryInterface;

final class DummyUserRepository implements UserRepositoryInterface
{
    public function queueForSaving(object $entity): static
    {
        return $this;
    }

    public function queueForRemoval(object $entity): static
    {
        return $this;
    }

    public function saveChanges(): void
    {
    }

    public function getById(string $id): ?User
    {
        return null;
    }
}
