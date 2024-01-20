<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Repository\Doctrine;

use Doctrine\ORM\EntityRepository;
use SamihSoylu\Journal\Domain\Entity\Category;
use SamihSoylu\Journal\Domain\Repository\CategoryRepositoryInterface;
use SamihSoylu\Journal\Domain\Repository\Doctrine\Trait\Saveable;

final class CategoryRepository extends EntityRepository implements CategoryRepositoryInterface
{
    use Saveable;

    public function getById(string $id): ?Category
    {
        return $this->find($id);
    }

    public function getByName(string $userId, string $name): ?Category
    {
        return $this->findOneBy([
            'user' => $userId,
            'name' => $name,
        ]);
    }
}
