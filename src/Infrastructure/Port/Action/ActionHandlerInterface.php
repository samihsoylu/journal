<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Infrastructure\Port\Action;

/**
 * @template TAction of ActionInterface
 */
interface ActionHandlerInterface
{
    /**
     * @param TAction $action
     */
    public function __invoke(ActionInterface $action): void;
}