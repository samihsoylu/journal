<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Core\Template\UseCase\Create;

use SamihSoylu\Journal\Infrastructure\Port\Action\ActionInterface;

final readonly class CreateDefaultTemplatesAction implements ActionInterface
{
    public function __construct(
        public string $userId,
        public string $passwordTransientId,
    ) {}
}