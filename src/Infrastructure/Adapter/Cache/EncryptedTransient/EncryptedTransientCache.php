<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Infrastructure\Adapter\Cache\EncryptedTransient;

use DateTimeImmutable;
use SamihSoylu\CipherSuite\TransientAesEncryptor\TransientAesEncryptorInterface;
use SamihSoylu\Journal\Infrastructure\Port\Cache\Cacheable;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class EncryptedTransientCache implements Cacheable
{
    private const DEFAULT_EXPIRY_IN_SECONDS = 300;

    public function __construct(
        private FilesystemAdapter $cache,
        private TransientAesEncryptorInterface $encryptor,
    ) {}

    public function get(string $key): ?string
    {
        $item = $this->cache->getItem($key);
        if (!$item->isHit()) {
            return null;
        }

        return $this->encryptor->decrypt($item->get());
    }

    public function set(string $key, string $value, int $ttl = null): void
    {
        $ttl = $ttl ?? self::DEFAULT_EXPIRY_IN_SECONDS;
        $expiresAt = new DateTimeImmutable("+{$ttl} seconds");

        $item = $this->cache->getItem($key)
            ->set((string) $this->encryptor->encrypt($value))
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