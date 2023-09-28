<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework\Event;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;
use SamihSoylu\Journal\Framework\Event\EventListener\EventListenerLocator;
use SamihSoylu\Journal\Framework\Event\EventListener\EventListenerValidator;
use SamihSoylu\Journal\Framework\Event\EventSubscriber\EventSubscriberLocator;
use SamihSoylu\Utility\Assert;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\SplFileInfo;
use UnexpectedValueException;

final readonly class EventDispatcherFactory
{
    public function __construct(
        private ContainerInterface $container,
        private EventListenerLocator $listenerLocator,
        private EventListenerValidator $listenerValidator,
        private EventSubscriberLocator $subscriberLocator,
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
            $fqcn = $this->getClassFQCN($file);

            $this->listenerValidator->validateListener($fqcn);

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
            $subscribers[] = $this->getClassFQCN($file);
        }

        return $subscribers;
    }

    private function getClassFQCN(SplFileInfo $file): string
    {
        $namespace = $this->getNamespaceFromFile($file);
        $className = $this->getClassNameFromFile($file);

        return $namespace . '\\' . $className;
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

    /**
     * @throws UnexpectedValueException
     */
    private function getNamespaceFromFile(SplFileInfo $file): string
    {
        preg_match('/namespace\s+([a-zA-Z0-9\\\\_]+);/', $file->getContents(), $matches);

        $namespace = $matches[1] ?? null;
        Assert::notNull(
            $namespace,
            "Missing namespace declaration in the file located at '{$file->getRealPath()}'. Ensure that the file contains a valid 'namespace' statement at the top."
        );

        return $namespace;
    }

    /**
     * @throws UnexpectedValueException
     */
    private function getClassNameFromFile(SplFileInfo $file): string
    {
        preg_match('/\s+class\s+([a-zA-Z0-9_]+)/', $file->getContents(), $matches);

        $className = $matches[1] ?? null;
        Assert::notNull(
            $className,
            "No class name declaration was found in the file located at '{$file->getRealPath()}'. Please ensure that the file contains a valid class definition."
        );

        return $className;
    }

    private function getInvokeMethodParameter(ReflectionClass $listener): ?ReflectionParameter
    {
        $parameters = $listener->getMethod('__invoke')->getParameters();

        return $parameters[0] ?? null;
    }
}