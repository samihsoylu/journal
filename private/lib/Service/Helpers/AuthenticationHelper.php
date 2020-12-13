<?php

namespace App\Service\Helpers;

use App\Utility\Cache;
use Symfony\Component\Cache\CacheItem;

class AuthenticationHelper
{
    public function getFailedLoginCount(): int
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $cache = Cache::getInstance();

        /** @var CacheItem $item */
        $item = $cache->getItem($ipAddress);

        if ($item->isHit()) {
            return (int)$item->get();
        }

        return 0;
    }

    public function setFailedLoginCount(int $count): void
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $cache = Cache::getInstance();

        /** @var CacheItem $item */
        $item = $cache->getItem($ipAddress);

        $item->expiresAfter(3600);
        $item->set($count);
        $cache->save($item);
    }
}