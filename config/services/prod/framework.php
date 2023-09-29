<?php

declare(strict_types=1);

use DI\Container;
use Psr\EventDispatcher\EventDispatcherInterface;
use SamihSoylu\Journal\Framework\Environment;
use SamihSoylu\Journal\Framework\Event\EventDispatcherFactory;
use SamihSoylu\Journal\Framework\Event\Provider\EventListenerProvider;
use SamihSoylu\Journal\Framework\Event\Provider\EventSubscriberProvider;
use Symfony\Component\Finder\Finder;

return function (Container $container) {
    $container->set(Environment::class, function () {
        return Environment::from($_ENV['JOURNAL_ENV']);
    });

    $container->set(EventDispatcherInterface::class, function (Container $container) {
        $factory = $container->get(EventDispatcherFactory::class);

        return $factory->create();
    });

    $container->set(EventListenerProvider::class, function () {
        return new EventListenerProvider(Finder::create(), $_ENV['JOURNAL_APPLICATION_DIR']);
    });

    $container->set(EventSubscriberProvider::class, function() {
        return new EventSubscriberProvider(Finder::create(), $_ENV['JOURNAL_APPLICATION_DIR']);
    });
};