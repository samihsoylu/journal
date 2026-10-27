<?php

declare(strict_types=1);

namespace App\Service\Export;

interface ExportArchiveStore
{
    /** @return string[] */
    public function listForUser(int $userId, string $username) : array;

    public function findForUser(int $userId, string $username, string $fileName) : ?string;

    public function deleteForUser(int $userId, string $username, string $fileName) : void;
}
