<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy;

use PHPUnit\Framework\Assert;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionHandlerInterface;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionInterface;

final class SpyActionHandler extends Assert implements ActionHandlerInterface
{
    private ?ActionInterface $actionInstance = null;

    public function __invoke(ActionInterface $action): void
    {
        $this->actionInstance = $action;
    }

    public function assertInvokedWith(ActionInterface $action): void
    {
        self::assertInstanceOf($action::class, $this->actionInstance);
    }
}
