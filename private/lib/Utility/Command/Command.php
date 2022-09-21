<?php declare(strict_types=1);

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

    public function __toString(): string
    {
        $arguments = $this->escapeArguments($this->arguments);

        $command  = implode(' ', $this->commands);
        $argument = implode(' ', $arguments);

        if (count($arguments) > 0) {
            $command .= " {$argument}";
        }

        return $command;
    }

    public function execute(): array
    {
        exec((string)$this, $output, $exitCode);

        if ($exitCode !== 0) {
            if (SENTRY_ENABLED && $output !== []) {
                \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($output): void {
                    $scope->setContext('Command output', $output);
                });
            }

            throw new \LogicException(
                "Failed to execute command: {$this} ",
                $exitCode
            );
        }

        return $output;
    }
}
