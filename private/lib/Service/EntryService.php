<?php

namespace App\Service;

use App\Database\Model\Entry;
use App\Database\Repository\EntryRepository;
use App\Exception\UserException\NotFoundException;
use App\Utility\Registry;
use App\Utility\UserSession;
use Doctrine\ORM\ORMException;

class EntryService
{
    protected EntryRepository $entryRepository;
    protected CategoryService $categoryService;

    public function __construct()
    {
        $this->entryRepository = Registry::get(EntryRepository::class);
        $this->categoryService = Registry::get(CategoryService::class);
    }

    public function getAllEntriesForUser(): ?array
    {
        return $this->entryRepository->findByUser(UserSession::getUserObject());
    }

    public function getAllEntriesForUserFromFilter(
        ?string $search,
        ?int $categoryId,
        ?int $startCreatedDate,
        ?int $endCreatedDate,
        ?int $page = 1,
        ?int $pageSize = 25
    ): array
    {
        $session = UserSession::load();

        if ($page < 1) {
            $page = 1;
        }
        $index = $page - 1;
        $offset = $index * $pageSize;

        $entries = $this->entryRepository->getEntriesBySearchQueryLimitCategoryStartEndDateAndOffset(
            $session->getUserId(),
            $search,
            $categoryId,
            $startCreatedDate,
            $endCreatedDate,
            $offset,
            $pageSize
        );

        $totalEntriesCount = $this->entryRepository->getTotalCountOfEntriesBySearchQueryLimitCategoryStartEndDateAndOffset(
            $session->getUserId(),
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

    public function getUriForPageFilter($page): string
    {
        $filterUrl = str_replace("&page={$page}", '', $_SERVER['REQUEST_URI']);

        // if no filters are currently being used
        if (strpos($filterUrl, '?') === false) {
            // /entires becomes /entries? for /entires?page=1
            return "{$filterUrl}?";
        }

        // for /entries?something=1&page=1
        return "{$filterUrl}&";
    }

    public function createEntry(int $categoryId, string $title, string $content): int
    {
        $category = $this->categoryService->getCategoryById($categoryId);

        $entry = new Entry();
        $entry->setReferencedCategory($category)
            ->setReferencedUser(UserSession::getUserObject())
            ->setTitle($title)
            ->setContent($content);

        $this->entryRepository->queue($entry);
        $this->entryRepository->save();

        return $entry->getId();
    }

    public function updateEntry(int $entryId, int $categoryId, string $entryTitle, string $entryContent): void
    {
        $category = $this->categoryService->getCategoryById($categoryId);

        $entry = $this->getEntryById($entryId);
        $entry->setReferencedCategory($category)
              ->setTitle($entryTitle)
              ->setContent($entryContent);

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
        $this->ensureUserOwnsEntry($entry);

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
        /** @var Entry $entry */
        $entry = $this->getEntryById($entryId);

        $this->entryRepository->remove($entry);
        $this->entryRepository->save();
    }

    public function ensureUserOwnsEntry(Entry $entry): void
    {
        $session = UserSession::load();

        if ($entry->getReferencedUser()->getId() !== $session->getUserId()) {
            // Found entry does not belong to the logged in user
            throw NotFoundException::entityIdNotFound('Entry', $entry->getId());
        }
    }
}
