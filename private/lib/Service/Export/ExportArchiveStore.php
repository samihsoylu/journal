<?php

declare(strict_types=1);

namespace App\Service\Export;

interface ExportArchiveStore
{
    /** @return string[] */
    public function listForUser(string $username) : array;

    public function findForUser(string $username, string $fileName) : ?string;

    public function deleteForUser(string $username, string $fileName) : void;
}
