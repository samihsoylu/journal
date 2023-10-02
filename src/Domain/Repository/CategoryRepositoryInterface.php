<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Repository;

use SamihSoylu\Journal\Domain\Entity\Category;
use SamihSoylu\Journal\Domain\Repository\Interface\SaveableInterface;

interface CategoryRepositoryInterface extends SaveableInterface
{
    public function getById(string $id): ?Category;
}