<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy;

use SamihSoylu\Journal\Infrastructure\Port\Cache\Cacheable;
use SamihSoylu\Journal\Infrastructure\Port\Cache\SecureCacheable;

final class DummyCache implements Cacheable, SecureCacheable
{
    public function get(string $key): ?string
    {
        return null;
    }

    public function set(string $key, string $value, int $ttl = null): void
    {
    }

    public function has(string $key): bool
    {
        return false;
    }

    public function remove(string $key): void
    {
    }
}
