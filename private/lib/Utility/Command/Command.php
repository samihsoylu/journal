<?php

namespace App\Utility\Command;

class Command
{
    private array $commands;
    private array $arguments;

    public function __construct(array $commands, array $arguments = [])
    {
        $this->commands = $commands;
        $this->arguments = $arguments;
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

    public function toString(): string
    {
        $commands = $this->escapeCommands($this->commands);
        $arguments = $this->escapeArguments($this->arguments);

        $command = implode(' ', $commands);
        $argument = implode(' ', $arguments);

        return "{$command} {$argument}";
    }
}
