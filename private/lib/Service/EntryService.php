<?php

namespace App\Service;

use App\Database\Model\Entry as EntryModel;
use App\Database\Repository\EntryRepository;
use App\Decorator\EntriesDecorator;
use App\Logic\CategoryLogic;
use App\Logic\EntryLogic;
use App\Logic\UserLogic;
use App\Utility\Registry;
use Defuse\Crypto\Key;

class EntryService
{
    private EntryRepository $repository;
    private EntryLogic $entryLogic;
    private CategoryLogic $categoryLogic;
    private UserLogic $userLogic;

    public function __construct()
    {
        /** @var EntryRepository $repository */
        $repository = Registry::get(EntryRepository::class);

        $this->repository    = $repository;
        $this->entryLogic    = new EntryLogic();
        $this->categoryLogic = new CategoryLogic();
        $this->userLogic     = new UserLogic();
    }

    /**
     * Create a new entry for a user
     *
     * @return int Entry id
     */
    public function createEntry(int $userId, Key $encryptionKey, int $categoryId, string $title, string $content): int
    {
        $category = $this->categoryLogic->getCategoryForUser($categoryId, $userId);

        $user = $this->userLogic->getUserById($userId);

        $entry = new EntryModel();
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
        $category = $this->categoryLogic->getCategoryForUser($categoryId, $userId);

        $entry = $this->entryLogic->getEntryForUser($entryId, $userId);
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
        return $this->entryLogic->getAllEntriesForUserFromFilter(
            $userId,
            $search,
            $categoryId,
            $startCreatedDate,
            $endCreatedDate,
            $page,
            $pageSize
        );
    }

    public function getEntryForUser(int $entryId, int $userId): EntryModel
    {
        return $this->entryLogic->getEntryForUser($entryId, $userId);
    }

    /**
     * Removes an existing entry for user
     *
     * @return void
     */
    public function deleteEntry(int $entryId, int $userId): void
    {
        $this->entryLogic->deleteEntry($entryId, $userId);
    }
}
