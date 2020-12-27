<?php declare(strict_types=1);

namespace App\Logic;

use App\Database\Model\Entry as EntryModel;
use App\Database\Model\User;
use App\Database\Repository\EntryRepository;
use App\Decorator\EntriesDecorator;
use App\Logic\Helper\EntryHelper;
use App\Utility\Registry;

class EntryLogic
{
    private EntryRepository $repository;
    private EntryHelper $helper;

    public function __construct()
    {
        /** @var EntryRepository $repository */
        $repository = Registry::get(EntryRepository::class);

        $this->repository = $repository;
        $this->helper     = new EntryHelper();
    }

    /**
     * @return EntryModel[]
     */
    public function getAllEntriesForUser(User $user): array
    {
        return $this->repository->findByUser($user);
    }

    /**
     * Gets entries for a user based on the provided filter parameters
     *
     * @return EntriesDecorator
     */
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

    public function getEntryForUser(int $entryId, int $userId): EntryModel
    {
        /** @var EntryModel $entry */
        $entry = $this->repository->getById($entryId);
        $this->helper->ensureEntryIsNotNull($entry, $entryId);
        $this->helper->ensureUserOwnsEntry($entry, $userId);

        return $entry;
    }

    /**
     * Removes an existing entry for user
     *
     * @return void
     */
    public function deleteEntry(int $entryId, int $userId): void
    {
        $entry = $this->getEntryForUser($entryId, $userId);

        // queue entry to be removed
        $this->repository->remove($entry);

        // executed queued tasks
        $this->repository->save();
    }

    /**
     * Count the total existing for a user
     *
     * @return int
     */
    public function getEntryCountForUser(User $user): int
    {
        $entries = $this->repository->findByUser($user);
        
        return count($entries);
    }

    /**
     * Get category specific entries for a user
     *
     * @return EntryModel[]
     */
    public function getEntiresForUserByCategoryId(int $userId, int $categoryId): array
    {
        return $this->repository->findByUserIdAndCategoryId($userId, $categoryId);
    }
}
