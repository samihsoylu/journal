<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework\Infrastructure\Port\Cache;

interface Cacheable
{
    public function get(string $key): ?string;

    /**
     * @param ?int $ttl The key's remaining Time To Live, in seconds
     */
    public function set(string $key, string $value, int $ttl = null): void;

    public function has(string $key): bool;

    public function remove(string $key): void;
}
