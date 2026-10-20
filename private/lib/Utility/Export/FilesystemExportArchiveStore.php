<?php

declare(strict_types=1);

namespace App\Utility\Export;

use App\Exception\UserException\NotFoundException;
use App\Service\Export\ExportArchiveStore;

final readonly class FilesystemExportArchiveStore implements ExportArchiveStore
{
    public function __construct(private string $exportPath = EXPORT_CACHE_PATH) {}

    public function listForUser(string $username) : array
    {
        $prefix = ExportFileName::prefix($username);
        $exportedFiles = glob($this->exportPath . sprintf('/%s__*.zip', $prefix)) ?: [];
        $pattern = '#\A' . preg_quote($prefix, '#') . '__\d{2}-\d{2}-\d{4}_\d{2}-\d{2}-\d{2}\.zip\z#';

        return array_values(array_filter(array_map(basename(...), $exportedFiles), static fn ($name) => preg_match($pattern, $name) === 1));
    }

    public function findForUser(string $username, string $fileName) : ?string
    {
        // Reconstruct the path from the authenticated username, never a supplied path.
        $suffix = ExportFileName::suffix($fileName);
        $path = $this->exportPath . sprintf('/%s__%s', ExportFileName::prefix($username), $suffix);

        return file_exists($path) ? $path : null;
    }

    public function deleteForUser(string $username, string $fileName) : void
    {
        $path = $this->findForUser($username, $fileName);

        if ($path === null) {
            throw NotFoundException::entityNameNotFound('Zip', $fileName);
        }

        @unlink($path);
    }
}
