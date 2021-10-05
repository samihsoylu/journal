<?php declare(strict_types=1);

use App\Database\Model\Entry;
use App\Database\Repository\EntryRepository;
use App\Utility\Command\Command;
use App\Utility\Encryptor;
use Defuse\Crypto\Key;

require_once(dirname(__DIR__) . '/init.php');

$userId = (int)$argv[1];
$username = $argv[2];
$userEncodedKey = $argv[3];

define('LOG_FILE_PATH', EXPORT_CACHE_PATH . "/{$username}.log");
define('LOCK_FILE_PATH', EXPORT_CACHE_PATH . "/{$username}.lock");

try {
    $encryptor = new Encryptor();
    $key = $encryptor->getKeyFromEncodedKey($userEncodedKey);

    $export = new EntryExporter($userId, $username, $key);
    $export->execute();
} catch (\Exception $e) {
    file_put_contents(LOG_FILE_PATH, $e, FILE_APPEND);
}

class EntryExporter {
    private const MAX_BATCH_SIZE = 5000;

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
        $this->ensureLockIsNotSet();
        $repository = new EntryRepository();

        $totalEntryCount = $repository->getTotalCountByUserId($this->userId);
        if ($totalEntryCount === 0) {
            return;
        }

        $totalPages = $this->calculateTotalBatches($totalEntryCount);
        for ($page = 0; $page < $totalPages; $page++) {
            $this->ensureLockFile($page, $totalPages);

            $entries = $repository->getSelectionByUserId($this->userId, $page, self::MAX_BATCH_SIZE);
            foreach ($entries as $entry) {
                $this->saveEntryToFile($entry);
            }

            $entries = null;
            $this->log('info', "Processed pages: {$page}/{$totalPages}");
        }

        $this->zipAllEntries();
        $this->removeLockFile();
    }

    private function zipAllEntries(): void
    {
        $dateString = (new DateTime())->format("d-m-Y_H-i-s");

        $command = new Command([
            'cd', EXPORT_CACHE_PATH, '&&',
            'zip', '-r', "{$this->username}__{$dateString}.zip", "{$this->username}/"
        ]);

        shell_exec($command->toString());
    }

    private function saveEntryToFile(Entry $entry): void
    {
        // Replace all spaces with hyphens
        $title = str_replace(' ', '-', $entry->getTitle());

        // Remove special characters
        $fileName = preg_replace('/[^A-Za-z0-9\-]/', '', $title);

        $this->ensureDirExists("{$this->getUserExportPath()}/");
        file_put_contents(
            "{$this->getUserExportPath()}/{$fileName}.md",
            $entry->getContentAsMarkup($this->key)
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

    private function log(string $type, string $message): void
    {
        $dateTime = (new DateTime())->format("d-m-Y h:i:s");

        file_put_contents(LOG_FILE_PATH, "[{$dateTime}][{$type}] {$message}\n", FILE_APPEND);
    }

    private function ensureLockIsNotSet(): void
    {
        if (file_exists(LOCK_FILE_PATH)) {
            $this->throwException("Lock file exists when it shouldn't");
        }
    }

    private function ensureLockFile(int $currentPage, int $totalPages): void
    {
        $encodedJson = json_encode([
            'progress' => $this->calculateProgress($currentPage, $totalPages),
        ], JSON_PRETTY_PRINT);

        file_put_contents(LOCK_FILE_PATH, $encodedJson);
    }

    private function removeLockFile(): void
    {
        unlink(LOCK_FILE_PATH);
    }

    private function calculateTotalBatches(int $totalItemCount): int
    {
        $totalBatches = ceil($totalItemCount / self::MAX_BATCH_SIZE);

        return (int)$totalBatches;
    }

    private function calculateProgress(int $currentPage, int $totalPages): int
    {
        $percentage = round(($currentPage / $totalPages) * 100);

        return (int)$percentage;
    }

    private function throwException(string $errorMessage): void
    {
        $this->log('error', $errorMessage);

        throw new \RuntimeException($errorMessage);
    }
}