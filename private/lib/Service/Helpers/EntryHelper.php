<?php

namespace App\Service\Helpers;

use App\Database\Model\Entry;
use App\Exception\UserException\NotFoundException;
use App\Utility\Encryptor;
use App\Utility\Registry;
use App\Utility\UserSession;

class EntryHelper
{
    private UserSession $session;

    public function __construct()
    {
        $this->session   = UserSession::load();
    }

    public function ensureUserOwnsEntry(Entry $entry): void
    {
        if ($entry->getReferencedUser()->getId() !== $this->session->getUserId()) {
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
