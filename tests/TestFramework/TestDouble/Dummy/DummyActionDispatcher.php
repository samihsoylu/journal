<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy;

use SamihSoylu\Journal\Infrastructure\Port\Action\ActionDispatcherInterface;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionInterface;

final class DummyActionDispatcher implements ActionDispatcherInterface
{
    public function dispatch(ActionInterface $action): void
    {
    }
}
