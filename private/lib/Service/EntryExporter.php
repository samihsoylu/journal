<?php

declare(strict_types=1);

namespace App\Service;

use App\Database\Repository\EntryRepository;
use App\Service\Helper\MediaHelper;
use App\Utility\BladeRenderer;
use App\Utility\Sanitize;
use Defuse\Crypto\Key;
use RuntimeException;
use ZipArchive;

final readonly class EntryExporter
{
    public function __construct(
        private EntryRepository $entries,
        private MediaService $media,
        private MediaHelper $mediaHelper,
        private Sanitize $sanitize,
        private string $exportPath = EXPORT_CACHE_PATH,
    ) {}

    public static function archivePrefix(string $username) : string
    {
        // Preserve distinct usernames while preventing paths/glob metacharacters.
        return preg_match('/\A[A-Za-z0-9._-]+\z/', $username) ? $username : '~' . bin2hex($username);
    }

    public function export(int $userId, string $username, Key $key) : ?string
    {
        if ($this->entries->getTotalCountByUserId($userId) === 0) {
            return null;
        }

        if ( ! is_dir($this->exportPath) && ! mkdir($this->exportPath, 0o750, true) && ! is_dir($this->exportPath)) {
            throw new RuntimeException('Could not create the export directory');
        }

        $archivePath = $this->exportPath . '/' . self::archivePrefix($username) . '__' . date('d-m-Y_H-i-s') . '.zip';

        if (file_exists($archivePath)) {
            throw new RuntimeException('An export already exists for this account and time');
        }

        $temporary = tempnam($this->exportPath, '.export-');

        if ($temporary === false) {
            throw new RuntimeException('Could not create a temporary export');
        }

        $archive = new ZipArchive();
        $opened = false;

        try {
            if ($archive->open($temporary, ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Could not open the export archive');
            }
            $opened = true;
            $root = $this->sanitize->stringForShell($username) ?: 'user_' . $userId;
            $blade = new BladeRenderer();

            foreach ($this->mediaHelper->getAllImageNamesForUser($userId) as $imageName) {
                $image = $this->media->getDecryptedImage($userId, $imageName, $key);

                if ( ! $archive->addFromString($root . '/media/' . $imageName, $image->getBinary())) {
                    throw new RuntimeException('Could not add media to the export');
                }
            }

            foreach ($this->entries->getAllEntriesForUser($userId) as $entry) {
                $category = $entry->getReferencedCategory();
                $categoryName = ($this->sanitize->stringForShell($category->getName()) ?: 'category') . '__' . $category->getId();
                $title = ($this->sanitize->stringForShell($entry->getTitle()) ?: 'entry') . '__' . $entry->getId();
                $content = $entry->getContentDecrypted($key);
                $prefixes = ['/media/'];

                if (defined('BASE_URL') && BASE_URL !== '') {
                    $prefixes[] = BASE_URL . '/media/';
                }

                foreach ($prefixes as $prefix) {
                    $content = str_replace(['"' . $prefix, "'" . $prefix], ['"../media/', "'../media/"], $content);
                }
                $document = $blade->render('export', ['content' => $content]);

                if ( ! $archive->addFromString($root . '/' . $categoryName . '/' . $title . '.html', $document)) {
                    throw new RuntimeException('Could not add an entry to the export');
                }
            }

            $closed = $archive->close();
            $opened = false;

            if ( ! $closed || ! rename($temporary, $archivePath)) {
                throw new RuntimeException('Could not finish the export archive');
            }

            return $archivePath;
        } finally {
            if ($opened) {
                $archive->close();
            }

            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
    }
}
