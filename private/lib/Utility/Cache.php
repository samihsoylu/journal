<?php declare(strict_types=1);

namespace App\Utility;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Cache utility that instantiates Symfony/Cache
 */
class Cache
{
    protected static $instance = null;
    public CacheInterface $cache;

    protected function __construct()
    {
        $this->cache = new FilesystemAdapter('', DEFAULT_CACHE_EXPIRY_TIME, SESSION_CACHE_PATH);
    }

    public static function getInstance(): FilesystemAdapter
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return (self::$instance)->cache;
    }
}
