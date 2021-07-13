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
     */
    public function getAllCategoriesForUser(int $userId): array
    {
        $user = $this->userHelper->getUserById($userId);

        return $this->categoryHelper->getAllCategoriesForUser($user);
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
    public function createCategory(int $userId, string $categoryName, string $categoryDescription): void
    {
        $user = $this->userHelper->getUserById($userId);
        $categoryCount = $this->categoryHelper->getCategoryCountForUser($user);

        $category = new Category();
        $category->setReferencedUser($user)
                 ->setName($categoryName)
                 ->setDescription($categoryDescription)
                 ->setSortOrder($categoryCount + 1);

        $this->repository->queue($category);

        try {
            $this->repository->save();
        } catch (UniqueConstraintViolationException $e) {
            throw InvalidArgumentException::categoryAlreadyExists($categoryName);
        }
    }

    public function updateCategory(int $userId, int $categoryId, string $categoryName, string $categoryDescription): void
    {
        $category = $this->categoryHelper->getCategoryForUser($categoryId, $userId);

        $category->setName($categoryName);
        $category->setDescription($categoryDescription);

        $this->repository->queue($category);
        $this->repository->save();
    }

    public function deleteCategoryAndAssociatedEntries(int $categoryId, int $userId): void
    {
        $category = $this->categoryHelper->getCategoryForUser($categoryId, $userId);

        // get associated entries and queue for deleting
        $entries = $this->entryHelper->getEntriesForUserByCategory($userId, $categoryId);
        foreach ($entries as $entry) {
            $this->repository->remove($entry);
        }

        // get associated templates and queue for deleting
        $templates = $this->templateHelper->getTemplatesForUserByCategory($userId, $categoryId);
        foreach ($templates as $template) {
            $this->repository->remove($template);
        }

        // queue category for deleting
        $this->repository->remove($category);

        // delete queued entries, templates and categories
        $this->repository->save();
    }

    public function updateCategoryOrder(int $userId, int $categoryId, int $order): void
    {
        $category = $this->categoryHelper->getCategoryForUser($categoryId, $userId);

        $category->setSortOrder($order);

        $this->repository->queue($category);
        $this->repository->save();
    }
}
