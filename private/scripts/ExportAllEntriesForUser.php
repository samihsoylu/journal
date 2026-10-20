<?php

declare(strict_types=1);

use App\Service\EntryExporter;
use App\Utility\Encryptor;
use App\Utility\Lock\Lock;
use App\Utility\Lock\LockName;

require dirname(__DIR__) . '/init.php';

/** @var App\Framework\Kernel $kernel */
$arguments = $_SERVER['argv'] ?? [];

if (count($arguments) !== 4) {
    printf("Usage: %s <userId> <username> <encodedEncryptionKey>\n", $_SERVER['PHP_SELF']);
    exit(1);
}

$userId = (int) $arguments[1];
$username = $arguments[2];
$lock = null;
$exitCode = 0;

try {
    $lockName = LockName::create($userId, EntryExporter::archivePrefix($username), LockName::ACTION_EXPORT_ALL_ENTRIES_FOR_USER);
    $lock = Lock::acquire($lockName);
    $key = new Encryptor()->getKeyFromEncodedKey($arguments[3]);
    $kernel->get(EntryExporter::class)->export($userId, $username, $key);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Export failed: ' . $exception->getMessage() . PHP_EOL);

    if (SENTRY_ENABLED) {
        \Sentry\captureException($exception);
    }
    $exitCode = 1;
} finally {
    $lock?->unlock();
}
exit($exitCode);
