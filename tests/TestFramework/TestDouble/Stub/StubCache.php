<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub;

use SamihSoylu\Journal\Infrastructure\Port\Cache\Cacheable;

final class StubCache implements Cacheable
{
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
        return isset($this->store[$key]);
    }

    public function remove(string $key): void
    {
        unset($this->store[$key]);
    }
}