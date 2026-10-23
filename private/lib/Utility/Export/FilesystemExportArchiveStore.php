<?php

declare(strict_types=1);

namespace App\Utility\Export;

use App\Exception\UserException\NotFoundException;
use App\Service\Export\ExportArchiveStore;
use RuntimeException;

final readonly class FilesystemExportArchiveStore implements ExportArchiveStore
{
    public function __construct(private string $exportPath = EXPORT_CACHE_PATH) {}

    public function listForUser(int $userId, string $username) : array
    {
        $prefix = ExportFileName::prefix($username);
        $exportedFiles = [];

        foreach ($this->directories($userId) as $directory) {
            clearstatcache(true, $directory);

            if ( ! is_dir($directory)) {
                if (file_exists($directory) || is_link($directory)) {
                    throw new RuntimeException('The export archive directory is unavailable');
                }

                continue;
            }

            if ( ! is_readable($directory) || ! is_executable($directory)) {
                throw new RuntimeException('Could not list export archives; the account was not deleted');
            }
            $matches = @glob($directory . sprintf('/%s__*.zip', $prefix), GLOB_ERR);

            if ($matches === false) {
                throw new RuntimeException('Could not list export archives; the account was not deleted');
            }
            array_push($exportedFiles, ...$matches);
        }
        $pattern = '#\A' . preg_quote($prefix, '#') . '__\d{2}-\d{2}-\d{4}_\d{2}-\d{2}-\d{2}\.zip\z#';

        return array_values(array_unique(array_filter(array_map(basename(...), $exportedFiles), static fn ($name) => preg_match($pattern, $name) === 1)));
    }

    public function findForUser(int $userId, string $username, string $fileName) : ?string
    {
        // Reconstruct the path from the authenticated username, never a supplied path.
        $suffix = ExportFileName::suffix($fileName);

        foreach ($this->directories($userId) as $directory) {
            $path = $directory . sprintf('/%s__%s', ExportFileName::prefix($username), $suffix);

            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    public function deleteForUser(int $userId, string $username, string $fileName) : void
    {
        $path = $this->findForUser($userId, $username, $fileName);

        if ($path === null) {
            throw NotFoundException::entityNameNotFound('Zip', $fileName);
        }

        // A basename can exist in both the v1 and account-scoped locations.
        // Successful account deletion must not leave either plaintext copy.
        foreach ($this->directories($userId) as $directory) {
            $candidate = $directory . '/' . basename($path);

            if (is_file($candidate) && ! @unlink($candidate)) {
                throw new RuntimeException('Could not remove an export archive; the account was not deleted');
            }
        }
    }

    private function directories(int $userId) : array
    {
        // New workers cannot give late archives to a different account reusing
        // a deleted username. Flat archives remain readable for v1 upgrades.
        return [$this->exportPath . '/' . $userId, $this->exportPath];
    }
}
