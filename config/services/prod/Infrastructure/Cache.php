<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\Journal\Framework\Infrastructure\Adapter\Cache\File\FileCache;
use SamihSoylu\Journal\Framework\Infrastructure\Adapter\Cache\SecureTransient\SecureTransientCache;
use SamihSoylu\Journal\Framework\Infrastructure\Port\Cache\Cacheable;
use SamihSoylu\Journal\Framework\Infrastructure\Port\Cache\SecureCacheable;

return function (Container $container): void {
    $container->set(Cacheable::class, fn (Container $container): FileCache => $container->get(FileCache::class));

    $container->set(SecureCacheable::class, fn (Container $container): SecureTransientCache => $container->get(SecureTransientCache::class));
};
