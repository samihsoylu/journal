<?php declare(strict_types=1);

namespace App\Utility\Lock;

class Lock
{
    private string $lockName;
    private string $lockFile;

    private function __construct(string $lockName)
    {
        $this->lockName = $lockName;
        $this->lockFile = CACHE_PATH . "/{$this->lockName}.lock";
    }

    public static function acquire(string $lockName): self
    {
        $lock = new self($lockName);

        return $lock->create();
    }

    private function create(): self
    {
        if (file_exists($this->lockFile)) {
            throw new \LogicException("Lock with name '{$this->lockName}' already exists");
        }

        $response = file_put_contents($this->lockFile, time());
        if ($response === false) {
            throw new \RuntimeException("Could not acquire lock '{$this->lockName}'");
        }

        return $this;
    }

    public function unlock(): void
    {
        unlink($this->lockFile);
    }

    public static function exists(string $lockName)
    {
        $self = new self($lockName);

        return file_exists($self->lockFile);
    }
}