<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Repository;

use SamihSoylu\Journal\Domain\Entity\Category;
use SamihSoylu\Journal\Domain\Entity\User;

interface CategoryRepositoryInterface extends SaveableInterface
{
    /**
     * @return Category[]
     */
    public function findByUser(User $user): array;

    /**
     * @return Category[]
     */
    public function findByUserAndCategoryName(User $user, string $categoryName): array;
}