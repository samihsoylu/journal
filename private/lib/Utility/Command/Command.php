<?php

declare(strict_types=1);

namespace App\Utility\Command;

use LogicException;
use Sentry\State\Scope;
use Stringable;

use function Sentry\configureScope;

final readonly class Command implements Stringable
{
    public function __construct(
        private array $commands,
        private array $arguments = [],
    ) {}

    private function escapeArguments(array $arguments) : array
    {
        $escapedArguments = [];

        foreach ($arguments as $argument) {
            $escapedArguments[] = escapeshellarg((string) $argument);
        }

        return $escapedArguments;
    }

    public function __toString() : string
    {
        $arguments = $this->escapeArguments($this->arguments);

        $command = implode(' ', $this->commands);
        $argument = implode(' ', $arguments);

        if ($arguments !== []) {
            $command .= ' ' . $argument;
        }

        return $command;
    }

    public function execute() : array
    {
        exec((string) $this, $output, $exitCode);

        if ($exitCode !== 0) {
            if (SENTRY_ENABLED && $output !== []) {
                configureScope(static function (Scope $scope) use ($output) : void {
                    // @phpstan-ignore-next-line
                    $scope->setContext('Command output', $output);
                });
            }

            throw new LogicException(
                sprintf('Failed to execute command: %s ', $this),
                $exitCode,
            );
        }

        return $output;
    }
}
