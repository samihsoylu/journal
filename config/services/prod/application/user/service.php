<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\Journal\Application\Workflow\CreateUserFlow;
use SamihSoylu\Journal\Infrastructure\Adapter\Cache\EncryptedTransient\EncryptedTransientCache;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionDispatcherInterface;

return function (Container $container) {
    $container->set(CreateUserFlow::class, function (Container $container) {
        return new CreateUserFlow(
            $container->get(EncryptedTransientCache::class),
            $container->get(ActionDispatcherInterface::class),
        );
    });
};