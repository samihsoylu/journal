<?php

declare(strict_types=1);

namespace App\Utility\Lock;

use LogicException;
use RuntimeException;

final readonly class Lock
{
    private string $lockFile;

    private function __construct(private string $lockName)
    {
        $this->lockFile = CACHE_PATH . sprintf('/%s.lock', $this->lockName);
    }

    public static function acquire(string $lockName) : self
    {
        $lock = new self($lockName);

        return $lock->create();
    }

    private function create() : self
    {
        if (file_exists($this->lockFile)) {
            throw new LogicException(sprintf("Lock with name '%s' already exists", $this->lockName));
        }

        $response = file_put_contents($this->lockFile, time());

        if ($response === false) {
            throw new RuntimeException(sprintf("Could not acquire lock '%s'", $this->lockName));
        }

        return $this;
    }

    public function unlock() : void
    {
        unlink($this->lockFile);
    }

    public static function exists(string $lockName) : bool
    {
        $self = new self($lockName);

        return file_exists($self->lockFile);
    }
}
