<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Listener;

use SamihSoylu\Journal\Application\Core\Category\UseCase\Create\CreateDefaultCategoriesAction;
use SamihSoylu\Journal\Application\Core\User\Event\UserCreatedEvent;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionDispatcherInterface;

final readonly class UserCreatedEventListener
{
    public function __construct(
        private ActionDispatcherInterface $actionDispatcher,
    ) {}

    public function __invoke(UserCreatedEvent $event): void
    {
        $this->actionDispatcher->dispatch(
            new CreateDefaultCategoriesAction($event->userId, $event->passwordTransientId)
        );
    }
}