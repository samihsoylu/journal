<?php declare(strict_types=1);

namespace App\Logic\Helper;

use App\Database\Model\Entry;
use App\Exception\UserException\NotFoundException;

class EntryHelper
{
    public function ensureUserOwnsEntry(Entry $entry, int $userId): void
    {
        if ($entry->getReferencedUser()->getId() !== $userId) {
            // Found entry does not belong to the logged in user
            throw NotFoundException::entityIdNotFound('Entry', $entry->getId());
        }
    }

    public function ensureEntryIsNotNull(?Entry $entry, $entryId): void
    {
        if ($entry === null) {
            throw NotFoundException::entityIdNotFound('Entry', $entryId);
        }
    }
}
