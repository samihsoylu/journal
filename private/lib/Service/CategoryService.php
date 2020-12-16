<?php

namespace App\Service;

use App\Database\Model\Category;
use App\Database\Model\User;
use App\Database\Repository\EntryRepository;
use App\Database\Repository\CategoryRepository;
use App\Exception\UserException\InvalidArgumentException;
use App\Service\Helpers\CategoryHelper;
use App\Utility\Registry;
use App\Utility\UserSession;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class CategoryService
{
    protected CategoryRepository $categoryRepository;

    protected EntryRepository $entryRepository;

    private CategoryHelper $helper;

    public function __construct()
    {
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = Registry::get(CategoryRepository::class);

        /** @var EntryRepository $entryRepository */
        $entryRepository = Registry::get(EntryRepository::class);

        /** @var CategoryHelper $helper */
        $helper = Registry::get(CategoryHelper::class);

        $this->categoryRepository = $categoryRepository;
        $this->entryRepository    = $entryRepository;
        $this->helper             = $helper;
    }

    public function getCategoryById(int $categoryId): Category
    {
        /** @var Category $category */
        $category = $this->categoryRepository->getById($categoryId);
        $this->helper->ensureCategoryIsNotNull($category, $categoryId);
        $this->helper->ensureUserOwnsCategory($category);

        return $category;
    }

    /**
     * @param User $user
     * @return Category[]
     */
    public function getAllUserCategories(User $user): array
    {
        return $this->categoryRepository->findByUser($user);
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
        $category = $this->getCategoryById($categoryId);

        $category->setName($categoryName);
        $category->setDescription($categoryDescription);

        $this->categoryRepository->queue($category);
        $this->categoryRepository->save();
    }

    public function deleteCategoryAndAssociatedEntries(int $categoryId): void
    {
        $category = $this->getCategoryById($categoryId);

        // get associated entries and delete them
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

    public function getCategoryCountForUser(User $user): int
    {
        $categories = $this->getAllUserCategories($user);

        return count($categories);
    }
}
