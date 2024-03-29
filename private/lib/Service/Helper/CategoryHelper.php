<?php declare(strict_types=1);

namespace App\Service\Helper;

use App\Database\Model\Category;
use App\Database\Model\User;
use App\Database\Repository\CategoryRepository;
use App\Exception\UserException\NotFoundException;
use App\Utility\Registry;

class CategoryHelper
{
    private CategoryRepository $repository;

    public function __construct()
    {
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = Registry::get(CategoryRepository::class);

        $this->repository = $categoryRepository;
    }

    public function getCategoryForUser(int $categoryId, int $userId): Category
    {
        $category = $this->repository->getById($categoryId);
        $this->ensureCategoryIsNotNull($category, $categoryId);
        $this->ensureUserOwnsCategory($category, $userId);

        return $category;
    }

    public function getCategoryByUserAndCategoryName(User $user, string $categoryName): ?Category
    {
        return $this->repository->findByCategoryName($user, $categoryName);
    }

    /**
     * @return Category[]
     */
    public function getAllCategoriesForUser(User $user): array
    {
        return $this->repository->findByUser($user);
    }

    public function getCategoryCountForUser(User $user): int
    {
        $categories = $this->getAllCategoriesForUser($user);

        return count($categories);
    }

    private function ensureCategoryIsNotNull(?Category $category, int $categoryId): void
    {
        if ($category === null) {
            throw NotFoundException::entityIdNotFound(Category::getClassName(), $categoryId);
        }
    }

    private function ensureUserOwnsCategory(Category $category, int $userId): void
    {
        if ($category->getReferencedUser()->getId() !== $userId) {
            // found category does not belong to the logged in user
            throw NotFoundException::entityIdNotFound(Category::getClassName(), $category->getId());
        }
    }
}
