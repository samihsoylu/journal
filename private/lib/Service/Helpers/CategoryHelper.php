<?php

namespace App\Service\Helpers;

use App\Database\Model\Category;
use App\Exception\UserException\NotFoundException;
use App\Utility\UserSession;

class CategoryHelper
{
    public function ensureCategoryIsNotNull(?Category $category, int $categoryId): void
    {
        if ($category === null) {
            throw NotFoundException::entityIdNotFound(Category::class, $categoryId);
        }
    }

    public function ensureUserOwnsCategory(Category $category): void
    {
        $session = UserSession::load();

        if ($category->getReferencedUser()->getId() !== $session->getUserId()) {
            // found category does not belong to the logged in user
            throw NotFoundException::entityIdNotFound(Category::class, $category->getId());
        }
    }
}