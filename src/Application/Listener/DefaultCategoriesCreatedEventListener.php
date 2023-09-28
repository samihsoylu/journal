<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Listener;

use SamihSoylu\Journal\Application\Core\Category\Event\DefaultCategoriesCreatedEvent;
use SamihSoylu\Journal\Application\Core\Template\UseCase\Create\CreateDefaultTemplatesAction;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionDispatcherInterface;

final class DefaultCategoriesCreatedEventListener
{
    public function __construct(
        private ActionDispatcherInterface $actionDispatcher,
    ) {}

    public function __invoke(DefaultCategoriesCreatedEvent $event): void
    {
        $this->actionDispatcher->dispatch(new CreateDefaultTemplatesAction(
            $event->userId,
            $event->passwordTransientId,
        ));
    }
}