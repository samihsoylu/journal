<?php

namespace App\Decorator;

use App\Database\Model\Entry;

class EntriesDecorator
{
    /** @var Entry[] */
    private array $entries;

    private int $totalPages;
    private int $currentPage;

    public function __construct(array $entries, int $totalPages, int $currentPage)
    {
        $this->entries             = $entries;
        $this->totalPages          = $totalPages;
        $this->currentPage         = $currentPage;
    }

    /**
     * @return Entry[]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getPaginationFilterUri(): string
    {
        $filterUrl = str_replace("&page={$this->currentPage}", '', $_SERVER['REQUEST_URI']);

        // if no filters are currently being used
        if (strpos($filterUrl, '?') === false) {
            // /entires becomes /entries? for /entires?page=1
            return "{$filterUrl}?";
        }

        // for /entries?something=1&page=1
        return "{$filterUrl}&";
    }

    public function getPreviousPageUrl(): string
    {
        $previousPage = $this->currentPage - 1;
        return "{$this->getPaginationFilterUri()}page={$previousPage}";
    }

    public function getNextPageUrl(): string
    {
        $nextPage = $this->currentPage + 1;
        return "{$this->getPaginationFilterUri()}page={$nextPage}";
    }
}
