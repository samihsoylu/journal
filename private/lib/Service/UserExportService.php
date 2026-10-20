<?php

declare(strict_types=1);

namespace App\Service;

use App\Database\Repository\EntryRepository;
use App\Exception\UserException\ActionNotPermittedException;
use App\Service\Export\ExportArchiveStore;
use App\Service\Export\ExportJobRunner;
use App\Service\Helper\UserHelper;
use Defuse\Crypto\Key;

final readonly class UserExportService
{
    public function __construct(
        private UserHelper $userHelper,
        private EntryRepository $entries,
        private ExportArchiveStore $archives,
        private ExportJobRunner $jobs,
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

        return $this->jobs->start($user->getId(), $user->getUsername(), $encryptionKey);
    }

    /**
     * @return string[]
     */
    public function getZipFileNamesForExportedEntriesByUser(int $userId) : array
    {
        $user = $this->userHelper->getUserById($userId);

        return $this->archives->listForUser($user->getUsername());
    }

    public function getZipFilePathForExportedEntriesByUser(int $userId, string $fileName) : ?string
    {
        $user = $this->userHelper->getUserById($userId);

        return $this->archives->findForUser($user->getUsername(), $fileName);
    }

    public function deleteExportedEntriesZipFile(int $userId, string $fileName) : void
    {
        $user = $this->userHelper->getUserById($userId);
        $this->archives->deleteForUser($user->getUsername(), $fileName);
    }

    public function getHasExportEntriesActionRunning(int $userId, string $username) : bool
    {
        return $this->jobs->isRunning($userId, $username);
    }
}
