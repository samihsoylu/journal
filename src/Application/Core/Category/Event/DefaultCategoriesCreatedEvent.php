<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Core\Category\Event;

final readonly class DefaultCategoriesCreatedEvent
{
    public function __construct(
        public string $userId,
        public string $passwordTransientId,
    ) {}
}