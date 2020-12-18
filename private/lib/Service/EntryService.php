<?php

namespace App\Service;

use App\Database\Model\Entry;
use App\Database\Model\User;
use App\Database\Repository\EntryRepository;
use App\Service\Helpers\EntryHelper;
use App\Utility\Registry;
use Defuse\Crypto\Key;

class EntryService
{
    private EntryRepository $repository;
    private EntryHelper $helper;
    private CategoryService $categoryService;
    private UserService $userService;

    public function __construct()
    {
        /** @var EntryRepository $repository */
        $repository = Registry::get(EntryRepository::class);

        $this->repository      = $repository;
        $this->helper          = new EntryHelper();
        $this->categoryService = new CategoryService();
        $this->userService     = new UserService();
    }

    /**
     * Gets entries for a user based on the provided filter parameters
     *
     * @return array
     */
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
            $totalPages = ceil($totalEntriesCount / $pageSize);
        }

        return [
            'entries'     => $entries,
            'totalPages'  => $totalPages,
            'currentPage' => $page,
            'filterUrl'   => $this->helper->getUriForPageFilter($page),
        ];
    }

    /**
     * Create a new entry for a user
     *
     * @return int Entry id
     */
    public function createEntry(int $userId, Key $encryptionKey, int $categoryId, string $title, string $content): int
    {
        $category = $this->categoryService->getCategoryForUser($categoryId, $userId);

        $user = $this->userService->getUserById($userId);

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
        $category = $this->categoryService->getCategoryForUser($categoryId, $userId);

        $entry = $this->getEntryForUser($entryId, $userId);
        $entry->setReferencedCategory($category)
              ->setTitle($title)
              ->setContentAndEncrypt($content, $encryptionKey);

        $this->repository->queue($entry);
        $this->repository->save();
    }

    public function getEntryForUser(int $entryId, int $userId): Entry
    {
        /** @var Entry $entry */
        $entry = $this->repository->getById($entryId);
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

        $this->repository->remove($entry);
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
     * @return Entry[]
     */
    public function getEntiresForUserByCategoryId(int $userId, int $categoryId): array
    {
        return $this->repository->findByUserIdAndCategoryId($userId, $categoryId);
    }
}
