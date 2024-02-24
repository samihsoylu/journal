<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework\Infrastructure\Port\Action;

interface ActionDispatcherInterface
{
    public function dispatch(ActionInterface $action): void;
}
