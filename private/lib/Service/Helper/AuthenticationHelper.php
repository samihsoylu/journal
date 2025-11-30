<?php

declare(strict_types=1);

namespace App\Service\Helper;

use App\Utility\Cache;
use Symfony\Component\Cache\CacheItem;

final readonly class AuthenticationHelper
{
    public function __construct(private Cache $cache) {}

    public function getFailedLoginCount() : int
    {
        $item = $this->cache->getItem($this->getIpAddressHashed());

        /** @var CacheItem $item */
        if ($item->isHit()) {
            return (int) $item->get();
        }

        return 0;
    }

    public function setFailedLoginCount(int $count) : void
    {
        // Since the `:` symbol is a reserved character, hashing the IP prevents an exception when using IPv6
        $item = $this->cache->getItem($this->getIpAddressHashed());

        /** @var CacheItem $item */
        $item->expiresAfter(3600);
        $item->set($count);

        $this->cache->save($item);
    }

    private function getIpAddressHashed() : string
    {
        return md5((string) $_SERVER['REMOTE_ADDR']);
    }
}
