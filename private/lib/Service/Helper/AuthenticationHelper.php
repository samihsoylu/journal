<?php declare(strict_types=1);

namespace App\Service\Helper;

use App\Utility\Cache;
use Symfony\Component\Cache\CacheItem;

class AuthenticationHelper
{
    public function getFailedLoginCount(): int
    {
        $cache = Cache::getInstance();

        $item = $cache->getItem($this->getIpAddressHashed());

        /** @var CacheItem $item */
        if ($item->isHit()) {
            return (int)$item->get();
        }

        return 0;
    }

    public function setFailedLoginCount(int $count): void
    {
        $cache = Cache::getInstance();

        // Since the `:` symbol is a reserved character, hashing the IP prevents an exception when using IPv6
        $item = $cache->getItem($this->getIpAddressHashed());

        /** @var CacheItem $item */
        $item->expiresAfter(3600);
        $item->set($count);
        $cache->save($item);
    }

    private function getIpAddressHashed(): string
    {
        return crypt($_SERVER['REMOTE_ADDR'], 'abcdef');
    }
}
