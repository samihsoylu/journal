<?php

declare(strict_types=1);

use SamihSoylu\Journal\Framework\Environment;

it('should correctly identify PROD environment', function (): void {
    expect(Environment::PROD->isProd())->toBeTrue()
        ->and(Environment::PROD->isTest())->toBeFalse()
        ->and(Environment::PROD->isDev())->toBeFalse();
});

it('should correctly identify TEST environment', function (): void {
    expect(Environment::TEST->isProd())->toBeFalse()
        ->and(Environment::TEST->isTest())->toBeTrue()
        ->and(Environment::TEST->isDev())->toBeFalse();
});

it('should correctly identify DEV environment', function (): void {
    expect(Environment::DEV->isProd())->toBeFalse()
        ->and(Environment::DEV->isTest())->toBeFalse()
        ->and(Environment::DEV->isDev())->toBeTrue();
});
