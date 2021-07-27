<?php

namespace App\Service;

use App\Database\Model\Category;
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
use Doctrine\ORM\ORMException;

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
        $categoryCount = $this->categoryHelper->getCategoryCountForUser($user);
        $uncategorizedCategory = $this->categoryHelper->getCategoryByUserAndCategoryName($user, Category::UNCATEGORIZED_CATEGORY_NAME);

        //uncategorized category should always be at bottom, increment order by 2 to make space for the new category
        if (!$uncategorizedCategory == null) {
            $uncategorizedCategory->setSortOrder($categoryCount + 2);

            $this->repository->queue($uncategorizedCategory);
        }

        // default order, add newly created category to the bottom of the category list
        if ($order === null) {
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

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     * @throws NotFoundException
     */
    public function deleteCategory(int $userId, int $categoryId): void
    {
        $category = $this->categoryHelper->getCategoryForUser($categoryId, $userId);

        $this->setUncategorizedEntriesAndTemplates($userId, $category);

        // queue category for deleting
        $this->repository->remove($category);

        // delete queued entries, templates and categories
        $this->repository->save();
    }

    /**
     * @throws NotFoundException
     * @throws InvalidArgumentException
     */
    public function setUncategorizedEntriesAndTemplates(int $userId, Category $category)
    {
        $categoryId = $category->getId();
        $user = $this->userHelper->getUserById($userId);

        // Creates uncategorized category if user doesn't have one
        $uncategorizedCategory = $this->categoryHelper->getCategoryByUserAndCategoryName($user, Category::UNCATEGORIZED_CATEGORY_NAME);
        if ($uncategorizedCategory === null) {
            $uncategorizedCategory = $this->createCategory($userId, Category::UNCATEGORIZED_CATEGORY_NAME, Category::UNCATEGORIZED_CATEGORY_DESCRIPTION);

            $this->repository->queue($uncategorizedCategory);
        }

        $templates = $this->templateHelper->getTemplatesForUserByCategory($userId, $categoryId);
        foreach ($templates as $template) {
            $template->setReferencedCategory($uncategorizedCategory);
            $this->repository->queue($template);
        }

        $entries = $this->entryHelper->getEntriesForUserByCategory($userId, $categoryId);
        foreach ($entries as $entry) {
            $entry->setReferencedCategory($uncategorizedCategory);
            $this->repository->queue($entry);
        }
    }

    public function updateCategoryOrder(int $userId, int $categoryId, int $order): void
    {
        $category = $this->categoryHelper->getCategoryForUser($categoryId, $userId);

        $category->setSortOrder($order);

        $this->repository->queue($category);
        $this->repository->save();
    }
}
