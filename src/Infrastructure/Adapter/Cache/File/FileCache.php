<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Infrastructure\Adapter\Cache\File;

use DateTimeImmutable;
use SamihSoylu\Journal\Infrastructure\Port\Cache\Cacheable;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final readonly class FileCache implements Cacheable
{
    private const DEFAULT_EXPIRY_IN_SECONDS = 86400;

    public function __construct(
        private FilesystemAdapter $cache,
    ) {}

    public function get(string $key): ?string
    {
        $item = $this->cache->getItem($key);
        if (!$item->isHit()) {
            return null;
        }

        return $item->get();
    }

    public function set(string $key, string $value, int $ttl = null): void
    {
        $ttl ??= self::DEFAULT_EXPIRY_IN_SECONDS;
        $expiresAt = new DateTimeImmutable("+{$ttl} seconds");

        $item = $this->cache->getItem($key)
            ->set($value)
            ->expiresAt($expiresAt);

        $this->cache->save($item);
    }

    public function has(string $key): bool
    {
        return $this->cache->hasItem($key);
    }

    public function remove(string $key): void
    {
        $this->cache->delete($key);
    }
}
