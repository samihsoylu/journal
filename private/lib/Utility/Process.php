<?php

namespace App\Utility;

class Process
{
    private array $commands;
    private array $arguments;
    private ?int $processId = null;

    public const STATUS_RUNNING = 'Running';
    public const STATUS_STOPPED = 'Stopped';

    public function __construct(array $commands = [], array $arguments = [])
    {
        $this->commands = $commands;
        $this->arguments = $arguments;
    }

    /**
     * Waits until process is complete
     *
     * @return string|null
     */
    public function execute(): ?string
    {
        $command = $this->getCommand();

        $output = shell_exec($command);
        if (!$output) {
            return null;
        }

        return $output;
    }

    /**
     * Start a system process and don't wait for it to finish
     *
     * @return int processId
     */
    public function start(): int
    {
        $command = $this->getCommand();

        $processId = shell_exec('/usr/bin/setsid ' . $command . ' > /dev/null 2>&1 & echo $!');

        $this->processId = filter_var($processId, FILTER_VALIDATE_INT);

        return $this->processId;
    }

    private function getCommand(): string
    {
        $commands  = $this->escapeCommands($this->commands);
        $arguments = $this->escapeArguments($this->arguments);

        $command   = implode(' ', $commands);
        $argument  = implode(' ', $arguments);

        return "{$command} {$argument}";
    }

    private function escapeCommands(array $commands): array
    {
        if ($commands === []) {
            throw new \RuntimeException("No command was provided");
        }

        $escapedCommands = [];
        foreach ($commands as $command) {
            $escapedCommands[] = escapeshellcmd($command);
        }

        return $escapedCommands;
    }

    private function escapeArguments(array $arguments): array
    {
        $escapedArguments = [];
        foreach ($arguments as $argument) {
            $escapedArguments[] = escapeshellarg($argument);
        }

        return $escapedArguments;
    }

    public function getProcessId(): ?int
    {
        return $this->processId;
    }

    public function isRunning(): bool
    {
        $command = "ps -p {$this->processId}";

        exec($command, $output);

        // return true if process exists
        return isset($output[1]);
    }

    public function getStatus(): string
    {
        return ($this->isRunning()) ? self::STATUS_RUNNING : self::STATUS_STOPPED;
    }

    public function stop(): string
    {
        $command = "kill {$this->processId}";

        exec($command);

        return $this->getStatus();
    }

    public function setProcessId(?int $processId): void
    {
        $this->processId = $processId;
    }
}
