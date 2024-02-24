<?php

declare(strict_types=1);

use SamihSoylu\Journal\Framework\Infrastructure\Adapter\Cache\SecureTransient\SecureTransientCache;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub\StubCache;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub\StubTransientAesEncryptor;

it('should encrypt and store the value, and then decrypt on retrieval', function (): void {
    $stubCache = new StubCache();

    $expectedKey = 'test_key';
    $expectedValue = 'test_value';

    $secureCache = new SecureTransientCache($stubCache, new StubTransientAesEncryptor());
    $secureCache->set($expectedKey, $expectedValue);

    expect($stubCache->get($expectedKey))->not()->toEqual($expectedValue)
        ->and($secureCache->get($expectedKey))->toBe($expectedValue);
});

it('should check if an item exists in the cache', function (): void {
    $secureCache = new SecureTransientCache(new StubCache(), new StubTransientAesEncryptor());
    $expectedKey = 'test_key';

    expect($secureCache->has($expectedKey))->toBeFalse();

    $secureCache->set($expectedKey, 'random-value');
    expect($secureCache->has($expectedKey))->toBeTrue();
});

it('should remove item from cache', function (): void {
    $secureCache = new SecureTransientCache(new StubCache(), new StubTransientAesEncryptor());
    $expectedKey = 'test_key';

    $secureCache->set($expectedKey, 'test_value');
    expect($secureCache->has($expectedKey))->toBeTrue();

    $secureCache->remove($expectedKey);
    expect($secureCache->has($expectedKey))->toBeFalse();
});

it('should return null when no item is found', function (): void {
    $secureCache = new SecureTransientCache(new StubCache(), new StubTransientAesEncryptor());

    expect($secureCache->get('random_test_key'))->toBeNull();
});
