<?php

declare(strict_types=1);

namespace App\Service;

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
    public function __construct(private UserHelper $userHelper) {}

    /**
     * @param Key $encryptionKey used for decrypting entry contents
     */
    public function exportUserEntries(int $userId, Key $encryptionKey) : int
    {
        $exports = $this->getZipFileNamesForExportedEntriesByUser($userId);

        if ($exports !== []) {
            throw new ActionNotPermittedException('Allowed count of exports reached');
        }

        $user = $this->userHelper->getUserById($userId);
        $exportScriptFilePath = SCRIPTS_PATH . '/ExportAllEntriesForUser.php';

        $this->ensureExportIsNotAlreadyRunning($user->getId(), $user->getUsername());
        $this->ensureScriptExists($exportScriptFilePath);

        $command = new Command([
            PHP_BINDIR . '/php', $exportScriptFilePath, $userId, $user->getUsername(), $encryptionKey->saveToAsciiSafeString(),
        ]);

        $process = Process::start(
            $command,
            BASE_PATH . sprintf('/private/cache/export/log/%s.log', $user->getUsername()),
        );

        return $process->getId();
    }

    /**
     * @return string[]
     */
    public function getZipFileNamesForExportedEntriesByUser(int $userId) : array
    {
        $user = $this->userHelper->getUserById($userId);

        /** @see EntryExporter::zipAllEntries() */
        $exportedFiles = glob(EXPORT_CACHE_PATH . sprintf('/%s__*.zip', $user->getUsername()));

        return array_map(basename(...), $exportedFiles);
    }

    public function getZipFilePathForExportedEntriesByUser(int $userId, string $fileName) : ?string
    {
        $user = $this->userHelper->getUserById($userId);

        // must be similar to samih__14-03-2022_00-19-32.zip
        $this->ensureValidExportEntriesZipFileName($fileName);

        // Results in: 14-03-2022_00-19-32.zip
        $fileNameSuffix = explode('__', $fileName)[1];

        // Here we reconstruct the file name in-case it was tampered
        $filePath = EXPORT_CACHE_PATH . sprintf('/%s__%s', $user->getUsername(), $fileNameSuffix);

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
        $lockName = LockName::create($userId, $username, LockName::ACTION_EXPORT_ALL_ENTRIES_FOR_USER);

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

    private function ensureValidExportEntriesZipFileName(string $fileName) : void
    {
        // expected file must adhere to samih__14-03-2022_00-19-32.zip
        if ( ! preg_match('/\S+_{2}\d{2}-\d{2}-\d{4}_\d{2}-\d{2}-\d{2}\S+/', $fileName)) {
            throw InvalidArgumentException::invalidFileNameProvided();
        }
    }
}
