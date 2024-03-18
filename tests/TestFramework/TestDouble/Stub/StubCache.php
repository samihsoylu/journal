<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub;

use SamihSoylu\Journal\Framework\Infrastructure\Port\Cache\Cacheable;
use SamihSoylu\Journal\Framework\Infrastructure\Port\Cache\SecureCacheable;

final class StubCache implements Cacheable, SecureCacheable
{
    public const DEFAULT_KEY_FOR_TRANSIENT_PASSWORD = 'ABCDEF';
    private array $store = [];

    public function get(string $key): ?string
    {
        return $this->store[$key] ?? null;
    }

    public function set(string $key, string $value, int $ttl = null): void
    {
        $this->store[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->store);
    }

    public function remove(string $key): void
    {
        unset($this->store[$key]);
    }

    public function getFirstStoredKey(): ?string
    {
        foreach (array_keys($this->store) as $key) {
            return $key;
        }

        return null;
    }
}