<?php declare(strict_types=1);

use App\Database\Model\Entry;
use App\Database\Repository\EntryRepository;
use App\Service\Helper\MediaHelper;
use App\Utility\Command\Command;
use App\Utility\Encryptor;
use App\Utility\Lock\Lock;
use App\Utility\Lock\LockName;
use App\Utility\Sanitize;
use Defuse\Crypto\Key;
use Jenssegers\Blade\Blade;

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
} catch (\Exception $exception) {
    $date = new DateTime();
    echo "[{$date->format('Y-m-d H:i:s')}] {$exception}\n";

    if (SENTRY_ENABLED) {
        \Sentry\captureException($exception);
    }
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

        try {
            $this->writeUserMediaToFile($exportDirectoryPath);
            $this->writeUserEntriesToDisk($exportDirectoryPath);
            $this->zipAllEntries($username);
        } finally {
            // once zipped, the directory is no longer needed
            // and if there is an error, we don't need sensitive information exposed.
            $this->removeEntriesFromDisk($exportDirectoryPath);
        }
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
            'cd',
            EXPORT_CACHE_PATH,
            '&&',
            '/usr/bin/zip',
            '-r',
            "{$username}__{$dateString}.zip",
            "{$username}/"
        ]);

        $command->execute();
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

        $blade = new Blade([TEMPLATE_PATH], TEMPLATE_CACHE_PATH);

        $entryContent = $entry->getContentDecrypted($this->key);
        $entryContent = str_replace('"/media', '"../media', $entryContent);

        $html = $blade->render('export', ['content' => $entryContent]);

        file_put_contents(
            "{$exportDirectoryPath}/{$category}/{$title}.html",
            $html
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

    private function writeUserMediaToFile(string $exportDirectoryPath): void
    {
        $mediaDirectory = "{$exportDirectoryPath}/media";
        $this->ensureDirExists($mediaDirectory);

        $helper = new MediaHelper();
        $service = new \App\Service\MediaService(new Encryptor(), $helper);

        $images = $helper->getAllImageNamesForUser($this->userId);
        foreach ($images as $imageName) {
            $image = $service->getDecryptedImage($this->userId, $imageName, $this->key);

            file_put_contents("{$mediaDirectory}/{$imageName}", $image->getBinary());
        }
    }
}

exit(0);
