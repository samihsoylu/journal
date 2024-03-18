<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\Journal\Framework\Console\CommandBootstrapper;
use SamihSoylu\Utility\FileInspector;
use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;

return function (Container $container): void {
    $container->set(Application::class, fn (): Application => new Application(
        $_ENV['JOURNAL_PROJECT_NAME'],
        $_ENV['JOURNAL_VERSION']
    ));

    $container->set(CommandBootstrapper::class, fn (Container $container): CommandBootstrapper => new CommandBootstrapper(
        $container,
        $container->get(Application::class),
        $container->get(Finder::class),
        $container->get(FileInspector::class),
        $_ENV['CONSOLE_COMMAND_DIR'],
    ));
};
