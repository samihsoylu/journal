<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Component\Template\Event;

final readonly class TemplateCreatedEvent
{
    public function __construct(
        public string $userId,
        public string $templateId,
    ) {}
}
