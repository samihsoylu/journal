<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Core\User\Event;

final readonly class UserCreatedEvent
{
    public function __construct(
        public string $userId,
        public string $passwordTransientId,
    ) {}
}