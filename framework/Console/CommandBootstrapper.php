<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework\Console;

use LogicException;
use Psr\Container\ContainerInterface;
use RuntimeException;
use SamihSoylu\Journal\Framework\Kernel;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final readonly class CommandBootstrapper
{
    public function __construct(
        private ContainerInterface $container,
        private Application $console,
        private Finder $find,
        private string $consoleCommandDir,
        private string $consoleCommandNamespace,
    ) {}

    public function run(): void
    {
        $this->initializeCommands();

        $this->console->run();
    }

    private function initializeCommands(): void
    {
        $commands = $this->find->files()->in($this->consoleCommandDir);

        foreach ($commands as $command) {
            $fqcn = $this->getFullyQualifiedClassName($command);
            $this->assertIsValidCommandClass($fqcn);

            /** @var Command $command */
            $command = $this->container->get($fqcn);

            $this->console->add($command);
        }
    }

    private function getFullyQualifiedClassName(SplFileInfo $command): string
    {
        $className = str_replace('.php', '', $command->getRelativePathname());
        $className = str_replace('/', '\\', $className);

        return $this->consoleCommandNamespace . $className;
    }

    private function assertIsValidCommandClass(string $fqcn): void
    {
        if (!class_exists($fqcn)) {
            throw new RuntimeException("Class '{$fqcn}' does not exist");
        }

        if (!is_subclass_of($fqcn, Command::class)) {
            throw new LogicException("Class '{$fqcn}' is not a command");
        }
    }
}