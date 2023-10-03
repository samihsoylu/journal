<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use SamihSoylu\Journal\Framework\Environment;
use SamihSoylu\Journal\Framework\Kernel;

it('should boot up kernel', function (): void {
    $kernel = Kernel::boot();

    expect($kernel->environment)->toBe(Environment::from($_ENV['JOURNAL_ENV']));
});

it('should initialize container', function (): void {
    $_ENV['JOURNAL_CONFIG_DIR'] = $_ENV['JOURNAL_ROOT_DIR'] . '/tests/TestFramework/TestDouble/Fake/FakeConfig/config';

    $kernel = Kernel::boot();

    expect($kernel->container)->toBeInstanceOf(ContainerInterface::class);
});

it('should throw when environment variable is not set', function (): void {
    $env = $_ENV['JOURNAL_ENV'];
    unset($_ENV['JOURNAL_ENV']);

    afterEach(function () use ($env): void {
        $_ENV['JOURNAL_ENV'] = $env;
    });

    Kernel::boot();
})->throws(RuntimeException::class);
