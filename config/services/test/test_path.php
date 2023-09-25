<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\Journal\Infrastructure\Adapter\Action\Synchronous\SynchronousActionDispatcher;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionDispatcherInterface;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestPath;

return function (Container $container) {
    $container->set(TestPath::class, function () {
        return new TestPath($_ENV['JOURNAL_TEST_DOUBLE_DIR_PATH']);
    });
};