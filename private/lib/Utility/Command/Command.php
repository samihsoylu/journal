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
        $arguments = $this->escapeArguments($this->arguments);

        $command  = implode(' ', $this->commands);
        $argument = implode(' ', $arguments);

        if (count($arguments) > 0) {
            $command .= " {$argument}";
        }

        return $command;
    }
}
