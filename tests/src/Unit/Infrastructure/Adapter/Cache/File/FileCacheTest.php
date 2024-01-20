<?php

declare(strict_types=1);

use SamihSoylu\Journal\Infrastructure\Adapter\Cache\File\FileCache;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

it('should return null if item is not in cache', function (): void {
    $adapter = new FilesystemAdapter('FileTest', 300);
    $fileCache = new FileCache($adapter);

    $key = 'test_key';
    $value = 'test_value';

    $fileCache->set($key, $value, 0);

    expect($fileCache->get($key))->toBeNull();
});

it('should return set item from the cache', function (): void {
    $adapter = new FilesystemAdapter('FileTest', 300);
    $fileCache = new FileCache($adapter);

    $key = 'test_key';
    $value = 'test_value';

    $fileCache->set($key, $value);

    expect($fileCache->get($key))->toBe($value);
});

it('should return true if an item exists in the cache', function (): void {
    $adapter = new FilesystemAdapter('FileTest', 300);
    $fileCache = new FileCache($adapter);

    $key = 'test_key';
    $value = 'test_value';

    $fileCache->set($key, $value);

    expect($fileCache->has($key))->toBeTrue();
});

it('should remove an item from the cache', function (): void {
    $adapter = new FilesystemAdapter('FileTest', 300);
    $fileCache = new FileCache($adapter);

    $key = 'test_key';
    $value = 'test_value';

    $fileCache->set($key, $value);

    expect($fileCache->get($key))->toBeString();

    $fileCache->remove($key);

    expect($fileCache->get($key))->toBeNull();
});
