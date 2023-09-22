<?php

namespace SamihSoylu\Journal\Domain\Repository\Doctrine;

use Doctrine\ORM\EntityRepository;
use SamihSoylu\Journal\Domain\Entity\Category;
use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Domain\Repository\CategoryRepositoryInterface;
use SamihSoylu\Journal\Domain\Repository\Doctrine\Trait\Saveable;

final class CategoryRepository extends EntityRepository implements CategoryRepositoryInterface
{
    use Saveable;

    public function getAll(): array
    {
        // TODO: Implement getAll() method.
    }

    public function getById(string $id): ?Category
    {
        // TODO: Implement getById() method.
    }

    public function findByUser(User $user): array
    {
        // TODO: Implement findByUser() method.
    }

    public function findByUserAndCategoryName(User $user, string $categoryName): array
    {
        // TODO: Implement findByUserAndCategoryName() method.
    }
}
