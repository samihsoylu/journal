<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy;

use PHPUnit\Framework\Assert;
use SamihSoylu\Journal\Infrastructure\Port\Cache\Cacheable;
use SamihSoylu\Journal\Infrastructure\Port\Cache\SecureCacheable;

class SpyCache extends Assert implements Cacheable, SecureCacheable
{
    protected array $store = [];

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

    public function assertValueExists(string $value): void
    {
        self::assertContains($value, $this->store);
    }
}
