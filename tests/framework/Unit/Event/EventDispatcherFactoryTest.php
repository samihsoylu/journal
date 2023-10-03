<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use SamihSoylu\Journal\Framework\Event\EventDispatcherFactory;
use SamihSoylu\Journal\Framework\Event\Provider\EventListenerProvider;
use SamihSoylu\Journal\Framework\Event\Provider\EventSubscriberProvider;
use SamihSoylu\Utility\ClassInspector;
use SamihSoylu\Utility\FileInspector;
use Symfony\Component\Finder\Finder;

it('should create event dispatcher with the correct listeners', function (): void {
    $fakeEventListenerDirPath = testKit()->testPath()->getFakeTestDoublePath() . '/FakeEventListener/FakeValidEventListener';

    $factory = new EventDispatcherFactory(
        testKit()->getService(ContainerInterface::class),
        new EventListenerProvider(Finder::create(), $fakeEventListenerDirPath),
        new EventSubscriberProvider(Finder::create(), $fakeEventListenerDirPath),
        testKit()->getService(FileInspector::class),
        testKit()->getService(ClassInspector::class),
    );

    $eventDispatcher = $factory->create();

    expect($eventDispatcher->getListeners())->toHaveCount(1);
});

it('should create event dispatcher with the correct subscriber', function (): void {
    $fakeEventSubscriberPath = testKit()->testPath()->getFakeTestDoublePath() . '/FakeEventListener/FakeValidEventSubscriber';

    $factory = new EventDispatcherFactory(
        testKit()->getService(ContainerInterface::class),
        new EventListenerProvider(Finder::create(), $fakeEventSubscriberPath),
        new EventSubscriberProvider(Finder::create(), $fakeEventSubscriberPath),
        testKit()->getService(FileInspector::class),
        testKit()->getService(ClassInspector::class),
    );

    $eventDispatcher = $factory->create();

    expect($eventDispatcher->getListeners())->toHaveCount(1);
});

it('should throw an exception when no event is provided in the event listener invoke method', function (): void {
    $fakeEventListenerDirPath = testKit()->testPath()->getFakeTestDoublePath() . '/FakeEventListener/FakeInvalidEventListener';

    $factory = new EventDispatcherFactory(
        testKit()->getService(ContainerInterface::class),
        new EventListenerProvider(Finder::create(), $fakeEventListenerDirPath),
        new EventSubscriberProvider(Finder::create(), $fakeEventListenerDirPath),
        testKit()->getService(FileInspector::class),
        testKit()->getService(ClassInspector::class),
    );

    $factory->create();
})->throws(LogicException::class)
    ->expectExceptionMessageMatches(
        "/The __invoke\(\) method in the listener class '([^']+)' is missing its required first parameter\. This parameter should be an object\. Make sure the method signature is __invoke\(Event \\\$event\)\./"
    );
