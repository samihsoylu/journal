<?php

namespace App\Utility;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\CacheInterface;

class Cache
{
    protected static $instance = null;

    public CacheInterface $cache;

    protected function __construct()
    {
        $this->cache = new FilesystemAdapter('', 0, SESSION_CACHE_PATH);
    }

    public static function getInstance(): CacheInterface
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return (self::$instance)->cache;
    }
}