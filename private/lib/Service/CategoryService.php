<?php

namespace App\Service;

use App\Database\Repository\UserRepository;
use App\Exception\UserException\NotFoundException;
use App\Database\Model\Category;
use App\Database\Repository\CategoryRepository;
use App\Exception\UserException;
use App\Utility\UserSession;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;

class CategoryService
{
    protected CategoryRepository $categoryRepository;

    protected UserRepository $userRepository;

    public function __construct()
    {
        $this->categoryRepository = new CategoryRepository();
        $this->userRepository = new UserRepository();
    }

    public function getCategoryById(int $categoryId): Category
    {
        return $this->categoryRepository->getById($categoryId);
    }

    /**
     * @return Category[]
     */
    public function getAllCategoriesForLoggedInUser(): array
    {
        return $this->categoryRepository->getAllCategoriesForUser(UserSession::getUserObject());
    }

    public function createCategory(string $categoryTitle, string $categoryDescription): void
    {
        $category = new Category();
        $category->setReferencedUser(UserSession::getUserObject());
        $category->setName($categoryTitle);
        $category->setDescription($categoryDescription);

        $this->categoryRepository->queue($category);

        try {
            $this->categoryRepository->save();
        } catch (UniqueConstraintViolationException $e) {
            throw new UserException("The category with title '{$categoryTitle}' already exists");
        }
    }

    /**
     * Updates an existing category
     *
     * @param int $id
     * @param string $categoryName
     * @param string $categoryDescription
     * @throws UserException
     */
    public function updateCategory(int $id, string $categoryName, string $categoryDescription): void
    {
        /** @var Category $category */
        $category = $this->categoryRepository->getById($id);
        $this->ensureUserOwnsCategory($category);

        $category->setName($categoryName);
        $category->setDescription($categoryDescription);

        $this->categoryRepository->queue($category);
        $this->categoryRepository->save();
    }

    /**
     * Deletes an existing category
     *
     * @param int $id
     * @throws NotFoundException|TransactionRequiredException|OptimisticLockException|ORMException
     */
    public function deleteCategory(int $id): void
    {
        /** @var Category $category */
        $category = $this->categoryRepository->getById($id);
        $this->ensureUserOwnsCategory($category);

        $this->categoryRepository->remove($category);
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
