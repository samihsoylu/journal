<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy;

final class EventListenerWithNoParamInInvokeMethod
{
    public function __invoke(): void
    {
    }
}
