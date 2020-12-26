<?php declare(strict_types=1);

namespace App\Decorator;

class EntryDecorator
{
    private int $entryId;
    private string $entryTitle;
    private int $categoryId;
    private string $categoryName;
    private string $entryContent;
    private string $getLastUpdatedTimestamp;

    public function __construct(
        int $entryId,
        string $entryTitle,
        int $categoryId,
        string $categoryName,
        string $entryContent,
        string $getLastUpdatedTimestamp
    ) {
        $this->entryId                 = $entryId;
        $this->entryTitle              = $entryTitle;
        $this->categoryId              = $categoryId;
        $this->categoryName            = $categoryName;
        $this->entryContent            = $entryContent;
        $this->getLastUpdatedTimestamp = $getLastUpdatedTimestamp;
    }

    public function getEntryId(): int
    {
        return $this->entryId;
    }

    public function getEntryTitle(): string
    {
        return $this->entryTitle;
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function getCategoryName(): string
    {
        return $this->categoryName;
    }

    public function getEntryContent(): string
    {
        return $this->entryContent;
    }

    public function getLastUpdatedTimestamp(): string
    {
        return $this->getLastUpdatedTimestamp;
    }
}
