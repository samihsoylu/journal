<?php

namespace App\Service\Helpers;

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

    public function getUriForPageFilter(int $page): string
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
}
