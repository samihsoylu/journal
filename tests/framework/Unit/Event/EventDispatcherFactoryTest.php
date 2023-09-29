<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use SamihSoylu\Journal\Framework\Event\EventDispatcherFactory;
use SamihSoylu\Journal\Framework\Event\EventListener\EventListenerValidator;
use SamihSoylu\Journal\Framework\Event\Provider\EventListenerProvider;
use SamihSoylu\Journal\Framework\Event\Provider\EventSubscriberProvider;
use SamihSoylu\Journal\Framework\Util\PhpFileParser;
use SamihSoylu\Utility\ClassInspector;
use SamihSoylu\Utility\FileInspector;
use Symfony\Component\Finder\Finder;

it('should create event dispatcher with the correct listeners', function () {
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

it('should create event dispatcher with the correct subscriber', function () {
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

it('should throw an exception when an invalid class file is named to be a listener', function () {
    $fakeEventListenerDirPath = testKit()->testPath()->getFakeTestDoublePath() . '/FakeEventListener/FakeInvalidEventListener';

    $factory = new EventDispatcherFactory(
        testKit()->getService(ContainerInterface::class),
        new EventListenerProvider(Finder::create(), $fakeEventListenerDirPath),
        new EventSubscriberProvider(Finder::create(), $fakeEventListenerDirPath),
        testKit()->getService(FileInspector::class),
        testKit()->getService(ClassInspector::class),
    );

    $factory->create();
})->throws(UnexpectedValueException::class);