<?php

declare(strict_types=1);

namespace App\Service\Model;

final readonly class SessionDecorator
{
    public function __construct(
        private bool $userHasAdminPrivileges,
        private string $antiCSRFToken,
        private int $privilegeLevel,
        private ?string $timezone = null,
    ) {}

    public function userHasAdminPrivileges() : bool
    {
        return $this->userHasAdminPrivileges;
    }

    public function getAntiCSRFToken() : string
    {
        return $this->antiCSRFToken;
    }

    public function getPrivilegeLevel() : int
    {
        return $this->privilegeLevel;
    }

    public function getTimezone() : ?string
    {
        return $this->timezone;
    }
}
