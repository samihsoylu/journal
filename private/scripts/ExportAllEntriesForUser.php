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

$userId = (int)$argv[1];
$username = $argv[2];
$encodedEncryptionKey = $argv[3];

$lockName = LockName::create($userId, $username, LockName::ACTION_EXPORT_ALL_ENTRIES_FOR_USER);
$lock = Lock::acquire($lockName);
try {
    $encryptionKey = (new Encryptor())->getKeyFromEncodedKey($encodedEncryptionKey);

    $export = new EntryExporter($userId, $username, $encryptionKey);
    $export->execute();
} finally {
    $lock->unlock();
}

class EntryExporter
{
    private int $userId;
    private string $username;
    private Key $key;

    public function __construct(int $userId, string $username, Key $key)
    {
        $this->userId = $userId;
        $this->username = $username;
        $this->key = $key;
    }

    public function execute(): void
    {
        $repository = new EntryRepository();

        $totalEntryCount = $repository->getTotalCountByUserId($this->userId);
        if ($totalEntryCount === 0) {
            return;
        }

        foreach ($repository->getAllEntriesForUser($this->userId) as $entry) {
            $this->saveEntryToFile($entry);
        }

        $this->zipAllEntries();
    }

    private function zipAllEntries(): void
    {
        $dateString = (new DateTime())->format("d-m-Y_H-i-s");

        $command = new Command([
            'cd', EXPORT_CACHE_PATH, '&&',
            '/usr/bin/zip', '-r', "{$this->username}__{$dateString}.zip", "{$this->username}/"
        ]);

        shell_exec($command->toString());
    }

    private function saveEntryToFile(Entry $entry): void
    {
        $title = Sanitize::string($entry->getTitle(), [Sanitize::OPTION_CLEAN_SPACES, Sanitize::OPTION_CLEAN_SPECIAL_CHARS]);

        // Remove special characters
        $fileName = preg_replace('/\W/', '', $title);

        $this->ensureDirExists("{$this->getUserExportPath()}/");
        file_put_contents(
            "{$this->getUserExportPath()}/{$fileName}.md",
            $entry->getContentDecrypted($this->key)
        );
    }

    private function ensureDirExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory);
        }
    }

    private function getUserExportPath(): string
    {
        return EXPORT_CACHE_PATH . "/{$this->username}";
    }
}
