<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework\Event;

use LogicException;
use Psr\Container\ContainerInterface;
use SamihSoylu\Journal\Framework\Event\Provider\EventListenerProvider;
use SamihSoylu\Journal\Framework\Event\Provider\EventSubscriberProvider;
use SamihSoylu\Utility\ClassInspector;
use SamihSoylu\Utility\FileInspector;
use Symfony\Component\EventDispatcher\EventDispatcher;

final readonly class EventDispatcherFactory
{
    private const ACTION_METHOD_NAME = '__invoke';

    public function __construct(
        private ContainerInterface $container,
        private EventListenerProvider $listenerProvider,
        private EventSubscriberProvider $subscriberProvider,
        private FileInspector $fileInspector,
        private ClassInspector $classInspector,
    ) {}

    public function create(): EventDispatcher
    {
        $dispatcher = new EventDispatcher();

        $listeners = $this->findAllListeners();
        foreach ($listeners as $eventName => $listener) {
            $dispatcher->addListener(
                $eventName,
                [$this->container->get($listener), self::ACTION_METHOD_NAME]
            );
        }

        $subscribers = $this->findAllSubscribers();
        foreach ($subscribers as $subscriber) {
            $dispatcher->addSubscriber($this->container->get($subscriber));
        }

        return $dispatcher;
    }

    private function findAllListeners(): array
    {
        $files = $this->listenerProvider->findEventListenerFiles();

        $listeners = [];
        foreach ($files as $file) {
            $fqcn = $this->fileInspector->getFullyQualifiedClassName($file);

            $eventName = $this->getEventName($fqcn);
            $listeners[$eventName] = $fqcn;
        }

        return $listeners;
    }

    private function findAllSubscribers(): array
    {
        $files = $this->subscriberProvider->findEventSubscriberFiles();

        $subscribers = [];
        foreach ($files as $file) {
            $subscribers[] = $this->fileInspector->getFullyQualifiedClassName($file);
        }

        return $subscribers;
    }

    private function getEventName(string $fqcn): string
    {
        $eventName = $this->classInspector->getFirstParameterTypeForMethod(
            $fqcn,
            self::ACTION_METHOD_NAME,
        );

        if ($eventName === null) {
            throw new LogicException(
                "The __invoke() method in the listener class '{$fqcn}' is missing its required first parameter. This parameter should be an object. Make sure the method signature is __invoke(Event \$event)."
            );
        }

        return $eventName;
    }
}
