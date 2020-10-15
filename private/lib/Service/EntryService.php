<?php

namespace App\Service;

use App\Database\Model\Category;
use App\Database\Model\Entry;
use App\Database\Repository\CategoryRepository;
use App\Database\Repository\EntryRepository;
use App\Exception\UserException\InvalidOperationException;
use App\Exception\UserException\NotFoundException;
use App\Utility\Sanitize;
use App\Utility\UserSession;
use Doctrine\ORM\ORMException;

class EntryService
{
    protected EntryRepository $entryRepository;
    protected CategoryRepository $categoryRepository;
    protected CategoryService $categoryService;

    public function __construct()
    {
        $this->entryRepository = new EntryRepository();
        $this->categoryService = new CategoryService();
        $this->categoryRepository = new CategoryRepository();
    }

    public function getAllEntriesForUser(): ?array
    {
        return $this->entryRepository->findByUser(UserSession::getUserObject());
    }

    public function getAllEntriesForUserFromFilter(
        ?string $search,
        ?int $limit,
        ?int $categoryId,
        ?int $startCreatedDate,
        ?int $endCreatedDate,
        ?int $offset
    ): ?array
    {
        $session = UserSession::load();

        if ($categoryId !== null) {
            $category = $this->categoryRepository->getById($categoryId);
            $this->categoryService->ensureUserOwnsCategory($category);
        }

        return $this->entryRepository->getEntriesBySearchQueryLimitCategoryStartEndDateAndOffset(
            $session->getUserId(),
            $search,
            $categoryId,
            $startCreatedDate,
            $endCreatedDate,
            $offset,
            $limit
        );
    }

    public function createEntry(int $categoryId, string $title, string $content): int
    {
        $category = $this->categoryRepository->getById($categoryId);

        /** @var Category $category */
        $this->categoryService->ensureUserOwnsCategory($category);

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
        /** @var Category $category */
        $category = $this->categoryRepository->getById($categoryId);
        $this->categoryService->ensureUserOwnsCategory($category);

        /** @var Entry $entry */
        $entry = $this->entryRepository->getById($entryId);
        $this->ensureUserOwnsEntry($entry);

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
    public function findEntryById(int $entryId): Entry
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
        $entry = $this->entryRepository->getById($entryId);
        $this->ensureUserOwnsEntry($entry);

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
