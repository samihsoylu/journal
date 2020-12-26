<?php declare(strict_types=1);

namespace App\Logic;

use App\Database\Model\Category as CategoryModel;
use App\Database\Model\User;
use App\Database\Repository\CategoryRepository;
use App\Logic\Helper\CategoryHelper;
use App\Utility\Registry;

class CategoryLogic
{
    private CategoryRepository $repository;
    private CategoryHelper $helper;

    public function __construct()
    {
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = Registry::get(CategoryRepository::class);

        $this->repository   = $categoryRepository;
        $this->helper       = new CategoryHelper();
    }

    public function getCategoryForUser(int $categoryId, $userId): CategoryModel
    {
        /** @var CategoryModel $category */
        $category = $this->repository->getById($categoryId);
        $this->helper->ensureCategoryIsNotNull($category, $categoryId);
        $this->helper->ensureUserOwnsCategory($category, $userId);

        return $category;
    }

    public function updateCategory(int $userId, int $categoryId, string $categoryName, string $categoryDescription): void
    {
        $category = $this->getCategoryForUser($categoryId, $userId);

        $category->setName($categoryName);
        $category->setDescription($categoryDescription);

        $this->repository->queue($category);
        $this->repository->save();
    }

    public function getCategoryCountForUser(User $user): int
    {
        $categories = $this->repository->findByUser($user);

        return count($categories);
    }
}
