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

    public function getById(string $id): ?Category
    {
        return $this->find($id);
    }
}
