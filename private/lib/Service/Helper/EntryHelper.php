<?php declare(strict_types=1);

namespace App\Service\Helper;

use App\Database\Model\Entry;
use App\Database\Model\User;
use App\Database\Repository\EntryRepository;
use App\Exception\UserException\NotFoundException;
use App\Utility\Registry;

class EntryHelper
{
    private EntryRepository $repository;

    public function __construct()
    {
        /** @var EntryRepository $repository */
        $repository = Registry::get(EntryRepository::class);

        $this->repository = $repository;
    }

    /**
     * @return Entry[]
     */
    public function getAllEntriesForUser(User $user): array
    {
        return $this->repository->findByUser($user);
    }

    public function getEntryForUser(int $entryId, int $userId): Entry
    {
        /** @var Entry $entry */
        $entry = $this->repository->getById($entryId);
        $this->ensureEntryIsNotNull($entry, $entryId);
        $this->ensureUserOwnsEntry($entry, $userId);

        return $entry;
    }

    /**
     * @return int
     */
    public function getEntryCountForUser(User $user): int
    {
        $entries = $this->repository->findByUser($user);

        return count($entries);
    }

    public function getEntryCountForCategory(int $userId, int $categoryId): int
    {
        $entries = $this->getEntriesForUserByCategory($userId, $categoryId);

        return count($entries);
    }

    /**
     * @return Entry[]
     */
    public function getEntriesForUserByCategory(int $userId, int $categoryId): array
    {
        return $this->repository->findByUserIdAndCategoryId($userId, $categoryId);
    }

    private function ensureUserOwnsEntry(Entry $entry, int $userId): void
    {
        if ($entry->getReferencedUser()->getId() !== $userId) {
            // Found entry does not belong to the logged in user
            throw NotFoundException::entityIdNotFound(Entry::getClassName(), $entry->getId());
        }
    }

    private function ensureEntryIsNotNull(?Entry $entry, $entryId): void
    {
        if ($entry === null) {
            throw NotFoundException::entityIdNotFound(Entry::getClassName(), $entryId);
        }
    }
}
