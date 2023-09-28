<?php

declare(strict_types=1);

use DI\Container;
use Psr\EventDispatcher\EventDispatcherInterface;
use SamihSoylu\Journal\Framework\Environment;
use SamihSoylu\Journal\Framework\Event\EventDispatcherFactory;
use SamihSoylu\Journal\Framework\Event\EventListener\EventListenerLocator;
use SamihSoylu\Journal\Framework\Event\EventSubscriber\EventSubscriberLocator;
use Symfony\Component\Finder\Finder;

return function (Container $container) {
    $container->set(Environment::class, function () {
        return Environment::from($_ENV['JOURNAL_ENV']);
    });

    $container->set(EventDispatcherInterface::class, function (Container $container) {
        $factory = $container->get(EventDispatcherFactory::class);

        return $factory->create();
    });

    $container->set(EventListenerLocator::class, function () {
        return new EventListenerLocator(Finder::create(), $_ENV['JOURNAL_APPLICATION_DIR']);
    });

    $container->set(EventSubscriberLocator::class, function() {
        return new EventSubscriberLocator(Finder::create(), $_ENV['JOURNAL_APPLICATION_DIR']);
    });
};