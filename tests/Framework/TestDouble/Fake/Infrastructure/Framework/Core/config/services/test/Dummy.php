<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\Journal\Tests\Framework\TestDouble\Dummy\DummyObjectInterface;
use SamihSoylu\Journal\Tests\Framework\TestDouble\Dummy\DummyTestObject;

return function (Container $container) {
    $container->set(DummyObjectInterface::class, function (Container $container) {
        return new DummyTestObject();
    });
};