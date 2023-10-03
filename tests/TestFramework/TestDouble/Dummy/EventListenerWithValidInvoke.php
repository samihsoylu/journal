<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy;

final class EventListenerWithValidInvoke
{
    public function __invoke(object $object): void
    {
    }
}
