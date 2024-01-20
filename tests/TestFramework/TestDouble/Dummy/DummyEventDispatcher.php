<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy;

use Psr\EventDispatcher\EventDispatcherInterface;

final class DummyEventDispatcher implements EventDispatcherInterface
{
    public function dispatch(object $event): void
    {
    }
}
