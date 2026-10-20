<?php

declare(strict_types=1);

namespace App\Service;

use App\Database\Repository\EntryRepository;
use App\Exception\UserException\ActionNotPermittedException;
use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\InvalidOperationException;
use App\Exception\UserException\NotFoundException;
use App\Service\Helper\UserHelper;
use App\Utility\Command\Command;
use App\Utility\Command\Process;
use App\Utility\Lock\Lock;
use App\Utility\Lock\LockName;
use Defuse\Crypto\Key;
use LogicException;

final readonly class UserExportService
{
    public function __construct(
        private UserHelper $userHelper,
        private EntryRepository $entries,
    ) {}

    /**
     * @param Key $encryptionKey used for decrypting entry contents
     */
    public function exportUserEntries(int $userId, Key $encryptionKey) : int
    {
        if ($this->entries->getTotalCountByUserId($userId) === 0) {
            throw new ActionNotPermittedException('There are no entries to export');
        }

        $exports = $this->getZipFileNamesForExportedEntriesByUser($userId);

        if ($exports !== []) {
            throw new ActionNotPermittedException('Allowed count of exports reached');
        }

        $user = $this->userHelper->getUserById($userId);
        $exportScriptFilePath = SCRIPTS_PATH . '/ExportAllEntriesForUser.php';

        $this->ensureExportIsNotAlreadyRunning($user->getId(), $user->getUsername());
        $this->ensureScriptExists($exportScriptFilePath);

        $logDirectory = EXPORT_CACHE_PATH . '/log';

        if ( ! is_dir($logDirectory) && ! mkdir($logDirectory, 0o750, true) && ! is_dir($logDirectory)) {
            throw new LogicException('Could not create the export log directory');
        }

        $command = new Command([escapeshellarg(PHP_BINDIR . '/php')], [
            $exportScriptFilePath, $userId, $user->getUsername(), $encryptionKey->saveToAsciiSafeString(),
        ]);

        $process = Process::start(
            $command,
            $logDirectory . '/' . EntryExporter::archivePrefix($user->getUsername()) . '.log',
        );

        return $process->getId();
    }

    /**
     * @return string[]
     */
    public function getZipFileNamesForExportedEntriesByUser(int $userId) : array
    {
        $user = $this->userHelper->getUserById($userId);

        $prefix = EntryExporter::archivePrefix($user->getUsername());
        $exportedFiles = glob(EXPORT_CACHE_PATH . sprintf('/%s__*.zip', $prefix)) ?: [];
        $pattern = '#\A' . preg_quote($prefix, '#') . '__\d{2}-\d{2}-\d{4}_\d{2}-\d{2}-\d{2}\.zip\z#';

        return array_values(array_filter(array_map(basename(...), $exportedFiles), static fn ($name) => preg_match($pattern, $name) === 1));
    }

    public function getZipFilePathForExportedEntriesByUser(int $userId, string $fileName) : ?string
    {
        $user = $this->userHelper->getUserById($userId);

        // must be similar to samih__14-03-2022_00-19-32.zip
        $fileNameSuffix = $this->getExportEntriesZipFileNameSuffix($fileName);

        // Here we reconstruct the file name in-case it was tampered
        $filePath = EXPORT_CACHE_PATH . sprintf('/%s__%s', EntryExporter::archivePrefix($user->getUsername()), $fileNameSuffix);

        return (file_exists($filePath)) ? $filePath : null;
    }

    public function deleteExportedEntriesZipFile(int $userId, string $fileName) : void
    {
        $filePath = $this->getZipFilePathForExportedEntriesByUser($userId, $fileName);

        if ($filePath === null) {
            throw NotFoundException::entityNameNotFound('Zip', $fileName);
        }

        @unlink($filePath);
    }

    public function getHasExportEntriesActionRunning(int $userId, string $username) : bool
    {
        $lockName = LockName::create($userId, EntryExporter::archivePrefix($username), LockName::ACTION_EXPORT_ALL_ENTRIES_FOR_USER);

        return Lock::exists($lockName);
    }

    private function ensureExportIsNotAlreadyRunning(int $userId, string $username) : void
    {
        if ($this->getHasExportEntriesActionRunning($userId, $username)) {
            throw InvalidOperationException::actionIsAlreadyRunning('exporting entries');
        }
    }

    private function ensureScriptExists(string $scriptPath) : void
    {
        if ( ! file_exists($scriptPath)) {
            throw new LogicException(sprintf('Script in path: %s does not exist', $scriptPath));
        }
    }

    private function getExportEntriesZipFileNameSuffix(string $fileName) : string
    {
        // expected file must adhere to samih__14-03-2022_00-19-32.zip
        if ( ! preg_match('#\A[A-Za-z0-9._%~-]+__(\d{2}-\d{2}-\d{4}_\d{2}-\d{2}-\d{2}\.zip)\z#', $fileName, $matches)) {
            throw InvalidArgumentException::invalidFileNameProvided();
        }

        return $matches[1];
    }
}
