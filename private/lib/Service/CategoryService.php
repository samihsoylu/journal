<?php

namespace App\Service;

use App\Database\Model\Category as CategoryModel;
use App\Database\Repository\CategoryRepository;
use App\Exception\UserException\InvalidArgumentException;
use App\Logic\CategoryLogic;
use App\Logic\UserLogic;
use App\Logic\EntryLogic;
use App\Utility\Registry;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class CategoryService
{
    private CategoryRepository $repository;
    private CategoryLogic $categoryLogic;
    private UserLogic $userLogic;
    private EntryLogic $entryLogic;

    public function __construct()
    {
        /** @var CategoryRepository $repository */
        $repository = Registry::get(CategoryRepository::class);
        $this->repository    = $repository;

        $this->categoryLogic = new CategoryLogic();
        $this->userLogic     = new UserLogic();
        $this->entryLogic    = new EntryLogic();
    }

    /**
     * @return CategoryModel[]
     */
    public function getAllCategoriesForUser(int $userId): array
    {
        $user = $this->userLogic->getUserById($userId);

        return $this->categoryLogic->getAllCategoriesForUser($user);
    }

    public function getCategoryForUser(int $categoryId, int $userId): CategoryModel
    {
        return $this->categoryLogic->getCategoryForUser($categoryId, $userId);
    }

    public function createCategory(int $userId, string $categoryTitle, string $categoryDescription): void
    {
        $user = $this->userLogic->getUserById($userId);

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
        $this->categoryLogic->updateCategory($userId, $categoryId, $categoryName, $categoryDescription);
    }

    public function deleteCategoryAndAssociatedEntries(int $categoryId, int $userId): void
    {
        $category = $this->categoryLogic->getCategoryForUser($categoryId, $userId);

        // get associated entries and queue for deleting
        $entries = $this->entryLogic->getEntiresForUserByCategoryId($userId, $categoryId);
        foreach ($entries as $entry) {
            $this->repository->remove($entry);
        }

        // queue category for deleting
        $this->repository->remove($category);

        // delete queued entries and categories
        $this->repository->save();
    }
}
