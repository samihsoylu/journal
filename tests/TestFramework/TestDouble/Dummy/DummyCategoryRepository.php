<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy;

use SamihSoylu\Journal\Domain\Entity\Category;
use SamihSoylu\Journal\Domain\Repository\CategoryRepositoryInterface;

final class DummyCategoryRepository implements CategoryRepositoryInterface
{
    public function getById(string $id): ?Category
    {
        return null;
    }

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

    public function getByName(string $userId, string $name): ?Category
    {
        return null;
    }
}
