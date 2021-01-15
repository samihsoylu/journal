<?php declare(strict_types=1);

namespace App\Service\Model;

class SessionDecorator
{
    private bool $userHasAdminPrivileges;
    private string $antiCSRFToken;
    private int $privilegeLevel;

    public function __construct(bool $userHasAdminPrivileges, string $antiCSRFToken, int $privilegeLevel)
    {
        $this->userHasAdminPrivileges = $userHasAdminPrivileges;
        $this->antiCSRFToken = $antiCSRFToken;
        $this->privilegeLevel = $privilegeLevel;
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
}
