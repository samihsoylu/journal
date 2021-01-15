<?php

namespace App\Service;

use App\Database\Model\Category as CategoryModel;
use App\Database\Repository\CategoryRepository;
use App\Exception\UserException\InvalidArgumentException;
use App\Service\Helper\CategoryHelper;
use App\Service\Helper\EntryHelper;
use App\Service\Helper\UserHelper;
use App\Utility\Registry;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class CategoryService
{
    private CategoryRepository $repository;
    private CategoryHelper $categoryHelper;
    private UserHelper $userHelper;
    private EntryHelper $entryHelper;

    public function __construct()
    {
        /** @var CategoryRepository $repository */
        $repository = Registry::get(CategoryRepository::class);
        $this->repository    = $repository;

        $this->categoryHelper = new CategoryHelper();
        $this->userHelper     = new UserHelper();
        $this->entryHelper    = new EntryHelper();
    }

    /**
     * @return CategoryModel[]
     */
    public function getAllCategoriesForUser(int $userId): array
    {
        $user = $this->userHelper->getUserById($userId);

        return $this->categoryHelper->getAllCategoriesForUser($user);
    }

    public function getCategoryForUser(int $categoryId, int $userId): CategoryModel
    {
        return $this->categoryHelper->getCategoryForUser($categoryId, $userId);
    }

    public function createCategory(int $userId, string $categoryTitle, string $categoryDescription): void
    {
        $user = $this->userHelper->getUserById($userId);

        $category = new CategoryModel();
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
        $category = $this->categoryHelper->getCategoryForUser($categoryId, $userId);

        // get associated entries and queue for deleting
        $entries = $this->entryHelper->getEntiresForUserByCategoryId($userId, $categoryId);
        foreach ($entries as $entry) {
            $this->repository->remove($entry);
        }

        // queue category for deleting
        $this->repository->remove($category);

        // delete queued entries and categories
        $this->repository->save();
    }
}
