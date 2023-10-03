<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Infrastructure\Adapter\Action\Synchronous;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionDispatcherInterface;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionHandlerInterface;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionInterface;

final readonly class SynchronousActionDispatcher implements ActionDispatcherInterface
{
    public function __construct(
        private ContainerInterface $container,
    ) {}

    public function dispatch(ActionInterface $action): void
    {
        $className = $action::class . 'Handler';
        if (!class_exists($className)) {
            throw new InvalidArgumentException("Action Handler '{$className}' was not found");
        }

        /** @var ActionHandlerInterface $handler */
        $handler = $this->container->get($className);
        $handler->__invoke($action);
    }
}
