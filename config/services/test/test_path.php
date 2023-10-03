<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestPath;

return function (Container $container): void {
    $container->set(TestPath::class, fn () => new TestPath($_ENV['JOURNAL_TEST_DOUBLE_DIR_PATH']));
};
