<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\Framework;

use PHPUnit\Framework\TestCase;
use SamihSoylu\Journal\Contract\Framework\AppEnvironment;
use SamihSoylu\Journal\Infrastructure\Framework\Core\Kernel;
use SamihSoylu\Journal\Tests\Framework\Core\TestKit;

abstract class BaseTestCase extends TestCase
{
    protected TestKit $testKit;

    protected function setUp(): void
    {
        $kernel = Kernel::boot();

        if (!in_array($kernel->environment, [AppEnvironment::TEST, AppEnvironment::DEV])) {
            throw new \RuntimeException(
                "Cannot instantiate TestKit in production"
            );
        }

        $this->testKit = $kernel->container->get(TestKit::class);

        parent::setUp();
    }
}