<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Entity\Enum;

enum Role: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case USER  = 'user';

    public function isOwner(): bool
    {
        return $this === self::OWNER;
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isUser(): bool
    {
        return $this === self::USER;
    }
}
