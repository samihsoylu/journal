<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework\Console;

use LogicException;
use Psr\Container\ContainerInterface;
use RuntimeException;
use SamihSoylu\Journal\Framework\Kernel;
use SamihSoylu\Utility\FileInspector;
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
        private FileInspector $fileInspector,
        private string $consoleCommandDir,
    ) {}

    public function run(): int
    {
        $this->initializeCommands();

        return $this->console->run();
    }

    private function initializeCommands(): void
    {
        $files = $this->find->files()->in($this->consoleCommandDir);

        foreach ($files as $file) {
            $fqcn = $this->fileInspector->getFullyQualifiedClassName($file);
            $this->assertIsValidCommandClass($fqcn);

            /** @var Command $command */
            $command = $this->container->get($fqcn);

            $this->console->add($command);
        }
    }

    private function assertIsValidCommandClass(string $fqcn): void
    {
        if (!is_subclass_of($fqcn, Command::class)) {
            throw new LogicException("Class '{$fqcn}' is not a command");
        }
    }
}