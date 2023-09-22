<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\Journal\Framework\Console\CommandBootstrapper;
use SamihSoylu\Journal\Framework\Environment;
use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;

return function (Container $container) {
    $container->set(Application::class, function () {
        return new Application(
            $_ENV['JOURNAL_PROJECT_NAME'],
            $_ENV['JOURNAL_VERSION']
        );
    });

    $container->set(CommandBootstrapper::class, function (Container $container) {
        return new CommandBootstrapper(
            $container,
            $container->get(Application::class),
            $container->get(Finder::class),
            $_ENV['CONSOLE_COMMAND_DIR'],
            $_ENV['CONSOLE_COMMAND_NAMESPACE'],
        );
    });
};