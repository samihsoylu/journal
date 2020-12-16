<?php

namespace App\Service;

use App\Database\Model\Entry;
use App\Database\Model\User;
use App\Database\Repository\EntryRepository;
use App\Exception\UserException\NotFoundException;
use App\Service\Helpers\EntryHelper;
use App\Utility\Registry;
use App\Utility\UserSession;
use Doctrine\ORM\ORMException;

class EntryService
{
    protected EntryRepository $entryRepository;
    protected CategoryService $categoryService;
    protected EntryHelper $helper;

    public function __construct()
    {
        /** @var EntryRepository $entryRepository */
        $entryRepository = Registry::get(EntryRepository::class);

        /** @var CategoryService $categoryService */
        $categoryService = Registry::get(CategoryService::class);

        /** @var EntryHelper $helper */
        $helper = Registry::get(EntryHelper::class);

        $this->entryRepository = $entryRepository;
        $this->categoryService = $categoryService;
        $this->helper          = $helper;
    }

    public function getAllEntriesForUserFromFilter(
        int $userId,
        ?string $search,
        ?int $categoryId,
        ?int $startCreatedDate,
        ?int $endCreatedDate,
        ?int $page = 1,
        ?int $pageSize = 25
    ): array {
        if ($page < 1) {
            $page = 1;
        }
        $index = $page - 1;
        $offset = $index * $pageSize;

        $entries = $this->entryRepository->getEntriesBySearchQueryLimitCategoryStartEndDateAndOffset(
            $userId,
            $search,
            $categoryId,
            $startCreatedDate,
            $endCreatedDate,
            $offset,
            $pageSize
        );

        $totalEntriesCount = $this->entryRepository->getTotalCountOfEntriesBySearchQueryLimitCategoryStartEndDateAndOffset(
            $userId,
            $search,
            $categoryId,
            $startCreatedDate,
            $endCreatedDate
        );

        $totalPages = 1;
        if ($totalEntriesCount > 0) {
            $totalPages = ceil($totalEntriesCount / $pageSize);
        }

        return [$page, $totalPages, $entries];
    }

    public function createEntry(int $categoryId, string $title, string $content): int
    {
        $category = $this->categoryService->getCategoryById($categoryId);

        $entry = new Entry();
        $entry->setReferencedCategory($category)
            ->setReferencedUser(UserSession::getUserObject())
            ->setTitle($title)
            ->setContentAndEncrypt($content);

        $this->entryRepository->queue($entry);
        $this->entryRepository->save();

        return $entry->getId();
    }

    public function updateEntry(int $entryId, int $categoryId, string $title, string $content): void
    {
        $category = $this->categoryService->getCategoryById($categoryId);

        $entry = $this->getEntryById($entryId);
        $entry->setReferencedCategory($category)
              ->setTitle($title)
              ->setContentAndEncrypt($content);

        $this->entryRepository->queue($entry);
        $this->entryRepository->save();
    }

    /**
     * Finds the requested entry by the provided id
     *
     * @param int $entryId
     * @return Entry
     * @throws NotFoundException|ORMException
     */
    public function getEntryById(int $entryId): Entry
    {
        /** @var Entry $entry */
        $entry = $this->entryRepository->getById($entryId);
        $this->helper->ensureUserOwnsEntry($entry);

        return $entry;
    }

    /**
     * Removes an existing entry from the database
     *
     * @param int $entryId
     * @throws NotFoundException|ORMException
     */
    public function deleteEntry(int $entryId): void
    {
        $entry = $this->getEntryById($entryId);

        $this->entryRepository->remove($entry);
        $this->entryRepository->save();
    }

    public function getEntryCountForUser(User $user): int
    {
        $entries = $this->entryRepository->findByUser($user);
        
        return count($entries);
    }

    public function getHelper(): EntryHelper
    {
        return $this->helper;
    }

    public function getCategoryService(): CategoryService
    {
        return $this->categoryService;
    }
}
