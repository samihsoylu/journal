<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Fake\FakeEventListener\FakeValidEventListener;

use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Fake\FakeEventListener\FakeEvent;

final class FakeEventListener
{
    public function __invoke(FakeEvent $event): void
    {
    }
}
