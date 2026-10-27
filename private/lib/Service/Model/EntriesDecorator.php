<?php

declare(strict_types=1);

namespace App\Service\Model;

use App\Database\Model\Entry;

final readonly class EntriesDecorator
{
    public function __construct(
        /** @var Entry[] */
        private array $entries,
        private int $totalPages,
        private int $currentPage,
    ) {}

    /**
     * @return Entry[]
     */
    public function getEntries() : array
    {
        return $this->entries;
    }

    public function getTotalPages() : int
    {
        return $this->totalPages;
    }

    public function getCurrentPage() : int
    {
        return $this->currentPage;
    }

    public function getPaginationFilterUri() : string
    {
        $filterUrl = str_replace('&page=' . $this->currentPage, '', $_SERVER['REQUEST_URI']);

        // if no filters are currently being used
        if ( ! str_contains($filterUrl, '?')) {
            // /entires becomes /entries? for /entires?page=1
            return $filterUrl . '?';
        }

        // for /entries?something=1&page=1
        return $filterUrl . '&';
    }

    public function getPreviousPageUrl() : string
    {
        $previousPage = $this->currentPage - 1;

        return sprintf('%spage=%d', $this->getPaginationFilterUri(), $previousPage);
    }

    public function getNextPageUrl() : string
    {
        $nextPage = $this->currentPage + 1;

        return sprintf('%spage=%d', $this->getPaginationFilterUri(), $nextPage);
    }
}
