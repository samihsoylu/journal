<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Component\Template\UseCase\Create;

use SamihSoylu\Journal\Framework\Infrastructure\Port\Action\ActionInterface;

final readonly class CreateTemplateAction implements ActionInterface
{
    public function __construct(
        public string $title,
        public string $content,
        public string $userId,
        public string $categoryId,
    ) {}
}
