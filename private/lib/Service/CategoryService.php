<?php

namespace App\Service;

use App\Database\Model\Category;
use App\Database\Repository\EntryRepository;
use App\Database\Repository\UserRepository;
use App\Database\Repository\CategoryRepository;
use App\Exception\UserException\NotFoundException;
use App\Exception\UserException\InvalidArgumentException;
use App\Utility\UserSession;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class CategoryService
{
    protected CategoryRepository $categoryRepository;

    protected UserRepository $userRepository;

    protected EntryRepository $entryRepository;

    public function __construct()
    {
        $this->categoryRepository = new CategoryRepository();
        $this->userRepository = new UserRepository();
        $this->entryRepository = new EntryRepository();
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
            throw InvalidArgumentException::categoryAlreadyExists($categoryTitle);
        }
    }

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

    public function deleteCategoryAndAssociatedEntries(int $categoryId): void
    {
        /** @var Category $category */
        $category = $this->categoryRepository->getById($categoryId);
        $userId   = $category->getReferencedUser()->getId();

        $this->ensureUserOwnsCategory($category);

        // delete associated entries
        $entries = $this->entryRepository->findByUserIdAndCategoryId($userId, $categoryId);
        foreach ($entries as $entry) {
            $this->entryRepository->remove($entry);
        }
        $this->entryRepository->save();

        // delete category
        $this->categoryRepository->remove($category);
        $this->categoryRepository->save();
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
