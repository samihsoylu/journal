<?php

use Psr\Container\ContainerInterface;
use SamihSoylu\Journal\Framework\Environment;
use SamihSoylu\Journal\Framework\Kernel;

it('should boot up kernel', function () {
    $kernel = Kernel::boot();

    expect($kernel->environment)->toBe(Environment::from($_ENV['JOURNAL_ENV']))
        ->and($kernel->isDebugMode)->toBe($_ENV['JOURNAL_ENABLE_DEBUG']);
});

it('should initialize container', function () {
    $_ENV['JOURNAL_CONFIG_DIR'] = $_ENV['JOURNAL_ROOT_DIR'] . '/tests/Framework/TestDouble/Fake/Core/Framework/Core';

    $kernel = Kernel::boot();

    expect($kernel->container)->toBeInstanceOf(ContainerInterface::class);
});