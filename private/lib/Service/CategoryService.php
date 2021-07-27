<?php

namespace App\Service;

use App\Database\Model\Category;
use App\Database\Model\User;
use App\Database\Repository\CategoryRepository;
use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\NotFoundException;
use App\Service\Helper\CategoryHelper;
use App\Service\Helper\EntryHelper;
use App\Service\Helper\TemplateHelper;
use App\Service\Helper\UserHelper;
use App\Service\Model\CategoryDecorator;
use App\Utility\Registry;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class CategoryService
{
    private CategoryRepository $repository;
    private CategoryHelper $categoryHelper;
    private UserHelper $userHelper;
    private EntryHelper $entryHelper;
    private TemplateHelper $templateHelper;

    public function __construct()
    {
        /** @var CategoryRepository $repository */
        $repository = Registry::get(CategoryRepository::class);
        $this->repository    = $repository;

        $this->categoryHelper = new CategoryHelper();
        $this->userHelper     = new UserHelper();
        $this->entryHelper    = new EntryHelper();
        $this->templateHelper = new TemplateHelper();
    }

    /**
     * @return Category[]
     * @throws NotFoundException
     */
    public function getAllCategoriesForUser(int $userId): array
    {
        $user = $this->userHelper->getUserById($userId);

        return $this->categoryHelper->getAllCategoriesForUser($user);
    }

    public function getAllCategoriesWithExcludeFilter(int $userId, array $excludeCategoryNames): array
    {
        $user = $this->userHelper->getUserById($userId);
        $categories = $this->categoryHelper->getAllCategoriesForUser($user);

        $filteredCategories = [];
        foreach ($categories as $category) {
            if (!in_array($category->getName(), $excludeCategoryNames, true)) {
                $filteredCategories[] = $category;
            }
        }

        return $filteredCategories;
    }

    public function getCategoryForUser(int $categoryId, int $userId): CategoryDecorator
    {
        $category = $this->categoryHelper->getCategoryForUser($categoryId, $userId);

        $entryCount = $this->entryHelper->getEntryCountForCategory($userId, $categoryId);
        $templateCount = $this->templateHelper->getTemplateCountForCategory($userId, $categoryId);

        return new CategoryDecorator(
            $category->getId(),
            $category->getName(),
            $category->getDescription(),
            $entryCount,
            $templateCount,
        );
    }

    /**
     * @throws InvalidArgumentException|NotFoundException
     */
    public function createCategory(int $userId, string $categoryName, string $categoryDescription, int $order = null): Category
    {
        $user = $this->userHelper->getUserById($userId);

        if ($order === null) {
            $categoryCount = $this->categoryHelper->getCategoryCountForUser($user);
            $order = ++$categoryCount;
        }

        $category = new Category();
        $category->setReferencedUser($user)
                 ->setName($categoryName)
                 ->setDescription($categoryDescription)
                 ->setSortOrder($order);

        $this->repository->queue($category);

        try {
            $this->repository->save();
        } catch (UniqueConstraintViolationException $e) {
            throw InvalidArgumentException::categoryAlreadyExists($categoryName);
        }

        return $category;
    }

    public function updateCategory(int $userId, int $categoryId, string $categoryName, string $categoryDescription): void
    {
        $category = $this->categoryHelper->getCategoryForUser($categoryId, $userId);

        $category->setName($categoryName);
        $category->setDescription($categoryDescription);

        $this->repository->queue($category);
        $this->repository->save();
    }

    public function deleteCategory(int $userId, int $categoryId): void
    {
        $category = $this->categoryHelper->getCategoryForUser($categoryId, $userId);

        $this->moveEntriesAndTemplatesAwayFromCategory($category);

        $this->repository->remove($category);
        $this->repository->save();
    }

    public function moveEntriesAndTemplatesAwayFromCategory(Category $category)
    {
        $user = $category->getReferencedUser();
        $uncategorizedCategory = $this->ensureUnCategorizedCategoryExists($user);

        $templates = $this->templateHelper->getTemplatesForUserByCategory($user->getId(), $category->getId());
        foreach ($templates as $template) {
            $template->setReferencedCategory($uncategorizedCategory);
            $this->repository->queue($template);
        }

        $entries = $this->entryHelper->getEntriesForUserByCategory($user->getId(), $category->getId());
        foreach ($entries as $entry) {
            $entry->setReferencedCategory($uncategorizedCategory);
            $this->repository->queue($entry);
        }

        $this->repository->save();
    }

    public function updateCategoryOrder(int $userId, int $categoryId, int $order): void
    {
        $category = $this->categoryHelper->getCategoryForUser($categoryId, $userId);
        $category->setSortOrder($order);
        $category->save();
    }

    private function ensureUnCategorizedCategoryExists(User $user): Category
    {
        $category = $this->repository->findByCategoryName($user, Category::UNCATEGORIZED_CATEGORY_NAME);
        if ($category === null) {
            $category = new Category();
            $category->setName(Category::UNCATEGORIZED_CATEGORY_NAME)
                ->setDescription(Category::UNCATEGORIZED_CATEGORY_DESCRIPTION)
                ->setReferencedUser($user)
                ->setSortOrder(2147483646)
                ->save();
        }

        return $category;
    }
}
