<?php

declare(strict_types=1);

use SamihSoylu\Journal\Infrastructure\Adapter\Cache\File\FileCache;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;

it('returns null if item is not in cache', function () {
    $adapter = new FilesystemAdapter('FileTest', 300);
    $fileCache = new FileCache($adapter);

    $key = 'test_key';
    $value = 'test_value';

    $fileCache->set($key, $value, 0);

    expect($fileCache->get($key))->toBeNull();
});

it('sets and gets item from cache', function () {
    $adapter = new FilesystemAdapter('FileTest', 300);
    $fileCache = new FileCache($adapter);

    $key = 'test_key';
    $value = 'test_value';

    $fileCache->set($key, $value);

    expect($fileCache->get($key))->toBe($value);
});

it('checks if an item exists in the cache', function () {
    $adapter = new FilesystemAdapter('FileTest', 300);
    $fileCache = new FileCache($adapter);

    $key = 'test_key';
    $value = 'test_value';

    $fileCache->set($key, $value);

    expect($fileCache->has($key))->toBeTrue();
});

it('removes item from cache', function () {
    $adapter = new FilesystemAdapter('FileTest', 300);
    $fileCache = new FileCache($adapter);

    $key = 'test_key';
    $value = 'test_value';

    $fileCache->set($key, $value);

    expect($fileCache->get($key))->toBeString();

    $fileCache->remove($key);

    expect($fileCache->get($key))->toBeNull();
});