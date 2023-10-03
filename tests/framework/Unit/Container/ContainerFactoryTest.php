<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use SamihSoylu\Journal\Framework\Container\ContainerFactory;
use SamihSoylu\Journal\Framework\Environment;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyObjectInterface;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyProdObject;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyTestObject;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

it('should create a container', function (): void {
    $configDir = $_ENV['JOURNAL_ROOT_DIR'] . '/tests/TestFramework/TestDouble/Fake/FakeConfig/config';

    $containerFactory = new ContainerFactory($configDir, Environment::TEST);
    $container = $containerFactory->create();

    expect($container)->toBeInstanceOf(ContainerInterface::class);
});

it('should load container configurations based on the app environment', function ($environment, $expectedInstance): void {
    $configDir = $_ENV['JOURNAL_ROOT_DIR'] . '/tests/TestFramework/TestDouble/Fake/FakeConfig/config';

    $containerFactory = new ContainerFactory($configDir, $environment);
    $container = $containerFactory->create();

    $dummyObject = $container->get(DummyObjectInterface::class);
    expect($dummyObject)->toBeInstanceOf($expectedInstance);
})->with([
    [Environment::TEST, DummyTestObject::class],
    [Environment::PROD, DummyProdObject::class],
]);

it('should throw exception when config dir does not exist', function (): void {
    $testDoubleDir = $_ENV['JOURNAL_ROOT_DIR'] . '/tests/TestFramework/TestDouble/';
    $containerFactory = new ContainerFactory($testDoubleDir, Environment::TEST);
    $container = $containerFactory->create();
})->throws(DirectoryNotFoundException::class);

it('should throw exception when configurator is not callable', function (): void {
    $configDir = $_ENV['JOURNAL_ROOT_DIR'] . '/tests/TestFramework/TestDouble/Fake/FakeConfig/invalidConfig';

    $containerFactory = new ContainerFactory($configDir, Environment::TEST);
    $containerFactory->create();
})->throws(LogicException::class);
