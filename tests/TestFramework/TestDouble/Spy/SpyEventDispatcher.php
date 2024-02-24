<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy;

use PHPUnit\Framework\Assert;
use Psr\EventDispatcher\EventDispatcherInterface;

final class SpyEventDispatcher extends Assert implements EventDispatcherInterface
{
    private array $dispatchedEvents = [];

    public function dispatch(object $event): void
    {
        $this->dispatchedEvents[] = $event;
    }

    /**
     * @param array<string, scalar> $expectedParameters
     */
    public function assertEventDispatchedWithParameters(string $expectedEvent, array $expectedParameters): void
    {
        $matchingEvents = array_filter(
            $this->dispatchedEvents,
            fn ($dispatchedEvent): bool => $dispatchedEvent::class === $expectedEvent
        );

        $dispatchedEvent = reset($matchingEvents);
        self::assertNotFalse($dispatchedEvent, "The event '{$expectedEvent}' was not dispatched.");

        $actualParameters = get_object_vars($dispatchedEvent);
        self::assertEquals(
            $expectedParameters,
            $actualParameters,
            "Parameters for the event '{$expectedEvent}' did not match."
        );
    }
}
