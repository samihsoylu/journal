<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\Journal\Infrastructure\Adapter\Action\Synchronous\SynchronousActionDispatcher;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionDispatcherInterface;

return function (Container $container) {
    $container->set(ActionDispatcherInterface::class, function (Container $container) {
        return $container->get(SynchronousActionDispatcher::class);
    });
};