<?php

namespace App\Utility\Command;

use App\Utility\Command\Interfaces\ProcessInterface;

class Process implements ProcessInterface
{
    private int $id;

    private function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Start a system process and don't wait for it to finish
     *
     * @return self
     */
    public static function start(Command $command, string $logPath = '/dev/null'): self
    {
        $processId = shell_exec('/usr/bin/setsid ' . $command->toString() . " > {$logPath} 2>&1 & echo $!");
        $id        = filter_var($processId, FILTER_VALIDATE_INT);

        return new self($id);
    }

    /**
     * Check if a process is running in the system
     *
     * @return bool true if process exists
     */
    public function isRunning(): bool
    {
        $command = new Command(['ps', '-p', $this->id]);
        exec($command->toString(), $output);

        return isset($output[1]);
    }

    /**
     * Kill an existing system process
     *
     * @return void
     */
    public function stop(): void
    {
        $command = new Command(['kill', $this->id]);

        exec($command->toString());
    }

    public static function getById(int $id): ?self
    {
        $self = new self($id);
        if (!$self->isRunning()) {
            return null;
        }

        return $self;
    }
}
