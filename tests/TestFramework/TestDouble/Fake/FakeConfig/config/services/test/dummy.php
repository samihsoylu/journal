<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyObjectInterface;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyTestObject;

return function (Container $container): void {
    $container->set(DummyObjectInterface::class, fn (): DummyTestObject => new DummyTestObject());
};