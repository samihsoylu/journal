<?php

declare(strict_types=1);

use SamihSoylu\Journal\Infrastructure\Adapter\Cache\EncryptedTransient\EncryptedTransientCache;
use SamihSoylu\Journal\Infrastructure\Adapter\Cache\EncryptedTransient\EncryptionService;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

beforeEach(function() {
    // Mock the FilesystemAdapter
    $this->mockedCache = Mockery::mock(FilesystemAdapter::class);
    // Mock the EncryptionService
    $this->mockedEncryptor = Mockery::mock(EncryptionService::class);

    $this->encCache = new EncryptedTransientCache($this->mockedCache, $this->mockedEncryptor);
    $this->key = "sampleKey";
    $this->value = "sampleValue";
});

afterEach(function() {
    Mockery::close();
});

it('can set, get, check, and remove encrypted values', function() {
    $encryptedValue = "encryptedSampleValue";

    $this->mockedEncryptor->shouldReceive('encrypt')
        ->with($this->value)
        ->andReturn($encryptedValue);

    $this->mockedEncryptor->shouldReceive('decrypt')
        ->with($encryptedValue)
        ->andReturn($this->value);

    $cacheItem = Mockery::mock('overload:' . \Symfony\Component\Cache\CacheItem::class);
    $cacheItem->shouldReceive('isHit')->andReturn(true);
    $cacheItem->shouldReceive('get')->andReturn($encryptedValue);
    $cacheItem->shouldReceive('set')->andReturn($cacheItem);
    $cacheItem->shouldReceive('expiresAt')->andReturn($cacheItem);

    $this->mockedCache->shouldReceive('getItem')
        ->with($this->key)
        ->andReturn($cacheItem);

    $this->mockedCache->shouldReceive('save')->with($cacheItem);
    $this->mockedCache->shouldReceive('hasItem')->with($this->key)->andReturn(true);
    $this->mockedCache->shouldReceive('delete')->with($this->key);

    $this->encCache->set($this->key, $this->value);
    expect($this->encCache->get($this->key))->toBe($this->value)
        ->and($this->encCache->has($this->key))->toBe(true);

    $this->encCache->remove($this->key);
});

it('returns null when getting a non-existing key', function() {
    $cacheItem = Mockery::mock('overload:' . \Symfony\Component\Cache\CacheItem::class);
    $cacheItem->shouldReceive('isHit')->andReturn(false);

    $this->mockedCache->shouldReceive('getItem')
        ->with($this->key)
        ->andReturn($cacheItem);

    expect($this->encCache->get($this->key))->toBeNull();
});
