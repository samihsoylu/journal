<?php declare(strict_types=1);

use App\Database\Model\Entry;
use App\Database\Repository\EntryRepository;
use App\Utility\Command\Command;
use App\Utility\Encryptor;
use App\Utility\Lock\Lock;
use App\Utility\Lock\LockName;
use App\Utility\Sanitize;
use Defuse\Crypto\Key;

require(dirname(__DIR__) . '/init.php');

if (count($argv) !== 4) {
    printf("Usage: %s <userId> <username> <encodedEncryptionKey>\n", $_SERVER['PHP_SELF']);
    exit(1);
}

$userId = (int)$argv[1];
$username = $argv[2];
$encodedEncryptionKey = $argv[3];

$lockName = LockName::create($userId, $username, LockName::ACTION_EXPORT_ALL_ENTRIES_FOR_USER);
$lock = Lock::acquire($lockName);
try {
    $encryptionKey = (new Encryptor())->getKeyFromEncodedKey($encodedEncryptionKey);

    $export = new EntryExporter($userId, $username, $encryptionKey, new EntryRepository());
    $export->execute();
} finally {
    $lock->unlock();
}

class EntryExporter
{
    private int $userId;
    private string $username;
    private Key $key;
    private EntryRepository $entryRepository;

    public function __construct(int $userId, string $username, Key $key, EntryRepository $entryRepository)
    {
        $this->userId = $userId;
        $this->username = $username;
        $this->key = $key;
        $this->entryRepository = $entryRepository;
    }

    public function execute(): void
    {
        if (!$this->userHasEntries()) {
            return;
        }

        $username = Sanitize::stringForShell($this->username);
        $exportDirectoryPath = EXPORT_CACHE_PATH . "/{$username}";

        $this->writeUserEntriesToDisk($exportDirectoryPath);
        $this->zipAllEntries($username);
        $this->removeEntriesFromDisk($exportDirectoryPath);
    }

    private function writeUserEntriesToDisk(string $exportDirectoryPath): void
    {
        foreach ($this->entryRepository->getAllEntriesForUser($this->userId) as $entry) {
            $this->saveEntryToFile($entry, $exportDirectoryPath);
        }
    }

    private function zipAllEntries(string $username): void
    {
        $dateString = (new DateTime())->format('d-m-Y_H-i-s');

        $command = new Command([
            'cd', EXPORT_CACHE_PATH, '&&',
            '/usr/bin/zip', '-r', "{$username}__{$dateString}.zip", "{$username}/"
        ]);

        try {
            $command->execute();
        } catch (\LogicException $exception) {
            // Intentionally caught so that removeEntriesFromDisk() is executed for privacy reasons
            echo "[{$dateString}] {$exception}\n";
            if (SENTRY_ENABLED) {
                \Sentry\captureException($exception);
            }
        }
    }

    private function removeEntriesFromDisk(string $exportDirectoryPath): void
    {
        $command = new Command(['rm', '-rf', $exportDirectoryPath]);

        $command->execute();
    }

    private function saveEntryToFile(Entry $entry, string $exportDirectoryPath): void
    {
        $title = Sanitize::stringForShell($entry->getTitle());
        $category = Sanitize::stringForShell($entry->getReferencedCategory()->getName());

        $this->ensureDirExists("{$exportDirectoryPath}/{$category}");
        file_put_contents(
            "{$exportDirectoryPath}/{$category}/{$title}.md",
            $entry->getContentDecrypted($this->key)
        );
    }

    private function userHasEntries(): bool
    {
        return ($this->entryRepository->getTotalCountByUserId($this->userId) > 0);
    }

    private function ensureDirExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }
}

exit(0);