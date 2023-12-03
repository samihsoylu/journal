<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\Core;

use Psr\Container\ContainerInterface;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\TestOrmInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final readonly class TestKit
{
    public function __construct(
        private ContainerInterface $container,
        private TestOrmInterface $testOrm,
        private TestDbPopulator $testDbPopulator,
        private TestPath $testPath,
    ) {}

    public function testOrm(): TestOrmInterface
    {
        return $this->testOrm;
    }

    public function testDbPopulator(): TestDbPopulator
    {
        return $this->testDbPopulator;
    }

    public function testPath(): TestPath
    {
        return $this->testPath;
    }

    /**
     * @template T
     *
     * @param class-string<T> $serviceId
     * @return T
     */
    public function getService(string $serviceId): object
    {
        return $this->container->get($serviceId);
    }

    /**
     * @param array<array-key, mixed> $args
     * @param array<array-key, mixed> $options
     * @param array<string> $inputs
     */
    public function executeConsoleCommand(
        Command $command,
        array $args = [],
        array $options = [],
        array $inputs = []
    ): void {
        $options['interactive'] ??= false;

        $this->assertInteractiveModeEnabledWithInputs($options, $inputs);

        $application = new Application();
        $application->add($command);

        $tester = new CommandTester($application->find($command->getName()));
        $tester->setInputs($inputs)
            ->execute($args, $options);
    }

    private function assertInteractiveModeEnabledWithInputs(array $options, array $inputs): void
    {
        if ($options['interactive'] === false && $inputs !== []) {
            throw new \RuntimeException(
                "The 'interactive' option must be set to 'true' when providing inputs."
            );
        }
    }
}
