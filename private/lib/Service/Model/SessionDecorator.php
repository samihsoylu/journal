<?php declare(strict_types=1);

namespace App\Service\Model;

class SessionDecorator
{
    private bool $userHasAdminPrivileges;
    private string $antiCSRFToken;
    private int $privilegeLevel;
    private ?string $timezone;

    public function __construct(
        bool $userHasAdminPrivileges,
        string $antiCSRFToken,
        int $privilegeLevel,
        ?string $timezone = null
    ) {
        $this->userHasAdminPrivileges = $userHasAdminPrivileges;
        $this->antiCSRFToken = $antiCSRFToken;
        $this->privilegeLevel = $privilegeLevel;
        $this->timezone = $timezone;
    }

    public function userHasAdminPrivileges(): bool
    {
        return $this->userHasAdminPrivileges;
    }

    public function getAntiCSRFToken(): string
    {
        return $this->antiCSRFToken;
    }

    public function getPrivilegeLevel(): int
    {
        return $this->privilegeLevel;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }
}
