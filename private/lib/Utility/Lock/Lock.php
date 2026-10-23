<?php

declare(strict_types=1);

namespace App\Utility\Lock;

use LogicException;
use RuntimeException;

final class Lock
{
    private readonly string $lockFile;
    private mixed $handle = null;

    private function __construct(
        string $lockName,
        string $directory = CACHE_PATH,
    ) {
        $this->lockFile = $directory . sprintf('/%s.lock', $lockName);
    }

    public static function acquire(string $lockName, string $directory = CACHE_PATH) : self
    {
        $lock = new self($lockName, $directory);
        $handle = $lock->open();

        if ( ! flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);

            throw new LogicException(sprintf("Lock with name '%s' is already held", $lockName));
        }
        $lock->handle = $handle;
        ftruncate($handle, 0);
        fwrite($handle, (string) getmypid());

        return $lock;
    }

    private function open() : mixed
    {
        $handle = @fopen($this->lockFile, 'c+');

        if ($handle === false) {
            throw new RuntimeException('Could not open the worker lock');
        }
        chmod($this->lockFile, 0o600);

        return $handle;
    }

    public function unlock() : void
    {
        if (isset($this->handle) && is_resource($this->handle)) {
            flock($this->handle, LOCK_UN);
            fclose($this->handle);
        }
        // Keep the inode: unlinking can let a third worker lock a different file
        // while another worker still holds the original open file.
    }

    public function __destruct()
    {
        $this->unlock();
    }

    public static function exists(string $lockName, string $directory = CACHE_PATH) : bool
    {
        $lock = new self($lockName, $directory);
        $handle = $lock->open();
        $available = flock($handle, LOCK_EX | LOCK_NB);

        if ($available) {
            flock($handle, LOCK_UN);
        }
        fclose($handle);

        return ! $available;
    }
}
