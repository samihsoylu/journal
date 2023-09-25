<?php

declare(strict_types=1);

use SamihSoylu\Journal\Infrastructure\Adapter\Cache\SecureTransient\SecureTransientCache;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub\StubCache;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub\StubTransientAesEncryptor;

it('encrypts and stores the value, and then decrypts on retrieval', function () {
    $stubCache = new StubCache();
    $stubEncryptor = new StubTransientAesEncryptor();
    $secureCache = new SecureTransientCache($stubCache, $stubEncryptor);
    $key = 'test_key';
    $value = 'test_value';

    $secureCache->set($key, $value);
    expect($stubCache->get($key))->toBe('encrypted:'.$value)
        ->and($secureCache->get($key))->toBe($value);
});

it('checks if an item exists in the cache', function () {
    $stubCache = new StubCache();
    $stubEncryptor = new StubTransientAesEncryptor();
    $secureCache = new SecureTransientCache($stubCache, $stubEncryptor);
    $key = 'test_key';

    expect($secureCache->has($key))->toBeFalse();
    $secureCache->set($key, 'value');
    expect($secureCache->has($key))->toBeTrue();
});

it('removes item from cache', function () {
    $stubCache = new StubCache();
    $stubEncryptor = new StubTransientAesEncryptor();
    $secureCache = new SecureTransientCache($stubCache, $stubEncryptor);
    $key = 'test_key';
    $value = 'test_value';

    $secureCache->set($key, $value);
    expect($secureCache->has($key))->toBeTrue();

    $secureCache->remove($key);
    expect($secureCache->has($key))->toBeFalse();
});