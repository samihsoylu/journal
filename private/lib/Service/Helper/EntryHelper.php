<?php declare(strict_types=1);

namespace App\Service\Helper;

use App\Database\Model\Entry;
use App\Database\Model\Entry as EntryModel;
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
     * @return EntryModel[]
     */
    public function getAllEntriesForUser(User $user): array
    {
        return $this->repository->findByUser($user);
    }

    public function getEntryForUser(int $entryId, int $userId): EntryModel
    {
        /** @var EntryModel $entry */
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

    /**
     * @return EntryModel[]
     */
    public function getEntiresForUserByCategoryId(int $userId, int $categoryId): array
    {
        return $this->repository->findByUserIdAndCategoryId($userId, $categoryId);
    }

    private function ensureUserOwnsEntry(Entry $entry, int $userId): void
    {
        if ($entry->getReferencedUser()->getId() !== $userId) {
            // Found entry does not belong to the logged in user
            throw NotFoundException::entityIdNotFound('Entry', $entry->getId());
        }
    }

    private function ensureEntryIsNotNull(?Entry $entry, $entryId): void
    {
        if ($entry === null) {
            throw NotFoundException::entityIdNotFound('Entry', $entryId);
        }
    }
}
