<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Core\Category\UseCase\Create;

use SamihSoylu\Journal\Infrastructure\Port\Action\ActionInterface;

final readonly class CreateDefaultCategoriesAction implements ActionInterface
{
    public function __construct(
        public string $userId,
        public string $passwordTransientId,
    ) {}
}