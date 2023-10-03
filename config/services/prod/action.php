<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\Journal\Infrastructure\Adapter\Action\Synchronous\SynchronousActionDispatcher;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionDispatcherInterface;

return function (Container $container): void {
    $container->set(ActionDispatcherInterface::class, fn (Container $container) => $container->get(SynchronousActionDispatcher::class));
};
