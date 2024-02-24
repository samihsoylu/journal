<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Listener\Category;

use SamihSoylu\Journal\Application\Component\Category\UseCase\Create\CreateDefaultCategoriesAction;
use SamihSoylu\Journal\Application\Component\User\Event\UserCreatedEvent;
use SamihSoylu\Journal\Framework\Infrastructure\Port\Action\ActionDispatcherInterface;

final readonly class CreateDefaultUserCategoriesListener
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
