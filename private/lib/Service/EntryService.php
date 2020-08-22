<?php

namespace App\Service;

use App\Database\Model\Category;
use App\Database\Model\Entry;
use App\Database\Repository\CategoryRepository;
use App\Database\Repository\EntryRepository;
use App\Utility\Sanitizer;
use App\Utility\UserSession;

class EntryService
{
    protected EntryRepository $entryRepository;
    protected CategoryRepository $categoryRepository;
    protected CategoryService $categoryService;

    public function __construct()
    {
        $this->entryRepository = new EntryRepository();
        $this->categoryRepository = new CategoryRepository();
        $this->categoryService = new CategoryService();
    }

    public function getAllEntriesForUser(): ?array
    {
        return $this->entryRepository->findByUser(UserSession::getUserObject());
    }

    public function createEntry(int $categoryId, string $title, string $content): void
    {
        /** @var Category $category */
        $category = $this->categoryRepository->getById($categoryId);
        $this->categoryService->ensureUserOwnsCategory($category);

        $title   = Sanitizer::sanitizeString($title, 'trim|capitalize');
        $content = Sanitizer::sanitizeString($content, 'trim');

        $entry = new Entry();
        $entry->setReferencedCategory($category)
            ->setReferencedUser(UserSession::getUserObject())
            ->setTitle($title)
            ->setContent($content);

        $this->entryRepository->queue($entry);
        $this->entryRepository->save();
    }
}
