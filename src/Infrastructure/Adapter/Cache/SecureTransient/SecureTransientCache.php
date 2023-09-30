<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Infrastructure\Adapter\Cache\SecureTransient;

use DateTimeImmutable;
use SamihSoylu\CipherSuite\TransientAesEncryptor\TransientAesEncryptorInterface;
use SamihSoylu\Journal\Infrastructure\Port\Cache\Cacheable;
use SamihSoylu\Journal\Infrastructure\Port\Cache\SecureCacheable;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class SecureTransientCache implements SecureCacheable
{
    private const DEFAULT_EXPIRY_IN_SECONDS = 300;

    public function __construct(
        private Cacheable $cache,
        private TransientAesEncryptorInterface $encryptor,
    ) {}

    public function get(string $key): ?string
    {
        $value = $this->cache->get($key);
        if ($value === null) {
            return null;
        }

        return $this->encryptor->decrypt($value);
    }

    public function set(string $key, string $value, int $ttl = null): void
    {
        $ttl = $ttl ?? self::DEFAULT_EXPIRY_IN_SECONDS;

        $this->cache->set(
            $key,
            $this->encryptor->encrypt($value),
            $ttl
        );
    }

    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }

    public function remove(string $key): void
    {
        $this->cache->remove($key);
    }
}