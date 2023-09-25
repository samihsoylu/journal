<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Infrastructure\Port\Action;

interface ActionHandlerInterface
{
    public function __invoke(ActionInterface $action): void;
}