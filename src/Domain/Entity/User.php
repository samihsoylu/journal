<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use SamihSoylu\Journal\Domain\Entity\Enum\Role;
use SamihSoylu\Journal\Domain\Entity\Trait\Identifiable;
use SamihSoylu\Journal\Domain\Entity\Trait\Timestampable;
use SamihSoylu\Journal\Domain\Repository\Doctrine\UserRepository;

#[Entity(repositoryClass: UserRepository::class), HasLifecycleCallbacks]
class User extends BaseEntity
{
    use Identifiable, Timestampable;

    #[Column(length: 255)]
    protected string $username;

    #[Column(type: Types::TEXT)]
    protected string $password;

    #[Column(length: 255)]
    protected string $emailAddress;

    #[Column(type: 'string', enumType: Role::class)]
    protected Role $role;

    #[Column(type: Types::TEXT)]
    protected string $protectedKey;

    #[Column(length: 255, nullable: true)]
    protected string $preferredTimezone;

    #[PrePersist]
    public function checkErrors(): void
    {
        $requiredProperties = ['username', 'password', 'emailAddress', 'encryptionKey', 'role'];

        $this->assertRequiredPropertiesProvided($requiredProperties);
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function setRole(Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getProtectedKey(): string
    {
        return $this->protectedKey;
    }

    public function setProtectedKey(string $protectedKey): self
    {
        $this->protectedKey = $protectedKey;

        return $this;
    }

    public function getPreferredTimezone(): ?string
    {
        return $this->preferredTimezone;
    }

    public function setPreferredTimezone(string $preferredTimezone): self
    {
        $this->preferredTimezone = $preferredTimezone;

        return $this;
    }
}
