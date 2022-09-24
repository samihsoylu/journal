<?php declare(strict_types=1);

namespace App\Service\Model;

use App\Database\Model\User;

class UserDecorator
{
    private int $id;
    private string $username;
    private string $emailAddress;
    private string $createdTimestamp;
    private string $lastUpdatedTimestamp;
    private int $privilegeLevel;

    public const ALLOWED_PRIVILEGE_LEVELS = User::ALLOWED_PRIVILEGE_LEVELS;
    private bool $isReadOnly;
    private int $totalCategories;
    private int $totalEntries;
    private int $totalTemplates;

    public function __construct(User $user, bool $isReadOnly, int $totalCategories, int $totalEntries, int $totalTemplates)
    {
        $this->id                   = $user->getId();
        $this->username             = $user->getUsername();
        $this->emailAddress         = $user->getEmailAddress();
        $this->createdTimestamp     = $user->getCreatedTimestampFormatted();
        $this->lastUpdatedTimestamp = $user->getLastUpdatedTimestampFormatted();
        $this->privilegeLevel       = $user->getPrivilegeLevel();

        $this->isReadOnly           = $isReadOnly;
        $this->totalCategories      = $totalCategories;
        $this->totalEntries         = $totalEntries;
        $this->totalTemplates       = $totalTemplates;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getLastUpdatedTimestamp(): string
    {
        return $this->lastUpdatedTimestamp;
    }

    public function getCreatedTimestamp(): string
    {
        return $this->createdTimestamp;
    }

    public function getPrivilegeLevel(): int
    {
        return $this->privilegeLevel;
    }

    public function isReadOnly(): bool
    {
        return $this->isReadOnly;
    }

    public function getTotalCategories(): int
    {
        return $this->totalCategories;
    }

    public function getTotalEntries(): int
    {
        return $this->totalEntries;
    }

    public function getTotalTemplates(): int
    {
        return $this->totalTemplates;
    }
}
