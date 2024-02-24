<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub;

use Ramsey\Uuid\UuidInterface;
use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Domain\Repository\UserRepositoryInterface;

final readonly class StubUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private ?UuidInterface $queueForSavingWillSetId = null,
        private ?User $getByIdWillReturn = null,
    ) {}

    public function queueForSaving(object $entity): static
    {
        if ($this->queueForSavingWillSetId instanceof UuidInterface) {
            $reflector = new \ReflectionObject($entity);
            $idProperty = $reflector->getProperty('id');
            $idProperty->setAccessible(true);
            $idProperty->setValue($entity, $this->queueForSavingWillSetId);
            $idProperty->setAccessible(false);
        }

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
        return $this->getByIdWillReturn;
    }
}
