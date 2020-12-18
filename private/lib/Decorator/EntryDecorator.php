<?php

namespace App\Decorator;

use App\Database\Model\Entry;
use Defuse\Crypto\Key;

class EntryDecorator
{
    private int $entryId;
    private string $entryTitle;
    private string $categoryName;
    private string $entryContent;
    private string $getLastUpdatedTimestamp;

    public function __construct(Entry $entry, Key $encryptionKey)
    {
        $this->entryId                       = $entry->getId();
        $this->entryTitle                    = $entry->getTitle();
        $this->categoryName                  = $entry->getReferencedCategory()->getName();
        $this->getLastUpdatedTimestamp = $entry->getLastUpdatedTimestampFormatted();

        $this->entryContent = $this->decodeEntryContentAsMarkup($entry, $encryptionKey);
    }

    public function decodeEntryContentAsMarkup(Entry $entry, Key $encryptionKey): string
    {
        return $entry->getContentAsMarkup($encryptionKey);
    }

    public function getEntryId(): int
    {
        return $this->entryId;
    }

    public function getEntryTitle(): string
    {
        return $this->entryTitle;
    }

    public function getCategoryName(): string
    {
        return $this->categoryName;
    }

    public function getEntryContent(): string
    {
        return $this->entryContent;
    }

    public function getGetLastUpdatedTimestamp(): string
    {
        return $this->getLastUpdatedTimestamp;
    }
}