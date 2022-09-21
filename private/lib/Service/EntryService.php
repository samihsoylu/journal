<?php declare(strict_types=1);

namespace App\Service;

use App\Database\Model\Entry;
use App\Database\Repository\EntryRepository;
use App\Service\Model\EntriesDecorator;
use App\Service\Helper\CategoryHelper;
use App\Service\Helper\EntryHelper;
use App\Service\Helper\UserHelper;
use App\Service\Model\EntryDecorator;
use App\Utility\Registry;
use Defuse\Crypto\Key;

class EntryService
{
    private EntryRepository $repository;
    private EntryHelper $entryHelper;
    private CategoryHelper $categoryHelper;
    private UserHelper $userHelper;

    public function __construct()
    {
        /** @var EntryRepository $repository */
        $repository = Registry::get(EntryRepository::class);

        $this->repository     = $repository;
        $this->entryHelper    = new EntryHelper();
        $this->categoryHelper = new CategoryHelper();
        $this->userHelper     = new UserHelper();
    }

    /**
     * Create a new entry for a user
     *
     * @return int Entry id
     */
    public function createEntry(int $userId, Key $encryptionKey, int $categoryId, string $title, string $content): int
    {
        $category = $this->categoryHelper->getCategoryForUser($categoryId, $userId);

        $user = $this->userHelper->getUserById($userId);

        $entry = new Entry();
        $entry->setReferencedCategory($category)
            ->setReferencedUser($user)
            ->setTitle($title)
            ->setContentAndEncrypt($content, $encryptionKey);

        $this->repository->queue($entry);
        $this->repository->save();

        return $entry->getId();
    }

    /**
     * Update an entry for a user
     *
     * @return void
     */
    public function updateEntry(int $userId, Key $encryptionKey, int $entryId, int $categoryId, string $title, string $content): void
    {
        $category = $this->categoryHelper->getCategoryForUser($categoryId, $userId);

        $entry = $this->entryHelper->getEntryForUser($entryId, $userId);
        $entry->setReferencedCategory($category)
            ->setTitle($title)
            ->setContentAndEncrypt($content, $encryptionKey);

        $this->repository->queue($entry);
        $this->repository->save();
    }

    public function getAllEntriesForUserFromFilter(
        int $userId,
        ?string $search,
        ?int $categoryId,
        ?int $startCreatedDate,
        ?int $endCreatedDate,
        ?int $page = 1,
        ?int $pageSize = 25
    ): EntriesDecorator {
        if ($page < 1) {
            $page = 1;
        }
        $index  = $page - 1;
        $offset = $index * $pageSize;

        $entries = $this->repository->getEntriesBySearchQueryLimitCategoryStartEndDateAndOffset(
            $userId,
            $search,
            $categoryId,
            $startCreatedDate,
            $endCreatedDate,
            $offset,
            $pageSize
        );

        $totalEntriesCount = $this->repository->getTotalCountOfEntriesBySearchQueryLimitCategoryStartEndDateAndOffset(
            $userId,
            $search,
            $categoryId,
            $startCreatedDate,
            $endCreatedDate
        );

        $totalPages = 1;
        if ($totalEntriesCount > 0) {
            $totalPages = (int)ceil($totalEntriesCount / $pageSize);
        }

        return new EntriesDecorator($entries, $totalPages, $page);
    }

    public function getEntryForUser(int $entryId, int $userId, Key $key): EntryDecorator
    {
        $entry = $this->entryHelper->getEntryForUser($entryId, $userId);

        return new EntryDecorator(
            $entry->getId(),
            $entry->getTitle(),
            $entry->getReferencedCategory()->getId(),
            $entry->getReferencedCategory()->getName(),
            $entry->getContentDecrypted($key),
            $entry->getLastUpdatedTimestampFormatted(),
        );
    }

    /**
     * Removes an existing entry for user
     *
     * @return void
     */
    public function deleteEntry(int $entryId, int $userId): void
    {
        $entry = $this->entryHelper->getEntryForUser($entryId, $userId);

        // queue entry to be removed
        $this->repository->remove($entry);

        // executed queued tasks
        $this->repository->save();
    }
}
