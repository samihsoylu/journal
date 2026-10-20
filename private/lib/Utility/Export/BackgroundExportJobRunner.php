<?php

declare(strict_types=1);

namespace App\Utility\Export;

use App\Exception\UserException\InvalidOperationException;
use App\Service\Export\ExportJobRunner;
use App\Utility\Command\Command;
use App\Utility\Command\Process;
use App\Utility\Lock\Lock;
use App\Utility\Lock\LockName;
use Defuse\Crypto\Key;
use LogicException;

final readonly class BackgroundExportJobRunner implements ExportJobRunner
{
    public function __construct(
        private string $scriptPath = SCRIPTS_PATH . '/ExportAllEntriesForUser.php',
        private string $logDirectory = EXPORT_CACHE_PATH . '/log',
    ) {}

    public function start(int $userId, string $username, Key $key) : int
    {
        if ($this->isRunning($userId, $username)) {
            throw InvalidOperationException::actionIsAlreadyRunning('exporting entries');
        }

        if ( ! file_exists($this->scriptPath)) {
            throw new LogicException(sprintf('Script in path: %s does not exist', $this->scriptPath));
        }

        if ( ! is_dir($this->logDirectory) && ! mkdir($this->logDirectory, 0o750, true) && ! is_dir($this->logDirectory)) {
            throw new LogicException('Could not create the export log directory');
        }

        $command = new Command([escapeshellarg(PHP_BINDIR . '/php')], [
            $this->scriptPath, $userId, $username, $key->saveToAsciiSafeString(),
        ]);

        return Process::start($command, $this->logDirectory . '/' . ExportFileName::prefix($username) . '.log')->getId();
    }

    public function isRunning(int $userId, string $username) : bool
    {
        return Lock::exists(LockName::create($userId, ExportFileName::prefix($username), LockName::ACTION_EXPORT_ALL_ENTRIES_FOR_USER));
    }
}
