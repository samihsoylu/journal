<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\Journal\Infrastructure\Adapter\Cache\File\FileCache;
use SamihSoylu\Journal\Infrastructure\Adapter\Cache\SecureTransient\SecureTransientCache;
use SamihSoylu\Journal\Infrastructure\Port\Cache\Cacheable;
use SamihSoylu\Journal\Infrastructure\Port\Cache\SecureCacheable;

return function (Container $container) {
    $container->set(Cacheable::class, function (Container $container) {
        return $container->get(FileCache::class);
    });

    $container->set(SecureCacheable::class, function (Container $container) {
       return $container->get(SecureTransientCache::class);
    });
};