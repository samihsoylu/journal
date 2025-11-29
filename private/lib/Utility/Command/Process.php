<?php

declare(strict_types=1);

namespace App\Utility\Command;

use App\Utility\Command\Interfaces\ProcessInterface;

final readonly class Process implements ProcessInterface
{
    private function __construct(private int $id) {}

    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Start a system process and don't wait for it to finish.
     */
    public static function start(Command $command, string $logPath = '/dev/null') : self
    {
        $setsidPath = $_ENV['SETSID_PATH'] ?? '/usr/bin/setsid';

        $processId = shell_exec(sprintf('%s %s > %s 2>&1 & echo $!', $setsidPath, $command, $logPath));
        $id = filter_var($processId, FILTER_VALIDATE_INT);

        return new self($id);
    }

    /**
     * Check if a process is running in the system.
     *
     * @return bool true if process exists
     */
    public function isRunning() : bool
    {
        $command = new Command(['ps', '-p', $this->id]);
        exec((string) $command, $output);

        return isset($output[1]);
    }

    /**
     * Kill an existing system process.
     */
    public function stop() : void
    {
        $command = new Command(['kill', $this->id]);
        $command->execute();
    }

    public static function getById(int $id) : ?self
    {
        $self = new self($id);

        if ( ! $self->isRunning()) {
            return null;
        }

        return $self;
    }
}
