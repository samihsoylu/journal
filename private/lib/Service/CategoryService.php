<?php

namespace App\Service;

use App\Database\Model\Category;
use App\Database\Model\User;
use App\Database\Repository\CategoryRepository;
use App\Exception\UserException\InvalidArgumentException;
use App\Service\Helpers\CategoryHelper;
use App\Utility\Registry;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class CategoryService
{
    private CategoryRepository $repository;
    private CategoryHelper $helper;
    private UserService $userService;
    private EntryService $entryService;

    public function __construct()
    {
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = Registry::get(CategoryRepository::class);

        $this->repository   = $categoryRepository;
        $this->helper       = new CategoryHelper();
        $this->userService  = new UserService();
        $this->entryService = new EntryService();
    }

    public function getCategoryForUser(int $categoryId, $userId): Category
    {
        /** @var Category $category */
        $category = $this->repository->getById($categoryId);
        $this->helper->ensureCategoryIsNotNull($category, $categoryId);
        $this->helper->ensureUserOwnsCategory($category, $userId);

        return $category;
    }

    /**
     * @return Category[]
     */
    public function getAllCategoriesForUser(int $userId): array
    {
        $user = $this->userService->getUserById($userId);

        return $this->repository->findByUser($user);
    }

    public function createCategory(int $userId, string $categoryTitle, string $categoryDescription): void
    {
        $user = $this->userService->getUserById($userId);

        $category = new Category();
        $category->setReferencedUser($user);
        $category->setName($categoryTitle);
        $category->setDescription($categoryDescription);

        $this->repository->queue($category);

        try {
            $this->repository->save();
        } catch (UniqueConstraintViolationException $e) {
            throw InvalidArgumentException::categoryAlreadyExists($categoryTitle);
        }
    }

    public function updateCategory(int $userId, int $categoryId, string $categoryName, string $categoryDescription): void
    {
        $category = $this->getCategoryForUser($categoryId, $userId);

        $category->setName($categoryName);
        $category->setDescription($categoryDescription);

        $this->repository->queue($category);
        $this->repository->save();
    }

    public function deleteCategoryAndAssociatedEntries(int $categoryId, int $userId): void
    {
        $category = $this->getCategoryForUser($categoryId, $userId);

        // get associated entries and delete them
        $entries = $this->entryService->getEntiresForUserByCategoryId($userId, $categoryId);
        foreach ($entries as $entry) {
            $this->repository->remove($entry);
        }

        // delete category
        $this->repository->remove($category);
        $this->repository->save();
    }

    public function getCategoryCountForUser(User $user): int
    {
        $categories = $this->repository->findByUser($user);

        return count($categories);
    }
}
