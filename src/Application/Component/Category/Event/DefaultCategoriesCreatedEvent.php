<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Component\Category\Event;

final readonly class DefaultCategoriesCreatedEvent
{
    public function __construct(
        public string $userId,
        public string $passwordTransientId,
    ) {}
}
