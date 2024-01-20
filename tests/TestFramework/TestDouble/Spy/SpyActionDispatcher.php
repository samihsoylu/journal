<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy;

use PHPUnit\Framework\Assert;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionDispatcherInterface;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionInterface;

final class SpyActionDispatcher extends Assert implements ActionDispatcherInterface
{
    /** @var array<ActionInterface> */
    private array $dispatchedActions = [];

    public function dispatch(ActionInterface $action): void
    {
        $this->dispatchedActions[] = $action;
    }

    public function assertActionDispatched(string $expectedAction): void
    {
        $matchingActions = array_filter(
            $this->dispatchedActions,
            fn ($dispatchedAction) => $dispatchedAction::class === $expectedAction
        );

        self::assertNotEmpty($matchingActions, "The action '{$expectedAction}' was not dispatched.");
    }

    public function assertActionDispatchedWithParameters(string $expectedAction, array $expectedParameters): void
    {
        $matchingActions = array_filter(
            $this->dispatchedActions,
            fn ($dispatchedAction) => $dispatchedAction::class === $expectedAction
        );

        $dispatchedAction = reset($matchingActions);
        self::assertNotFalse($dispatchedAction, "The action '{$expectedAction}' was not dispatched.");

        $actualParameters = get_object_vars($dispatchedAction);
        self::assertEquals(
            $expectedParameters,
            $actualParameters,
            "Parameters for the action '{$expectedAction}' did not match."
        );
    }
}
