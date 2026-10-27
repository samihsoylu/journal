<?php

declare(strict_types=1);

namespace App\Utility;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * Cache utility that provides Symfony/Cache FilesystemAdapter.
 */
final readonly class Cache
{
    private FilesystemAdapter $cache;

    public function __construct()
    {
        $this->cache = new FilesystemAdapter('', DEFAULT_CACHE_EXPIRY_TIME, SESSION_CACHE_PATH);
    }

    public function getItem(string $key) : mixed
    {
        return $this->cache->getItem($key);
    }

    public function save(mixed $item) : bool
    {
        return $this->cache->save($item);
    }

    public function delete(string $key) : bool
    {
        return $this->cache->delete($key);
    }
}
