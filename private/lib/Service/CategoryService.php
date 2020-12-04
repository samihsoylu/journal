<?php

namespace App\Service;

use App\Database\Model\Category;
use App\Database\Repository\EntryRepository;
use App\Database\Repository\CategoryRepository;
use App\Exception\UserException\NotFoundException;
use App\Exception\UserException\InvalidArgumentException;
use App\Utility\Registry;
use App\Utility\UserSession;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class CategoryService
{
    protected CategoryRepository $categoryRepository;

    protected EntryRepository $entryRepository;

    public function __construct()
    {
        $this->categoryRepository = Registry::get(CategoryRepository::class);
        $this->entryRepository    = Registry::get(EntryRepository::class);
    }

    public function getCategoryById(int $categoryId): Category
    {
        /** @var Category $category */
        $category = $this->categoryRepository->getById($categoryId);
        $this->ensureCategoryIsNotNull($category, $categoryId);
        $this->ensureUserOwnsCategory($category);

        return $category;
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

    public function updateCategory(int $categoryId, string $categoryName, string $categoryDescription): void
    {
        /** @var Category $category */
        $category = $this->getCategoryById($categoryId);

        $category->setName($categoryName);
        $category->setDescription($categoryDescription);

        $this->categoryRepository->queue($category);
        $this->categoryRepository->save();
    }

    public function deleteCategoryAndAssociatedEntries(int $categoryId): void
    {
        /** @var Category $category */
        $category = $this->getCategoryById($categoryId);

        // delete associated entries
        $entries = $this->entryRepository->findByUserIdAndCategoryId(
            $category->getReferencedUser()->getId(),
            $categoryId
        );
        foreach ($entries as $entry) {
            $this->entryRepository->remove($entry);
        }
        $this->entryRepository->save();

        // delete category
        $this->categoryRepository->remove($category);
        $this->categoryRepository->save();
    }

    private function ensureCategoryIsNotNull(?Category $category, int $categoryId): void
    {
        if ($category === null) {
            throw NotFoundException::entityIdNotFound(Category::class, $categoryId);
        }
    }

    private function ensureUserOwnsCategory(Category $category): void
    {
        $session = UserSession::load();

        if ($category->getReferencedUser()->getId() !== $session->getUserId()) {
            // found category does not belong to the logged in user
            throw NotFoundException::entityIdNotFound(Category::class, $category->getId());
        }
    }
}
