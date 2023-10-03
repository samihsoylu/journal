<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Fake\FakeEventListener\FakeValidEventSubscriber;

use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Fake\FakeEventListener\FakeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FakeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FakeEvent::class => 'doNothing',
        ];
    }

    public function doNothing(FakeEvent $event): void
    {
    }
}
