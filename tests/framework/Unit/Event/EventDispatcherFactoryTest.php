<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use SamihSoylu\Journal\Framework\Event\EventDispatcherFactory;
use SamihSoylu\Journal\Framework\Event\EventListener\EventListenerLocator;
use SamihSoylu\Journal\Framework\Event\EventListener\EventListenerValidator;
use SamihSoylu\Journal\Framework\Event\EventSubscriber\EventSubscriberLocator;
use SamihSoylu\Journal\Framework\Util\PhpFileParser;
use Symfony\Component\Finder\Finder;

it('should create event dispatcher with the correct listeners', function () {
    $fakeEventListenerDirPath = testKit()->testPath()->getFakeTestDoublePath() . '/FakeEventListener/FakeValidEventListener';

    $factory = new EventDispatcherFactory(
        testKit()->getService(ContainerInterface::class),
        new EventListenerLocator(Finder::create(), $fakeEventListenerDirPath),
        testKit()->getService(EventListenerValidator::class),
        new EventSubscriberLocator(Finder::create(), $fakeEventListenerDirPath),
        testKit()->getService(PhpFileParser::class),
    );

    $eventDispatcher = $factory->create();

    expect($eventDispatcher->getListeners())->toHaveCount(1);
});

it('should create event dispatcher with the correct subscriber', function () {
    $fakeEventSubscriberPath = testKit()->testPath()->getFakeTestDoublePath() . '/FakeEventListener/FakeValidEventSubscriber';

    $factory = new EventDispatcherFactory(
        testKit()->getService(ContainerInterface::class),
        new EventListenerLocator(Finder::create(), $fakeEventSubscriberPath),
        testKit()->getService(EventListenerValidator::class),
        new EventSubscriberLocator(Finder::create(), $fakeEventSubscriberPath),
        testKit()->getService(PhpFileParser::class),
    );

    $eventDispatcher = $factory->create();

    expect($eventDispatcher->getListeners())->toHaveCount(1);
});

it('should throw an exception when an invalid class file is named to be a listener', function () {
    $fakeEventListenerDirPath = testKit()->testPath()->getFakeTestDoublePath() . '/FakeEventListener/FakeInvalidEventListener';

    $factory = new EventDispatcherFactory(
        testKit()->getService(ContainerInterface::class),
        new EventListenerLocator(Finder::create(), $fakeEventListenerDirPath),
        testKit()->getService(EventListenerValidator::class),
        new EventSubscriberLocator(Finder::create(), $fakeEventListenerDirPath),
        testKit()->getService(PhpFileParser::class),
    );

    $factory->create();
})->throws(UnexpectedValueException::class);