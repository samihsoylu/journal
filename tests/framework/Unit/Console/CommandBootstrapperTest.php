<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use SamihSoylu\Journal\Framework\Console\CommandBootstrapper;
use SamihSoylu\Utility\FileInspector;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Finder\Finder;

it('should correctly bootstrap and run commands', function (): void {
    $fakeCommandDirPath = testKit()->testPath()->getFakeTestDoublePath() . '/FakeCommand/ValidCommand';

    $console = Mockery::mock(Application::class);
    $console->shouldReceive('add')
        ->once();

    $console->shouldReceive('run')
        ->andReturn(0);

    $bootstrapper = new CommandBootstrapper(
        testKit()->getService(ContainerInterface::class),
        $console,
        Finder::create(),
        testKit()->getService(FileInspector::class),
        $fakeCommandDirPath,
    );

    expect($bootstrapper->run())->toBe(Command::SUCCESS);
});

it('should throw on invalid command class', function (): void {
    $fakeCommandDirPath = testKit()->testPath()->getFakeTestDoublePath() . '/FakeCommand/InvalidCommand';
    $console = Mockery::mock(Application::class);

    $bootstrapper = new CommandBootstrapper(
        testKit()->getService(ContainerInterface::class),
        $console,
        Finder::create(),
        testKit()->getService(FileInspector::class),
        $fakeCommandDirPath,
    );

    $bootstrapper->run();
})->throws(LogicException::class);
