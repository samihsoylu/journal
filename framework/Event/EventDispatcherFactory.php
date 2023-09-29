<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework\Event;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;
use SamihSoylu\Journal\Framework\Event\EventListener\EventListenerLocator;
use SamihSoylu\Journal\Framework\Event\EventListener\EventListenerValidator;
use SamihSoylu\Journal\Framework\Event\EventSubscriber\EventSubscriberLocator;
use SamihSoylu\Journal\Framework\Util\PhpFileParser;
use SamihSoylu\Utility\Assert;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\SplFileInfo;
use UnexpectedValueException;

final readonly class EventDispatcherFactory
{
    public function __construct(
        private ContainerInterface     $container,
        private EventListenerLocator   $listenerLocator,
        private EventListenerValidator $listenerValidator,
        private EventSubscriberLocator $subscriberLocator,
        private PhpFileParser          $fileHelper,
    ) {}

    public function create(): EventDispatcher
    {
        $dispatcher = new EventDispatcher();

        $listeners = $this->findAllListeners();
        foreach ($listeners as $eventName => $listener) {
            $dispatcher->addListener(
                $eventName,
                [$this->container->get($listener), '__invoke']
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
        $files = $this->listenerLocator->findEventListenerFiles();

        $listeners = [];
        foreach ($files as $file) {
            $fqcn = $this->fileHelper->getFullyQualifiedClassName($file);

            $this->listenerValidator->validateListener($fqcn);
            // refactor this, do you even need validateListener? Or You can perform assertions in get event name?

            $eventName = $this->getEventName($fqcn);
            $listeners[$eventName] = $fqcn;
        }

        return $listeners;
    }

    private function findAllSubscribers(): array
    {
        $files = $this->subscriberLocator->findEventSubscriberFiles();

        $subscribers = [];
        foreach ($files as $file) {
            $subscribers[] = $this->fileHelper->getFullyQualifiedClassName($file);
        }

        return $subscribers;
    }

    private function getEventName(string $fqcn): string
    {
        $listener = new ReflectionClass($fqcn);
        $type = $this->getInvokeMethodParameter($listener)?->getType();

        Assert::notNull(
            $type,
            "Listener class '{$fqcn}' has a missing or incorrect type declaration for its __invoke() method. The method's first parameter must have a type hint specifying an Event object."
        );

        return $type->getName();
    }

    private function getInvokeMethodParameter(ReflectionClass $listener): ?ReflectionParameter
    {
        $parameters = $listener->getMethod('__invoke')->getParameters();

        return $parameters[0] ?? null;
    }
}