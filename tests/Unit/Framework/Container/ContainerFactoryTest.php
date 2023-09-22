<?php

use Psr\Container\ContainerInterface;
use SamihSoylu\Journal\Framework\Container\ContainerFactory;
use SamihSoylu\Journal\Framework\Environment;
use SamihSoylu\Journal\Tests\Framework\TestDouble\Dummy\DummyObjectInterface;
use SamihSoylu\Journal\Tests\Framework\TestDouble\Dummy\DummyProdObject;
use SamihSoylu\Journal\Tests\Framework\TestDouble\Dummy\DummyTestObject;

it('should create a container', function() {
    $configDir = __DIR__ . '/config';
    $environment = Environment::DEV;

    $containerFactory = new ContainerFactory($configDir, $environment);
    $container = $containerFactory->create();

    expect($container)->toBeInstanceOf(ContainerInterface::class);
});

it('should load container configurations based on the app environment', function($environment, $expectedInstance) {
    $testConfigDir = $_ENV['JOURNAL_ROOT_DIR'] . '/tests/Framework/TestDouble/Fake/Core/Framework/Core/config';

    $factory = new ContainerFactory($testConfigDir, $environment);
    $container = $factory->create();

    $dummyObject = $container->get(DummyObjectInterface::class);
    expect($dummyObject)->toBeInstanceOf($expectedInstance);
})->with([
    'test' => [Environment::TEST, DummyTestObject::class],
    'production' => [Environment::PROD, DummyProdObject::class],
]);