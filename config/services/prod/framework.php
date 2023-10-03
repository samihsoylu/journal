<?php

declare(strict_types=1);

use DI\Container;
use Psr\EventDispatcher\EventDispatcherInterface;
use SamihSoylu\Journal\Framework\Environment;
use SamihSoylu\Journal\Framework\Event\EventDispatcherFactory;
use SamihSoylu\Journal\Framework\Event\Provider\EventListenerProvider;
use SamihSoylu\Journal\Framework\Event\Provider\EventSubscriberProvider;
use Symfony\Component\Finder\Finder;

return function (Container $container): void {
    $container->set(Environment::class, fn () => Environment::from($_ENV['JOURNAL_ENV']));

    $container->set(EventDispatcherInterface::class, function (Container $container) {
        $factory = $container->get(EventDispatcherFactory::class);

        return $factory->create();
    });

    $container->set(EventListenerProvider::class, fn () => new EventListenerProvider(Finder::create(), $_ENV['JOURNAL_APPLICATION_DIR']));

    $container->set(EventSubscriberProvider::class, fn () => new EventSubscriberProvider(Finder::create(), $_ENV['JOURNAL_APPLICATION_DIR']));
};
