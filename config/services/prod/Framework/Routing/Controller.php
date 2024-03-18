<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\Journal\Framework\Infrastructure\Adapter\Action\Synchronous\SynchronousActionDispatcher;
use SamihSoylu\Journal\Framework\Infrastructure\Port\Action\ActionDispatcherInterface;
use SamihSoylu\Journal\Framework\Routing\Controller\ValueResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory;

return function (Container $container): void {
    $container->set(ArgumentResolver::class, function (Container $container): ArgumentResolver {
        return new ArgumentResolver(
            argumentValueResolvers: [$container->get(ValueResolver::class)],
            namedResolvers: $container,
        );
    });
};
