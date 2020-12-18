<?php

namespace App\Service\Helpers;

use App\Database\Model\Category;
use App\Exception\UserException\NotFoundException;

class CategoryHelper
{
    public function ensureCategoryIsNotNull(?Category $category, int $categoryId): void
    {
        if ($category === null) {
            throw NotFoundException::entityIdNotFound(Category::class, $categoryId);
        }
    }

    public function ensureUserOwnsCategory(Category $category, int $userId): void
    {
        if ($category->getReferencedUser()->getId() !== $userId) {
            // found category does not belong to the logged in user
            throw NotFoundException::entityIdNotFound(Category::class, $category->getId());
        }
    }
}
