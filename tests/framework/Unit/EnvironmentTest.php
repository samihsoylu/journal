<?php

declare(strict_types=1);

use SamihSoylu\Journal\Framework\Environment;

it('should correctly identify PROD environment', function () {
    expect(Environment::PROD->isProd())->toBeTrue()
        ->and(Environment::PROD->isTest())->toBeFalse()
        ->and(Environment::PROD->isDev())->toBeFalse();
});

it('should correctly identify TEST environment', function () {
    expect(Environment::TEST->isProd())->toBeFalse()
        ->and(Environment::TEST->isTest())->toBeTrue()
        ->and(Environment::TEST->isDev())->toBeFalse();
});

it('should correctly identify DEV environment', function () {
    expect(Environment::DEV->isProd())->toBeFalse()
        ->and(Environment::DEV->isTest())->toBeFalse()
        ->and(Environment::DEV->isDev())->toBeTrue();
});