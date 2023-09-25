<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy;

use SamihSoylu\Journal\Infrastructure\Port\Action\ActionHandlerInterface;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionInterface;
use PHPUnit\Framework\TestCase as BaseTestCase;

final class SpyActionHandler extends BaseTestCase implements ActionHandlerInterface
{
    private ?ActionInterface $actionInstance = null;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function __invoke(ActionInterface $action): void
    {
        $this->actionInstance = $action;
    }

    public function assertInvokedWith(ActionInterface $action): void
    {
        self::assertInstanceOf($action::class, $this->actionInstance);
    }
}