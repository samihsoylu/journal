<?php declare(strict_types=1);

namespace App\Database\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * This model class represents a single database record from the `users` table.
 *
 * @ORM\Entity
 * @ORM\Table(name="users", uniqueConstraints={
 *     @UniqueConstraint(name="unique_username",columns={"username"}),
 *     @UniqueConstraint(name="unique_email",columns={"emailAddress"})
 * })
 */
class User extends AbstractModel
{
    public const PRIVILEGE_LEVEL_OWNER = 1;
    public const PRIVILEGE_LEVEL_ADMIN = 2;
    public const PRIVILEGE_LEVEL_USER  = 3;

    public const ALLOWED_PRIVILEGE_LEVELS = [
        self::PRIVILEGE_LEVEL_OWNER => 'Owner',
        self::PRIVILEGE_LEVEL_ADMIN => 'Admin',
        self::PRIVILEGE_LEVEL_USER  => 'User',
    ];

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected int $id;

    /**
     * @ORM\Column(type="string")
     */
    protected string $username;

    /**
     * @ORM\Column(type="text")
     */
    protected string $password;

    /**
     * @ORM\Column(type="string")
     */
    protected string $emailAddress;

    /**
     * @ORM\Column(type="integer", options={"unsigned":true, "default":0});
     */
    protected int $privilegeLevel;

    /**
     * @ORM\Column(type="text")
     */
    protected string $encryptionKey;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $createdTimestamp;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $lastUpdatedTimestamp;

    public function getUsername(): string
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

    public function getPrivilegeLevel(): int
    {
        return $this->privilegeLevel;
    }

    public function getPrivilegeLevelAsString(): string
    {
        $privilegeLevelAsString = self::ALLOWED_PRIVILEGE_LEVELS[$this->privilegeLevel] ?? null;
        if ($privilegeLevelAsString === null) {
            throw new \RuntimeException("Privilege level {$this->privilegeLevel} does not exist");
        }

        return $privilegeLevelAsString;
    }

    public function setPrivilegeLevel(int $privilegeLevel): self
    {
        if (!array_key_exists($privilegeLevel, self::ALLOWED_PRIVILEGE_LEVELS)) {
            throw new \RuntimeException("Privilege level {$privilegeLevel} does not exist");
        }

        $this->privilegeLevel = $privilegeLevel;

        return $this;
    }

    public function getEncryptionKey(): string
    {
        return $this->encryptionKey;
    }

    public function setEncryptionKey(string $encryptionKey): void
    {
        $this->encryptionKey = $encryptionKey;
    }
}
