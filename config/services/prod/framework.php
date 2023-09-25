<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\Journal\Framework\Environment;

return function (Container $container) {
    $container->set(Environment::class, function () {
        return Environment::from($_ENV['JOURNAL_ENV']);
    });
};